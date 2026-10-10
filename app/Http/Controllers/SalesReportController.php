<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Mechanic;
use App\Models\Sales;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
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

class SalesReportController extends Controller implements HasMiddleware
{
    public const MODE_DAILY = 'daily';
    public const MODE_MONTHLY = 'monthly';
    public const MODE_YEARLY = 'yearly';

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:sales-report.view',
                only: ['index', 'show']
            ),
            new Middleware(
                'permission:sales-report.print',
                only: ['print']
            ),
            new Middleware(
                'permission:sales-report.pdf',
                only: ['pdf']
            ),
            new Middleware(
                'permission:sales-report.excel',
                only: ['csv', 'excel']
            ),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->filters($request);
        [$fromDate, $toDate] = $this->periodDates($filters);

        $rows = $this->periodRows(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $filters
        );

        $sales = $this->saleQuery(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $filters
        )
            ->with([
                'customer:id,customer_code,customer_name,mobile',
                'mechanic:id,mechanic_code,mechanic_name',
            ])
            ->withSum('items as bill_cost_total', 'cost_total')
            ->withSum('items as bill_gross_profit', 'gross_profit')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(fn(Sales $sale): array => $this->salePayload($sale));

        return Inertia::render('SalesReports/Index', [
            'rows' => $rows->values(),
            'summary' => $this->summary($rows),
            'sales' => $sales,
            'filters' => [
                ...$filters,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'customers' => $this->customers($companyId, $storeId),
            'mechanics' => $this->mechanics($companyId, $storeId),
            'context' => $this->contextPayload(
                $request,
                $companyId,
                $storeId
            ),
        ]);
    }

    public function show(Request $request, Sales $sale): Response
    {
        [$companyId, $storeId] = $this->context($request);

        abort_unless(
            (int) $sale->company_id === $companyId
                && (int) $sale->store_id === $storeId,
            404
        );

        $sale->load([
            'customer',
            'mechanic',
            'items.product',
            'items.godown',
            'salePayments.account',
            'payments.account',
        ]);

        return Inertia::render('SalesReports/Show', [
            'sale' => $this->salePayload($sale, true),
        ]);
    }

    public function print(Request $request): HttpResponse
    {
        return response()->view(
            'sales_reports.print',
            $this->exportPayload($request, false)
        );
    }

    public function pdf(Request $request): HttpResponse
    {
        $payload = $this->exportPayload($request, true);

        return Pdf::loadView('sales_reports.pdf', $payload)
            ->setPaper($payload['paper'], 'portrait')
            ->download($this->fileName($payload['filters'], 'pdf'));
    }

    public function csv(Request $request): StreamedResponse
    {
        $payload = $this->exportPayload($request, false);

        return response()->streamDownload(
            function () use ($payload): void {
                $stream = fopen('php://output', 'wb');
                fwrite($stream, "\xEF\xBB\xBF");

                fputcsv($stream, [
                    'Period',
                    'Bills',
                    'Gross Sales',
                    'Sales Returns',
                    'Net Sales',
                    'Paid',
                    'Outstanding',
                    'Cost',
                    'Gross Profit',
                    'Commission',
                    'Net Profit',
                    'Cash',
                    'Bank',
                    'UPI / GPay',
                    'Card',
                ]);

                foreach ($payload['rows'] as $row) {
                    fputcsv($stream, [
                        $row['label'],
                        $row['bill_count'],
                        $this->csvNumber($row['gross_sales']),
                        $this->csvNumber($row['return_amount']),
                        $this->csvNumber($row['net_sales']),
                        $this->csvNumber($row['paid_amount']),
                        $this->csvNumber($row['outstanding_amount']),
                        $this->csvNumber($row['cost_total']),
                        $this->csvNumber($row['gross_profit']),
                        $this->csvNumber($row['commission_amount']),
                        $this->csvNumber($row['net_profit']),
                        $this->csvNumber($row['cash_amount']),
                        $this->csvNumber($row['bank_amount']),
                        $this->csvNumber($row['upi_amount']),
                        $this->csvNumber($row['card_amount']),
                    ]);
                }

                fclose($stream);
            },
            $this->fileName($payload['filters'], 'csv'),
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function excel(Request $request): StreamedResponse
    {
        return $this->csv($request);
    }

    private function exportPayload(Request $request, bool $pdfMode): array
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->filters($request);
        [$fromDate, $toDate] = $this->periodDates($filters);
        $rows = $this->periodRows(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $filters
        );

        return [
            'rows' => $rows,
            'summary' => $this->summary($rows),
            'sales' => $this->saleQuery(
                $companyId,
                $storeId,
                $fromDate,
                $toDate,
                $filters
            )
                ->with(['customer:id,customer_code,customer_name'])
                ->withSum('items as bill_cost_total', 'cost_total')
                ->withSum('items as bill_gross_profit', 'gross_profit')
                ->orderBy('invoice_date')
                ->orderBy('id')
                ->get()
                ->map(fn(Sales $sale): array => $this->salePayload($sale)),
            'filters' => [
                ...$filters,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'store' => Store::query()
                ->with('company')
                ->findOrFail($storeId),
            'paper' => $this->paper($request),
            'pdfMode' => $pdfMode,
            'generatedAt' => Carbon::now('Asia/Kolkata'),
        ];
    }

    private function periodRows(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        array $filters
    ): Collection {
        $mode = $filters['mode'];
        $sales = $this->salesAggregate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $filters
        );
        $returns = $this->returnsAggregate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $mode,
            $filters
        );
        $collections = $this->collectionsAggregate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $mode
        );

        return $this->periodSkeleton($fromDate, $toDate, $mode)
            ->map(function (array $period) use (
                $sales,
                $returns,
                $collections
            ): array {
                $sale = $sales->get($period['key'], []);
                $flow = $collections->get($period['key'], []);
                $returnAmount = (float) ($returns->get($period['key']) ?? 0);
                $grossSales = (float) ($sale['gross_sales'] ?? 0);

                return [
                    ...$period,
                    'bill_count' => (int) ($sale['bill_count'] ?? 0),
                    'gross_sales' => round($grossSales, 2),
                    'return_amount' => round($returnAmount, 2),
                    'net_sales' => round($grossSales - $returnAmount, 2),
                    'paid_amount' => round(
                        (float) ($sale['paid_amount'] ?? 0),
                        2
                    ),
                    'outstanding_amount' => round(
                        (float) ($sale['outstanding_amount'] ?? 0),
                        2
                    ),
                    'cost_total' => round(
                        (float) ($sale['cost_total'] ?? 0),
                        2
                    ),
                    'gross_profit' => round(
                        (float) ($sale['gross_profit'] ?? 0),
                        2
                    ),
                    'commission_amount' => round(
                        (float) ($sale['commission_amount'] ?? 0),
                        2
                    ),
                    'net_profit' => round(
                        (float) ($sale['net_profit'] ?? 0),
                        2
                    ),
                    'gst_sales' => round(
                        (float) ($sale['gst_sales'] ?? 0),
                        2
                    ),
                    'non_gst_sales' => round(
                        (float) ($sale['non_gst_sales'] ?? 0),
                        2
                    ),
                    'cash_amount' => round(
                        (float) ($flow['cash_amount'] ?? 0),
                        2
                    ),
                    'bank_amount' => round(
                        (float) ($flow['bank_amount'] ?? 0),
                        2
                    ),
                    'upi_amount' => round(
                        (float) ($flow['upi_amount'] ?? 0),
                        2
                    ),
                    'card_amount' => round(
                        (float) ($flow['card_amount'] ?? 0),
                        2
                    ),
                ];
            });
    }

    private function salesAggregate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        array $filters
    ): Collection {
        $periodSql = $this->periodSql('s.invoice_date', $filters['mode']);

        $itemTotals = DB::table('sale_items')
            ->selectRaw(
                'sale_id, '
                    . 'COALESCE(SUM(cost_total), 0) AS cost_total, '
                    . 'COALESCE(SUM(gross_profit), 0) AS gross_profit'
            )
            ->groupBy('sale_id');

        $query = DB::table('sales as s')
            ->leftJoinSub($itemTotals, 'it', 'it.sale_id', '=', 's.id')
            ->where('s.company_id', $companyId)
            ->where('s.store_id', $storeId)
            ->whereBetween('s.invoice_date', [$fromDate, $toDate])
            ->where('s.status', Sales::STATUS_COMPLETED);

        $this->applySaleFilters($query, $filters, 's');

        return $query
            ->selectRaw(
                "{$periodSql} AS period_key, "
                    . 'COUNT(*) AS bill_count, '
                    . 'COALESCE(SUM(s.grand_total), 0) AS gross_sales, '
                    . 'COALESCE(SUM(s.paid_amount), 0) AS paid_amount, '
                    . 'COALESCE(SUM(s.balance_amount), 0) AS outstanding_amount, '
                    . 'COALESCE(SUM(it.cost_total), 0) AS cost_total, '
                    . 'COALESCE(SUM(it.gross_profit), 0) AS gross_profit, '
                    . 'COALESCE(SUM(s.commission_amount), 0) AS commission_amount, '
                    . 'COALESCE(SUM('
                    . 'COALESCE(it.gross_profit, 0) '
                    . '- s.final_discount + s.round_off - s.commission_amount'
                    . '), 0) AS net_profit, '
                    . "COALESCE(SUM(CASE WHEN s.billing_type = 'gst' "
                    . 'THEN s.grand_total ELSE 0 END), 0) AS gst_sales, '
                    . "COALESCE(SUM(CASE WHEN s.billing_type = 'non_gst' "
                    . 'THEN s.grand_total ELSE 0 END), 0) AS non_gst_sales'
            )
            ->groupByRaw($periodSql)
            ->get()
            ->mapWithKeys(fn($row): array => [
                (string) $row->period_key => (array) $row,
            ]);
    }

    private function returnsAggregate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        string $mode,
        array $filters
    ): Collection {
        if (!Schema::hasTable('sales_returns')) {
            return collect();
        }

        $periodSql = $this->periodSql('sr.return_date', $mode);
        $query = DB::table('sales_returns as sr')
            ->join('sales as s', 's.id', '=', 'sr.sale_id')
            ->where('sr.company_id', $companyId)
            ->where('s.store_id', $storeId)
            ->whereBetween('sr.return_date', [$fromDate, $toDate])
            ->whereIn('sr.status', ['completed', 'Completed']);

        if ($filters['customer_id']) {
            $query->where('sr.customer_id', $filters['customer_id']);
        }

        if ($filters['mechanic_id']) {
            $query->where('s.mechanic_id', $filters['mechanic_id']);
        }

        return $query
            ->selectRaw(
                "{$periodSql} AS period_key, "
                    . 'COALESCE(SUM(sr.total_amount), 0) AS return_amount'
            )
            ->groupByRaw($periodSql)
            ->pluck('return_amount', 'period_key')
            ->map(fn($amount): float => (float) $amount);
    }

    private function collectionsAggregate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        string $mode
    ): Collection {
        if (!Schema::hasTable('account_transactions')) {
            return collect();
        }

        $periodSql = $this->periodSql('transaction_date', $mode);

        return DB::table('account_transactions')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereBetween('transaction_date', [$fromDate, $toDate])
            ->whereIn('transaction_type', ['sale', 'customer_receipt'])
            ->selectRaw(
                "{$periodSql} AS period_key, "
                    . "COALESCE(SUM(CASE WHEN LOWER(payment_mode) = 'cash' "
                    . 'THEN debit ELSE 0 END), 0) AS cash_amount, '
                    . "COALESCE(SUM(CASE WHEN LOWER(payment_mode) IN "
                    . "('bank', 'bank_transfer') THEN debit ELSE 0 END), 0) "
                    . 'AS bank_amount, '
                    . "COALESCE(SUM(CASE WHEN LOWER(payment_mode) IN "
                    . "('upi', 'gpay') THEN debit ELSE 0 END), 0) "
                    . 'AS upi_amount, '
                    . "COALESCE(SUM(CASE WHEN LOWER(payment_mode) = 'card' "
                    . 'THEN debit ELSE 0 END), 0) AS card_amount'
            )
            ->groupByRaw($periodSql)
            ->get()
            ->mapWithKeys(fn($row): array => [
                (string) $row->period_key => (array) $row,
            ]);
    }

    private function saleQuery(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        array $filters
    ): Builder {
        $query = Sales::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->where('status', Sales::STATUS_COMPLETED);

        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if ($filters['mechanic_id']) {
            $query->where('mechanic_id', $filters['mechanic_id']);
        }

        if ($filters['payment_status']) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if ($filters['billing_type']) {
            $query->where('billing_type', $filters['billing_type']);
        }

        if ($filters['sale_source']) {
            $query->where('sale_source', $filters['sale_source']);
        }

        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function (Builder $nested) use ($search): void {
                $nested
                    ->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function (Builder $customer) use ($search): void {
                        $customer
                            ->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('customer_code', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function applySaleFilters($query, array $filters, string $alias): void
    {
        foreach (
            [
                'customer_id',
                'mechanic_id',
                'payment_status',
                'billing_type',
                'sale_source',
            ] as $column
        ) {
            if ($filters[$column]) {
                $query->where("{$alias}.{$column}", $filters[$column]);
            }
        }
    }

    private function salePayload(Sales $sale, bool $withDetails = false): array
    {
        $costTotal = $sale->bill_cost_total !== null
            ? (float) $sale->bill_cost_total
            : ($sale->relationLoaded('items')
                ? (float) $sale->items->sum('cost_total')
                : 0);
        $grossProfit = $sale->bill_gross_profit !== null
            ? (float) $sale->bill_gross_profit
            : ($sale->relationLoaded('items')
                ? (float) $sale->items->sum('gross_profit')
                : 0);

        $payload = [
            'id' => $sale->id,
            'invoice_no' => $sale->invoice_no,
            'invoice_date' => optional($sale->invoice_date)->toDateString(),
            'customer' => $sale->customer ? [
                'id' => $sale->customer->id,
                'code' => $sale->customer->customer_code,
                'name' => $sale->customer->customer_name,
                'mobile' => $sale->customer->mobile,
            ] : null,
            'mechanic' => $sale->mechanic ? [
                'id' => $sale->mechanic->id,
                'code' => $sale->mechanic->mechanic_code,
                'name' => $sale->mechanic->mechanic_name,
            ] : null,
            'sale_source' => $sale->sale_source,
            'billing_type' => $sale->billing_type,
            'grand_total' => round((float) $sale->grand_total, 2),
            'paid_amount' => round((float) $sale->paid_amount, 2),
            'balance_amount' => round((float) $sale->balance_amount, 2),
            'payment_status' => $sale->payment_status,
            'cost_total' => round($costTotal, 2),
            'gross_profit' => round($grossProfit, 2),
            'commission_amount' => round((float) $sale->commission_amount, 2),
            'net_profit' => round(
                $grossProfit
                    - (float) $sale->final_discount
                    + (float) $sale->round_off
                    - (float) $sale->commission_amount,
                2
            ),
        ];

        if ($withDetails) {
            $payload['items'] = $sale->items->map(fn($item): array => [
                'id' => $item->id,
                'product_name' => $item->product?->product_name,
                'godown_name' => $item->godown?->godown_name,
                'quantity' => (float) $item->quantity,
                'sale_price' => (float) $item->sale_price,
                'cost_total' => (float) ($item->cost_total ?? 0),
                'gst_amount' => (float) $item->gst_amount,
                'line_total' => (float) $item->line_total,
                'gross_profit' => (float) ($item->gross_profit ?? 0),
            ])->values();

            $payload['payments'] = collect()
                ->merge($sale->salePayments)
                ->merge($sale->payments)
                ->map(fn($payment): array => [
                    'id' => $payment->id,
                    'date' => optional(
                        $payment->payment_date
                    )->toDateString(),
                    'mode' => $payment->payment_mode,
                    'account_name' => $payment->account?->account_name,
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'reference_no' => $payment->reference_no,
                ])->values();
        }

        return $payload;
    }

    private function periodSkeleton(
        string $fromDate,
        string $toDate,
        string $mode
    ): Collection {
        $rows = collect();
        $cursor = Carbon::parse($fromDate)->startOfDay();
        $end = Carbon::parse($toDate)->endOfDay();

        while ($cursor->lte($end)) {
            if ($mode === self::MODE_YEARLY) {
                $key = $cursor->format('Y');
                $label = $cursor->format('Y');
                $cursor->addYear()->startOfYear();
            } elseif ($mode === self::MODE_MONTHLY) {
                $key = $cursor->format('Y-m');
                $label = $cursor->format('F Y');
                $cursor->addMonth()->startOfMonth();
            } else {
                $key = $cursor->toDateString();
                $label = $cursor->format('d-m-Y');
                $cursor->addDay();
            }

            $rows->push(['key' => $key, 'label' => $label]);
        }

        return $rows;
    }

    private function summary(Collection $rows): array
    {
        $fields = [
            'bill_count',
            'gross_sales',
            'return_amount',
            'net_sales',
            'paid_amount',
            'outstanding_amount',
            'cost_total',
            'gross_profit',
            'commission_amount',
            'net_profit',
            'gst_sales',
            'non_gst_sales',
            'cash_amount',
            'bank_amount',
            'upi_amount',
            'card_amount',
        ];

        return collect($fields)->mapWithKeys(fn(string $field): array => [
            $field => $field === 'bill_count'
                ? (int) $rows->sum($field)
                : round((float) $rows->sum($field), 2),
        ])->all();
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'mode' => ['nullable', Rule::in([
                self::MODE_DAILY,
                self::MODE_MONTHLY,
                self::MODE_YEARLY,
            ])],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'customer_id' => ['nullable', 'integer'],
            'mechanic_id' => ['nullable', 'integer'],
            'payment_status' => ['nullable', Rule::in([
                Sales::PAYMENT_UNPAID,
                Sales::PAYMENT_PARTIAL,
                Sales::PAYMENT_PAID,
            ])],
            'billing_type' => ['nullable', Rule::in(['gst', 'non_gst'])],
            'sale_source' => ['nullable', Rule::in(['customer', 'mechanic'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $today = Carbon::today('Asia/Kolkata');

        return [
            'mode' => $validated['mode'] ?? self::MODE_DAILY,
            'date' => $validated['date'] ?? $today->toDateString(),
            'month' => $validated['month'] ?? $today->format('Y-m'),
            'year' => (int) ($validated['year'] ?? $today->year),
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'customer_id' => isset($validated['customer_id'])
                ? (int) $validated['customer_id']
                : null,
            'mechanic_id' => isset($validated['mechanic_id'])
                ? (int) $validated['mechanic_id']
                : null,
            'payment_status' => $validated['payment_status'] ?? null,
            'billing_type' => $validated['billing_type'] ?? null,
            'sale_source' => $validated['sale_source'] ?? null,
            'search' => trim((string) ($validated['search'] ?? '')),
        ];
    }

    private function periodDates(array $filters): array
    {
        if ($filters['from_date'] && $filters['to_date']) {
            return [$filters['from_date'], $filters['to_date']];
        }

        if ($filters['mode'] === self::MODE_YEARLY) {
            $date = Carbon::create($filters['year'], 1, 1);
            return [
                $date->copy()->startOfYear()->toDateString(),
                $date->copy()->endOfYear()->toDateString(),
            ];
        }

        if ($filters['mode'] === self::MODE_MONTHLY) {
            $date = Carbon::createFromFormat('Y-m', $filters['month']);
            return [
                $date->copy()->startOfMonth()->toDateString(),
                $date->copy()->endOfMonth()->toDateString(),
            ];
        }

        return [$filters['date'], $filters['date']];
    }

    private function customers(int $companyId, int $storeId): Collection
    {
        return Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas(
                'stores',
                fn(Builder $query) =>
                $query->where('stores.id', $storeId)
            )
            ->orderBy('customer_name')
            ->get(['id', 'customer_code', 'customer_name']);
    }

    private function mechanics(int $companyId, int $storeId): Collection
    {
        return Mechanic::query()
            ->forContext($companyId, $storeId)
            ->active()
            ->orderBy('mechanic_name')
            ->get(['id', 'mechanic_code', 'mechanic_name']);
    }

    private function context(Request $request): array
    {
        $user = $request->user();
        $companyId = (int) session('active_company_id');
        $storeId = (int) session('active_store_id');

        if (!$user?->is_super_user && !$companyId) {
            $companyId = (int) $user?->company_id;
        }

        abort_if(!$companyId || !$storeId, 403, 'Select a company and branch.');

        $store = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        if (!$user?->is_super_user && method_exists($user, 'stores')) {
            abort_unless(
                $user->stores()->whereKey($storeId)->exists(),
                403
            );
        }

        return [(int) $store->company_id, (int) $store->id];
    }

    private function contextPayload(
        Request $request,
        int $companyId,
        int $storeId
    ): array {
        $store = Store::query()->findOrFail($storeId);

        return [
            'company_id' => $companyId,
            'store_id' => $storeId,
            'store_name' => $store->store_name ?? $store->name,
            'is_super_user' => (bool) $request->user()?->is_super_user,
        ];
    }

    private function periodSql(string $column, string $mode): string
    {
        return match ($mode) {
            self::MODE_YEARLY => "DATE_FORMAT({$column}, '%Y')",
            self::MODE_MONTHLY => "DATE_FORMAT({$column}, '%Y-%m')",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }

    private function paper(Request $request): string
    {
        return strtolower((string) $request->input('paper')) === 'a5'
            ? 'a5'
            : 'a4';
    }

    private function fileName(array $filters, string $extension): string
    {
        return 'sales-report-'
            . $filters['mode']
            . '-'
            . $filters['from_date']
            . '-to-'
            . $filters['to_date']
            . '.'
            . $extension;
    }

    private function csvNumber($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
