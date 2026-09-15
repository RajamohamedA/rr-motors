<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItems extends Model
{
    protected $table = 'sale_items';

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'sale_id',
        'product_id',
        'godown_id',

        'quantity',
        'free_quantity',

        'sale_price',

        /*
        |--------------------------------------------------------------------------
        | Cost Snapshot
        |--------------------------------------------------------------------------
        */

        'unit_cost',
        'cost_total',
        'cost_source',

        /*
        |--------------------------------------------------------------------------
        | Item Discount
        |--------------------------------------------------------------------------
        */

        'discount_type',
        'discount_value',
        'discount',

        /*
        |--------------------------------------------------------------------------
        | GST
        |--------------------------------------------------------------------------
        */

        'gst_percent',
        'gst_amount',

        /*
        |--------------------------------------------------------------------------
        | Sale and Profit Snapshot
        |--------------------------------------------------------------------------
        */

        'line_total',
        'net_sale_amount',
        'gross_profit',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'quantity' =>
            'decimal:3',

            'free_quantity' =>
            'decimal:3',

            'sale_price' =>
            'decimal:2',

            'unit_cost' =>
            'decimal:2',

            'cost_total' =>
            'decimal:2',

            'discount_value' =>
            'decimal:2',

            'discount' =>
            'decimal:2',

            'gst_percent' =>
            'decimal:2',

            'gst_amount' =>
            'decimal:2',

            'line_total' =>
            'decimal:2',

            'net_sale_amount' =>
            'decimal:2',

            'gross_profit' =>
            'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Sale
    |--------------------------------------------------------------------------
    */

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sales::class,
            'sale_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Godown
    |--------------------------------------------------------------------------
    */

    public function godown(): BelongsTo
    {
        return $this->belongsTo(
            Godown::class,
            'godown_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Total Stock Quantity
    |--------------------------------------------------------------------------
    |
    | Free quantity also leaves inventory and must be included in cost.
    |
    */

    public function totalStockQuantity(): float
    {
        return round(
            (float) $this->quantity
                +
                (float) $this->free_quantity,
            3
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Cost Total
    |--------------------------------------------------------------------------
    */

    public function calculateCostTotal(
        float $unitCost
    ): float {
        return round(
            $this->totalStockQuantity()
                *
                $unitCost,
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Gross Profit
    |--------------------------------------------------------------------------
    |
    | net_sale_amount must exclude GST.
    |
    */

    public function calculateGrossProfit(
        float $netSaleAmount,
        float $costTotal
    ): float {
        return round(
            $netSaleAmount
                -
                $costTotal,
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cost Snapshot Available
    |--------------------------------------------------------------------------
    */

    public function hasCostSnapshot(): bool
    {
        return
            $this->unit_cost !== null
            &&
            $this->cost_total !== null
            &&
            $this->net_sale_amount !== null
            &&
            $this->gross_profit !== null;
    }
}
