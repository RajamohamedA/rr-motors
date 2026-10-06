<?php



namespace App\Http\Controllers;



use App\Models\Account;

use App\Models\AccountTransaction;

use App\Models\Mechanic;

use App\Models\MechanicCommission;

use App\Models\MechanicLedgerEntry;

use App\Models\MechanicPayment;

use App\Models\MechanicPaymentAllocation;

use App\Services\MechanicPaymentService;

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

use Illuminate\Validation\Rule;

use Illuminate\Validation\ValidationException;

use Inertia\Inertia;

use Inertia\Response;

use Symfony\Component\HttpFoundation\Response as HttpResponse;



class MechanicPaymentController extends Controller implements HasMiddleware

{

    public function __construct(

        private readonly MechanicPaymentService $paymentService

    ) {}



    public static function middleware(): array

    {

        return [

            new Middleware(

                'permission:mechanic-payment.view',

                only: ['index']

            ),

            new Middleware(

                'permission:mechanic-payment.show',

                only: ['show']

            ),

            new Middleware(

                'permission:mechanic-payment.create',

                only: [

                    'create',

                    'store',

                    'searchMechanics',

                    'mechanicCommissions',

                ]

            ),

            new Middleware(

                'permission:mechanic-payment.delete',

                only: ['cancel']

            ),

            new Middleware(

                'permission:mechanic-payment.print',

                only: ['print', 'pdf']

            ),

        ];
    }



    /*

    |--------------------------------------------------------------------------

    | Payment Register

    |--------------------------------------------------------------------------

    */



    public function index(Request $request): Response

