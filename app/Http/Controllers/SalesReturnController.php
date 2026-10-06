<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\CustomerLedgerEntry;
use App\Models\MechanicCommission;
use App\Models\MechanicLedgerEntry;
use App\Models\Product;
use App\Models\SaleItems;
use App\Models\SaleReturnItem;
use App\Models\Sales;
use App\Models\SalesReturn;
use App\Services\CustomerLedgerService;
use App\Services\MechanicLedgerService;
use App\Services\MechanicPaymentService;
use App\Services\StockService;
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
        [$companyId, $storeId] = $this->context($request);

        return Inertia::render('SalesReturns/Create', [
            'returnNo' => SalesReturn::generateReturnNo(),
            'accounts' => $this->refundAccounts($companyId, $storeId),
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
        StockService $stockService,
        CustomerLedgerService $customerLedgerService,
        MechanicLedgerService $mechanicLedgerService,
        MechanicPaymentService $mechanicPaymentService
    ): RedirectResponse {
        $validated = $this->validateReturnRequest($request);
        [$companyId, $storeId] = $this->context($request);

        $salesReturn = DB::transaction(function () use (
            $validated,
            $companyId,
            $storeId,
            $stockService,
            $customerLedgerService,
            $mechanicLedgerService,
            $mechanicPaymentService
        ) {
            $sale = Sales::query()
                ->with([
                    'items.product',
                    'mechanicCommission',
                ])
                ->lockForUpdate()
                ->findOrFail($validated['sale_id']);

            $this->ensureSaleContext($sale, $companyId, $storeId);
            $this->ensureSaleIsReturnable($sale);
            $lines = $this->buildLines($sale, $validated);
            $totals = $this->calculateTotals($lines);
            $this->ensureReturnAmountAvailable(
                $sale,
                $totals['total_amount']
            );

            $account = $this->refundAccount(
                $validated,
                $companyId,
                $storeId
            );

            $refundAmount = $validated['refund_status'] === 'refunded'
                ? $totals['total_amount']
                : 0;

            $return = SalesReturn::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'sale_id' => $sale->id,
                'return_no' => 'TMP-' . Str::uuid(),
                'return_date' => $validated['return_date'],
                'customer_id' => $sale->customer_id,
                'account_id' => $account?->id,
                'billing_type' => $sale->billing_type ?? 'gst',
                ...$totals,
                'refund_amount' => $refundAmount,
                'refund_method' => $validated['refund_method'],
                'refund_status' => $validated['refund_status'],
                'status' => 'completed',
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $return->update([
                'return_no' => 'SRT' . str_pad(
                    (string) $return->id,
                    6,
                    '0',
                    STR_PAD_LEFT
                ),
            ]);

            $this->saveLines($return, $lines, $stockService, 'CREATE');

            $customerLedgerService->postSalesReturn(
                $return,
                auth()->id()
            );

            $this->syncRefundAccounting(
                $return,
                $account,
                $refundAmount,
                $customerLedgerService
            );

            $this->postMechanicCommissionReturn(
                $return,
                $sale,
                $totals['sub_total'],
                $mechanicLedgerService
            );

            $this->refreshMechanicCommission(
                $sale,
                $mechanicPaymentService
            );

            $this->recalculateSaleBalance($sale);

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
            'sale.mechanic',
            'sale.mechanicCommission.completedSalesReturns',
            'customer',
            'account',
            'refundAccountTransaction',
            'items.product.unit',
            'items.saleItem',
        ]);

        $refundReversal = null;

        if ($salesReturn->refundAccountTransaction) {
            $refundReversal = AccountTransaction::query()
                ->where(
                    'reversal_of_id',
                    $salesReturn->refundAccountTransaction->id
                )
                ->first();
        }

        $ledgerEntries = CustomerLedgerEntry::query()
            ->forContext(
                (int) $salesReturn->company_id,
                (int) $salesReturn->store_id
            )
            ->where('customer_id', $salesReturn->customer_id)
            ->where('reference_id', $salesReturn->id)
            ->whereIn('reference_type', [
                'sales_return',
                'sales_return_refund',
            ])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $mechanicImpact = $this->mechanicImpact($salesReturn);

        return Inertia::render('SalesReturns/Show', [
            'salesReturn' => $salesReturn,
            'refundReversal' => $refundReversal,
            'ledgerEntries' => $ledgerEntries,
            'mechanicImpact' => $mechanicImpact,
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
            'accounts' => $this->refundAccounts(
                (int) $salesReturn->company_id,
                (int) $salesReturn->store_id
            ),
        ]);
    }

    public function update(
        Request $request,
        SalesReturn $salesReturn,
        StockService $stockService,
        CustomerLedgerService $customerLedgerService,
        MechanicLedgerService $mechanicLedgerService,
        MechanicPaymentService $mechanicPaymentService
    ): RedirectResponse {
        $validated = $this->validateReturnRequest($request);
        [$companyId, $storeId] = $this->context($request);
        $this->ensureReturnContext($request, $salesReturn);

        DB::transaction(function () use (
            $salesReturn,
            $validated,
            $companyId,
            $storeId,
            $stockService,
            $customerLedgerService,
            $mechanicLedgerService,
            $mechanicPaymentService
        ) {
            $return = SalesReturn::query()
                ->with([
                    'items.product',
                    'items.saleItem',
                    'sale.items.product',
                    'sale.mechanicCommission',
                ])
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

            $this->reverseMechanicCommissionReturn(
                $return,
                $mechanicLedgerService,
                'Reversal before editing sales return '
                    . $return->return_no
            );

            $this->reverseStock($return, $stockService, 'EDIT_REVERSE');
            $return->items()->delete();

            $lines = $this->buildLines(
                $return->sale,
                $validated,
                $return->id
            );
            $totals = $this->calculateTotals($lines);
            $this->ensureReturnAmountAvailable(
                $return->sale,
                $totals['total_amount'],
                $return->id
            );

            $account = $this->refundAccount(
                $validated,
                $companyId,
                $storeId
            );

            $refundAmount = $validated['refund_status'] === 'refunded'
                ? $totals['total_amount']
                : 0;

            $return->update([
                'return_date' => $validated['return_date'],
                'account_id' => $account?->id,
                ...$totals,
                'refund_amount' => $refundAmount,
                'refund_method' => $validated['refund_method'],
                'refund_status' => $validated['refund_status'],
                'remarks' => $validated['remarks'] ?? null,
            ]);

            $this->saveLines($return, $lines, $stockService, 'UPDATE');

            $customerLedgerService->postSalesReturn(
                $return->refresh(),
                auth()->id()
            );

            $this->syncRefundAccounting(
                $return,
                $account,
                $refundAmount,
                $customerLedgerService
            );

            $this->postMechanicCommissionReturn(
                $return,
                $return->sale,
                $totals['sub_total'],
                $mechanicLedgerService,
                $return->id
            );

            $this->refreshMechanicCommission(
                $return->sale,
                $mechanicPaymentService
            );

            $this->recalculateSaleBalance($return->sale);
        });

        return redirect()
            ->route('sales-returns.show', $salesReturn)
            ->with('success', 'Sales return updated successfully.');
    }

    public function cancel(
        Request $request,
        SalesReturn $salesReturn,
        StockService $stockService,
        CustomerLedgerService $customerLedgerService,
        MechanicLedgerService $mechanicLedgerService,
        MechanicPaymentService $mechanicPaymentService
    ): RedirectResponse {
        $this->ensureReturnContext($request, $salesReturn);

        DB::transaction(function () use (
            $salesReturn,
            $stockService,
            $customerLedgerService,
            $mechanicLedgerService,
            $mechanicPaymentService
        ) {
            $return = SalesReturn::query()
                ->with([
                    'items.product',
                    'items.saleItem',
                    'sale',
                ])
                ->lockForUpdate()
                ->findOrFail($salesReturn->id);

            if ($return->isCancelled()) {
                throw ValidationException::withMessages([
                    'sales_return' => 'This return is already cancelled.',
                ]);
            }

            $this->reverseStock($return, $stockService, 'CANCEL');

            $this->reverseRefundAccounting(
                $return,
                $customerLedgerService
            );

            $customerLedgerService->reverseSalesReturn(
                $return,
                now(),
                'Cancellation of sales return ' . $return->return_no,
                auth()->id()
            );

            $this->reverseMechanicCommissionReturn(
                $return,
                $mechanicLedgerService,
                'Cancellation of mechanic commission adjustment for '
                    . $return->return_no
            );

            $return->update([
                'status' => 'cancelled',
                'refund_status' => 'cancelled',
            ]);

            $this->refreshMechanicCommission(
                $return->sale,
                $mechanicPaymentService
            );

            $this->recalculateSaleBalance($return->sale);
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
        $paper = $request->validate([
            'paper' => ['nullable', Rule::in(['a4', 'a5'])],
        ])['paper'] ?? 'a4';

        $this->loadForDocument($salesReturn);

        $mechanicImpact = $this->mechanicImpact($salesReturn);

        return view(
            'sales_returns.print',
            compact('salesReturn', 'paper', 'mechanicImpact')
        );
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

        $mechanicImpact = $this->mechanicImpact($salesReturn);

        return Pdf::loadView(
            'sales_returns.pdf',
            compact('salesReturn', 'paper', 'mechanicImpact')
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
        $validated = $request->validate([
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'return_date' => ['required', 'date'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
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

        $moneyRefundMethods = [
            'cash',
            'bank',
            'original_payment',
        ];

        if (
            $validated['refund_status'] === 'refunded'
            && !in_array(
                $validated['refund_method'],
                $moneyRefundMethods,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'refund_method' =>
                'Select Cash, Bank or Original Payment for a completed refund.',
            ]);
        }

        if (
            in_array(
                $validated['refund_method'],
                $moneyRefundMethods,
                true
            )
            && $validated['refund_status'] === 'refunded'
            && empty($validated['account_id'])
        ) {
            throw ValidationException::withMessages([
                'account_id' =>
                'Select the cash or bank account used for the refund.',
            ]);
        }

        if (
            $validated['refund_method'] === 'customer_credit'
            && $validated['refund_status'] !== 'credited'
        ) {
            throw ValidationException::withMessages([
                'refund_status' =>
                'Customer Credit must use the Credited status.',
            ]);
        }

        return $validated;
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

    /*
    |--------------------------------------------------------------------------
    | Mechanic Commission Adjustment
    |--------------------------------------------------------------------------
    |
    | A sales return reduces the commission earned on the original sale.
    | The adjustment is posted as a debit in the mechanic ledger:
    |
    | - unpaid commission: reduces the mechanic payable;
    | - already-paid commission: creates a debit/advance balance recoverable
    |   from the mechanic or adjustable against a future commission.
    |
    */

    private function postMechanicCommissionReturn(
        SalesReturn $return,
        Sales $sale,
        float $returnedCommissionBase,
        MechanicLedgerService $mechanicLedgerService,
        ?int $excludeReturnId = null
    ): void {
        if (!$sale->isMechanicSale() || !$sale->mechanic_id) {
            return;
        }

        $commission = $sale->mechanicCommission;

        if (
            !$commission
            || $commission->isCancelled()
            || (float) $commission->commission_amount <= 0
        ) {
            return;
        }

        $amount = $this->mechanicReturnCommissionAmount(
            $return,
            $commission,
            $returnedCommissionBase,
            $excludeReturnId
        );

        if ($amount <= 0) {
            return;
        }

        $mechanicLedgerService->recordSalesReturnCommission(
            salesReturn: $return,
            mechanicId: (int) $sale->mechanic_id,
            amount: $amount,
            createdBy: auth()->id(),
            invoiceNo: $sale->invoice_no
        );
    }

    private function mechanicReturnCommissionAmount(
        SalesReturn $return,
        MechanicCommission $commission,
        float $returnedCommissionBase,
        ?int $excludeReturnId = null
    ): float {
        $commissionAmount = round(
            (float) $commission->commission_amount,
            2
        );

        $commissionBase = round(
            (float) $commission->commission_base,
            2
        );

        if ($commissionBase <= 0 || $commissionAmount <= 0) {
            return 0;
        }

        $previousReturnBase = (float) SalesReturn::query()
            ->where('company_id', $return->company_id)
            ->where('store_id', $return->store_id)
            ->where('sale_id', $return->sale_id)
            ->where('status', SalesReturn::STATUS_COMPLETED)
            ->when(
                $excludeReturnId ?? $return->id,
                fn(Builder $query) =>
                $query->where(
                    'id',
                    '!=',
                    $excludeReturnId ?? $return->id
                )
            )
            ->sum('sub_total');

        $previouslyReduced = round(
            $commissionAmount
                * min(1, max(0, $previousReturnBase) / $commissionBase),
            2
        );

        $remainingCommission = max(
            0,
            round($commissionAmount - $previouslyReduced, 2)
        );

        $currentReduction = round(
            $commissionAmount
                * min(
                    1,
                    max(0, $returnedCommissionBase) / $commissionBase
                ),
            2
        );

        return min($currentReduction, $remainingCommission);
    }

    private function reverseMechanicCommissionReturn(
        SalesReturn $return,
        MechanicLedgerService $mechanicLedgerService,
        string $remarks
    ): void {
        $mechanicLedgerService->reverseReference(
            companyId: (int) $return->company_id,
            storeId: (int) $return->store_id,
            referenceType: $return->getMorphClass(),
            referenceId: (int) $return->id,
            remarks: $remarks,
            createdBy: auth()->id(),
            transactionDate: now()->toDateString()
        );
    }

    private function refreshMechanicCommission(
        Sales $sale,
        MechanicPaymentService $mechanicPaymentService
    ): void {
        if (!$sale->isMechanicSale() || !$sale->mechanic_id) {
            return;
        }

        $commission = MechanicCommission::query()
            ->where('company_id', $sale->company_id)
            ->where('store_id', $sale->store_id)
            ->where('sale_id', $sale->id)
            ->lockForUpdate()
            ->first();

        if (!$commission || $commission->isCancelled()) {
            return;
        }

        $mechanicPaymentService->refreshCommissionPaymentSummary(
            $commission
        );
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
                $line['sale_item']->godown_id,
                $return->id,
                $return->return_no,
                (float) ($line['sale_item']->unit_cost ?? 0),
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
                $item->saleItem?->godown_id,
                $return->id,
                $return->return_no,
                (float) ($item->saleItem?->unit_cost ?? 0),
                'Reverse sales return ' . strtolower($action)
            );
        }
    }

    private function ensureReturnAmountAvailable(
        Sales $sale,
        float $newReturnAmount,
        ?int $excludeReturnId = null
    ): void {
        $previousReturnAmount = (float) SalesReturn::query()
            ->where('company_id', $sale->company_id)
            ->where('store_id', $sale->store_id)
            ->where('sale_id', $sale->id)
            ->where('status', 'completed')
            ->when(
                $excludeReturnId,
                fn(Builder $query) =>
                $query->where('id', '!=', $excludeReturnId)
            )
            ->sum('total_amount');

        if (
            round($previousReturnAmount + $newReturnAmount, 2)
            > round((float) $sale->grand_total, 2) + 0.009
        ) {
            throw ValidationException::withMessages([
                'items' =>
                'The total sales return amount cannot exceed the original invoice total.',
            ]);
        }
    }

    private function recalculateSaleBalance(Sales $sale): void
    {
        $sale = Sales::query()
            ->lockForUpdate()
            ->findOrFail($sale->id);

        $returnedAmount = (float) SalesReturn::query()
            ->where('company_id', $sale->company_id)
            ->where('store_id', $sale->store_id)
            ->where('sale_id', $sale->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $netInvoiceAmount = max(
            0,
            round((float) $sale->grand_total - $returnedAmount, 2)
        );

        $paidAmount = round((float) $sale->paid_amount, 2);
        $balanceAmount = max(
            0,
            round($netInvoiceAmount - $paidAmount, 2)
        );

        $paymentStatus = match (true) {
            $balanceAmount <= 0.009 => Sales::PAYMENT_PAID,
            $paidAmount > 0.009 => Sales::PAYMENT_PARTIAL,
            default => Sales::PAYMENT_UNPAID,
        };

        $sale->update([
            'balance_amount' => $balanceAmount,
            'payment_status' => $paymentStatus,
        ]);
    }

    private function refundAccounts(
        int $companyId,
        int $storeId
    ) {
        return Account::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get([
                'id',
                'account_code',
                'account_name',
                'account_type',
                'payment_mode',
                'bank_name',
            ]);
    }

    private function refundAccount(
        array $validated,
        int $companyId,
        int $storeId
    ): ?Account {
        if ($validated['refund_status'] !== 'refunded') {
            return null;
        }

        return Account::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->lockForUpdate()
            ->findOrFail((int) $validated['account_id']);
    }

    private function syncRefundAccounting(
        SalesReturn $return,
        ?Account $account,
        float $refundAmount,
        CustomerLedgerService $customerLedgerService
    ): void {
        $existing = AccountTransaction::query()
            ->where('company_id', $return->company_id)
            ->where('store_id', $return->store_id)
            ->where('reference_type', 'sales_return_refund')
            ->where('reference_id', $return->id)
            ->where('is_reversal', false)
            ->lockForUpdate()
            ->first();

        if ($refundAmount <= 0 || !$account) {
            if ($existing) {
                $wasReversed = AccountTransaction::query()
                    ->where('reversal_of_id', $existing->id)
                    ->exists();

                if ($wasReversed) {
                    throw ValidationException::withMessages([
                        'refund_status' =>
                        'A reversed refund transaction cannot be modified.',
                    ]);
                }

                $existing->delete();
            }

            $customerLedgerService->syncSalesReturnRefund(
                $return,
                0,
                auth()->id()
            );

            return;
        }

        $values = [
            'account_id' => $account->id,
            'transaction_date' => $return->return_date,
            'transaction_type' => 'sales_return_refund',
            'reference_no' => $return->return_no,
            'debit' => 0,
            'credit' => round($refundAmount, 2),
            'payment_mode' => $return->refund_method,
            'external_reference' => null,
            'remarks' => 'Refund paid for sales return ' . $return->return_no,
            'is_reversal' => false,
            'reversal_of_id' => null,
            'created_by' => auth()->id(),
        ];

        if ($existing) {
            $wasReversed = AccountTransaction::query()
                ->where('reversal_of_id', $existing->id)
                ->exists();

            if ($wasReversed) {
                throw ValidationException::withMessages([
                    'refund_status' =>
                    'A reversed refund transaction cannot be modified.',
                ]);
            }

            $existing->update($values);
        } else {
            AccountTransaction::create(array_merge(
                [
                    'company_id' => $return->company_id,
                    'store_id' => $return->store_id,
                    'reference_type' => 'sales_return_refund',
                    'reference_id' => $return->id,
                ],
                $values
            ));
        }

        $customerLedgerService->syncSalesReturnRefund(
            $return,
            $refundAmount,
            auth()->id()
        );
    }

    private function reverseRefundAccounting(
        SalesReturn $return,
        CustomerLedgerService $customerLedgerService
    ): void {
        $original = AccountTransaction::query()
            ->where('company_id', $return->company_id)
            ->where('store_id', $return->store_id)
            ->where('reference_type', 'sales_return_refund')
            ->where('reference_id', $return->id)
            ->where('is_reversal', false)
            ->lockForUpdate()
            ->first();

        if ($original) {
            $reversal = AccountTransaction::query()
                ->where('reversal_of_id', $original->id)
                ->lockForUpdate()
                ->first();

            if (!$reversal) {
                AccountTransaction::create([
                    'company_id' => $return->company_id,
                    'store_id' => $return->store_id,
                    'account_id' => $original->account_id,
                    'transaction_date' => now()->toDateString(),
                    'transaction_type' => 'reversal',
                    'reference_type' => 'sales_return_refund_reversal',
                    'reference_id' => $return->id,
                    'reference_no' => $return->return_no,
                    'debit' => $original->credit,
                    'credit' => $original->debit,
                    'payment_mode' => $original->payment_mode,
                    'external_reference' => $original->external_reference,
                    'remarks' =>
                    'Reversal of refund for ' . $return->return_no,
                    'is_reversal' => true,
                    'reversal_of_id' => $original->id,
                    'created_by' => auth()->id(),
                ]);
            }
        }

        $customerLedgerService->reverseSalesReturnRefund(
            $return,
            now(),
            'Reversal of refund for ' . $return->return_no,
            auth()->id()
        );
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
            'sale.mechanic',
            'sale.mechanicCommission.completedSalesReturns',
            'customer',
            'account',
            'refundAccountTransaction',
            'items.product.unit',
            'items.saleItem',
        ]);
    }

    private function mechanicImpact(
        SalesReturn $salesReturn
    ): ?array {
        $commission = $salesReturn->sale?->mechanicCommission;
        $mechanic = $salesReturn->sale?->mechanic;

        if (!$commission || !$mechanic) {
            return null;
        }

        $returnCommissionEntry = MechanicLedgerEntry::query()
            ->forContext(
                (int) $salesReturn->company_id,
                (int) $salesReturn->store_id
            )
            ->forMechanic((int) $mechanic->id)
            ->where(
                'transaction_type',
                MechanicLedgerEntry::TYPE_SALES_RETURN_COMMISSION
            )
            ->forReference(
                $salesReturn->getMorphClass(),
                (int) $salesReturn->id
            )
            ->first();

        $originalCommission = round(
            (float) $commission->commission_amount,
            2
        );
        $returnedCommission = $commission->returnedCommissionAmount();
        $effectiveCommission = $commission->effectiveCommissionAmount();
        $paidCommission = round((float) $commission->paid_amount, 2);
        $originalReturnDeduction = round(
            (float) ($returnCommissionEntry?->debit ?? 0),
            2
        );
        $reversedReturnDeduction = $returnCommissionEntry
            ? round(
                (float) $returnCommissionEntry
                    ->reversals()
                    ->sum('credit'),
                2
            )
            : 0;
        $netReturnDeduction = max(
            0,
            round(
                $originalReturnDeduction - $reversedReturnDeduction,
                2
            )
        );

        return [
            'mechanic' => [
                'id' => (int) $mechanic->id,
                'mechanic_code' => $mechanic->mechanic_code,
                'mechanic_name' => $mechanic->mechanic_name,
            ],
            'commission_status' => $commission->status,
            'original_commission' => $originalCommission,
            'this_return_original_deduction' => $originalReturnDeduction,
            'this_return_reversed' => $reversedReturnDeduction,
            'this_return_deduction' => $netReturnDeduction,
            'total_return_deduction' => $returnedCommission,
            'effective_commission' => $effectiveCommission,
            'paid_commission' => $paidCommission,
            'payable_balance' => max(
                0,
                round($effectiveCommission - $paidCommission, 2)
            ),
            'advance_amount' => $commission->excessPaidAdvanceAmount(),
            'ledger_entry_id' => $returnCommissionEntry?->id,
        ];
    }
}
