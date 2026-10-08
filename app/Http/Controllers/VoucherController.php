<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Mechanic;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Voucher;
use App\Services\AccountTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class VoucherController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly AccountTransactionService $accountTransactions
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:voucher.view', only: ['index']),
            new Middleware('permission:voucher.show', only: ['show', 'print', 'pdf']),
            new Middleware('permission:voucher.create', only: [
                'create',
                'store',
                'searchParties',
            ]),
            new Middleware('permission:voucher.delete', only: ['cancel']),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $filters = $this->validatedFilters($request);

        $baseQuery = Voucher::query()
            ->forContext($companyId, $storeId)
            ->when(
                $filters['search'],
                function (Builder $query, string $search): void {
                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('voucher_no', 'like', "%{$search}%")
                            ->orWhere('reference_no', 'like', "%{$search}%")
                            ->orWhere('party_name', 'like', "%{$search}%")
                            ->orWhere('remarks', 'like', "%{$search}%")
                            ->orWhereHas('account', function (Builder $accountQuery) use ($search): void {
                                $accountQuery
                                    ->where('account_name', 'like', "%{$search}%")
                                    ->orWhere('account_code', 'like', "%{$search}%");
                            });
                    });
                }
            )
            ->when(
                $filters['voucher_type'] !== 'all',
                fn(Builder $query) => $query->where(
                    'voucher_type',
                    $filters['voucher_type']
                )
            )
            ->when(
                $filters['status'] !== 'all',
                fn(Builder $query) => $query->where('status', $filters['status'])
            )
            ->when(
                $filters['account_id'],
                fn(Builder $query, int $accountId) => $query->where(
                    'account_id',
                    $accountId
                )
            )
            ->when(
                $filters['from_date'],
                fn(Builder $query, string $date) => $query->whereDate(
                    'voucher_date',
                    '>=',
                    $date
                )
            )
            ->when(
                $filters['to_date'],
                fn(Builder $query, string $date) => $query->whereDate(
                    'voucher_date',
                    '<=',
                    $date
                )
            );

        $summary = (clone $baseQuery)
            ->where('status', Voucher::STATUS_COMPLETED)
            ->selectRaw(
                'COUNT(*) AS voucher_count, '
                    . 'COALESCE(SUM(CASE WHEN voucher_type = ? THEN amount ELSE 0 END), 0) AS receipt_total, '
                    . 'COALESCE(SUM(CASE WHEN voucher_type = ? THEN amount ELSE 0 END), 0) AS payment_total',
                [
                    Voucher::TYPE_RECEIPT,
                    Voucher::TYPE_PAYMENT,
                ]
            )
            ->first();

        $vouchers = $baseQuery
            ->with([
                'account:id,account_code,account_name,account_type',
                'creator:id,name',
            ])
            ->orderByDesc('voucher_date')
            ->orderByDesc('id')
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(fn(Voucher $voucher): array => $this->voucherPayload($voucher));

        $accounts = Account::query()
            ->forContext($companyId, $storeId)
            ->orderBy('account_name')
            ->get([
                'id',
                'account_code',
                'account_name',
                'account_type',
                'status',
            ])
            ->map(fn(Account $account): array => [
                'id' => $account->id,
                'code' => $account->account_code,
                'name' => $account->account_name,
                'type' => $account->account_type,
                'status' => (bool) $account->status,
            ])
            ->values();

        $receiptTotal = round((float) ($summary->receipt_total ?? 0), 2);
        $paymentTotal = round((float) ($summary->payment_total ?? 0), 2);

        return Inertia::render('Vouchers/Index', [
            'vouchers' => $vouchers,
            'accounts' => $accounts,
            'filters' => $filters,
            'summary' => [
                'voucher_count' => (int) ($summary->voucher_count ?? 0),
                'receipt_total' => $receiptTotal,
                'payment_total' => $paymentTotal,
                'net_flow' => round($receiptTotal - $paymentTotal, 2),
            ],
            'context' => $this->contextPayload($request, $companyId, $storeId),
        ]);
    }

    public function create(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $requestedType = (string) $request->query(
            'type',
            Voucher::TYPE_RECEIPT
        );

        $voucherType = in_array(
            $requestedType,
            array_keys(Voucher::types()),
            true
        )
            ? $requestedType
            : Voucher::TYPE_RECEIPT;

        $today = Carbon::now('Asia/Kolkata')->toDateString();

        $accounts = Account::query()
            ->forContext($companyId, $storeId)
            ->active()
            ->orderBy('account_name')
            ->get()
            ->map(fn(Account $account): array => [
                'id' => $account->id,
                'code' => $account->account_code,
                'name' => $account->account_name,
                'type' => $account->account_type,
                'payment_mode' => $account->payment_mode,
                'balance' => $account->balanceUntil($today),
            ])
            ->values();

        return Inertia::render('Vouchers/Create', [
            'accounts' => $accounts,
            'defaults' => [
                'voucher_type' => $voucherType,
                'voucher_date' => $today,
                'payment_mode' => $this->defaultPaymentMode($accounts->first()),
            ],
            'voucher_types' => Voucher::types(),
            'payment_modes' => $this->paymentModes(),
            'party_types' => $this->partyTypes(),
            'context' => $this->contextPayload($request, $companyId, $storeId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'voucher_type' => [
                'required',
                Rule::in(array_keys(Voucher::types())),
            ],
            'account_id' => ['required', 'integer'],
            'voucher_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999.99'],
            'payment_mode' => ['required', 'string', 'max:30'],
            'reference_no' => ['nullable', 'string', 'max:150'],
            'party_type' => [
                'nullable',
                Rule::in(array_keys($this->partyTypes())),
            ],
            'party_id' => ['nullable', 'integer'],
            'party_name' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $account = Account::query()
            ->forContext($companyId, $storeId)
            ->active()
            ->find($validated['account_id']);

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' => 'Select an active account from the current branch.',
            ]);
        }

        [$partyType, $partyId, $partyName] = $this->resolveParty(
            companyId: $companyId,
            storeId: $storeId,
            inputType: $validated['party_type'] ?? null,
            inputId: isset($validated['party_id'])
                ? (int) $validated['party_id']
                : null,
            inputName: $validated['party_name'] ?? null
        );

        $voucher = DB::transaction(function () use (
            $request,
            $validated,
            $companyId,
            $storeId,
            $account,
            $partyType,
            $partyId,
            $partyName
        ): Voucher {
            $voucher = Voucher::query()->create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'account_id' => $account->id,
                'voucher_type' => $validated['voucher_type'],
                'voucher_no' => Voucher::generateVoucherNo(
                    $companyId,
                    $storeId,
                    $validated['voucher_type']
                ),
                'voucher_date' => Carbon::parse(
                    $validated['voucher_date']
                )->toDateString(),
                'amount' => round((float) $validated['amount'], 2),
                'payment_mode' => $validated['payment_mode'],
                'reference_no' => $validated['reference_no'] ?? null,
                'party_type' => $partyType,
                'party_id' => $partyId,
                'party_name' => $partyName,
                'remarks' => $validated['remarks'] ?? null,
                'status' => Voucher::STATUS_COMPLETED,
                'created_by' => $request->user()?->id,
            ]);

            $this->accountTransactions->postVoucher($voucher);

            return $voucher;
        });

        return redirect()
            ->route('vouchers.show', $voucher)
            ->with('success', 'Voucher recorded successfully.');
    }

    public function show(Request $request, Voucher $voucher): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureVoucherContext($voucher, $companyId, $storeId);

        $voucher->load([
            'account',
            'party',
            'creator:id,name',
            'canceller:id,name',
            'accountTransactions' => fn($query) => $query
                ->orderBy('transaction_date')
                ->orderBy('id'),
        ]);

        return Inertia::render('Vouchers/Show', [
            'voucher' => $this->voucherPayload($voucher, true),
            'context' => $this->contextPayload($request, $companyId, $storeId),
        ]);
    }

    public function cancel(
        Request $request,
        Voucher $voucher
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureVoucherContext($voucher, $companyId, $storeId);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use (
            $request,
            $voucher,
            $companyId,
            $storeId
        ): void {
            $lockedVoucher = Voucher::query()
                ->forContext($companyId, $storeId)
                ->whereKey($voucher->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedVoucher->isCancelled()) {
                throw ValidationException::withMessages([
                    'voucher' => 'This voucher is already cancelled.',
                ]);
            }

            $reason = trim((string) $request->input('reason'));
            $remarks = $reason !== ''
                ? 'Voucher cancelled: ' . $reason
                : 'Voucher cancelled';

            $this->accountTransactions->reverseVoucher(
                voucher: $lockedVoucher,
                remarks: $remarks,
                createdBy: $request->user()?->id
            );

            $lockedVoucher->forceFill([
                'status' => Voucher::STATUS_CANCELLED,
                'cancelled_by' => $request->user()?->id,
                'cancelled_at' => now(),
            ])->save();
        });

        return back()->with('success', 'Voucher cancelled successfully.');
    }

    public function searchParties(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in(['customer', 'supplier', 'mechanic']),
            ],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $items = match ($validated['type']) {
            'customer' => Customer::query()
                ->where('company_id', $companyId)
                ->where('status', true)
                ->when($search, fn(Builder $query) => $query->where(
                    fn(Builder $inner) => $inner
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                ))
                ->orderBy('customer_name')
                ->limit(20)
                ->get()
                ->map(fn(Customer $customer): array => [
                    'id' => $customer->id,
                    'code' => $customer->customer_code,
                    'name' => $customer->customer_name,
                    'contact' => $customer->mobile,
                    'type' => 'customer',
                ]),
            'supplier' => Supplier::query()
                ->forCompany($companyId)
                ->forStore($storeId)
                ->active()
                ->when($search, fn(Builder $query) => $query->where(
                    fn(Builder $inner) => $inner
                        ->where('supplier_name', 'like', "%{$search}%")
                        ->orWhere('supplier_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                ))
                ->orderBy('supplier_name')
                ->limit(20)
                ->get()
                ->map(fn(Supplier $supplier): array => [
                    'id' => $supplier->id,
                    'code' => $supplier->supplier_code,
                    'name' => $supplier->supplier_name,
                    'contact' => $supplier->phone,
                    'type' => 'supplier',
                ]),
            'mechanic' => Mechanic::query()
                ->forContext($companyId, $storeId)
                ->active()
                ->search($search)
                ->orderBy('mechanic_name')
                ->limit(20)
                ->get()
                ->map(fn(Mechanic $mechanic): array => [
                    'id' => $mechanic->id,
                    'code' => $mechanic->mechanic_code,
                    'name' => $mechanic->mechanic_name,
                    'contact' => $mechanic->mobile,
                    'type' => 'mechanic',
                ]),
        };

        return response()->json([
            'data' => $items->values(),
        ]);
    }

    public function print(Request $request, Voucher $voucher): HttpResponse
    {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureVoucherContext($voucher, $companyId, $storeId);

        $voucher->load([
            'company',
            'store',
            'account',
            'party',
            'creator:id,name',
            'canceller:id,name',
            'accountTransactions' => fn($query) => $query
                ->orderBy('transaction_date')
                ->orderBy('id'),
        ]);

        return response()->view('vouchers.print', [
            'voucher' => $voucher,
            'paper' => $this->paper($request),
            'pdfMode' => false,
        ]);
    }

    public function pdf(Request $request, Voucher $voucher): HttpResponse
    {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureVoucherContext($voucher, $companyId, $storeId);

        $voucher->load([
            'company',
            'store',
            'account',
            'party',
            'creator:id,name',
            'canceller:id,name',
            'accountTransactions' => fn($query) => $query
                ->orderBy('transaction_date')
                ->orderBy('id'),
        ]);

        $paper = $this->paper($request);

        return Pdf::loadView('vouchers.pdf', [
            'voucher' => $voucher,
            'paper' => $paper,
            'pdfMode' => true,
        ])
            ->setPaper($paper === 'a5' ? 'a5' : 'a4', 'portrait')
            ->download(Str::slug($voucher->voucher_no) . '.pdf');
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'voucher_type' => [
                'nullable',
                Rule::in(array_merge(
                    ['all'],
                    array_keys(Voucher::types())
                )),
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'all',
                    Voucher::STATUS_COMPLETED,
                    Voucher::STATUS_CANCELLED,
                ]),
            ],
            'account_id' => ['nullable', 'integer'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'voucher_type' => $validated['voucher_type'] ?? 'all',
            'status' => $validated['status'] ?? 'all',
            'account_id' => isset($validated['account_id'])
                ? (int) $validated['account_id']
                : null,
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'per_page' => (int) ($validated['per_page'] ?? 20),
        ];
    }

    private function resolveParty(
        int $companyId,
        int $storeId,
        ?string $inputType,
        ?int $inputId,
        ?string $inputName
    ): array {
        $inputName = trim((string) $inputName);

        if (!$inputType || $inputType === 'other') {
            return [
                null,
                null,
                $inputName !== '' ? $inputName : null,
            ];
        }

        if (!$inputId) {
            throw ValidationException::withMessages([
                'party_id' => 'Select a valid ' . $inputType . '.',
            ]);
        }

        $party = match ($inputType) {
            'customer' => Customer::query()
                ->where('company_id', $companyId)
                ->where('status', true)
                ->find($inputId),
            'supplier' => Supplier::query()
                ->forCompany($companyId)
                ->forStore($storeId)
                ->active()
                ->find($inputId),
            'mechanic' => Mechanic::query()
                ->forContext($companyId, $storeId)
                ->active()
                ->find($inputId),
        };

        if (!$party) {
            throw ValidationException::withMessages([
                'party_id' => 'The selected party is not available in this branch.',
            ]);
        }

        $name = match ($inputType) {
            'customer' => $party->customer_name,
            'supplier' => $party->supplier_name,
            'mechanic' => $party->mechanic_name,
        };

        return [
            $party->getMorphClass(),
            $party->getKey(),
            $name,
        ];
    }

    private function voucherPayload(
        Voucher $voucher,
        bool $detailed = false
    ): array {
        $payload = [
            'id' => $voucher->id,
            'voucher_no' => $voucher->voucher_no,
            'voucher_type' => $voucher->voucher_type,
            'voucher_date' => $voucher->voucher_date?->toDateString(),
            'amount' => (float) $voucher->amount,
            'payment_mode' => $voucher->payment_mode,
            'reference_no' => $voucher->reference_no,
            'party_type' => $this->partyTypeLabel($voucher->party_type),
            'party_name' => $voucher->party_name,
            'remarks' => $voucher->remarks,
            'status' => $voucher->status,
            'is_cancelled' => $voucher->isCancelled(),
            'flow' => $voucher->isReceipt() ? 'in' : 'out',
            'account' => $voucher->relationLoaded('account') && $voucher->account
                ? [
                    'id' => $voucher->account->id,
                    'code' => $voucher->account->account_code,
                    'name' => $voucher->account->account_name,
                    'type' => $voucher->account->account_type,
                ]
                : null,
            'created_by' => $voucher->relationLoaded('creator')
                ? $voucher->creator?->name
                : null,
            'created_at' => $voucher->created_at?->toIso8601String(),
        ];

        if ($detailed) {
            $payload['party'] = $voucher->relationLoaded('party') && $voucher->party
                ? [
                    'id' => $voucher->party->getKey(),
                    'name' => $voucher->party_name,
                    'type' => $this->partyTypeLabel($voucher->party_type),
                ]
                : null;
            $payload['cancelled_by'] = $voucher->relationLoaded('canceller')
                ? $voucher->canceller?->name
                : null;
            $payload['cancelled_at'] = $voucher->cancelled_at?->toIso8601String();
            $payload['transactions'] = $voucher->relationLoaded('accountTransactions')
                ? $voucher->accountTransactions->map(fn($transaction): array => [
                    'id' => $transaction->id,
                    'date' => $transaction->transaction_date?->toDateString(),
                    'type' => $transaction->transaction_type,
                    'debit' => (float) $transaction->debit,
                    'credit' => (float) $transaction->credit,
                    'is_reversal' => (bool) $transaction->is_reversal,
                    'remarks' => $transaction->remarks,
                ])->values()
                : [];
        }

        return $payload;
    }

    private function context(Request $request): array
    {
        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');

        if ($companyId <= 0 || $storeId <= 0) {
            abort(403, 'Select an active company and branch.');
        }

        $storeExists = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->exists();

        if (!$storeExists) {
            abort(403, 'The selected branch is unavailable.');
        }

        return [$companyId, $storeId];
    }

    private function ensureVoucherContext(
        Voucher $voucher,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $voucher->company_id === $companyId
                && (int) $voucher->store_id === $storeId,
            404
        );
    }

    private function contextPayload(
        Request $request,
        int $companyId,
        int $storeId
    ): array {
        $store = Store::query()->find($storeId);

        return [
            'company_id' => $companyId,
            'store_id' => $storeId,
            'store_name' => $store?->store_name ?? $store?->name,
            'user_name' => $request->user()?->name,
        ];
    }

    private function partyTypes(): array
    {
        return [
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'mechanic' => 'Mechanic',
            'other' => 'Other',
        ];
    }

    private function paymentModes(): array
    {
        return [
            'cash' => 'Cash',
            'gpay' => 'GPay / UPI',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'cheque' => 'Cheque',
            'other' => 'Other',
        ];
    }

    private function defaultPaymentMode(?array $account): string
    {
        return (string) ($account['payment_mode'] ?? 'cash');
    }

    private function partyTypeLabel(?string $partyType): ?string
    {
        return match ($partyType) {
            Customer::class => 'customer',
            Supplier::class => 'supplier',
            Mechanic::class => 'mechanic',
            default => null,
        };
    }

    private function paper(Request $request): string
    {
        return strtolower((string) $request->query('paper')) === 'a5'
            ? 'a5'
            : 'a4';
    }
}
