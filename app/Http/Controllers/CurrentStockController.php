<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CurrentStockController extends Controller implements HasMiddleware
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
                'permission:current-stock.view',
                only: ['index']
            ),
            new Middleware(
                'permission:current-stock.print',
                only: ['print']
            ),
            new Middleware(
                'permission:current-stock.pdf',
                only: ['pdf']
            ),
            new Middleware(
                'permission:current-stock.excel',
                only: ['excel']
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Current Stock Register
    |--------------------------------------------------------------------------
    |
    | StockLedger::balance_qty is a running balance snapshot. Therefore, the
    | current quantity comes from the latest ledger row for each combination:
    |
    | company + store + product + location
    |
    | A NULL godown_id represents the Shop location.
    |
    */

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'location' => [
                'nullable',
                Rule::in(['all', 'shop', 'godown']),
            ],
            'godown_id' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],
            'stock_status' => [
                'nullable',
                Rule::in([
                    'all',
                    'in_stock',
                    'low_stock',
                    'out_of_stock',
                    'negative',
                ]),
            ],
            'per_page' => [
                'nullable',
                'integer',
                Rule::in([15, 25, 50, 100]),
            ],
        ]);

        $filters = [
            'search' => trim($filters['search'] ?? ''),
            'location' => $filters['location'] ?? 'all',
            'godown_id' => $filters['godown_id'] ?? null,
            'category_id' => $filters['category_id'] ?? null,
            'brand_id' => $filters['brand_id'] ?? null,
            'stock_status' => $filters['stock_status'] ?? 'all',
            'per_page' => (int) ($filters['per_page'] ?? 25),
        ];

        $this->validateGodownFilter(
            $filters['godown_id'],
            $companyId,
            $storeId
        );

        $latestLedgerIds = StockLedger::query()
            ->selectRaw('MAX(id)')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->groupBy(
                'product_id',
                DB::raw('COALESCE(godown_id, 0)')
            );

        $query = StockLedger::query()
            ->select('stock_ledgers.*')
            ->join(
                'products',
                'products.id',
                '=',
                'stock_ledgers.product_id'
            )
            ->whereIn('stock_ledgers.id', $latestLedgerIds)
            ->where('stock_ledgers.company_id', $companyId)
            ->where('stock_ledgers.store_id', $storeId)
            ->where('products.company_id', $companyId)
            ->where('products.store_id', $storeId)
            ->where('products.status', true)
            ->with([
                'product:id,company_id,store_id,category_id,brand_id,unit_id,product_code,barcode,product_name,part_number,manufacturer,rack_location,minimum_stock,purchase_price,selling_price,status',
                'product.category:id,name',
                'product.brand:id,brand_name',
                'product.unit:id,unit_name',
                'godown:id,company_id,store_id,godown_name,godown_code,status',
            ]);

        $this->applyFilters(
            $query,
            $filters,
            $companyId,
            $storeId
        );

        $summary = $this->summary(clone $query);

        $stocks = $query
            ->orderBy('products.product_name')
            ->orderByRaw('stock_ledgers.godown_id IS NOT NULL')
            ->orderBy('stock_ledgers.godown_id')
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(
                fn(StockLedger $ledger) => $this->stockRow($ledger)
            );

        $productFilter = Product::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true);

        $categories = Category::query()
            ->select(['id', 'name'])
            ->whereIn(
                'id',
                (clone $productFilter)
                    ->whereNotNull('category_id')
                    ->select('category_id')
                    ->distinct()
            )
            ->orderBy('name')
            ->get();

        $brands = Brand::query()
            ->select(['id', 'brand_name'])
            ->whereIn(
                'id',
                (clone $productFilter)
                    ->whereNotNull('brand_id')
                    ->select('brand_id')
                    ->distinct()
            )
            ->orderBy('brand_name')
            ->get();

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

        return Inertia::render('Inventory/CurrentStock/Index', [
            'stocks' => $stocks,
            'filters' => $filters,
            'summary' => $summary,
            'categories' => $categories,
            'brands' => $brands,
            'godowns' => $godowns,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Print Current Stock
    |--------------------------------------------------------------------------
    */

    public function print(Request $request): View
    {
        $data = $this->exportData($request);

        return view(
            'inventory.current-stock.print',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Download Current Stock PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(Request $request)
    {
        $data = $this->exportData($request);

        return Pdf::loadView(
            'inventory.current-stock.pdf',
            $data
        )
            ->setPaper('a4', 'landscape')
            ->download(
                'current-stock-'
                    . now()->format('Y-m-d-His')
                    . '.pdf'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Download Current Stock for Excel
    |--------------------------------------------------------------------------
    |
    | CSV is generated natively and opens directly in Microsoft Excel or
    | LibreOffice. This avoids requiring a separate spreadsheet package.
    |
    */

    public function excel(Request $request): StreamedResponse
    {
        $data = $this->exportData($request);
        $stocks = $data['stocks'];
        $summary = $data['summary'];
        $filters = $data['filters'];
        $store = $data['store'];
        $generatedAt = $data['generatedAt'];
        $fileName = 'current-stock-'
            . now()->format('Y-m-d-His')
            . '.csv';

        return response()->streamDownload(
            function () use (
                $stocks,
                $summary,
                $filters,
                $store,
                $generatedAt
            ) {
                $output = fopen('php://output', 'w');

                fwrite($output, "\xEF\xBB\xBF");

                fputcsv($output, [
                    'CURRENT STOCK REGISTER',
                ]);
                fputcsv($output, [
                    'Company',
                    $this->safeSpreadsheetValue(
                        $store->company?->company_name
                    ),
                ]);
                fputcsv($output, [
                    'Branch',
                    $this->safeSpreadsheetValue(
                        $store->store_name
                    ),
                ]);
                fputcsv($output, [
                    'Generated At',
                    $generatedAt->format('d-m-Y h:i A'),
                ]);
                fputcsv($output, [
                    'Location Filter',
                    match ($filters['location'] ?? 'all') {
                        'shop' => 'Shop only',
                        'godown' => 'Godowns only',
                        default => 'All locations',
                    },
                    'Stock Filter',
                    $this->stockStatusLabel(
                        $filters['stock_status'] ?? 'all'
                    ),
                    'Search',
                    $this->safeSpreadsheetValue(
                        $filters['search'] ?? ''
                    ),
                ]);
                fputcsv($output, []);
                fputcsv($output, [
                    'Products',
                    $summary['product_count'],
                    'Total Quantity',
                    $summary['total_quantity'],
                    'Stock Value',
                    $summary['stock_value'],
                    'Low Stock',
                    $summary['low_stock_count'],
                    'Out of Stock',
                    $summary['out_of_stock_count'],
                    'Negative Stock',
                    $summary['negative_stock_count'],
                ]);
                fputcsv($output, []);

                fputcsv($output, [
                    'S.No',
                    'Product Code',
                    'Product Name',
                    'Part Number',
                    'Category',
                    'Brand',
                    'Unit',
                    'Location Type',
                    'Location',
                    'Rack',
                    'Current Quantity',
                    'Minimum Stock',
                    'Stock Status',
                    'Unit Cost',
                    'Stock Value',
                    'Last Transaction Date',
                    'Last Transaction Type',
                    'Reference Number',
                ]);

                foreach ($stocks as $index => $stock) {
                    fputcsv($output, [
                        $index + 1,
                        $this->safeSpreadsheetValue(
                            $stock['product']?->product_code
                        ),
                        $this->safeSpreadsheetValue(
                            $stock['product']?->product_name
                        ),
                        $this->safeSpreadsheetValue(
                            $stock['product']?->part_number
                        ),
                        $this->safeSpreadsheetValue(
                            $stock['product']?->category?->name
                        ),
                        $this->safeSpreadsheetValue(
                            $stock['product']?->brand?->brand_name
                        ),
                        $this->safeSpreadsheetValue(
                            $stock['product']?->unit?->unit_name
                        ),
                        ucfirst($stock['location_type']),
                        $this->safeSpreadsheetValue(
                            $stock['location_name']
                        ),
                        $this->safeSpreadsheetValue(
                            $stock['product']?->rack_location
                        ),
                        $stock['quantity'],
                        $stock['minimum_stock'],
                        $this->stockStatusLabel(
                            $stock['stock_status']
                        ),
                        $stock['unit_cost'],
                        $stock['stock_value'],
                        $stock['last_transaction_date'],
                        $this->safeSpreadsheetValue(
                            $stock['last_transaction_type']
                        ),
                        $this->safeSpreadsheetValue(
                            $stock['reference_no']
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

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'location' => [
                'nullable',
                Rule::in(['all', 'shop', 'godown']),
            ],
            'godown_id' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],
            'stock_status' => [
                'nullable',
                Rule::in([
                    'all',
                    'in_stock',
                    'low_stock',
                    'out_of_stock',
                    'negative',
                ]),
            ],
        ]);

        $filters = [
            'search' => trim($validated['search'] ?? ''),
            'location' => $validated['location'] ?? 'all',
            'godown_id' => $validated['godown_id'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'stock_status' => $validated['stock_status'] ?? 'all',
        ];

        $this->validateGodownFilter(
            $filters['godown_id'],
            $companyId,
            $storeId
        );

        $latestLedgerIds = StockLedger::query()
            ->selectRaw('MAX(id)')
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->groupBy(
                'product_id',
                DB::raw('COALESCE(godown_id, 0)')
            );

        $query = StockLedger::query()
            ->select('stock_ledgers.*')
            ->join(
                'products',
                'products.id',
                '=',
                'stock_ledgers.product_id'
            )
            ->whereIn('stock_ledgers.id', $latestLedgerIds)
            ->where('stock_ledgers.company_id', $companyId)
            ->where('stock_ledgers.store_id', $storeId)
            ->where('products.company_id', $companyId)
            ->where('products.store_id', $storeId)
            ->where('products.status', true)
            ->with([
                'product:id,company_id,store_id,category_id,brand_id,unit_id,product_code,barcode,product_name,part_number,manufacturer,rack_location,minimum_stock,purchase_price,selling_price,status',
                'product.category:id,name',
                'product.brand:id,brand_name',
                'product.unit:id,unit_name',
                'godown:id,company_id,store_id,godown_name,godown_code,status',
            ]);

        $this->applyFilters(
            $query,
            $filters,
            $companyId,
            $storeId
        );

        $summary = $this->summary(clone $query);

        $stocks = $query
            ->orderBy('products.product_name')
            ->orderByRaw('stock_ledgers.godown_id IS NOT NULL')
            ->orderBy('stock_ledgers.godown_id')
            ->get()
            ->map(
                fn(StockLedger $ledger) => $this->stockRow($ledger)
            );

        $store = Store::query()
            ->with('company')
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        return [
            'stocks' => $stocks,
            'summary' => $summary,
            'filters' => $filters,
            'store' => $store,
            'generatedAt' => now(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Register Filters
    |--------------------------------------------------------------------------
    */

    private function applyFilters(
        Builder $query,
        array $filters,
        int $companyId,
        int $storeId
    ): void {
        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';

            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where('products.product_name', 'like', $search)
                    ->orWhere('products.product_code', 'like', $search)
                    ->orWhere('products.barcode', 'like', $search)
                    ->orWhere('products.part_number', 'like', $search)
                    ->orWhere('products.manufacturer', 'like', $search)
                    ->orWhere('products.rack_location', 'like', $search)
                    ->orWhere(
                        'stock_ledgers.reference_no',
                        'like',
                        $search
                    );
            });
        }

        match ($filters['location']) {
            'shop' => $query->whereNull('stock_ledgers.godown_id'),
            'godown' => $query->whereNotNull('stock_ledgers.godown_id'),
            default => null,
        };

        if ($filters['godown_id']) {
            $query->where(
                'stock_ledgers.godown_id',
                (int) $filters['godown_id']
            );
        }

        if ($filters['category_id']) {
            $query->where(
                'products.category_id',
                (int) $filters['category_id']
            );
        }

        if ($filters['brand_id']) {
            $query->where(
                'products.brand_id',
                (int) $filters['brand_id']
            );
        }

        match ($filters['stock_status']) {
            'in_stock' => $query->whereColumn(
                'stock_ledgers.balance_qty',
                '>',
                'products.minimum_stock'
            ),
            'low_stock' => $query
                ->where('stock_ledgers.balance_qty', '>', 0)
                ->whereColumn(
                    'stock_ledgers.balance_qty',
                    '<=',
                    'products.minimum_stock'
                ),
            'out_of_stock' => $query->where(
                'stock_ledgers.balance_qty',
                0
            ),
            'negative' => $query->where(
                'stock_ledgers.balance_qty',
                '<',
                0
            ),
            default => null,
        };

        $query
            ->where('stock_ledgers.company_id', $companyId)
            ->where('stock_ledgers.store_id', $storeId);
    }

    /*
    |--------------------------------------------------------------------------
    | Current Stock Summary
    |--------------------------------------------------------------------------
    */

    private function summary(Builder $query): array
    {
        $summary = $query
            ->reorder()
            /*
            |--------------------------------------------------------------
            | Remove The Register Row Selection
            |--------------------------------------------------------------
            |
            | The main register query selects stock_ledgers.*. The summary
            | query is cloned from it, so we must clear those columns before
            | adding aggregate expressions. Otherwise MySQL's
            | ONLY_FULL_GROUP_BY mode rejects the query.
            |
            */
            ->select([])
            ->selectRaw(
                'COUNT(*) AS location_count,
                 COUNT(DISTINCT stock_ledgers.product_id) AS product_count,
                 COALESCE(SUM(stock_ledgers.balance_qty), 0) AS total_quantity,
                 COALESCE(SUM(
                    GREATEST(stock_ledgers.balance_qty, 0)
                    * COALESCE(
                        NULLIF(stock_ledgers.unit_cost, 0),
                        products.purchase_price,
                        0
                    )
                 ), 0) AS stock_value,
                 SUM(
                    CASE
                        WHEN stock_ledgers.balance_qty > 0
                         AND stock_ledgers.balance_qty <= products.minimum_stock
                        THEN 1 ELSE 0
                    END
                 ) AS low_stock_count,
                 SUM(
                    CASE
                        WHEN stock_ledgers.balance_qty = 0
                        THEN 1 ELSE 0
                    END
                 ) AS out_of_stock_count,
                 SUM(
                    CASE
                        WHEN stock_ledgers.balance_qty < 0
                        THEN 1 ELSE 0
                    END
                 ) AS negative_stock_count'
            )
            ->first();

        return [
            'location_count' => (int) ($summary->location_count ?? 0),
            'product_count' => (int) ($summary->product_count ?? 0),
            'total_quantity' => round(
                (float) ($summary->total_quantity ?? 0),
                2
            ),
            'stock_value' => round(
                (float) ($summary->stock_value ?? 0),
                2
            ),
            'low_stock_count' => (int) ($summary->low_stock_count ?? 0),
            'out_of_stock_count' => (int) ($summary->out_of_stock_count ?? 0),
            'negative_stock_count' => (int) ($summary->negative_stock_count ?? 0),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Format Current Stock Row
    |--------------------------------------------------------------------------
    */

    private function stockRow(StockLedger $ledger): array
    {
        $quantity = (float) $ledger->balance_qty;
        $minimumStock = (float) ($ledger->product?->minimum_stock ?? 0);
        $unitCost = (float) $ledger->unit_cost;

        if ($unitCost <= 0) {
            $unitCost = (float) ($ledger->product?->purchase_price ?? 0);
        }

        return [
            'ledger_id' => $ledger->id,
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
            'quantity' => round($quantity, 2),
            'minimum_stock' => round($minimumStock, 2),
            'unit_cost' => round($unitCost, 2),
            'stock_value' => round(
                max(0, $quantity) * $unitCost,
                2
            ),
            'stock_status' => $this->stockStatus(
                $quantity,
                $minimumStock
            ),
            'last_transaction_date' => $ledger->transaction_date
                ?->toDateString(),
            'last_transaction_type' => $ledger->transaction_type,
            'reference_no' => $ledger->reference_no,
        ];
    }

    private function stockStatus(
        float $quantity,
        float $minimumStock
    ): string {
        if ($quantity < 0) {
            return 'negative';
        }

        if ($quantity == 0) {
            return 'out_of_stock';
        }

        if ($quantity <= $minimumStock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    private function stockStatusLabel(string $status): string
    {
        return match ($status) {
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
            'negative' => 'Negative Stock',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Protect Spreadsheet Cells
    |--------------------------------------------------------------------------
    |
    | Values beginning with =, +, - or @ can be interpreted as spreadsheet
    | formulas. Prefixing them with an apostrophe keeps exports safe.
    |
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

    /*
    |--------------------------------------------------------------------------
    | Validate Godown Filter Context
    |--------------------------------------------------------------------------
    */

    private function validateGodownFilter(
        ?int $godownId,
        int $companyId,
        int $storeId
    ): void {
        if (!$godownId) {
            return;
        }

        $validGodown = Godown::query()
            ->whereKey($godownId)
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->exists();

        abort_unless($validGodown, 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Active Business Context
    |--------------------------------------------------------------------------
    */

    private function context(Request $request): array
    {
        $user = $request->user();

        abort_unless(
            $user && (bool) $user->status,
            403
        );

        $companyId = (int) $request->session()->get(
            'active_company_id'
        );
        $storeId = (int) $request->session()->get(
            'active_store_id'
        );

        abort_unless(
            $companyId > 0 && $storeId > 0,
            403,
            'No active business branch selected.'
        );

        $store = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->first();

        abort_unless(
            $store,
            403,
            'The active branch is unavailable.'
        );

        if (!$user->is_super_user) {
            abort_unless(
                (int) $user->company_id === $companyId,
                403
            );

            if (!$user->hasRole('Company Admin')) {
                $assigned = $user->stores()
                    ->where('stores.id', $storeId)
                    ->where('stores.status', true)
                    ->exists();

                abort_unless($assigned, 403);
            }
        }

        return [$companyId, $storeId];
    }
}
