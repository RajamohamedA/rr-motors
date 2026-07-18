<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLedger;

class StockService
{
    public function increaseStock(
        Product $product,
        float $qty,
        string $transactionType,
        int $companyId,
        int $storeId,
        ?int $referenceId,
        ?string $referenceNo,
        float $unitCost = 0,
        string $remarks = ''
    ) {

        $product->increment('current_stock', $qty);

        $product->refresh();

        StockLedger::create([

            'company_id'       => $companyId,
            'store_id'         => $storeId,
            'product_id'       => $product->id,

            'transaction_date' => now(),

            'transaction_type' => $transactionType,

            'reference_id'     => $referenceId,

            'reference_no'     => $referenceNo,

            'qty_in'           => $qty,

            'qty_out'          => 0,

            'balance_qty'      => $product->current_stock,

            'unit_cost'        => $unitCost,

            'total_cost'       => $qty * $unitCost,

            'remarks'          => $remarks,

        ]);
    }

    public function decreaseStock(
        Product $product,
        float $qty,
        string $transactionType,
        int $companyId,
        int $storeId,
        ?int $referenceId,
        ?string $referenceNo,
        float $unitCost = 0,
        string $remarks = ''
    ) {

        $product->decrement('current_stock', $qty);

        $product->refresh();

        StockLedger::create([

            'company_id'       => $companyId,
            'store_id'         => $storeId,
            'product_id'       => $product->id,

            'transaction_date' => now(),

            'transaction_type' => $transactionType,

            'reference_id'     => $referenceId,

            'reference_no'     => $referenceNo,

            'qty_in'           => 0,

            'qty_out'          => $qty,

            'balance_qty'      => $product->current_stock,

            'unit_cost'        => $unitCost,

            'total_cost'       => $qty * $unitCost,

            'remarks'          => $remarks,

        ]);
    }
}