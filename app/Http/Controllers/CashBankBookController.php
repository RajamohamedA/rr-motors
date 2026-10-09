<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashBankBookController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:accounts-report.view',
                only: ['index', 'print', 'pdf', 'excel']
            ),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->filters($request);

        $accounts = $this->accounts($companyId, $storeId);
        $selectedAccountIds = $this->selectedAccountIds(
            $accounts,
            $filters['account_id']
        );

        $openingBalances = $this->openingBalances(
            $accounts,
            $selectedAccountIds,
            $companyId,
            $storeId,
            $filters['from_date']
        );

        $runningBalances = $openingBalances;

        $transactions = $this->transactionQuery(
            companyId: $companyId,
            storeId: $storeId,
            accountIds: $selectedAccountIds,
            filters: $filters
        )
            ->paginate($filters['per_page'])
            ->withQueryString();

        $pageOpeningAdjustments = $this->movementBeforePage(
            companyId: $companyId,
            storeId: $storeId,
            accountIds: $selectedAccountIds,
            filters: $filters,
            firstTransaction: $transactions->first()
        );

        foreach ($pageOpeningAdjustments as $accountId => $movement) {
            $runningBalances[$accountId] = round(
                ($runningBalances[$accountId] ?? 0) + $movement,
                2
            );
        }

        $transactions->through(
            function (AccountTransaction $transaction) use (&$runningBalances): array {
                $accountId = (int) $transaction->account_id;
                $runningBalances[$accountId] = round(
                    ($runningBalances[$accountId] ?? 0)
                        + (float) $transaction->debit
                        - (float) $transaction->credit,
                    2
                );

                return $this->transactionPayload(
                    $transaction,
                    $runningBalances[$accountId]
                );
            }
        );

        return Inertia::render('CashBankBook/Index', [
            'transactions' => $transactions,
            'accounts' => $accounts->map(
                fn(Account $account): array => $this->accountPayload($account)
            )->values(),
            'filters' => $filters,
            'summary' => $this->summary(
                companyId: $companyId,
                storeId: $storeId,
                accountIds: $selectedAccountIds,
                accounts: $accounts,
                openingBalances: $openingBalances,
                filters: $filters
            ),
            'transaction_types' => $this->transactionTypes(),
            'context' => $this->contextPayload(
                $request,
                $companyId,
                $storeId
            ),
        ]);
    }

    public function print(Request $request): HttpResponse
    {
        return response()->view(
            'cash_bank_book.print',
            $this->printPayload($request, false)
        );
    }

    public function pdf(Request $request): HttpResponse
    {
        $payload = $this->printPayload($request, true);

        return Pdf::loadView(
            'cash_bank_book.pdf',
            $payload
        )
            ->setPaper($payload['paper'], 'portrait')
            ->download(
                'cash-bank-book-'
                    . $payload['filters']['from_date']
                    . '-to-'
                    . $payload['filters']['to_date']
                    . '.pdf'
            );
    }

    public function excel(Request $request): StreamedResponse
    {
        $payload = $this->printPayload($request, false);
        $fileName = 'cash-bank-book-'
            . $payload['filters']['from_date']
            . '-to-'
            . $payload['filters']['to_date']
            . '.csv';

        return response()->streamDownload(
            function () use ($payload): void {
                $stream = fopen('php://output', 'wb');
                fwrite($stream, "\xEF\xBB\xBF");

                fputcsv($stream, [
                    'Date',
                    'Account',
                    'Account Code',
                    'Transaction Type',
                    'Reference No',
                    'Payment Mode',
                    'Remarks',
                    'Money In',
                    'Money Out',
                    'Running Balance',
                ]);

                foreach ($payload['rows'] as $row) {
                    fputcsv($stream, [
                        $row['date'],
                        $row['account_name'],
                        $row['account_code'],
                        $row['transaction_type_label'],
                        $row['reference_no'],
                        $row['payment_mode'],
                        $row['remarks'],
                        number_format($row['debit'], 2, '.', ''),
                        number_format($row['credit'], 2, '.', ''),
                        number_format($row['running_balance'], 2, '.', ''),
                    ]);
                }

                fclose($stream);
            },
            $fileName,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function printPayload(Request $request, bool $pdfMode): array
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->filters($request);
        $accounts = $this->accounts($companyId, $storeId);
        $accountIds = $this->selectedAccountIds(
            $accounts,
            $filters['account_id']
        );
        $openingBalances = $this->openingBalances(
            $accounts,
            $accountIds,
            $companyId,
            $storeId,
            $filters['from_date']
        );
        $runningBalances = $openingBalances;

        $rows = $this->transactionQuery(
            companyId: $companyId,
            storeId: $storeId,
            accountIds: $accountIds,
            filters: $filters
        )
            ->get()
            ->map(function (AccountTransaction $transaction) use (&$runningBalances): array {
                $accountId = (int) $transaction->account_id;
                $runningBalances[$accountId] = round(
                    ($runningBalances[$accountId] ?? 0)
                        + (float) $transaction->debit
                        - (float) $transaction->credit,
                    2
                );

                return $this->transactionPayload(
                    $transaction,
                    $runningBalances[$accountId]
                );
            });

        return [
            'rows' => $rows,
            'accounts' => $accounts
                ->whereIn('id', $accountIds)
                ->values(),
            'filters' => $filters,
            'summary' => $this->summary(
                companyId: $companyId,
                storeId: $storeId,
                accountIds: $accountIds,
                accounts: $accounts,
                openingBalances: $openingBalances,
                filters: $filters
            ),
            'store' => Store::query()
                ->with('company')
                ->findOrFail($storeId),
            'paper' => $this->paper($request),
            'pdfMode' => $pdfMode,
            'generatedAt' => Carbon::now('Asia/Kolkata'),
        ];
    }

    private function transactionQuery(
        int $companyId,
        int $storeId,
        array $accountIds,
        array $filters
    ): Builder {
        return AccountTransaction::query()
            ->with('account:id,account_code,account_name,account_type')
            ->forContext($companyId, $storeId)
            ->whereIn('account_id', $accountIds)
            ->betweenDates(
                $filters['from_date'],
                $filters['to_date']
            )
            ->when(
                $filters['transaction_type'] !== '',
                fn(Builder $query) => $query->where(
                    'transaction_type',
                    $filters['transaction_type']
                )
            )
            ->when(
                $filters['search'] !== '',
                function (Builder $query) use ($filters): void {
                    $search = $filters['search'];
                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('reference_no', 'like', "%{$search}%")
                            ->orWhere('external_reference', 'like', "%{$search}%")
                            ->orWhere('remarks', 'like', "%{$search}%")
                            ->orWhereHas(
                                'account',
                                fn(Builder $accountQuery) => $accountQuery
                                    ->where('account_name', 'like', "%{$search}%")
                                    ->orWhere('account_code', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->chronological();
    }

    private function openingBalances(
        Collection $accounts,
        array $accountIds,
        int $companyId,
        int $storeId,
        string $fromDate
    ): array {
        $balances = [];

        foreach ($accounts->whereIn('id', $accountIds) as $account) {
            $opening = 0.0;

            if (
                $account->opening_balance_date === null
                || $account->opening_balance_date->toDateString() <= $fromDate
            ) {
                $opening = (float) $account->opening_balance;
            }

            $balances[(int) $account->id] = $opening;
        }

        $movements = AccountTransaction::query()
            ->forContext($companyId, $storeId)
            ->whereIn('account_id', $accountIds)
            ->whereDate('transaction_date', '<', $fromDate)
            ->selectRaw(
                'account_id, COALESCE(SUM(debit - credit), 0) AS movement'
            )
            ->groupBy('account_id')
            ->pluck('movement', 'account_id');

        foreach ($movements as $accountId => $movement) {
            $balances[(int) $accountId] = round(
                ($balances[(int) $accountId] ?? 0) + (float) $movement,
                2
            );
        }

        return $balances;
    }

    private function movementBeforePage(
        int $companyId,
        int $storeId,
        array $accountIds,
        array $filters,
        ?AccountTransaction $firstTransaction
    ): array {
        if (!$firstTransaction) {
            return [];
        }

        return AccountTransaction::query()
            ->forContext($companyId, $storeId)
            ->whereIn('account_id', $accountIds)
            ->betweenDates($filters['from_date'], $filters['to_date'])
            ->when(
                $filters['transaction_type'] !== '',
                fn(Builder $query) => $query->where(
                    'transaction_type',
                    $filters['transaction_type']
                )
            )
            ->when(
                $filters['search'] !== '',
                function (Builder $query) use ($filters): void {
                    $search = $filters['search'];
                    $query->where(function (Builder $searchQuery) use ($search): void {
                        $searchQuery
                            ->where('reference_no', 'like', "%{$search}%")
                            ->orWhere('external_reference', 'like', "%{$search}%")
                            ->orWhere('remarks', 'like', "%{$search}%")
                            ->orWhereHas(
                                'account',
                                fn(Builder $accountQuery) => $accountQuery
                                    ->where('account_name', 'like', "%{$search}%")
                                    ->orWhere('account_code', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->where(function (Builder $query) use ($firstTransaction): void {
                $query
                    ->whereDate(
                        'transaction_date',
                        '<',
                        $firstTransaction->transaction_date
                    )
                    ->orWhere(function (Builder $sameDate) use ($firstTransaction): void {
                        $sameDate
                            ->whereDate(
                                'transaction_date',
                                $firstTransaction->transaction_date
                            )
                            ->where('id', '<', $firstTransaction->id);
                    });
            })
            ->selectRaw(
                'account_id, COALESCE(SUM(debit - credit), 0) AS movement'
            )
            ->groupBy('account_id')
            ->pluck('movement', 'account_id')
            ->map(fn($value) => (float) $value)
            ->all();
    }

    private function summary(
        int $companyId,
        int $storeId,
        array $accountIds,
        Collection $accounts,
        array $openingBalances,
        array $filters
    ): array {
        $totals = $this->transactionQuery(
            companyId: $companyId,
            storeId: $storeId,
            accountIds: $accountIds,
            filters: $filters
        )
            ->reorder()
            ->selectRaw(
                'COALESCE(SUM(debit), 0) AS money_in, '
                    . 'COALESCE(SUM(credit), 0) AS money_out'
            )
            ->first();

        $openingTotal = round(array_sum($openingBalances), 2);
        $moneyIn = round((float) ($totals?->money_in ?? 0), 2);
        $moneyOut = round((float) ($totals?->money_out ?? 0), 2);

        $accountBalances = [];
        foreach ($accounts->whereIn('id', $accountIds) as $account) {
            $accountBalances[] = [
                ...$this->accountPayload($account),
                'opening_balance' => round(
                    $openingBalances[(int) $account->id] ?? 0,
                    2
                ),
            ];
        }

        return [
            'opening_balance' => $openingTotal,
            'money_in' => $moneyIn,
            'money_out' => $moneyOut,
            'closing_balance' => round(
                $openingTotal + $moneyIn - $moneyOut,
                2
            ),
            'accounts_count' => count($accountIds),
            'account_balances' => $accountBalances,
        ];
    }

    private function transactionPayload(
        AccountTransaction $transaction,
        float $runningBalance
    ): array {
        return [
            'id' => $transaction->id,
            'date' => $transaction->transaction_date?->toDateString(),
            'account_id' => (int) $transaction->account_id,
            'account_name' => $transaction->account?->account_name,
            'account_code' => $transaction->account?->account_code,
            'account_type' => $transaction->account?->account_type,
            'transaction_type' => $transaction->transaction_type,
            'transaction_type_label' => str($transaction->transaction_type)
                ->replace('_', ' ')
                ->title()
                ->toString(),
            'reference_type' => $transaction->reference_type,
            'reference_id' => $transaction->reference_id,
            'reference_no' => $transaction->reference_no,
            'payment_mode' => $transaction->payment_mode,
            'external_reference' => $transaction->external_reference,
            'remarks' => $transaction->remarks,
            'debit' => round((float) $transaction->debit, 2),
            'credit' => round((float) $transaction->credit, 2),
            'running_balance' => round($runningBalance, 2),
            'is_reversal' => (bool) $transaction->is_reversal,
        ];
    }

    private function accounts(int $companyId, int $storeId): Collection
    {
        return Account::query()
            ->forContext($companyId, $storeId)
            ->whereIn('account_type', [
                Account::TYPE_CASH,
                Account::TYPE_BANK,
                Account::TYPE_UPI,
                Account::TYPE_CARD,
            ])
            ->orderByRaw(
                "FIELD(account_type, 'cash', 'bank', 'upi', 'card')"
            )
            ->orderBy('account_name')
            ->get();
    }

    private function selectedAccountIds(
        Collection $accounts,
        ?int $accountId
    ): array {
        if ($accountId !== null) {
            abort_unless(
                $accounts->contains('id', $accountId),
                404,
                'Account not found in the active branch.'
            );

            return [$accountId];
        }

        return $accounts->pluck('id')->map(fn($id) => (int) $id)->all();
    }

    private function accountPayload(Account $account): array
    {
        return [
            'id' => $account->id,
            'code' => $account->account_code,
            'name' => $account->account_name,
            'type' => $account->account_type,
            'payment_mode' => $account->payment_mode,
            'bank_name' => $account->bank_name,
            'status' => (bool) $account->status,
        ];
    }

    private function transactionTypes(): array
    {
        return collect(AccountTransaction::transactionTypes())
            ->map(fn(string $type): array => [
                'value' => $type,
                'label' => str($type)
                    ->replace('_', ' ')
                    ->title()
                    ->toString(),
            ])
            ->values()
            ->all();
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'account_id' => ['nullable', 'integer'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'transaction_type' => [
                'nullable',
                Rule::in(AccountTransaction::transactionTypes()),
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 20, 50, 100])],
            'paper' => ['nullable', Rule::in(['a4', 'a5'])],
        ]);

        $today = Carbon::now('Asia/Kolkata');

        return [
            'account_id' => isset($validated['account_id'])
                ? (int) $validated['account_id']
                : null,
            'from_date' => $validated['from_date']
                ?? $today->copy()->startOfMonth()->toDateString(),
            'to_date' => $validated['to_date']
                ?? $today->toDateString(),
            'transaction_type' => (string) (
                $validated['transaction_type'] ?? ''
            ),
            'search' => trim((string) ($validated['search'] ?? '')),
            'per_page' => (int) ($validated['per_page'] ?? 20),
        ];
    }

    private function paper(Request $request): string
    {
        return strtolower((string) $request->query('paper', 'a4')) === 'a5'
            ? 'a5'
            : 'a4';
    }

    private function context(Request $request): array
    {
        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');

        abort_if(
            $companyId <= 0 || $storeId <= 0,
            403,
            'Select an active company and branch.'
        );

        $validStore = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->exists();

        abort_unless($validStore, 403, 'The selected branch is unavailable.');

        return [$companyId, $storeId];
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
