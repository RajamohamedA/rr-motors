<?php

namespace App\Http\Controllers;

use App\Models\StockLedger;
use App\Models\Product;
use App\Models\Store;
use App\Models\Company;
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

    public function index(Request $request)
    {
        $query = StockLedger::with([
            'product',
            'company',
            'store'
        ]);

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

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

        $ledgers = $query
            ->orderBy('transaction_date')
            ->paginate(50);

        return view('stock-ledgers.index', [
            'ledgers' => $ledgers,
            'companies' => Company::all(),
            'stores' => Store::all(),
            'products' => Product::all()
        ]);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);

        $ledgers = StockLedger::with([
            'company',
            'store'
        ])
            ->where('product_id', $id)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        return view('stock-ledgers.show', compact(
            'product',
            'ledgers'
        ));
    }
}
