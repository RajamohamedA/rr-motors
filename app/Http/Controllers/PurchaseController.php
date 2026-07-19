<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Company;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\StockService;
use App\Models\StockLedger;

class PurchaseController extends Controller
{

    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    public function index()
    {
        $purchases = Purchase::with([
            'company',
            'store',
            'supplier'
        ])
        ->latest()
        ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $purchaseNo = 'PUR' . str_pad((Purchase::max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT);

        $companies = Company::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();
        $suppliers = Supplier::where('status', 1)->get();

        return view('purchases.create', compact(
            'purchaseNo',
            'companies',
            'stores',
            'suppliers'
        ));
    }

    public function search(Request $request)
    {
        $search = $request->search;

        $products = Product::where('status',1)
            ->where(function($q) use ($search){

                $q->where('product_code','like',"%{$search}%")
                ->orWhere('barcode','like',"%{$search}%")
                ->orWhere('product_name','like',"%{$search}%")
                ->orWhere('part_number','like',"%{$search}%");

            })
            ->select(
                'id',
                'product_code',
                'product_name',
                'purchase_price',
                'gst_percent',
                'current_stock'
            )
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {

        DB::beginTransaction();
    
        try {
    
            $purchase = Purchase::create([
    
                'company_id'     => $request->company_id,
                'store_id'       => $request->store_id,
                'supplier_id'    => $request->supplier_id,
    
                'purchase_no'    => $request->purchase_no,
                'purchase_date'  => $request->purchase_date,
    
                'invoice_no'     => $request->invoice_no,
                'invoice_date'   => $request->invoice_date,
    
                'subtotal'       => $request->grand_total,
                'discount'       => 0,
                'tax_amount'     => 0,
                'other_charges'  => 0,
                'grand_total'    => $request->grand_total,
    
                'paid_amount'    => 0,
                'balance_amount' => $request->grand_total,
    
                'remarks'        => $request->remarks,
    
                'status'         => 1
    
            ]);

    
            foreach ($request->product_id as $key => $productId) {
    
                $product = Product::findOrFail($productId);
    
                PurchaseItem::create([
    
                    'purchase_id'    => $purchase->id,
    
                    'product_id'     => $productId,
    
                    'quantity'       => $request->qty[$key],
    
                    'free_quantity'  => 0,
    
                    'purchase_price' => $request->price[$key],
    
                    'discount'       => 0,
    
                    'gst_percent'    => $request->gst[$key],
    
                    'gst_amount'     => 0,
    
                    'line_total'     => $request->qty[$key] * $request->price[$key],
    
                ]);


    
                $this->stockService->increaseStock(
    
                    $product,
    
                    $request->qty[$key],
    
                    'Purchase',
    
                    $purchase->company_id,
    
                    $purchase->store_id,
    
                    $purchase->id,
    
                    $purchase->purchase_no,
    
                    $request->price[$key]
    
                );
            }
    
            DB::commit();
    
            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase Saved Successfully.');
    
        } catch (\Exception $e) {
    
        dd($e->getMessage());
        
            DB::rollBack();
    
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
    public function show(string $id)
    {
        //
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items.product');

        return view('purchases.edit', [
            'purchase'  => $purchase,
            'companies' => Company::all(),
            'stores'    => Store::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'purchase_date' => 'required|date',
            'company_id'    => 'required',
            'store_id'      => 'required',
            'supplier_id'   => 'required',
            'product_id'    => 'required|array|min:1',
            'qty'           => 'required|array',
            'price'         => 'required|array',
            'gst'           => 'required|array',
        ]);
    
        DB::beginTransaction();
    
        try {
    
            /*
            |--------------------------------------------------------------------------
            | Reverse Previous Stock
            |--------------------------------------------------------------------------
            */
    
            foreach ($purchase->items as $item) {
    
                $product = Product::find($item->product_id);
    
                if ($product) {
    
                    $this->stockService->decreaseStock(
                        $product,
                        $item->quantity,
                        'Purchase',
                        $purchase->company_id,
                        $purchase->store_id,
                        $purchase->id,
                        $purchase->purchase_no,
                        $item->purchase_price,
                        'Purchase Updated'
                    );
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | Delete Previous Stock Ledger
            |--------------------------------------------------------------------------
            */
    
            StockLedger::where('reference_id', $purchase->id)
                ->where('transaction_type', 'Purchase')
                ->delete();
    
            /*
            |--------------------------------------------------------------------------
            | Delete Previous Purchase Items
            |--------------------------------------------------------------------------
            */
    
            $purchase->items()->delete();
    
            /*
            |--------------------------------------------------------------------------
            | Update Purchase Header
            |--------------------------------------------------------------------------
            */
    
            $purchase->update([
    
                'purchase_date' => $request->purchase_date,
    
                'company_id' => $request->company_id,
    
                'store_id' => $request->store_id,
    
                'supplier_id' => $request->supplier_id,
    
                'invoice_no' => $request->invoice_no,
    
                'invoice_date' => $request->invoice_date,
    
                'grand_total' => $request->grand_total,
    
                'remarks' => $request->remarks,
    
            ]);
    
            /*
            |--------------------------------------------------------------------------
            | Save Purchase Items
            |--------------------------------------------------------------------------
            */
    
            foreach ($request->product_id as $key => $productId) {
    
                $qty = (float)$request->qty[$key];
    
                $price = (float)$request->price[$key];
    
                $gstPercent = (float)$request->gst[$key];
    
                $discount = 0;
    
                $subtotal = $qty * $price;
    
                $gstAmount = ($subtotal - $discount) * $gstPercent / 100;
    
                $lineTotal = ($subtotal - $discount) + $gstAmount;
    
                PurchaseItem::create([
    
                    'purchase_id' => $purchase->id,
    
                    'product_id' => $productId,
    
                    'quantity' => $qty,
    
                    'free_quantity' => 0,
    
                    'purchase_price' => $price,
    
                    'discount' => $discount,
    
                    'gst_percent' => $gstPercent,
    
                    'gst_amount' => $gstAmount,
    
                    'line_total' => $lineTotal,
    
                ]);
    
                /*
                |--------------------------------------------------------------------------
                | Increase Stock Again
                |--------------------------------------------------------------------------
                */
    
                $product = Product::findOrFail($productId);
    
                $this->stockService->increaseStock(
    
                    $product,
    
                    $qty,
    
                    'Purchase',
    
                    $purchase->company_id,
    
                    $purchase->store_id,
    
                    $purchase->id,
    
                    $purchase->purchase_no,
    
                    $price,
    
                    'Purchase Updated'
    
                );
            }
    
            DB::commit();
    
            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase Updated Successfully.');
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
    
        }
    }

    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
    
        try {
    
            /*
            |--------------------------------------------------------------------------
            | Reverse Stock
            |--------------------------------------------------------------------------
            */
    
            foreach ($purchase->items as $item) {
    
                $product = Product::find($item->product_id);
    
                if ($product) {
    
                    $this->stockService->decreaseStock(
    
                        $product,
    
                        $item->quantity,
    
                        'Purchase',
    
                        $purchase->company_id,
    
                        $purchase->store_id,
    
                        $purchase->id,
    
                        $purchase->purchase_no,
    
                        $item->purchase_price,
    
                        'Purchase Deleted'
    
                    );
                }
            }
    
            /*
            |--------------------------------------------------------------------------
            | Delete Stock Ledger
            |--------------------------------------------------------------------------
            */
    
            StockLedger::where('reference_id', $purchase->id)
                ->where('transaction_type', 'Purchase')
                ->delete();
    
            /*
            |--------------------------------------------------------------------------
            | Delete Purchase Items
            |--------------------------------------------------------------------------
            */
    
            $purchase->items()->delete();
    
            /*
            |--------------------------------------------------------------------------
            | Delete Purchase
            |--------------------------------------------------------------------------
            */
    
            $purchase->delete();
    
            DB::commit();
    
            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase Deleted Successfully.');
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return back()->with('error', $e->getMessage());
    
        }
    }
}