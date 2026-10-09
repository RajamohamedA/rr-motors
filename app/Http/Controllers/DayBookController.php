<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\Expense;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DayBookController extends Controller implements HasMiddleware
{
    public const MODE_DAILY = 'daily';

    public const MODE_MONTHLY = 'monthly';

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
        [$fromDate, $toDate] = $this->periodDates($filters);

        $dayRows = $this->dayRows(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );

        $summary = $this->summary($dayRows);

        return Inertia::render('DayBook/Index', [
            'rows' => $dayRows->values(),
            'filters' => [
                ...$filters,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'summary' => $summary,
            'expense_details' => $this->expenseDetails(
                companyId: $companyId,
                storeId: $storeId,
                fromDate: $fromDate,
                toDate: $toDate,
                detailDate: $filters['detail_date'],
                category: $filters['expense_category'],
                search: $filters['expense_search']
            ),
            'expense_categories' => $this->expenseCategories(
                $companyId,
                $storeId
            ),
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
            'day_book.print',
            $this->printPayload($request, false)
        );
    }

    public function pdf(Request $request): HttpResponse
    {
        $payload = $this->printPayload($request, true);

        return Pdf::loadView('day_book.pdf', $payload)
            ->setPaper($payload['paper'], 'portrait')
            ->download(
                'day-book-'
                    . $payload['filters']['from_date']
                    . '-to-'
                    . $payload['filters']['to_date']
                    . '.pdf'
            );
    }

    public function excel(Request $request): StreamedResponse
    {
        $payload = $this->printPayload($request, false);
        $fileName = 'day-book-'
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
                    'Sales Bills',
                    'Sales Amount',
                    'Purchase Bills',
                    'Purchase Amount',
                    'Expense Count',
                    'Expense Amount',
                    'Mechanic Commission',
                    'Gross Profit',
                    'Net Profit Before Expenses',
                    'Final Profit',
                    'Money In',
                    'Money Out',
                ]);

                foreach ($payload['rows'] as $row) {
                    fputcsv($stream, [
                        $row['date'],
                        $row['sales_count'],
                        number_format($row['sales_amount'], 2, '.', ''),
                        $row['purchase_count'],
                        number_format($row['purchase_amount'], 2, '.', ''),
                        $row['expense_count'],
                        number_format($row['expense_amount'], 2, '.', ''),
                        number_format($row['mechanic_commission'], 2, '.', ''),
                        number_format($row['gross_profit'], 2, '.', ''),
                        number_format(
                            $row['net_profit_before_expenses'],
                            2,
                            '.',
                            ''
                        ),
                        number_format($row['final_profit'], 2, '.', ''),
                        number_format($row['money_in'], 2, '.', ''),
                        number_format($row['money_out'], 2, '.', ''),
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
        [$fromDate, $toDate] = $this->periodDates($filters);
        $rows = $this->dayRows(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );

        return [
            'rows' => $rows,
            'summary' => $this->summary($rows),
            'filters' => [
                ...$filters,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'expenses' => $this->allExpenseDetails(
                $companyId,
                $storeId,
                $fromDate,
                $toDate,
                $filters['expense_category'],
                $filters['expense_search']
            ),
            'store' => Store::query()
                ->with('company')
                ->findOrFail($storeId),
            'paper' => $this->paper($request),
            'pdfMode' => $pdfMode,
            'generatedAt' => Carbon::now('Asia/Kolkata'),
        ];
    }

    private function dayRows(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate
    ): Collection {
        $sales = $this->salesByDate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );
        $purchases = $this->purchasesByDate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );
        $expenses = $this->expensesByDate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );
        $profits = $this->profitsByDate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );
        $commissions = $this->commissionsByDate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );
        $cashFlow = $this->cashFlowByDate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate
        );

        $rows = collect();
        $cursor = Carbon::parse($fromDate);
        $lastDate = Carbon::parse($toDate);

        while ($cursor->lte($lastDate)) {
            $date = $cursor->toDateString();
            $sale = $sales->get($date, []);
            $purchase = $purchases->get($date, []);
            $expense = $expenses->get($date, []);
            $profit = $profits->get($date, []);
            $commission = (float) ($commissions->get($date) ?? 0);
            $flow = $cashFlow->get($date, []);

            $grossProfit = round(
                (float) ($profit['gross_profit'] ?? 0),
                2
            );
            $netProfitBeforeExpenses = round(
                $grossProfit
                    - (float) ($sale['final_discount'] ?? 0)
                    + (float) ($sale['round_off'] ?? 0)
                    - $commission,
                2
            );
            $expenseAmount = round(
                (float) ($expense['amount'] ?? 0),
                2
            );

            $rows->push([
                'date' => $date,
                'day_label' => $cursor->format('l'),
                'sales_count' => (int) ($sale['count'] ?? 0),
                'sales_amount' => round(
                    (float) ($sale['amount'] ?? 0),
                    2
                ),
                'purchase_count' => (int) ($purchase['count'] ?? 0),
                'purchase_amount' => round(
                    (float) ($purchase['amount'] ?? 0),
                    2
                ),
                'expense_count' => (int) ($expense['count'] ?? 0),
                'expense_amount' => $expenseAmount,
                'mechanic_commission' => round($commission, 2),
                'cost_total' => round(
                    (float) ($profit['cost_total'] ?? 0),
                    2
                ),
                'gross_profit' => $grossProfit,
                'net_profit_before_expenses' => $netProfitBeforeExpenses,
                'final_profit' => round(
                    $netProfitBeforeExpenses - $expenseAmount,
                    2
                ),
                'money_in' => round((float) ($flow['money_in'] ?? 0), 2),
                'money_out' => round((float) ($flow['money_out'] ?? 0), 2),
                'missing_cost_lines' => (int) (
                    $profit['missing_cost_lines'] ?? 0
                ),
                'expense_url' => route('expenses.index', [
                    'period' => 'custom',
                    'from_date' => $date,
                    'to_date' => $date,
                ]),
            ]);

            $cursor->addDay();
        }

        return $rows->sortByDesc('date')->values();
    }

    private function salesByDate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate
    ): Collection {
        if (!Schema::hasTable('sales')) {
            return collect();
        }

        return DB::table('sales')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', 'Completed')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->selectRaw(
                'invoice_date AS report_date, COUNT(*) AS bill_count, '
                    . 'COALESCE(SUM(grand_total), 0) AS amount, '
                    . 'COALESCE(SUM(final_discount), 0) AS final_discount, '
                    . 'COALESCE(SUM(round_off), 0) AS round_off'
            )
            ->groupBy('invoice_date')
            ->get()
            ->mapWithKeys(fn(object $row): array => [
                (string) $row->report_date => [
                    'count' => (int) $row->bill_count,
                    'amount' => (float) $row->amount,
                    'final_discount' => (float) $row->final_discount,
                    'round_off' => (float) $row->round_off,
                ],
            ]);
    }

    private function purchasesByDate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate
    ): Collection {
        if (!Schema::hasTable('purchases')) {
            return collect();
        }

        return DB::table('purchases')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->whereBetween('purchase_date', [$fromDate, $toDate])
            ->selectRaw(
                'purchase_date AS report_date, COUNT(*) AS bill_count, '
                    . 'COALESCE(SUM(grand_total), 0) AS amount'
            )
            ->groupBy('purchase_date')
            ->get()
            ->mapWithKeys(fn(object $row): array => [
                (string) $row->report_date => [
                    'count' => (int) $row->bill_count,
                    'amount' => (float) $row->amount,
                ],
            ]);
    }

    private function expensesByDate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate
    ): Collection {
        if (!Schema::hasTable('expenses')) {
            return collect();
        }

        return DB::table('expenses')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', Expense::STATUS_COMPLETED)
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->selectRaw(
                'expense_date AS report_date, COUNT(*) AS expense_count, '
                    . 'COALESCE(SUM(amount), 0) AS amount'
            )
            ->groupBy('expense_date')
            ->get()
            ->mapWithKeys(fn(object $row): array => [
                (string) $row->report_date => [
                    'count' => (int) $row->expense_count,
                    'amount' => (float) $row->amount,
                ],
            ]);
    }

    private function profitsByDate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate
    ): Collection {
        if (!Schema::hasTable('sale_items') || !Schema::hasTable('sales')) {
            return collect();
        }

        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.company_id', $companyId)
            ->where('sales.store_id', $storeId)
            ->where('sales.status', 'Completed')
            ->whereBetween('sales.invoice_date', [$fromDate, $toDate])
            ->selectRaw(
                'sales.invoice_date AS report_date, '
                    . 'COALESCE(SUM(sale_items.cost_total), 0) AS cost_total, '
                    . 'COALESCE(SUM(sale_items.gross_profit), 0) AS gross_profit, '
                    . 'SUM(CASE WHEN sale_items.cost_total IS NULL '
                    . 'OR sale_items.gross_profit IS NULL THEN 1 ELSE 0 END) '
                    . 'AS missing_cost_lines'
            )
            ->groupBy('sales.invoice_date')
            ->get()
            ->mapWithKeys(fn(object $row): array => [
                (string) $row->report_date => [
                    'cost_total' => (float) $row->cost_total,
                    'gross_profit' => (float) $row->gross_profit,
                    'missing_cost_lines' => (int) $row->missing_cost_lines,
                ],
            ]);
    }

    private function commissionsByDate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate
    ): Collection {
        if (
            !Schema::hasTable('mechanic_commissions')
            || !Schema::hasTable('sales')
        ) {
            return collect();
        }

        return DB::table('mechanic_commissions')
            ->join('sales', 'sales.id', '=', 'mechanic_commissions.sale_id')
            ->where('mechanic_commissions.company_id', $companyId)
            ->where('mechanic_commissions.store_id', $storeId)
            ->where('mechanic_commissions.status', '!=', 'Cancelled')
            ->whereBetween('sales.invoice_date', [$fromDate, $toDate])
            ->selectRaw(
                'sales.invoice_date AS report_date, '
                    . 'COALESCE(SUM(mechanic_commissions.commission_amount), 0) '
                    . 'AS commission_total'
            )
            ->groupBy('sales.invoice_date')
            ->pluck('commission_total', 'report_date');
    }

    private function cashFlowByDate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate
    ): Collection {
        if (!Schema::hasTable('account_transactions')) {
            return collect();
        }

        return DB::table('account_transactions')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->selectRaw(
                'transaction_date AS report_date, '
                    . 'COALESCE(SUM(debit), 0) AS money_in, '
                    . 'COALESCE(SUM(credit), 0) AS money_out'
            )
            ->groupBy('transaction_date')
            ->get()
            ->mapWithKeys(fn(object $row): array => [
                (string) $row->report_date => [
                    'money_in' => (float) $row->money_in,
                    'money_out' => (float) $row->money_out,
                ],
            ]);
    }

    private function summary(Collection $rows): array
    {
        return [
            'days_count' => $rows->count(),
            'sales_count' => $rows->sum('sales_count'),
            'sales_amount' => round($rows->sum('sales_amount'), 2),
            'purchase_count' => $rows->sum('purchase_count'),
            'purchase_amount' => round($rows->sum('purchase_amount'), 2),
            'expense_count' => $rows->sum('expense_count'),
            'expense_amount' => round($rows->sum('expense_amount'), 2),
            'mechanic_commission' => round(
                $rows->sum('mechanic_commission'),
                2
            ),
            'cost_total' => round($rows->sum('cost_total'), 2),
            'gross_profit' => round($rows->sum('gross_profit'), 2),
            'net_profit_before_expenses' => round(
                $rows->sum('net_profit_before_expenses'),
                2
            ),
            'final_profit' => round($rows->sum('final_profit'), 2),
            'money_in' => round($rows->sum('money_in'), 2),
            'money_out' => round($rows->sum('money_out'), 2),
            'missing_cost_lines' => $rows->sum('missing_cost_lines'),
        ];
    }

    private function expenseDetails(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        ?string $detailDate,
        string $category,
        string $search
    ): array {
        if (!Schema::hasTable('expenses')) {
            return [
                'data' => [],
                'total' => 0,
                'total_amount' => 0.0,
                'selected_date' => $detailDate,
            ];
        }

        $query = Expense::query()
            ->with('account:id,account_code,account_name,account_type')
            ->forContext($companyId, $storeId)
            ->completed()
            ->betweenDates($fromDate, $toDate)
            ->when(
                $detailDate,
                fn($builder, string $date) =>
                $builder->whereDate('expense_date', $date)
            )
            ->when(
                $category !== '',
                fn($builder, string $value) =>
                $builder->where('expense_category', $value)
            )
            ->search($search);

        $totalAmount = round((float) (clone $query)->sum('amount'), 2);
        $expenses = $query
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return [
            'data' => $expenses->map(
                fn(Expense $expense): array =>
                $this->expensePayload($expense)
            )->values(),
            'total' => $expenses->count(),
            'total_amount' => $totalAmount,
            'selected_date' => $detailDate,
        ];
    }

    private function allExpenseDetails(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        string $category,
        string $search
    ): Collection {
        if (!Schema::hasTable('expenses')) {
            return collect();
        }

        return Expense::query()
            ->with('account:id,account_code,account_name,account_type')
            ->forContext($companyId, $storeId)
            ->completed()
            ->betweenDates($fromDate, $toDate)
            ->when(
                $category !== '',
                fn($builder, string $value) =>
                $builder->where('expense_category', $value)
            )
            ->search($search)
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get()
            ->map(fn(Expense $expense): array => $this->expensePayload($expense));
    }

    private function expensePayload(Expense $expense): array
    {
        return [
            'id' => $expense->id,
            'expense_no' => $expense->expense_no,
            'date' => $expense->expense_date?->toDateString(),
            'category' => $expense->expense_category,
            'payee_name' => $expense->payee_name,
            'amount' => round((float) $expense->amount, 2),
            'payment_mode' => $expense->payment_mode,
            'reference_no' => $expense->reference_no,
            'remarks' => $expense->remarks,
            'account_name' => $expense->account?->account_name,
            'account_code' => $expense->account?->account_code,
            'show_url' => route('expenses.show', $expense),
        ];
    }

    private function expenseCategories(
        int $companyId,
        int $storeId
    ): array {
        if (!Schema::hasTable('expenses')) {
            return [];
        }

        return Expense::query()
            ->forContext($companyId, $storeId)
            ->whereNotNull('expense_category')
            ->where('expense_category', '!=', '')
            ->distinct()
            ->orderBy('expense_category')
            ->pluck('expense_category')
            ->values()
            ->all();
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'mode' => [
                'nullable',
                Rule::in([self::MODE_DAILY, self::MODE_MONTHLY]),
            ],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'detail_date' => ['nullable', 'date'],
            'expense_category' => ['nullable', 'string', 'max:100'],
            'expense_search' => ['nullable', 'string', 'max:100'],
            'paper' => ['nullable', Rule::in(['a4', 'a5'])],
        ]);

        $today = Carbon::now('Asia/Kolkata');
        $mode = $validated['mode'] ?? self::MODE_DAILY;

        return [
            'mode' => $mode,
            'date' => $validated['date'] ?? $today->toDateString(),
            'month' => $validated['month'] ?? $today->format('Y-m'),
            'detail_date' => isset($validated['detail_date'])
                ? (string) $validated['detail_date']
                : null,
            'expense_category' => trim(
                (string) ($validated['expense_category'] ?? '')
            ),
            'expense_search' => trim(
                (string) ($validated['expense_search'] ?? '')
            ),
        ];
    }

    private function periodDates(array $filters): array
    {
        if ($filters['mode'] === self::MODE_MONTHLY) {
            $month = Carbon::createFromFormat(
                'Y-m-d',
                $filters['month'] . '-01',
                'Asia/Kolkata'
            );

            return [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ];
        }

        return [$filters['date'], $filters['date']];
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
