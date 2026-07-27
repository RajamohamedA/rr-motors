<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Sales::with([
            'customer',
            'company',
            'store'
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'LIKE', '%' . $request->invoice_no . '%');
        }

        $sales = $query
            ->orderByDesc('invoice_date')
            ->paginate(20);

        $summary = [
            'subtotal' => $query->sum('sub_total'),
            'gst'      => $query->sum('gst_total'),
            'discount' => $query->sum('discount'),
            'grand'    => $query->sum('grand_total'),
            'paid'     => $query->sum('paid_amount'),
            'balance'  => $query->sum('balance_amount'),
        ];

        $customers = Customer::where('status', 1)->get();
        $companies = Company::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();

        return view('reports.sales.index', compact(
            'sales',
            'summary',
            'customers',
            'companies',
            'stores'
        ));


        return view(
            'reports.sales.index',
            compact('sales', 'summary')
        );
    }

    public function print(Request $request)
    {
        $query = Sales::with([
            'customer',
            'company',
            'store'
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'LIKE', '%' . $request->invoice_no . '%');
        }

        $sales = $query
            ->orderBy('invoice_date')
            ->get();

        $summary = [
            'subtotal' => $sales->sum('sub_total'),
            'gst' => $sales->sum('gst_total'),
            'discount' => $sales->sum('discount'),
            'grand' => $sales->sum('grand_total'),
            'paid' => $sales->sum('paid_amount'),
            'balance' => $sales->sum('balance_amount'),
        ];

        return view(
            'reports.sales.print',
            compact('sales', 'summary')
        );
    }

    public function pdf(Request $request)
    {
        $query = Sales::with([
            'customer',
            'company',
            'store'
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'LIKE', '%' . $request->invoice_no . '%');
        }

        $sales = $query->orderBy('invoice_date')->get();

        $summary = [
            'subtotal' => $sales->sum('sub_total'),
            'gst'      => $sales->sum('gst_total'),
            'discount' => $sales->sum('discount'),
            'grand'    => $sales->sum('grand_total'),
            'paid'     => $sales->sum('paid_amount'),
            'balance'  => $sales->sum('balance_amount'),
        ];

        $pdf = Pdf::loadView(
            'reports.sales.pdf',
            compact('sales', 'summary')
        );

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('SalesReport.pdf');
    }

    public function csv(Request $request)
    {
        $query = Sales::with([
            'customer',
            'company',
            'store'
        ]);

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('invoice_no')) {
            $query->where('invoice_no', 'LIKE', '%' . $request->invoice_no . '%');
        }

        $sales = $query->orderBy('invoice_date')->get();

        $fileName = 'SalesReport_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate',
            'Expires' => '0',
        ];

        $callback = function () use ($sales) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date',
                'Invoice No',
                'Customer',
                'Company',
                'Store',
                'Grand Total',
                'Paid',
                'Balance'
            ]);

            foreach ($sales as $sale) {

                fputcsv($file, [

                    $sale->invoice_date,

                    $sale->invoice_no,

                    optional($sale->customer)->customer_name,

                    optional($sale->company)->company_name,

                    optional($sale->store)->store_name,

                    $sale->grand_total,

                    $sale->paid_amount,

                    $sale->balance_amount,

                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