    {

        [$companyId, $storeId] = $this->context();



        $search = trim(

            $request->string('search')->toString()

        );



        $status = trim(

            $request->string('status')->toString()

        );



        $paymentMode = trim(

            $request->string('payment_mode')->toString()

        );



        $mechanicId = $request->integer('mechanic_id') ?: null;

        $accountId = $request->integer('account_id') ?: null;

        $fromDate = $request->date('from_date')?->format('Y-m-d');

        $toDate = $request->date('to_date')?->format('Y-m-d');



        $baseQuery = MechanicPayment::query()

            ->forContext($companyId, $storeId)

            ->when(

                $search !== '',

                function (Builder $query) use ($search) {

                    $query->where(

                        function (Builder $inner) use ($search) {

                            $inner

                                ->where(

                                    'payment_no',

                                    'like',

                                    "%{$search}%"

                                )

                                ->orWhere(

                                    'reference_no',

                                    'like',

                                    "%{$search}%"

                                )

                                ->orWhereHas(

                                    'mechanic',

                                    function (Builder $mechanic) use ($search) {

                                        $mechanic

                                            ->where(

                                                'mechanic_name',

                                                'like',

                                                "%{$search}%"

                                            )

                                            ->orWhere(

                                                'mechanic_code',

                                                'like',

                                                "%{$search}%"

                                            )

                                            ->orWhere(

                                                'mobile',

                                                'like',

                                                "%{$search}%"

                                            );
                                    }

                                );
                        }

                    );
                }

            )

            ->when(

                in_array(

                    $status,

                    [

                        MechanicPayment::STATUS_COMPLETED,

                        MechanicPayment::STATUS_CANCELLED,

                    ],

                    true

                ),

                fn(Builder $query) =>

                $query->where('status', $status)

            )

            ->when(

                $paymentMode !== '',

                fn(Builder $query) =>

                $query->where('payment_mode', $paymentMode)

            )

            ->when(

                $mechanicId,

                fn(Builder $query) =>

                $query->where('mechanic_id', $mechanicId)

            )

            ->when(

                $accountId,

                fn(Builder $query) =>

                $query->where('account_id', $accountId)

            )

            ->when(

                $fromDate,

                fn(Builder $query) =>

                $query->whereDate(

                    'payment_date',

                    '>=',

                    $fromDate

                )

            )

            ->when(

                $toDate,

                fn(Builder $query) =>

                $query->whereDate(

                    'payment_date',

                    '<=',

                    $toDate

                )

            );



        $payments = (clone $baseQuery)

            ->with([

                'mechanic:id,mechanic_code,mechanic_name,mobile',

                'account:id,account_code,account_name,account_type,payment_mode',

                'creator:id,name',

                'canceller:id,name',

            ])

            ->withSum(

                'allocations as allocated_amount',

                'allocated_amount'

            )

            ->orderByDesc('payment_date')

            ->orderByDesc('id')

            ->paginate(20)

            ->withQueryString();



        $payments->through(

            fn(MechanicPayment $payment) =>

            $this->paymentData($payment)

        );



        $summaryQuery = MechanicPayment::query()

            ->forContext($companyId, $storeId)

            ->when(

                $fromDate,

                fn(Builder $query) =>

                $query->whereDate(

                    'payment_date',

                    '>=',

                    $fromDate

                )

            )

            ->when(

                $toDate,

                fn(Builder $query) =>

                $query->whereDate(

                    'payment_date',

                    '<=',

                    $toDate

                )

            );



        $completedAmount = (float) (clone $summaryQuery)

            ->completed()

            ->sum('amount');



        $allocatedAmount = (float) MechanicPaymentAllocation::query()

            ->forContext($companyId, $storeId)

            ->whereHas(

                'payment',

                function (Builder $payment) use (

                    $fromDate,

                    $toDate

                ) {

                    $payment

                        ->completed()

                        ->when(

                            $fromDate,

                            fn(Builder $query) =>

                            $query->whereDate(

                                'payment_date',

                                '>=',

                                $fromDate

                            )

                        )

                        ->when(

                            $toDate,

                            fn(Builder $query) =>

                            $query->whereDate(

                                'payment_date',

                                '<=',

                                $toDate

                            )

                        );
                }

            )

            ->sum('allocated_amount');



        $summary = [

            'completed_amount' => round($completedAmount, 2),

            'completed_count' => (clone $summaryQuery)

                ->completed()

                ->count(),

            'cancelled_amount' => round(

                (float) (clone $summaryQuery)

                    ->cancelled()

                    ->sum('amount'),

                2

            ),

            'cancelled_count' => (clone $summaryQuery)

                ->cancelled()

                ->count(),

            'allocated_amount' => round($allocatedAmount, 2),

            'advance_amount' => max(

                0,

                round($completedAmount - $allocatedAmount, 2)

            ),

        ];



        return Inertia::render('MechanicPayments/Index', [

            'payments' => $payments,

            'summary' => $summary,

            'filters' => [

                'search' => $search,

                'status' => $status,

                'payment_mode' => $paymentMode,

                'mechanic_id' => $mechanicId,

                'account_id' => $accountId,

                'from_date' => $fromDate,

                'to_date' => $toDate,

            ],

            'accounts' => $this->accounts(

                $companyId,

                $storeId

            ),

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



        $selectedMechanic = null;



        $requestedMechanicId = $request->integer('mechanic_id')

            ?: $request->integer('mechanic');



        if ($requestedMechanicId) {

            $mechanic = Mechanic::query()

                ->forContext($companyId, $storeId)

                ->active()

                ->find($requestedMechanicId);



            if ($mechanic) {

                $selectedMechanic = $this->mechanicData(

                    $mechanic,

                    $companyId,

                    $storeId

                );
            }
        }



        return Inertia::render('MechanicPayments/Create', [

            'accounts' => $this->accounts(

                $companyId,

                $storeId

            ),

            'payment_modes' => $this->paymentModes(),

            'selected_mechanic' => $selectedMechanic,

            'default_date' => now()->toDateString(),

        ]);
    }



    /*

    |--------------------------------------------------------------------------

    | Store

    |--------------------------------------------------------------------------

    */



    public function store(Request $request): RedirectResponse

    {

        [$companyId, $storeId] = $this->context();



        $validated = $request->validate([

            'mechanic_id' => [

                'required',

                'integer',

                'exists:mechanics,id',

            ],

            'account_id' => [

                'required',

                'integer',

                'exists:accounts,id',

            ],

            'payment_date' => [

                'required',

                'date',

            ],

            'amount' => [

                'required',

                'numeric',

                'min:0.01',

            ],

            'payment_mode' => [

                'required',

                Rule::in($this->paymentModes()),

            ],

            'reference_no' => [

                'nullable',

                'string',

                'max:150',

            ],

            'remarks' => [

                'nullable',

                'string',

                'max:2000',

            ],

            'allocations' => [

                'nullable',

                'array',

            ],

            'allocations.*.mechanic_commission_id' => [

                'required',

                'integer',

                'distinct',

                'exists:mechanic_commissions,id',

            ],

            'allocations.*.allocated_amount' => [

                'required',

                'numeric',

                'min:0.01',

            ],

        ]);



        try {

            $payment = $this->paymentService->create(

                data: [

                    'company_id' => $companyId,

                    'store_id' => $storeId,

                    'mechanic_id' =>

                    (int) $validated['mechanic_id'],

                    'account_id' =>

                    (int) $validated['account_id'],

                    'payment_date' =>

                    $validated['payment_date'],

                    'amount' =>

                    (float) $validated['amount'],

                    'payment_mode' =>

                    $validated['payment_mode'],

                    'reference_no' =>

                    $validated['reference_no'] ?? null,

                    'remarks' =>

                    $validated['remarks'] ?? null,

                ],

                allocations: $validated['allocations'] ?? [],

                createdBy: auth()->id()

            );
        } catch (ValidationException $exception) {

            throw $exception;
        } catch (\Throwable $exception) {

            report($exception);



            return AppResponse::error(

                'Mechanic payment could not be saved. Please try again.',

                true

            );
        }



        return AppResponse::successRoute(

            'mechanic-payments.show',

            'Mechanic payment recorded successfully.',

            $payment

        );
    }



    /*

    |--------------------------------------------------------------------------

    | Show

    |--------------------------------------------------------------------------

    */



    public function show(

        MechanicPayment $mechanicPayment

    ): Response {

        [$companyId, $storeId] = $this->context();



        $this->ensurePaymentAvailable(

            $mechanicPayment,

            $companyId,

            $storeId

        );



        $mechanicPayment->load([

            'company:id,company_name',

            'store:id,store_name',

            'mechanic:id,mechanic_code,mechanic_name,mobile,email,address,city,state,pincode',

            'account:id,account_code,account_name,account_type,payment_mode,bank_name,account_number,upi_id',

            'allocations.commission.sale:id,invoice_no,invoice_date,grand_total',

            'allocations.commission.completedSalesReturns:id,sale_id,sub_total',

            'creator:id,name',

            'canceller:id,name',

        ]);



        $accountEntries = AccountTransaction::query()

            ->forContext($companyId, $storeId)

            ->forReference(

                $mechanicPayment->getMorphClass(),

                $mechanicPayment->id

            )

            ->orderBy('id')

            ->get()

            ->map(fn(AccountTransaction $entry) => [

                'id' => $entry->id,

                'debit' => (float) $entry->debit,

                'credit' => (float) $entry->credit,

                'is_reversal' => (bool) $entry->is_reversal,

            ]);



        $ledgerEntries = MechanicLedgerEntry::query()

            ->forContext($companyId, $storeId)

            ->forReference(

                $mechanicPayment->getMorphClass(),

                $mechanicPayment->id

            )

            ->orderBy('id')

            ->get()

            ->map(fn(MechanicLedgerEntry $entry) => [

                'id' => $entry->id,

                'transaction_type' => $entry->transaction_type,

                'debit' => (float) $entry->debit,

                'credit' => (float) $entry->credit,

                'is_reversal' => (bool) $entry->is_reversal,

            ]);



        return Inertia::render('MechanicPayments/Show', [

            'payment' => $this->paymentData(

                $mechanicPayment

            ),

            'account_transactions' => $accountEntries,

            'ledger_entries' => $ledgerEntries,

        ]);
    }



    /*

    |--------------------------------------------------------------------------

    | Cancel

    |--------------------------------------------------------------------------

    */



    public function cancel(

        MechanicPayment $mechanicPayment

    ): RedirectResponse {

        [$companyId, $storeId] = $this->context();



        $this->ensurePaymentAvailable(

            $mechanicPayment,

            $companyId,

            $storeId

        );



        try {

            $this->paymentService->cancel(

                payment: $mechanicPayment,

                cancelledBy: auth()->id(),

                remarks: 'Mechanic payment cancelled by user.'

            );
        } catch (ValidationException $exception) {

            return AppResponse::warning(

                collect($exception->errors())

                    ->flatten()

                    ->first()

                    ?: 'Mechanic payment cannot be cancelled.'

            );
        } catch (\Throwable $exception) {

            report($exception);



            return AppResponse::error(

                'Mechanic payment could not be cancelled. Please try again.',

                true

            );
        }



        return AppResponse::success(

            'Mechanic payment cancelled and accounting reversals created successfully.'

        );
    }



    /*

    |--------------------------------------------------------------------------

    | AJAX Mechanic Search

    |--------------------------------------------------------------------------

    */



    public function searchMechanics(

        Request $request

    ): JsonResponse {

        [$companyId, $storeId] = $this->context();



        $search = trim(

            $request->string('search')->toString()

        );



        $mechanics = Mechanic::query()

            ->forContext($companyId, $storeId)

            ->active()

            ->search($search)

            ->orderBy('mechanic_name')

            ->limit(20)

            ->get([

                'id',

                'company_id',

                'mechanic_code',

                'mechanic_name',

                'mobile',

                'email',

            ])

            ->map(

                fn(Mechanic $mechanic) =>

                $this->mechanicData(

                    $mechanic,

                    $companyId,

                    $storeId

                )

            )

            ->values();



        return response()->json([

            'data' => $mechanics,

        ]);
    }



    /*

    |--------------------------------------------------------------------------

    | AJAX Outstanding Commissions

    |--------------------------------------------------------------------------

    */



    public function mechanicCommissions(

        Mechanic $mechanic

    ): JsonResponse {

        [$companyId, $storeId] = $this->context();



        abort_unless(

            Mechanic::query()

                ->whereKey($mechanic->id)

                ->forContext($companyId, $storeId)

                ->active()

                ->exists(),

            404

        );



        $commissions = DB::transaction(function () use (

            $companyId,

            $storeId,

            $mechanic

        ) {

            $allCommissions = MechanicCommission::query()

                ->forContext($companyId, $storeId)

                ->forMechanic($mechanic->id)

                ->notCancelled()

                ->orderBy('id')

                ->lockForUpdate()

                ->get();



            foreach ($allCommissions as $commission) {

                $this->paymentService

                    ->refreshCommissionPaymentSummary(

                        $commission

                    );
            }



            return MechanicCommission::query()

                ->forContext($companyId, $storeId)

                ->forMechanic($mechanic->id)

                ->outstanding()

                ->with([

                    'sale:id,invoice_no,invoice_date,grand_total',

                    'completedSalesReturns:id,sale_id,sub_total',

                ])

                ->orderBy('id')

                ->get();
        })

            ->map(fn(MechanicCommission $commission) => [

                'id' => $commission->id,

                'sale_id' => $commission->sale_id,

                'invoice_no' => $commission->sale?->invoice_no,

                'invoice_date' => $commission->sale?->invoice_date

                    ?->format('Y-m-d'),

                'commission_amount' =>

                (float) $commission->commission_amount,

                'returned_commission_amount' =>

                $commission->returnedCommissionAmount(),

                'effective_commission_amount' =>

                $commission->effectiveCommissionAmount(),

                'paid_amount' =>

                (float) $commission->paid_amount,

                'balance_amount' =>

                (float) $commission->balance_amount,

                'advance_amount' =>

                $commission->excessPaidAdvanceAmount(),

                'status' => $commission->status,

            ])

            ->values();



        $ledgerTotals = MechanicLedgerEntry::query()

            ->forContext($companyId, $storeId)

            ->forMechanic($mechanic->id)

            ->selectRaw(

                'COALESCE(SUM(credit), 0) AS total_credit, '

                    . 'COALESCE(SUM(debit), 0) AS total_debit'

            )

            ->first();



        $ledgerBalance = round(

            (float) $ledgerTotals->total_credit

                - (float) $ledgerTotals->total_debit,

            2

        );



        return response()->json([

            'mechanic' => [

                'id' => $mechanic->id,

                'mechanic_code' => $mechanic->mechanic_code,

                'mechanic_name' => $mechanic->mechanic_name,

                'mobile' => $mechanic->mobile,

            ],

            'commissions' => $commissions,

            'outstanding_total' => round(

                (float) $commissions->sum('balance_amount'),

                2

            ),

            'ledger_balance' => $ledgerBalance,

            'advance_amount' => $ledgerBalance < 0

                ? abs($ledgerBalance)

                : 0,

        ]);
    }



    /*

    |--------------------------------------------------------------------------

    | Print and PDF

    |--------------------------------------------------------------------------

    */



    public function print(

        Request $request,

        MechanicPayment $mechanicPayment

    ): ViewContract {

        [$companyId, $storeId] = $this->context();



        $this->ensurePaymentAvailable(

            $mechanicPayment,

            $companyId,

            $storeId

        );



        $paper = $request->validate([

            'paper' => [

                'nullable',

                Rule::in(['a4', 'a5']),

            ],

        ])['paper'] ?? 'a4';



        $this->loadDocumentRelations($mechanicPayment);



        return view('mechanic_payments.print', [

            'payment' => $mechanicPayment,

            'paper' => $paper,

            'pdfMode' => false,

        ]);
    }



    public function pdf(

        Request $request,

        MechanicPayment $mechanicPayment

    ): HttpResponse {

        [$companyId, $storeId] = $this->context();



        $this->ensurePaymentAvailable(

            $mechanicPayment,

            $companyId,

            $storeId

        );



        $paper = $request->validate([

            'paper' => [

                'nullable',

                Rule::in(['a4', 'a5']),

            ],

        ])['paper'] ?? 'a4';



        $this->loadDocumentRelations($mechanicPayment);



        return Pdf::loadView('mechanic_payments.pdf', [

            'payment' => $mechanicPayment,

            'paper' => $paper,

        ])

            ->setPaper($paper, 'portrait')

            ->download(

                'mechanic-payment-'

                    . $mechanicPayment->payment_no

                    . '-'

                    . strtoupper($paper)

                    . '.pdf'

            );
    }



    /*

    |--------------------------------------------------------------------------

    | Data Mapping

    |--------------------------------------------------------------------------

    */



    private function paymentData(

        MechanicPayment $payment

    ): array {

        $allocatedAmount = isset($payment->allocated_amount)

            ? (float) $payment->allocated_amount

            : $payment->allocatedAmount();



        return [

            'id' => $payment->id,

            'payment_no' => $payment->payment_no,

            'payment_date' => $payment->payment_date?->format('Y-m-d'),

            'amount' => (float) $payment->amount,

            'allocated_amount' => round($allocatedAmount, 2),

            'advance_amount' => max(

                0,

                round(

                    (float) $payment->amount - $allocatedAmount,

                    2

                )

            ),

            'payment_mode' => $payment->payment_mode,

            'reference_no' => $payment->reference_no,

            'status' => $payment->status,

            'remarks' => $payment->remarks,

            'cancelled_at' => $payment->cancelled_at?->toDateTimeString(),

            'mechanic' => $payment->mechanic

                ? [

                    'id' => $payment->mechanic->id,

                    'mechanic_code' =>

                    $payment->mechanic->mechanic_code,

                    'mechanic_name' =>

                    $payment->mechanic->mechanic_name,

                    'mobile' => $payment->mechanic->mobile,

                    'email' => $payment->mechanic->email,

                    'address' => $payment->mechanic->address,

                    'city' => $payment->mechanic->city,

                    'state' => $payment->mechanic->state,

                    'pincode' => $payment->mechanic->pincode,

                ]

                : null,

            'account' => $payment->account

                ? [

                    'id' => $payment->account->id,

                    'account_code' => $payment->account->account_code,

                    'account_name' => $payment->account->account_name,

                    'account_type' => $payment->account->account_type,

                    'payment_mode' => $payment->account->payment_mode,

                    'bank_name' => $payment->account->bank_name,

                    'account_number' =>

                    $payment->account->account_number,

                    'upi_id' => $payment->account->upi_id,

                ]

                : null,

            'allocations' => $payment->relationLoaded('allocations')

                ? $payment->allocations

                ->map(fn(MechanicPaymentAllocation $allocation) => [

                    'id' => $allocation->id,

                    'allocated_amount' =>

                    (float) $allocation->allocated_amount,

                    'commission' => $allocation->commission

                        ? [

                            'id' => $allocation->commission->id,

                            'commission_amount' =>

                            (float) $allocation

                                ->commission

                                ->commission_amount,

                            'returned_commission_amount' =>

                            $allocation

                                ->commission

                                ->returnedCommissionAmount(),

                            'effective_commission_amount' =>

                            $allocation

                                ->commission

                                ->effectiveCommissionAmount(),

                            'paid_amount' =>

                            (float) $allocation

                                ->commission

                                ->paid_amount,

                            'balance_amount' =>

                            (float) $allocation

                                ->commission

                                ->balance_amount,

                            'advance_amount' =>

                            $allocation

                                ->commission

                                ->excessPaidAdvanceAmount(),

                            'status' =>

                            $allocation->commission->status,

                            'invoice_no' =>

                            $allocation

                                ->commission

                                ->sale

                                ?->invoice_no,

                            'invoice_date' =>

                            $allocation

                                ->commission

                                ->sale

                                ?->invoice_date

                                ?->format('Y-m-d'),

                        ]

                        : null,

                ])

                ->values()

                : [],

            'creator' => $payment->creator

                ? [

                    'id' => $payment->creator->id,

                    'name' => $payment->creator->name,

                ]

                : null,

            'canceller' => $payment->canceller

                ? [

                    'id' => $payment->canceller->id,

                    'name' => $payment->canceller->name,

                ]

                : null,

            'created_at' => $payment->created_at?->toDateTimeString(),

        ];
    }



    private function mechanicData(

        Mechanic $mechanic,

        int $companyId,

        int $storeId

    ): array {

        $totals = MechanicLedgerEntry::query()

            ->forContext($companyId, $storeId)

            ->forMechanic($mechanic->id)

            ->selectRaw(

                'COALESCE(SUM(credit), 0) AS total_credit, '

                    . 'COALESCE(SUM(debit), 0) AS total_debit'

            )

            ->first();



        $balance = round(

            (float) $totals->total_credit

                - (float) $totals->total_debit,

            2

        );



        return [

            'id' => $mechanic->id,

            'mechanic_code' => $mechanic->mechanic_code,

            'mechanic_name' => $mechanic->mechanic_name,

            'mobile' => $mechanic->mobile,

            'email' => $mechanic->email,

            'balance' => $balance,

            'payable_amount' => max(0, $balance),

            'advance_amount' => $balance < 0

                ? abs($balance)

                : 0,

        ];
    }



    private function accounts(

        int $companyId,

        int $storeId

    ) {

        return Account::query()

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

                'account_number',

                'upi_id',

            ]);
    }



    private function loadDocumentRelations(

        MechanicPayment $payment

    ): void {

        $payment->load([

            'company',

            'store',

            'mechanic',

            'account',

            'allocations.commission.sale',

            'allocations.commission.completedSalesReturns',

            'creator',

            'canceller',

        ]);
    }



    private function ensurePaymentAvailable(

        MechanicPayment $payment,

        int $companyId,

        int $storeId

    ): void {

        abort_unless(

            (int) $payment->company_id === $companyId

                && (int) $payment->store_id === $storeId,

            404

        );
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



    private function context(): array

    {

        $companyId = (int) session('active_company_id');

        $storeId = (int) session('active_store_id');



        abort_if(

            !$companyId || !$storeId,

            403,

            'Select an active company and branch before accessing mechanic payments.'

        );



        return [$companyId, $storeId];
    }
}
