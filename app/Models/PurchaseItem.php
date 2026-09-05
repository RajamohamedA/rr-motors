<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'purchase_id',

        'product_id',

        'godown_id',

        'quantity',

        'free_quantity',

        'purchase_price',

        'discount',

        'taxable_amount',

        'gst_percent',

        'gst_amount',

        'line_total',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'quantity' =>
        'decimal:2',

        'free_quantity' =>
        'decimal:2',

        'purchase_price' =>
        'decimal:2',

        'discount' =>
        'decimal:2',

        'taxable_amount' =>
        'decimal:2',

        'gst_percent' =>
        'decimal:2',

        'gst_amount' =>
        'decimal:2',

        'line_total' =>
        'decimal:2',
    ];


    /*
    |--------------------------------------------------------------------------
    | Purchase
    |--------------------------------------------------------------------------
    */

    public function purchase()
    {
        return $this->belongsTo(
            Purchase::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Godown
    |--------------------------------------------------------------------------
    |
    | NULL godown_id means stock belongs to Shop.
    |
    */

    public function godown()
    {
        return $this->belongsTo(
            Godown::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Physical Stock Quantity
    |--------------------------------------------------------------------------
    |
    | Purchased quantity + free quantity both enter physical stock.
    |
    */

    public function getStockQuantityAttribute(): float
    {
        return
            (float) $this->quantity
            +
            (float) $this->free_quantity;
    }


    /*
    |--------------------------------------------------------------------------
    | Shop Stock
    |--------------------------------------------------------------------------
    */

    public function isShopStock(): bool
    {
        return $this->godown_id === null;
    }


    /*
    |--------------------------------------------------------------------------
    | Godown Stock
    |--------------------------------------------------------------------------
    */

    public function isGodownStock(): bool
    {
        return $this->godown_id !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | Gross Amount
    |--------------------------------------------------------------------------
    */

    public function getGrossAmountAttribute(): float
    {
        return round(
            (float) $this->quantity
                *
                (float) $this->purchase_price,
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Net Before Tax
    |--------------------------------------------------------------------------
    */

    public function getNetBeforeTaxAttribute(): float
    {
        return round(
            max(
                0,
                $this->gross_amount
                    -
                    (float) $this->discount
            ),
            2
        );
    }
}
