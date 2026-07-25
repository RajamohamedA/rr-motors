<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesReturn;
use App\Models\SaleReturnItem;
use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    /**
     * Display a listing.
     */
    public function index()
    {
        $returns = SalesReturn::with([
            'sale',
            'customer'
        ])
        ->latest()
        ->paginate(15);

        return view('sales_returns.index', compact('returns'));
    }

    /**
     * Show create page.
     */
    public function create()
    {
        return view('sales_returns.create');
    }

    /**
     * Search Sales Invoice
     */
    public function searchInvoice(Request $request)
    {
        $search = $request->search;

        return Sales::with('customer')
            ->where('invoice_no', 'LIKE', "%{$search}%")
            ->orWhereHas('customer', function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get();
    }

    /**
     * Store Sales Return
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id'      => 'required|exists:sales,id',
            'customer_id'  => 'required|exists:customers,id',
            'return_date'  => 'required|date',
            'product_id'   => 'required|array',
            'qty'          => 'required|array',
            'price'        => 'required|array',
            'gst'          => 'required|array',
            'gst_amount'   => 'required|array',
            'amount'       => 'required|array',
        ]);
    
        DB::beginTransaction();
    
        try {
    
            // Load Sale
            $sale = Sales::with('items.product')->findOrFail($request->sale_id);
    
            // Company ID
            $companyId = $request->company_id ?? $sale->company_id;
    
            if (!$companyId) {
                throw new \Exception('Company ID not found.');
            }
    
            // Generate Return Number
            $returnNo = 'SR-' . str_pad(
                (SalesReturn::max('id') ?? 0) + 1,
                5,
                '0',
                STR_PAD_LEFT
            );
    
            // Create Sales Return Header
            $return = SalesReturn::create([
                'company_id'   => $companyId,
                'sale_id'      => $request->sale_id,
                'customer_id'  => $request->customer_id,
                'return_no'    => $returnNo,
                'return_date'  => $request->return_date,
                'total_amount' => $request->grand_total,
                'remarks'      => $request->remarks,
            ]);
    
            foreach ($request->product_id as $key => $productId) {
    
                $qty = (float) ($request->qty[$key] ?? 0);
    
                // Ignore zero quantity
                if ($qty <= 0) {
                    continue;
                }
    
                // Find Sale Item
                $saleItem = $sale->items
                    ->where('product_id', $productId)
                    ->first();
    
                if (!$saleItem) {
                    throw new \Exception("Product not found in Sale.");
                }
    
                // Already Returned Qty
                $returnedQty = SaleReturnItem::where('product_id', $productId)
                    ->whereHas('salesReturn', function ($q) use ($sale) {
                        $q->where('sale_id', $sale->id);
                    })
                    ->sum('quantity');
    
                $balanceQty = $saleItem->quantity - $returnedQty;
    
                if ($qty > $balanceQty) {
                    throw new \Exception(
                        "Return Qty exceeds Balance Qty for Product : "
                        . $saleItem->product->product_name
                    );
                }
    
                // Save Return Item
                SaleReturnItem::create([
                    'sale_return_id' => $return->id,
                    'product_id'     => $productId,
                    'quantity'       => $qty,
                    'sale_price'     => $request->price[$key],
                    'gst_percent'    => $request->gst[$key],
                    'gst_amount'     => $request->gst_amount[$key],
                    'line_total'     => $request->amount[$key],
                ]);
    
                // Restore Stock
                $product = Product::find($productId);
    
                if ($product) {
                    $product->current_stock += $qty;
                    $product->save();
                }
            }
    
            DB::commit();
    
            return redirect()
                ->route('sales-returns.index')
                ->with('success', 'Sales Return Saved Successfully.');
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show Return
     */
    public function show($id)
    {
        $return = SalesReturn::with([
            'sale',
            'customer',
            'items.product.unit'
        ])->findOrFail($id);

        return view('sales_returns.show', compact('return'));
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $return = SalesReturn::with([
            'customer',
            'sale',
            'items.product.unit'
        ])->findOrFail($id);

        return view('sales_returns.edit', compact('return'));
    }

    /**
     * Update
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $salesReturn = SalesReturn::with('items')->findOrFail($id);

            $salesReturn->update([
                'return_date' => $request->return_date,
                'remarks'     => $request->remarks,
            ]);

            // Delete old items
            $salesReturn->items()->delete();

            $grandTotal = 0;

            foreach ($request->product_id as $key => $productId) {

                $qty = (float)$request->qty[$key];

                if ($qty <= 0) {
                    continue;
                }

                $price = (float)$request->price[$key];

                $gst = (float)$request->gst[$key];

                $amount = $qty * $price;

                $gstAmount = ($amount * $gst) / 100;

                $lineTotal = $amount + $gstAmount;

                SaleReturnItem::create([

                    'sale_return_id' => $salesReturn->id,
                    'product_id'     => $productId,
                    'quantity'       => $qty,
                    'sale_price'     => $price,
                    'gst_percent'    => $gst,
                    'gst_amount'     => $gstAmount,
                    'line_total'     => $lineTotal,

                ]);

                $grandTotal += $lineTotal;
            }

            $salesReturn->update([
                'total_amount' => $grandTotal
            ]);

            DB::commit();

            return redirect()
                ->route('sales-returns.index')
                ->with('success', 'Sales Return Updated Successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            dd($e->getMessage());
        }
    }
    

    /**
     * Delete
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $salesReturn = SalesReturn::with('items')->findOrFail($id);

            /*
        |--------------------------------------------------------------------------
        | Reverse Stock
        |--------------------------------------------------------------------------
        */

            foreach ($salesReturn->items as $item) {

                $product = Product::find($item->product_id);

                if ($product) {

                    $this->stockService->increaseStock(

                        $product,

                        $item->quantity,

                        'Sales Return',

                        $salesReturn->company_id,

                        null, // store_id if available

                        $salesReturn->id,

                        $salesReturn->return_no,

                        $item->sale_price,

                        'Sales Return Deleted'

                    );
                }
            }

            /*
        |--------------------------------------------------------------------------
        | Delete Stock Ledger
        |--------------------------------------------------------------------------
        */

            StockLedger::where('reference_id', $salesReturn->id)
                ->where('transaction_type', 'Sales Return')
                ->delete();

            /*
        |--------------------------------------------------------------------------
        | Delete Return Items
        |--------------------------------------------------------------------------
        */

            $salesReturn->items()->delete();

            /*
        |--------------------------------------------------------------------------
        | Delete Return Header
        |--------------------------------------------------------------------------
        */

            $salesReturn->delete();

            DB::commit();

            return redirect()
                ->route('sales-returns.index')
                ->with('success', 'Sales Return Deleted Successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
    

    public function getInvoice($id)
    {
        $sale = Sales::with([
            'customer',
            'items.product.unit'
        ])->findOrFail($id);
    
        foreach ($sale->items as $item) {
    
            $returnedQty = SaleReturnItem::where('product_id', $item->product_id)
                ->whereHas('salesReturn', function ($q) use ($sale) {
                    $q->where('sale_id', $sale->id);
                })
                ->sum('quantity');
    
            $item->returned_qty = $returnedQty;
            $item->balance_qty = $item->quantity - $returnedQty;
        }
    
        return response()->json($sale);
    }

    public function print($id)
    {
        $return = SalesReturn::with([
            'customer',
            'sale',
            'items.product.unit'
        ])->findOrFail($id);

        return view('sales_returns.print', compact('return'));
    }
}