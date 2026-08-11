<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Store;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Stock;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;



class StockAdjustmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:stock-adjustment.view',
                only: ['index']
            ),

            new Middleware(
                'permission:stock-adjustment.create',
                only: [
                    'create',
                    'store',
                    'searchProduct'
                ]
            ),

            new Middleware(
                'permission:stock-adjustment.edit',
                only: [
                    'edit',
                    'update'
                ]
            ),

            new Middleware(
                'permission:stock-adjustment.delete',
                only: [
                    'destroy'
                ]
            ),

            new Middleware(
                'permission:stock-adjustment.show',
                only: [
                    'show'
                ]
            ),

            new Middleware(
                'permission:stock-adjustment.print',
                only: [
                    'print'
                ]
            ),

        ];
    }


    public function index()
    {
        $adjustments = StockAdjustment::with('store')
            ->latest()
            ->get();

        return view(
            'stock_adjustments.index',
            compact('adjustments')
        );
    }

    public function create(Request $request)
    {

        $products = Product::all();

        $stores = Store::all();

        $selectedProduct = null;
        $selectedStore = null;
        $currentStock = 0;
        $unitCost = 0;


        if ($request->product) {
            $selectedProduct = Product::find($request->product);

            if ($selectedProduct) {
                $unitCost = $selectedProduct->purchase_price;
            }
        }


        if ($request->store) {
            $selectedStore = Store::find($request->store);
        }



        if ($selectedProduct && $selectedStore) {
            $currentStock = StockLedger::where('store_id', $selectedStore->id)
                ->where('product_id', $selectedProduct->id)
                ->latest('id')
                ->value('balance_qty');

            $currentStock = (float) ($currentStock ?? 0);
        }

        return view(
            'stock_adjustments.create',
            compact(
                'products',
                'stores',
                'selectedProduct',
                'selectedStore',
                'currentStock',
                'unitCost'
            )
        );
    }



    public function store(Request $request)
    {
        $request->validate([
            'store_id'   => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|numeric|min:0.01',
            'type'       => 'required|in:increase,decrease',
            'unit_cost'  => 'required|numeric|min:0',
            'remarks'    => 'nullable|string|max:500',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Get Current Stock From Stock Ledger
        |--------------------------------------------------------------------------
        */

        $currentStock = StockLedger::where('store_id', $request->store_id)
            ->where('product_id', $request->product_id)
            ->latest('id')
            ->value('balance_qty');

        $currentStock = (float) ($currentStock ?? 0);


        /*
        |--------------------------------------------------------------------------
        | Prevent Negative Stock
        |--------------------------------------------------------------------------
        */

        if (
            $request->type === 'decrease' &&
            $request->qty > $currentStock
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'qty' => 'Adjustment quantity cannot exceed available stock.',
                ]);
        }


        DB::transaction(function () use ($request, $currentStock) {

            $product = Product::findOrFail($request->product_id);


            /*
            |--------------------------------------------------------------------------
            | Calculate New Balance
            |--------------------------------------------------------------------------
            */

            $newBalance = $request->type === 'increase'
                ? $currentStock + $request->qty
                : $currentStock - $request->qty;


            /*
            |--------------------------------------------------------------------------
            | Create Stock Adjustment
            |--------------------------------------------------------------------------
            */

            $adjustment = StockAdjustment::create([
                'company_id'      => auth()->user()->company_id,
                'store_id'        => $request->store_id,
                'adjustment_date' => now(),
                'adjustment_no'   => 'ADJ-' . now()->format('YmdHis'),
                'type'            => $request->type,
                'remarks'         => $request->remarks,
                'created_by'      => auth()->id(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create Adjustment Item
            |--------------------------------------------------------------------------
            */

            $adjustment->items()->create([
                'product_id' => $product->id,
                'qty'        => $request->qty,
                'unit_cost'  => $request->unit_cost,
                'total_cost' => $request->qty * $request->unit_cost,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create Stock Ledger
            |--------------------------------------------------------------------------
            */

            StockLedger::create([
                'company_id'       => auth()->user()->company_id,
                'store_id'         => $request->store_id,
                'product_id'       => $product->id,
                'transaction_date' => now(),
                'transaction_type' => 'Stock Adjustment',

                'reference_id'     => $adjustment->id,
                'reference_no'     => $adjustment->adjustment_no,

                'qty_in' => $request->type === 'increase'
                    ? $request->qty
                    : 0,

                'qty_out' => $request->type === 'decrease'
                    ? $request->qty
                    : 0,

                'balance_qty' => $newBalance,

                'unit_cost' => $request->unit_cost,

                'total_cost' => $request->qty * $request->unit_cost,

                'remarks' => $request->remarks,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Update Product Current Stock
            |--------------------------------------------------------------------------
            */

            $product->current_stock = $newBalance;

            $product->save();
        });


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->can('stock-adjustment.view')) {

            return redirect()
                ->route('stock-adjustments.index')
                ->with(
                    'success',
                    'Stock adjusted successfully.'
                );
        }

        return redirect()
            ->route('inventory.current-stock')
            ->with(
                'success',
                'Stock adjusted successfully.'
            );
    }

    public function getProductStock(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $currentStock = StockLedger::where('store_id', $request->store_id)
            ->where('product_id', $request->product_id)
            ->latest('id')
            ->value('balance_qty');

        $currentStock = (float) ($currentStock ?? 0);

        return response()->json([
            'stock' => $currentStock,
            'unit_cost' => $product->purchase_price,
        ]);
    }

    public function show($id)
    {

     dd('test');
        $adjustment = StockAdjustment::with('items.product')
            ->findOrFail($id);

        return view(
            'stock_adjustments.show',
            compact('adjustment')
        );
    }
}
