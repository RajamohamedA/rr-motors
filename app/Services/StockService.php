<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use App\Models\Godown;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockService
{
    /**
     * Get the latest balance for one stock location.
     *
     * godown_id = NULL represents shop stock.
     */
    private function getCurrentBalance(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): float {
        return (float) (
            StockLedger::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->where(function ($query) use ($godownId) {
                if ($godownId === null) {
                    $query->whereNull('godown_id');
                } else {
                    $query->where('godown_id', $godownId);
                }
            })
            ->latest('id')
            ->value('balance_qty') ?? 0
        );
    }


    /**
     * Public stock lookup for controllers and AJAX requests.
     */
    public function getStock(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): float {
        return $this->getCurrentBalance(
            $companyId,
            $storeId,
            $godownId,
            $productId
        );
    }


    /**
     * Return the latest quantity and weighted-average cost for one location.
     *
     * Existing ledger rows created before the valuation upgrade have a NULL
     * average_cost. In that case the product purchase price is used only as
     * the opening fallback; every new movement stores a fresh snapshot.
     */
    public function getValuation(
        int $companyId,
        int $storeId,
        ?int $godownId,
        Product $product
    ): array {
        $latest = StockLedger::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('product_id', $product->id)
            ->where(function ($query) use ($godownId) {
                $godownId === null
                    ? $query->whereNull('godown_id')
                    : $query->where('godown_id', $godownId);
            })
            ->latest('id')
            ->first([
                'balance_qty',
                'average_cost',
                'balance_value',
            ]);

        $quantity = (float) ($latest?->balance_qty ?? 0);

        $averageCost = $latest?->average_cost !== null
            ? (float) $latest->average_cost
            : (float) ($product->purchase_price ?? 0);

        return [
            'quantity' => round($quantity, 3),
            'average_cost' => round($averageCost, 4),
            'balance_value' => round($quantity * $averageCost, 2),
            'cost_source' => $latest?->average_cost !== null
                ? 'weighted_average'
                : 'purchase_price_fallback',
        ];
    }


    /**
     * Lock the product row.
     *
     * All stock movements for the same product are serialized through this
     * lock, preventing simultaneous requests from using the same balance.
     */
    private function lockProduct(
        Product $product,
        int $companyId,
        int $storeId
    ): Product {
        $lockedProduct = Product::query()
            ->whereKey($product->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        if (
            isset($lockedProduct->company_id) &&
            (int) $lockedProduct->company_id !== $companyId
        ) {
            throw new RuntimeException(
                'The selected product does not belong to the active company.'
            );
        }

        if ((int) $lockedProduct->store_id !== $storeId || !Store::query()
            ->whereKey($storeId)->where('company_id', $companyId)->exists()) {
            throw new RuntimeException('The selected product does not belong to the active branch.');
        }

        return $lockedProduct;
    }


    private function validateGodown(int $companyId, int $storeId, ?int $godownId): void
    {
        if ($godownId !== null && !Godown::query()->whereKey($godownId)
            ->where('company_id', $companyId)->where('store_id', $storeId)->exists()) {
            throw new RuntimeException('The selected godown does not belong to the active branch.');
        }
    }

    /**
     * Create one stock-ledger entry.
     */
    private function createLedgerEntry(
        Product $product,
        int $companyId,
        int $storeId,
        ?int $godownId,
        string $transactionType,
        ?int $referenceId,
        ?string $referenceNo,
        float $qtyIn,
        float $qtyOut,
        float $balanceQty,
        float $unitCost,
        float $averageCost,
        float $balanceValue,
        string $remarks
    ): StockLedger {
        return StockLedger::forceCreate([
            'company_id' => $companyId,
            'store_id' => $storeId,
            'godown_id' => $godownId,
            'product_id' => $product->id,

            'transaction_date' => now(),
            'transaction_type' => $transactionType,

            'reference_id' => $referenceId,
            'reference_no' => $referenceNo,

            'qty_in' => $qtyIn,
            'qty_out' => $qtyOut,
            'balance_qty' => $balanceQty,

            'unit_cost' => $unitCost,
            'total_cost' => ($qtyIn + $qtyOut) * $unitCost,
            'average_cost' => $averageCost,
            'balance_value' => $balanceValue,

            'remarks' => $remarks,
        ]);
    }


    /**
     * Increase stock at a location.
     *
     * This also increases Product::current_stock because stock is entering
     * the business through purchase, sales return, opening stock, etc.
     */
    public function increaseStock(
        Product $product,
        float $qty,
        string $transactionType,
        int $companyId,
        int $storeId,
        ?int $godownId,
        ?int $referenceId,
        ?string $referenceNo,
        float $unitCost = 0,
        string $remarks = ''
    ): array {
        if ($qty <= 0) {
            throw new RuntimeException(
                'Stock increase quantity must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $product,
            $qty,
            $transactionType,
            $companyId,
            $storeId,
            $godownId,
            $referenceId,
            $referenceNo,
            $unitCost,
            $remarks
        ) {
            $lockedProduct = $this->lockProduct(
                $product,
                $companyId,
                $storeId
            );
            $this->validateGodown($companyId, $storeId, $godownId);

            $valuation = $this->getValuation(
                $companyId,
                $storeId,
                $godownId,
                $lockedProduct
            );

            $currentBalance = $valuation['quantity'];
            $newBalance = $currentBalance + $qty;

            $incomingCost = $unitCost > 0
                ? $unitCost
                : $valuation['average_cost'];
            $currentValue = $currentBalance * $valuation['average_cost'];
            $newValue = $currentValue + ($qty * $incomingCost);
            $newAverageCost = $newBalance > 0
                ? $newValue / $newBalance
                : 0;

            $this->createLedgerEntry(
                product: $lockedProduct,
                companyId: $companyId,
                storeId: $storeId,
                godownId: $godownId,
                transactionType: $transactionType,
                referenceId: $referenceId,
                referenceNo: $referenceNo,
                qtyIn: $qty,
                qtyOut: 0,
                balanceQty: $newBalance,
                unitCost: $incomingCost,
                averageCost: $newAverageCost,
                balanceValue: $newValue,
                remarks: $remarks
            );

            $lockedProduct->increment(
                'current_stock',
                $qty
            );

            return [
                'stock_before' => round($currentBalance, 3),
                'stock_after' => round($newBalance, 3),
                'unit_cost' => round($incomingCost, 4),
                'average_cost' => round($newAverageCost, 4),
                'balance_value' => round($newValue, 2),
                'cost_source' => 'weighted_average',
            ];
        });
    }


    /**
     * Decrease stock at a location.
     *
     * This also decreases Product::current_stock because stock is leaving
     * the business through sale, purchase return, damage, etc.
     */
    public function decreaseStock(
        Product $product,
        float $qty,
        string $transactionType,
        int $companyId,
        int $storeId,
        ?int $godownId,
        ?int $referenceId,
        ?string $referenceNo,
        float $unitCost = 0,
        string $remarks = ''
    ): array {
        if ($qty <= 0) {
            throw new RuntimeException(
                'Stock decrease quantity must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $product,
            $qty,
            $transactionType,
            $companyId,
            $storeId,
            $godownId,
            $referenceId,
            $referenceNo,
            $unitCost,
            $remarks
        ) {
            $lockedProduct = $this->lockProduct(
                $product,
                $companyId,
                $storeId
            );
            $this->validateGodown($companyId, $storeId, $godownId);

            $valuation = $this->getValuation(
                $companyId,
                $storeId,
                $godownId,
                $lockedProduct
            );

            $currentBalance = $valuation['quantity'];

            if ($qty > $currentBalance) {
                $locationName = $godownId === null
                    ? 'Shop'
                    : 'Godown ID ' . $godownId;

                throw new RuntimeException(
                    "Insufficient stock for {$lockedProduct->product_name} " .
                        "in {$locationName}. Available: {$currentBalance}, " .
                        "Required: {$qty}"
                );
            }

            $newBalance = $currentBalance - $qty;
            // Outgoing stock always uses its stored valuation. Never use a
            // selling price supplied by an older caller as inventory cost.
            $movementCost = max(0, $valuation['average_cost']);
            $newValue = max(0, $newBalance * $movementCost);

            $this->createLedgerEntry(
                product: $lockedProduct,
                companyId: $companyId,
                storeId: $storeId,
                godownId: $godownId,
                transactionType: $transactionType,
                referenceId: $referenceId,
                referenceNo: $referenceNo,
                qtyIn: 0,
                qtyOut: $qty,
                balanceQty: $newBalance,
                unitCost: $movementCost,
                averageCost: $movementCost,
                balanceValue: $newValue,
                remarks: $remarks
            );

            $lockedProduct->decrement(
                'current_stock',
                $qty
            );

            return [
                'stock_before' => round($currentBalance, 3),
                'stock_after' => round($newBalance, 3),
                'unit_cost' => round($movementCost, 4),
                'cost_total' => round($qty * $movementCost, 2),
                'average_cost' => round($movementCost, 4),
                'balance_value' => round($newValue, 2),
                'cost_source' => $valuation['cost_source'],
            ];
        });
    }


    /**
     * Transfer stock between two locations in the same branch.
     *
     * Examples:
     *
     * Shop to Godown:
     * fromGodownId = NULL
     * toGodownId   = godown ID
     *
     * Godown to Shop:
     * fromGodownId = godown ID
     * toGodownId   = NULL
     *
     * Godown to Godown:
     * fromGodownId = source godown ID
     * toGodownId   = destination godown ID
     *
     * Product::current_stock is not changed because the product remains
     * inside the same company and branch.
     */
    public function transferStock(
        Product $product,
        float $qty,
        int $companyId,
        int $storeId,
        ?int $fromGodownId,
        ?int $toGodownId,
        ?int $referenceId = null,
        ?string $referenceNo = null,
        float $unitCost = 0,
        string $remarks = '',
        string $transactionType = 'Stock Transfer'
    ): array {
        if ($qty <= 0) {
            throw new RuntimeException(
                'Transfer quantity must be greater than zero.'
            );
        }

        if ($fromGodownId === $toGodownId) {
            throw new RuntimeException(
                'Source and destination locations cannot be the same.'
            );
        }

        return DB::transaction(function () use (
            $product,
            $qty,
            $companyId,
            $storeId,
            $fromGodownId,
            $toGodownId,
            $referenceId,
            $referenceNo,
            $unitCost,
            $remarks,
            $transactionType
        ) {
            $lockedProduct = $this->lockProduct(
                $product,
                $companyId,
                $storeId
            );
            $this->validateGodown($companyId, $storeId, $fromGodownId);
            $this->validateGodown($companyId, $storeId, $toGodownId);

            /*
            |--------------------------------------------------------------------------
            | Read balances after obtaining the product lock
            |--------------------------------------------------------------------------
            */

            $sourceValuation = $this->getValuation(
                $companyId,
                $storeId,
                $fromGodownId,
                $lockedProduct
            );

            $destinationValuation = $this->getValuation(
                $companyId,
                $storeId,
                $toGodownId,
                $lockedProduct
            );

            $sourceBalance = $sourceValuation['quantity'];
            $destinationBalance = $destinationValuation['quantity'];

            if ($qty > $sourceBalance) {
                $sourceName = $fromGodownId === null
                    ? 'Shop'
                    : 'Godown ID ' . $fromGodownId;

                throw new RuntimeException(
                    "Insufficient stock in {$sourceName} for " .
                        "{$lockedProduct->product_name}. " .
                        "Available: {$sourceBalance}, Required: {$qty}"
                );
            }

            $sourceNewBalance = $sourceBalance - $qty;
            $destinationNewBalance = $destinationBalance + $qty;

            $movementCost = $sourceValuation['average_cost'] > 0
                ? $sourceValuation['average_cost']
                : max(0, $unitCost);

            $sourceNewValue = max(
                0,
                $sourceNewBalance * $movementCost
            );

            $destinationCurrentValue =
                $destinationBalance
                * $destinationValuation['average_cost'];

            $destinationNewValue =
                $destinationCurrentValue
                + ($qty * $movementCost);

            $destinationNewAverageCost = $destinationNewBalance > 0
                ? $destinationNewValue / $destinationNewBalance
                : 0;

            /*
            |--------------------------------------------------------------------------
            | Source ledger entry
            |--------------------------------------------------------------------------
            */

            $this->createLedgerEntry(
                product: $lockedProduct,
                companyId: $companyId,
                storeId: $storeId,
                godownId: $fromGodownId,
                transactionType: $transactionType,
                referenceId: $referenceId,
                referenceNo: $referenceNo,
                qtyIn: 0,
                qtyOut: $qty,
                balanceQty: $sourceNewBalance,
                unitCost: $movementCost,
                averageCost: $movementCost,
                balanceValue: $sourceNewValue,
                remarks: $remarks
            );

            /*
            |--------------------------------------------------------------------------
            | Destination ledger entry
            |--------------------------------------------------------------------------
            */

            $this->createLedgerEntry(
                product: $lockedProduct,
                companyId: $companyId,
                storeId: $storeId,
                godownId: $toGodownId,
                transactionType: $transactionType,
                referenceId: $referenceId,
                referenceNo: $referenceNo,
                qtyIn: $qty,
                qtyOut: 0,
                balanceQty: $destinationNewBalance,
                unitCost: $movementCost,
                averageCost: $destinationNewAverageCost,
                balanceValue: $destinationNewValue,
                remarks: $remarks
            );

            return [
                'from_stock_before' => $sourceBalance,
                'from_stock_after' => $sourceNewBalance,
                'to_stock_before' => $destinationBalance,
                'to_stock_after' => $destinationNewBalance,
                'unit_cost' => round($movementCost, 4),
                'cost_total' => round($qty * $movementCost, 2),
                'cost_source' => $sourceValuation['cost_source'],
            ];
        });
    }


    /**
     * Reverse a completed transfer.
     *
     * Cancellation moves stock from the original destination back to the
     * original source and creates separate reversing ledger entries.
     */
    public function reverseTransfer(
        Product $product,
        float $qty,
        int $companyId,
        int $storeId,
        ?int $originalFromGodownId,
        ?int $originalToGodownId,
        ?int $referenceId = null,
        ?string $referenceNo = null,
        float $unitCost = 0,
        string $remarks = ''
    ): array {
        return $this->transferStock(
            product: $product,
            qty: $qty,
            companyId: $companyId,
            storeId: $storeId,
            fromGodownId: $originalToGodownId,
            toGodownId: $originalFromGodownId,
            referenceId: $referenceId,
            referenceNo: $referenceNo,
            unitCost: $unitCost,
            remarks: $remarks,
            transactionType: 'Stock Transfer Cancellation'
        );
    }
}
