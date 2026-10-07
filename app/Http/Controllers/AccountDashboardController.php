<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\SaleItems;
use App\Models\Sales;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AccountDashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.dashboard.view'),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId, $store] = $this->context($request);

        $validated = $request->validate([
            'date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $date = Carbon::parse(
            $validated['date'] ?? now()->toDateString()
        )->toDateString();

        $salesQuery = Sales::query()
            ->forContext($companyId, $storeId)
            ->completed()
            ->forDate($date);

        $sales = $this->salesSummary($salesQuery);
        $salesReturns = $this->salesReturnSummary(
            $companyId,
            $storeId,
            $date
        );
        $profit = $this->profitSummary(
            $companyId,
            $storeId,
            $date,
            $sales
        );

        $transactionQuery = AccountTransaction::query()
            ->forContext($companyId, $storeId)
            ->whereDate('transaction_date', $date);

        $collections = $this->collectionSummary(
            clone $transactionQuery
        );

        $payments = $this->paymentSummary(
            clone $transactionQuery
        );

        $accountBalances = $this->accountBalances(
            $companyId,
            $storeId,
            $date
        );
        $accountSummary = $this->accountBalanceSummary($accountBalances);

        $customerBalances = $this->partyBalances(
            table: 'customer_ledger_entries',
            partyColumn: 'customer_id',
            companyId: $companyId,
            storeId: $storeId,
            date: $date,
            effectSql: 'debit - credit'
        );

        $supplierBalances = $this->partyBalances(
            table: 'supplier_ledger_entries',
            partyColumn: 'supplier_id',
            companyId: $companyId,
            storeId: $storeId,
            date: $date,
            effectSql: 'credit - debit'
        );

        $mechanicBalances = $this->partyBalances(
            table: 'mechanic_ledger_entries',
            partyColumn: 'mechanic_id',
            companyId: $companyId,
            storeId: $storeId,
            date: $date,
            effectSql: 'credit - debit'
        );

        $unpaidInvoices = (clone $salesQuery)
            ->with([
                'customer:id,customer_code,customer_name,mobile,phone',
            ])
            ->where('balance_amount', '>', 0)
            ->orderByDesc('balance_amount')
            ->orderBy('invoice_no')
            ->limit(50)
            ->get([
                'id',
                'customer_id',
                'invoice_no',
                'invoice_date',
                'grand_total',
                'paid_amount',
                'advance_used',
                'balance_amount',
                'payment_status',
            ])
            ->map(fn(Sales $sale): array => [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'invoice_date' => $sale->invoice_date?->toDateString(),
                'customer' => $sale->customer
                    ? [
                        'id' => $sale->customer->id,
                        'customer_code' => $sale->customer->customer_code,
                        'customer_name' => $sale->customer->customer_name,
                        'mobile' => $sale->customer->mobile
                            ?: $sale->customer->phone,
                    ]
                    : null,
                'grand_total' => (float) $sale->grand_total,
                'paid_amount' => (float) $sale->paid_amount,
                'advance_used' => (float) $sale->advance_used,
                'balance_amount' => (float) $sale->balance_amount,
                'payment_status' => $sale->payment_status,
            ]);

        $recentTransactions = (clone $transactionQuery)
            ->with([
                'account:id,account_code,account_name,account_type',
                'creator:id,name',
            ])
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(fn(AccountTransaction $entry): array => [
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
                'payment_mode' => $entry->payment_mode,
                'remarks' => $entry->remarks,
                'is_reversal' => (bool) $entry->is_reversal,
                'account' => $entry->account
                    ? [
                        'id' => $entry->account->id,
                        'account_code' => $entry->account->account_code,
                        'account_name' => $entry->account->account_name,
                        'account_type' => $entry->account->account_type,
                    ]
                    : null,
                'created_by' => $entry->creator?->name ?? 'System',
            ]);

        return Inertia::render('Accounts/Dashboard', [
            'selectedDate' => $date,
            'isToday' => $date === now()->toDateString(),
            'summary' => [
                ...$sales,
                ...$salesReturns,
                ...$profit,
                ...$accountSummary,
                'customer_outstanding' => $customerBalances['positive'],
                'customer_advance' => $customerBalances['negative'],
                'supplier_outstanding' => $supplierBalances['positive'],
                'supplier_advance' => $supplierBalances['negative'],
                'mechanic_payable' => $mechanicBalances['positive'],
                'mechanic_advance' => $mechanicBalances['negative'],
            ],
            'collections' => $collections,
            'payments' => $payments,
            'accountBalances' => $accountBalances,
            'unpaidInvoices' => $unpaidInvoices,
            'recentTransactions' => $recentTransactions,
            'context' => [
                'company_id' => $companyId,
                'store_id' => $storeId,
                'company_name' => $request->session()->get(
                    'active_company_name'
                ),
                'store_name' => $store->store_name
                    ?? $request->session()->get('active_store_name'),
            ],
        ]);
    }

    private function salesSummary(Builder $query): array
    {
        $row = (clone $query)
            ->selectRaw('COUNT(*) AS bill_count')
            ->selectRaw('COALESCE(SUM(grand_total), 0) AS sales_total')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) AS settled_total')
            ->selectRaw('COALESCE(SUM(advance_used), 0) AS advance_used')
            ->selectRaw('COALESCE(SUM(balance_amount), 0) AS outstanding_total')
            ->selectRaw('COALESCE(SUM(final_discount), 0) AS final_discount')
            ->selectRaw('COALESCE(SUM(round_off), 0) AS round_off')
            ->selectRaw('COALESCE(SUM(commission_amount), 0) AS mechanic_commission')
            ->selectRaw(
                "SUM(CASE WHEN payment_status = 'Paid' THEN 1 ELSE 0 END) "
                    . 'AS paid_bill_count'
            )
            ->selectRaw(
                "SUM(CASE WHEN payment_status = 'Partially Paid' "
                    . 'THEN 1 ELSE 0 END) AS partial_bill_count'
            )
            ->selectRaw(
                "SUM(CASE WHEN payment_status = 'Unpaid' THEN 1 ELSE 0 END) "
                    . 'AS unpaid_bill_count'
            )
            ->first();

        return [
            'bill_count' => (int) $row->bill_count,
            'sales_total' => round((float) $row->sales_total, 2),
            'settled_total' => round((float) $row->settled_total, 2),
            'paid_total' => round((float) $row->settled_total, 2),
            'advance_used' => round((float) $row->advance_used, 2),
            'outstanding_total' => round(
                (float) $row->outstanding_total,
                2
            ),
            'unpaid_total' => round((float) $row->outstanding_total, 2),
            'final_discount' => round((float) $row->final_discount, 2),
            'round_off' => round((float) $row->round_off, 2),
            'mechanic_commission' => round(
                (float) $row->mechanic_commission,
                2
            ),
            'paid_bill_count' => (int) $row->paid_bill_count,
            'partial_bill_count' => (int) $row->partial_bill_count,
            'unpaid_bill_count' => (int) $row->unpaid_bill_count,
        ];
    }

    private function salesReturnSummary(
        int $companyId,
        int $storeId,
        string $date
    ): array {
        if (!DB::getSchemaBuilder()->hasTable('sales_returns')) {
            return [
                'sales_return_count' => 0,
                'sales_return_total' => 0.0,
                'net_sales_total' => 0.0,
            ];
        }

        $row = DB::table('sales_returns')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereDate('return_date', $date)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) AS return_count')
            ->selectRaw('COALESCE(SUM(total_amount), 0) AS return_total')
            ->first();

        $returnTotal = round((float) $row->return_total, 2);
        $salesTotal = round(
            (float) Sales::query()
                ->forContext($companyId, $storeId)
                ->completed()
                ->forDate($date)
                ->sum('grand_total'),
            2
        );

        return [
            'sales_return_count' => (int) $row->return_count,
            'sales_return_total' => $returnTotal,
            'net_sales_total' => round($salesTotal - $returnTotal, 2),
        ];
    }

    private function profitSummary(
        int $companyId,
        int $storeId,
        string $date,
        array $sales
    ): array {
        $row = SaleItems::query()
            ->whereHas('sale', function (Builder $query) use (
                $companyId,
                $storeId,
                $date
            ): void {
                $query
                    ->forContext($companyId, $storeId)
                    ->completed()
                    ->forDate($date);
            })
            ->selectRaw('COALESCE(SUM(cost_total), 0) AS cost_total')
            ->selectRaw('COALESCE(SUM(gross_profit), 0) AS gross_profit')
            ->selectRaw(
                'SUM(CASE WHEN cost_total IS NULL '
                    . 'OR gross_profit IS NULL THEN 1 ELSE 0 END) '
                    . 'AS missing_cost_lines'
            )
            ->first();

        $grossProfit = round((float) $row->gross_profit, 2);
        $netProfit = round(
            $grossProfit
                - $sales['final_discount']
                + $sales['round_off']
                - $sales['mechanic_commission'],
            2
        );

        return [
            'cost_total' => round((float) $row->cost_total, 2),
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
            'missing_cost_lines' => (int) $row->missing_cost_lines,
        ];
    }

    private function collectionSummary(Builder $query): array
    {
        $types = [
            AccountTransaction::TYPE_SALE,
            AccountTransaction::TYPE_CUSTOMER_RECEIPT,
            AccountTransaction::TYPE_RECEIPT_VOUCHER,
        ];

        $rows = $query
            ->whereIn('transaction_type', $types)
            ->where('debit', '>', 0)
            ->selectRaw("COALESCE(NULLIF(LOWER(payment_mode), ''), 'other') AS mode")
            ->selectRaw('COALESCE(SUM(debit), 0) AS total')
            ->groupBy('mode')
            ->pluck('total', 'mode')
            ->map(fn($amount): float => (float) $amount)
            ->all();

        return $this->paymentModeTotals($rows);
    }

    private function paymentSummary(Builder $query): array
    {
        $types = [
            'supplier' => AccountTransaction::TYPE_SUPPLIER_PAYMENT,
            'mechanic' => AccountTransaction::TYPE_MECHANIC_PAYMENT,
            'customer_refund' => AccountTransaction::TYPE_CUSTOMER_REFUND,
            'expense' => AccountTransaction::TYPE_EXPENSE,
            'payment_voucher' => AccountTransaction::TYPE_PAYMENT_VOUCHER,
        ];

        $totals = [];

        foreach ($types as $key => $type) {
            $totals[$key] = round(
                (float) (clone $query)
                    ->where('transaction_type', $type)
                    ->sum('credit'),
                2
            );
        }

        $totals['total'] = round(array_sum($totals), 2);

        return $totals;
    }

    private function paymentModeTotals(array $rows): array
    {
        $cash = (float) ($rows['cash'] ?? 0);
        $gpay = (float) ($rows['gpay'] ?? 0);
        $phonePe = (float) ($rows['phonepe'] ?? 0);
        $otherUpi = (float) ($rows['upi'] ?? 0);
        $bankTransfer = (float) ($rows['bank_transfer'] ?? 0);
        $card = (float) ($rows['card'] ?? 0);
        $cheque = (float) ($rows['cheque'] ?? 0);
        $other = (float) ($rows['other'] ?? 0);

        $upi = $gpay + $phonePe + $otherUpi;
        $bank = $bankTransfer + $cheque;

        return [
            'cash' => round($cash, 2),
            'gpay' => round($gpay, 2),
            'phonepe' => round($phonePe, 2),
            'other_upi' => round($otherUpi, 2),
            'upi' => round($upi, 2),
            'bank_transfer' => round($bankTransfer, 2),
            'cheque' => round($cheque, 2),
            'bank' => round($bank, 2),
            'card' => round($card, 2),
            'other' => round($other, 2),
            'total' => round($cash + $upi + $bank + $card + $other, 2),
        ];
    }

    private function accountBalances(
        int $companyId,
        int $storeId,
        string $date
    ): Collection {
        return Account::query()
            ->forContext($companyId, $storeId)
            ->active()
            ->withSum(
                ['transactions as debit_total' => function (Builder $query) use (
                    $companyId,
                    $storeId,
                    $date
                ): void {
                    $query
                        ->forContext($companyId, $storeId)
                        ->whereDate('transaction_date', '<=', $date);
                }],
                'debit'
            )
            ->withSum(
                ['transactions as credit_total' => function (Builder $query) use (
                    $companyId,
                    $storeId,
                    $date
                ): void {
                    $query
                        ->forContext($companyId, $storeId)
                        ->whereDate('transaction_date', '<=', $date);
                }],
                'credit'
            )
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get()
            ->map(function (Account $account) use ($date): array {
                $openingBalance = 0.0;

                if (
                    !$account->opening_balance_date
                    || $account->opening_balance_date->toDateString() <= $date
                ) {
                    $openingBalance = (float) $account->opening_balance;
                }

                return [
                    'id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'account_type' => $account->account_type,
                    'payment_mode' => $account->payment_mode,
                    'opening_balance' => $openingBalance,
                    'debit_total' => (float) ($account->debit_total ?? 0),
                    'credit_total' => (float) ($account->credit_total ?? 0),
                    'balance' => round(
                        $openingBalance
                            + (float) ($account->debit_total ?? 0)
                            - (float) ($account->credit_total ?? 0),
                        2
                    ),
                ];
            });
    }

    private function partyBalances(
        string $table,
        string $partyColumn,
        int $companyId,
        int $storeId,
        string $date,
        string $effectSql
    ): array {
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return [
                'positive' => 0.0,
                'negative' => 0.0,
            ];
        }

        $balances = DB::table($table)
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereDate('transaction_date', '<=', $date)
            ->groupBy($partyColumn)
            ->select($partyColumn)
            ->selectRaw("COALESCE(SUM({$effectSql}), 0) AS balance")
            ->pluck('balance');

        $positive = 0.0;
        $negative = 0.0;

        foreach ($balances as $balance) {
            $amount = round((float) $balance, 2);

            if ($amount > 0) {
                $positive += $amount;
            } elseif ($amount < 0) {
                $negative += abs($amount);
            }
        }

        return [
            'positive' => round($positive, 2),
            'negative' => round($negative, 2),
        ];
    }

    private function accountBalanceSummary(Collection $accounts): array
    {
        $sumType = fn(string $type): float => round(
            (float) $accounts
                ->where('account_type', $type)
                ->sum('balance'),
            2
        );

        return [
            'total_account_balance' => round(
                (float) $accounts->sum('balance'),
                2
            ),
            'cash_balance' => $sumType(Account::TYPE_CASH),
            'bank_balance' => $sumType(Account::TYPE_BANK),
            'upi_balance' => $sumType(Account::TYPE_UPI),
            'card_balance' => $sumType(Account::TYPE_CARD),
        ];
    }

    private function transactionTypeLabel(string $type): string
    {
        return str($type)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    private function context(Request $request): array
    {
        $user = $request->user();

        abort_unless($user && (bool) $user->status, 403);

        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');

        abort_unless(
            $companyId > 0 && $storeId > 0,
            403,
            'Select an active company and branch before opening Accounts.'
        );

        $store = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->first();

        abort_unless($store, 403, 'The active branch is invalid or inactive.');

        if (!(bool) ($user->is_super_user ?? false)) {
            abort_unless((int) $user->company_id === $companyId, 403);

            if (!$user->hasRole('Company Admin')) {
                abort_unless(
                    $user->stores()
                        ->where('stores.id', $storeId)
                        ->where('stores.status', true)
                        ->exists(),
                    403
                );
            }
        }

        return [$companyId, $storeId, $store];
    }
}
