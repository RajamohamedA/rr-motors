<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLedger;

class StockService
{
    /**
     * Get current stock for a specific location.
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

        $product->increment('current_stock', $qty);
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


        /*
        |--------------------------------------------------------------------------
        | Product Overall Stock
        |--------------------------------------------------------------------------
        */

        $product->decrement('current_stock', $qty);
    }
}
