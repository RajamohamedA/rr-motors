<?php

namespace App\Http\Controllers;

use App\Models\Godown;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\StockService;
use App\Services\SupplierLedgerService;
use App\Support\AppResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PurchaseReturnController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:purchase-return.view',
                only: ['index', 'show', 'print', 'pdf']
            ),
            new Middleware(
                'permission:purchase-return.create',
                only: ['create', 'store']
            ),
            new Middleware(
                'permission:purchase-return.edit',
                only: ['edit', 'update']
            ),
            new Middleware(
                'permission:purchase-return.delete',
                only: ['destroy']
            ),
        ];
    }
    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'supplier_id' => $request->input('supplier_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
        $baseQuery = PurchaseReturn::query()
            ->where('company_id', $companyId)
            ->whereHas(
                'purchase',
                fn(Builder $query) =>
                $query->where('store_id', $storeId)
            );
        $returns = (clone $baseQuery)
            ->with([
                'purchase:id,purchase_no,store_id,tax_type',
                'supplier:id,supplier_name',
            ])
            ->when($filters['search'], function (Builder $query, string $search) {
                $query->where(function (Builder $nested) use ($search) {
                    $nested
                        ->where('return_no', 'like', "%{$search}%")
                        ->orWhereHas(
                            'supplier',
                            fn(Builder $supplierQuery) =>
                            $supplierQuery->where('supplier_name', 'like', "%{$search}%")
                        )
                        ->orWhereHas('purchase', function (Builder $purchaseQuery) use ($search) {
                            $purchaseQuery
                                ->where('purchase_no', 'like', "%{$search}%")
                                ->orWhere('invoice_no', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                $filters['supplier_id'],
                fn(Builder $query, $supplierId) =>
                $query->where('supplier_id', (int) $supplierId)
            )
            ->when(
                $filters['date_from'],
                fn(Builder $query, $date) =>
                $query->whereDate('return_date', '>=', $date)
            )
            ->when(
                $filters['date_to'],
                fn(Builder $query, $date) =>
                $query->whereDate('return_date', '<=', $date)
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();
        $summary = [
            'total' => (clone $baseQuery)->count(),
            'return_value' => (float) (clone $baseQuery)->sum('total_amount'),
            'gst_value' => (float) (clone $baseQuery)->sum('gst_total'),
        ];
        $suppliers = Supplier::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas(
                'stores',
                fn(Builder $query) =>
                $query->where('stores.id', $storeId)
            )
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_name']);
        return Inertia::render('PurchaseReturns/Index', [
            'returns' => $returns,
            'suppliers' => $suppliers,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }
    public function create(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);
        return Inertia::render('PurchaseReturns/Create', [
            'returnNo' => $this->previewReturnNumber(),
            'context' => [
                'company_id' => $companyId,
                'store_id' => $storeId,
            ],
        ]);
    }
    public function search(Request $request)
    {
        $this->authorizeLookup($request);
        $validated = $request->validate([
            'search' => ['required', 'string', 'max:100'],
        ]);
        [$companyId, $storeId] = $this->context($request);
        $search = trim($validated['search']);
        $purchases = Purchase::query()
            ->with(['supplier:id,supplier_name'])
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->where(function (Builder $query) use ($search) {
                $query
                    ->where('purchase_no', 'like', "%{$search}%")
                    ->orWhere('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas(
                        'supplier',
                        fn(Builder $supplierQuery) =>
                        $supplierQuery->where('supplier_name', 'like', "%{$search}%")
                    );
            })
            ->latest('id')
            ->limit(20)
            ->get([
                'id',
                'company_id',
                'store_id',
                'supplier_id',
                'purchase_no',
                'purchase_date',
                'invoice_no',
                'invoice_date',
                'tax_type',
                'grand_total',
            ]);
        return response()->json($purchases);
    }
    public function getInvoice(Request $request, $id)
    {
        $this->authorizeLookup($request);
        [$companyId, $storeId] = $this->context($request);
        $purchase = Purchase::query()
            ->with([
                'supplier:id,supplier_name',
                'items.product.unit',
                'items.godown',
            ])
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->findOrFail($id);
        foreach ($purchase->items as $item) {
            $returnedQty = $this->returnedQuantity(
                $purchase->id,
                $item->product_id,
                $item->godown_id
            );
            $item->setAttribute('returned_qty', $returnedQty);
            $item->setAttribute(
                'balance_qty',
                max(0, (float) $item->quantity - $returnedQty)
            );
            $item->setAttribute(
                'godown_name',
                $item->godown?->godown_name ?? 'Shop'
            );
            $item->setAttribute(
                'return_unit_price',
                $this->returnUnitPrice($item)
            );
        }
        return response()->json($purchase);
    }
    public function store(
        Request $request,
        StockService $stockService,
        SupplierLedgerService $supplierLedgerService
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $validated = $this->validateRequest($request);
        try {
            $return = DB::transaction(function () use (
                $validated,
                $companyId,
                $storeId,
                $stockService,
                $supplierLedgerService
            ) {
                $purchase = Purchase::query()
                    ->with(['items.product'])
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->findOrFail($validated['purchase_id']);
                $lines = $this->buildValidatedLines(
                    $purchase,
                    $validated,
                    null
                );
                if (count($lines) === 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Enter a return quantity for at least one product.',
                    ]);
                }
                $return = PurchaseReturn::create([
                    'company_id' => $companyId,
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $purchase->supplier_id,
                    'return_no' => $this->generateReturnNumber(),
                    'return_date' => $validated['return_date'],
                    'sub_total' => 0,
                    'gst_total' => 0,
                    'total_amount' => 0,
                    'remarks' => $validated['remarks'] ?? null,
                ]);
                $totals = $this->postReturnLines(
                    $return,
                    $purchase,
                    $lines,
                    $stockService
                );
                $return->update([
                    'sub_total' => $totals['sub_total'],
                    'gst_total' => $totals['gst_total'],
                    'total_amount' => $totals['total_amount'],
                ]);
                $supplierLedgerService
                    ->postPurchaseReturn(
                        $return->fresh('purchase'),
                        auth()->id()
                    );
                return $return;
            });
            return AppResponse::successRoute(
                'purchase-returns.show',
                'Purchase return created successfully.',
                $return
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            return AppResponse::error(
                $exception->getMessage() ?: 'Unable to create the purchase return.',
                true
            );
        }
    }
    public function show(
        Request $request,
        PurchaseReturn $purchaseReturn
    ): Response {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureReturnContext(
            $purchaseReturn,
            $companyId,
            $storeId
        );
        $purchaseReturn->load([
            'purchase.company',
            'purchase.store',
            'supplier',
            'items.product.unit',
            'items.godown',
        ]);
        return Inertia::render('PurchaseReturns/Show', [
            'purchaseReturn' => $purchaseReturn,
        ]);
    }
    public function edit(
        Request $request,
        PurchaseReturn $purchaseReturn
    ): Response {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureReturnContext(
            $purchaseReturn,
            $companyId,
            $storeId
        );
        $purchaseReturn->load([
            'supplier',
            'purchase.company',
            'purchase.store',
            'purchase.items.product.unit',
            'purchase.items.godown',
            'items.product.unit',
            'items.godown',
        ]);
        foreach ($purchaseReturn->items as $item) {
            $purchaseItem = $this->findPurchaseItem(
                $purchaseReturn->purchase,
                $item->product_id,
                $item->godown_id
            );
            if (!$purchaseItem) {
                continue;
            }
            $alreadyReturned = $this->returnedQuantity(
                $purchaseReturn->purchase_id,
                $item->product_id,
                $item->godown_id,
                $purchaseReturn->id
            );
            $item->setAttribute('purchase_item', [
                'quantity' => (float) $purchaseItem->quantity,
                'returned_qty' => $alreadyReturned,
                'current_return_qty' => (float) $item->quantity,
                'balance_qty' => max(
                    0,
                    (float) $purchaseItem->quantity - $alreadyReturned
                ),
                'return_unit_price' => $this->returnUnitPrice($purchaseItem),
            ]);
        }
        return Inertia::render('PurchaseReturns/Edit', [
            'purchaseReturn' => $purchaseReturn,
        ]);
    }
    public function update(
        Request $request,
        PurchaseReturn $purchaseReturn,
        StockService $stockService,
        SupplierLedgerService $supplierLedgerService
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureReturnContext(
            $purchaseReturn,
            $companyId,
            $storeId
        );
        $validated = $this->validateRequest($request, false);
        try {
            DB::transaction(function () use (
                $purchaseReturn,
                $validated,
                $companyId,
                $storeId,
                $stockService,
                $supplierLedgerService
            ) {
                $lockedReturn = PurchaseReturn::query()
                    ->with(['items', 'purchase.items.product'])
                    ->lockForUpdate()
                    ->findOrFail($purchaseReturn->id);
                $purchase = $lockedReturn->purchase;
                abort_unless(
                    (int) $purchase->company_id === $companyId
                        && (int) $purchase->store_id === $storeId,
                    404
                );
                $lines = $this->buildValidatedLines(
                    $purchase,
                    $validated,
                    $lockedReturn->id
                );
                if (count($lines) === 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Enter a return quantity for at least one product.',
                    ]);
                }
                foreach ($lockedReturn->items as $oldItem) {
                    $product = Product::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->findOrFail($oldItem->product_id);
                    $stockService->increaseStock(
                        $product,
                        (float) $oldItem->quantity,
                        'PURCHASE_RETURN_EDIT_REVERSE',
                        $companyId,
                        $storeId,
                        $oldItem->godown_id,
                        $lockedReturn->id,
                        $lockedReturn->return_no,
                        (float) $oldItem->purchase_price,
                        'Reverse old purchase return'
                    );
                }
                $lockedReturn->items()->delete();
                $totals = $this->postReturnLines(
                    $lockedReturn,
                    $purchase,
                    $lines,
                    $stockService
                );
                $lockedReturn->update([
                    'return_date' => $validated['return_date'],
                    'sub_total' => $totals['sub_total'],
                    'gst_total' => $totals['gst_total'],
                    'total_amount' => $totals['total_amount'],
                    'remarks' => $validated['remarks'] ?? null,
                ]);
                $supplierLedgerService
                    ->postPurchaseReturn(
                        $lockedReturn->fresh('purchase'),
                        auth()->id()
                    );
            });
            return AppResponse::successRoute(
                'purchase-returns.show',
                'Purchase return updated successfully.',
                $purchaseReturn
            );
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            return AppResponse::error(
                $exception->getMessage() ?: 'Unable to update the purchase return.',
                true
            );
        }
    }
    public function destroy(
        Request $request,
        PurchaseReturn $purchaseReturn,
        StockService $stockService,
        SupplierLedgerService $supplierLedgerService
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureReturnContext(
            $purchaseReturn,
            $companyId,
            $storeId
        );
        try {
            DB::transaction(function () use (
                $purchaseReturn,
                $companyId,
                $storeId,
                $stockService,
                $supplierLedgerService
            ) {
                $lockedReturn = PurchaseReturn::query()
                    ->with(['items', 'purchase'])
                    ->lockForUpdate()
                    ->findOrFail($purchaseReturn->id);
                abort_unless(
                    (int) $lockedReturn->purchase->company_id === $companyId
                        && (int) $lockedReturn->purchase->store_id === $storeId,
                    404
                );
                foreach ($lockedReturn->items as $item) {
                    $product = Product::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->findOrFail($item->product_id);
                    $stockService->increaseStock(
                        $product,
                        (float) $item->quantity,
                        'PURCHASE_RETURN_DELETE',
                        $companyId,
                        $storeId,
                        $item->godown_id,
                        $lockedReturn->id,
                        $lockedReturn->return_no,
                        (float) $item->purchase_price,
                        'Reverse deleted purchase return'
                    );
                }
                $supplierLedgerService
                    ->reversePurchaseReturn(
                        purchaseReturn: $lockedReturn,
                        reversalDate: now()->toDateString(),
                        remarks: 'Purchase return deleted - supplier ledger reversal',
                        createdBy: auth()->id()
                    );
                $lockedReturn->items()->delete();
                $lockedReturn->delete();
            });
            return AppResponse::successRoute(
                'purchase-returns.index',
                'Purchase return deleted successfully.'
            );
        } catch (Throwable $exception) {
            report($exception);
            return AppResponse::error(
                $exception->getMessage() ?: 'Unable to delete the purchase return.'
            );
        }
    }
    public function print(Request $request, $id)
    {
        [$companyId, $storeId] = $this->context($request);
        $purchaseReturn = PurchaseReturn::query()
            ->with([
                'purchase.company',
                'purchase.store',
                'supplier',
                'items.product.unit',
                'items.godown',
            ])
            ->where('company_id', $companyId)
            ->whereHas(
                'purchase',
                fn(Builder $query) =>
                $query->where('store_id', $storeId)
            )
            ->findOrFail($id);
        return view(
            'purchase_returns.print',
            compact('purchaseReturn')
        );
    }
    public function pdf(Request $request, $id)
    {
        [$companyId, $storeId] = $this->context($request);
        $purchaseReturn = PurchaseReturn::query()
            ->with([
                'purchase.company',
                'purchase.store',
                'supplier',
                'items.product.unit',
                'items.godown',
            ])
            ->where('company_id', $companyId)
            ->whereHas(
                'purchase',
                fn(Builder $query) =>
                $query->where('store_id', $storeId)
            )
            ->findOrFail($id);
        $pdf = Pdf::loadView(
            'purchase_returns.pdf',
            compact('purchaseReturn')
        )->setPaper('a4', 'landscape');
        return $pdf->download(
            'purchase-return-' . $purchaseReturn->return_no . '.pdf'
        );
    }
    private function validateRequest(
        Request $request,
        bool $requirePurchase = true
    ): array {
        $rules = [
            'return_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'exists:products,id'],
            'godown_id' => ['required', 'array'],
            'godown_id.*' => ['nullable', 'integer', 'exists:godowns,id'],
            'qty' => ['required', 'array'],
            'qty.*' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'array'],
            'price.*' => ['nullable', 'numeric', 'min:0'],
            'gst' => ['nullable', 'array'],
            'gst.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
        if ($requirePurchase) {
            $rules['purchase_id'] = [
                'required',
                'integer',
                'exists:purchases,id',
            ];
        }
        return $request->validate($rules);
    }
    private function buildValidatedLines(
        Purchase $purchase,
        array $validated,
        ?int $excludeReturnId
    ): array {
        $lines = [];
        foreach ($validated['product_id'] as $index => $productId) {
            $qty = (float) ($validated['qty'][$index] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $godownId = $validated['godown_id'][$index] ?? null;
            $godownId = filled($godownId) ? (int) $godownId : null;
            $purchaseItem = $this->findPurchaseItem(
                $purchase,
                (int) $productId,
                $godownId
            );
            if (!$purchaseItem) {
                throw ValidationException::withMessages([
                    "product_id.{$index}" =>
                    'This product/location does not belong to the selected purchase.',
                ]);
            }
            $product = Product::query()
                ->where('company_id', $purchase->company_id)
                ->where('store_id', $purchase->store_id)
                ->whereKey($productId)
                ->first();
            if (!$product) {
                throw ValidationException::withMessages([
                    "product_id.{$index}" =>
                    'This product is not available in the active branch.',
                ]);
            }
            if ($godownId !== null) {
                $godownExists = Godown::query()
                    ->where('company_id', $purchase->company_id)
                    ->where('store_id', $purchase->store_id)
                    ->whereKey($godownId)
                    ->exists();
                if (!$godownExists) {
                    throw ValidationException::withMessages([
                        "godown_id.{$index}" =>
                        'The selected godown does not belong to the active branch.',
                    ]);
                }
            }
            $alreadyReturned = $this->returnedQuantity(
                $purchase->id,
                (int) $productId,
                $godownId,
                $excludeReturnId
            );
            $availableQty = max(
                0,
                (float) $purchaseItem->quantity - $alreadyReturned
            );
            if ($qty > $availableQty) {
                throw ValidationException::withMessages([
                    "qty.{$index}" =>
                    'Return quantity exceeds the available balance. Available: '
                        . number_format($availableQty, 2),
                ]);
            }
            $unitPrice = $this->returnUnitPrice($purchaseItem);
            $gstPercent = $purchase->tax_type === 'gst'
                ? (float) $purchaseItem->gst_percent
                : 0;
            $basicAmount = round($qty * $unitPrice, 2);
            $gstAmount = round(
                $basicAmount * $gstPercent / 100,
                2
            );
            $lineTotal = round($basicAmount + $gstAmount, 2);
            $lines[] = [
                'index' => $index,
                'product' => $product,
                'product_id' => (int) $productId,
                'godown_id' => $godownId,
                'quantity' => $qty,
                'purchase_price' => $unitPrice,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'basic_amount' => $basicAmount,
                'line_total' => $lineTotal,
            ];
        }
        return $lines;
    }
    private function postReturnLines(
        PurchaseReturn $return,
        Purchase $purchase,
        array $lines,
        StockService $stockService
    ): array {
        $subTotal = 0;
        $gstTotal = 0;
        $totalAmount = 0;
        foreach ($lines as $line) {
            PurchaseReturnItem::create([
                'purchase_return_id' => $return->id,
                'product_id' => $line['product_id'],
                'godown_id' => $line['godown_id'],
                'quantity' => $line['quantity'],
                'purchase_price' => $line['purchase_price'],
                'gst_percent' => $line['gst_percent'],
                'gst_amount' => $line['gst_amount'],
                'line_total' => $line['line_total'],
            ]);
            $stockService->decreaseStock(
                $line['product'],
                $line['quantity'],
                'PURCHASE_RETURN',
                (int) $purchase->company_id,
                (int) $purchase->store_id,
                $line['godown_id'],
                $return->id,
                $return->return_no,
                $line['purchase_price'],
                'Purchase Return'
            );
            $subTotal += $line['basic_amount'];
            $gstTotal += $line['gst_amount'];
            $totalAmount += $line['line_total'];
        }
        return [
            'sub_total' => round($subTotal, 2),
            'gst_total' => round($gstTotal, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }
    private function returnUnitPrice(
        PurchaseItem $purchaseItem
    ): float {
        $quantity = (float) $purchaseItem->quantity;
        if ($quantity <= 0) {
            return 0;
        }
        if (array_key_exists(
            'taxable_amount',
            $purchaseItem->getAttributes()
        )) {
            return round(
                (float) $purchaseItem->taxable_amount / $quantity,
                4
            );
        }
        $grossAmount = $quantity * (float) $purchaseItem->purchase_price;
        $taxableAmount = max(
            0,
            $grossAmount - (float) $purchaseItem->discount
        );
        return round($taxableAmount / $quantity, 4);
    }
    private function findPurchaseItem(
        Purchase $purchase,
        int $productId,
        ?int $godownId
    ): ?PurchaseItem {
        return $purchase->items->first(
            function (PurchaseItem $item) use (
                $productId,
                $godownId
            ) {
                return (int) $item->product_id === $productId
                    && (
                        $godownId === null
                        ? $item->godown_id === null
                        : (int) $item->godown_id === $godownId
                    );
            }
        );
    }
    private function returnedQuantity(
        int $purchaseId,
        int $productId,
        ?int $godownId,
        ?int $excludeReturnId = null
    ): float {
        return (float) PurchaseReturnItem::query()
            ->where('product_id', $productId)
            ->when(
                $godownId === null,
                fn(Builder $query) => $query->whereNull('godown_id'),
                fn(Builder $query) => $query->where('godown_id', $godownId)
            )
            ->when(
                $excludeReturnId,
                fn(Builder $query) =>
                $query->where('purchase_return_id', '!=', $excludeReturnId)
            )
            ->whereHas(
                'purchaseReturn',
                fn(Builder $query) =>
                $query->where('purchase_id', $purchaseId)
            )
            ->sum('quantity');
    }
    private function ensureReturnContext(
        PurchaseReturn $purchaseReturn,
        int $companyId,
        int $storeId
    ): void {
        $belongsToContext = PurchaseReturn::query()
            ->whereKey($purchaseReturn->id)
            ->where('company_id', $companyId)
            ->whereHas(
                'purchase',
                fn(Builder $query) =>
                $query->where('store_id', $storeId)
            )
            ->exists();
        abort_unless($belongsToContext, 404);
    }
    private function authorizeLookup(
        Request $request
    ): void {
        $user = $request->user();
        abort_unless(
            $user
                && (
                    $user->can('purchase-return.view')
                    || $user->can('purchase-return.create')
                    || $user->can('purchase-return.edit')
                ),
            403
        );
    }
    private function context(
        Request $request
    ): array {
        $user = $request->user();
        abort_unless(
            $user && (bool) $user->status,
            403
        );
        $companyId = (int) session('active_company_id');
        $storeId = (int) session('active_store_id');
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
            $isCompanyAdmin = $user->hasRole('Company Admin');
            if (!$isCompanyAdmin) {
                $assigned = $user->stores()
                    ->where('stores.id', $storeId)
                    ->where('stores.status', true)
                    ->exists();
                abort_unless($assigned, 403);
            }
        }
        return [$companyId, $storeId];
    }
    private function generateReturnNumber(): string
    {
        $nextId = (int) (PurchaseReturn::query()->max('id') ?? 0) + 1;
        return 'PR' . str_pad(
            (string) $nextId,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
    private function previewReturnNumber(): string
    {
        return $this->generateReturnNumber();
    }
}
