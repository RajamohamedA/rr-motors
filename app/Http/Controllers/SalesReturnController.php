<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SalesReturn;
use App\Models\SaleReturnItem;
use App\Models\Product;
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
        DB::beginTransaction();

        try {

        $returnNo = 'SR-' . str_pad(
                        (SalesReturn::max('id') ?? 0) + 1,
                        5,
                        '0',
                        STR_PAD_LEFT
                    );

            $return = SalesReturn::create([

                'company_id'  => $request->company_id,

                'sale_id'     => $request->sale_id,

                'customer_id' => $request->customer_id,

                'return_no'   => $returnNo,

                'return_date' => $request->return_date,

                'total_amount'=> $request->grand_total,

                'remarks'     => $request->remarks,

            ]);

            foreach ($request->product_id as $key => $productId) {

                SaleReturnItem::create([

                    'sale_return_id' => $return->id,

                    'product_id'     => $productId,

                    'quantity'       => $request->qty[$key],

                    'sale_price'     => $request->price[$key],

                    'gst_percent'    => $request->gst[$key],

                    'gst_amount'     => $request->gst_amount[$key],

                    'line_total'     => $request->amount[$key],

                ]);

                // Restore Stock
                $product = Product::find($productId);

                if ($product) {

                    $product->current_stock += $request->qty[$key];

                    $product->save();
                }
            }

            DB::commit();

            return redirect()
                ->route('sales-returns.index')
                ->with('success', 'Sales Return Saved Successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
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
            'items.product',
            'customer',
            'sale'
        ])->findOrFail($id);

        return view('sales_returns.edit', compact('return'));
    }

    /**
     * Update
     */
    public function update(Request $request, $id)
    {
        return redirect()
            ->route('sales-returns.index')
            ->with('success', 'Update feature will be added next.');
    }

    /**
     * Delete
     */
    public function destroy($id)
    {
        return redirect()
            ->route('sales-returns.index')
            ->with('success', 'Delete feature will be added next.');
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
}