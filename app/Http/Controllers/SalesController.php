<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SaleItems;
use App\Models\Product;
use App\Models\Company;
use App\Models\Store;       
use App\Models\Customer;
use App\Models\StockLedger;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index()
    {
        $sales = Sales::with('customer')
            ->latest()
            ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $companies = Company::where('status',1)->get();
        $stores = Store::where('status',1)->get();
        $customers = Customer::where('status',1)->get();

        $last = Sales::latest('id')->first();

        $next = $last ? $last->id + 1 : 1;

        $invoiceNo = 'SAL'.str_pad($next,6,'0',STR_PAD_LEFT);

        return view('sales.create', compact(
            'companies',
            'stores',
            'customers',
            'invoiceNo'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([

            'invoice_no'=>'required|unique:sales',

            'invoice_date'=>'required',

            'company_id'=>'required',

            'store_id'=>'required',

            'customer_id'=>'required',

            'product_id'=>'required|array',

            'qty'=>'required|array',

            'price'=>'required|array'

        ]);

        DB::transaction(function() use($request){

            $sale = Sales::create([

                'company_id'=>$request->company_id,

                'store_id'=>$request->store_id,

                'customer_id'=>$request->customer_id,

                'invoice_no'=>$request->invoice_no,

                'invoice_date'=>$request->invoice_date,

                'sub_total'=>$request->grand_total,

                'discount'=>0,

                'gst_total'=>0,

                'grand_total'=>$request->grand_total,

                'paid_amount'=>$request->paid_amount ?? 0,

                'balance_amount'=>$request->grand_total - ($request->paid_amount ?? 0),

                'remarks'=>$request->remarks

            ]);

            foreach($request->product_id as $key=>$productId){

                $product = Product::findOrFail($productId);

                $qty = $request->qty[$key];

                $price = $request->price[$key];

                $gst = $request->gst[$key];

                $amount = $qty * $price;

                SaleItems::create([

                    'sale_id'=>$sale->id,

                    'product_id'=>$productId,

                    'quantity'=>$qty,

                    'free_quantity'=>0,

                    'sale_price'=>$price,

                    'discount'=>0,

                    'gst_percent'=>$gst,

                    'gst_amount'=>0,

                    'line_total'=>$amount

                ]);

                $this->stockService->decreaseStock(

                    $product,

                    $qty,

                    'Sale',

                    $sale->company_id,

                    $sale->store_id,

                    $sale->id,

                    $sale->invoice_no,

                    $price,

                    'Sales Invoice'

                );

            }

        });

        return redirect()
            ->route('sales.index')
            ->with('success','Sales Invoice Saved Successfully');
    }

    public function show($id)
    {
        $sale = Sales::with([
            'customer',
            'company',
            'store',
            'items.product'
        ])->findOrFail($id);

        return view('sales.show', compact('sale'));
    }

    public function edit($id)
    {
        $sale = Sales::with('items')->findOrFail($id);

        $customers = Customer   ::where('status', 1)->get();

        $companies = Company::where('status',1)->get();
        $stores = Store::where('status',1)->get();

        return view('sales.edit', compact(
            'sale',
            'customers',
            'companies',
            'stores'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company_id' => 'required',
            'store_id' => 'required',
            'customer_id' => 'required',
            'invoice_date' => 'required|date',
            'product_id' => 'required|array|min:1',
            'qty' => 'required|array',
            'price' => 'required|array',
            'gst' => 'required|array',
        ]);
    
        DB::beginTransaction();
    
        try {
    
            $sale = Sales::findOrFail($id);
    
            // ===========================
            // Restore Previous Stock
            // ===========================
    
            $oldItems = SaleItems::where('sale_id', $sale->id)->get();
    
            foreach ($oldItems as $item) {
    
                $product = Product::find($item->product_id);
    
                if ($product) {
                    $product->current_stock += $item->quantity;
                    $product->save();
                }
            }
    
            // ===========================
            // Delete Old Items
            // ===========================
    
            SaleItems::where('sale_id', $sale->id)->delete();
    
            // ===========================
            // Update Sales Header
            // ===========================
    
            $sale->update([
    
                'company_id'     => $request->company_id,
                'store_id'       => $request->store_id,
                'customer_id'    => $request->customer_id,
                'invoice_date'   => $request->invoice_date,
                'grand_total'    => $request->grand_total,
                'paid_amount'    => $request->paid_amount ?? 0,
                'balance_amount' => $request->balance_amount ?? 0,
                'remarks'        => $request->remarks,
    
            ]);
    
            // ===========================
            // Save New Items
            // ===========================
    
            foreach ($request->product_id as $key => $productId) {
    
                $qty = $request->qty[$key];
                $price = $request->price[$key];
                $gst = $request->gst[$key];
    
                $gstAmount = ($qty * $price * $gst) / 100;
    
                $lineTotal = ($qty * $price) + $gstAmount;
    
                SaleItems::create([
    
                    'sale_id'       => $sale->id,
                    'product_id'    => $productId,
                    'quantity'      => $qty,
                    'free_quantity' => 0,
                    'sale_price'    => $price,
                    'discount'      => 0,
                    'gst_percent'   => $gst,
                    'gst_amount'    => $gstAmount,
                    'line_total'    => $lineTotal,
    
                ]);
    
                // ===========================
                // Deduct Stock
                // ===========================
    
                $product = Product::find($productId);
    
                if ($product) {
    
                    if ($product->current_stock < $qty) {
                        throw new \Exception("Insufficient stock for {$product->product_name}");
                    }
    
                    $product->current_stock -= $qty;
                    $product->save();
                }
            }
    
            DB::commit();
    
            return redirect()
                ->route('sales.index')
                ->with('success', 'Sales Invoice updated successfully.');
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function print($id)
    {
        $sale = Sales::with([
            'company',
            'store',
            'customer',
            'items.product'
        ])->findOrFail($id);

        return view('sales.print', compact('sale'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $sale = Sales::with('items')->findOrFail($id);

            // Restore Stock
            foreach ($sale->items as $item) {

                $product = Product::find($item->product_id);

                if ($product) {

                    $product->current_stock += $item->quantity;

                    $product->save();
                }
            }

            // Delete Items
            SaleItems::where('sale_id', $sale->id)->delete();

            // Delete Invoice
            $sale->delete();

            DB::commit();

            return redirect()
                ->route('sales.index')
                ->with('success','Sales Invoice deleted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error',$e->getMessage());
        }
    }
}   