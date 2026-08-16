<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PurchaseReportController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware / Permissions
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | View Report
            |--------------------------------------------------------------------------
            */

            new Middleware(
                'permission:purchase-report.view',
                only: [
                    'index',
                    'search',
                    'show',
                ]
            ),


            /*
            |--------------------------------------------------------------------------
            | Print
            |--------------------------------------------------------------------------
            */

            new Middleware(
                'permission:purchase-report.print',
                only: [
                    'print',
                    'printReport',
                ]
            ),


            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */

            new Middleware(
                'permission:purchase-report.pdf',
                only: [
                    'pdfReport',
                ]
            ),


            /*
            |--------------------------------------------------------------------------
            | CSV / Excel
            |--------------------------------------------------------------------------
            */

            new Middleware(
                'permission:purchase-report.excel',
                only: [
                    'csvReport',
                ]
            ),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | Do NOT clear purchase_report_filters here.
        |
        | The filters must remain available for:
        |
        | - Print
        | - PDF
        | - CSV
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $companies = Company::where(
                'status',
                1
            )
                ->orderBy('company_name')
                ->get();
        } else {

            $companies = Company::where(
                'id',
                auth()->user()->company_id
            )
                ->where(
                    'status',
                    1
                )
                ->orderBy('company_name')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        |
        | Only load suppliers belonging to allowed companies.
        |
        */

        if (auth()->user()->is_super_user) {

            $suppliers = Supplier::orderBy(
                'supplier_name'
            )->get();
        } else {

            $suppliers = Supplier::where(
                'company_id',
                auth()->user()->company_id
            )
                ->orderBy('supplier_name')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $products = Product::orderBy(
                'product_name'
            )->get();
        } else {

            $products = Product::where(
                'company_id',
                auth()->user()->company_id
            )
                ->orderBy('product_name')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $categories = Category::orderBy(
                'name'
            )->get();
        } else {

            $categories = Category::where(
                'company_id',
                auth()->user()->company_id
            )
                ->orderBy('name')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $brands = Brand::orderBy(
                'brand_name'
            )->get();
        } else {

            $brands = Brand::where(
                'company_id',
                auth()->user()->company_id
            )
                ->orderBy('brand_name')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Stores
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $stores = Store::where(
                'status',
                1
            )
                ->orderBy('store_name')
                ->get();
        } else {

            $stores = Store::where(
                'company_id',
                auth()->user()->company_id
            )
                ->where(
                    'status',
                    1
                )
                ->orderBy('store_name')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'purchase_reports.index',
            compact(
                'companies',
                'suppliers',
                'products',
                'categories',
                'brands',
                'stores'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Filters
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'company_id' => [
                'nullable',
                'integer',
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'store_id' => [
                'nullable',
                'integer',
            ],

            'purchase_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'product_id' => [
                'nullable',
                'integer',
            ],

            'category_id' => [
                'nullable',
                'integer',
            ],

            'brand_id' => [
                'nullable',
                'integer',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Save Filters In Session
        |--------------------------------------------------------------------------
        */

        session()->put(
            'purchase_report_filters',
            $validated
        );


        /*
        |--------------------------------------------------------------------------
        | Get Report Data
        |--------------------------------------------------------------------------
        */

        $purchases =
            $this->getReportData(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Return Result Partial
        |--------------------------------------------------------------------------
        |
        | This is loaded into #purchaseReportResult
        | through JavaScript.
        |
        */

        return view(
            'purchase_reports.result',
            compact('purchases')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $query = Purchase::with([
            'company',
            'supplier',
            'store',
            'items.product.unit',
        ]);

        if (auth()->user()->is_super_user) {

            $purchase = $query->findOrFail($id);
        } else {

            $purchase = $query
                ->where(
                    'company_id',
                    auth()->user()->company_id
                )
                ->findOrFail($id);
        }

        return view(
            'purchase_reports.show',
            compact('purchase')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT SINGLE PURCHASE
    |--------------------------------------------------------------------------
    */

    public function print($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Query Purchase
        |--------------------------------------------------------------------------
        */

        $query = Purchase::with([

            'company',

            'supplier',

            'store',

            'items.product.unit',

            'items.product.brand',

            'items.product.category',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Company Security
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $purchase =
                $query->findOrFail($id);
        } else {

            $purchase =
                $query
                ->where(
                    'company_id',
                    auth()->user()->company_id
                )
                ->findOrFail($id);
        }


        /*
        |--------------------------------------------------------------------------
        | Print View
        |--------------------------------------------------------------------------
        */

        return view(
            'purchase_reports.print',
            compact('purchase')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT REPORT
    |--------------------------------------------------------------------------
    */

    public function printReport(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Check Session
        |--------------------------------------------------------------------------
        */

        if (
            !session()->has(
                'purchase_report_filters'
            )
        ) {

            return redirect()
                ->route(
                    'purchase-reports.index'
                )
                ->with(
                    'error',
                    'Please search the report before printing.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Saved Filters
        |--------------------------------------------------------------------------
        */

        $filters =
            session(
                'purchase_report_filters',
                []
            );


        /*
        |--------------------------------------------------------------------------
        | Merge Filters
        |--------------------------------------------------------------------------
        */

        $request->merge(
            $filters
        );


        /*
        |--------------------------------------------------------------------------
        | Get Same Report Data
        |--------------------------------------------------------------------------
        */

        $purchases =
            $this->getReportData(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Print View
        |--------------------------------------------------------------------------
        */

        return view(
            'purchase_reports.report_print',
            compact('purchases')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF REPORT
    |--------------------------------------------------------------------------
    */

    public function pdfReport(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Check Session
        |--------------------------------------------------------------------------
        */

        if (
            !session()->has(
                'purchase_report_filters'
            )
        ) {

            return redirect()
                ->route(
                    'purchase-reports.index'
                )
                ->with(
                    'error',
                    'Please search the report before exporting.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Saved Filters
        |--------------------------------------------------------------------------
        */

        $filters =
            session(
                'purchase_report_filters',
                []
            );


        /*
        |--------------------------------------------------------------------------
        | Merge Filters
        |--------------------------------------------------------------------------
        */

        $request->merge(
            $filters
        );


        /*
        |--------------------------------------------------------------------------
        | Get Same Report Data
        |--------------------------------------------------------------------------
        */

        $purchases =
            $this->getReportData(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'purchase_reports.report_pdf',
                compact('purchases')
            )
            ->setPaper(
                'A4',
                'landscape'
            );


        /*
        |--------------------------------------------------------------------------
        | Stream PDF
        |--------------------------------------------------------------------------
        */

        return $pdf->stream(
            'PurchaseReport.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CSV REPORT
    |--------------------------------------------------------------------------
    */

    public function csvReport(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Check Session
        |--------------------------------------------------------------------------
        */

        if (
            !session()->has(
                'purchase_report_filters'
            )
        ) {

            return redirect()
                ->route(
                    'purchase-reports.index'
                )
                ->with(
                    'error',
                    'Please search the report before exporting.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Get Saved Filters
        |--------------------------------------------------------------------------
        */

        $filters =
            session(
                'purchase_report_filters',
                []
            );


        /*
        |--------------------------------------------------------------------------
        | Merge Filters
        |--------------------------------------------------------------------------
        */

        $request->merge(
            $filters
        );


        /*
        |--------------------------------------------------------------------------
        | Get Same Report Data
        |--------------------------------------------------------------------------
        */

        $purchases =
            $this->getReportData(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | File Name
        |--------------------------------------------------------------------------
        */

        $fileName =
            'Purchase_Report_' .
            now()->format('Ymd_His') .
            '.csv';


        /*
        |--------------------------------------------------------------------------
        | Headers
        |--------------------------------------------------------------------------
        */

        $headers = [

            'Content-Type' =>
            'text/csv',

            'Content-Disposition' =>
            'attachment; filename="' .
                $fileName .
                '"',

        ];


        /*
        |--------------------------------------------------------------------------
        | CSV Callback
        |--------------------------------------------------------------------------
        */

        $callback = function () use (
            $purchases
        ) {

            $file =
                fopen(
                    'php://output',
                    'w'
                );


            /*
            |--------------------------------------------------------------------------
            | CSV Header
            |--------------------------------------------------------------------------
            */

            fputcsv(
                $file,
                [

                    'Company',

                    'Purchase Date',

                    'Purchase No',

                    'Supplier',

                    'Store',

                    'Items',

                    'Subtotal',

                    'Discount',

                    'Tax',

                    'Grand Total',

                    'Status',

                ]
            );


            /*
            |--------------------------------------------------------------------------
            | CSV Rows
            |--------------------------------------------------------------------------
            */

            foreach (
                $purchases
                as $purchase
            ) {

                fputcsv(
                    $file,
                    [

                        $purchase
                            ->company
                            ->company_name
                            ?? '',

                        $purchase->purchase_date
                            ? \Carbon\Carbon::parse(
                                $purchase->purchase_date
                            )->format('d-m-Y')
                            : '',

                        $purchase->purchase_no
                            ?? '',

                        $purchase
                            ->supplier
                            ->supplier_name
                            ?? '',

                        $purchase
                            ->store
                            ->store_name
                            ?? '',

                        $purchase
                            ->items
                            ->count(),

                        number_format(
                            (float) (
                                $purchase->subtotal
                                ?? 0
                            ),
                            2,
                            '.',
                            ''
                        ),

                        number_format(
                            (float) (
                                $purchase
                                ->discount_amount
                                ?? 0
                            ),
                            2,
                            '.',
                            ''
                        ),

                        number_format(
                            (float) (
                                $purchase
                                ->tax_amount
                                ?? 0
                            ),
                            2,
                            '.',
                            ''
                        ),

                        number_format(
                            (float) (
                                $purchase
                                ->grand_total
                                ?? 0
                            ),
                            2,
                            '.',
                            ''
                        ),

                        $purchase->status
                            ?? '',

                    ]
                );
            }


            fclose($file);
        };


        /*
        |--------------------------------------------------------------------------
        | Return CSV
        |--------------------------------------------------------------------------
        */

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET REPORT DATA
    |--------------------------------------------------------------------------
    */

    private function getReportData(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = Purchase::with([

            'company',

            'supplier',

            'store',

            'items.product.brand',

            'items.product.category',

        ]);


        /*
        |--------------------------------------------------------------------------
        | COMPANY SECURITY
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->is_super_user
        ) {

            /*
            |--------------------------------------------------------------------------
            | Super Admin
            |--------------------------------------------------------------------------
            |
            | No company selected = All Companies
            |
            */

            if (
                $request->filled(
                    'company_id'
                )
            ) {

                $query->where(
                    'company_id',
                    $request->company_id
                );
            }
        } else {

            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            |
            | NEVER trust company_id from browser.
            |
            */

            $query->where(
                'company_id',
                auth()->user()->company_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FROM DATE
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'from_date'
            )
        ) {

            $query->whereDate(
                'purchase_date',
                '>=',
                $request->from_date
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TO DATE
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'to_date'
            )
        ) {

            $query->whereDate(
                'purchase_date',
                '<=',
                $request->to_date
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PURCHASE NUMBER
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'purchase_no'
            )
        ) {

            $query->where(
                'purchase_no',
                'LIKE',
                '%' .
                    $request->purchase_no .
                    '%'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'supplier_id'
            )
        ) {

            $query->where(
                'supplier_id',
                $request->supplier_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STORE
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'store_id'
            )
        ) {

            $query->where(
                'store_id',
                $request->store_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCT
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'product_id'
            )
        ) {

            $query->whereHas(
                'items',
                function ($q) use (
                    $request
                ) {

                    $q->where(
                        'product_id',
                        $request->product_id
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CATEGORY
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'category_id'
            )
        ) {

            $query->whereHas(
                'items.product',
                function ($q) use (
                    $request
                ) {

                    $q->where(
                        'category_id',
                        $request->category_id
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | BRAND
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled(
                'brand_id'
            )
        ) {

            $query->whereHas(
                'items.product',
                function ($q) use (
                    $request
                ) {

                    $q->where(
                        'brand_id',
                        $request->brand_id
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Return Data
        |--------------------------------------------------------------------------
        */

        return $query
            ->orderByDesc(
                'purchase_date'
            )
            ->orderByDesc(
                'id'
            )
            ->get();
    }
}
