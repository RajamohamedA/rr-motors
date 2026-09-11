<?php

namespace App\Http\Controllers;

use App\Models\Godown;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockLedger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class StockAdjustmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:stock-adjustment.view',
                only: ['index']
            ),
            new Middleware(
                'permission:stock-adjustment.create',
                only: [
                    'create',
                    'store',
                    'searchProduct',
                    'getProductStock',
                    'productStock',
                ]
            ),
            new Middleware(
                'permission:stock-adjustment.show',
                only: ['show']
            ),
            new Middleware(
                'permission:stock-adjustment.edit',
                only: ['edit', 'update']
            ),
            new Middleware(
                'permission:stock-adjustment.delete',
                only: ['destroy', 'cancel']
            ),
            new Middleware(
                'permission:stock-adjustment.print',
                only: ['print', 'pdf']
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['increase', 'decrease'])],
            'status' => [
                'nullable',
                Rule::in(['Completed', 'Cancelled']),
            ],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'per_page' => ['nullable', 'integer', Rule::in([25, 50, 100, 200])],
        ]);

        $query = StockAdjustment::query()
            ->forContext($companyId, $storeId)
            ->with([
                'store:id,store_name',
                'creator:id,name',
            ])
            ->withCount('items')
            ->withSum('items as total_quantity', 'qty')
            ->withSum('items as total_cost', 'total_cost');

        $this->applyIndexFilters($query, $filters);

        $summaryQuery = StockAdjustment::query()
            ->forContext($companyId, $storeId);

        $this->applyIndexFilters($summaryQuery, $filters, false);

        $summary = [
            'total_adjustments' => (clone $summaryQuery)->count(),
            'completed' => (clone $summaryQuery)
                ->where('status', 'Completed')
                ->count(),
            'cancelled' => (clone $summaryQuery)
                ->where('status', 'Cancelled')
                ->count(),
            'increase_quantity' => $this->summaryQuantity(
                clone $summaryQuery,
                'increase'
            ),
            'decrease_quantity' => $this->summaryQuantity(
                clone $summaryQuery,
                'decrease'
            ),
        ];

        $adjustments = $query
            ->latest('adjustment_date')
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        return Inertia::render('StockAdjustments/Index', [
            'adjustments' => $adjustments,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'type' => $filters['type'] ?? '',
                'status' => $filters['status'] ?? '',
                'from_date' => $filters['from_date'] ?? '',
                'to_date' => $filters['to_date'] ?? '',
                'per_page' => (int) ($filters['per_page'] ?? 25),
            ],
            'summary' => $summary,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $selectedProduct = null;

        if ($request->filled('product')) {
            $selectedProduct = Product::query()
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->where('status', true)
                ->find((int) $request->input('product'));
        }

        return Inertia::render('StockAdjustments/Create', [
            'adjustment_no' => $this->generateAdjustmentNo($storeId),
            'today' => now()->toDateString(),
            'godowns' => $this->godowns($companyId, $storeId),
            'selected_product' => $selectedProduct
                ? $this->productPayload($selectedProduct)
                : null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        [$companyId, $storeId] = $this->context($request);
        $validated = $this->validateAdjustment($request);

        $this->ensureUniqueItemLocations($validated['items']);

        $adjustment = DB::transaction(function () use (
            $validated,
            $companyId,
            $storeId
        ) {
            $adjustment = StockAdjustment::query()->create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'adjustment_date' => $validated['adjustment_date'],
                'adjustment_no' => $this->generateAdjustmentNo($storeId),
                'type' => $validated['type'],
                'remarks' => $this->nullableText(
                    $validated['remarks'] ?? null
                ),
                'status' => 'Completed',
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $index => $item) {
                $this->applyItem(
                    $adjustment,
                    $item,
                    $index,
                    $companyId,
                    $storeId,
                    $validated['type'],
                    'STOCK_ADJUSTMENT'
                );
            }

            return $adjustment;
        }, 3);

        return redirect()
            ->route('stock-adjustments.show', $adjustment)
            ->with('success', 'Stock adjustment created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        StockAdjustment $stockAdjustment
    ): Response {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($stockAdjustment, $companyId, $storeId);

        $stockAdjustment->load([
            'company:id,company_name',
            'store:id,store_name',
            'creator:id,name',
            'cancelledBy:id,name',
            'items.product:id,product_code,product_name,part_number,purchase_price',
            'items.godown:id,godown_name',
        ]);

        return Inertia::render('StockAdjustments/Show', [
            'adjustment' => $stockAdjustment,
            'totals' => [
                'quantity' => $stockAdjustment->total_quantity,
                'cost' => $stockAdjustment->total_cost,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        StockAdjustment $stockAdjustment
    ): Response {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($stockAdjustment, $companyId, $storeId);

        if (!$stockAdjustment->isCompleted()) {
            abort(422, 'A cancelled adjustment cannot be edited.');
        }

        $stockAdjustment->load([
            'items.product:id,product_code,product_name,part_number,purchase_price',
            'items.godown:id,godown_name',
        ]);

        return Inertia::render('StockAdjustments/Edit', [
            'adjustment' => $stockAdjustment,
            'godowns' => $this->godowns($companyId, $storeId),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        StockAdjustment $stockAdjustment
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($stockAdjustment, $companyId, $storeId);

        $validated = $this->validateAdjustment($request);
        $this->ensureUniqueItemLocations($validated['items']);

        DB::transaction(function () use (
            $stockAdjustment,
            $validated,
            $companyId,
            $storeId
        ) {
            $adjustment = StockAdjustment::query()
                ->forContext($companyId, $storeId)
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($stockAdjustment->id);

            if (!$adjustment->isCompleted()) {
                throw ValidationException::withMessages([
                    'adjustment' => 'A cancelled adjustment cannot be edited.',
                ]);
            }

            foreach ($adjustment->items as $index => $oldItem) {
                $this->reverseItem(
                    $adjustment,
                    $oldItem,
                    $index,
                    $companyId,
                    $storeId,
                    'STOCK_ADJUSTMENT_EDIT_REVERSAL'
                );
            }

            $adjustment->items()->delete();

            $adjustment->update([
                'adjustment_date' => $validated['adjustment_date'],
                'type' => $validated['type'],
                'remarks' => $this->nullableText(
                    $validated['remarks'] ?? null
                ),
            ]);

            foreach ($validated['items'] as $index => $item) {
                $this->applyItem(
                    $adjustment,
                    $item,
                    $index,
                    $companyId,
                    $storeId,
                    $validated['type'],
                    'STOCK_ADJUSTMENT_EDIT'
                );
            }
        }, 3);

        return redirect()
            ->route('stock-adjustments.show', $stockAdjustment)
            ->with('success', 'Stock adjustment updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel / Destroy
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        StockAdjustment $stockAdjustment
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($stockAdjustment, $companyId, $storeId);

        $validated = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use (
            $stockAdjustment,
            $validated,
            $companyId,
            $storeId
        ) {
            $adjustment = StockAdjustment::query()
                ->forContext($companyId, $storeId)
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($stockAdjustment->id);

            if (!$adjustment->canBeCancelled()) {
                throw ValidationException::withMessages([
                    'adjustment' => 'This adjustment is already cancelled.',
                ]);
            }

            foreach ($adjustment->items as $index => $item) {
                $this->reverseItem(
                    $adjustment,
                    $item,
                    $index,
                    $companyId,
                    $storeId,
                    'STOCK_ADJUSTMENT_CANCEL'
                );
            }

            $adjustment->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancel_reason' => $this->nullableText(
                    $validated['cancel_reason'] ?? null
                ) ?? 'Cancelled by user from Stock Adjustment screen.',
            ]);
        }, 3);

        return redirect()
            ->route('stock-adjustments.index')
            ->with('success', 'Stock adjustment cancelled successfully.');
    }

    public function destroy(
        Request $request,
        StockAdjustment $stockAdjustment
    ): RedirectResponse {
        return $this->cancel($request, $stockAdjustment);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    public function searchProduct(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'godown_id' => ['nullable', 'integer'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));
        $godownId = isset($validated['godown_id'])
            ? (int) $validated['godown_id']
            : null;

        $this->ensureGodown($godownId, $companyId, $storeId);

        if (mb_strlen($term) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->where(function (Builder $query) use ($term) {
                $like = '%' . $term . '%';

                $query
                    ->where('product_name', 'like', $like)
                    ->orWhere('product_code', 'like', $like)
                    ->orWhere('barcode', 'like', $like)
                    ->orWhere('part_number', 'like', $like);
            })
            ->orderBy('product_name')
            ->limit(20)
            ->get([
                'id',
                'product_code',
                'product_name',
                'part_number',
                'barcode',
                'purchase_price',
                'current_stock',
            ])
            ->map(function (Product $product) use (
                $companyId,
                $storeId,
                $godownId
            ) {
                $payload = $this->productPayload($product);
                $payload['stock'] = $this->currentStock(
                    $companyId,
                    $storeId,
                    $godownId,
                    $product->id
                );

                return $payload;
            });

        return response()->json(['products' => $products]);
    }

    /*
    |--------------------------------------------------------------------------
    | Current Product Stock
    |--------------------------------------------------------------------------
    */

    public function getProductStock(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'godown_id' => ['nullable', 'integer'],
        ]);

        $product = Product::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->findOrFail((int) $validated['product_id']);

        $godownId = isset($validated['godown_id'])
            ? (int) $validated['godown_id']
            : null;

        $this->ensureGodown($godownId, $companyId, $storeId);

        return response()->json([
            'success' => true,
            'stock' => $this->currentStock(
                $companyId,
                $storeId,
                $godownId,
                $product->id
            ),
            'unit_cost' => round((float) $product->purchase_price, 2),
            'location' => $godownId === null ? 'Shop' : 'Godown',
        ]);
    }

    public function productStock(
        Request $request,
        Product $product,
        int $company,
        int $store
    ): JsonResponse {
        [$companyId, $storeId] = $this->context($request);

        abort_unless(
            $company === $companyId && $store === $storeId,
            403
        );

        abort_unless(
            (int) $product->company_id === $companyId
                && (int) $product->store_id === $storeId,
            404
        );

        $godownId = $request->filled('godown_id')
            ? (int) $request->input('godown_id')
            : null;

        $this->ensureGodown($godownId, $companyId, $storeId);

        return response()->json([
            'stock' => $this->currentStock(
                $companyId,
                $storeId,
                $godownId,
                $product->id
            ),
            'unit_cost' => round((float) $product->purchase_price, 2),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Print / PDF
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request,
        StockAdjustment $stockAdjustment
    ): HttpResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($stockAdjustment, $companyId, $storeId);

        $stockAdjustment->load($this->printRelations());

        return response()->view(
            'stock_adjustments.print',
            ['adjustment' => $stockAdjustment]
        );
    }

    public function pdf(
        Request $request,
        StockAdjustment $stockAdjustment
    ): HttpResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureContext($stockAdjustment, $companyId, $storeId);

        $stockAdjustment->load($this->printRelations());

        $validated = $request->validate([
            'paper' => ['nullable', Rule::in(['a4', 'a5'])],
        ]);
        $paperSize = $validated['paper'] ?? 'a4';

        return Pdf::loadView(
            'stock_adjustments.pdf',
            [
                'adjustment' => $stockAdjustment,
                'paperSize' => $paperSize,
            ]
        )
            ->setPaper($paperSize, 'portrait')
            ->download($stockAdjustment->adjustment_no . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateAdjustment(Request $request): array
    {
        return $request->validate([
            'adjustment_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['increase', 'decrease'])],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'items.*.godown_id' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);
    }

    private function ensureUniqueItemLocations(array $items): void
    {
        $seen = [];

        foreach ($items as $index => $item) {
            $key = (int) $item['product_id']
                . ':'
                . ($item['godown_id'] ?? 'shop');

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" =>
                    'The same product and stock location is listed more than once.',
                ]);
            }

            $seen[$key] = true;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Apply / Reverse Stock
    |--------------------------------------------------------------------------
    */

    private function applyItem(
        StockAdjustment $adjustment,
        array $item,
        int $index,
        int $companyId,
        int $storeId,
        string $type,
        string $transactionType
    ): void {
        $product = Product::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->lockForUpdate()
            ->find((int) $item['product_id']);

        if (!$product) {
            throw ValidationException::withMessages([
                "items.{$index}.product_id" =>
                'The selected product is unavailable in this branch.',
            ]);
        }

        $godownId = filled($item['godown_id'] ?? null)
            ? (int) $item['godown_id']
            : null;

        $this->ensureGodown($godownId, $companyId, $storeId, $index);

        $quantity = round((float) $item['qty'], 2);
        $unitCost = round((float) $item['unit_cost'], 2);
        $before = $this->lockedCurrentStock(
            $companyId,
            $storeId,
            $godownId,
            $product->id
        );

        if ($type === 'decrease' && $quantity > $before) {
            throw ValidationException::withMessages([
                "items.{$index}.qty" =>
                'Quantity exceeds available stock of '
                    . $this->formatQuantity($before)
                    . '.',
            ]);
        }

        $after = round(
            $type === 'increase'
                ? $before + $quantity
                : $before - $quantity,
            2
        );
        $totalCost = round($quantity * $unitCost, 2);

        $adjustment->items()->create([
            'product_id' => $product->id,
            'godown_id' => $godownId,
            'qty' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'stock_before' => $before,
            'stock_after' => $after,
        ]);

        $this->createLedger(
            $adjustment,
            $product,
            $godownId,
            $type === 'increase' ? $quantity : 0,
            $type === 'decrease' ? $quantity : 0,
            $after,
            $unitCost,
            $totalCost,
            $transactionType
        );

        if ($godownId === null) {
            $product->update(['current_stock' => $after]);
        }
    }

    private function reverseItem(
        StockAdjustment $adjustment,
        mixed $item,
        int $index,
        int $companyId,
        int $storeId,
        string $transactionType
    ): void {
        $product = Product::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->lockForUpdate()
            ->findOrFail($item->product_id);

        $godownId = $item->godown_id !== null
            ? (int) $item->godown_id
            : null;
        $quantity = round((float) $item->qty, 2);
        $before = $this->lockedCurrentStock(
            $companyId,
            $storeId,
            $godownId,
            $product->id
        );

        $reverseType = $adjustment->type === 'increase'
            ? 'decrease'
            : 'increase';

        if ($reverseType === 'decrease' && $quantity > $before) {
            throw ValidationException::withMessages([
                'adjustment' =>
                'Cannot reverse item '
                    . ($index + 1)
                    . ' because only '
                    . $this->formatQuantity($before)
                    . ' is currently available.',
            ]);
        }

        $after = round(
            $reverseType === 'increase'
                ? $before + $quantity
                : $before - $quantity,
            2
        );
        $unitCost = round((float) $item->unit_cost, 2);

        $this->createLedger(
            $adjustment,
            $product,
            $godownId,
            $reverseType === 'increase' ? $quantity : 0,
            $reverseType === 'decrease' ? $quantity : 0,
            $after,
            $unitCost,
            round($quantity * $unitCost, 2),
            $transactionType
        );

        if ($godownId === null) {
            $product->update(['current_stock' => $after]);
        }
    }

    private function createLedger(
        StockAdjustment $adjustment,
        Product $product,
        ?int $godownId,
        float $qtyIn,
        float $qtyOut,
        float $balance,
        float $unitCost,
        float $totalCost,
        string $transactionType
    ): void {
        StockLedger::query()->create([
            'company_id' => $adjustment->company_id,
            'store_id' => $adjustment->store_id,
            'godown_id' => $godownId,
            'product_id' => $product->id,
            'transaction_date' => $adjustment->adjustment_date,
            'transaction_type' => $transactionType,
            'reference_id' => $adjustment->id,
            'reference_no' => $adjustment->adjustment_no,
            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'balance_qty' => $balance,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'remarks' => $adjustment->remarks,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Stock Helpers
    |--------------------------------------------------------------------------
    */

    private function currentStock(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): float {
        return round(
            (float) ($this->stockLocationQuery(
                $companyId,
                $storeId,
                $godownId,
                $productId
            )
                ->latest('id')
                ->value('balance_qty') ?? 0),
            2
        );
    }

    private function lockedCurrentStock(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): float {
        $ledger = $this->stockLocationQuery(
            $companyId,
            $storeId,
            $godownId,
            $productId
        )
            ->latest('id')
            ->lockForUpdate()
            ->first(['id', 'balance_qty']);

        return round((float) ($ledger?->balance_qty ?? 0), 2);
    }

    private function stockLocationQuery(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): Builder {
        return StockLedger::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->when(
                $godownId === null,
                fn(Builder $query) => $query->whereNull('godown_id'),
                fn(Builder $query) => $query->where(
                    'godown_id',
                    $godownId
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Context / Lookup Helpers
    |--------------------------------------------------------------------------
    */

    private function context(Request $request): array
    {
        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');

        abort_if(
            $companyId <= 0 || $storeId <= 0,
            403,
            'Please select an active company and branch.'
        );

        return [$companyId, $storeId];
    }

    private function ensureContext(
        StockAdjustment $adjustment,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $adjustment->company_id === $companyId
                && (int) $adjustment->store_id === $storeId,
            404
        );
    }

    private function ensureGodown(
        ?int $godownId,
        int $companyId,
        int $storeId,
        ?int $itemIndex = null
    ): void {
        if ($godownId === null) {
            return;
        }

        $exists = Godown::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->whereKey($godownId)
            ->exists();

        if (!$exists) {
            $key = $itemIndex === null
                ? 'godown_id'
                : "items.{$itemIndex}.godown_id";

            throw ValidationException::withMessages([
                $key => 'The selected Godown is unavailable in this branch.',
            ]);
        }
    }

    private function godowns(int $companyId, int $storeId): mixed
    {
        return Godown::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('godown_name')
            ->get(['id', 'godown_name']);
    }

    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->product_name,
            'part_number' => $product->part_number,
            'barcode' => $product->barcode,
            'purchase_price' => round(
                (float) $product->purchase_price,
                2
            ),
        ];
    }

    private function generateAdjustmentNo(int $storeId): string
    {
        do {
            $number = 'ADJ-'
                . $storeId
                . '-'
                . now()->format('YmdHis')
                . '-'
                . Str::upper(Str::random(4));
        } while (
            StockAdjustment::query()
            ->where('adjustment_no', $number)
            ->exists()
        );

        return $number;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function formatQuantity(float $quantity): string
    {
        return number_format($quantity, 2, '.', '');
    }

    /*
    |--------------------------------------------------------------------------
    | Index Helpers
    |--------------------------------------------------------------------------
    */

    private function applyIndexFilters(
        Builder $query,
        array $filters,
        bool $includeStatus = true
    ): void {
        $query
            ->when(
                filled($filters['search'] ?? null),
                function (Builder $query) use ($filters) {
                    $search = '%' . trim($filters['search']) . '%';

                    $query->where(function (Builder $query) use ($search) {
                        $query
                            ->where('adjustment_no', 'like', $search)
                            ->orWhere('remarks', 'like', $search)
                            ->orWhereHas(
                                'items.product',
                                fn(Builder $productQuery) => $productQuery
                                    ->where('product_name', 'like', $search)
                                    ->orWhere('product_code', 'like', $search)
                            );
                    });
                }
            )
            ->when(
                filled($filters['type'] ?? null),
                fn(Builder $query) => $query->where(
                    'type',
                    $filters['type']
                )
            )
            ->when(
                $includeStatus && filled($filters['status'] ?? null),
                fn(Builder $query) => $query->where(
                    'status',
                    $filters['status']
                )
            )
            ->when(
                filled($filters['from_date'] ?? null),
                fn(Builder $query) => $query->whereDate(
                    'adjustment_date',
                    '>=',
                    $filters['from_date']
                )
            )
            ->when(
                filled($filters['to_date'] ?? null),
                fn(Builder $query) => $query->whereDate(
                    'adjustment_date',
                    '<=',
                    $filters['to_date']
                )
            );
    }

    private function summaryQuantity(
        Builder $query,
        string $type
    ): float {
        return round(
            (float) DB::query()
                ->fromSub(
                    $query
                        ->where('status', 'Completed')
                        ->where('type', $type)
                        ->select('id'),
                    'filtered_adjustments'
                )
                ->join(
                    'stock_adjustment_items',
                    'stock_adjustment_items.stock_adjustment_id',
                    '=',
                    'filtered_adjustments.id'
                )
                ->sum('stock_adjustment_items.qty'),
            2
        );
    }

    private function printRelations(): array
    {
        return [
            'company',
            'store',
            'creator:id,name',
            'cancelledBy:id,name',
            'items.product',
            'items.godown',
        ];
    }
}
