<?php

namespace App\Http\Controllers;

use App\Models\StockLedger;
use App\Models\Product;
use App\Models\Store;
use App\Models\Company;
use App\Models\Godown;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockLedgerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:stock-ledger.view',
                only: [
                    'index',
                    'search'
                ]
            ),

            new Middleware(
                'permission:stock-ledger.show',
                only: [
                    'show'
                ]
            ),

            new Middleware(
                'permission:stock-ledger.print',
                only: [
                    'printReport'
                ]
            ),

            new Middleware(
                'permission:stock-ledger.pdf',
                only: [
                    'pdfReport'
                ]
            ),

            new Middleware(
                'permission:stock-ledger.excel',
                only: [
                    'csvReport'
                ]
            ),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = StockLedger::with([
            'product',
            'company',
            'store',
            'godown',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        if ($request->filled('company_id')) {

            $query->where(
                'company_id',
                $request->company_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        */

        if ($request->filled('store_id')) {

            $query->where(
                'store_id',
                $request->store_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Godown
        |--------------------------------------------------------------------------
        */

        if ($request->filled('godown_id')) {

            $query->where(
                'godown_id',
                $request->godown_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        if ($request->filled('product_id')) {

            $query->where(
                'product_id',
                $request->product_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $query->whereDate(
                'transaction_date',
                '>=',
                $request->from_date
            );
        }


        if ($request->filled('to_date')) {

            $query->whereDate(
                'transaction_date',
                '<=',
                $request->to_date
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Ledger
        |--------------------------------------------------------------------------
        */

        $ledgers = $query
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->paginate(50);


        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        $companies = Company::where(
            'status',
            1
        )->get();


        $stores = Store::where(
            'status',
            1
        )->get();


        $godowns = Godown::where(
            'status',
            1
        )->get();


        $products = Product::where(
            'status',
            1
        )->get();


        return view(
            'stock-ledgers.index',
            compact(
                'ledgers',
                'companies',
                'stores',
                'godowns',
                'products'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $product = Product::findOrFail($id);


        $ledgers = StockLedger::with([
            'company',
            'store',
            'godown',
        ])
            ->where(
                'product_id',
                $id
            )
            ->orderBy(
                'transaction_date'
            )
            ->orderBy('id')
            ->get();


        return view(
            'stock-ledgers.show',
            compact(
                'product',
                'ledgers'
            )
        );
    }
}
