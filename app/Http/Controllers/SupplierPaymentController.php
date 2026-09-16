<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\SupplierPayment;
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

class SupplierPaymentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:supplier-payment.view',
                only: ['index']
            ),
            new Middleware(
                'permission:supplier-payment.show',
                only: ['show', 'print', 'pdf']
            ),
            new Middleware(
                'permission:supplier-payment.create',
                only: ['create', 'store']
            ),
            new Middleware(
                'permission:supplier-payment.view|supplier-payment.create',
                only: ['searchSuppliers', 'supplierPurchases']
            ),
            new Middleware(
                'permission:supplier-payment.delete',
                only: ['cancel']
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

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $paymentMode = trim($request->string('payment_mode')->toString());
        $fromDate = $request->date('from_date')?->format('Y-m-d');
        $toDate = $request->date('to_date')?->format('Y-m-d');

        $baseQuery = SupplierPayment::query()
            ->forContext($companyId, $storeId)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('payment_no', 'like', "%{$search}%")
                        ->orWhere('reference_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function (Builder $supplier) use ($search) {
                            $supplier
                                ->where('supplier_name', 'like', "%{$search}%")
                                ->orWhere('supplier_code', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                in_array($status, [
                    SupplierPayment::STATUS_COMPLETED,
                    SupplierPayment::STATUS_CANCELLED,
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
                'supplier:id,supplier_code,supplier_name,phone',
                'purchase:id,purchase_no,purchase_date,grand_total',
                'account:id,account_code,account_name,account_type,payment_mode',
                'creator:id,name',
                'canceller:id,name',
            ])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $payments->through(
            fn(SupplierPayment $payment) => $this->paymentData($payment)
        );

        $summaryBase = SupplierPayment::query()
            ->forContext($companyId, $storeId)
            ->when(
                $fromDate,
                fn(Builder $query) => $query->whereDate('payment_date', '>=', $fromDate)
            )
            ->when(
                $toDate,
                fn(Builder $query) => $query->whereDate('payment_date', '<=', $toDate)
            );

        $summary = [
            'completed_amount' => (float) (clone $summaryBase)
                ->completed()
                ->sum('amount'),
            'completed_count' => (clone $summaryBase)
                ->completed()
                ->count(),
            'cancelled_amount' => (float) (clone $summaryBase)
                ->cancelled()
                ->sum('amount'),
            'advance_amount' => (float) (clone $summaryBase)
                ->completed()
                ->whereNull('purchase_id')
                ->sum('amount'),
        ];

        return Inertia::render('SupplierPayments/Index', [
            'payments' => $payments,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'payment_mode' => $paymentMode,
                'from_date' => $fromDate,
                'to_date' => $toDate,
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
            ])
            ->map(fn(Account $account) => [
                'id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type,
                'payment_mode' => $account->payment_mode,
                'bank_name' => $account->bank_name,
            ])
            ->values();

        $selectedSupplier = null;

        if ($request->filled('supplier')) {
            $supplier = Supplier::query()
                ->forContext($companyId, $storeId)
                ->active()
                ->find((int) $request->input('supplier'));

            if ($supplier) {
                $selectedSupplier = $this->supplierData($supplier);
            }
        }

        return Inertia::render('SupplierPayments/Create', [
            'accounts' => $accounts,
            'payment_modes' => $this->paymentModes(),
            'selected_supplier' => $selectedSupplier,
            'today' => now()->toDateString(),
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
            'supplier_id' => ['required', 'integer'],
            'purchase_id' => ['nullable', 'integer'],
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
        ]);

        try {
            $payment = DB::transaction(function () use (
                $validated,
                $companyId,
                $storeId
            ) {
                $supplier = Supplier::query()
                    ->forContext($companyId, $storeId)
                    ->active()
                    ->lockForUpdate()
                    ->findOrFail((int) $validated['supplier_id']);

                $account = Account::query()
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->where('status', true)
                    ->lockForUpdate()
                    ->findOrFail((int) $validated['account_id']);

                $purchase = null;

                if (!empty($validated['purchase_id'])) {
                    $purchase = Purchase::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->where('supplier_id', $supplier->id)
                        ->lockForUpdate()
                        ->findOrFail((int) $validated['purchase_id']);

                    $outstanding = $this->purchaseOutstanding(
                        $purchase,
                        $companyId,
                        $storeId
                    );

                    if ((float) $validated['amount'] > $outstanding + 0.009) {
                        throw ValidationException::withMessages([
                            'amount' => sprintf(
                                'Payment cannot exceed the purchase outstanding amount of %.2f.',
                                $outstanding
                            ),
                        ]);
                    }
                }

                $payment = SupplierPayment::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'supplier_id' => $supplier->id,
                    'purchase_id' => $purchase?->id,
                    'account_id' => $account->id,
                    'payment_no' => SupplierPayment::generatePaymentNo(
                        $companyId,
                        $storeId
                    ),
                    'payment_date' => $validated['payment_date'],
                    'amount' => round((float) $validated['amount'], 2),
                    'payment_mode' => $validated['payment_mode'],
                    'reference_no' => $this->nullableString(
                        $validated['reference_no'] ?? null
                    ),
                    'status' => SupplierPayment::STATUS_COMPLETED,
                    'remarks' => $this->nullableString(
                        $validated['remarks'] ?? null
                    ),
                    'created_by' => auth()->id(),
                ]);

                AccountTransaction::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'account_id' => $account->id,
                    'transaction_date' => $payment->payment_date,
                    'transaction_type' => 'payment',
                    'reference_type' => 'supplier_payment',
                    'reference_id' => $payment->id,
                    'reference_no' => $payment->payment_no,
                    'debit' => 0,
                    'credit' => $payment->amount,
                    'payment_mode' => $payment->payment_mode,
                    'external_reference' => $payment->reference_no,
                    'remarks' => $payment->remarks,
                    'is_reversal' => false,
                    'reversal_of_id' => null,
                    'created_by' => auth()->id(),
                ]);

                return $payment;
            }, 3);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return AppResponse::error(
                'Supplier payment could not be saved. Please try again.'
            );
        }

        return AppResponse::successRoute(
            'supplier-payments.show',
            'Supplier payment recorded successfully.',
            $payment
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(SupplierPayment $supplierPayment): Response
    {
        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable(
            $supplierPayment,
            $companyId,
            $storeId
        );

        $supplierPayment->load([
            'supplier:id,supplier_code,supplier_name,phone,email,gst_number,address,city,state',
            'purchase:id,purchase_no,purchase_date,grand_total',
            'account:id,account_code,account_name,account_type,payment_mode,bank_name,account_number,upi_id',
            'creator:id,name',
            'canceller:id,name',
        ]);

        $accountEntry = AccountTransaction::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('reference_type', 'supplier_payment')
            ->where('reference_id', $supplierPayment->id)
            ->where('is_reversal', false)
            ->first();

        return Inertia::render('SupplierPayments/Show', [
            'payment' => $this->paymentData($supplierPayment),
            'account_transaction' => $accountEntry
                ? [
                    'id' => $accountEntry->id,
                    'debit' => (float) $accountEntry->debit,
                    'credit' => (float) $accountEntry->credit,
                    'is_reversal' => (bool) $accountEntry->is_reversal,
                ]
                : null,
        ]);
    }

    public function print(
        SupplierPayment $supplierPayment
    ): ViewContract {
        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable($supplierPayment, $companyId, $storeId);
        $this->loadPaymentDocumentRelations($supplierPayment);

        return view('supplier_payments.print', [
            'payment' => $supplierPayment,
            'paper' => 'a4',
            'pdfMode' => false,
        ]);
    }

    public function pdf(
        SupplierPayment $supplierPayment
    ): HttpResponse {
        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable($supplierPayment, $companyId, $storeId);
        $this->loadPaymentDocumentRelations($supplierPayment);

        return Pdf::loadView('supplier_payments.pdf', [
            'payment' => $supplierPayment,
            'paper' => 'a4',
        ])
            ->setPaper('a4', 'portrait')
            ->download("supplier-payment-{$supplierPayment->payment_no}.pdf");
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel With Reversal
    |--------------------------------------------------------------------------
    */

    public function cancel(
        SupplierPayment $supplierPayment
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context();

        $this->ensurePaymentAvailable(
            $supplierPayment,
            $companyId,
            $storeId
        );

        try {
            DB::transaction(function () use (
                $supplierPayment,
                $companyId,
                $storeId
            ) {
                $payment = SupplierPayment::query()
                    ->forContext($companyId, $storeId)
                    ->lockForUpdate()
                    ->findOrFail($supplierPayment->id);

                if (!$payment->canBeCancelled()) {
                    throw ValidationException::withMessages([
                        'payment' => 'This supplier payment is already cancelled.',
                    ]);
                }

                $originalEntry = AccountTransaction::query()
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->where('reference_type', 'supplier_payment')
                    ->where('reference_id', $payment->id)
                    ->where('is_reversal', false)
                    ->lockForUpdate()
                    ->firstOrFail();

                $alreadyReversed = AccountTransaction::query()
                    ->where('reversal_of_id', $originalEntry->id)
                    ->exists();

                if ($alreadyReversed) {
                    throw ValidationException::withMessages([
                        'payment' => 'The accounting entry has already been reversed.',
                    ]);
                }

                AccountTransaction::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'account_id' => $originalEntry->account_id,
                    'transaction_date' => now()->toDateString(),
                    'transaction_type' => 'reversal',
                    'reference_type' => 'supplier_payment_reversal',
                    'reference_id' => $payment->id,
                    'reference_no' => $payment->payment_no,
                    'debit' => $originalEntry->credit,
                    'credit' => $originalEntry->debit,
                    'payment_mode' => $originalEntry->payment_mode,
                    'external_reference' => $originalEntry->external_reference,
                    'remarks' => "Reversal of supplier payment {$payment->payment_no}",
                    'is_reversal' => true,
                    'reversal_of_id' => $originalEntry->id,
                    'created_by' => auth()->id(),
                ]);

                $payment->update([
                    'status' => SupplierPayment::STATUS_CANCELLED,
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                ]);
            }, 3);
        } catch (ValidationException $exception) {
            return AppResponse::warning(
                collect($exception->errors())->flatten()->first()
                    ?? 'Supplier payment cannot be cancelled.'
            );
        } catch (\Throwable $exception) {
            report($exception);

            return AppResponse::error(
                'Supplier payment could not be cancelled. Please try again.'
            );
        }

        return AppResponse::success(
            'Supplier payment cancelled and accounting reversal created successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Supplier Search
    |--------------------------------------------------------------------------
    */

    public function searchSuppliers(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context();

        $search = trim($request->string('search')->toString());

        $suppliers = Supplier::query()
            ->forContext($companyId, $storeId)
            ->active()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner
                        ->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('gst_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('supplier_name')
            ->limit(20)
            ->get([
                'id',
                'supplier_code',
                'supplier_name',
                'phone',
                'gst_number',
                'opening_balance',
                'balance_type',
            ])
            ->map(fn(Supplier $supplier) => $this->supplierData($supplier))
            ->values();

        return response()->json([
            'data' => $suppliers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Outstanding Purchases
    |--------------------------------------------------------------------------
    */

    public function supplierPurchases(
        Request $request,
        Supplier $supplier
    ): JsonResponse {
        [$companyId, $storeId] = $this->context();

        $this->ensureSupplierAvailable(
            $supplier,
            $companyId,
            $storeId
        );

        $purchases = Purchase::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('supplier_id', $supplier->id)
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get([
                'id',
                'purchase_no',
                'purchase_date',
                'grand_total',
            ])
            ->map(function (Purchase $purchase) use ($companyId, $storeId) {
                $outstanding = $this->purchaseOutstanding(
                    $purchase,
                    $companyId,
                    $storeId
                );

                return [
                    'id' => $purchase->id,
                    'purchase_no' => $purchase->purchase_no,
                    'purchase_date' => (string) $purchase->purchase_date,
                    'grand_total' => (float) $purchase->grand_total,
                    'outstanding' => $outstanding,
                ];
            })
            ->filter(fn(array $purchase) => $purchase['outstanding'] > 0)
            ->values();

        return response()->json([
            'data' => $purchases,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function purchaseOutstanding(
        Purchase $purchase,
        int $companyId,
        int $storeId
    ): float {
        $returns = (float) PurchaseReturn::query()
            ->where('company_id', $companyId)
            ->where('purchase_id', $purchase->id)
            ->sum('total_amount');

        $payments = (float) SupplierPayment::query()
            ->forContext($companyId, $storeId)
            ->completed()
            ->where('purchase_id', $purchase->id)
            ->sum('amount');

        return max(
            0,
            round(
                (float) $purchase->grand_total
                    - $returns
                    - $payments,
                2
            )
        );
    }

    private function loadPaymentDocumentRelations(
        SupplierPayment $payment
    ): void {
        $payment->load([
            'company',
            'store',
            'supplier',
            'purchase',
            'account',
            'creator:id,name',
            'canceller:id,name',
        ]);
    }

    private function ensurePaymentAvailable(
        SupplierPayment $payment,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $payment->company_id === $companyId
                && (int) $payment->store_id === $storeId,
            404
        );
    }

    private function ensureSupplierAvailable(
        Supplier $supplier,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $supplier->company_id === $companyId
                && $supplier->isAvailableInStore($storeId),
            404
        );
    }

    private function paymentData(
        SupplierPayment $payment
    ): array {
        return [
            'id' => $payment->id,
            'payment_no' => $payment->payment_no,
            'payment_date' => $payment->payment_date?->format('Y-m-d'),
            'amount' => (float) $payment->amount,
            'payment_mode' => $payment->payment_mode,
            'reference_no' => $payment->reference_no,
            'status' => $payment->status,
            'remarks' => $payment->remarks,
            'is_advance' => $payment->isAdvancePayment(),
            'can_cancel' => $payment->canBeCancelled(),
            'cancelled_at' => $payment->cancelled_at?->toIso8601String(),
            'supplier' => $payment->relationLoaded('supplier')
                && $payment->supplier
                ? $this->supplierData($payment->supplier)
                : null,
            'purchase' => $payment->relationLoaded('purchase')
                && $payment->purchase
                ? [
                    'id' => $payment->purchase->id,
                    'purchase_no' => $payment->purchase->purchase_no,
                    'purchase_date' => (string) $payment->purchase->purchase_date,
                    'grand_total' => (float) $payment->purchase->grand_total,
                ]
                : null,
            'account' => $payment->relationLoaded('account')
                && $payment->account
                ? [
                    'id' => $payment->account->id,
                    'account_code' => $payment->account->account_code,
                    'account_name' => $payment->account->account_name,
                    'account_type' => $payment->account->account_type,
                    'payment_mode' => $payment->account->payment_mode,
                    'bank_name' => $payment->account->bank_name,
                    'account_number' => $payment->account->account_number,
                    'upi_id' => $payment->account->upi_id,
                ]
                : null,
            'creator' => $payment->relationLoaded('creator')
                && $payment->creator
                ? [
                    'id' => $payment->creator->id,
                    'name' => $payment->creator->name,
                ]
                : null,
            'canceller' => $payment->relationLoaded('canceller')
                && $payment->canceller
                ? [
                    'id' => $payment->canceller->id,
                    'name' => $payment->canceller->name,
                ]
                : null,
        ];
    }

    private function supplierData(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id,
            'supplier_code' => $supplier->supplier_code,
            'supplier_name' => $supplier->supplier_name,
            'phone' => $supplier->phone,
            'gst_number' => $supplier->gst_number,
            'opening_balance' => (float) $supplier->opening_balance,
            'balance_type' => $supplier->balance_type,
        ];
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
            'Select an active company and branch before accessing supplier payments.'
        );

        return [$companyId, $storeId];
    }
}
