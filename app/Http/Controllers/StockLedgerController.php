<?php

namespace App\Http\Controllers;

use App\Models\Godown;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockLedgerController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:stock-ledger.view',
                only: ['index', 'search']
            ),
            new Middleware(
                'permission:stock-ledger.show',
                only: ['show']
            ),
            new Middleware(
                'permission:stock-ledger.print',
                only: ['printReport']
            ),
            new Middleware(
                'permission:stock-ledger.pdf',
                only: ['pdfReport']
            ),
            new Middleware(
                'permission:stock-ledger.excel',
                only: ['csvReport']
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Stock Ledger Register
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->validatedFilters($request);

        $this->validateGodown(
            $filters['godown_id'],
            $companyId,
            $storeId
        );

        $query = $this->ledgerQuery($companyId, $storeId);
        $this->applyFilters($query, $filters);

        $summary = $this->summary(
            clone $query,
            $filters,
            $companyId,
            $storeId
        );

        $ledgers = $query
            ->orderByDesc('stock_ledgers.transaction_date')
            ->orderByDesc('stock_ledgers.id')
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(
                fn(StockLedger $ledger) => $this->ledgerRow($ledger)
            );

        $godowns = Godown::query()
            ->select([
                'id',
                'godown_name',
                'godown_code',
            ])
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('godown_name')
            ->get();

        $transactionTypes = StockLedger::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->whereNotNull('transaction_type')
            ->where('transaction_type', '<>', '')
            ->distinct()
            ->orderBy('transaction_type')
            ->pluck('transaction_type')
            ->values();

        $selectedProduct = null;

        if ($filters['product_id']) {
            $selectedProduct = Product::query()
                ->select([
                    'id',
                    'product_code',
                    'product_name',
                    'barcode',
                    'part_number',
                ])
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->find($filters['product_id']);
        }

        return Inertia::render('Inventory/StockLedgers/Index', [
            'ledgers' => $ledgers,
            'filters' => $filters,
            'summary' => $summary,
            'godowns' => $godowns,
            'transactionTypes' => $transactionTypes,
            'selectedProduct' => $selectedProduct,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Product Search
    |--------------------------------------------------------------------------
    */

    public function search(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $search = trim($validated['search'] ?? '');

        if (mb_strlen($search) < 2) {
            return response()->json([
                'products' => [],
                'message' => 'Enter at least 2 characters.',
            ]);
        }

        $term = '%' . $search . '%';

        $products = Product::query()
            ->select([
                'id',
                'product_code',
                'product_name',
                'barcode',
                'part_number',
                'rack_location',
            ])
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->where(function (Builder $query) use ($term) {
                $query
                    ->where('product_name', 'like', $term)
                    ->orWhere('product_code', 'like', $term)
                    ->orWhere('barcode', 'like', $term)
                    ->orWhere('part_number', 'like', $term)
                    ->orWhere('manufacturer', 'like', $term);
            })
            ->orderBy('product_name')
            ->limit(15)
            ->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Show One Ledger Transaction
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        StockLedger $stockLedger
    ): Response {
        [$companyId, $storeId] = $this->context($request);

        $this->ensureLedgerContext(
            $stockLedger,
            $companyId,
            $storeId
        );

        $stockLedger->load([
            'company',
            'store',
            'godown',
            'product.category',
            'product.brand',
            'product.unit',
        ]);

        $previous = StockLedger::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('product_id', $stockLedger->product_id)
            ->where(function (Builder $query) use ($stockLedger) {
                if ($stockLedger->godown_id === null) {
                    $query->whereNull('godown_id');
                } else {
                    $query->where('godown_id', $stockLedger->godown_id);
                }
            })
            ->where('id', '<', $stockLedger->id)
            ->latest('id')
            ->first();

        return Inertia::render('Inventory/StockLedgers/Show', [
            'ledger' => $this->ledgerRow($stockLedger),
            'openingBalance' => round(
                (float) ($previous?->balance_qty ?? 0),
                2
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Print Report
    |--------------------------------------------------------------------------
    */

    public function printReport(Request $request): View
    {
        return view(
            'inventory.stock-ledgers.print',
            $this->exportData($request)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Report
    |--------------------------------------------------------------------------
    */

    public function pdfReport(Request $request)
    {
        return Pdf::loadView(
            'inventory.stock-ledgers.pdf',
            $this->exportData($request)
        )
            ->setPaper('a4', 'landscape')
            ->download(
                'stock-ledger-'
                    . now()->format('Y-m-d-His')
                    . '.pdf'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Excel-Compatible CSV Report
    |--------------------------------------------------------------------------
    */

    public function csvReport(Request $request): StreamedResponse
    {
        $data = $this->exportData($request);
        $fileName = 'stock-ledger-'
            . now()->format('Y-m-d-His')
            . '.csv';

        return response()->streamDownload(
            function () use ($data) {
                $output = fopen('php://output', 'w');

                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, ['STOCK LEDGER REPORT']);
                fputcsv($output, [
                    'Company',
                    $this->safeSpreadsheetValue(
                        $data['store']->company?->company_name
                    ),
                ]);
                fputcsv($output, [
                    'Branch',
                    $this->safeSpreadsheetValue(
                        $data['store']->store_name
                    ),
                ]);
                fputcsv($output, [
                    'Generated At',
                    $data['generatedAt']->format('d-m-Y h:i A'),
                ]);
                fputcsv($output, []);
                fputcsv($output, [
                    'Opening Quantity',
                    $data['summary']['opening_quantity'],
                    'Quantity In',
                    $data['summary']['quantity_in'],
                    'Quantity Out',
                    $data['summary']['quantity_out'],
                    'Closing Quantity',
                    $data['summary']['closing_quantity'],
                    'Inward Value',
                    $data['summary']['inward_value'],
                    'Outward Value',
                    $data['summary']['outward_value'],
                ]);
                fputcsv($output, []);
                fputcsv($output, [
                    'S.No',
                    'Date',
                    'Product Code',
                    'Product Name',
                    'Part Number',
                    'Location',
                    'Transaction Type',
                    'Reference Number',
                    'Quantity In',
                    'Quantity Out',
                    'Balance Quantity',
                    'Unit Cost',
                    'Total Cost',
                    'Remarks',
                ]);

                foreach ($data['ledgers'] as $index => $ledger) {
                    fputcsv($output, [
                        $index + 1,
                        $ledger['transaction_date'],
                        $this->safeSpreadsheetValue(
                            $ledger['product']?->product_code
                        ),
                        $this->safeSpreadsheetValue(
                            $ledger['product']?->product_name
                        ),
                        $this->safeSpreadsheetValue(
                            $ledger['product']?->part_number
                        ),
                        $this->safeSpreadsheetValue(
                            $ledger['location_name']
                        ),
                        $this->safeSpreadsheetValue(
                            $ledger['transaction_type']
                        ),
                        $this->safeSpreadsheetValue(
                            $ledger['reference_no']
                        ),
                        $ledger['qty_in'],
                        $ledger['qty_out'],
                        $ledger['balance_qty'],
                        $ledger['unit_cost'],
                        $ledger['total_cost'],
                        $this->safeSpreadsheetValue(
                            $ledger['remarks']
                        ),
                    ]);
                }

                fclose($output);
            },
            $fileName,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Export Data
    |--------------------------------------------------------------------------
    */

    private function exportData(Request $request): array
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = $this->validatedFilters($request, false);

        $this->validateGodown(
            $filters['godown_id'],
            $companyId,
            $storeId
        );

        $query = $this->ledgerQuery($companyId, $storeId);
        $this->applyFilters($query, $filters);

        $summary = $this->summary(
            clone $query,
            $filters,
            $companyId,
            $storeId
        );

        $ledgers = $query
            ->orderBy('stock_ledgers.transaction_date')
            ->orderBy('stock_ledgers.id')
            ->get()
            ->map(
                fn(StockLedger $ledger) => $this->ledgerRow($ledger)
            );

        $store = Store::query()
            ->with('company')
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        return [
            'ledgers' => $ledgers,
            'summary' => $summary,
            'filters' => $filters,
            'store' => $store,
            'generatedAt' => now(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Base Ledger Query
    |--------------------------------------------------------------------------
    */

    private function ledgerQuery(
        int $companyId,
        int $storeId
    ): Builder {
        return StockLedger::query()
            ->select('stock_ledgers.*')
            ->join(
                'products',
                'products.id',
                '=',
                'stock_ledgers.product_id'
            )
            ->where('stock_ledgers.company_id', $companyId)
            ->where('stock_ledgers.store_id', $storeId)
            ->where('products.company_id', $companyId)
            ->where('products.store_id', $storeId)
            ->with([
                'product:id,company_id,store_id,category_id,brand_id,unit_id,product_code,barcode,product_name,part_number,manufacturer,rack_location',
                'product.category:id,name',
                'product.brand:id,brand_name',
                'product.unit:id,unit_name',
                'godown:id,company_id,store_id,godown_name,godown_code',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */

    private function applyFilters(
        Builder $query,
        array $filters,
        bool $includeDates = true,
        bool $includeTransactionType = true
    ): void {
        if ($filters['search'] !== '') {
            $term = '%' . $filters['search'] . '%';

            $query->where(function (Builder $query) use ($term) {
                $query
                    ->where('products.product_name', 'like', $term)
                    ->orWhere('products.product_code', 'like', $term)
                    ->orWhere('products.barcode', 'like', $term)
                    ->orWhere('products.part_number', 'like', $term)
                    ->orWhere('stock_ledgers.reference_no', 'like', $term)
                    ->orWhere('stock_ledgers.remarks', 'like', $term);
            });
        }

        if ($filters['product_id']) {
            $query->where(
                'stock_ledgers.product_id',
                $filters['product_id']
            );
        }

        match ($filters['location']) {
            'shop' => $query->whereNull('stock_ledgers.godown_id'),
            'godown' => $query->whereNotNull('stock_ledgers.godown_id'),
            default => null,
        };

        if ($filters['godown_id']) {
            $query->where(
                'stock_ledgers.godown_id',
                $filters['godown_id']
            );
        }

        if (
            $includeTransactionType
            && $filters['transaction_type'] !== ''
        ) {
            $query->where(
                'stock_ledgers.transaction_type',
                $filters['transaction_type']
            );
        }

        if ($includeDates && $filters['from_date']) {
            $query->whereDate(
                'stock_ledgers.transaction_date',
                '>=',
                $filters['from_date']
            );
        }

        if ($includeDates && $filters['to_date']) {
            $query->whereDate(
                'stock_ledgers.transaction_date',
                '<=',
                $filters['to_date']
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    private function summary(
        Builder $filteredQuery,
        array $filters,
        int $companyId,
        int $storeId
    ): array {
        $movement = $filteredQuery
            ->reorder()
            ->select([])
            ->selectRaw(
                'COUNT(*) AS transaction_count,
                 COALESCE(SUM(stock_ledgers.qty_in), 0) AS quantity_in,
                 COALESCE(SUM(stock_ledgers.qty_out), 0) AS quantity_out,
                 COALESCE(SUM(
                    stock_ledgers.qty_in * stock_ledgers.unit_cost
                 ), 0) AS inward_value,
                 COALESCE(SUM(
                    stock_ledgers.qty_out * stock_ledgers.unit_cost
                 ), 0) AS outward_value'
            )
            ->first();

        $opening = $this->openingQuantity(
            $filters,
            $companyId,
            $storeId
        );
        $quantityIn = (float) ($movement->quantity_in ?? 0);
        $quantityOut = (float) ($movement->quantity_out ?? 0);

        return [
            'transaction_count' => (int) (
                $movement->transaction_count ?? 0
            ),
            'opening_quantity' => round($opening, 2),
            'quantity_in' => round($quantityIn, 2),
            'quantity_out' => round($quantityOut, 2),
            'closing_quantity' => round(
                $opening + $quantityIn - $quantityOut,
                2
            ),
            'inward_value' => round(
                (float) ($movement->inward_value ?? 0),
                2
            ),
            'outward_value' => round(
                (float) ($movement->outward_value ?? 0),
                2
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Opening Quantity
    |--------------------------------------------------------------------------
    |
    | balance_qty is a snapshot. We select only the final snapshot for every
    | product/location before the selected start date and then sum it.
    |
    */

    private function openingQuantity(
        array $filters,
        int $companyId,
        int $storeId
    ): float {
        if (!$filters['from_date']) {
            return 0;
        }

        $candidate = $this->ledgerQuery($companyId, $storeId);
        $this->applyFilters(
            $candidate,
            $filters,
            false,
            false
        );

        $candidate->whereDate(
            'stock_ledgers.transaction_date',
            '<',
            $filters['from_date']
        );

        $latestIds = (clone $candidate)
            ->reorder()
            ->select([])
            ->selectRaw('MAX(stock_ledgers.id)')
            ->groupBy(
                'stock_ledgers.product_id',
                DB::raw('COALESCE(stock_ledgers.godown_id, 0)')
            );

        return (float) StockLedger::query()
            ->whereIn('id', $latestIds)
            ->sum('balance_qty');
    }

    /*
    |--------------------------------------------------------------------------
    | Format Ledger Row
    |--------------------------------------------------------------------------
    */

    private function ledgerRow(StockLedger $ledger): array
    {
        return [
            'id' => $ledger->id,
            'product_id' => $ledger->product_id,
            'product' => $ledger->product,
            'godown_id' => $ledger->godown_id,
            'godown' => $ledger->godown,
            'location_type' => $ledger->godown_id === null
                ? 'shop'
                : 'godown',
            'location_name' => $ledger->godown_id === null
                ? 'Shop'
                : ($ledger->godown?->godown_name ?? 'Godown'),
            'transaction_date' => $ledger->transaction_date
                ? date(
                    'Y-m-d',
                    strtotime((string) $ledger->transaction_date)
                )
                : null,
            'transaction_type' => $ledger->transaction_type,
            'reference_id' => $ledger->reference_id,
            'reference_no' => $ledger->reference_no,
            'qty_in' => round((float) $ledger->qty_in, 2),
            'qty_out' => round((float) $ledger->qty_out, 2),
            'balance_qty' => round((float) $ledger->balance_qty, 2),
            'unit_cost' => round((float) $ledger->unit_cost, 2),
            'total_cost' => round((float) $ledger->total_cost, 2),
            'remarks' => $ledger->remarks,
            'created_at' => $ledger->created_at?->toIso8601String(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Filters
    |--------------------------------------------------------------------------
    */

    private function validatedFilters(
        Request $request,
        bool $includePerPage = true
    ): array {
        $rules = [
            'search' => ['nullable', 'string', 'max:150'],
            'product_id' => [
                'nullable',
                'integer',
                'exists:products,id',
            ],
            'location' => [
                'nullable',
                Rule::in(['all', 'shop', 'godown']),
            ],
            'godown_id' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],
            'transaction_type' => [
                'nullable',
                'string',
                'max:30',
            ],
            'from_date' => ['nullable', 'date'],
            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],
        ];

        if ($includePerPage) {
            $rules['per_page'] = [
                'nullable',
                'integer',
                Rule::in([25, 50, 100, 200]),
            ];
        }

        $validated = $request->validate($rules);

        return [
            'search' => trim($validated['search'] ?? ''),
            'product_id' => isset($validated['product_id'])
                ? (int) $validated['product_id']
                : null,
            'location' => $validated['location'] ?? 'all',
            'godown_id' => isset($validated['godown_id'])
                ? (int) $validated['godown_id']
                : null,
            'transaction_type' => trim(
                $validated['transaction_type'] ?? ''
            ),
            'from_date' => $validated['from_date'] ?? null,
            'to_date' => $validated['to_date'] ?? null,
            'per_page' => $includePerPage
                ? (int) ($validated['per_page'] ?? 50)
                : 50,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Godown Context
    |--------------------------------------------------------------------------
    */

    private function validateGodown(
        ?int $godownId,
        int $companyId,
        int $storeId
    ): void {
        if (!$godownId) {
            return;
        }

        $valid = Godown::query()
            ->whereKey($godownId)
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->exists();

        abort_unless($valid, 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Ledger Context Guard
    |--------------------------------------------------------------------------
    */

    private function ensureLedgerContext(
        StockLedger $ledger,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $ledger->company_id === $companyId
                && (int) $ledger->store_id === $storeId,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Business Context
    |--------------------------------------------------------------------------
    */

    private function context(Request $request): array
    {
        $companyId = (int) $request->session()->get(
            'active_company_id'
        );
        $storeId = (int) $request->session()->get(
            'active_store_id'
        );

        abort_if(
            $companyId <= 0 || $storeId <= 0,
            403,
            'Please select an active company and branch.'
        );

        $storeExists = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->exists();

        abort_unless(
            $storeExists,
            403,
            'The selected branch is unavailable.'
        );

        return [$companyId, $storeId];
    }

    /*
    |--------------------------------------------------------------------------
    | Spreadsheet Formula Protection
    |--------------------------------------------------------------------------
    */

    private function safeSpreadsheetValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        if (
            $value !== ''
            && in_array($value[0], ['=', '+', '-', '@'], true)
        ) {
            return "'" . $value;
        }

        return $value;
    }
}
