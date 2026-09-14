<?php

namespace App\Services;

use App\Models\Product;
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
     * Lock the product row.
     *
     * All stock movements for the same product are serialized through this
     * lock, preventing simultaneous requests from using the same balance.
     */
    private function lockProduct(
        Product $product,
        int $companyId
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

        return $lockedProduct;
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
        string $remarks
    ): StockLedger {
        return StockLedger::create([
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
    ): void {
        if ($qty <= 0) {
            throw new RuntimeException(
                'Stock increase quantity must be greater than zero.'
            );
        }

        DB::transaction(function () use (
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
                $companyId
            );

            $currentBalance = $this->getCurrentBalance(
                $companyId,
                $storeId,
                $godownId,
                $lockedProduct->id
            );

            $newBalance = $currentBalance + $qty;

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
                unitCost: $unitCost,
                remarks: $remarks
            );

            $lockedProduct->increment(
                'current_stock',
                $qty
            );
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
    ): void {
        if ($qty <= 0) {
            throw new RuntimeException(
                'Stock decrease quantity must be greater than zero.'
            );
        }

        DB::transaction(function () use (
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
                $companyId
            );

            $currentBalance = $this->getCurrentBalance(
                $companyId,
                $storeId,
                $godownId,
                $lockedProduct->id
            );

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
                unitCost: $unitCost,
                remarks: $remarks
            );

            $lockedProduct->decrement(
                'current_stock',
                $qty
            );
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
                $companyId
            );

            /*
            |--------------------------------------------------------------------------
            | Read balances after obtaining the product lock
            |--------------------------------------------------------------------------
            */

            $sourceBalance = $this->getCurrentBalance(
                $companyId,
                $storeId,
                $fromGodownId,
                $lockedProduct->id
            );

            $destinationBalance = $this->getCurrentBalance(
                $companyId,
                $storeId,
                $toGodownId,
                $lockedProduct->id
            );

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
                unitCost: $unitCost,
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
                unitCost: $unitCost,
                remarks: $remarks
            );

            return [
                'from_stock_before' => $sourceBalance,
                'from_stock_after' => $sourceNewBalance,
                'to_stock_before' => $destinationBalance,
                'to_stock_after' => $destinationNewBalance,
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
