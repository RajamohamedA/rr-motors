<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Godown;
use App\Models\Mechanic;
use App\Models\MechanicCommission;
use App\Models\Product;
use App\Models\SaleItems;
use App\Models\Sales;
use App\Models\SalePayment;
use App\Models\StockLedger;
use App\Models\Store;
use App\Services\StockService;
use App\Services\CustomerLedgerService;
use App\Support\AppResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;


class SalesController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:sales.view',
                only: [
                    'index',
                    'show',
                    'print',
                    'pdf',
                    'companyData',
                ]
            ),

            new Middleware(
                'permission:sales.create',
                only: [
                    'create',
                    'store',
                    'quickTransfer',
                    'quickCustomer',
                    'quickMechanic',
                ]
            ),

            new Middleware(
                'permission:sales.create|sales.edit',
                only: [
                    'search',
                    'searchCustomers',
                    'checkStock',
                ]
            ),

            new Middleware('permission:sales.edit', only: ['edit', 'update']),
            new Middleware('permission:sales.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $filters = [
            'search' => trim((string) $request->input('search', '')),

            /*
            |--------------------------------------------------------------------------
            | Quick Date Filter
            |--------------------------------------------------------------------------
            |
            | today | yesterday | this_week | this_month | all
            |
            */

            'quick_filter' => $request->input(
                'quick_filter',
                'today'
            ),

            'customer_id' => $request->input('customer_id'),
            'mechanic_id' => $request->input('mechanic_id'),
            'sale_source' => $request->input('sale_source'),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = Sales::query()
            ->with([
                'customer:id,customer_name',
                'mechanic:id,mechanic_name',
            ])
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->when(
                empty($filters['date_from']) && empty($filters['date_to']),
                function (Builder $query) use ($filters) {

                    switch ($filters['quick_filter']) {

                        case 'yesterday':

                            $query->whereDate(
                                'invoice_date',
                                now()->subDay()->toDateString()
                            );

                            break;

                        case 'this_week':

                            $query->whereBetween(
                                'invoice_date',
                                [
                                    now()->startOfWeek()->toDateString(),
                                    now()->endOfWeek()->toDateString(),
                                ]
                            );

                            break;

                        case 'this_month':

                            $query->whereBetween(
                                'invoice_date',
                                [
                                    now()->startOfMonth()->toDateString(),
                                    now()->endOfMonth()->toDateString(),
                                ]
                            );

                            break;

                        case 'all':

                            break;

                        case 'today':

                        default:

                            $query->whereDate(
                                'invoice_date',
                                now()->toDateString()
                            );

                            break;
                    }
                }
            )
            ->when($filters['search'], function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested
                        ->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn(Builder $q) =>
                        $q->where('customer_name', 'like', "%{$search}%"))
                        ->orWhereHas('mechanic', fn(Builder $q) =>
                        $q->where('mechanic_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['customer_id'], fn(Builder $q, $id) =>
            $q->where('customer_id', (int) $id))
            ->when($filters['mechanic_id'], fn(Builder $q, $id) =>
            $q->where('mechanic_id', (int) $id))
            ->when($filters['sale_source'], fn(Builder $q, $source) =>
            $q->where('sale_source', $source))
            ->when($filters['status'], fn(Builder $q, $status) =>
            $q->where('status', $status))
            ->when($filters['date_from'], fn(Builder $q, $date) =>
            $q->whereDate('invoice_date', '>=', $date))
            ->when($filters['date_to'], fn(Builder $q, $date) =>
            $q->whereDate('invoice_date', '<=', $date));

        $sales = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Sales Summary
        |--------------------------------------------------------------------------
        */

        $summaryQuery = Sales::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', 'Completed');


        $todayQuery =
            (clone $summaryQuery)
            ->whereDate(
                'invoice_date',
                now()->toDateString()
            );


        $yesterdayQuery =
            (clone $summaryQuery)
            ->whereDate(
                'invoice_date',
                now()->subDay()->toDateString()
            );


        $summary = [

            'sales_count' => (clone $summaryQuery)->count(),

            'sales_value' =>
            (float) (clone $summaryQuery)
                ->sum('grand_total'),

            'receivable' =>
            (float) (clone $summaryQuery)
                ->sum('balance_amount'),

            'mechanic_commission' =>
            (float) (clone $summaryQuery)
                ->sum('commission_amount'),


            /*
            |--------------------------------------------------------------------------
            | Today
            |--------------------------------------------------------------------------
            */

            'today_sales' =>
            (float) (clone $todayQuery)
                ->sum('grand_total'),

            'today_invoices' => (clone $todayQuery)->count(),

            'today_paid' =>
            (float) (clone $todayQuery)
                ->sum('paid_amount'),

            'today_balance' =>
            (float) (clone $todayQuery)
                ->sum('balance_amount'),


            /*
            |--------------------------------------------------------------------------
            | Yesterday
            |--------------------------------------------------------------------------
            */

            'yesterday_sales' =>
            (float) (clone $yesterdayQuery)
                ->sum('grand_total'),

            'yesterday_invoices' => (clone $yesterdayQuery)->count(),

            'yesterday_paid' =>
            (float) (clone $yesterdayQuery)
                ->sum('paid_amount'),

            'yesterday_balance' =>
            (float) (clone $yesterdayQuery)
                ->sum('balance_amount'),
        ];

        return Inertia::render('Sales/Index', [
            'sales' => $sales,
            'filters' => $filters,
            'summary' => $summary,
            'customers' => $this->customersForStore($companyId, $storeId),
            'mechanics' => $this->mechanicsForStore($companyId, $storeId),
        ]);
    }

    public function create(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        return Inertia::render('Sales/Create', [
            'invoiceNo' => $this->previewInvoiceNo($storeId),
            'customers' => $this->initialCustomersForStore(
                $companyId,
                $storeId
            ),
            'mechanics' => $this->mechanicsForStore($companyId, $storeId),
            'accounts' => $this->paymentAccountsForStore(
                $companyId,
                $storeId
            ),
            'godowns' => Godown::query()
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->where('status', true)
                ->orderBy('godown_name')
                ->get(['id', 'godown_name', 'godown_code']),
        ]);
    }

    public function searchCustomers(Request $request)
    {
        $this->authorizeLookup($request);

        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'search' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
        ]);

        $search = trim($validated['search']);

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas(
                'stores',
                fn(Builder $query) => $query->where(
                    'stores.id',
                    $storeId
                )
            )
            ->where(function (Builder $query) use ($search) {
                $query
                    ->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%");
            })
            ->orderByRaw('is_system_customer DESC')
            ->orderBy('customer_name')
            ->limit(20)
            ->get([
                'id',
                'customer_code',
                'customer_name',
                'mobile',
                'phone',
                'gst_number',
                'customer_type',
                'is_system_customer',
            ]);

        return response()->json([
            'success' => true,
            'customers' => $customers,
        ]);
    }

    public function search(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Permission
        |--------------------------------------------------------------------------
        */

        $this->authorizeLookup(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'search' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Active Company / Store
        |--------------------------------------------------------------------------
        */

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $search =
            trim(
                $validated['search']
            );


        /*
        |--------------------------------------------------------------------------
        | Product Search
        |--------------------------------------------------------------------------
        */

        $products =
            Product::query()

            ->with([
                'unit',
                'brand',
                'category',
            ])

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'status',
                true
            )

            ->where(
                function (
                    Builder $query
                ) use (
                    $search
                ) {

                    $query
                        ->where(
                            'product_name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'product_code',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'barcode',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'part_number',
                            'like',
                            "%{$search}%"
                        );
                }
            )

            ->orderBy(
                'product_name'
            )

            ->limit(
                20
            )

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Attach Shop Stock
        |--------------------------------------------------------------------------
        */

        $products->transform(
            function (
                Product $product
            ) use (
                $companyId,
                $storeId
            ) {

                $shopStock =
                    $this->currentStock(
                        $companyId,
                        $storeId,
                        null,
                        $product->id
                    );


                $product->setAttribute(
                    'shop_stock',
                    $shopStock
                );


                return $product;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | JSON Response
        |--------------------------------------------------------------------------
        */

        return response()->json(
            $products
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Check Stock / Stock Assist
    |--------------------------------------------------------------------------
    |
    | Returns Shop stock + every active Godown stock for the selected product.
    | The frontend can use suggested_transfer_qty as the default editable value.
    |
    */

    public function checkStock(Request $request)
    {
        $this->authorizeLookup($request);

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ]);


        [$companyId, $storeId] =
            $this->context($request);


        $product =
            Product::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->findOrFail(
                $validated['product_id']
            );


        $requiredQty =
            (float) $validated['quantity'];


        $shopStock =
            $this->currentStock(
                $companyId,
                $storeId,
                null,
                $product->id
            );


        $shortage =
            max(
                0,
                round(
                    $requiredQty - $shopStock,
                    2
                )
            );


        $godowns =
            Godown::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('godown_name')
            ->get([
                'id',
                'godown_name',
                'godown_code',
            ])
            ->map(
                function (
                    Godown $godown
                ) use (
                    $companyId,
                    $storeId,
                    $product,
                    $shortage
                ) {

                    $available =
                        $this->currentStock(
                            $companyId,
                            $storeId,
                            $godown->id,
                            $product->id
                        );


                    return [
                        'id' =>
                        $godown->id,

                        'godown_name' =>
                        $godown->godown_name,

                        'godown_code' =>
                        $godown->godown_code,

                        'available_stock' =>
                        $available,

                        /*
                            |--------------------------------------------------------------------------
                            | Editable Default
                            |--------------------------------------------------------------------------
                            |
                            | Frontend starts with this quantity, but the cashier
                            | can change it before transferring.
                            |
                            */

                        'suggested_transfer_qty' =>
                        min(
                            $shortage,
                            $available
                        ),
                    ];
                }
            )
            ->values();


        $godownStock =
            round(
                (float) $godowns->sum(
                    'available_stock'
                ),
                2
            );


        $totalStock =
            round(
                $shopStock
                    +
                    $godownStock,
                2
            );


        return response()->json([
            'success' =>
            true,

            'product' => [
                'id' =>
                $product->id,

                'product_name' =>
                $product->product_name,

                'product_code' =>
                $product->product_code,
            ],

            'required_qty' =>
            $requiredQty,

            'shop_stock' =>
            $shopStock,

            'shortage_qty' =>
            $shortage,

            'godown_stock' =>
            $godownStock,

            'total_stock' =>
            $totalStock,

            'shop_has_enough' =>
            $shopStock >= $requiredQty,

            'total_has_enough' =>
            $totalStock >= $requiredQty,

            'godowns' =>
            $godowns,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Quick Transfer Godown -> Shop
    |--------------------------------------------------------------------------
    |
    | transfer_qty is intentionally editable from the Sales page.
    | Company, Store and destination are NEVER trusted from the browser.
    |
    */

    public function quickTransfer(
        Request $request,
        StockService $stockService
    ) {
        $this->authorizeLookup($request);

        $validated = $request->validate([
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'from_godown_id' => [
                'required',
                'integer',
                'exists:godowns,id',
            ],

            'transfer_qty' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ]);


        [$companyId, $storeId] =
            $this->context($request);


        try {

            $result =
                DB::transaction(
                    function () use (
                        $validated,
                        $companyId,
                        $storeId,
                        $stockService
                    ) {

                        $product =
                            Product::query()
                            ->where('company_id', $companyId)
                            ->where('store_id', $storeId)
                            ->where('status', true)
                            ->lockForUpdate()
                            ->findOrFail(
                                $validated['product_id']
                            );


                        $godown =
                            Godown::query()
                            ->where('company_id', $companyId)
                            ->where('store_id', $storeId)
                            ->where('status', true)
                            ->lockForUpdate()
                            ->findOrFail(
                                $validated['from_godown_id']
                            );


                        $transferQty =
                            round(
                                (float) $validated['transfer_qty'],
                                2
                            );


                        $godownStock =
                            $this->currentStock(
                                $companyId,
                                $storeId,
                                $godown->id,
                                $product->id
                            );


                        if (
                            $transferQty
                            >
                            $godownStock
                        ) {

                            throw ValidationException::withMessages([
                                'transfer_qty' =>
                                'Transfer quantity cannot exceed Godown stock. Available: '
                                    .
                                    number_format(
                                        $godownStock,
                                        2
                                    ),
                            ]);
                        }


                        $referenceNo =
                            'QTR-'
                            .
                            now()->format(
                                'YmdHis'
                            )
                            .
                            '-'
                            .
                            $product->id;


                        /*
                        |--------------------------------------------------------------------------
                        | Godown OUT
                        |--------------------------------------------------------------------------
                        */

                        $stockService->decreaseStock(
                            $product,
                            $transferQty,
                            'QUICK_TRANSFER_OUT',
                            $companyId,
                            $storeId,
                            $godown->id,
                            null,
                            $referenceNo,
                            0,
                            'Quick transfer to Shop from '
                                .
                                $godown->godown_name
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | Shop IN
                        |--------------------------------------------------------------------------
                        */

                        $stockService->increaseStock(
                            $product,
                            $transferQty,
                            'QUICK_TRANSFER_IN',
                            $companyId,
                            $storeId,
                            null,
                            null,
                            $referenceNo,
                            0,
                            'Quick transfer from '
                                .
                                $godown->godown_name
                        );


                        $shopStock =
                            $this->currentStock(
                                $companyId,
                                $storeId,
                                null,
                                $product->id
                            );


                        $remainingGodownStock =
                            $this->currentStock(
                                $companyId,
                                $storeId,
                                $godown->id,
                                $product->id
                            );


                        return [
                            'product_id' =>
                            $product->id,

                            'product_name' =>
                            $product->product_name,

                            'from_godown_id' =>
                            $godown->id,

                            'from_godown_name' =>
                            $godown->godown_name,

                            'transferred_qty' =>
                            $transferQty,

                            'shop_stock' =>
                            $shopStock,

                            'godown_stock' =>
                            $remainingGodownStock,

                            'reference_no' =>
                            $referenceNo,
                        ];
                    }
                );


            return response()->json([
                'success' =>
                true,

                'message' =>
                'Stock transferred to Shop successfully.',

                'transfer' =>
                $result,
            ]);
        } catch (
            ValidationException $exception
        ) {

            throw $exception;
        } catch (
            Throwable $exception
        ) {

            report(
                $exception
            );


            return response()->json([
                'success' =>
                false,

                'message' =>
                $exception->getMessage()
                    ?: 'Unable to transfer stock.',
            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Company Data - Backward Compatibility
    |--------------------------------------------------------------------------
    |
    | The URL may contain a company ID, but access remains restricted to the
    | active business context. This prevents cross-company data exposure.
    |
    */

    public function companyData(
        Request $request,
        $company
    ) {
        [$companyId, $storeId] =
            $this->context($request);


        abort_unless(
            (int) $company
                ===
                $companyId,
            404
        );


        $store =
            Store::query()
            ->where('company_id', $companyId)
            ->whereKey($storeId)
            ->where('status', true)
            ->firstOrFail();


        return response()->json([
            'company_id' =>
            $companyId,

            'store' => [
                'id' =>
                $store->id,

                'store_name' =>
                $store->store_name,

                'store_code' =>
                $store->store_code,
            ],

            'customers' =>
            $this->customersForStore(
                $companyId,
                $storeId
            ),

            'mechanics' =>
            $this->mechanicsForStore(
                $companyId,
                $storeId
            ),

            'godowns' =>
            Godown::query()
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->where('status', true)
                ->orderBy('godown_name')
                ->get([
                    'id',
                    'godown_name',
                    'godown_code',
                ]),
        ]);
    }


    public function quickCustomer(
        Request $request
    ) {
        /*
        |--------------------------------------------------------------------------
        | Active Company and Branch
        |--------------------------------------------------------------------------
        */

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'customer_name' => [
                'required',
                'string',
                'max:150',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'gst_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize Values
        |--------------------------------------------------------------------------
        */

        $customerName =
            trim(
                $validated['customer_name']
            );

        $mobile =
            filled(
                $validated['mobile'] ?? null
            )
            ? trim(
                $validated['mobile']
            )
            : null;

        $email =
            filled(
                $validated['email'] ?? null
            )
            ? strtolower(
                trim(
                    $validated['email']
                )
            )
            : null;

        $gstNumber =
            filled(
                $validated['gst_number'] ?? null
            )
            ? strtoupper(
                trim(
                    $validated['gst_number']
                )
            )
            : null;

        $billingAddress =
            filled(
                $validated['address'] ?? null
            )
            ? trim(
                $validated['address']
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Existing Customer by Mobile
        |--------------------------------------------------------------------------
        */

        if ($mobile !== null) {
            $existingCustomer =
                Customer::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'mobile',
                    $mobile
                )
                ->first();

            if ($existingCustomer) {
                $existingCustomer->attachToStore(
                    $storeId
                );

                return response()->json([
                    'success' => true,

                    'existing' => true,

                    'message' =>
                    'Existing customer added to this branch.',

                    'customer' => [
                        'id' =>
                        $existingCustomer->id,

                        'customer_code' =>
                        $existingCustomer->customer_code,

                        'customer_name' =>
                        $existingCustomer->customer_name,

                        'mobile' =>
                        $existingCustomer->mobile,
                    ],
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Customer by GST Number
        |--------------------------------------------------------------------------
        */

        if ($gstNumber !== null) {
            $existingCustomer =
                Customer::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'gst_number',
                    $gstNumber
                )
                ->first();

            if ($existingCustomer) {
                $existingCustomer->attachToStore(
                    $storeId
                );

                return response()->json([
                    'success' => true,

                    'existing' => true,

                    'message' =>
                    'Existing GST customer added to this branch.',

                    'customer' => [
                        'id' =>
                        $existingCustomer->id,

                        'customer_code' =>
                        $existingCustomer->customer_code,

                        'customer_name' =>
                        $existingCustomer->customer_name,

                        'mobile' =>
                        $existingCustomer->mobile,
                    ],
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Create Customer
        |--------------------------------------------------------------------------
        */

        try {
            $customer =
                DB::transaction(
                    function () use (
                        $companyId,
                        $storeId,
                        $customerName,
                        $mobile,
                        $email,
                        $gstNumber,
                        $billingAddress
                    ) {
                        /*
                        |--------------------------------------------------------------
                        | Generate Customer Code
                        |--------------------------------------------------------------
                        */

                        $lastCustomer =
                            Customer::query()
                            ->where(
                                'company_id',
                                $companyId
                            )
                            ->whereNotNull(
                                'customer_code'
                            )
                            ->lockForUpdate()
                            ->latest('id')
                            ->first();

                        $lastNumber =
                            $lastCustomer
                            ? (int) preg_replace(
                                '/\D/',
                                '',
                                (string)
                                $lastCustomer->customer_code
                            )
                            : 0;

                        $customerCode =
                            'CUS'
                            .
                            str_pad(
                                (string) ($lastNumber + 1),
                                6,
                                '0',
                                STR_PAD_LEFT
                            );


                        /*
                        |--------------------------------------------------------------
                        | Save Customer
                        |--------------------------------------------------------------
                        */

                        $customer =
                            Customer::create([
                                'company_id' =>
                                $companyId,

                                'customer_code' =>
                                $customerCode,

                                'customer_name' =>
                                $customerName,

                                'customer_type' =>
                                'regular',

                                'is_system_customer' =>
                                false,

                                'mobile' =>
                                $mobile,

                                'phone' =>
                                $mobile,

                                'email' =>
                                $email,

                                'billing_address' =>
                                $billingAddress,

                                'shipping_address' =>
                                $billingAddress,

                                'gst_number' =>
                                $gstNumber,

                                'credit_limit' =>
                                0,

                                'credit_days' =>
                                0,

                                'opening_balance' =>
                                0,

                                'balance_type' =>
                                'Dr',

                                'status' =>
                                true,
                            ]);


                        /*
                        |--------------------------------------------------------------
                        | Attach to Current Branch
                        |--------------------------------------------------------------
                        */

                        $customer->attachToStore(
                            $storeId
                        );

                        return $customer;
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | Success Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,

                'existing' => false,

                'message' =>
                'Customer created successfully.',

                'customer' => [
                    'id' =>
                    $customer->id,

                    'customer_code' =>
                    $customer->customer_code,

                    'customer_name' =>
                    $customer->customer_name,

                    'mobile' =>
                    $customer->mobile,
                ],
            ]);
        } catch (Throwable $exception) {
            report(
                $exception
            );

            return response()->json([
                'success' => false,

                'message' =>
                $exception->getMessage()
                    ?: 'Unable to create customer.',
            ], 500);
        }
    }

    public function quickMechanic(Request $request)
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'mechanic_name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        if (filled($validated['mobile'] ?? null)) {
            $existing = Mechanic::query()
                ->where('company_id', $companyId)
                ->where('mobile', trim($validated['mobile']))
                ->first();

            if ($existing) {
                $existing->stores()->syncWithoutDetaching([$storeId]);

                return response()->json([
                    'success' => true,
                    'existing' => true,
                    'message' => 'Existing mechanic added to this branch.',
                    'mechanic' => [
                        'id' => $existing->id,
                        'mechanic_code' => $existing->mechanic_code,
                        'mechanic_name' => $existing->mechanic_name,
                        'mobile' => $existing->mobile,
                    ],
                ]);
            }
        }

        $mechanic = DB::transaction(function () use ($validated, $companyId, $storeId) {
            $mechanic = Mechanic::create([
                'company_id' => $companyId,
                'mechanic_code' => $this->generateMechanicCode($companyId),
                'mechanic_name' => trim($validated['mechanic_name']),
                'mobile' => filled($validated['mobile'] ?? null)
                    ? trim($validated['mobile'])
                    : null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'status' => true,
            ]);

            $mechanic->stores()->syncWithoutDetaching([$storeId]);

            return $mechanic;
        });

        return response()->json([
            'success' => true,
            'existing' => false,
            'message' => 'Mechanic created successfully.',
            'mechanic' => [
                'id' => $mechanic->id,
                'mechanic_code' => $mechanic->mechanic_code,
                'mechanic_name' => $mechanic->mechanic_name,
                'mobile' => $mechanic->mobile,
            ],
        ]);
    }

    public function store(
        Request $request,
        StockService $stockService,
        CustomerLedgerService $customerLedgerService
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $validated = $this->validateSaleRequest($request);
        $userId = (int) $request->user()->id;

        try {
            $sale = DB::transaction(function () use (
                $validated,
                $companyId,
                $storeId,
                $stockService,
                $customerLedgerService,
                $userId
            ) {
                $customer = $this->validateCustomer(
                    (int) $validated['customer_id'],
                    $companyId,
                    $storeId
                );

                $customerLedgerService->ensureOpeningBalance(
                    $customer,
                    $companyId,
                    $storeId,
                    $userId
                );

                $lines = $this->buildSaleLines(
                    $validated,
                    $companyId,
                    $storeId
                );

                if (count($lines) === 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Add at least one product to the sale.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                |
                | sub_total is the taxable value after per-item discounts.
                | The legacy sales.discount column is no longer used and is saved as 0.
                |
                */

                $subTotal =
                    round(
                        collect($lines)->sum(
                            'taxable_amount'
                        ),
                        2
                    );


                $itemDiscountTotal =
                    round(
                        collect($lines)->sum(
                            'discount'
                        ),
                        2
                    );


                $gstTotal =
                    round(
                        collect($lines)->sum(
                            'gst_amount'
                        ),
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Mechanic Commission Base
                |--------------------------------------------------------------------------
                |
                | Commission is calculated on taxable value after item discounts,
                | excluding GST and excluding final invoice discount.
                |
                */

                $commissionBase =
                    max(
                        0,
                        $subTotal
                    );


                $commission =
                    $this->calculateCommission(
                        $validated,
                        $companyId,
                        $storeId,
                        $commissionBase
                    );


                /*
                |--------------------------------------------------------------------------
                | Gross Total Before Final Discount
                |--------------------------------------------------------------------------
                */

                $grossTotal =
                    max(
                        0,
                        round(
                            $subTotal
                                +
                                $gstTotal,
                            2
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | Final Discount
                |--------------------------------------------------------------------------
                |
                | This is the final negotiated discount applied after GST.
                |
                */

                $finalDiscount = round(
                    (float) ($validated['final_discount'] ?? 0),
                    2
                );

                if ($finalDiscount > $grossTotal) {
                    throw ValidationException::withMessages([
                        'final_discount' =>
                        'Final discount cannot exceed the invoice amount.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Round Off
                |--------------------------------------------------------------------------
                |
                | Example:
                | 499.52 -> 500.00 => +0.48
                | 499.49 -> 499.00 => -0.49
                |
                */

                $beforeRoundOff = max(
                    0,
                    round(
                        $grossTotal - $finalDiscount,
                        2
                    )
                );

                $grandTotal = round(
                    $beforeRoundOff,
                    0
                );

                $roundOff = round(
                    $grandTotal - $beforeRoundOff,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | Payment
                |--------------------------------------------------------------------------
                */

                $paymentLines = $this->buildPaymentLines(
                    $validated,
                    $companyId,
                    $storeId
                );

                $directPaidAmount = $this->paymentTotal(
                    $paymentLines,
                    (float) ($validated['paid_amount'] ?? 0)
                );

                if ($directPaidAmount > $grandTotal) {
                    throw ValidationException::withMessages([
                        'paid_amount' =>
                        'Paid amount cannot exceed the net invoice amount.',
                    ]);
                }

                $availableAdvance = max(
                    0,
                    -$customerLedgerService->currentBalance(
                        $companyId,
                        $storeId,
                        $customer->id
                    )
                );

                $advanceUsed = round(
                    min(
                        $availableAdvance,
                        max(0, $grandTotal - $directPaidAmount)
                    ),
                    2
                );

                $paidAmount = round(
                    $directPaidAmount + $advanceUsed,
                    2
                );

                $balanceAmount = round(
                    $grandTotal - $paidAmount,
                    2
                );

                $paymentStatus = $this->paymentStatus(
                    $paidAmount,
                    $balanceAmount
                );

                $sale = Sales::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'customer_id' => $customer->id,

                    'sale_source' => $commission['sale_source'],
                    'mechanic_id' => $commission['mechanic_id'],
                    'commission_type' => $commission['commission_type'],
                    'commission_percent' => $commission['commission_percent'],
                    'commission_amount' => $commission['commission_amount'],
                    'commission_status' => 'Pending',

                    'invoice_no' => $this->generateInvoiceNo($storeId),
                    'invoice_date' => $validated['invoice_date'],
                    'billing_type' => $validated['billing_type'] ?? 'gst',

                    'sub_total' => $subTotal,

                    /*
                    |--------------------------------------------------------------------------
                    | Legacy Header Discount
                    |--------------------------------------------------------------------------
                    |
                    | New Sales UI has no sale-level discount.
                    |
                    */

                    'discount' => 0,

                    'gst_total' => $gstTotal,
                    'final_discount' => $finalDiscount,
                    'round_off' => $roundOff,
                    'grand_total' => $grandTotal,
                    'advance_used' => $advanceUsed,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'payment_status' => $paymentStatus,

                    'remarks' => $validated['remarks'] ?? null,
                    'status' => 'Completed',
                ]);

                foreach ($lines as $line) {
                    $stockSnapshot = $stockService->decreaseStock(
                        $line['product'],
                        $line['stock_quantity'],
                        'SALE',
                        $companyId,
                        $storeId,
                        $line['godown_id'],
                        $sale->id,
                        $sale->invoice_no,
                        0,
                        'Sales Invoice'
                    );

                    $costTotal = round(
                        (float) $stockSnapshot['cost_total'],
                        2
                    );

                    SaleItems::create([
                        'sale_id' => $sale->id,
                        'product_id' => $line['product']->id,
                        'godown_id' => $line['godown_id'],
                        'quantity' => $line['quantity'],
                        'free_quantity' => $line['free_quantity'],
                        'sale_price' => $line['sale_price'],
                        'unit_cost' => $stockSnapshot['unit_cost'],
                        'cost_total' => $costTotal,
                        'cost_source' => $stockSnapshot['cost_source'],
                        'discount_type' => $line['discount_type'],
                        'discount_value' => $line['discount_value'],
                        'discount' => $line['discount'],
                        'gst_percent' => $line['gst_percent'],
                        'gst_amount' => $line['gst_amount'],
                        'line_total' => $line['line_total'],
                        'net_sale_amount' => $line['taxable_amount'],
                        'gross_profit' => round(
                            $line['taxable_amount'] - $costTotal,
                            2
                        ),
                    ]);
                }

                $this->recordSalePayments(
                    $sale,
                    $paymentLines,
                    $directPaidAmount,
                    $companyId,
                    $storeId,
                    $userId
                );

                $customerLedgerService->postSale(
                    $sale,
                    $userId
                );

                $customerLedgerService->syncSaleCollection(
                    $sale,
                    $directPaidAmount,
                    $userId
                );

                if (
                    $commission['sale_source'] === 'mechanic'
                    && $commission['mechanic_id']
                    && $commission['commission_amount'] > 0
                ) {
                    MechanicCommission::create([
                        'company_id' => $companyId,
                        'store_id' => $storeId,
                        'mechanic_id' => $commission['mechanic_id'],
                        'sale_id' => $sale->id,
                        'commission_type' => $commission['commission_type'],
                        'commission_percent' => $commission['commission_type'] === 'percentage'
                            ? $commission['commission_percent']
                            : null,
                        'commission_base' => $commissionBase,
                        'commission_amount' => $commission['commission_amount'],
                        'paid_amount' => 0,
                        'balance_amount' => $commission['commission_amount'],
                        'status' => 'Pending',
                        'remarks' => 'Commission generated from sale ' . $sale->invoice_no,
                    ]);
                }

                return $sale;
            });

            return AppResponse::successRoute(
                'sales.show',
                'Sale created successfully.',
                $sale
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return AppResponse::error(
                $exception->getMessage() ?: 'Unable to create sale.',
                true
            );
        }
    }

    public function show(Request $request, Sales $sale): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureSaleContext($sale, $companyId, $storeId);

        $sale->load([
            'company',
            'store',
            'customer',
            'mechanic',
            'mechanicCommission',
            'salePayments.account',
            'items.product.unit',
            'items.godown',
        ]);

        return Inertia::render('Sales/Show', [
            'sale' => $sale,
        ]);
    }

    public function edit(Request $request, Sales $sale): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureSaleContext($sale, $companyId, $storeId);

        $sale->load([
            'customer',
            'mechanic',
            'mechanicCommission',
            'salePayments.account',
            'items.product.unit',
            'items.godown',
        ]);

        return Inertia::render('Sales/Edit', [
            'sale' => $sale,
            'customers' => [
                [
                    'id' => $sale->customer->id,
                    'customer_code' => $sale->customer->customer_code,
                    'customer_name' => $sale->customer->customer_name,
                    'mobile' => $sale->customer->mobile,
                    'phone' => $sale->customer->phone,
                    'gst_number' => $sale->customer->gst_number,
                    'customer_type' => $sale->customer->customer_type,
                    'is_system_customer' => $sale->customer->is_system_customer,
                ],
            ],
            'mechanics' => $this->mechanicsForStore($companyId, $storeId),
            'accounts' => $this->paymentAccountsForStore(
                $companyId,
                $storeId
            ),
            'godowns' => Godown::query()
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->where('status', true)
                ->orderBy('godown_name')
                ->get(['id', 'godown_name', 'godown_code']),
        ]);
    }

    public function update(
        Request $request,
        Sales $sale,
        StockService $stockService,
        CustomerLedgerService $customerLedgerService
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureSaleContext($sale, $companyId, $storeId);
        $validated = $this->validateSaleRequest($request);
        $userId = (int) $request->user()->id;

        try {
            DB::transaction(function () use (
                $sale,
                $validated,
                $companyId,
                $storeId,
                $stockService,
                $customerLedgerService,
                $userId
            ) {
                $lockedSale = Sales::query()
                    ->with(['items', 'mechanicCommission'])
                    ->lockForUpdate()
                    ->findOrFail($sale->id);

                $hasAllocatedReceipts = CustomerPaymentAllocation::query()
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->where('sale_id', $lockedSale->id)
                    ->whereHas(
                        'customerPayment',
                        fn(Builder $query) => $query->where(
                            'status',
                            CustomerPayment::STATUS_COMPLETED
                        )
                    )
                    ->exists();

                if ($hasAllocatedReceipts) {
                    throw ValidationException::withMessages([
                        'sale' =>
                        'Customer receipts are allocated to this invoice. Cancel those receipts before editing the sale.',
                    ]);
                }

                if (
                    $lockedSale->mechanicCommission
                    && (float) $lockedSale->mechanicCommission->paid_amount > 0
                ) {
                    throw ValidationException::withMessages([
                        'mechanic_id' =>
                        'Mechanic commission has already been paid. This sale cannot be edited.',
                    ]);
                }

                $customer = $this->validateCustomer(
                    (int) $validated['customer_id'],
                    $companyId,
                    $storeId
                );

                $customerLedgerService->ensureOpeningBalance(
                    $customer,
                    $companyId,
                    $storeId,
                    $userId
                );

                $oldDirectPaid = max(
                    0,
                    round(
                        (float) $lockedSale->paid_amount
                            - (float) ($lockedSale->advance_used ?? 0),
                        2
                    )
                );

                if ((int) $lockedSale->customer_id === (int) $customer->id) {
                    /*
                    | Remove this sale and its direct collection from the
                    | current ledger balance before recalculating advance.
                    */
                    $balanceBeforeThisSale = round(
                        $customerLedgerService->currentBalance(
                            $companyId,
                            $storeId,
                            $customer->id
                        )
                            - (float) $lockedSale->grand_total
                            + $oldDirectPaid,
                        2
                    );
                } else {
                    /*
                    | The invoice is being moved to another customer. The new
                    | customer's current credit balance is the available advance.
                    */
                    $balanceBeforeThisSale = $customerLedgerService->currentBalance(
                        $companyId,
                        $storeId,
                        $customer->id
                    );
                }

                $lines = $this->buildSaleLines(
                    $validated,
                    $companyId,
                    $storeId
                );

                if (count($lines) === 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Add at least one product to the sale.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Totals
                |--------------------------------------------------------------------------
                */

                $subTotal =
                    round(
                        collect($lines)->sum(
                            'taxable_amount'
                        ),
                        2
                    );


                $itemDiscountTotal =
                    round(
                        collect($lines)->sum(
                            'discount'
                        ),
                        2
                    );


                $gstTotal =
                    round(
                        collect($lines)->sum(
                            'gst_amount'
                        ),
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Mechanic Commission Base
                |--------------------------------------------------------------------------
                */

                $commissionBase =
                    max(
                        0,
                        $subTotal
                    );


                $commission =
                    $this->calculateCommission(
                        $validated,
                        $companyId,
                        $storeId,
                        $commissionBase
                    );


                /*
                |--------------------------------------------------------------------------
                | Gross Total Before Final Discount
                |--------------------------------------------------------------------------
                */

                $grossTotal =
                    max(
                        0,
                        round(
                            $subTotal
                                +
                                $gstTotal,
                            2
                        )
                    );


                /*
                |--------------------------------------------------------------------------
                | Final Discount
                |--------------------------------------------------------------------------
                */

                $finalDiscount = round(
                    (float) ($validated['final_discount'] ?? 0),
                    2
                );

                if ($finalDiscount > $grossTotal) {
                    throw ValidationException::withMessages([
                        'final_discount' =>
                        'Final discount cannot exceed the invoice amount.',
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Round Off
                |--------------------------------------------------------------------------
                */

                $beforeRoundOff = max(
                    0,
                    round(
                        $grossTotal - $finalDiscount,
                        2
                    )
                );

                $grandTotal = round(
                    $beforeRoundOff,
                    0
                );

                $roundOff = round(
                    $grandTotal - $beforeRoundOff,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | Payment
                |--------------------------------------------------------------------------
                */

                $paymentLines = $this->buildPaymentLines(
                    $validated,
                    $companyId,
                    $storeId
                );

                $directPaidAmount = $this->paymentTotal(
                    $paymentLines,
                    (float) ($validated['paid_amount'] ?? 0)
                );

                if ($directPaidAmount > $grandTotal) {
                    throw ValidationException::withMessages([
                        'paid_amount' =>
                        'Paid amount cannot exceed the net invoice amount.',
                    ]);
                }

                $availableAdvance = max(
                    0,
                    -$balanceBeforeThisSale
                );

                $advanceUsed = round(
                    min(
                        $availableAdvance,
                        max(0, $grandTotal - $directPaidAmount)
                    ),
                    2
                );

                $paidAmount = round(
                    $directPaidAmount + $advanceUsed,
                    2
                );

                $balanceAmount = round(
                    $grandTotal - $paidAmount,
                    2
                );

                $paymentStatus = $this->paymentStatus(
                    $paidAmount,
                    $balanceAmount
                );

                /*
                |--------------------------------------------------------------------------
                | Restore Old Stock
                |--------------------------------------------------------------------------
                */

                foreach ($lockedSale->items as $oldItem) {
                    $product = Product::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->findOrFail($oldItem->product_id);

                    $stockService->increaseStock(
                        $product,
                        (float) $oldItem->quantity + (float) $oldItem->free_quantity,
                        'SALE_EDIT_REVERSE',
                        $companyId,
                        $storeId,
                        $oldItem->godown_id,
                        $lockedSale->id,
                        $lockedSale->invoice_no,
                        (float) ($oldItem->unit_cost ?? 0),
                        'Reverse old sales invoice'
                    );
                }

                $lockedSale->items()->delete();

                /*
                |--------------------------------------------------------------------------
                | Post Updated Stock
                |--------------------------------------------------------------------------
                */

                foreach ($lines as $line) {
                    $stockSnapshot = $stockService->decreaseStock(
                        $line['product'],
                        $line['stock_quantity'],
                        'SALE',
                        $companyId,
                        $storeId,
                        $line['godown_id'],
                        $lockedSale->id,
                        $lockedSale->invoice_no,
                        0,
                        'Sales Invoice'
                    );

                    $costTotal = round(
                        (float) $stockSnapshot['cost_total'],
                        2
                    );

                    SaleItems::create([
                        'sale_id' => $lockedSale->id,
                        'product_id' => $line['product']->id,
                        'godown_id' => $line['godown_id'],
                        'quantity' => $line['quantity'],
                        'free_quantity' => $line['free_quantity'],
                        'sale_price' => $line['sale_price'],
                        'unit_cost' => $stockSnapshot['unit_cost'],
                        'cost_total' => $costTotal,
                        'cost_source' => $stockSnapshot['cost_source'],
                        'discount_type' => $line['discount_type'],
                        'discount_value' => $line['discount_value'],
                        'discount' => $line['discount'],
                        'gst_percent' => $line['gst_percent'],
                        'gst_amount' => $line['gst_amount'],
                        'line_total' => $line['line_total'],
                        'net_sale_amount' => $line['taxable_amount'],
                        'gross_profit' => round(
                            $line['taxable_amount'] - $costTotal,
                            2
                        ),
                    ]);
                }

                $lockedSale->update([
                    'customer_id' => $customer->id,
                    'sale_source' => $commission['sale_source'],
                    'mechanic_id' => $commission['mechanic_id'],
                    'commission_type' => $commission['commission_type'],
                    'commission_percent' => $commission['commission_percent'],
                    'commission_amount' => $commission['commission_amount'],
                    'commission_status' => 'Pending',

                    'invoice_date' => $validated['invoice_date'],
                    'billing_type' =>
                    $validated['billing_type']
                        ??
                        $lockedSale->billing_type
                        ??
                        'gst',
                    'sub_total' => $subTotal,
                    'discount' => 0,
                    'gst_total' => $gstTotal,
                    'final_discount' => $finalDiscount,
                    'round_off' => $roundOff,
                    'grand_total' => $grandTotal,
                    'advance_used' => $advanceUsed,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'payment_status' => $paymentStatus,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                $this->reverseSalePayments(
                    $lockedSale,
                    $companyId,
                    $storeId,
                    $userId,
                    'Sales invoice edited'
                );

                $this->recordSalePayments(
                    $lockedSale,
                    $paymentLines,
                    $directPaidAmount,
                    $companyId,
                    $storeId,
                    $userId
                );

                $customerLedgerService->postSale(
                    $lockedSale->refresh(),
                    $userId
                );

                $customerLedgerService->syncSaleCollection(
                    $lockedSale,
                    $directPaidAmount,
                    $userId
                );

                $lockedSale->mechanicCommission()->delete();

                if (
                    $commission['sale_source'] === 'mechanic'
                    && $commission['mechanic_id']
                    && $commission['commission_amount'] > 0
                ) {
                    MechanicCommission::create([
                        'company_id' => $companyId,
                        'store_id' => $storeId,
                        'mechanic_id' => $commission['mechanic_id'],
                        'sale_id' => $lockedSale->id,
                        'commission_type' => $commission['commission_type'],
                        'commission_percent' => $commission['commission_type'] === 'percentage'
                            ? $commission['commission_percent']
                            : null,
                        'commission_base' => $commissionBase,
                        'commission_amount' => $commission['commission_amount'],
                        'paid_amount' => 0,
                        'balance_amount' => $commission['commission_amount'],
                        'status' => 'Pending',
                        'remarks' => 'Commission generated from sale ' . $lockedSale->invoice_no,
                    ]);
                }
            });

            return AppResponse::successRoute(
                'sales.show',
                'Sale updated successfully.',
                $sale
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return AppResponse::error(
                $exception->getMessage() ?: 'Unable to update sale.',
                true
            );
        }
    }

    public function destroy(
        Request $request,
        Sales $sale,
        StockService $stockService,
        CustomerLedgerService $customerLedgerService
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureSaleContext($sale, $companyId, $storeId);
        $userId = (int) $request->user()->id;

        try {
            DB::transaction(function () use (
                $sale,
                $companyId,
                $storeId,
                $stockService,
                $customerLedgerService,
                $userId
            ) {
                $lockedSale = Sales::query()
                    ->with(['items', 'mechanicCommission'])
                    ->lockForUpdate()
                    ->findOrFail($sale->id);

                if ($lockedSale->status === 'Cancelled') {
                    throw ValidationException::withMessages([
                        'sale' => 'This sale is already cancelled.',
                    ]);
                }

                $hasAllocatedReceipts = CustomerPaymentAllocation::query()
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->where('sale_id', $lockedSale->id)
                    ->whereHas(
                        'customerPayment',
                        fn(Builder $query) => $query->where(
                            'status',
                            CustomerPayment::STATUS_COMPLETED
                        )
                    )
                    ->exists();

                if ($hasAllocatedReceipts) {
                    throw ValidationException::withMessages([
                        'sale' =>
                        'Customer receipts are allocated to this invoice. Cancel those receipts before cancelling the sale.',
                    ]);
                }

                if (
                    $lockedSale->mechanicCommission
                    && (float) $lockedSale->mechanicCommission->paid_amount > 0
                ) {
                    throw ValidationException::withMessages([
                        'sale' =>
                        'This sale has a paid mechanic commission and cannot be cancelled.',
                    ]);
                }

                foreach ($lockedSale->items as $item) {
                    $product = Product::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->findOrFail($item->product_id);

                    $stockService->increaseStock(
                        $product,
                        (float) $item->quantity + (float) $item->free_quantity,
                        'SALE_CANCELLATION',
                        $companyId,
                        $storeId,
                        $item->godown_id,
                        $lockedSale->id,
                        $lockedSale->invoice_no,
                        (float) ($item->unit_cost ?? 0),
                        'Reverse cancelled sales invoice'
                    );
                }

                $this->reverseSalePayments(
                    $lockedSale,
                    $companyId,
                    $storeId,
                    $userId,
                    'Sales invoice cancelled'
                );

                $customerLedgerService->reverseSaleCollection(
                    $lockedSale,
                    now(),
                    'Reversal of collection for cancelled invoice '
                        . $lockedSale->invoice_no,
                    $userId
                );

                $customerLedgerService->reverseSale(
                    $lockedSale,
                    now(),
                    'Cancellation of sale ' . $lockedSale->invoice_no,
                    $userId
                );

                $lockedSale->update([
                    'status' => Sales::STATUS_CANCELLED,
                ]);
            });

            return AppResponse::successRoute(
                'sales.index',
                'Sale cancelled successfully.'
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return AppResponse::error(
                $exception->getMessage() ?: 'Unable to cancel sale.'
            );
        }
    }

    public function print(Request $request, Sales $sale)
    {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureSaleContext($sale, $companyId, $storeId);

        $sale->load([
            'company',
            'store',
            'customer',
            'mechanic',
            'items.product.unit',
            'items.godown',
        ]);

        return view('sales.print', compact('sale'));
    }

    public function pdf(
        Request $request,
        Sales $sale
    ) {
        [$companyId, $storeId] = $this->context($request);

        $this->ensureSaleContext(
            $sale,
            $companyId,
            $storeId
        );

        $validated = $request->validate([
            'paper' => [
                'nullable',
                'in:a4,a5',
            ],
        ]);

        $paper = $validated['paper'] ?? 'a4';

        $sale->load([
            'company',
            'store',
            'customer',
            'mechanic',
            'items.product.unit',
            'items.godown',
        ]);

        $pdf = Pdf::loadView(
            'sales.pdf',
            compact(
                'sale',
                'paper'
            )
        )->setPaper(
            $paper,
            'portrait'
        );

        return $pdf->download(
            'invoice-'
                . $sale->invoice_no
                . '-'
                . strtoupper($paper)
                . '.pdf'
        );
    }


    private function validateSaleRequest(Request $request): array
    {
        return $request->validate([
            'invoice_date' => ['required', 'date'],

            'billing_type' => [
                'nullable',
                Rule::in([
                    'gst',
                    'non_gst',
                ]),
            ],

            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'sale_source' => [
                'required',
                Rule::in([
                    'customer',
                    'mechanic',
                ]),
            ],

            'mechanic_id' => [
                'nullable',
                'integer',
                'exists:mechanics,id',
            ],

            'commission_type' => [
                'nullable',
                Rule::in([
                    'percentage',
                    'fixed',
                ]),
            ],

            'commission_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'commission_fixed_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Final Invoice Discount Only
            |--------------------------------------------------------------------------
            |
            | There is no separate sale-level discount in the new Sales UI.
            | The legacy sales.discount column is kept in the database as 0.
            |
            */

            'final_discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'paid_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payments' => [
                'nullable',
                'array',
            ],

            'payments.*.account_id' => [
                'required',
                'integer',
                'exists:accounts,id',
            ],

            'payments.*.payment_mode' => [
                'required',
                Rule::in([
                    'cash',
                    'gpay',
                    'phonepe',
                    'upi',
                    'bank_transfer',
                    'card',
                    'cheque',
                ]),
            ],

            'payments.*.amount' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'payments.*.reference_no' => [
                'nullable',
                'string',
                'max:150',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Product Rows
            |--------------------------------------------------------------------------
            */

            'product_id' => [
                'required',
                'array',
                'min:1',
            ],

            'product_id.*' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'godown_id' => [
                'required',
                'array',
            ],

            'godown_id.*' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],

            'qty' => [
                'required',
                'array',
                'min:1',
            ],

            'qty.*' => [
                'required',
                'numeric',
                'min:0.001',
            ],

            'free_quantity' => [
                'nullable',
                'array',
            ],

            'free_quantity.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'sale_price' => [
                'required',
                'array',
                'min:1',
            ],

            'sale_price.*' => [
                'required',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Per-Item Discount
            |--------------------------------------------------------------------------
            |
            | percentage -> discount_value is a %
            | fixed      -> discount_value is a ₹ amount
            |
            */

            'discount_type' => [
                'nullable',
                'array',
            ],

            'discount_type.*' => [
                'nullable',
                Rule::in([
                    'percentage',
                    'fixed',
                ]),
            ],

            'discount_value' => [
                'nullable',
                'array',
            ],

            'discount_value.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Legacy Item Discount Compatibility
            |--------------------------------------------------------------------------
            |
            | Allows the existing Edit page to keep working until it is upgraded
            | to discount_type + discount_value.
            |
            */

            'item_discount' => [
                'nullable',
                'array',
            ],

            'item_discount.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | GST
            |--------------------------------------------------------------------------
            |
            | GST is still calculated and stored for both gst and non_gst bills.
            | billing_type controls only customer-facing presentation.
            |
            */

            'gst' => [
                'nullable',
                'array',
            ],

            'gst.*' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ]);
    }

    private function buildSaleLines(
        array $validated,
        int $companyId,
        int $storeId
    ): array {
        $lines = [];

        foreach ($validated['product_id'] as $index => $productId) {

            $quantity =
                round(
                    (float) (
                        $validated['qty'][$index]
                        ?? 0
                    ),
                    3
                );

            $freeQuantity =
                round(
                    (float) (
                        $validated['free_quantity'][$index]
                        ?? 0
                    ),
                    3
                );

            $salePrice =
                round(
                    (float) (
                        $validated['sale_price'][$index]
                        ?? 0
                    ),
                    2
                );

            $discountType =
                $validated['discount_type'][$index]
                ?? 'fixed';

            $discountValue =
                round(
                    (float) (
                        $validated['discount_value'][$index]
                        ??
                        $validated['item_discount'][$index]
                        ??
                        0
                    ),
                    2
                );

            $gstPercent =
                round(
                    (float) (
                        $validated['gst'][$index]
                        ?? 0
                    ),
                    2
                );

            $godownId =
                $validated['godown_id'][$index]
                ?? null;

            $godownId =
                filled($godownId)
                ? (int) $godownId
                : null;


            /*
            |--------------------------------------------------------------------------
            | Product Branch Isolation
            |--------------------------------------------------------------------------
            */

            $product =
                Product::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'store_id',
                    $storeId
                )
                ->where(
                    'status',
                    true
                )
                ->find(
                    $productId
                );


            if (!$product) {
                throw ValidationException::withMessages([
                    "product_id.{$index}" =>
                    'Selected product is not available in the active branch.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Godown Branch Isolation
            |--------------------------------------------------------------------------
            */

            if ($godownId !== null) {

                $validGodown =
                    Godown::query()
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->where(
                        'store_id',
                        $storeId
                    )
                    ->where(
                        'status',
                        true
                    )
                    ->whereKey(
                        $godownId
                    )
                    ->exists();


                if (!$validGodown) {
                    throw ValidationException::withMessages([
                        "godown_id.{$index}" =>
                        'Selected godown is not available in the active branch.',
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Gross Amount
            |--------------------------------------------------------------------------
            */

            $grossAmount =
                round(
                    $quantity
                        *
                        $salePrice,
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Discount Amount
            |--------------------------------------------------------------------------
            */

            if ($discountType === 'percentage') {

                if ($discountValue > 100) {
                    throw ValidationException::withMessages([
                        "discount_value.{$index}" =>
                        'Percentage discount cannot exceed 100%.',
                    ]);
                }


                $discountAmount =
                    round(
                        $grossAmount
                            *
                            $discountValue
                            /
                            100,
                        2
                    );
            } else {

                $discountAmount =
                    round(
                        $discountValue,
                        2
                    );
            }


            if ($discountAmount > $grossAmount) {
                throw ValidationException::withMessages([
                    "discount_value.{$index}" =>
                    'Item discount cannot exceed the item amount.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Taxable + GST
            |--------------------------------------------------------------------------
            */

            $taxableAmount =
                round(
                    $grossAmount
                        -
                        $discountAmount,
                    2
                );


            $gstAmount =
                round(
                    $taxableAmount
                        *
                        $gstPercent
                        /
                        100,
                    2
                );


            $lineTotal =
                round(
                    $taxableAmount
                        +
                        $gstAmount,
                    2
                );


            $lines[] = [
                'product' =>
                $product,

                'godown_id' =>
                $godownId,

                'quantity' =>
                $quantity,

                'free_quantity' =>
                $freeQuantity,

                'stock_quantity' =>
                round(
                    $quantity
                        +
                        $freeQuantity,
                    3
                ),

                'sale_price' =>
                $salePrice,

                'discount_type' =>
                $discountType,

                'discount_value' =>
                $discountValue,

                'discount' =>
                $discountAmount,

                'gross_amount' =>
                $grossAmount,

                'taxable_amount' =>
                $taxableAmount,

                'gst_percent' =>
                $gstPercent,

                'gst_amount' =>
                $gstAmount,

                'line_total' =>
                $lineTotal,
            ];
        }

        return $lines;
    }

    private function calculateCommission(
        array $validated,
        int $companyId,
        int $storeId,
        float $commissionBase
    ): array {
        if (($validated['sale_source'] ?? 'customer') !== 'mechanic') {
            return [
                'sale_source' => 'customer',
                'mechanic_id' => null,
                'commission_type' => null,
                'commission_percent' => null,
                'commission_amount' => 0,
            ];
        }

        $mechanicId = (int) ($validated['mechanic_id'] ?? 0);

        if ($mechanicId <= 0) {
            throw ValidationException::withMessages([
                'mechanic_id' => 'Please select a mechanic.',
            ]);
        }

        $mechanic = Mechanic::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas('stores', fn(Builder $q) =>
            $q->where('stores.id', $storeId))
            ->find($mechanicId);

        if (!$mechanic) {
            throw ValidationException::withMessages([
                'mechanic_id' =>
                'Selected mechanic is not available for this branch.',
            ]);
        }

        $type = $validated['commission_type'] ?? null;

        if (!in_array($type, ['percentage', 'fixed'], true)) {
            throw ValidationException::withMessages([
                'commission_type' => 'Please select a commission type.',
            ]);
        }

        if ($type === 'percentage') {
            $percent = (float) ($validated['commission_percent'] ?? 0);

            if ($percent <= 0 || $percent > 100) {
                throw ValidationException::withMessages([
                    'commission_percent' =>
                    'Enter a valid commission percentage.',
                ]);
            }

            return [
                'sale_source' => 'mechanic',
                'mechanic_id' => $mechanic->id,
                'commission_type' => 'percentage',
                'commission_percent' => $percent,
                'commission_amount' => round(
                    $commissionBase * $percent / 100,
                    2
                ),
            ];
        }

        $fixed = round(
            (float) ($validated['commission_fixed_amount'] ?? 0),
            2
        );

        if ($fixed <= 0) {
            throw ValidationException::withMessages([
                'commission_fixed_amount' =>
                'Enter a valid fixed commission amount.',
            ]);
        }

        if ($fixed > $commissionBase) {
            throw ValidationException::withMessages([
                'commission_fixed_amount' =>
                'Commission cannot exceed taxable sale value.',
            ]);
        }

        return [
            'sale_source' => 'mechanic',
            'mechanic_id' => $mechanic->id,
            'commission_type' => 'fixed',
            'commission_percent' => null,
            'commission_amount' => $fixed,
        ];
    }

    private function validateCustomer(
        int $customerId,
        int $companyId,
        int $storeId
    ): Customer {
        $customer = Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereKey($customerId)
            ->whereHas('stores', fn(Builder $q) =>
            $q->where('stores.id', $storeId))
            ->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'customer_id' =>
                'Selected customer is not available for this branch.',
            ]);
        }

        return $customer;
    }

    private function customersForStore(
        int $companyId,
        int $storeId
    ) {
        return Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas('stores', fn(Builder $q) =>
            $q->where('stores.id', $storeId))
            ->orderByRaw('is_system_customer DESC')
            ->orderBy('customer_name')
            ->get([
                'id',
                'customer_name',
                'mobile',
                'customer_type',
                'is_system_customer',
            ]);
    }

    private function initialCustomersForStore(
        int $companyId,
        int $storeId
    ) {
        return Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->where('is_system_customer', true)
            ->whereHas(
                'stores',
                fn(Builder $query) => $query->where(
                    'stores.id',
                    $storeId
                )
            )
            ->orderBy('customer_name')
            ->get([
                'id',
                'customer_code',
                'customer_name',
                'mobile',
                'phone',
                'gst_number',
                'customer_type',
                'is_system_customer',
            ]);
    }

    private function mechanicsForStore(
        int $companyId,
        int $storeId
    ) {
        return Mechanic::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas('stores', fn(Builder $q) =>
            $q->where('stores.id', $storeId))
            ->orderBy('mechanic_name')
            ->get([
                'id',
                'mechanic_code',
                'mechanic_name',
                'mobile',
            ]);
    }

    private function paymentAccountsForStore(
        int $companyId,
        int $storeId
    ) {
        return Account::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderByRaw(
                "FIELD(account_type, 'cash', 'upi', 'bank', 'card', 'other')"
            )
            ->orderBy('account_name')
            ->get([
                'id',
                'account_code',
                'account_name',
                'account_type',
                'payment_mode',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Sale Payment Lines
    |--------------------------------------------------------------------------
    */

    private function buildPaymentLines(
        array $validated,
        int $companyId,
        int $storeId
    ): array {
        $payments = $validated['payments'] ?? [];

        if (!is_array($payments) || count($payments) === 0) {
            return [];
        }

        $lines = [];

        foreach ($payments as $index => $payment) {
            $account = Account::query()
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->where('status', true)
                ->find($payment['account_id']);

            if (!$account) {
                throw ValidationException::withMessages([
                    "payments.{$index}.account_id" =>
                    'The selected payment account is invalid for the active branch.',
                ]);
            }

            $lines[] = [
                'account_id' => $account->id,
                'payment_mode' => $payment['payment_mode'],
                'amount' => round((float) $payment['amount'], 2),
                'reference_no' => filled($payment['reference_no'] ?? null)
                    ? trim((string) $payment['reference_no'])
                    : null,
            ];
        }

        return $lines;
    }

    private function paymentTotal(
        array $paymentLines,
        float $legacyPaidAmount
    ): float {
        if (count($paymentLines) === 0) {
            return round(max(0, $legacyPaidAmount), 2);
        }

        return round(
            collect($paymentLines)->sum('amount'),
            2
        );
    }

    private function recordSalePayments(
        Sales $sale,
        array $paymentLines,
        float $paidAmount,
        int $companyId,
        int $storeId,
        int $userId
    ): void {
        if ($paidAmount <= 0) {
            return;
        }

        if (count($paymentLines) === 0) {
            SalePayment::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'sale_id' => $sale->id,
                'account_id' => null,
                'payment_date' => $sale->invoice_date,
                'payment_mode' => 'legacy',
                'amount' => $paidAmount,
                'status' => SalePayment::STATUS_COMPLETED,
                'remarks' =>
                'Payment captured by the legacy paid amount field; payment account was not supplied.',
                'created_by' => $userId,
            ]);

            return;
        }

        foreach ($paymentLines as $line) {
            $payment = SalePayment::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'sale_id' => $sale->id,
                'account_id' => $line['account_id'],
                'payment_date' => $sale->invoice_date,
                'payment_mode' => $line['payment_mode'],
                'amount' => $line['amount'],
                'reference_no' => $line['reference_no'],
                'status' => SalePayment::STATUS_COMPLETED,
                'remarks' => 'Payment received with sales invoice.',
                'created_by' => $userId,
            ]);

            AccountTransaction::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'account_id' => $line['account_id'],
                'transaction_date' => $sale->invoice_date,
                'transaction_type' => AccountTransaction::TYPE_SALE,
                'reference_type' => SalePayment::class,
                'reference_id' => $payment->id,
                'reference_no' => $sale->invoice_no,
                'debit' => $line['amount'],
                'credit' => 0,
                'payment_mode' => $line['payment_mode'],
                'external_reference' => $line['reference_no'],
                'remarks' => 'Sales collection for ' . $sale->invoice_no,
                'created_by' => $userId,
            ]);
        }
    }

    private function reverseSalePayments(
        Sales $sale,
        int $companyId,
        int $storeId,
        int $userId,
        string $reason
    ): void {
        $payments = SalePayment::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('sale_id', $sale->id)
            ->where('status', SalePayment::STATUS_COMPLETED)
            ->lockForUpdate()
            ->get();

        foreach ($payments as $payment) {
            if ($payment->account_id !== null) {
                $originalTransaction = AccountTransaction::query()
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->where('reference_type', SalePayment::class)
                    ->where('reference_id', $payment->id)
                    ->where('is_reversal', false)
                    ->latest('id')
                    ->first();

                AccountTransaction::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'account_id' => $payment->account_id,
                    'transaction_date' => now()->toDateString(),
                    'transaction_type' => AccountTransaction::TYPE_REVERSAL,
                    'reference_type' => SalePayment::class,
                    'reference_id' => $payment->id,
                    'reference_no' => $sale->invoice_no,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'payment_mode' => $payment->payment_mode,
                    'external_reference' => $payment->reference_no,
                    'remarks' => $reason,
                    'is_reversal' => true,
                    'reversal_of_id' => $originalTransaction?->id,
                    'created_by' => $userId,
                ]);
            }

            $payment->update([
                'status' => SalePayment::STATUS_CANCELLED,
                'cancelled_by' => $userId,
                'cancelled_at' => now(),
                'remarks' => trim(
                    ($payment->remarks ? $payment->remarks . ' ' : '')
                        . $reason
                ),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Status
    |--------------------------------------------------------------------------
    */

    private function paymentStatus(
        float $paidAmount,
        float $balanceAmount
    ): string {
        if ($paidAmount <= 0) {
            return 'Unpaid';
        }

        if ($balanceAmount > 0) {
            return 'Partially Paid';
        }

        return 'Paid';
    }


    private function currentStock(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): float {
        return (float) (
            StockLedger::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->when(
                $godownId === null,
                fn(Builder $q) => $q->whereNull('godown_id'),
                fn(Builder $q) => $q->where('godown_id', $godownId)
            )
            ->latest('id')
            ->value('balance_qty')
            ?? 0
        );
    }

    private function ensureSaleContext(
        Sales $sale,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $sale->company_id === $companyId
                && (int) $sale->store_id === $storeId,
            404
        );
    }

    private function authorizeLookup(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user
                && (
                    $user->can('sales.view')
                    || $user->can('sales.create')
                    || $user->can('sales.edit')
                ),
            403
        );
    }

    private function context(Request $request): array
    {
        $user = $request->user();

        abort_unless(
            $user && (bool) $user->status,
            403
        );

        $companyId = (int) session('active_company_id');
        $storeId = (int) session('active_store_id');

        abort_unless(
            $companyId > 0 && $storeId > 0,
            403,
            'No active business branch selected.'
        );

        $store = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->first();

        abort_unless(
            $store,
            403,
            'The active branch is unavailable.'
        );

        if (!$user->is_super_user) {
            abort_unless(
                (int) $user->company_id === $companyId,
                403
            );

            if (!$user->hasRole('Company Admin')) {
                $assigned = $user
                    ->stores()
                    ->where('stores.id', $storeId)
                    ->where('stores.status', true)
                    ->exists();

                abort_unless($assigned, 403);
            }
        }

        return [$companyId, $storeId];
    }

    private function generateInvoiceNo(int $storeId): string
    {
        $lastSale = Sales::query()
            ->where('store_id', $storeId)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (!$lastSale) {
            return 'INV000001';
        }

        $number = (int) preg_replace(
            '/\D/',
            '',
            (string) $lastSale->invoice_no
        );

        return 'INV' . str_pad(
            (string) ($number + 1),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    private function previewInvoiceNo(int $storeId): string
    {
        $lastSale = Sales::query()
            ->where('store_id', $storeId)
            ->latest('id')
            ->first();

        if (!$lastSale) {
            return 'INV000001';
        }

        $number = (int) preg_replace(
            '/\D/',
            '',
            (string) $lastSale->invoice_no
        );

        return 'INV' . str_pad(
            (string) ($number + 1),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    private function generateMechanicCode(int $companyId): string
    {
        $last = Mechanic::query()
            ->where('company_id', $companyId)
            ->latest('id')
            ->first();

        $number = $last
            ? ((int) preg_replace('/\D/', '', (string) $last->mechanic_code)) + 1
            : 1;

        return 'MEC' . str_pad(
            (string) $number,
            5,
            '0',
            STR_PAD_LEFT
        );
    }
}
