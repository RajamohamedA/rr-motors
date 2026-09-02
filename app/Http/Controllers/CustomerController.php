<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Sales;
use App\Models\Store;
use App\Services\GstLookupService;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Controller Permission Middleware
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:customer.view',
                only: [
                    'index',
                    'duplicateLookup',
                ]
            ),

            new Middleware(
                'permission:customer.show',
                only: [
                    'show',
                ]
            ),

            new Middleware(
                'permission:customer.create',
                only: [
                    'create',
                    'store',
                    'attachExisting',
                ]
            ),

            new Middleware(
                'permission:customer.create|customer.edit',
                only: [
                    'gstLookup',
                ]
            ),

            new Middleware(
                'permission:customer.edit',
                only: [
                    'edit',
                    'update',
                    'toggleStatus',
                ]
            ),

            new Middleware(
                'permission:customer.delete',
                only: [
                    'removeFromBranch',
                    'destroy',
                ]
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $search =
            trim(
                $request
                    ->string('search')
                    ->toString()
            );


        $status =
            $request->input(
                'status'
            );


        $type =
            trim(
                $request
                    ->string('type')
                    ->toString()
            );


        $customers =
            Customer::query()

            ->where(
                'company_id',
                $companyId
            )

            ->whereHas(
                'stores',
                fn($query) =>
                $query->where(
                    'stores.id',
                    $storeId
                )
            )

            ->withCount(
                'stores'
            )

            ->when(
                $search !== '',
                function (
                    $query
                ) use (
                    $search
                ) {

                    $query->where(
                        function (
                            $q
                        ) use (
                            $search
                        ) {

                            $q->where(
                                'customer_name',
                                'like',
                                "%{$search}%"
                            )

                                ->orWhere(
                                    'customer_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'mobile',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'gst_number',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== null &&
                    $status !== '',
                fn($query) =>
                $query->where(
                    'status',
                    (bool) ((int) $status)
                )
            )

            ->when(
                $type !== '',
                fn($query) =>
                $query->where(
                    'customer_type',
                    $type
                )
            )

            ->orderByDesc(
                'is_system_customer'
            )

            ->orderBy(
                'customer_name'
            )

            ->paginate(
                15
            )

            ->withQueryString();


        $customers->through(
            fn(
                Customer $customer
            ) => [

                'id' =>
                $customer->id,

                'customer_code' =>
                $customer->customer_code,

                'customer_name' =>
                $customer->customer_name,

                'customer_type' =>
                $customer->customer_type,

                'is_system_customer' =>
                (bool) $customer->is_system_customer,

                'mobile' =>
                $customer->mobile,

                'email' =>
                $customer->email,

                'gst_number' =>
                $customer->gst_number,

                'city' =>
                $customer->city,

                'state' =>
                $customer->state,

                'credit_limit' =>
                (float) $customer->credit_limit,

                'credit_days' =>
                (int) $customer->credit_days,

                'status' =>
                (bool) $customer->status,

                'stores_count' =>
                $customer->stores_count,
            ]
        );


        $summaryBase =
            Customer::query()

            ->where(
                'company_id',
                $companyId
            )

            ->whereHas(
                'stores',
                fn($query) =>
                $query->where(
                    'stores.id',
                    $storeId
                )
            );


        $summary = [

            'total' => (clone $summaryBase)
                ->count(),

            'active' => (clone $summaryBase)
                ->where(
                    'status',
                    true
                )
                ->count(),

            'inactive' => (clone $summaryBase)
                ->where(
                    'status',
                    false
                )
                ->count(),

            'credit_customers' => (clone $summaryBase)
                ->where(
                    'credit_limit',
                    '>',
                    0
                )
                ->count(),
        ];


        return Inertia::render(
            'Customers/Index',
            [

                'customers' =>
                $customers,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status,

                    'type' =>
                    $type,
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        return Inertia::render(
            'Customers/Create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $validated =
            $this->normalizeCustomerData(
                $this->validateCustomer(
                    $request
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Duplicate Detection
        |--------------------------------------------------------------------------
        */

        $duplicate =
            $this->findDuplicate(
                companyId: $companyId,

                gstNumber: $validated['gst_number']
                    ?? null,

                mobile: $validated['mobile']
                    ?? null,

                email: $validated['email']
                    ?? null
            );


        if (
            $duplicate
        ) {

            /*
            |--------------------------------------------------------------------------
            | Already In Current Branch
            |--------------------------------------------------------------------------
            */

            if (
                $duplicate
                ->stores()
                ->where(
                    'stores.id',
                    $storeId
                )
                ->exists()
            ) {

                return AppResponse::warning(
                    "Customer {$duplicate->customer_name} already exists in this branch."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Exists Elsewhere In Company
            |--------------------------------------------------------------------------
            */

            return redirect()

                ->route(
                    'customers.create'
                )

                ->withInput()

                ->with(
                    'duplicate_customer',
                    [

                        'id' =>
                        $duplicate->id,

                        'customer_code' =>
                        $duplicate->customer_code,

                        'customer_name' =>
                        $duplicate->customer_name,

                        'mobile' =>
                        $duplicate->mobile,

                        'email' =>
                        $duplicate->email,

                        'gst_number' =>
                        $duplicate->gst_number,

                        'city' =>
                        $duplicate->city,

                        'state' =>
                        $duplicate->state,

                        'message' =>
                        'This customer already exists in another branch of this company.',
                    ]
                )

                ->with(
                    'app_message',
                    [

                        'id' =>
                        uniqid(
                            'msg_',
                            true
                        ),

                        'type' =>
                        'warning',

                        'message' =>
                        'Matching customer found. Add the existing customer to this branch instead of creating a duplicate.',
                    ]
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $customer =
            DB::transaction(
                function () use (
                    $validated,
                    $companyId,
                    $storeId
                ) {

                    $customer =
                        Customer::create([

                            'company_id' =>
                            $companyId,

                            'customer_code' =>
                            $this->generateCustomerCode(
                                $companyId
                            ),

                            'customer_name' =>
                            trim(
                                $validated['customer_name']
                            ),

                            'customer_type' =>
                            'regular',

                            'is_system_customer' =>
                            false,

                            'contact_person' =>
                            $validated['contact_person']
                                ?? null,

                            'mobile' =>
                            $validated['mobile']
                                ?? null,

                            'phone' =>
                            $validated['phone']
                                ?? null,

                            'email' =>
                            $validated['email']
                                ?? null,

                            'billing_address' =>
                            $validated['billing_address']
                                ?? null,

                            'shipping_address' =>
                            $validated['shipping_address']
                                ?? null,

                            'city' =>
                            $validated['city']
                                ?? null,

                            'state' =>
                            $validated['state']
                                ?? null,

                            'country' =>
                            $validated['country']
                                ?? 'India',

                            'pincode' =>
                            $validated['pincode']
                                ?? null,

                            'gst_number' =>
                            $validated['gst_number']
                                ?? null,

                            'pan_number' =>
                            $validated['pan_number']
                                ?? null,

                            'credit_limit' =>
                            $validated['credit_limit']
                                ?? 0,

                            'credit_days' =>
                            $validated['credit_days']
                                ?? 0,

                            'opening_balance' =>
                            $validated['opening_balance']
                                ?? 0,

                            'balance_type' =>
                            $validated['balance_type']
                                ?? 'Dr',

                            'status' =>
                            (bool) (
                                $validated['status']
                                ?? true
                            ),

                            'remarks' =>
                            $validated['remarks']
                                ?? null,
                        ]);


                    $customer
                        ->stores()
                        ->syncWithoutDetaching([
                            $storeId,
                        ]);


                    return $customer;
                }
            );


        return AppResponse::successRoute(
            'customers.show',
            'Customer created successfully.',
            $customer
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Customer $customer
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCustomerAvailable(
            $customer,
            $companyId,
            $storeId
        );


        $customer->load([
            'stores:id,company_id,store_name,store_code,status',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Sales - 15 Per Page
        |--------------------------------------------------------------------------
        */

        $sales =
            Sales::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'customer_id',
                $customer->id
            )
            ->orderByDesc(
                'invoice_date'
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(
                15,
                [
                    'id',
                    'store_id',
                    'invoice_no',
                    'invoice_date',
                    'grand_total',
                    'paid_amount',
                    'balance_amount',
                    'status',
                ],
                'sales_page'
            )
            ->withQueryString();


        $sales->through(
            fn(
                Sales $sale
            ) => [
                'id' =>
                $sale->id,

                'invoice_no' =>
                $sale->invoice_no,

                'invoice_date' =>
                $sale->invoice_date,

                'grand_total' =>
                (float) $sale->grand_total,

                'paid_amount' =>
                (float) $sale->paid_amount,

                'balance_amount' =>
                (float) $sale->balance_amount,

                'status' =>
                $sale->status,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Customer Payments - 15 Per Page
        |--------------------------------------------------------------------------
        */

        $payments =
            CustomerPayment::query()
            ->where(
                'company_id',
                $companyId
            )
            ->when(
                Schema::hasColumn(
                    'customer_payments',
                    'store_id'
                ),
                fn($query) =>
                $query->where(
                    'store_id',
                    $storeId
                )
            )
            ->where(
                'customer_id',
                $customer->id
            )
            ->orderByDesc(
                $this->paymentDateColumn()
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(
                15,
                ['*'],
                'payments_page'
            )
            ->withQueryString();


        $payments->through(
            fn(
                CustomerPayment $payment
            ) =>
            $this->paymentData(
                $payment
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Combined Ledger / Transactions - 15 Per Page
        |--------------------------------------------------------------------------
        */

        $transactions =
            $this->customerTransactions(
                customer: $customer,

                companyId: $companyId,

                storeId: $storeId
            );


        /*
        |--------------------------------------------------------------------------
        | Account Summary
        |--------------------------------------------------------------------------
        */

        $totalSales =
            (float) Sales::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'store_id',
                    $storeId
                )
                ->where(
                    'customer_id',
                    $customer->id
                )
                ->sum(
                    'grand_total'
                );


        $salesPaid =
            (float) Sales::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'store_id',
                    $storeId
                )
                ->where(
                    'customer_id',
                    $customer->id
                )
                ->sum(
                    'paid_amount'
                );


        $separatePayments =
            $this->totalCustomerPayments(
                companyId: $companyId,

                customerId: $customer->id,

                storeId: $storeId
            );


        $openingBalance =
            (float) $customer->opening_balance;


        $openingDebit =
            $customer->balance_type === 'Dr'
            ? $openingBalance
            : 0;


        $openingCredit =
            $customer->balance_type === 'Cr'
            ? $openingBalance
            : 0;


        $currentBalance =
            max(
                0,
                $openingDebit
                    +
                    $totalSales
                    -
                    $salesPaid
                    -
                    $separatePayments
                    -
                    $openingCredit
            );


        $creditLimit =
            (float) $customer->credit_limit;


        $availableCredit =
            $creditLimit > 0
            ? max(
                0,
                $creditLimit - $currentBalance
            )
            : 0;


        $lastSale =
            Sales::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'customer_id',
                $customer->id
            )
            ->latest(
                'invoice_date'
            )
            ->first([
                'id',
                'invoice_no',
                'invoice_date',
                'grand_total',
            ]);


        $lastPayment =
            CustomerPayment::query()
            ->where(
                'company_id',
                $companyId
            )
            ->when(
                Schema::hasColumn(
                    'customer_payments',
                    'store_id'
                ),
                fn($query) =>
                $query->where(
                    'store_id',
                    $storeId
                )
            )
            ->where(
                'customer_id',
                $customer->id
            )
            ->orderByDesc(
                $this->paymentDateColumn()
            )
            ->first();


        return Inertia::render(
            'Customers/Show',
            [
                'customer' =>
                $this->customerData(
                    $customer
                ),

                'account_summary' => [
                    'company_id' =>
                    $companyId,

                    'store_id' =>
                    $storeId,

                    'store_name' =>
                    session(
                        'active_store_name'
                    ),

                    'opening_balance' =>
                    $openingBalance,

                    'balance_type' =>
                    $customer->balance_type,

                    'current_balance' =>
                    $currentBalance,

                    'credit_limit' =>
                    $creditLimit,

                    'available_credit' =>
                    $availableCredit,

                    'credit_days' =>
                    (int) $customer->credit_days,

                    'total_sales' =>
                    $totalSales,

                    'sales_paid' =>
                    $salesPaid,

                    'separate_payments' =>
                    $separatePayments,

                    'last_sale' =>
                    $lastSale
                        ? [
                            'id' =>
                            $lastSale->id,

                            'invoice_no' =>
                            $lastSale->invoice_no,

                            'date' =>
                            $lastSale->invoice_date,

                            'amount' =>
                            (float) $lastSale->grand_total,
                        ]
                        : null,

                    'last_payment' =>
                    $lastPayment
                        ? $this->paymentData(
                            $lastPayment
                        )
                        : null,
                ],

                'transactions' =>
                $transactions,

                'sales' =>
                $sales,

                'payments' =>
                $payments,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Customer $customer
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCustomerAvailable(
            $customer,
            $companyId,
            $storeId
        );


        if (
            $customer->is_system_customer
        ) {

            abort(
                403,
                'System customers cannot be edited.'
            );
        }


        $customer->load([
            'stores:id,company_id,store_name,store_code,status',
        ]);


        return Inertia::render(
            'Customers/Edit',
            [

                'customer' =>
                $this->customerData(
                    $customer
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Customer $customer
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCustomerAvailable(
            $customer,
            $companyId,
            $storeId
        );


        if (
            $customer->is_system_customer
        ) {

            return AppResponse::error(
                'System customers cannot be modified.'
            );
        }


        $validated =
            $this->normalizeCustomerData(
                $this->validateCustomer(
                    $request,
                    $customer->id
                )
            );


        $duplicate =
            $this->findDuplicate(
                companyId: $companyId,

                gstNumber: $validated['gst_number']
                    ?? null,

                mobile: $validated['mobile']
                    ?? null,

                email: $validated['email']
                    ?? null,

                ignoreCustomerId: $customer->id
            );


        if (
            $duplicate
        ) {

            return AppResponse::warning(
                "Another customer already uses the same GST, mobile or email: {$duplicate->customer_name}."
            );
        }


        $customer->update([

            'customer_name' =>
            trim(
                $validated['customer_name']
            ),

            'contact_person' =>
            $validated['contact_person']
                ?? null,

            'mobile' =>
            $validated['mobile']
                ?? null,

            'phone' =>
            $validated['phone']
                ?? null,

            'email' =>
            $validated['email']
                ?? null,

            'billing_address' =>
            $validated['billing_address']
                ?? null,

            'shipping_address' =>
            $validated['shipping_address']
                ?? null,

            'city' =>
            $validated['city']
                ?? null,

            'state' =>
            $validated['state']
                ?? null,

            'country' =>
            $validated['country']
                ?? 'India',

            'pincode' =>
            $validated['pincode']
                ?? null,

            'gst_number' =>
            $validated['gst_number']
                ?? null,

            'pan_number' =>
            $validated['pan_number']
                ?? null,

            'credit_limit' =>
            $validated['credit_limit']
                ?? 0,

            'credit_days' =>
            $validated['credit_days']
                ?? 0,

            'opening_balance' =>
            $validated['opening_balance']
                ?? 0,

            'balance_type' =>
            $validated['balance_type']
                ?? 'Dr',

            'status' =>
            (bool) (
                $validated['status']
                ?? true
            ),

            'remarks' =>
            $validated['remarks']
                ?? null,
        ]);


        return AppResponse::successRoute(
            'customers.show',
            'Customer updated successfully.',
            $customer
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Attach Existing Customer To Current Branch
    |--------------------------------------------------------------------------
    */

    public function attachExisting(
        Customer $customer
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        abort_unless(
            (int) $customer->company_id ===
                $companyId,
            404
        );


        if (
            !$customer->status
        ) {

            return AppResponse::error(
                'Inactive customers cannot be added to this branch.'
            );
        }


        $customer
            ->stores()
            ->syncWithoutDetaching([
                $storeId,
            ]);


        return AppResponse::successRoute(
            'customers.show',
            'Customer added to this branch successfully.',
            $customer
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove From Current Branch
    |--------------------------------------------------------------------------
    */

    public function removeFromBranch(
        Customer $customer
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCustomerAvailable(
            $customer,
            $companyId,
            $storeId
        );


        if (
            $customer->is_system_customer
        ) {

            return AppResponse::error(
                'The Walk-in Customer cannot be removed from a branch.'
            );
        }


        $customer
            ->stores()
            ->detach(
                $storeId
            );


        return AppResponse::successRoute(
            'customers.index',
            'Customer removed from this branch.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Customer $customer
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCustomerAvailable(
            $customer,
            $companyId,
            $storeId
        );


        if (
            $customer->is_system_customer
        ) {

            return AppResponse::error(
                'System customers cannot be deleted.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction Protection
        |--------------------------------------------------------------------------
        */

        if (
            $customer->sales()
            ->exists()
            ||
            $customer->payments()
            ->exists()
            ||
            $customer->quotations()
            ->exists()
        ) {

            return AppResponse::error(
                'This customer has transactions and cannot be permanently deleted. Remove the customer from this branch instead.'
            );
        }


        DB::transaction(
            function () use (
                $customer
            ) {

                $customer
                    ->stores()
                    ->detach();


                $customer->delete();
            }
        );


        return AppResponse::successRoute(
            'customers.index',
            'Customer deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Lookup
    |--------------------------------------------------------------------------
    |
    | Called by React while entering customer details.
    |
    */

    public function duplicateLookup(
        Request $request
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $validated =
            $request->validate([

                'gst_number' => [
                    'nullable',
                    'string',
                    'max:15',
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
            ]);


        $customer =
            $this->findDuplicate(
                companyId: $companyId,

                gstNumber: $validated['gst_number']
                    ?? null,

                mobile: $validated['mobile']
                    ?? null,

                email: $validated['email']
                    ?? null
            );


        if (
            !$customer
        ) {

            return response()->json([

                'found' =>
                false,
            ]);
        }


        $alreadyInBranch =
            $customer
            ->stores()
            ->where(
                'stores.id',
                $storeId
            )
            ->exists();


        return response()->json([

            'found' =>
            true,

            'already_in_branch' =>
            $alreadyInBranch,

            'customer' => [

                'id' =>
                $customer->id,

                'customer_code' =>
                $customer->customer_code,

                'customer_name' =>
                $customer->customer_name,

                'mobile' =>
                $customer->mobile,

                'email' =>
                $customer->email,

                'gst_number' =>
                $customer->gst_number,

                'city' =>
                $customer->city,

                'state' =>
                $customer->state,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GST Lookup
    |--------------------------------------------------------------------------
    |
    | The controller does NOT know which GST API provider is being used.
    |
    | GstLookupService handles the provider implementation.
    |
    */

    public function gstLookup(
        Request $request,
        GstLookupService $gstLookupService
    ) {

        $request->validate([

            'gst_number' => [
                'required',
                'string',
                'size:15',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/',
            ],
        ]);


        $gstNumber =
            strtoupper(
                trim(
                    $request->gst_number
                )
            );


        try {

            $result =
                $gstLookupService->lookup(
                    $gstNumber
                );


            return response()->json([

                'success' =>
                true,

                'data' =>
                $result,
            ]);
        } catch (
            \Throwable $exception
        ) {

            report(
                $exception
            );


            return response()->json(
                [

                    'success' =>
                    false,

                    'message' =>
                    'GST details could not be fetched. You can continue entering the customer manually.',
                ],
                422
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Customer
    |--------------------------------------------------------------------------
    */

    private function validateCustomer(
        Request $request,
        ?int $customerId = null
    ): array {

        [
            $companyId,
        ] = $this->context();


        return $request->validate([

            'customer_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'billing_address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'shipping_address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'pincode' => [
                'nullable',
                'string',
                'max:10',
            ],

            'gst_number' => [
                'nullable',
                'string',
                'size:15',
                'regex:/^[0-9]{2}[A-Za-z]{5}[0-9]{4}[A-Za-z][1-9A-Za-z]Z[0-9A-Za-z]$/',

                Rule::unique(
                    'customers',
                    'gst_number'
                )
                    ->where(
                        fn($query) =>
                        $query->where(
                            'company_id',
                            $companyId
                        )
                    )
                    ->ignore(
                        $customerId
                    ),
            ],

            'pan_number' => [
                'nullable',
                'string',
                'size:10',
                'regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/',
            ],

            'credit_limit' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'credit_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:3650',
            ],

            'opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'balance_type' => [
                'required',
                Rule::in([
                    'Dr',
                    'Cr',
                ]),
            ],

            'status' => [
                'required',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Customer Data
    |--------------------------------------------------------------------------
    */

    private function normalizeCustomerData(
        array $data
    ): array {

        $data['customer_name'] =
            trim(
                $data['customer_name']
            );

        foreach (
            [
                'contact_person',
                'phone',
                'billing_address',
                'shipping_address',
                'city',
                'state',
                'pincode',
                'remarks',
            ] as $field
        ) {

            $data[$field] =
                !empty($data[$field])
                ? trim($data[$field])
                : null;
        }

        $data['mobile'] =
            !empty($data['mobile'])
            ? preg_replace(
                '/\s+/',
                '',
                trim($data['mobile'])
            )
            : null;

        $data['email'] =
            !empty($data['email'])
            ? strtolower(
                trim($data['email'])
            )
            : null;

        $data['gst_number'] =
            !empty($data['gst_number'])
            ? strtoupper(
                trim($data['gst_number'])
            )
            : null;

        $data['pan_number'] =
            !empty($data['pan_number'])
            ? strtoupper(
                trim($data['pan_number'])
            )
            : null;

        $data['country'] =
            !empty($data['country'])
            ? trim($data['country'])
            : 'India';

        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection
    |--------------------------------------------------------------------------
    */

    private function findDuplicate(
        int $companyId,
        ?string $gstNumber = null,
        ?string $mobile = null,
        ?string $email = null,
        ?int $ignoreCustomerId = null
    ): ?Customer {

        $gstNumber =
            $gstNumber
            ? strtoupper(
                trim(
                    $gstNumber
                )
            )
            : null;


        $mobile =
            $mobile
            ? trim(
                $mobile
            )
            : null;


        $email =
            $email
            ? strtolower(
                trim(
                    $email
                )
            )
            : null;


        if (
            !$gstNumber &&
            !$mobile &&
            !$email
        ) {

            return null;
        }


        return Customer::query()

            ->where(
                'company_id',
                $companyId
            )

            ->when(
                $ignoreCustomerId,
                fn($query) =>
                $query->whereKeyNot(
                    $ignoreCustomerId
                )
            )

            ->where(
                function (
                    $query
                ) use (
                    $gstNumber,
                    $mobile,
                    $email
                ) {

                    if (
                        $gstNumber
                    ) {

                        $query->orWhere(
                            'gst_number',
                            $gstNumber
                        );
                    }


                    if (
                        $mobile
                    ) {

                        $query->orWhere(
                            'mobile',
                            $mobile
                        );
                    }


                    if (
                        $email
                    ) {

                        $query->orWhere(
                            'email',
                            $email
                        );
                    }
                }
            )

            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Code
    |--------------------------------------------------------------------------
    */

    private function generateCustomerCode(
        int $companyId
    ): string {

        $lastCustomer =
            Customer::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'customer_code',
                'like',
                'CUS%'
            )

            ->orderByDesc(
                'id'
            )

            ->first();


        $nextNumber = 1;


        if (
            $lastCustomer &&
            preg_match(
                '/^CUS(\d+)$/',
                $lastCustomer->customer_code,
                $matches
            )
        ) {

            $nextNumber =
                ((int) $matches[1]) + 1;
        }


        return 'CUS'
            .
            str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Context
    |--------------------------------------------------------------------------
    */

    private function context(): array
    {
        $companyId =
            (int) session(
                'active_company_id'
            );


        $storeId =
            (int) session(
                'active_store_id'
            );


        abort_if(
            !$companyId ||
                !$storeId,
            403,
            'Select an active company and branch before accessing customers.'
        );


        return [
            $companyId,
            $storeId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Access
    |--------------------------------------------------------------------------
    */

    private function ensureCustomerAvailable(
        Customer $customer,
        int $companyId,
        int $storeId
    ): void {

        abort_unless(
            (int) $customer->company_id ===
                $companyId,
            404
        );


        abort_unless(
            $customer
                ->stores()
                ->where(
                    'stores.id',
                    $storeId
                )
                ->exists(),
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Transactions
    |--------------------------------------------------------------------------
    */

    private function customerTransactions(
        Customer $customer,
        int $companyId,
        int $storeId
    ) {

        $paymentAmount =
            $this->paymentAmountColumn();


        $paymentDate =
            $this->paymentDateColumn();


        $paymentReference =
            $this->paymentReferenceColumn();


        $salesQuery =
            DB::table(
                'sales'
            )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'customer_id',
                $customer->id
            )
            ->selectRaw(
                "
                    id,
                    invoice_date as transaction_date,
                    'sale' as transaction_type,
                    invoice_no as reference_no,
                    grand_total as debit,
                    0 as credit
                    "
            );


        $paymentQuery =
            DB::table(
                'customer_payments'
            )
            ->where(
                'company_id',
                $companyId
            )
            ->when(
                Schema::hasColumn(
                    'customer_payments',
                    'store_id'
                ),
                fn($query) =>
                $query->where(
                    'store_id',
                    $storeId
                )
            )
            ->where(
                'customer_id',
                $customer->id
            )
            ->selectRaw(
                "
                    id,
                    {$paymentDate} as transaction_date,
                    'payment' as transaction_type,
                    {$paymentReference} as reference_no,
                    0 as debit,
                    {$paymentAmount} as credit
                    "
            );


        $union =
            $salesQuery->unionAll(
                $paymentQuery
            );


        $transactions =
            DB::query()
            ->fromSub(
                $union,
                'customer_transactions'
            )
            ->orderByDesc(
                'transaction_date'
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(
                15,
                ['*'],
                'transactions_page'
            )
            ->withQueryString();


        $runningBalance =
            0;


        $transactions->through(
            function (
                $row
            ) use (
                &$runningBalance
            ) {

                $debit =
                    (float) $row->debit;


                $credit =
                    (float) $row->credit;


                $runningBalance +=
                    $debit - $credit;


                return [
                    'id' =>
                    $row->id,

                    'date' =>
                    $row->transaction_date,

                    'type' =>
                    $row->transaction_type,

                    'reference' =>
                    $row->reference_no,

                    'debit' =>
                    $debit,

                    'credit' =>
                    $credit,

                    'page_balance' =>
                    $runningBalance,
                ];
            }
        );


        return $transactions;
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Schema Helpers
    |--------------------------------------------------------------------------
    |
    | These helpers make the Customer module compatible with the existing
    | customer_payments table even if the payment module uses amount,
    | paid_amount, payment_amount or similar naming.
    |
    */

    private function paymentAmountColumn(): string
    {
        foreach (
            [
                'amount',
                'payment_amount',
                'paid_amount',
            ] as $column
        ) {

            if (
                Schema::hasColumn(
                    'customer_payments',
                    $column
                )
            ) {

                return $column;
            }
        }


        return 'id';
    }


    private function paymentDateColumn(): string
    {
        foreach (
            [
                'payment_date',
                'date',
                'created_at',
            ] as $column
        ) {

            if (
                Schema::hasColumn(
                    'customer_payments',
                    $column
                )
            ) {

                return $column;
            }
        }


        return 'created_at';
    }


    private function paymentReferenceColumn(): string
    {
        foreach (
            [
                'receipt_no',
                'reference_no',
                'payment_no',
            ] as $column
        ) {

            if (
                Schema::hasColumn(
                    'customer_payments',
                    $column
                )
            ) {

                return $column;
            }
        }


        return 'id';
    }


    private function totalCustomerPayments(
        int $companyId,
        int $customerId,
        int $storeId
    ): float {

        $amountColumn =
            $this->paymentAmountColumn();


        if (
            $amountColumn === 'id'
        ) {

            return 0;
        }


        return (float)
        CustomerPayment::query()
            ->where(
                'company_id',
                $companyId
            )
            ->when(
                Schema::hasColumn(
                    'customer_payments',
                    'store_id'
                ),
                fn($query) =>
                $query->where(
                    'store_id',
                    $storeId
                )
            )
            ->where(
                'customer_id',
                $customerId
            )
            ->sum(
                $amountColumn
            );
    }


    private function paymentData(
        CustomerPayment $payment
    ): array {

        $amountColumn =
            $this->paymentAmountColumn();


        $dateColumn =
            $this->paymentDateColumn();


        $referenceColumn =
            $this->paymentReferenceColumn();


        return [
            'id' =>
            $payment->id,

            'date' =>
            $payment->{$dateColumn},

            'reference_no' =>
            $payment->{$referenceColumn},

            'amount' =>
            $amountColumn === 'id'
                ? 0
                : (float) $payment->{$amountColumn},

            'payment_method' =>
            $payment->payment_method
                ?? $payment->payment_mode
                ?? null,

            'remarks' =>
            $payment->remarks
                ?? null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | React Data
    |--------------------------------------------------------------------------
    */

    private function customerData(
        Customer $customer
    ): array {

        return [

            'id' =>
            $customer->id,

            'customer_code' =>
            $customer->customer_code,

            'customer_name' =>
            $customer->customer_name,

            'customer_type' =>
            $customer->customer_type,

            'is_system_customer' =>
            (bool) $customer->is_system_customer,

            'contact_person' =>
            $customer->contact_person,

            'mobile' =>
            $customer->mobile,

            'phone' =>
            $customer->phone,

            'email' =>
            $customer->email,

            'billing_address' =>
            $customer->billing_address,

            'shipping_address' =>
            $customer->shipping_address,

            'city' =>
            $customer->city,

            'state' =>
            $customer->state,

            'country' =>
            $customer->country,

            'pincode' =>
            $customer->pincode,

            'gst_number' =>
            $customer->gst_number,

            'pan_number' =>
            $customer->pan_number,

            'credit_limit' =>
            (float) $customer->credit_limit,

            'credit_days' =>
            (int) $customer->credit_days,

            'opening_balance' =>
            (float) $customer->opening_balance,

            'balance_type' =>
            $customer->balance_type,

            'status' =>
            (bool) $customer->status,

            'remarks' =>
            $customer->remarks,

            'shared' =>
            $customer->stores
                ->count() > 1,

            'stores_count' =>
            $customer->stores
                ->count(),

            'stores' =>
            $customer->stores

                ->map(
                    fn(
                        Store $store
                    ) => [

                        'id' =>
                        $store->id,

                        'store_name' =>
                        $store->store_name,

                        'store_code' =>
                        $store->store_code,

                        'status' =>
                        (bool) $store->status,
                    ]
                )

                ->values()

                ->all(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Toggle Customer Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Customer $customer
    ) {

        /*
        |--------------------------------------------------------------------------
        | Resolve Active Context
        |--------------------------------------------------------------------------
        */

        $companyId =
            (int) session(
                'active_company_id'
            );


        $storeId =
            (int) session(
                'active_store_id'
            );


        if (
            !$companyId ||
            !$storeId
        ) {

            abort(
                403,
                'No active company or branch selected.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Company Isolation
        |--------------------------------------------------------------------------
        */

        if (
            (int) $customer->company_id !==
            $companyId
        ) {

            abort(
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch Access
        |--------------------------------------------------------------------------
        */

        $belongsToBranch =
            $customer
            ->stores()
            ->where(
                'stores.id',
                $storeId
            )
            ->exists();


        if (
            !$belongsToBranch
        ) {

            abort(
                404
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Protect Walk-in Customer
        |--------------------------------------------------------------------------
        */

        if (
            $customer->is_system_customer
        ) {

            return AppResponse::error(
                'The Walk-in Customer is a protected system customer and cannot be deactivated.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Toggle
        |--------------------------------------------------------------------------
        */

        $newStatus =
            !$customer->status;


        try {

            $customer->update([
                'status' =>
                $newStatus,
            ]);
        } catch (
            \Throwable $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'Customer status could not be updated. Please try again.'
            );
        }


        return AppResponse::success(
            $newStatus
                ? 'Customer activated successfully.'
                : 'Customer deactivated successfully.'
        );
    }
}
