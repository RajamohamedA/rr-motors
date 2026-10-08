<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OutstandingReportController extends Controller implements HasMiddleware
{
    public const TYPE_CUSTOMER = 'customer';

    public const TYPE_SUPPLIER = 'supplier';

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

        $rows = $this->reportQuery(
            type: $filters['type'],
            companyId: $companyId,
            storeId: $storeId,
            asOfDate: $filters['as_of_date'],
            search: $filters['search'],
            minimumBalance: $filters['minimum_balance']
        )
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(
                fn(Customer|Supplier $party): array =>
                $this->partyPayload($party, $filters['type'])
            );

        $customerSummary = $this->summary(
            self::TYPE_CUSTOMER,
            $companyId,
            $storeId,
            $filters['as_of_date']
        );

        $supplierSummary = $this->summary(
            self::TYPE_SUPPLIER,
            $companyId,
            $storeId,
            $filters['as_of_date']
        );

        return Inertia::render('OutstandingReports/Index', [
            'rows' => $rows,
            'filters' => $filters,
            'summary' => [
                'customer' => $customerSummary,
                'supplier' => $supplierSummary,
                'combined_total' => round(
                    $customerSummary['total']
                        + $supplierSummary['total'],
                    2
                ),
            ],
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
            'outstanding_reports.print',
            $this->printPayload($request, false)
        );
    }

    public function pdf(Request $request): HttpResponse
    {
        $payload = $this->printPayload($request, true);
        $paper = $payload['paper'];
        $type = $payload['filters']['type'];

        return Pdf::loadView(
            'outstanding_reports.pdf',
            $payload
        )
            ->setPaper($paper, 'portrait')
            ->download(
                $type
                    . '-outstanding-'
                    . $payload['filters']['as_of_date']
                    . '.pdf'
            );
    }

    public function excel(Request $request): StreamedResponse
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->filters($request);

        $rows = $this->reportQuery(
            type: $filters['type'],
            companyId: $companyId,
            storeId: $storeId,
            asOfDate: $filters['as_of_date'],
            search: $filters['search'],
            minimumBalance: $filters['minimum_balance']
        )->get();

        $fileName = $filters['type']
            . '-outstanding-'
            . $filters['as_of_date']
            . '.csv';

        return response()->streamDownload(
            function () use ($rows, $filters): void {
                $stream = fopen('php://output', 'wb');

                fwrite($stream, "\xEF\xBB\xBF");

                fputcsv($stream, [
                    'Code',
                    'Name',
                    'Mobile',
                    'City',
                    'Credit Days',
                    'Outstanding Amount',
                    'Balance Type',
                    'As Of Date',
                ]);

                foreach ($rows as $party) {
                    $payload = $this->partyPayload(
                        $party,
                        $filters['type']
                    );

                    fputcsv($stream, [
                        $payload['code'],
                        $payload['name'],
                        $payload['mobile'],
                        $payload['city'],
                        $payload['credit_days'],
                        number_format($payload['outstanding_balance'], 2, '.', ''),
                        $filters['type'] === self::TYPE_CUSTOMER
                            ? 'Dr'
                            : 'Cr',
                        $filters['as_of_date'],
                    ]);
                }

                fclose($stream);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    private function reportQuery(
        string $type,
        int $companyId,
        int $storeId,
        string $asOfDate,
        string $search,
        float $minimumBalance
    ): Builder {
        if ($type === self::TYPE_SUPPLIER) {
            return Supplier::query()
                ->forCompany($companyId)
                ->forStore($storeId)
                ->select('suppliers.*')
                ->selectSub(
                    SupplierLedgerEntry::query()
                        ->selectRaw(
                            'COALESCE(SUM(credit - debit), 0)'
                        )
                        ->whereColumn(
                            'supplier_id',
                            'suppliers.id'
                        )
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->whereDate(
                            'transaction_date',
                            '<=',
                            $asOfDate
                        ),
                    'outstanding_balance'
                )
                ->when(
                    $search !== '',
                    function (Builder $query) use ($search): void {
                        $query->where(
                            function (Builder $searchQuery) use ($search): void {
                                $searchQuery
                                    ->where(
                                        'supplier_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'supplier_code',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'phone',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'contact_person',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                    }
                )
                ->having('outstanding_balance', '>', $minimumBalance)
                ->orderByDesc('outstanding_balance')
                ->orderBy('supplier_name');
        }

        return Customer::query()
            ->where('company_id', $companyId)
            ->whereHas(
                'stores',
                fn(Builder $query) =>
                $query->where('stores.id', $storeId)
            )
            ->select('customers.*')
            ->selectSub(
                CustomerLedgerEntry::query()
                    ->selectRaw(
                        'COALESCE(SUM(debit - credit), 0)'
                    )
                    ->whereColumn(
                        'customer_id',
                        'customers.id'
                    )
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->whereDate(
                        'transaction_date',
                        '<=',
                        $asOfDate
                    ),
                'outstanding_balance'
            )
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(
                        function (Builder $searchQuery) use ($search): void {
                            $searchQuery
                                ->where(
                                    'customer_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'customer_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'mobile',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'contact_person',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->having('outstanding_balance', '>', $minimumBalance)
            ->orderByDesc('outstanding_balance')
            ->orderBy('customer_name');
    }

    private function summary(
        string $type,
        int $companyId,
        int $storeId,
        string $asOfDate
    ): array {
        $query = $type === self::TYPE_SUPPLIER
            ? SupplierLedgerEntry::query()
            ->selectRaw(
                'supplier_id AS party_id, '
                    . 'SUM(credit - debit) AS balance'
            )
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereDate('transaction_date', '<=', $asOfDate)
            ->groupBy('supplier_id')
            ->having('balance', '>', 0)
            : CustomerLedgerEntry::query()
            ->selectRaw(
                'customer_id AS party_id, '
                    . 'SUM(debit - credit) AS balance'
            )
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereDate('transaction_date', '<=', $asOfDate)
            ->groupBy('customer_id')
            ->having('balance', '>', 0);

        $balances = $query->pluck('balance');

        return [
            'count' => $balances->count(),
            'total' => round(
                $balances->sum(
                    fn($balance) => (float) $balance
                ),
                2
            ),
            'highest' => round(
                (float) ($balances->max() ?? 0),
                2
            ),
        ];
    }

    private function printPayload(
        Request $request,
        bool $pdfMode
    ): array {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->filters($request);

        $rows = $this->reportQuery(
            type: $filters['type'],
            companyId: $companyId,
            storeId: $storeId,
            asOfDate: $filters['as_of_date'],
            search: $filters['search'],
            minimumBalance: $filters['minimum_balance']
        )
            ->get()
            ->map(
                fn(Customer|Supplier $party): array =>
                $this->partyPayload($party, $filters['type'])
            );

        return [
            'rows' => $rows,
            'filters' => $filters,
            'summary' => [
                'count' => $rows->count(),
                'total' => round(
                    $rows->sum('outstanding_balance'),
                    2
                ),
                'highest' => round(
                    (float) ($rows->max('outstanding_balance') ?? 0),
                    2
                ),
            ],
            'store' => Store::query()->find($storeId),
            'paper' => $this->paper($request),
            'pdfMode' => $pdfMode,
            'generatedAt' => Carbon::now('Asia/Kolkata'),
        ];
    }

    private function partyPayload(
        Customer|Supplier $party,
        string $type
    ): array {
        $supplier = $type === self::TYPE_SUPPLIER;

        return [
            'id' => $party->id,
            'type' => $type,
            'code' => $supplier
                ? $party->supplier_code
                : $party->customer_code,
            'name' => $supplier
                ? $party->supplier_name
                : $party->customer_name,
            'contact_person' => $party->contact_person,
            'mobile' => $supplier
                ? $party->phone
                : $party->mobile,
            'city' => $party->city,
            'credit_days' => (int) $party->credit_days,
            'credit_limit' => (float) $party->credit_limit,
            'outstanding_balance' => round(
                (float) $party->outstanding_balance,
                2
            ),
            'ledger_url' => $supplier
                ? route('supplier-ledger.index', [
                    'supplier_id' => $party->id,
                ])
                : route('customer-ledger.index', [
                    'customer_id' => $party->id,
                ]),
            'party_url' => $supplier
                ? route('suppliers.show', $party)
                : route('customers.show', $party),
        ];
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'type' => [
                'nullable',
                Rule::in([
                    self::TYPE_CUSTOMER,
                    self::TYPE_SUPPLIER,
                ]),
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'as_of_date' => ['nullable', 'date', 'before_or_equal:today'],
            'minimum_balance' => ['nullable', 'numeric', 'min:0'],
            'per_page' => [
                'nullable',
                'integer',
                Rule::in([10, 20, 50, 100]),
            ],
            'paper' => ['nullable', Rule::in(['a4', 'a5'])],
        ]);

        return [
            'type' => $validated['type'] ?? self::TYPE_CUSTOMER,
            'search' => trim((string) ($validated['search'] ?? '')),
            'as_of_date' => $validated['as_of_date']
                ?? Carbon::now('Asia/Kolkata')->toDateString(),
            'minimum_balance' => round(
                (float) ($validated['minimum_balance'] ?? 0),
                2
            ),
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
