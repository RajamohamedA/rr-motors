<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Store;
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

class AccountController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:account.view',
                only: ['index', 'show']
            ),
            new Middleware(
                'permission:account.create',
                only: ['create', 'store']
            ),
            new Middleware(
                'permission:account.edit',
                only: ['edit', 'update', 'toggleStatus']
            ),
        ];
    }
    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'account_type' => (string) $request->input('account_type', 'all'),
            'status' => (string) $request->input('status', 'active'),
            'per_page' => (int) $request->integer('per_page', 20),
        ];

        if (
            $filters['account_type'] !== 'all'
            && !array_key_exists($filters['account_type'], Account::types())
        ) {
            $filters['account_type'] = 'all';
        }

        if (!in_array($filters['status'], ['all', 'active', 'inactive'], true)) {
            $filters['status'] = 'active';
        }
        $filters['per_page'] = in_array(
            $filters['per_page'],
            [20, 50, 100],
            true
        ) ? $filters['per_page'] : 20;
        $accounts = Account::query()
            ->forContext($companyId, $storeId)
            ->select('accounts.*')
            ->selectSub(
                AccountTransaction::query()
                    ->selectRaw(
                        'COALESCE(SUM(account_transactions.debit '
                            . '- account_transactions.credit), 0)'
                    )
                    ->whereColumn(
                        'account_transactions.account_id',
                        'accounts.id'
                    ),
                'transaction_movement'
            )
            ->when(
                $filters['search'] !== '',
                function (Builder $query) use ($filters) {
                    $search = $filters['search'];
                    $query->where(function (Builder $query) use ($search) {
                        $query
                            ->where('account_name', 'like', "%{$search}%")
                            ->orWhere('account_code', 'like', "%{$search}%")
                            ->orWhere('bank_name', 'like', "%{$search}%")
                            ->orWhere('account_number', 'like', "%{$search}%")
                            ->orWhere('upi_id', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $filters['account_type'] !== 'all',
                fn(Builder $query) => $query->where(
                    'account_type',
                    $filters['account_type']
                )
            )
            ->when(
                $filters['status'] === 'active',
                fn(Builder $query) => $query->where('status', true)
            )
            ->when(
                $filters['status'] === 'inactive',
                fn(Builder $query) => $query->where('status', false)
            )
            ->orderByDesc('status')
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(fn(Account $account) => $this->accountPayload($account));
        $summaryRows = Account::query()
            ->forContext($companyId, $storeId)
            ->active()
            ->select('accounts.*')
            ->selectSub(
                AccountTransaction::query()
                    ->selectRaw(
                        'COALESCE(SUM(account_transactions.debit '
                            . '- account_transactions.credit), 0)'
                    )
                    ->whereColumn(
                        'account_transactions.account_id',
                        'accounts.id'
                    ),
                'transaction_movement'
            )
            ->get();
        $summary = [
            'total_balance' => round(
                $summaryRows->sum(fn(Account $account) =>
                $this->calculatedBalance($account)),
                2
            ),
            'cash_balance' => $this->sumType(
                $summaryRows,
                Account::TYPE_CASH
            ),
            'bank_balance' => $this->sumType(
                $summaryRows,
                Account::TYPE_BANK
            ),
            'digital_balance' => round(
                $this->sumType($summaryRows, Account::TYPE_UPI)
                    + $this->sumType($summaryRows, Account::TYPE_CARD),
                2
            ),
            'active_accounts' => $summaryRows->count(),
        ];
        return Inertia::render('Accounts/Index', [
            'accounts' => $accounts,
            'filters' => $filters,
            'summary' => $summary,
            'accountTypes' => Account::types(),
            'context' => $this->contextPayload($request),
        ]);
    }
    public function create(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);
        return Inertia::render('Accounts/Create', [
            'nextCode' => $this->nextAccountCode(
                $companyId,
                $storeId,
                Account::TYPE_BANK
            ),
            'accountTypes' => Account::types(),
            'paymentModes' => Account::paymentModes(),
            'context' => $this->contextPayload($request),
        ]);
    }
    public function store(Request $request): RedirectResponse
    {
        [$companyId, $storeId] = $this->context($request);
        $validated = $this->validateAccount($request, $companyId, $storeId);
        $validated = $this->normaliseAccountData($validated);
        $account = DB::transaction(function () use (
            $validated,
            $companyId,
            $storeId
        ) {
            return Account::query()->create([
                ...$validated,
                'company_id' => $companyId,
                'store_id' => $storeId,
                'account_code' => strtoupper($validated['account_code']),
                'opening_balance' => round(
                    (float) $validated['opening_balance'],
                    2
                ),
                'is_system' => false,
                'status' => (bool) ($validated['status'] ?? true),
                'created_by' => auth()->id(),
            ]);
        });
        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Cash/Bank account created successfully.');
    }
    public function show(
        Request $request,
        Account $account
    ): Response {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($account, $companyId, $storeId);
        $validatedFilters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'transaction_type' => [
                'nullable',
                Rule::in([
                    'all',
                    ...AccountTransaction::transactionTypes(),
                ]),
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $filters = [
            'from_date' => (string) ($validatedFilters['from_date']
                ?? now()->startOfMonth()->toDateString()),
            'to_date' => (string) ($validatedFilters['to_date']
                ?? now()->toDateString()),
            'transaction_type' => (string) ($validatedFilters['transaction_type']
                ?? 'all'),
            'search' => trim((string) ($validatedFilters['search'] ?? '')),
            'per_page' => (int) ($validatedFilters['per_page'] ?? 50),
        ];
        $filters['per_page'] = in_array(
            $filters['per_page'],
            [20, 50, 100],
            true
        ) ? $filters['per_page'] : 50;
        $openingMovement = AccountTransaction::query()
            ->forContext($companyId, $storeId)
            ->forAccount((int) $account->id)
            ->whereDate('transaction_date', '<', $filters['from_date'])
            ->selectRaw('COALESCE(SUM(debit - credit), 0) AS movement')
            ->value('movement');
        $openingBalance = round(
            (float) $account->opening_balance + (float) $openingMovement,
            2
        );
        $transactionQuery = AccountTransaction::query()
            ->forContext($companyId, $storeId)
            ->forAccount((int) $account->id)
            ->betweenDates($filters['from_date'], $filters['to_date'])
            ->when(
                $filters['transaction_type'] !== 'all',
                fn(Builder $query) => $query->ofType(
                    $filters['transaction_type']
                )
            )
            ->when(
                $filters['search'] !== '',
                function (Builder $query) use ($filters) {
                    $search = $filters['search'];
                    $query->where(function (Builder $query) use ($search) {
                        $query
                            ->where('reference_no', 'like', "%{$search}%")
                            ->orWhere('external_reference', 'like', "%{$search}%")
                            ->orWhere('remarks', 'like', "%{$search}%");
                    });
                }
            );
        $periodTotals = (clone $transactionQuery)
            ->selectRaw(
                'COALESCE(SUM(debit), 0) AS total_debit, '
                    . 'COALESCE(SUM(credit), 0) AS total_credit'
            )
            ->first();
        $runningBalance = $openingBalance;
        $allBalances = (clone $transactionQuery)
            ->chronological()
            ->get()
            ->mapWithKeys(function (AccountTransaction $entry) use (&$runningBalance) {
                $runningBalance = round(
                    $runningBalance + $entry->balanceEffect(),
                    2
                );
                return [$entry->id => $runningBalance];
            });
        $transactions = (clone $transactionQuery)
            ->with('creator:id,name')
            ->chronological()
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(function (AccountTransaction $entry) use ($allBalances) {
                return [
                    'id' => $entry->id,
                    'transaction_date' => $entry->transaction_date?->toDateString(),
                    'transaction_type' => $entry->transaction_type,
                    'type_label' => $this->transactionTypeLabel(
                        $entry->transaction_type
                    ),
                    'reference_type' => $entry->reference_type,
                    'reference_id' => $entry->reference_id,
                    'reference_no' => $entry->reference_no,
                    'debit' => (float) $entry->debit,
                    'credit' => (float) $entry->credit,
                    'balance' => (float) ($allBalances[$entry->id] ?? 0),
                    'payment_mode' => $entry->payment_mode,
                    'external_reference' => $entry->external_reference,
                    'remarks' => $entry->remarks,
                    'is_reversal' => (bool) $entry->is_reversal,
                    'created_by' => $entry->creator?->name ?? 'System',
                ];
            });
        $closingBalance = round(
            $openingBalance
                + (float) $periodTotals->total_debit
                - (float) $periodTotals->total_credit,
            2
        );
        $totalMovement = AccountTransaction::query()
            ->forContext($companyId, $storeId)
            ->forAccount((int) $account->id)
            ->selectRaw('COALESCE(SUM(debit - credit), 0) AS movement')
            ->value('movement');
        $account->setAttribute(
            'transaction_movement',
            (float) $totalMovement
        );
        return Inertia::render('Accounts/Show', [
            'account' => [
                ...$this->accountPayload($account),
                'opening_period_balance' => $openingBalance,
                'closing_period_balance' => $closingBalance,
            ],
            'transactions' => $transactions,
            'summary' => [
                'opening_balance' => $openingBalance,
                'period_debit' => (float) $periodTotals->total_debit,
                'period_credit' => (float) $periodTotals->total_credit,
                'closing_balance' => $closingBalance,
            ],
            'filters' => $filters,
            'transactionTypes' => $this->transactionTypes(),
            'context' => $this->contextPayload($request),
        ]);
    }
    public function edit(
        Request $request,
        Account $account
    ): Response {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($account, $companyId, $storeId);
        return Inertia::render('Accounts/Edit', [
            'account' => $this->accountPayload($account),
            'accountTypes' => Account::types(),
            'paymentModes' => Account::paymentModes(),
            'context' => $this->contextPayload($request),
        ]);
    }
    public function update(
        Request $request,
        Account $account
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($account, $companyId, $storeId);
        $validated = $this->validateAccount(
            $request,
            $companyId,
            $storeId,
            $account
        );
        $validated = $this->normaliseAccountData($validated);

        $openingChanged =
            round((float) $validated['opening_balance'], 2)
            !== round((float) $account->opening_balance, 2)
            || ($validated['opening_balance_date'] ?? null)
            !== $account->opening_balance_date?->toDateString();

        $hasTransactions = $account->transactions()->exists();

        if ($openingChanged && $hasTransactions) {
            throw ValidationException::withMessages([
                'opening_balance' =>
                'Opening balance cannot be changed after transactions exist. '
                    . 'Use an account adjustment instead.',
            ]);
        }

        if (
            $hasTransactions
            && $validated['account_type'] !== $account->account_type
        ) {
            throw ValidationException::withMessages([
                'account_type' =>
                'Account type cannot be changed after transactions exist.',
            ]);
        }
        DB::transaction(function () use ($account, $validated) {
            $payload = [
                ...$validated,
                'account_code' => strtoupper($validated['account_code']),
                'opening_balance' => round(
                    (float) $validated['opening_balance'],
                    2
                ),
                'status' => (bool) ($validated['status'] ?? $account->status),
            ];
            if ($account->is_system) {
                $payload['account_code'] = $account->account_code;
                $payload['account_type'] = $account->account_type;
                $payload['payment_mode'] = $account->payment_mode;
                $payload['status'] = true;
            }
            $account->update($payload);
        });
        return redirect()
            ->route('accounts.show', $account)
            ->with('success', 'Cash/Bank account updated successfully.');
    }
    public function toggleStatus(
        Request $request,
        Account $account
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($account, $companyId, $storeId);
        if ($account->is_system) {
            throw ValidationException::withMessages([
                'account' => 'System accounts cannot be deactivated.',
            ]);
        }
        $account->update([
            'status' => !$account->status,
        ]);
        return back()->with(
            'success',
            $account->status
                ? 'Account activated successfully.'
                : 'Account deactivated successfully.'
        );
    }
    private function validateAccount(
        Request $request,
        int $companyId,
        int $storeId,
        ?Account $account = null
    ): array {
        $accountCodeRule = Rule::unique('accounts', 'account_code')
            ->where(fn($query) => $query
                ->where('company_id', $companyId)
                ->where('store_id', $storeId))
            ->ignore($account?->id);
        return $request->validate([
            'account_code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9\_-]+$/',
                $accountCodeRule,
            ],
            'account_name' => ['required', 'string', 'max:150'],
            'account_type' => [
                'required',
                Rule::in(array_keys(Account::types())),
            ],
            'payment_mode' => [
                'nullable',
                Rule::in(array_keys(Account::paymentModes())),
            ],
            'bank_name' => [
                Rule::requiredIf(
                    $request->input('account_type') === Account::TYPE_BANK
                ),
                'nullable',
                'string',
                'max:150',
            ],
            'account_number' => [
                Rule::requiredIf(
                    $request->input('account_type') === Account::TYPE_BANK
                ),
                'nullable',
                'string',
                'max:50',
            ],
            'upi_id' => [
                Rule::requiredIf(
                    $request->input('account_type') === Account::TYPE_UPI
                ),
                'nullable',
                'string',
                'max:100',
            ],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'opening_balance_date' => [
                Rule::requiredIf(
                    (float) $request->input('opening_balance', 0) > 0
                ),
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'status' => ['nullable', 'boolean'],
        ]);
    }

    private function normaliseAccountData(array $validated): array
    {
        $type = $validated['account_type'];

        $validated['account_code'] = strtoupper(
            trim((string) $validated['account_code'])
        );
        $validated['account_name'] = trim(
            (string) $validated['account_name']
        );
        $validated['opening_balance'] = round(
            (float) $validated['opening_balance'],
            2
        );

        $validated['payment_mode'] = $validated['payment_mode']
            ?? match ($type) {
                Account::TYPE_CASH => 'cash',
                Account::TYPE_BANK => 'bank_transfer',
                Account::TYPE_UPI => 'gpay',
                Account::TYPE_CARD => 'card',
                default => 'other',
            };

        $validated['bank_name'] = $type === Account::TYPE_BANK
            ? $this->nullableTrim($validated['bank_name'] ?? null)
            : null;
        $validated['account_number'] = $type === Account::TYPE_BANK
            ? $this->nullableTrim($validated['account_number'] ?? null)
            : null;
        $validated['upi_id'] = $type === Account::TYPE_UPI
            ? $this->nullableTrim($validated['upi_id'] ?? null)
            : null;

        if ($validated['opening_balance'] <= 0) {
            $validated['opening_balance_date'] = null;
        }

        return $validated;
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function accountPayload(Account $account): array
    {
        $movement = (float) ($account->transaction_movement ?? 0);
        return [
            'id' => $account->id,
            'account_code' => $account->account_code,
            'account_name' => $account->account_name,
            'account_type' => $account->account_type,
            'account_type_label' => Account::types()[$account->account_type]
                ?? ucfirst($account->account_type),
            'payment_mode' => $account->payment_mode,
            'bank_name' => $account->bank_name,
            'account_number' => $account->account_number,
            'masked_account_number' => $this->maskAccountNumber(
                $account->account_number
            ),
            'upi_id' => $account->upi_id,
            'opening_balance' => (float) $account->opening_balance,
            'opening_balance_date' => $account->opening_balance_date
                ?->toDateString(),
            'current_balance' => round(
                (float) $account->opening_balance + $movement,
                2
            ),
            'is_system' => (bool) $account->is_system,
            'status' => (bool) $account->status,
            'created_at' => $account->created_at?->toDateTimeString(),
        ];
    }
    private function calculatedBalance(Account $account): float
    {
        return round(
            (float) $account->opening_balance
                + (float) ($account->transaction_movement ?? 0),
            2
        );
    }
    private function sumType($accounts, string $type): float
    {
        return round(
            $accounts
                ->where('account_type', $type)
                ->sum(fn(Account $account) =>
                $this->calculatedBalance($account)),
            2
        );
    }
    private function maskAccountNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }
        $length = strlen($number);
        if ($length <= 4) {
            return $number;
        }
        return str_repeat('•', $length - 4) . substr($number, -4);
    }
    private function nextAccountCode(
        int $companyId,
        int $storeId,
        string $type
    ): string {
        $prefix = match ($type) {
            Account::TYPE_CASH => 'CSH',
            Account::TYPE_BANK => 'BNK',
            Account::TYPE_UPI => 'UPI',
            Account::TYPE_CARD => 'CRD',
            default => 'ACC',
        };
        $lastCode = Account::query()
            ->forContext($companyId, $storeId)
            ->where('account_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('account_code');
        $number = $lastCode
            ? ((int) preg_replace('/\D/', '', $lastCode)) + 1
            : 1;
        return $prefix . str_pad(
            (string) $number,
            4,
            '0',
            STR_PAD_LEFT
        );
    }
    private function transactionTypes(): array
    {
        return collect(AccountTransaction::transactionTypes())
            ->mapWithKeys(fn(string $type) => [
                $type => $this->transactionTypeLabel($type),
            ])
            ->all();
    }
    private function transactionTypeLabel(string $type): string
    {
        return str($type)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }
    private function ensureContext(
        Account $account,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $account->company_id === $companyId
                && (int) $account->store_id === $storeId,
            404
        );
    }
    private function context(Request $request): array
    {
        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');
        abort_if(
            $companyId <= 0 || $storeId <= 0,
            403,
            'Select a company and branch before opening accounts.'
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
    private function contextPayload(Request $request): array
    {
        return [
            'company_id' => (int) $request->session()->get('active_company_id'),
            'store_id' => (int) $request->session()->get('active_store_id'),
            'company_name' => $request->session()->get('active_company_name'),
            'store_name' => $request->session()->get('active_store_name'),
        ];
    }
}
