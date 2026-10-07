<?php



namespace App\Http\Controllers;



use App\Models\Account;

use App\Models\AccountTransaction;

use App\Models\Customer;

use App\Models\CustomerLedgerEntry;

use App\Models\CustomerPayment;

use App\Models\CustomerPaymentAllocation;

use App\Models\Sales;

use App\Models\Store;

use App\Services\AccountTransactionService;

use App\Services\CustomerLedgerService;

use App\Support\AppResponse;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Contracts\View\View as ViewContract;

use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Routing\Controllers\HasMiddleware;

use Illuminate\Routing\Controllers\Middleware;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

use Illuminate\Validation\Rule;

use Illuminate\Validation\ValidationException;

use Inertia\Inertia;

use Inertia\Response;

use Symfony\Component\HttpFoundation\Response as HttpResponse;



class CustomerPaymentController extends Controller implements HasMiddleware

{

    public static function middleware(): array

    {

        return [

            new Middleware(

                'permission:customer-payment.view',

                only: [

                    'index',

                    'show',

                    'print',

                    'pdf',

                    'searchCustomers',

                    'customerSales',

                    'searchInvoice',

                    'invoiceDetails',

                ]

            ),

            new Middleware(

                'permission:customer-payment.create',

                only: [

                    'create',

                    'store',

                    'cancel',

                ]

            ),

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------

    */



    public function index(Request $request): Response

    {

        [$companyId, $storeId] = $this->context();



        $validated = $request->validate([

            'search' => ['nullable', 'string', 'max:100'],

            'status' => ['nullable', 'string', 'max:20'],

            'payment_mode' => ['nullable', 'string', 'max:30'],

            'from_date' => ['nullable', 'date'],

            'to_date' => [

                'nullable',

                'date',

                'after_or_equal:from_date',

            ],

            'per_page' => ['nullable', 'integer', 'in:20,50,100'],

        ]);



        $search = trim((string) ($validated['search'] ?? ''));

        $status = (string) ($validated['status'] ?? '');

        $paymentMode = (string) ($validated['payment_mode'] ?? '');

        $fromDate = $validated['from_date'] ?? null;

        $toDate = $validated['to_date'] ?? null;

        $perPage = (int) ($validated['per_page'] ?? 20);



        $baseQuery = CustomerPayment::query()

            ->forContext($companyId, $storeId)

            ->when($search !== '', function (Builder $query) use ($search): void {

                $query->where(function (Builder $inner) use ($search): void {

                    $inner

                        ->where('receipt_no', 'like', "%{$search}%")

                        ->orWhere('reference_no', 'like', "%{$search}%")

                        ->orWhereHas('customer', function (Builder $customer) use ($search): void {

                            $customer

                                ->where('customer_name', 'like', "%{$search}%")

                                ->orWhere('customer_code', 'like', "%{$search}%")

                                ->orWhere('mobile', 'like', "%{$search}%");
                        });
                });
            })

            ->when(

                in_array($status, [

                    CustomerPayment::STATUS_COMPLETED,

                    CustomerPayment::STATUS_CANCELLED,

                ], true),

                fn(Builder $query) => $query->where('status', $status)

            )

            ->when(

                $paymentMode !== '',

                fn(Builder $query) => $query->where('payment_mode', $paymentMode)

            )

            ->when(

                $fromDate,

                fn(Builder $query) => $query->whereDate('payment_date', '>=', $fromDate)

            )

            ->when(

                $toDate,

                fn(Builder $query) => $query->whereDate('payment_date', '<=', $toDate)

            );



        $payments = (clone $baseQuery)

            ->with([

                'customer:id,customer_code,customer_name,mobile',

                'account:id,account_code,account_name,account_type,payment_mode',

                'allocations.sale:id,invoice_no,invoice_date,grand_total',

                'creator:id,name',

                'canceller:id,name',

            ])

            ->orderByDesc('payment_date')

            ->orderByDesc('id')

            ->paginate($perPage)

            ->withQueryString();



        $payments->through(

            fn(CustomerPayment $payment): array => $this->paymentData($payment)

        );



        $summaryBase = CustomerPayment::query()

            ->forContext($companyId, $storeId)

            ->when(

                $fromDate,

                fn(Builder $query) => $query->whereDate('payment_date', '>=', $fromDate)

            )

            ->when(

                $toDate,

                fn(Builder $query) => $query->whereDate('payment_date', '<=', $toDate)

            );



        /*
    | Available advance is the total credit balance in the customer
    | ledger. Receipt allocations alone cannot detect advance that was
    | automatically consumed by a later sales invoice.

        */

        $advanceAmount = CustomerLedgerEntry::query()

            ->forContext($companyId, $storeId)

            ->selectRaw('customer_id, SUM(debit - credit) AS balance')

            ->groupBy('customer_id')

            ->havingRaw('SUM(debit - credit) < 0')

            ->get()

            ->sum(

                fn(CustomerLedgerEntry $entry): float =>

                abs((float) $entry->balance)

            );



        $summary = [

            'completed_amount' => (float) (clone $summaryBase)

                ->completed()

                ->sum('amount'),

            'completed_count' => (clone $summaryBase)

                ->completed()

                ->count(),

            'cancelled_amount' => (float) (clone $summaryBase)

                ->where('status', CustomerPayment::STATUS_CANCELLED)

                ->sum('amount'),

            'advance_amount' => round($advanceAmount, 2),

        ];



        return Inertia::render('CustomerPayments/Index', [

            'payments' => $payments,

            'summary' => $summary,

            'filters' => [

                'search' => $search,

                'status' => $status,

                'payment_mode' => $paymentMode,

                'from_date' => $fromDate,

                'to_date' => $toDate,

                'per_page' => $perPage,

            ],

            'payment_modes' => $this->paymentModes(),

        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------

    */



    public function create(Request $request): Response

    {

        [$companyId, $storeId] = $this->context();



        $accounts = Account::query()

            ->where('company_id', $companyId)

            ->where('store_id', $storeId)

            ->where('status', true)

            ->orderBy('account_type')

            ->orderBy('account_name')

            ->get([

                'id',

                'account_code',

                'account_name',

                'account_type',

                'payment_mode',

                'bank_name',

            ]);



        $selectedCustomer = null;



        if ($request->filled('customer')) {

            $customer = $this->customerQuery($companyId, $storeId)

                ->find((int) $request->input('customer'));



            if ($customer) {

                $selectedCustomer = $this->customerData(

                    $customer,

                    $companyId,

                    $storeId

                );
            }
        }



        return Inertia::render('CustomerPayments/Create', [

            'accounts' => $accounts,

            'payment_modes' => $this->paymentModes(),

            'selected_customer' => $selectedCustomer,

            'today' => now()->toDateString(),

        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Store Receipt
    |--------------------------------------------------------------------------

    */



    public function store(

        Request $request,

        CustomerLedgerService $customerLedgerService,

        AccountTransactionService $accountTransactionService

    ): RedirectResponse {

        [$companyId, $storeId] = $this->context();



        $validated = $request->validate([

            'customer_id' => ['required', 'integer'],

            'account_id' => ['required', 'integer'],

            'payment_date' => ['required', 'date'],

            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],

            'payment_mode' => [

                'required',

                'string',

                Rule::in($this->paymentModes()),

            ],

            'reference_no' => ['nullable', 'string', 'max:150'],

            'remarks' => ['nullable', 'string', 'max:2000'],

            'allocations' => ['nullable', 'array'],

            'allocations.\*.sale_id' => [

                'required',

                'integer',

                'distinct',

            ],

            'allocations.\*.amount' => [

                'required',

                'numeric',

                'gt:0',

                'decimal:0,2',

            ],

        ]);



        try {

            $payment = DB::transaction(function () use (

                $validated,

                $companyId,

                $storeId,

                $customerLedgerService,

                $accountTransactionService

            ): CustomerPayment {

                $customer = $this->customerQuery($companyId, $storeId)

                    ->lockForUpdate()

                    ->findOrFail((int) $validated['customer_id']);



                $account = Account::query()

                    ->where('company_id', $companyId)

                    ->where('store_id', $storeId)

                    ->where('status', true)

                    ->lockForUpdate()

                    ->findOrFail((int) $validated['account_id']);



                if (!$this->accountSupportsPaymentMode($account, $validated['payment_mode'])) {

                    throw ValidationException::withMessages([

                        'payment_mode' =>

                        'The payment mode does not match the selected cash/bank account.',

                    ]);
                }



                $allocations = collect($validated['allocations'] ?? [])

                    ->map(fn(array $allocation): array => [

                        'sale_id' => (int) $allocation['sale_id'],

                        'amount' => round((float) $allocation['amount'], 2),

                    ])

                    ->filter(fn(array $allocation): bool => $allocation['amount'] > 0)

                    ->values();



                $allocatedTotal = round((float) $allocations->sum('amount'), 2);

                $paymentAmount = round((float) $validated['amount'], 2);



                if ($allocatedTotal > $paymentAmount + 0.009) {

                    throw ValidationException::withMessages([

                        'allocations' =>

                        'The allocated invoice amount cannot exceed the receipt amount.',

                    ]);
                }



                $payment = CustomerPayment::create([

                    'company_id' => $companyId,

                    'store_id' => $storeId,

                    'customer_id' => $customer->id,

                    'sale_id' => null,

                    'account_id' => $account->id,

                    'receipt_no' => 'TMP-' . Str::uuid(),

                    'payment_date' => $validated['payment_date'],

                    'amount' => $paymentAmount,

                    'payment_mode' => $validated['payment_mode'],

                    'reference_no' => $this->nullableString($validated['reference_no'] ?? null),

                    'status' => CustomerPayment::STATUS_COMPLETED,

                    'remarks' => $this->nullableString($validated['remarks'] ?? null),

                    'created_by' => auth()->id(),

                ]);



                $payment->update([

                    'receipt_no' => 'RCPT' . str_pad(

                        (string) $payment->id,

                        6,

                        '0',

                        STR_PAD_LEFT

                    ),

                ]);



                foreach ($allocations as $allocation) {

                    $sale = Sales::query()

                        ->where('company_id', $companyId)

                        ->where('store_id', $storeId)

                        ->where('customer_id', $customer->id)

                        ->where('status', '!=', 'Cancelled')

                        ->lockForUpdate()

                        ->findOrFail($allocation['sale_id']);



                    $outstanding = round((float) $sale->balance_amount, 2);



                    if ($allocation['amount'] > $outstanding + 0.009) {

                        throw ValidationException::withMessages([

                            'allocations' => sprintf(

                                'Allocation for invoice %s cannot exceed its outstanding amount of %.2f.',

                                $sale->invoice_no,

                                $outstanding

                            ),

                        ]);
                    }



                    CustomerPaymentAllocation::create([

                        'company_id' => $companyId,

                        'store_id' => $storeId,

                        'customer_payment_id' => $payment->id,

                        'sale_id' => $sale->id,

                        'allocated_amount' => $allocation['amount'],

                    ]);



                    $newPaid = round((float) $sale->paid_amount + $allocation['amount'], 2);

                    $newBalance = max(0, round((float) $sale->balance_amount - $allocation['amount'], 2));



                    $sale->update([

                        'paid_amount' => $newPaid,

                        'balance_amount' => $newBalance,

                        'payment_status' => $this->salePaymentStatus($newPaid, $newBalance),

                    ]);
                }



                $accountTransactionService->postCustomerReceipt(

                    $payment,

                    auth()->id()

                );



                $customerLedgerService->postCustomerPayment(

                    $payment,

                    auth()->id()

                );



                return $payment;
            }, 3);
        } catch (ValidationException $exception) {

            throw $exception;
        } catch (\Throwable $exception) {

            report($exception);



            return AppResponse::error(

                'Customer payment could not be saved. Please try again.'

            );
        }



        return AppResponse::successRoute(

            'customer-payments.show',

            'Customer payment recorded successfully.',

            $payment

        );
    }



    /*
    |--------------------------------------------------------------------------
    | Show / Print / PDF
    |--------------------------------------------------------------------------

    */



    public function show(CustomerPayment $customerPayment): Response

    {

        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable($customerPayment, $companyId, $storeId);

        $this->loadDocumentRelations($customerPayment);



        $accountTransaction = AccountTransaction::query()

            ->where('company_id', $companyId)

            ->where('store_id', $storeId)

            ->where('reference_type', 'customer_payment')

            ->where('reference_id', $customerPayment->id)

            ->where('is_reversal', false)

            ->first();



        $ledgerEntries = CustomerLedgerEntry::query()

            ->forContext($companyId, $storeId)

            ->forReference('customer_payment', $customerPayment->id)

            ->orderBy('transaction_date')

            ->orderBy('id')

            ->get();



        return Inertia::render('CustomerPayments/Show', [

            'payment' => $this->paymentData($customerPayment),

            'account_transaction' => $accountTransaction,

            'customer_ledger_entries' => $ledgerEntries,

        ]);
    }



    public function print(CustomerPayment $customerPayment): ViewContract

    {

        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable($customerPayment, $companyId, $storeId);

        $this->loadDocumentRelations($customerPayment);



        return view('customer_payments.print', [

            'payment' => $customerPayment,

            'paper' => 'a4',

            'pdfMode' => false,

        ]);
    }



    public function pdf(CustomerPayment $customerPayment): HttpResponse

    {

        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable($customerPayment, $companyId, $storeId);

        $this->loadDocumentRelations($customerPayment);



        return Pdf::loadView('customer_payments.pdf', [

            'payment' => $customerPayment,

            'paper' => 'a4',

        ])

            ->setPaper('a4', 'portrait')

            ->download("customer-payment-{$customerPayment->receipt_no}.pdf");
    }



    /*
    |--------------------------------------------------------------------------
    | Cancel With Reversals
    |--------------------------------------------------------------------------

    */



    public function cancel(

        CustomerPayment $customerPayment,

        CustomerLedgerService $customerLedgerService,

        AccountTransactionService $accountTransactionService

    ): RedirectResponse {

        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable($customerPayment, $companyId, $storeId);



        try {

            DB::transaction(function () use (

                $customerPayment,

                $companyId,

                $storeId,

                $customerLedgerService,

                $accountTransactionService

            ): void {

                $payment = CustomerPayment::query()

                    ->forContext($companyId, $storeId)

                    ->lockForUpdate()

                    ->findOrFail($customerPayment->id);



                if ($payment->status !== CustomerPayment::STATUS_COMPLETED) {

                    throw ValidationException::withMessages([

                        'payment' => 'This customer payment is already cancelled.',

                    ]);
                }



                /*
    | Do not cancel an advance receipt after its credit has been
    | consumed by a sales invoice. The sale must first be edited
    | or cancelled so its advance_used value remains consistent.

                */

                $allocatedTotal = (float) CustomerPaymentAllocation::query()

                    ->forContext($companyId, $storeId)

                    ->forPayment($payment->id)

                    ->sum('allocated_amount');



                $unallocatedAmount = max(

                    0,

                    round((float) $payment->amount - $allocatedTotal, 2)

                );



                $availableAdvance = max(

                    0,

                    -$customerLedgerService->currentBalance(

                        $companyId,

                        $storeId,

                        (int) $payment->customer_id

                    )

                );



                if ($unallocatedAmount > $availableAdvance + 0.009) {

                    throw ValidationException::withMessages([

                        'payment' =>

                        'This receipt advance has already been used in a sales invoice. Cancel or edit that sale before cancelling this receipt.',

                    ]);
                }



                $accountReversals = $accountTransactionService->reverseReference(

                    companyId: $companyId,

                    storeId: $storeId,

                    referenceType: 'customer_payment',

                    referenceId: (int) $payment->id,

                    remarks: "Reversal of customer receipt {$payment->receipt_no}",

                    createdBy: auth()->id(),

                    transactionDate: now()->toDateString()

                );



                if ($accountReversals === []) {

                    throw ValidationException::withMessages([

                        'payment' =>

                        'The customer receipt account transaction is missing or has already been reversed.',

                    ]);
                }



                $allocations = CustomerPaymentAllocation::query()

                    ->forContext($companyId, $storeId)

                    ->forPayment($payment->id)

                    ->lockForUpdate()

                    ->get();



                foreach ($allocations as $allocation) {

                    $sale = Sales::query()

                        ->where('company_id', $companyId)

                        ->where('store_id', $storeId)

                        ->where('customer_id', $payment->customer_id)

                        ->lockForUpdate()

                        ->findOrFail($allocation->sale_id);



                    $newPaid = max(0, round((float) $sale->paid_amount - (float) $allocation->allocated_amount, 2));

                    $newBalance = min(

                        (float) $sale->grand_total,

                        round((float) $sale->balance_amount + (float) $allocation->allocated_amount, 2)

                    );



                    $sale->update([

                        'paid_amount' => $newPaid,

                        'balance_amount' => $newBalance,

                        'payment_status' => $this->salePaymentStatus($newPaid, $newBalance),

                    ]);
                }



                $customerLedgerService->reverseCustomerPayment(

                    payment: $payment,

                    reversalDate: now(),

                    remarks: "Reversal of customer receipt {$payment->receipt_no}",

                    createdBy: auth()->id()

                );



                $payment->update([

                    'status' => CustomerPayment::STATUS_CANCELLED,

                    'cancelled_by' => auth()->id(),

                    'cancelled_at' => now(),

                ]);
            }, 3);
        } catch (ValidationException $exception) {

            return AppResponse::warning(

                collect($exception->errors())->flatten()->first()

                    ?? 'Customer payment cannot be cancelled.'

            );
        } catch (\Throwable $exception) {

            report($exception);



            return AppResponse::error(

                'Customer payment could not be cancelled. Please try again.'

            );
        }



        return AppResponse::success(

            'Customer payment cancelled and reversing entries created successfully.'

        );
    }



    /*
    |--------------------------------------------------------------------------
    | AJAX Customer Search / Outstanding Sales
    |--------------------------------------------------------------------------

    */



    public function searchCustomers(Request $request): JsonResponse

    {

        [$companyId, $storeId] = $this->context();

        $search = trim($request->string('search')->toString());



        $customers = $this->customerQuery($companyId, $storeId)

            ->when($search !== '', function (Builder $query) use ($search): void {

                $query->where(function (Builder $inner) use ($search): void {

                    $inner

                        ->where('customer_name', 'like', "%{$search}%")

                        ->orWhere('customer_code', 'like', "%{$search}%")

                        ->orWhere('mobile', 'like', "%{$search}%")

                        ->orWhere('gst_number', 'like', "%{$search}%");
                });
            })

            ->orderBy('customer_name')

            ->limit(20)

            ->get()

            ->map(fn(Customer $customer): array => $this->customerData(

                $customer,

                $companyId,

                $storeId

            ))

            ->values();



        return response()->json(['data' => $customers]);
    }



    public function customerSales(

        Customer $customer

    ): JsonResponse {

        [$companyId, $storeId] = $this->context();

        $this->ensureCustomerAvailable($customer, $companyId, $storeId);



        $sales = Sales::query()

            ->where('company_id', $companyId)

            ->where('store_id', $storeId)

            ->where('customer_id', $customer->id)

            ->where('status', '!=', 'Cancelled')

            ->where('balance_amount', '>', 0)

            ->orderBy('invoice_date')

            ->orderBy('id')

            ->get([

                'id',

                'invoice_no',

                'invoice_date',

                'grand_total',

                'advance_used',

                'paid_amount',

                'balance_amount',

                'payment_status',

            ])

            ->map(fn(Sales $sale): array => [

                'id' => $sale->id,

                'invoice_no' => $sale->invoice_no,

                'invoice_date' => $sale->invoice_date?->format('Y-m-d'),

                'grand_total' => (float) $sale->grand_total,

                'advance_used' => (float) $sale->advance_used,

                'paid_amount' => (float) $sale->paid_amount,

                'direct_paid_amount' => (float) $sale->direct_paid_amount,

                'outstanding' => (float) $sale->balance_amount,

                'payment_status' => $sale->payment_status,

            ])

            ->values();



        return response()->json(['data' => $sales]);
    }



    /*
    |--------------------------------------------------------------------------
    | Compatible Invoice Search Endpoints
    |--------------------------------------------------------------------------

    */



    public function searchInvoice(

        Request $request

    ): JsonResponse {

        [$companyId, $storeId] = $this->context();



        $search = trim(

            $request->string('search')->toString()

        );



        $sales = Sales::query()

            ->with([

                'customer:id,customer_code,customer_name,mobile',

            ])

            ->where('company_id', $companyId)

            ->where('store_id', $storeId)

            ->where('status', '!=', 'Cancelled')

            ->where('balance_amount', '>', 0)

            ->when(

                $search !== '',

                function (Builder $query) use ($search): void {

                    $query->where(function (Builder $inner) use ($search): void {

                        $inner

                            ->where('invoice_no', 'like', "%{$search}%")

                            ->orWhereHas('customer', function (Builder $customer) use ($search): void {

                                $customer

                                    ->where('customer_name', 'like', "%{$search}%")

                                    ->orWhere('customer_code', 'like', "%{$search}%")

                                    ->orWhere('mobile', 'like', "%{$search}%");
                            });
                    });
                }

            )

            ->orderByDesc('invoice_date')

            ->orderByDesc('id')

            ->limit(20)

            ->get([

                'id',

                'customer_id',

                'invoice_no',

                'invoice_date',

                'grand_total',

                'advance_used',

                'paid_amount',

                'balance_amount',

                'payment_status',

            ])

            ->map(fn(Sales $sale): array => $this->saleData($sale))

            ->values();



        return response()->json([

            'data' => $sales,

        ]);
    }



    public function invoiceDetails(

        Sales $sale

    ): JsonResponse {

        [$companyId, $storeId] = $this->context();



        abort_unless(

            (int) $sale->company_id === $companyId

                && (int) $sale->store_id === $storeId

                && $sale->status !== 'Cancelled',

            404

        );



        $sale->load([

            'customer:id,customer_code,customer_name,mobile,gst_number',

        ]);



        return response()->json([

            'data' => $this->saleData($sale),

        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------

    */



    private function customerQuery(

        int $companyId,

        int $storeId

    ): Builder {

        return Customer::query()

            ->where('company_id', $companyId)

            ->where('status', true)

            ->whereHas('stores', function (Builder $query) use ($storeId): void {

                $query->where('stores.id', $storeId);
            });
    }



    private function customerData(

        Customer $customer,

        int $companyId,

        int $storeId

    ): array {

        $balance = (float) CustomerLedgerEntry::query()

            ->forContext($companyId, $storeId)

            ->forCustomer($customer->id)

            ->selectRaw('COALESCE(SUM(debit - credit), 0) AS balance')

            ->value('balance');



        return [

            'id' => $customer->id,

            'customer_code' => $customer->customer_code,

            'customer_name' => $customer->customer_name,

            'mobile' => $customer->mobile,

            'gst_number' => $customer->gst_number,

            'current_balance' => round($balance, 2),

            'balance_label' => $this->balanceLabel($balance),

        ];
    }



    private function paymentData(CustomerPayment $payment): array

    {

        $allocatedAmount = $payment->relationLoaded('allocations')

            ? (float) $payment->allocations->sum('allocated_amount')

            : (float) $payment->allocations()->sum('allocated_amount');



        return [

            'id' => $payment->id,

            'receipt_no' => $payment->receipt_no,

            'payment_date' => $payment->payment_date?->format('Y-m-d'),

            'amount' => (float) $payment->amount,

            'allocated_amount' => round($allocatedAmount, 2),

            'unallocated_amount' => max(0, round((float) $payment->amount - $allocatedAmount, 2)),

            'is_advance' => $allocatedAmount <= 0,

            'payment_mode' => $payment->payment_mode,

            'reference_no' => $payment->reference_no,

            'status' => $payment->status,

            'remarks' => $payment->remarks,

            'can_cancel' => $payment->status === CustomerPayment::STATUS_COMPLETED,

            'cancelled_at' => $payment->cancelled_at?->toIso8601String(),

            'customer' => $payment->customer,

            'account' => $payment->account,

            'allocations' => $payment->relationLoaded('allocations')

                ? $payment->allocations->map(fn(CustomerPaymentAllocation $allocation): array => [

                    'id' => $allocation->id,

                    'sale_id' => $allocation->sale_id,

                    'allocated_amount' => (float) $allocation->allocated_amount,

                    'sale' => $allocation->sale,

                ])->values()

                : [],

            'creator' => $payment->creator,

            'canceller' => $payment->canceller,

        ];
    }



    private function saleData(Sales $sale): array

    {

        return [

            'id' => $sale->id,

            'invoice_no' => $sale->invoice_no,

            'invoice_date' => $sale->invoice_date?->format('Y-m-d'),

            'grand_total' => (float) $sale->grand_total,

            'advance_used' => (float) $sale->advance_used,

            'paid_amount' => (float) $sale->paid_amount,

            'direct_paid_amount' => (float) $sale->direct_paid_amount,

            'balance_amount' => (float) $sale->balance_amount,

            'outstanding' => (float) $sale->balance_amount,

            'payment_status' => $sale->payment_status,

            'customer' => $sale->customer,

        ];
    }



    private function loadDocumentRelations(CustomerPayment $payment): void

    {

        $payment->load([

            'company',

            'store',

            'customer',

            'account',

            'allocations.sale',

            'creator:id,name',

            'canceller:id,name',

        ]);
    }



    private function ensurePaymentAvailable(

        CustomerPayment $payment,

        int $companyId,

        int $storeId

    ): void {

        abort_unless(

            (int) $payment->company_id === $companyId

                && (int) $payment->store_id === $storeId,

            404

        );
    }



    private function ensureCustomerAvailable(

        Customer $customer,

        int $companyId,

        int $storeId

    ): void {

        abort_unless(

            (int) $customer->company_id === $companyId

                && $customer->stores()->where('stores.id', $storeId)->exists(),

            404

        );
    }



    private function salePaymentStatus(

        float $paidAmount,

        float $balanceAmount

    ): string {

        if ($balanceAmount <= 0.009) {

            return 'Paid';
        }



        return $paidAmount > 0.009

            ? 'Partially Paid'

            : 'Unpaid';
    }



    private function accountSupportsPaymentMode(

        Account $account,

        string $paymentMode

    ): bool {

        $accountMode = $this->normalizePaymentMode((string) $account->payment_mode);



        return $accountMode === ''

            || $accountMode === $this->normalizePaymentMode($paymentMode);
    }



    private function normalizePaymentMode(string $paymentMode): string

    {

        $mode = str_replace(

            ['-', ' '],

            '\_',

            strtolower(trim($paymentMode))

        );



        return match ($mode) {

            'upi', 'phonepe', 'g_pay' => 'gpay',

            'bank', 'banktransfer', 'cheque' => 'bank_transfer',

            default => $mode,
        };
    }



    private function paymentModes(): array

    {

        return [

            'Cash',

            'GPay',

            'PhonePe',

            'UPI',

            'Card',

            'Bank Transfer',

            'Cheque',

        ];
    }



    private function balanceLabel(float $balance): string

    {

        if ($balance > 0) {

            return number_format(abs($balance), 2) . ' Dr';
        }



        if ($balance < 0) {

            return number_format(abs($balance), 2) . ' Cr';
        }



        return '0.00';
    }



    private function nullableString(?string $value): ?string

    {

        $value = trim((string) $value);



        return $value !== '' ? $value : null;
    }



    private function context(): array

    {

        $companyId = (int) session('active_company_id');

        $storeId = (int) session('active_store_id');



        abort_if(

            !$companyId || !$storeId,

            403,

            'Select an active company and branch before accessing customer payments.'

        );



        $validStore = Store::query()

            ->whereKey($storeId)

            ->where('company_id', $companyId)

            ->where('status', true)

            ->exists();



        abort_unless(

            $validStore,

            403,

            'The active branch is invalid or inactive.'

        );



        return [$companyId, $storeId];
    }
}
