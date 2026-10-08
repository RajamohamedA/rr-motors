<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Expense;
use App\Models\Store;
use App\Services\AccountTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ExpenseController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly AccountTransactionService $accountTransactions
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:expense.view',
                only: ['index', 'show']
            ),
            new Middleware(
                'permission:expense.create',
                only: ['create', 'store']
            ),
            new Middleware(
                'permission:expense.delete',
                only: ['cancel']
            ),
            new Middleware(
                'permission:expense.print',
                only: ['print', 'pdf']
            ),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'account_id' => ['nullable', 'integer'],
            'expense_category' => ['nullable', 'string', 'max:100'],
            'period' => [
                'nullable',
                Rule::in([
                    'today',
                    'yesterday',
                    'month',
                    'all',
                    'custom',
                ]),
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'all',
                    Expense::STATUS_COMPLETED,
                    Expense::STATUS_CANCELLED,
                ]),
            ],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
        ]);

        $period = $filters['period'] ?? 'today';
        $today = Carbon::now('Asia/Kolkata');
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;

        if ($period === 'today') {
            $fromDate = $today->toDateString();
            $toDate = $today->toDateString();
        } elseif ($period === 'yesterday') {
            $fromDate = $today->copy()->subDay()->toDateString();
            $toDate = $fromDate;
        } elseif ($period === 'month') {
            $fromDate = $today->copy()->startOfMonth()->toDateString();
            $toDate = $today->copy()->endOfMonth()->toDateString();
        } elseif ($period === 'all') {
            $fromDate = null;
            $toDate = null;
        } elseif (!$fromDate && !$toDate) {
            $period = 'today';
            $fromDate = $today->toDateString();
            $toDate = $today->toDateString();
        }

        $filters = [
            'search' => trim((string) ($filters['search'] ?? '')),
            'account_id' => isset($filters['account_id'])
                ? (int) $filters['account_id']
                : null,
            'expense_category' => trim(
                (string) ($filters['expense_category'] ?? '')
            ),
            'period' => $period,
            'status' => $filters['status'] ?? 'all',
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'per_page' => (int) ($filters['per_page'] ?? 20),
        ];

        $query = Expense::query()
            ->forContext($companyId, $storeId)
            ->search($filters['search'])
            ->when(
                $filters['account_id'],
                fn(Builder $builder, int $accountId) =>
                $builder->where('account_id', $accountId)
            )
            ->when(
                $filters['expense_category'] !== '',
                fn(Builder $builder, string $category) =>
                $builder->where('expense_category', $category)
            )
            ->when(
                $filters['status'] !== 'all',
                fn(Builder $builder, string $status) =>
                $builder->where('status', $status)
            )
            ->when(
                $filters['from_date'],
                fn(Builder $builder, string $date) =>
                $builder->whereDate('expense_date', '>=', $date)
            )
            ->when(
                $filters['to_date'],
                fn(Builder $builder, string $date) =>
                $builder->whereDate('expense_date', '<=', $date)
            );

        $summary = (clone $query)
            ->selectRaw(
                'COUNT(*) AS total_count, '
                    . 'COALESCE(SUM(CASE WHEN status = ? THEN amount ELSE 0 END), 0) AS completed_total, '
                    . 'COALESCE(SUM(CASE WHEN status = ? THEN amount ELSE 0 END), 0) AS cancelled_total',
                [
                    Expense::STATUS_COMPLETED,
                    Expense::STATUS_CANCELLED,
                ]
            )
            ->first();

        $expenses = $query
            ->with([
                'account:id,account_code,account_name,account_type',
                'creator:id,name',
            ])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(
                fn(Expense $expense): array =>
                $this->expensePayload($expense)
            );

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'accounts' => $this->accounts($companyId, $storeId, false),
            'categories' => $this->categories($companyId, $storeId),
            'filters' => $filters,
            'summary' => [
                'total_count' => (int) ($summary->total_count ?? 0),
                'completed_total' => round(
                    (float) ($summary->completed_total ?? 0),
                    2
                ),
                'cancelled_total' => round(
                    (float) ($summary->cancelled_total ?? 0),
                    2
                ),
                'period' => $filters['period'],
                'period_label' => $this->periodLabel(
                    $filters['period'],
                    $filters['from_date'],
                    $filters['to_date']
                ),
            ],
            'context' => $this->contextPayload(
                $request,
                $companyId,
                $storeId
            ),
        ]);
    }

    public function create(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        return Inertia::render('Expenses/Create', [
            'accounts' => $this->accounts($companyId, $storeId, true),
            'categories' => $this->categories($companyId, $storeId),
            'payment_modes' => $this->paymentModes(),
            'defaults' => [
                'expense_date' => Carbon::now('Asia/Kolkata')->toDateString(),
            ],
            'context' => $this->contextPayload(
                $request,
                $companyId,
                $storeId
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'account_id' => ['required', 'integer'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'expense_category' => ['required', 'string', 'max:100'],
            'payee_name' => ['nullable', 'string', 'max:150'],
            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999999.99',
            ],
            'payment_mode' => [
                'required',
                Rule::in(array_keys($this->paymentModes())),
            ],
            'reference_no' => ['nullable', 'string', 'max:150'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $account = Account::query()
            ->forContext($companyId, $storeId)
            ->active()
            ->find($validated['account_id']);

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' =>
                'Select an active Cash/Bank account from the current branch.',
            ]);
        }

        $expense = DB::transaction(function () use (
            $request,
            $validated,
            $companyId,
            $storeId,
            $account
        ): Expense {
            $amount = round((float) $validated['amount'], 2);

            $this->accountTransactions->ensureSufficientBalance(
                companyId: $companyId,
                storeId: $storeId,
                accountId: (int) $account->id,
                requiredAmount: $amount,
                asOfDate: $validated['expense_date'],
                lock: true
            );

            $expense = Expense::query()->create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'account_id' => $account->id,
                'expense_no' => Expense::generateExpenseNo(
                    $companyId,
                    $storeId
                ),
                'expense_date' => $validated['expense_date'],
                'expense_category' => trim(
                    $validated['expense_category']
                ),
                'payee_name' => $this->nullableString(
                    $validated['payee_name'] ?? null
                ),
                'amount' => $amount,
                'payment_mode' => $validated['payment_mode'],
                'reference_no' => $this->nullableString(
                    $validated['reference_no'] ?? null
                ),
                'remarks' => $this->nullableString(
                    $validated['remarks'] ?? null
                ),
                'status' => Expense::STATUS_COMPLETED,
                'created_by' => $request->user()?->id,
            ]);

            $this->accountTransactions->credit(
                companyId: $companyId,
                storeId: $storeId,
                accountId: (int) $account->id,
                transactionDate: $expense->expense_date->toDateString(),
                transactionType: AccountTransaction::TYPE_EXPENSE,
                referenceType: $expense->getMorphClass(),
                referenceId: (int) $expense->id,
                amount: $amount,
                referenceNo: $expense->expense_no,
                paymentMode: $expense->payment_mode,
                externalReference: $expense->reference_no,
                remarks: $expense->remarks
                    ?: 'Expense: ' . $expense->expense_category,
                createdBy: $request->user()?->id
            );

            return $expense;
        });

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(
        Request $request,
        Expense $expense
    ): Response {
        [$companyId, $storeId] = $this->context($request);

        $this->authoriseExpense($expense, $companyId, $storeId);

        $expense->load([
            'company',
            'store',
            'account',
            'creator:id,name',
            'canceller:id,name',
            'accountTransactions' => fn($query) =>
            $query->orderBy('transaction_date')->orderBy('id'),
        ]);

        return Inertia::render('Expenses/Show', [
            'expense' => $this->expensePayload($expense, true),
            'context' => $this->contextPayload(
                $request,
                $companyId,
                $storeId
            ),
        ]);
    }

    public function cancel(
        Request $request,
        Expense $expense
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);

        $this->authoriseExpense($expense, $companyId, $storeId);

        $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use (
            $request,
            $expense,
            $companyId,
            $storeId
        ): void {
            $lockedExpense = Expense::query()
                ->forContext($companyId, $storeId)
                ->whereKey($expense->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedExpense->isCancelled()) {
                throw ValidationException::withMessages([
                    'expense' => 'This expense has already been cancelled.',
                ]);
            }

            $cancelRemarks = $this->nullableString(
                $request->input('remarks')
            ) ?: 'Cancellation of expense ' . $lockedExpense->expense_no;

            $this->accountTransactions->reverseReference(
                companyId: $companyId,
                storeId: $storeId,
                referenceType: $lockedExpense->getMorphClass(),
                referenceId: (int) $lockedExpense->id,
                remarks: $cancelRemarks,
                createdBy: $request->user()?->id,
                transactionDate: Carbon::now('Asia/Kolkata')->toDateString()
            );

            $lockedExpense->forceFill([
                'status' => Expense::STATUS_CANCELLED,
                'cancelled_by' => $request->user()?->id,
                'cancelled_at' => now(),
                'remarks' => $lockedExpense->remarks
                    ?: $cancelRemarks,
            ])->save();
        });

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', 'Expense cancelled and account entry reversed.');
    }

    public function print(
        Request $request,
        Expense $expense
    ): HttpResponse {
        [$companyId, $storeId] = $this->context($request);

        $this->authoriseExpense($expense, $companyId, $storeId);

        $expense->load([
            'company',
            'store',
            'account',
            'creator:id,name',
            'canceller:id,name',
            'accountTransactions' => fn($query) =>
            $query->orderBy('transaction_date')->orderBy('id'),
        ]);

        return response()->view('expenses.print', [
            'expense' => $expense,
            'paper' => $this->paper($request),
            'pdfMode' => false,
        ]);
    }

    public function pdf(
        Request $request,
        Expense $expense
    ): HttpResponse {
        [$companyId, $storeId] = $this->context($request);

        $this->authoriseExpense($expense, $companyId, $storeId);

        $expense->load([
            'company',
            'store',
            'account',
            'creator:id,name',
            'canceller:id,name',
            'accountTransactions' => fn($query) =>
            $query->orderBy('transaction_date')->orderBy('id'),
        ]);

        $paper = $this->paper($request);

        return Pdf::loadView('expenses.pdf', [
            'expense' => $expense,
            'paper' => $paper,
            'pdfMode' => true,
        ])
            ->setPaper($paper, 'portrait')
            ->download($expense->expense_no . '.pdf');
    }

    private function accounts(
        int $companyId,
        int $storeId,
        bool $activeOnly
    ): array {
        return Account::query()
            ->forContext($companyId, $storeId)
            ->when($activeOnly, fn(Builder $query) => $query->active())
            ->orderBy('account_name')
            ->get()
            ->map(fn(Account $account): array => [
                'id' => $account->id,
                'code' => $account->account_code,
                'name' => $account->account_name,
                'type' => $account->account_type,
                'payment_mode' => $account->payment_mode,
                'balance' => $account->balanceUntil(),
                'status' => (bool) $account->status,
            ])
            ->values()
            ->all();
    }

    private function categories(int $companyId, int $storeId): array
    {
        $stored = Expense::query()
            ->forContext($companyId, $storeId)
            ->whereNotNull('expense_category')
            ->where('expense_category', '!=', '')
            ->distinct()
            ->orderBy('expense_category')
            ->pluck('expense_category');

        return $stored
            ->merge([
                'Electricity',
                'Rent',
                'Salary and Wages',
                'Transport',
                'Repairs and Maintenance',
                'Office Supplies',
                'Telephone and Internet',
                'Fuel',
                'Miscellaneous',
            ])
            ->filter()
            ->unique(fn(string $value) => mb_strtolower($value))
            ->sort()
            ->values()
            ->all();
    }

    private function paymentModes(): array
    {
        return [
            'cash' => 'Cash',
            'gpay' => 'GPay / UPI',
            'bank_transfer' => 'Bank Transfer',
            'card' => 'Card',
            'cheque' => 'Cheque',
        ];
    }

    private function periodLabel(
        string $period,
        ?string $fromDate,
        ?string $toDate
    ): string {
        return match ($period) {
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'month' => 'This Month',
            'all' => 'All Time',
            default => $fromDate && $toDate
                ? Carbon::parse($fromDate)->format('d M Y')
                . ' - '
                . Carbon::parse($toDate)->format('d M Y')
                : 'Custom Period',
        };
    }

    private function expensePayload(
        Expense $expense,
        bool $detailed = false
    ): array {
        $payload = [
            'id' => $expense->id,
            'expense_no' => $expense->expense_no,
            'expense_date' => $expense->expense_date?->toDateString(),
            'expense_category' => $expense->expense_category,
            'payee_name' => $expense->payee_name,
            'amount' => (float) $expense->amount,
            'payment_mode' => $expense->payment_mode,
            'reference_no' => $expense->reference_no,
            'remarks' => $expense->remarks,
            'status' => $expense->status,
            'is_cancelled' => $expense->isCancelled(),
            'account' => $expense->account
                ? [
                    'id' => $expense->account->id,
                    'code' => $expense->account->account_code,
                    'name' => $expense->account->account_name,
                    'type' => $expense->account->account_type,
                ]
                : null,
            'created_by' => $expense->creator?->name,
            'cancelled_by' => $expense->canceller?->name,
            'cancelled_at' => $expense->cancelled_at?->toIso8601String(),
            'show_url' => route('expenses.show', $expense),
        ];

        if ($detailed) {
            $payload['print_url'] = route('expenses.print', $expense);
            $payload['pdf_url'] = route('expenses.pdf', $expense);
            $payload['transactions'] = $expense->accountTransactions
                ->map(fn(AccountTransaction $transaction): array => [
                    'id' => $transaction->id,
                    'date' => $transaction->transaction_date?->toDateString(),
                    'type' => $transaction->transaction_type,
                    'debit' => (float) $transaction->debit,
                    'credit' => (float) $transaction->credit,
                    'is_reversal' => (bool) $transaction->is_reversal,
                    'remarks' => $transaction->remarks,
                ])
                ->values()
                ->all();
        }

        return $payload;
    }

    private function paper(Request $request): string
    {
        return strtolower((string) $request->query('paper', 'a4')) === 'a5'
            ? 'a5'
            : 'a4';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
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

    private function authoriseExpense(
        Expense $expense,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $expense->company_id === $companyId
                && (int) $expense->store_id === $storeId,
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
}
