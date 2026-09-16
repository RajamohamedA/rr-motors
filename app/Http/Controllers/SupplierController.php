<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\GstLookupService;
use App\Support\AppResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Permission Middleware
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:supplier.view',
                only: [
                    'index',
                    'duplicateLookup',
                ]
            ),

            new Middleware(
                'permission:supplier.show',
                only: [
                    'show',
                ]
            ),

            new Middleware(
                'permission:supplier.create',
                only: [
                    'create',
                    'store',
                    'attachExisting',
                ]
            ),

            new Middleware(
                'permission:supplier.create|supplier.edit',
                only: [
                    'gstLookup',
                ]
            ),

            new Middleware(
                'permission:supplier.edit',
                only: [
                    'edit',
                    'update',
                    'toggleStatus',
                ]
            ),

            new Middleware(
                'permission:supplier.delete',
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


        $suppliers =
            Supplier::query()

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
                                'supplier_name',
                                'like',
                                "%{$search}%"
                            )

                                ->orWhere(
                                    'supplier_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'phone',
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

            ->orderBy(
                'supplier_name'
            )

            ->paginate(
                15
            )

            ->withQueryString();


        $suppliers->through(
            fn(
                Supplier $supplier
            ) => [

                'id' =>
                $supplier->id,

                'supplier_code' =>
                $supplier->supplier_code,

                'supplier_name' =>
                $supplier->supplier_name,

                'contact_person' =>
                $supplier->contact_person,

                'phone' =>
                $supplier->phone,

                'email' =>
                $supplier->email,

                'gst_number' =>
                $supplier->gst_number,

                'city' =>
                $supplier->city,

                'state' =>
                $supplier->state,

                'opening_balance' =>
                (float) $supplier->opening_balance,

                'credit_limit' =>
                (float) $supplier->credit_limit,

                'credit_days' =>
                (int) $supplier->credit_days,

                'balance_type' =>
                $supplier->balance_type,

                'status' =>
                (bool) $supplier->status,

                'stores_count' =>
                $supplier->stores_count,
            ]
        );


        $summaryBase =
            Supplier::query()

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

            'gst_suppliers' => (clone $summaryBase)
                ->whereNotNull(
                    'gst_number'
                )
                ->where(
                    'gst_number',
                    '!=',
                    ''
                )
                ->count(),

            'credit_suppliers' => (clone $summaryBase)
                ->where(
                    'credit_limit',
                    '>',
                    0
                )
                ->count(),
        ];


        return Inertia::render(
            'Suppliers/Index',
            [

                'suppliers' =>
                $suppliers,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status,
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
            'Suppliers/Create'
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
            $this->normalizeSupplierData(
                $this->validateSupplier(
                    $request
                )
            );


        $duplicate =
            $this->findDuplicate(
                companyId: $companyId,

                supplierName: $validated['supplier_name']
                    ?? null,

                gstNumber: $validated['gst_number']
                    ?? null,

                phone: $validated['phone']
                    ?? null,

                email: $validated['email']
                    ?? null
            );


        if (
            $duplicate
        ) {

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
                    "Supplier {$duplicate->supplier_name} already exists in this branch."
                );
            }


            return redirect()

                ->route(
                    'suppliers.create'
                )

                ->withInput()

                ->with(
                    'duplicate_supplier',
                    [

                        'id' =>
                        $duplicate->id,

                        'supplier_code' =>
                        $duplicate->supplier_code,

                        'supplier_name' =>
                        $duplicate->supplier_name,

                        'phone' =>
                        $duplicate->phone,

                        'email' =>
                        $duplicate->email,

                        'gst_number' =>
                        $duplicate->gst_number,

                        'city' =>
                        $duplicate->city,

                        'state' =>
                        $duplicate->state,

                        'message' =>
                        'This supplier already exists in another branch of this company.',
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
                        'Matching supplier found. Add the existing supplier to this branch instead of creating a duplicate.',
                    ]
                );
        }


        try {

            $supplier =
                DB::transaction(
                    function () use (
                        $validated,
                        $companyId,
                        $storeId
                    ) {

                        $supplier =
                            Supplier::create([

                                'company_id' =>
                                $companyId,

                                'supplier_code' =>
                                $this->generateSupplierCode(
                                    $companyId
                                ),

                                'supplier_name' =>
                                $validated['supplier_name'],

                                'contact_person' =>
                                $validated['contact_person']
                                    ?? null,

                                'phone' =>
                                $validated['phone']
                                    ?? null,

                                'alternate_phone' =>
                                $validated['alternate_phone']
                                    ?? null,

                                'email' =>
                                $validated['email']
                                    ?? null,

                                'gst_number' =>
                                $validated['gst_number']
                                    ?? null,

                                'pan_number' =>
                                $validated['pan_number']
                                    ?? null,

                                'address' =>
                                $validated['address']
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

                                'opening_balance' =>
                                $validated['opening_balance']
                                    ?? 0,

                                'credit_limit' =>
                                $validated['credit_limit']
                                    ?? 0,

                                'credit_days' =>
                                $validated['credit_days']
                                    ?? 0,

                                'balance_type' =>
                                $validated['balance_type']
                                    ?? 'Cr',

                                'status' =>
                                (bool) (
                                    $validated['status']
                                    ?? true
                                ),

                                'remarks' =>
                                $validated['remarks']
                                    ?? null,
                            ]);


                        $supplier
                            ->stores()
                            ->syncWithoutDetaching([
                                $storeId,
                            ]);


                        return $supplier;
                    }
                );
        } catch (
            QueryException $exception
        ) {

            if (
                ($exception->errorInfo[1] ?? null) === 1062
            ) {

                return AppResponse::warning(
                    'A supplier with the same name, GST number, or supplier code already exists in this company.'
                );
            }

            report(
                $exception
            );

            return AppResponse::error(
                'Supplier could not be created. Please try again.'
            );
        }


        return AppResponse::successRoute(
            'suppliers.show',
            'Supplier created successfully.',
            $supplier
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Supplier $supplier
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureSupplierAvailable(
            $supplier,
            $companyId,
            $storeId
        );


        $supplier->load([
            'stores:id,company_id,store_name,store_code,status',
        ]);


        $purchases =
            Purchase::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'supplier_id',
                $supplier->id
            )

            ->orderByDesc(
                'purchase_date'
            )

            ->orderByDesc(
                'id'
            )

            ->paginate(
                15,
                ['*'],
                'purchases_page'
            )

            ->withQueryString();


        $purchaseReturns =
            PurchaseReturn::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'supplier_id',
                $supplier->id
            )

            ->whereHas(
                'purchase',
                function (
                    $query
                ) use (
                    $storeId
                ) {

                    $query->where(
                        'store_id',
                        $storeId
                    );
                }
            )

            ->orderByDesc(
                'return_date'
            )

            ->orderByDesc(
                'id'
            )

            ->paginate(
                15,
                [
                    'id',
                    'company_id',
                    'purchase_id',
                    'supplier_id',
                    'return_no',
                    'return_date',
                    'sub_total',
                    'gst_total',
                    'total_amount',
                    'remarks',
                ],
                'returns_page'
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Supplier Payments
        |--------------------------------------------------------------------------
        |
        | Includes purchase-linked payments and unallocated supplier advances.
        | Cancelled documents remain visible for audit but do not affect balance.
        |
        */

        $supplierPayments =
            SupplierPayment::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'supplier_id',
                $supplier->id
            )

            ->with([
                'purchase:id,purchase_no,purchase_date,grand_total',
                'account:id,account_code,account_name,account_type,payment_mode',
                'creator:id,name',
                'canceller:id,name',
            ])

            ->orderByDesc(
                'payment_date'
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


        $supplierPayments->through(
            fn(
                SupplierPayment $payment
            ) => [

                'id' =>
                $payment->id,

                'payment_no' =>
                $payment->payment_no,

                'payment_date' =>
                $payment->payment_date
                    ?->format('Y-m-d'),

                'amount' =>
                (float) $payment->amount,

                'payment_mode' =>
                $payment->payment_mode,

                'reference_no' =>
                $payment->reference_no,

                'status' =>
                $payment->status,

                'remarks' =>
                $payment->remarks,

                'is_advance' =>
                $payment->purchase_id === null,

                'can_cancel' =>
                $payment->canBeCancelled(),

                'purchase' =>
                $payment->purchase
                    ? [
                        'id' =>
                        $payment->purchase->id,

                        'purchase_no' =>
                        $payment->purchase->purchase_no,

                        'purchase_date' =>
                        (string) $payment->purchase->purchase_date,

                        'grand_total' =>
                        (float) $payment->purchase->grand_total,
                    ]
                    : null,

                'account' =>
                $payment->account
                    ? [
                        'id' =>
                        $payment->account->id,

                        'account_code' =>
                        $payment->account->account_code,

                        'account_name' =>
                        $payment->account->account_name,

                        'account_type' =>
                        $payment->account->account_type,

                        'payment_mode' =>
                        $payment->account->payment_mode,
                    ]
                    : null,

                'creator' =>
                $payment->creator
                    ? [
                        'id' =>
                        $payment->creator->id,

                        'name' =>
                        $payment->creator->name,
                    ]
                    : null,
            ]
        );


        $totalPurchases =
            (float) Purchase::query()

                ->where(
                    'company_id',
                    $companyId
                )

                ->where(
                    'store_id',
                    $storeId
                )

                ->where(
                    'supplier_id',
                    $supplier->id
                )

                ->sum(
                    'grand_total'
                );


        $totalReturns =
            (float) PurchaseReturn::query()

                ->where(
                    'company_id',
                    $companyId
                )

                ->where(
                    'supplier_id',
                    $supplier->id
                )

                ->whereHas(
                    'purchase',
                    function (
                        $query
                    ) use (
                        $storeId
                    ) {

                        $query->where(
                            'store_id',
                            $storeId
                        );
                    }
                )

                ->sum(
                    'total_amount'
                );


        $completedPaymentsQuery =
            SupplierPayment::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'supplier_id',
                $supplier->id
            )

            ->where(
                'status',
                SupplierPayment::STATUS_COMPLETED
            );


        $totalPayments =
            (float) (clone $completedPaymentsQuery)
                ->sum(
                    'amount'
                );


        $allocatedPayments =
            (float) (clone $completedPaymentsQuery)
                ->whereNotNull(
                    'purchase_id'
                )
                ->sum(
                    'amount'
                );


        $advancePayments =
            (float) (clone $completedPaymentsQuery)
                ->whereNull(
                    'purchase_id'
                )
                ->sum(
                    'amount'
                );


        $openingBalance =
            (float) $supplier->opening_balance;


        $openingCredit =
            $supplier->balance_type === 'Cr'
            ? $openingBalance
            : 0;


        $openingDebit =
            $supplier->balance_type === 'Dr'
            ? $openingBalance
            : 0;


        $netSupplierBalance =
            round(
                $openingCredit
                    +
                    $totalPurchases
                    -
                    $totalReturns
                    -
                    $openingDebit
                    -
                    $totalPayments,
                2
            );


        $currentPayable =
            max(
                0,
                $netSupplierBalance
            );


        $supplierAdvanceBalance =
            max(
                0,
                -$netSupplierBalance
            );


        $creditLimit =
            (float) $supplier->credit_limit;


        $availableCredit =
            $creditLimit > 0
            ? max(
                0,
                $creditLimit - $currentPayable
            )
            : 0;


        $lastPurchase =
            Purchase::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'supplier_id',
                $supplier->id
            )

            ->latest(
                'purchase_date'
            )

            ->first();


        $lastPayment =
            SupplierPayment::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'supplier_id',
                $supplier->id
            )

            ->where(
                'status',
                SupplierPayment::STATUS_COMPLETED
            )

            ->latest(
                'payment_date'
            )

            ->latest(
                'id'
            )

            ->first();


        return Inertia::render(
            'Suppliers/Show',
            [

                'supplier' =>
                $this->supplierData(
                    $supplier
                ),

                'account_summary' => [

                    'opening_balance' =>
                    $openingBalance,

                    'balance_type' =>
                    $supplier->balance_type,

                    'current_payable' =>
                    $currentPayable,

                    'supplier_advance_balance' =>
                    $supplierAdvanceBalance,

                    'net_supplier_balance' =>
                    $netSupplierBalance,

                    'credit_limit' =>
                    $creditLimit,

                    'available_credit' =>
                    $availableCredit,

                    'credit_days' =>
                    (int) $supplier->credit_days,

                    'total_purchases' =>
                    $totalPurchases,

                    'total_returns' =>
                    $totalReturns,

                    'total_payments' =>
                    $totalPayments,

                    'allocated_payments' =>
                    $allocatedPayments,

                    'advance_payments' =>
                    $advancePayments,

                    'last_purchase' =>
                    $lastPurchase
                        ? [
                            'id' =>
                            $lastPurchase->id,

                            'purchase_no' =>
                            $lastPurchase->purchase_no
                                ?? null,

                            'date' =>
                            $lastPurchase->purchase_date,

                            'amount' =>
                            (float) (
                                $lastPurchase->grand_total
                                ?? 0
                            ),
                        ]
                        : null,

                    'last_payment' =>
                    $lastPayment
                        ? [
                            'id' =>
                            $lastPayment->id,

                            'payment_no' =>
                            $lastPayment->payment_no,

                            'date' =>
                            $lastPayment->payment_date,

                            'amount' =>
                            (float) $lastPayment->amount,

                            'is_advance' =>
                            $lastPayment->purchase_id === null,
                        ]
                        : null,
                ],

                'purchases' =>
                $purchases,

                'purchase_returns' =>
                $purchaseReturns,

                'supplier_payments' =>
                $supplierPayments,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Supplier $supplier
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureSupplierAvailable(
            $supplier,
            $companyId,
            $storeId
        );


        $supplier->load([
            'stores:id,company_id,store_name,store_code,status',
        ]);


        return Inertia::render(
            'Suppliers/Edit',
            [

                'supplier' =>
                $this->supplierData(
                    $supplier
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
        Supplier $supplier
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureSupplierAvailable(
            $supplier,
            $companyId,
            $storeId
        );


        $validated =
            $this->normalizeSupplierData(
                $this->validateSupplier(
                    $request,
                    $supplier->id
                )
            );


        $duplicate =
            $this->findDuplicate(
                companyId: $companyId,

                supplierName: $validated['supplier_name']
                    ?? null,

                gstNumber: $validated['gst_number']
                    ?? null,

                phone: $validated['phone']
                    ?? null,

                email: $validated['email']
                    ?? null,

                ignoreSupplierId: $supplier->id
            );


        if (
            $duplicate
        ) {

            return AppResponse::warning(
                "Another supplier already uses the same GST, phone or email: {$duplicate->supplier_name}."
            );
        }


        try {

            $supplier->update([

                'supplier_name' =>
                $validated['supplier_name'],

                'contact_person' =>
                $validated['contact_person']
                    ?? null,

                'phone' =>
                $validated['phone']
                    ?? null,

                'alternate_phone' =>
                $validated['alternate_phone']
                    ?? null,

                'email' =>
                $validated['email']
                    ?? null,

                'gst_number' =>
                $validated['gst_number']
                    ?? null,

                'pan_number' =>
                $validated['pan_number']
                    ?? null,

                'address' =>
                $validated['address']
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

                'opening_balance' =>
                $validated['opening_balance']
                    ?? 0,

                'credit_limit' =>
                $validated['credit_limit']
                    ?? 0,

                'credit_days' =>
                $validated['credit_days']
                    ?? 0,

                'balance_type' =>
                $validated['balance_type']
                    ?? 'Cr',

                'status' =>
                (bool) (
                    $validated['status']
                    ?? true
                ),

                'remarks' =>
                $validated['remarks']
                    ?? null,
            ]);
        } catch (
            QueryException $exception
        ) {

            if (
                ($exception->errorInfo[1] ?? null) === 1062
            ) {

                return AppResponse::warning(
                    'Another supplier already uses the same supplier name, GST number, or supplier code.'
                );
            }

            report(
                $exception
            );

            return AppResponse::error(
                'Supplier could not be updated. Please try again.'
            );
        }


        return AppResponse::successRoute(
            'suppliers.show',
            'Supplier updated successfully.',
            $supplier
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Attach Existing Supplier
    |--------------------------------------------------------------------------
    */

    public function attachExisting(
        Supplier $supplier
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        abort_unless(
            (int) $supplier->company_id ===
                $companyId,
            404
        );


        if (
            !$supplier->status
        ) {

            return AppResponse::error(
                'Inactive suppliers cannot be added to this branch.'
            );
        }


        $supplier
            ->stores()
            ->syncWithoutDetaching([
                $storeId,
            ]);


        return AppResponse::successRoute(
            'suppliers.show',
            'Supplier added to this branch successfully.',
            $supplier
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Remove From Current Branch
    |--------------------------------------------------------------------------
    */

    public function removeFromBranch(
        Supplier $supplier
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureSupplierAvailable(
            $supplier,
            $companyId,
            $storeId
        );


        $hasPurchases =
            Purchase::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'supplier_id',
                $supplier->id
            )

            ->exists();


        if (
            $hasPurchases
        ) {

            return AppResponse::error(
                'This supplier has purchases in the current branch and cannot be removed from this branch.'
            );
        }


        $supplier
            ->stores()
            ->detach(
                $storeId
            );


        return AppResponse::successRoute(
            'suppliers.index',
            'Supplier removed from this branch.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Supplier $supplier
    ): RedirectResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureSupplierAvailable(
            $supplier,
            $companyId,
            $storeId
        );


        if (
            $supplier
            ->purchases()
            ->exists()
        ) {

            return AppResponse::error(
                'This supplier has purchase transactions and cannot be permanently deleted.'
            );
        }


        DB::transaction(
            function () use (
                $supplier
            ) {

                $supplier
                    ->stores()
                    ->detach();


                $supplier->delete();
            }
        );


        return AppResponse::successRoute(
            'suppliers.index',
            'Supplier deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Lookup
    |--------------------------------------------------------------------------
    */

    public function duplicateLookup(
        Request $request
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        /*
        |--------------------------------------------------------------------------
        | Validate Lookup Request
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'supplier_name' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'gst_number' => [
                    'nullable',
                    'string',
                    'max:15',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                /*
                |--------------------------------------------------------------------------
                | Edit Page Only
                |--------------------------------------------------------------------------
                |
                | Create page will send null.
                | Edit page sends the current supplier ID so it does not
                | detect itself as a duplicate.
                |
                */

                'ignore_supplier_id' => [
                    'nullable',
                    'integer',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Validate Ignore Supplier
        |--------------------------------------------------------------------------
        |
        | Never blindly trust the ID coming from React.
        |
        */

        $ignoreSupplierId =
            isset(
                $validated['ignore_supplier_id']
            )
            ? (int) $validated['ignore_supplier_id']
            : null;


        if (
            $ignoreSupplierId
        ) {

            $validSupplier =
                Supplier::query()

                ->where(
                    'id',
                    $ignoreSupplierId
                )

                ->where(
                    'company_id',
                    $companyId
                )

                ->exists();


            if (
                !$validSupplier
            ) {

                $ignoreSupplierId =
                    null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Find Duplicate
        |--------------------------------------------------------------------------
        */

        $supplier =
            $this->findDuplicate(

                companyId: $companyId,

                supplierName: $validated['supplier_name']
                    ?? null,

                gstNumber: $validated['gst_number']
                    ?? null,

                phone: $validated['phone']
                    ?? null,

                email: $validated['email']
                    ?? null,

                ignoreSupplierId: $ignoreSupplierId
            );


        /*
        |--------------------------------------------------------------------------
        | No Duplicate
        |--------------------------------------------------------------------------
        */

        if (
            !$supplier
        ) {

            return response()->json([

                'found' =>
                false,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Check Current Branch
        |--------------------------------------------------------------------------
        */

        $alreadyInBranch =
            $supplier

            ->stores()

            ->where(
                'stores.id',
                $storeId
            )

            ->exists();


        /*
        |--------------------------------------------------------------------------
        | Duplicate Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'found' =>
            true,

            'already_in_branch' =>
            $alreadyInBranch,

            'supplier' => [

                'id' =>
                $supplier->id,

                'supplier_code' =>
                $supplier->supplier_code,

                'supplier_name' =>
                $supplier->supplier_name,

                'phone' =>
                $supplier->phone,

                'email' =>
                $supplier->email,

                'gst_number' =>
                $supplier->gst_number,

                'city' =>
                $supplier->city,

                'state' =>
                $supplier->state,
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | GST Lookup
    |--------------------------------------------------------------------------
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
                    'GST details could not be fetched. You can continue entering the supplier manually.',
                ],
                422
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateSupplier(
        Request $request,
        ?int $supplierId = null
    ): array {

        [
            $companyId,
        ] = $this->context();


        return $request->validate([

            'supplier_name' => [
                'required',
                'string',
                'max:150',

                Rule::unique(
                    'suppliers',
                    'supplier_name'
                )
                    ->where(
                        fn($query) =>
                        $query->where(
                            'company_id',
                            $companyId
                        )
                    )
                    ->ignore(
                        $supplierId
                    ),
            ],

            'contact_person' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'alternate_phone' => [
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
                'size:15',
                'regex:/^[0-9]{2}[A-Za-z]{5}[0-9]{4}[A-Za-z][1-9A-Za-z]Z[0-9A-Za-z]$/',

                Rule::unique(
                    'suppliers',
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
                        $supplierId
                    ),
            ],

            'pan_number' => [
                'nullable',
                'string',
                'size:10',
                'regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/',
            ],

            'address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'state' => [
                'nullable',
                'string',
                'max:255',
            ],

            'country' => [
                'nullable',
                'string',
                'max:255',
            ],

            'pincode' => [
                'nullable',
                'string',
                'max:10',
            ],

            'opening_balance' => [
                'nullable',
                'numeric',
                'min:0',
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
    | Normalize Supplier Data
    |--------------------------------------------------------------------------
    */

    private function normalizeSupplierData(
        array $data
    ): array {

        $data['supplier_name'] =
            trim(
                $data['supplier_name']
            );


        foreach (
            [
                'contact_person',
                'phone',
                'alternate_phone',
                'address',
                'city',
                'state',
                'country',
                'pincode',
                'remarks',
            ] as $field
        ) {

            $data[$field] =
                !empty($data[$field])
                ? trim(
                    $data[$field]
                )
                : null;
        }


        $data['country'] =
            $data['country']
            ?? 'India';


        $data['email'] =
            !empty($data['email'])
            ? strtolower(
                trim(
                    $data['email']
                )
            )
            : null;


        $data['gst_number'] =
            !empty($data['gst_number'])
            ? strtoupper(
                trim(
                    $data['gst_number']
                )
            )
            : null;


        $data['pan_number'] =
            !empty($data['pan_number'])
            ? strtoupper(
                trim(
                    $data['pan_number']
                )
            )
            : null;


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection
    |--------------------------------------------------------------------------
    */

    private function findDuplicate(
        int $companyId,
        ?string $supplierName = null,
        ?string $gstNumber = null,
        ?string $phone = null,
        ?string $email = null,
        ?int $ignoreSupplierId = null
    ): ?Supplier {

        $supplierName =
            $supplierName
            ? trim(
                $supplierName
            )
            : null;

        $gstNumber =
            $gstNumber
            ? strtoupper(
                trim(
                    $gstNumber
                )
            )
            : null;

        $phone =
            $phone
            ? trim(
                $phone
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
            !$supplierName &&
            !$gstNumber &&
            !$phone &&
            !$email
        ) {

            return null;
        }


        return Supplier::query()

            ->where(
                'company_id',
                $companyId
            )

            ->when(
                $ignoreSupplierId,
                fn($query) =>
                $query->whereKeyNot(
                    $ignoreSupplierId
                )
            )

            ->where(
                function (
                    $query
                ) use (
                    $supplierName,
                    $gstNumber,
                    $phone,
                    $email
                ) {

                    if (
                        $supplierName
                    ) {

                        $query->orWhere(
                            'supplier_name',
                            $supplierName
                        );
                    }


                    if (
                        $gstNumber
                    ) {

                        $query->orWhere(
                            'gst_number',
                            $gstNumber
                        );
                    }


                    if (
                        $phone
                    ) {

                        $query->orWhere(
                            'phone',
                            $phone
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
    | Supplier Code
    |--------------------------------------------------------------------------
    */

    private function generateSupplierCode(
        int $companyId
    ): string {

        $lastSupplier =
            Supplier::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'supplier_code',
                'like',
                'SUP%'
            )

            ->orderByDesc(
                'id'
            )

            ->first();


        $nextNumber =
            1;


        if (
            $lastSupplier &&
            preg_match(
                '/^SUP(\d+)$/',
                $lastSupplier->supplier_code,
                $matches
            )
        ) {

            $nextNumber =
                ((int) $matches[1]) + 1;
        }


        return 'SUP'
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
            'Select an active company and branch before accessing suppliers.'
        );


        return [
            $companyId,
            $storeId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier Access
    |--------------------------------------------------------------------------
    */

    private function ensureSupplierAvailable(
        Supplier $supplier,
        int $companyId,
        int $storeId
    ): void {

        abort_unless(
            (int) $supplier->company_id ===
                $companyId,
            404
        );


        abort_unless(
            $supplier
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
    | React Supplier Data
    |--------------------------------------------------------------------------
    */

    private function supplierData(
        Supplier $supplier
    ): array {

        return [

            'id' =>
            $supplier->id,

            'supplier_code' =>
            $supplier->supplier_code,

            'supplier_name' =>
            $supplier->supplier_name,

            'contact_person' =>
            $supplier->contact_person,

            'phone' =>
            $supplier->phone,

            'alternate_phone' =>
            $supplier->alternate_phone,

            'email' =>
            $supplier->email,

            'gst_number' =>
            $supplier->gst_number,

            'pan_number' =>
            $supplier->pan_number,

            'address' =>
            $supplier->address,

            'city' =>
            $supplier->city,

            'state' =>
            $supplier->state,

            'country' =>
            $supplier->country,

            'pincode' =>
            $supplier->pincode,

            'opening_balance' =>
            (float) $supplier->opening_balance,

            'credit_limit' =>
            (float) $supplier->credit_limit,

            'credit_days' =>
            (int) $supplier->credit_days,

            'balance_type' =>
            $supplier->balance_type,

            'status' =>
            (bool) $supplier->status,

            'remarks' =>
            $supplier->remarks,

            'shared' =>
            $supplier->stores
                ->count() > 1,

            'stores_count' =>
            $supplier->stores
                ->count(),

            'stores' =>
            $supplier->stores

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


    public function toggleStatus(
        Supplier $supplier
    ) {
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


        if (
            (int) $supplier->company_id !==
            $companyId
        ) {
            abort(
                404
            );
        }


        $belongsToBranch =
            $supplier
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


        $newStatus =
            !$supplier->status;


        try {

            $supplier->update([
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
                'Supplier status could not be updated. Please try again.'
            );
        }


        return AppResponse::success(
            $newStatus
                ? 'Supplier activated successfully.'
                : 'Supplier deactivated successfully.'
        );
    }
}
