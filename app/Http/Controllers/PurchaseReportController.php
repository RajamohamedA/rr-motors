<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Store;
use App\Models\Supplier;
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

class PurchaseReportController extends Controller implements HasMiddleware
{
    public const MODE_DAILY = 'daily';
    public const MODE_MONTHLY = 'monthly';
    public const MODE_YEARLY = 'yearly';

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:purchase-report.view',
                only: ['index', 'show']
            ),
            new Middleware(
                'permission:purchase-report.print',
                only: ['print', 'printReport']
            ),
            new Middleware(
                'permission:purchase-report.pdf',
                only: ['pdf', 'pdfReport']
            ),
            new Middleware(
                'permission:purchase-report.excel',
                only: ['excel', 'csvReport']
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
            $filters['mode'],
            $filters
        );

        $purchases = $this->purchaseQuery(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $filters
        )
            ->with([
                'supplier:id,supplier_code,supplier_name',
                'store:id,store_name',
            ])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString()
            ->through(
                fn(Purchase $purchase): array =>
                $this->purchasePayload($purchase)
            );

        return Inertia::render('PurchaseReports/Index', [
            'rows' => $rows->values(),
            'summary' => $this->summary($rows),
            'purchases' => $purchases,
            'filters' => [
                ...$filters,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
            'suppliers' => Supplier::query()
                ->forCompany($companyId)
                ->forStore($storeId)
                ->orderBy('supplier_name')
                ->get(['id', 'supplier_code', 'supplier_name']),
            'context' => $this->contextPayload(
                $request,
                $companyId,
                $storeId
            ),
        ]);
    }

    public function show(Request $request, Purchase $purchase): Response
    {
        [$companyId, $storeId] = $this->context($request);

        abort_unless(
            (int) $purchase->company_id === $companyId
                && (int) $purchase->store_id === $storeId,
            404
        );

        $purchase->load([
            'supplier',
            'store',
            'items.product',
            'items.godown',
        ]);

        return Inertia::render('PurchaseReports/Show', [
            'purchase' => $this->purchasePayload($purchase, true),
        ]);
    }

    public function print(Request $request): HttpResponse
    {
        return response()->view(
            'purchase_reports.print',
            $this->exportPayload($request, false)
        );
    }

    public function pdf(Request $request): HttpResponse
    {
        $payload = $this->exportPayload($request, true);

        return Pdf::loadView('purchase_reports.pdf', $payload)
            ->setPaper($payload['paper'], 'portrait')
            ->download($this->fileName($payload['filters'], 'pdf'));
    }

    public function excel(Request $request): StreamedResponse
    {
        $payload = $this->exportPayload($request, false);

        return response()->streamDownload(
            function () use ($payload): void {
                $stream = fopen('php://output', 'wb');
                fwrite($stream, "\xEF\xBB\xBF");

                fputcsv($stream, [
                    'Period',
                    'Bills',
                    'Gross Purchase',
                    'Purchase Return',
                    'Net Purchase',
                    'Paid Amount',
                    'Outstanding',
                    'GST Purchase',
                    'Non-GST Purchase',
                ]);

                foreach ($payload['rows'] as $row) {
                    fputcsv($stream, [
                        $row['label'],
                        $row['bill_count'],
                        $this->csvNumber($row['gross_purchase']),
                        $this->csvNumber($row['return_amount']),
                        $this->csvNumber($row['net_purchase']),
                        $this->csvNumber($row['paid_amount']),
                        $this->csvNumber($row['outstanding_amount']),
                        $this->csvNumber($row['gst_purchase']),
                        $this->csvNumber($row['non_gst_purchase']),
                    ]);
                }

                fclose($stream);
            },
            $this->fileName($payload['filters'], 'csv'),
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    /* Compatibility with the existing route names. */
    public function printReport(Request $request): HttpResponse
    {
        return $this->print($request);
    }

    public function pdfReport(Request $request): HttpResponse
    {
        return $this->pdf($request);
    }

    public function csvReport(Request $request): StreamedResponse
    {
        return $this->excel($request);
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
            $filters['mode'],
            $filters
        );

        return [
            'rows' => $rows,
            'summary' => $this->summary($rows),
            'purchases' => $this->purchaseQuery(
                $companyId,
                $storeId,
                $fromDate,
                $toDate,
                $filters
            )
                ->with('supplier:id,supplier_code,supplier_name')
                ->orderBy('purchase_date')
                ->orderBy('id')
                ->get()
                ->map(
                    fn(Purchase $purchase): array =>
                    $this->purchasePayload($purchase)
                ),
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
        string $mode,
        array $filters
    ): Collection {
        $purchases = $this->purchaseAggregate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $mode,
            $filters
        );
        $returns = $this->returnAggregate(
            $companyId,
            $storeId,
            $fromDate,
            $toDate,
            $mode,
            $filters
        );

        return $this->periodSkeleton(
            $fromDate,
            $toDate,
            $mode
        )->map(function (array $period) use ($purchases, $returns): array {
            $purchase = $purchases->get($period['key'], []);
            $returnAmount = (float) ($returns->get($period['key']) ?? 0);
            $gross = (float) ($purchase['gross_purchase'] ?? 0);

            return [
                ...$period,
                'bill_count' => (int) ($purchase['bill_count'] ?? 0),
                'gross_purchase' => round($gross, 2),
                'return_amount' => round($returnAmount, 2),
                'net_purchase' => round($gross - $returnAmount, 2),
                'paid_amount' => round(
                    (float) ($purchase['paid_amount'] ?? 0),
                    2
                ),
                'outstanding_amount' => round(
                    (float) ($purchase['outstanding_amount'] ?? 0),
                    2
                ),
                'gst_purchase' => round(
                    (float) ($purchase['gst_purchase'] ?? 0),
                    2
                ),
                'non_gst_purchase' => round(
                    (float) ($purchase['non_gst_purchase'] ?? 0),
                    2
                ),
            ];
        });
    }

    private function purchaseAggregate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        string $mode,
        array $filters
    ): Collection {
        $periodSql = $this->periodSql('purchase_date', $mode);
        $paidColumn = Schema::hasColumn('purchases', 'paid_amount')
            ? 'paid_amount'
            : '0';
        $balanceColumn = Schema::hasColumn('purchases', 'balance_amount')
            ? 'balance_amount'
            : '0';
        $billingColumn = $this->purchaseBillingColumn();

        $query = DB::table('purchases')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereBetween('purchase_date', [$fromDate, $toDate]);

        $this->applyCompletedPurchaseFilter($query);

        if ($filters['supplier_id']) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (
            $filters['payment_status']
            && Schema::hasColumn('purchases', 'payment_status')
        ) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (
            $filters['billing_type']
            && $billingColumn
        ) {
            $query->whereRaw(
                "LOWER({$billingColumn}) = ?",
                [strtolower($filters['billing_type'])]
            );
        }

        $select = [
            DB::raw("{$periodSql} AS period_key"),
            DB::raw('COUNT(*) AS bill_count'),
            DB::raw('COALESCE(SUM(grand_total), 0) AS gross_purchase'),
            DB::raw("COALESCE(SUM({$paidColumn}), 0) AS paid_amount"),
            DB::raw(
                "COALESCE(SUM({$balanceColumn}), 0) AS outstanding_amount"
            ),
        ];

        if ($billingColumn) {
            $select[] = DB::raw(
                "COALESCE(SUM(CASE WHEN LOWER({$billingColumn}) = 'gst' "
                    . 'THEN grand_total ELSE 0 END), 0) AS gst_purchase'
            );
            $select[] = DB::raw(
                "COALESCE(SUM(CASE WHEN LOWER({$billingColumn}) = 'non_gst' "
                    . 'THEN grand_total ELSE 0 END), 0) AS non_gst_purchase'
            );
        } else {
            $select[] = DB::raw('0 AS gst_purchase');
            $select[] = DB::raw('0 AS non_gst_purchase');
        }

        return $query
            ->select($select)
            ->groupByRaw($periodSql)
            ->get()
            ->mapWithKeys(fn($row): array => [
                (string) $row->period_key => [
                    'bill_count' => (int) $row->bill_count,
                    'gross_purchase' => (float) $row->gross_purchase,
                    'paid_amount' => (float) $row->paid_amount,
                    'outstanding_amount' => (float) $row->outstanding_amount,
                    'gst_purchase' => (float) $row->gst_purchase,
                    'non_gst_purchase' => (float) $row->non_gst_purchase,
                ],
            ]);
    }

    private function returnAggregate(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        string $mode,
        array $filters
    ): Collection {
        if (!Schema::hasTable('purchase_returns')) {
            return collect();
        }

        $dateColumn = Schema::hasColumn(
            'purchase_returns',
            'return_date'
        ) ? 'return_date' : 'created_at';
        $amountColumn = Schema::hasColumn(
            'purchase_returns',
            'total_amount'
        ) ? 'total_amount' : 'grand_total';
        $periodSql = $this->periodSql("pr.{$dateColumn}", $mode);
        $hasStoreId = Schema::hasColumn('purchase_returns', 'store_id');
        $hasCompanyId = Schema::hasColumn('purchase_returns', 'company_id');
        $hasSupplierId = Schema::hasColumn(
            'purchase_returns',
            'supplier_id'
        );
        $mustJoinPurchase = !$hasStoreId
            || !$hasCompanyId
            || ($filters['supplier_id'] && !$hasSupplierId);

        $query = DB::table('purchase_returns as pr');

        if ($mustJoinPurchase) {
            $query->join(
                'purchases as p',
                'p.id',
                '=',
                'pr.purchase_id'
            );
        }

        $query
            ->where(
                $hasCompanyId ? 'pr.company_id' : 'p.company_id',
                $companyId
            )
            ->where(
                $hasStoreId ? 'pr.store_id' : 'p.store_id',
                $storeId
            )
            ->whereBetween(
                "pr.{$dateColumn}",
                [$fromDate, $toDate]
            );

        if ($filters['supplier_id']) {
            $query->where(
                $hasSupplierId ? 'pr.supplier_id' : 'p.supplier_id',
                $filters['supplier_id']
            );
        }

        if (Schema::hasColumn('purchase_returns', 'status')) {
            $query->where(function ($statusQuery): void {
                $statusQuery
                    ->whereIn(
                        'pr.status',
                        [1, '1', 'completed', 'Completed']
                    )
                    ->orWhereNull('pr.status');
            });
        }

        return $query
            ->selectRaw(
                "{$periodSql} AS period_key, "
                    . "COALESCE(SUM(pr.{$amountColumn}), 0) AS return_amount"
            )
            ->groupByRaw($periodSql)
            ->pluck('return_amount', 'period_key')
            ->map(fn($amount): float => (float) $amount);
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

    private function purchaseQuery(
        int $companyId,
        int $storeId,
        string $fromDate,
        string $toDate,
        array $filters
    ): Builder {
        $query = Purchase::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereBetween('purchase_date', [$fromDate, $toDate]);

        if ($filters['supplier_id']) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('purchase_no', 'like', "%{$search}%")
                    ->when(
                        Schema::hasColumn('purchases', 'invoice_no'),
                        fn(Builder $query): Builder => $query->orWhere(
                            'invoice_no',
                            'like',
                            "%{$search}%"
                        )
                    )
                    ->orWhereHas('supplier', function (Builder $supplierQuery) use ($search): void {
                        $supplierQuery
                            ->where('supplier_name', 'like', "%{$search}%")
                            ->orWhere('supplier_code', 'like', "%{$search}%");
                    });
            });
        }

        if (
            $filters['payment_status']
            && Schema::hasColumn('purchases', 'payment_status')
        ) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (
            $filters['billing_type']
            && $this->purchaseBillingColumn()
        ) {
            $query->whereRaw(
                'LOWER(' . $this->purchaseBillingColumn() . ') = ?',
                [strtolower($filters['billing_type'])]
            );
        }

        if (Schema::hasColumn('purchases', 'status')) {
            $query->where(function (Builder $statusQuery): void {
                $statusQuery
                    ->whereIn('status', [1, '1', 'Completed', 'completed'])
                    ->orWhereNull('status');
            });
        }

        return $query;
    }

    private function purchasePayload(
        Purchase $purchase,
        bool $withItems = false
    ): array {
        $payload = [
            'id' => $purchase->id,
            'purchase_no' => $purchase->purchase_no,
            'purchase_date' => optional($purchase->purchase_date)
                ->toDateString(),
            'supplier' => $purchase->supplier ? [
                'id' => $purchase->supplier->id,
                'code' => $purchase->supplier->supplier_code,
                'name' => $purchase->supplier->supplier_name,
            ] : null,
            'billing_type' => strtolower((string) (
                $purchase->billing_type
                ?? $purchase->tax_type
                ?? ''
            )),
            'grand_total' => round((float) $purchase->grand_total, 2),
            'paid_amount' => round((float) ($purchase->paid_amount ?? 0), 2),
            'balance_amount' => round(
                (float) ($purchase->balance_amount ?? 0),
                2
            ),
            'payment_status' => $purchase->payment_status ?? null,
            'status' => $purchase->status,
        ];

        if ($withItems) {
            $payload['items'] = $purchase->items->map(
                fn($item): array => [
                    'id' => $item->id,
                    'product_name' => $item->product?->product_name,
                    'godown_name' => $item->godown?->godown_name,
                    'quantity' => (float) $item->quantity,
                    'purchase_price' => (float) $item->purchase_price,
                    'gst_amount' => (float) ($item->gst_amount ?? 0),
                    'line_total' => (float) $item->line_total,
                ]
            )->values();
        }

        return $payload;
    }

    private function summary(Collection $rows): array
    {
        return [
            'bill_count' => (int) $rows->sum('bill_count'),
            'gross_purchase' => round($rows->sum('gross_purchase'), 2),
            'return_amount' => round($rows->sum('return_amount'), 2),
            'net_purchase' => round($rows->sum('net_purchase'), 2),
            'paid_amount' => round($rows->sum('paid_amount'), 2),
            'outstanding_amount' => round(
                $rows->sum('outstanding_amount'),
                2
            ),
            'gst_purchase' => round($rows->sum('gst_purchase'), 2),
            'non_gst_purchase' => round(
                $rows->sum('non_gst_purchase'),
                2
            ),
        ];
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'mode' => [
                'nullable',
                Rule::in([
                    self::MODE_DAILY,
                    self::MODE_MONTHLY,
                    self::MODE_YEARLY,
                ]),
            ],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'supplier_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'payment_status' => [
                'nullable',
                Rule::in(['Unpaid', 'Partially Paid', 'Paid']),
            ],
            'billing_type' => [
                'nullable',
                Rule::in(['gst', 'non_gst']),
            ],
        ]);

        return [
            'mode' => $validated['mode'] ?? self::MODE_DAILY,
            'date' => $validated['date'] ?? Carbon::today('Asia/Kolkata')
                ->toDateString(),
            'month' => $validated['month'] ?? Carbon::today('Asia/Kolkata')
                ->format('Y-m'),
            'year' => (int) ($validated['year'] ?? Carbon::today('Asia/Kolkata')
                ->year),
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'supplier_id' => isset($validated['supplier_id'])
                ? (int) $validated['supplier_id']
                : null,
            'search' => trim((string) ($validated['search'] ?? '')),
            'payment_status' => $validated['payment_status'] ?? null,
            'billing_type' => $validated['billing_type'] ?? null,
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

    private function applyCompletedPurchaseFilter($query): void
    {
        if (!Schema::hasColumn('purchases', 'status')) {
            return;
        }

        $query->where(function ($statusQuery): void {
            $statusQuery
                ->whereIn('status', [1, '1', 'Completed', 'completed'])
                ->orWhereNull('status');
        });
    }

    private function paper(Request $request): string
    {
        return strtolower((string) $request->input('paper')) === 'a5'
            ? 'a5'
            : 'a4';
    }

    private function purchaseBillingColumn(): ?string
    {
        if (Schema::hasColumn('purchases', 'billing_type')) {
            return 'billing_type';
        }

        if (Schema::hasColumn('purchases', 'tax_type')) {
            return 'tax_type';
        }

        return null;
    }

    private function fileName(array $filters, string $extension): string
    {
        return 'purchase-report-'
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
