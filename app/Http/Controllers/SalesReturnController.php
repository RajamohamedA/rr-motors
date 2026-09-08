<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItems;
use App\Models\SaleReturnItem;
use App\Models\Sales;
use App\Models\SalesReturn;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SalesReturnController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:sales-return.view',
                only: ['index']
            ),
            new Middleware(
                'permission:sales-return.view|sales-return.create|sales-return.edit',
                only: ['searchInvoice', 'getInvoice']
            ),
            new Middleware(
                'permission:sales-return.show',
                only: ['show']
            ),
            new Middleware(
                'permission:sales-return.create',
                only: ['create', 'store']
            ),
            new Middleware(
                'permission:sales-return.edit',
                only: ['edit', 'update']
            ),
            new Middleware(
                'permission:sales-return.delete',
                only: ['cancel']
            ),
            new Middleware(
                'permission:sales-return.print',
                only: ['print', 'pdf']
            ),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'quick_filter' => $request->input('quick_filter', 'today'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'status' => $request->input('status', 'all'),
        ];

        $query = SalesReturn::query()
            ->forContext($companyId, $storeId)
            ->with([
                'sale:id,invoice_no',
                'customer:id,customer_name,mobile',
            ])
            ->when($filters['search'], function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('return_no', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn(Builder $sale) =>
                        $sale->where('invoice_no', 'like', "%{$search}%"))
                        ->orWhereHas('customer', fn(Builder $customer) =>
                        $customer
                            ->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] !== 'all', fn(Builder $query) =>
            $query->where('status', $filters['status']));

        $this->applyDateFilter($query, $filters);

        $returns = $query
            ->latest('return_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $todayAmount = SalesReturn::query()
            ->forContext($companyId, $storeId)
            ->completed()
            ->whereDate('return_date', today())
            ->sum('total_amount');

        return Inertia::render('SalesReturns/Index', [
            'returns' => $returns,
            'filters' => $filters,
            'todayAmount' => (float) $todayAmount,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->context($request);

        return Inertia::render('SalesReturns/Create', [
            'returnNo' => SalesReturn::generateReturnNo(),
        ]);
    }

    public function searchInvoice(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $search = trim($validated['search']);

        $sales = Sales::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', 'Completed')
            ->with('customer:id,customer_name,mobile,customer_code')
            ->where(function (Builder $query) use ($search) {
                $query
                    ->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn(Builder $customer) =>
                    $customer
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%"));
            })
            ->latest('invoice_date')
            ->limit(20)
            ->get([
                'id',
                'invoice_no',
                'invoice_date',
                'customer_id',
                'billing_type',
                'grand_total',
                'payment_status',
            ]);

        return response()->json(['sales' => $sales]);
    }

    public function getInvoice(
        Request $request,
        Sales $sale
    ): JsonResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureSaleContext($sale, $companyId, $storeId);
        $this->ensureSaleIsReturnable($sale);

        $sale->load([
            'customer:id,customer_name,mobile,customer_code,gst_number',
            'items.product.unit',
        ]);

        $sale->items->each(function (SaleItems $item) {
            $returned = SaleReturnItem::query()
                ->where('sale_item_id', $item->id)
                ->whereHas('salesReturn', fn(Builder $query) =>
                $query->where('status', 'completed'))
                ->selectRaw(
                    'COALESCE(SUM(quantity), 0) AS quantity,
                     COALESCE(SUM(free_quantity), 0) AS free_quantity'
                )
                ->first();

            $item->setAttribute('returned_qty', (float) $returned->quantity);
            $item->setAttribute(
                'returned_free_qty',
                (float) $returned->free_quantity
            );
            $item->setAttribute(
                'balance_qty',
                max(0, (float) $item->quantity - (float) $returned->quantity)
            );
            $item->setAttribute(
                'balance_free_qty',
                max(
                    0,
                    (float) $item->free_quantity
                        - (float) $returned->free_quantity
                )
            );
        });

        return response()->json(['sale' => $sale]);
    }

    public function store(
        Request $request,
        StockService $stockService
    ): RedirectResponse {
        $validated = $this->validateReturnRequest($request);
        [$companyId, $storeId] = $this->context($request);

        $salesReturn = DB::transaction(function () use (
            $validated,
            $companyId,
            $storeId,
            $stockService
        ) {
            $sale = Sales::query()
                ->with('items.product')
                ->lockForUpdate()
                ->findOrFail($validated['sale_id']);

            $this->ensureSaleContext($sale, $companyId, $storeId);
            $this->ensureSaleIsReturnable($sale);
            $lines = $this->buildLines($sale, $validated);
            $totals = $this->calculateTotals($lines);

            $return = SalesReturn::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'sale_id' => $sale->id,
                'return_no' => $this->nextReturnNo(),
                'return_date' => $validated['return_date'],
                'customer_id' => $sale->customer_id,
                'billing_type' => $sale->billing_type ?? 'gst',
                ...$totals,
                'refund_amount' => $totals['total_amount'],
                'refund_method' => $validated['refund_method'],
                'refund_status' => $validated['refund_status'],
                'status' => 'completed',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->saveLines($return, $lines, $stockService, 'CREATE');

            return $return;
        });

        return redirect()
            ->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return created successfully.');
    }

    public function show(
        Request $request,
        SalesReturn $salesReturn
    ): Response {
        $this->ensureReturnContext($request, $salesReturn);

        $salesReturn->load([
            'company',
            'store',
            'sale',
            'customer',
            'items.product.unit',
            'items.saleItem',
        ]);

        return Inertia::render('SalesReturns/Show', [
            'salesReturn' => $salesReturn,
        ]);
    }

    public function edit(
        Request $request,
        SalesReturn $salesReturn
    ): Response {
        $this->ensureReturnContext($request, $salesReturn);

        abort_if($salesReturn->isCancelled(), 422, 'Cancelled return cannot be edited.');

        $salesReturn->load([
            'sale.customer',
            'sale.items.product.unit',
            'items.product.unit',
        ]);

        return Inertia::render('SalesReturns/Edit', [
            'salesReturn' => $salesReturn,
        ]);
    }

    public function update(
        Request $request,
        SalesReturn $salesReturn,
        StockService $stockService
    ): RedirectResponse {
        $validated = $this->validateReturnRequest($request);
        [$companyId, $storeId] = $this->context($request);
        $this->ensureReturnContext($request, $salesReturn);

        DB::transaction(function () use (
            $salesReturn,
            $validated,
            $companyId,
            $storeId,
            $stockService
        ) {
            $return = SalesReturn::query()
                ->with(['items.product', 'sale.items.product'])
                ->lockForUpdate()
                ->findOrFail($salesReturn->id);

            if ($return->isCancelled()) {
                throw ValidationException::withMessages([
                    'sales_return' => 'Cancelled return cannot be edited.',
                ]);
            }

            if ((int) $validated['sale_id'] !== (int) $return->sale_id) {
                throw ValidationException::withMessages([
                    'sale_id' => 'The original invoice cannot be changed.',
                ]);
            }

            $this->reverseStock($return, $stockService, 'EDIT_REVERSE');
            $return->items()->delete();

            $lines = $this->buildLines(
                $return->sale,
                $validated,
                $return->id
            );
            $totals = $this->calculateTotals($lines);

            $return->update([
                'return_date' => $validated['return_date'],
                ...$totals,
                'refund_amount' => $totals['total_amount'],
                'refund_method' => $validated['refund_method'],
                'refund_status' => $validated['refund_status'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->saveLines($return, $lines, $stockService, 'UPDATE');
        });

        return redirect()
            ->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return updated successfully.');
    }

    public function cancel(
        Request $request,
        SalesReturn $salesReturn,
        StockService $stockService
    ): RedirectResponse {
        $this->ensureReturnContext($request, $salesReturn);

        DB::transaction(function () use ($salesReturn, $stockService) {
            $return = SalesReturn::query()
                ->with('items.product')
                ->lockForUpdate()
                ->findOrFail($salesReturn->id);

            if ($return->isCancelled()) {
                throw ValidationException::withMessages([
                    'sales_return' => 'This return is already cancelled.',
                ]);
            }

            $this->reverseStock($return, $stockService, 'CANCEL');
            $return->update([
                'status' => 'cancelled',
                'refund_status' => 'cancelled',
            ]);
        });

        return redirect()
            ->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return cancelled and stock reversed.');
    }

    public function print(
        Request $request,
        SalesReturn $salesReturn
    ) {
        $this->ensureReturnContext($request, $salesReturn);
        $this->loadForDocument($salesReturn);

        return view('sales_returns.print', compact('salesReturn'));
    }

    public function pdf(
        Request $request,
        SalesReturn $salesReturn
    ) {
        $this->ensureReturnContext($request, $salesReturn);
        $paper = $request->validate([
            'paper' => ['nullable', Rule::in(['a4', 'a5'])],
        ])['paper'] ?? 'a4';

        $this->loadForDocument($salesReturn);

        return Pdf::loadView(
            'sales_returns.pdf',
            compact('salesReturn', 'paper')
        )
            ->setPaper($paper, 'portrait')
            ->download(
                'sales-return-'
                    . $salesReturn->return_no
                    . '-'
                    . strtoupper($paper)
                    . '.pdf'
            );
    }

    private function validateReturnRequest(Request $request): array
    {
        return $request->validate([
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'return_date' => ['required', 'date'],
            'refund_method' => [
                'required',
                Rule::in(['cash', 'bank', 'original_payment', 'customer_credit', 'none']),
            ],
            'refund_status' => [
                'required',
                Rule::in(['pending', 'refunded', 'credited']),
            ],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'sale_item_id' => ['required', 'array', 'min:1'],
            'sale_item_id.*' => ['required', 'integer', 'exists:sale_items,id'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['nullable', 'numeric', 'min:0'],
            'free_qty' => ['nullable', 'array'],
            'free_qty.*' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function buildLines(
        Sales $sale,
        array $validated,
        ?int $excludeReturnId = null
    ): array {
        $lines = [];

        foreach ($validated['sale_item_id'] as $index => $saleItemId) {
            $quantity = round((float) ($validated['qty'][$index] ?? 0), 2);
            $freeQuantity = round((float) ($validated['free_qty'][$index] ?? 0), 2);

            if ($quantity <= 0 && $freeQuantity <= 0) {
                continue;
            }

            $saleItem = $sale->items->firstWhere('id', (int) $saleItemId);

            if (!$saleItem) {
                throw ValidationException::withMessages([
                    "sale_item_id.{$index}" => 'Item does not belong to this invoice.',
                ]);
            }

            $returned = SaleReturnItem::query()
                ->where('sale_item_id', $saleItem->id)
                ->when($excludeReturnId, fn(Builder $query) =>
                $query->where('sale_return_id', '!=', $excludeReturnId))
                ->whereHas('salesReturn', fn(Builder $query) =>
                $query->where('status', 'completed'))
                ->selectRaw(
                    'COALESCE(SUM(quantity), 0) AS quantity,
                     COALESCE(SUM(free_quantity), 0) AS free_quantity'
                )
                ->first();

            $quantityBalance = (float) $saleItem->quantity - (float) $returned->quantity;
            $freeBalance = (float) $saleItem->free_quantity - (float) $returned->free_quantity;

            if ($quantity > $quantityBalance || $freeQuantity > $freeBalance) {
                throw ValidationException::withMessages([
                    "qty.{$index}" => 'Return quantity exceeds the remaining invoice quantity.',
                ]);
            }

            $gross = round($quantity * (float) $saleItem->sale_price, 2);
            $discount = $this->returnDiscount($saleItem, $quantity, $gross);
            $taxable = round(max(0, $gross - $discount), 2);
            $gst = round($taxable * (float) $saleItem->gst_percent / 100, 2);

            $lines[] = [
                'sale_item' => $saleItem,
                'product' => $saleItem->product,
                'quantity' => $quantity,
                'free_quantity' => $freeQuantity,
                'sale_price' => (float) $saleItem->sale_price,
                'discount_type' => $saleItem->discount_type ?? 'fixed',
                'discount_value' => (float) ($saleItem->discount_value ?? 0),
                'discount_amount' => $discount,
                'taxable_amount' => $taxable,
                'gst_percent' => (float) $saleItem->gst_percent,
                'gst_amount' => $gst,
                'line_total' => round($taxable + $gst, 2),
            ];
        }

        if (!$lines) {
            throw ValidationException::withMessages([
                'items' => 'Enter a return quantity for at least one item.',
            ]);
        }

        return $lines;
    }

    private function returnDiscount(
        SaleItems $saleItem,
        float $quantity,
        float $gross
    ): float {
        if (($saleItem->discount_type ?? 'fixed') === 'percentage') {
            return round(
                min($gross, $gross * (float) $saleItem->discount_value / 100),
                2
            );
        }

        $soldQuantity = max(0.01, (float) $saleItem->quantity);

        return round(
            min($gross, (float) $saleItem->discount * $quantity / $soldQuantity),
            2
        );
    }

    private function calculateTotals(array $lines): array
    {
        $subTotal = round(array_sum(array_column($lines, 'taxable_amount')), 2);
        $discount = round(array_sum(array_column($lines, 'discount_amount')), 2);
        $gst = round(array_sum(array_column($lines, 'gst_amount')), 2);
        $unrounded = $subTotal + $gst;
        $total = round($unrounded);

        return [
            'sub_total' => $subTotal,
            'discount_total' => $discount,
            'gst_total' => $gst,
            'round_off' => round($total - $unrounded, 2),
            'total_amount' => round($total, 2),
        ];
    }

    private function saveLines(
        SalesReturn $return,
        array $lines,
        StockService $stockService,
        string $action
    ): void {
        foreach ($lines as $line) {
            SaleReturnItem::create([
                'sale_return_id' => $return->id,
                'sale_item_id' => $line['sale_item']->id,
                'product_id' => $line['product']->id,
                'quantity' => $line['quantity'],
                'free_quantity' => $line['free_quantity'],
                'sale_price' => $line['sale_price'],
                'discount_type' => $line['discount_type'],
                'discount_value' => $line['discount_value'],
                'discount_amount' => $line['discount_amount'],
                'taxable_amount' => $line['taxable_amount'],
                'gst_percent' => $line['gst_percent'],
                'gst_amount' => $line['gst_amount'],
                'line_total' => $line['line_total'],
            ]);

            $stockService->increaseStock(
                $line['product'],
                $line['quantity'] + $line['free_quantity'],
                'SALES_RETURN_' . $action,
                $return->company_id,
                $return->store_id,
                null,
                $return->id,
                $return->return_no,
                $line['sale_price'],
                'Sales return ' . strtolower($action)
            );
        }
    }

    private function reverseStock(
        SalesReturn $return,
        StockService $stockService,
        string $action
    ): void {
        foreach ($return->items as $item) {
            $product = Product::query()
                ->where('company_id', $return->company_id)
                ->where('store_id', $return->store_id)
                ->findOrFail($item->product_id);

            $stockService->decreaseStock(
                $product,
                $item->totalStockQuantity(),
                'SALES_RETURN_' . $action,
                $return->company_id,
                $return->store_id,
                null,
                $return->id,
                $return->return_no,
                (float) $item->sale_price,
                'Reverse sales return ' . strtolower($action)
            );
        }
    }

    private function nextReturnNo(): string
    {
        $lastId = (int) SalesReturn::query()
            ->lockForUpdate()
            ->max('id');

        return 'SRT' . str_pad((string) ($lastId + 1), 6, '0', STR_PAD_LEFT);
    }

    private function context(Request $request): array
    {
        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');

        abort_if(!$companyId || !$storeId, 403, 'Select an active company and branch.');

        return [$companyId, $storeId];
    }

    private function ensureSaleContext(
        Sales $sale,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $sale->company_id === $companyId
                && (int) $sale->store_id === $storeId,
            404
        );
    }

    private function ensureSaleIsReturnable(Sales $sale): void
    {
        if ($sale->status !== 'Completed') {
            throw ValidationException::withMessages([
                'sale_id' => 'Only completed sales invoices can be returned.',
            ]);
        }
    }

    private function ensureReturnContext(
        Request $request,
        SalesReturn $return
    ): void {
        [$companyId, $storeId] = $this->context($request);

        abort_unless(
            (int) $return->company_id === $companyId
                && (int) $return->store_id === $storeId,
            404
        );
    }

    private function applyDateFilter(Builder $query, array $filters): void
    {
        match ($filters['quick_filter']) {
            'today' => $query->whereDate('return_date', today()),
            'yesterday' => $query->whereDate('return_date', today()->subDay()),
            'this_week' => $query->whereBetween('return_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]),
            'this_month' => $query
                ->whereYear('return_date', now()->year)
                ->whereMonth('return_date', now()->month),
            'custom' => $query
                ->when($filters['date_from'], fn(Builder $query) =>
                $query->whereDate('return_date', '>=', $filters['date_from']))
                ->when($filters['date_to'], fn(Builder $query) =>
                $query->whereDate('return_date', '<=', $filters['date_to'])),
            default => null,
        };
    }

    private function loadForDocument(SalesReturn $return): void
    {
        $return->load([
            'company',
            'store',
            'sale',
            'customer',
            'items.product.unit',
            'items.saleItem',
        ]);
    }
}
