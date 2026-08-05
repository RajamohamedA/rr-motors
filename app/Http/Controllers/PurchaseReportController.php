<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;


class PurchaseReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase-report.view', only: [
                'index',
                'search',
                'show'
            ]),

            new Middleware('permission:purchase-report.print', only: [
                'print',
            ]),

            new Middleware('permission:purchase-report.pdf', only: [
                'pdf',
            ]),

            new Middleware('permission:purchase-report.excel', only: [
                'csvReport',
            ]),
        ];
    }
    public function index()
    {
        session()->forget('purchase_report_filters');

        $suppliers = Supplier::orderBy('supplier_name')->get();

        $products = Product::orderBy('product_name')->get();

        $categories = Category::orderBy('name')->get();

        $brands = Brand::orderBy('brand_name')->get();

        $stores = Store::orderBy('store_name')->get();

        return view('purchase_reports.index', compact(

            'suppliers',

            'products',

            'categories',

            'brands',

            'stores'

        ));
    }


    public function search(Request $request)
    {
        if (
            !$request->filled('from_date') &&
            !$request->filled('to_date') &&
            !$request->filled('purchase_no') &&
            !$request->filled('supplier_id') &&
            !$request->filled('store_id') &&
            !$request->filled('product_id') &&
            !$request->filled('category_id') &&
            !$request->filled('brand_id')
        ) {
            return view('purchase_reports.result', [
                'purchases' => collect(),
                'message' => 'Please select at least one search filter.'
            ]);
        }

        session()->put('purchase_report_filters', $request->except('_token'));

        // Store filters in session
        session()->put(
            'purchase_report_filters',
            $request->except('_token')
        );

        $purchases = $this->getReportData($request);

        return view(
            'purchase_reports.result',
            compact('purchases')
        );
    }


    public function show($id)
    {
        $purchase = Purchase::with([
            'company',
            'supplier',
            'store',
            'items.product',
            'items.unit'
        ])->findOrFail($id);

        return view(
            'purchase_reports.show',
            compact('purchase')
        );
    }

    public function print(Purchase $purchase)
    {
        $purchase->load([
            'company',
            'supplier',
            'store',
            'items.product.unit',
        ]);

        return view('purchase_reports.print', compact('purchase'));
    }

    public function printReport(Request $request)
    {
        if (!session()->has('purchase_report_filters')) {
            return redirect()
                ->route('purchase-reports.index')
                ->with('error', 'Please search the report before printing.');
        }

        $request->merge(session('purchase_report_filters'));

        $purchases = $this->getReportData($request);

        return view('purchase_reports.report_print', compact('purchases'));
    }



    public function pdfReport(Request $request)
    {
        if (!session()->has('purchase_report_filters')) {
            return redirect()
                ->route('purchase-reports.index')
                ->with('error', 'Please search the report before printing.');
        }

        $request->merge(session('purchase_report_filters'));
        
        $purchases = $this->getReportData($request);

        $pdf = Pdf::loadView(
            'purchase_reports.report_pdf',
            compact('purchases')
        )->setPaper('A4', 'landscape');

        return $pdf->stream('PurchaseReport.pdf');
    }

    private function getReportData(Request $request)
    {
        $query = Purchase::with([
            'supplier',
            'store',
            'items.product.brand',
            'items.product.category'
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('purchase_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('purchase_date', '<=', $request->to_date);
        }

        if ($request->filled('purchase_no')) {
            $query->where('purchase_no', 'LIKE', '%' . $request->purchase_no . '%');
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('product_id')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('items.product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        if ($request->filled('brand_id')) {
            $query->whereHas('items.product', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        return $query
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();
    }

    public function csvReport(Request $request)
    {
        if (!session()->has('purchase_report_filters')) {
            return redirect()
                ->route('purchase-reports.index')
                ->with('error', 'Please search the report before exporting.');
        }

        $purchases = $this->getReportData($request);

        $fileName = 'Purchase_Report_' .
            now()->format('Ymd_His') .
            '.csv';

        $headers = [

            'Content-Type' => 'text/csv',

            'Content-Disposition' =>
            'attachment; filename="' . $fileName . '"',

        ];

        $callback = function () use ($purchases) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [

                'Invoice No',

                'Purchase Date',

                'Supplier',

                'Store',

                'Subtotal',

                'Discount',

                'Tax',

                'Grand Total',

                'Status'

            ]);

            foreach ($purchases as $purchase) {

                fputcsv($file, [

                    $purchase->invoice_no,

                    optional($purchase->purchase_date)
                        ? \Carbon\Carbon::parse($purchase->purchase_date)
                        ->format('d-m-Y')
                        : '',

                    $purchase->supplier->supplier_name ?? '',

                    $purchase->store->store_name ?? '',

                    number_format($purchase->subtotal, 2, '.', ''),

                    number_format($purchase->discount_amount, 2, '.', ''),

                    number_format($purchase->tax_amount, 2, '.', ''),

                    number_format($purchase->grand_total, 2, '.', ''),

                    $purchase->status

                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
