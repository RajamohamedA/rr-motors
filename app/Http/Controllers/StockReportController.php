<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Store;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:current-stock.view',
                only: [
                    'index',
                    'search',
                    'currentStock'
                ]
            ),

            new Middleware(
                'permission:current-stock.print',
                only: [
                    'print'
                ]
            ),

            new Middleware(
                'permission:current-stock.pdf',
                only: [
                    'pdf'
                ]
            ),

            new Middleware(
                'permission:current-stock.excel',
                only: [
                    'csv'
                ]
            ),

        ];
    }
    /**
     * Build Current Stock Query
     */
    private function stockQuery(Request $request)
    {
        $query = StockLedger::with([
            'product.category',
            'product.brand',
            'product.unit',
            'company',
            'store'
        ])
        ->select(
            'company_id',
            'store_id',
            'product_id'
        )
        ->selectRaw('SUM(qty_in - qty_out) as current_stock')
        ->groupBy(
            'company_id',
            'store_id',
            'product_id'
        );

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        return $query;
    }

    /**
     * Current Stock Screen
     */
    public function currentStock(Request $request)
    {
        $stocks = $this->stockQuery($request)->get();

        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {

            $stocks = $stocks->filter(function ($row) use ($request) {

                return optional($row->product)->category_id == $request->category_id;

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Brand Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('brand_id')) {

            $stocks = $stocks->filter(function ($row) use ($request) {

                return optional($row->product)->brand_id == $request->brand_id;

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Product Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('product')) {

            $search = strtolower($request->product);

            $stocks = $stocks->filter(function ($row) use ($search) {

                return str_contains(
                    strtolower(optional($row->product)->product_name),
                    $search
                );

            });

        }

        $companies = Company::where('status',1)
                            ->orderBy('company_name')
                            ->get();

        $stores = Store::where('status',1)
                        ->orderBy('store_name')
                        ->get();

        $categories = Category::where('status',1)
                                ->orderBy('name')
                                ->get();

        $brands = Brand::where('status',1)
                        ->orderBy('brand_name')
                        ->get();

        $products = Product::where('status',1)
                            ->orderBy('product_name')
                            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Summary
        |--------------------------------------------------------------------------
        */

        $summary = [

            'products' => $stocks->count(),

            'qty' => $stocks->sum('current_stock'),

            'value' => $stocks->sum(function ($stock) {

                return $stock->current_stock *
                    (optional($stock->product)->purchase_price ?? 0);

            }),

            'low_stock' => $stocks->filter(function ($stock) {

                $min = optional($stock->product)->minimum_stock ?? 10;

                return $stock->current_stock > 0 &&
                       $stock->current_stock <= $min;

            })->count(),

            'out_stock' => $stocks->filter(function ($stock) {

                return $stock->current_stock <= 0;

            })->count(),

        ];

        return view(
            'inventory.current-stock.index',
            compact(
                'stocks',
                'companies',
                'stores',
                'categories',
                'brands',
                'products',
                'summary'
            )
        );
    }

    /**
     * Print Current Stock
     */
    public function print(Request $request)
    {
        $stocks = $this->stockQuery($request)->get();

        if ($request->filled('category_id')) {

            $stocks = $stocks->filter(function ($row) use ($request) {

                return optional($row->product)->category_id == $request->category_id;
            });
        }

        if ($request->filled('brand_id')) {

            $stocks = $stocks->filter(function ($row) use ($request) {

                return optional($row->product)->brand_id == $request->brand_id;
            });
        }

        if ($request->filled('product')) {

            $search = strtolower($request->product);

            $stocks = $stocks->filter(function ($row) use ($search) {

                return str_contains(
                    strtolower(optional($row->product)->product_name),
                    $search
                );
            });
        }

        $summary = [

            'products' => $stocks->count(),

            'qty' => $stocks->sum('current_stock'),

            'value' => $stocks->sum(function ($stock) {

                return $stock->current_stock *
                    (optional($stock->product)->purchase_price ?? 0);
            }),

            'low_stock' => $stocks->filter(function ($stock) {

                $min = optional($stock->product)->minimum_stock ?? 10;

                return $stock->current_stock > 0 &&
                    $stock->current_stock <= $min;
            })->count(),

            'out_stock' => $stocks->filter(function ($stock) {

                return $stock->current_stock <= 0;
            })->count(),

        ];

        return view(
            'inventory.current-stock.print',
            compact(
                'stocks',
                'summary'
            )
        );
    }

    /**
     * Export Current Stock PDF
     */
    public function pdf(Request $request)
    {
        $stocks = $this->stockQuery($request)->get();

        $summary = [

            'products' => $stocks->count(),

            'qty' => $stocks->sum('current_stock'),

            'value' => $stocks->sum(function ($stock) {

                return $stock->current_stock *
                    ($stock->product->purchase_price ?? 0);
            }),

            'low_stock' => $stocks->filter(function ($stock) {

                return $stock->current_stock > 0 &&
                    $stock->current_stock <= ($stock->product->minimum_stock ?? 10);
            })->count(),

            'out_stock' => $stocks->where('current_stock', '<=', 0)->count(),

        ];

        $pdf = Pdf::loadView(
            'inventory.current-stock.pdf',
            compact('stocks', 'summary')
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('CurrentStock.pdf');
    }

    /**
     * Export Current Stock CSV
     */
    public function csv(Request $request)
    {
        $stocks = $this->stockQuery($request)->get();

        if ($request->filled('category_id')) {
            $stocks = $stocks->filter(function ($row) use ($request) {
                return optional($row->product)->category_id == $request->category_id;
            });
        }

        if ($request->filled('brand_id')) {
            $stocks = $stocks->filter(function ($row) use ($request) {
                return optional($row->product)->brand_id == $request->brand_id;
            });
        }

        if ($request->filled('product')) {
            $search = strtolower($request->product);

            $stocks = $stocks->filter(function ($row) use ($search) {

                return str_contains(
                    strtolower(optional($row->product)->product_name),
                    $search
                );
            });
        }

        $fileName = 'Current_Stock_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($stocks) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Product Code',
                'Product Name',
                'Category',
                'Brand',
                'Company',
                'Store',
                'Unit',
                'Purchase Price',
                'Selling Price',
                'Current Stock',
                'Stock Value'
            ]);

            foreach ($stocks as $stock) {

                $purchasePrice = $stock->product->purchase_price ?? 0;
                $sellingPrice  = $stock->product->selling_price ?? 0;
                $stockValue    = $stock->current_stock * $purchasePrice;

                fputcsv($file, [

                    $stock->product->product_code,

                    $stock->product->product_name,

                    optional($stock->product->category)->category_name,

                    optional($stock->product->brand)->brand_name,

                    optional($stock->company)->company_name,

                    optional($stock->store)->store_name,

                    optional($stock->product->unit)->short_name,

                    number_format($purchasePrice, 2, '.', ''),

                    number_format($sellingPrice, 2, '.', ''),

                    number_format($stock->current_stock, 2, '.', ''),

                    number_format($stockValue, 2, '.', ''),

                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

}