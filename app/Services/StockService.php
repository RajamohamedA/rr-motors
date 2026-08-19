<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLedger;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Get current stock for a specific location.
     *
     * godown_id = NULL means Shop stock.
     */
    private function getCurrentBalance(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): float {

        return (float) (
            StockLedger::where('company_id', $companyId)
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
     * Get current stock for a specific location.
     *
     * Public method for controllers / AJAX.
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
     * Increase stock.
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
            throw new \Exception(
                'Stock increase quantity must be greater than zero.'
            );
        }


        $currentBalance = $this->getCurrentBalance(
            $companyId,
            $storeId,
            $godownId,
            $product->id
        );


        $newBalance = $currentBalance + $qty;


        StockLedger::create([

            'company_id'       => $companyId,
            'store_id'         => $storeId,
            'godown_id'        => $godownId,
            'product_id'       => $product->id,

            'transaction_date' => now(),

            'transaction_type' => $transactionType,

            'reference_id'     => $referenceId,
            'reference_no'     => $referenceNo,

            'qty_in'           => $qty,
            'qty_out'          => 0,

            'balance_qty'      => $newBalance,

            'unit_cost'        => $unitCost,

            'total_cost'       => $qty * $unitCost,

            'remarks'          => $remarks,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Product Overall Stock
        |--------------------------------------------------------------------------
        */

        $product->increment(
            'current_stock',
            $qty
        );
    }


    /**
     * Decrease stock.
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
            throw new \Exception(
                'Stock decrease quantity must be greater than zero.'
            );
        }


        $currentBalance = $this->getCurrentBalance(
            $companyId,
            $storeId,
            $godownId,
            $product->id
        );


        if ($qty > $currentBalance) {

            $locationName =
                $godownId === null
                ? 'Shop'
                : 'Godown ID ' . $godownId;


            throw new \Exception(
                "Insufficient stock for {$product->product_name} in {$locationName}. " .
                    "Available: {$currentBalance}, Required: {$qty}"
            );
        }


        $newBalance = $currentBalance - $qty;


        StockLedger::create([

            'company_id'       => $companyId,
            'store_id'         => $storeId,
            'godown_id'        => $godownId,
            'product_id'       => $product->id,

            'transaction_date' => now(),

            'transaction_type' => $transactionType,

            'reference_id'     => $referenceId,
            'reference_no'     => $referenceNo,

            'qty_in'           => 0,
            'qty_out'          => $qty,

            'balance_qty'      => $newBalance,

            'unit_cost'        => $unitCost,

            'total_cost'       => $qty * $unitCost,

            'remarks'          => $remarks,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Product Overall Stock
        |--------------------------------------------------------------------------
        */

        $product->decrement(
            'current_stock',
            $qty
        );
    }


    /**
     * Transfer stock between two locations.
     *
     * Example:
     *
     * Godown → Shop
     *
     * Source godown_id = 1
     * Destination godown_id = NULL
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
        string $remarks = ''
    ): void {

        if ($qty <= 0) {
            throw new \Exception(
                'Transfer quantity must be greater than zero.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent Same Location Transfer
        |--------------------------------------------------------------------------
        */

        if ($fromGodownId === $toGodownId) {

            throw new \Exception(
                'Source and destination locations cannot be the same.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Source Stock
        |--------------------------------------------------------------------------
        */

        $sourceBalance = $this->getCurrentBalance(
            $companyId,
            $storeId,
            $fromGodownId,
            $product->id
        );


        if ($qty > $sourceBalance) {

            $sourceName =
                $fromGodownId === null
                ? 'Shop'
                : 'Godown ID ' . $fromGodownId;


            throw new \Exception(
                "Insufficient stock in {$sourceName} for {$product->product_name}. " .
                    "Available: {$sourceBalance}, Required: {$qty}"
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Perform Both Ledger Entries Atomically
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use (
            $product,
            $qty,
            $companyId,
            $storeId,
            $fromGodownId,
            $toGodownId,
            $referenceId,
            $referenceNo,
            $unitCost,
            $remarks
        ) {

            /*
            |--------------------------------------------------------------------------
            | Source → Decrease
            |--------------------------------------------------------------------------
            */

            $this->decreaseLocationOnly(
                $product,
                $qty,
                'Stock Transfer',
                $companyId,
                $storeId,
                $fromGodownId,
                $referenceId,
                $referenceNo,
                $unitCost,
                $remarks
            );


            /*
            |--------------------------------------------------------------------------
            | Destination → Increase
            |--------------------------------------------------------------------------
            */

            $this->increaseLocationOnly(
                $product,
                $qty,
                'Stock Transfer',
                $companyId,
                $storeId,
                $toGodownId,
                $referenceId,
                $referenceNo,
                $unitCost,
                $remarks
            );
        });
    }


    /**
     * Decrease stock location only.
     *
     * Used by transfer.
     *
     * Does NOT change Product::current_stock.
     */
    private function decreaseLocationOnly(
        Product $product,
        float $qty,
        string $transactionType,
        int $companyId,
        int $storeId,
        ?int $godownId,
        ?int $referenceId,
        ?string $referenceNo,
        float $unitCost,
        string $remarks
    ): void {

        $currentBalance = $this->getCurrentBalance(
            $companyId,
            $storeId,
            $godownId,
            $product->id
        );


        if ($qty > $currentBalance) {

            throw new \Exception(
                "Insufficient stock for {$product->product_name}. " .
                    "Available: {$currentBalance}, Required: {$qty}"
            );
        }


        $newBalance = $currentBalance - $qty;


        StockLedger::create([

            'company_id'       => $companyId,
            'store_id'         => $storeId,
            'godown_id'        => $godownId,
            'product_id'       => $product->id,

            'transaction_date' => now(),

            'transaction_type' => $transactionType,

            'reference_id'     => $referenceId,
            'reference_no'     => $referenceNo,

            'qty_in'           => 0,
            'qty_out'          => $qty,

            'balance_qty'      => $newBalance,

            'unit_cost'        => $unitCost,

            'total_cost'       => $qty * $unitCost,

            'remarks'          => $remarks,
        ]);
    }


    /**
     * Increase stock location only.
     *
     * Used by transfer.
     *
     * Does NOT change Product::current_stock.
     */
    private function increaseLocationOnly(
        Product $product,
        float $qty,
        string $transactionType,
        int $companyId,
        int $storeId,
        ?int $godownId,
        ?int $referenceId,
        ?string $referenceNo,
        float $unitCost,
        string $remarks
    ): void {

        $currentBalance = $this->getCurrentBalance(
            $companyId,
            $storeId,
            $godownId,
            $product->id
        );


        $newBalance = $currentBalance + $qty;


        StockLedger::create([

            'company_id'       => $companyId,
            'store_id'         => $storeId,
            'godown_id'        => $godownId,
            'product_id'       => $product->id,

            'transaction_date' => now(),

            'transaction_type' => $transactionType,

            'reference_id'     => $referenceId,
            'reference_no'     => $referenceNo,

            'qty_in'           => $qty,
            'qty_out'          => 0,

            'balance_qty'      => $newBalance,

            'unit_cost'        => $unitCost,

            'total_cost'       => $qty * $unitCost,

            'remarks'          => $remarks,
        ]);
    }
}
