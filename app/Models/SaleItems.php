<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        | Item Discount
        |--------------------------------------------------------------------------
        |
        | discount_type:
        | percentage / fixed
        |
        | discount_value:
        | 10  = 10%
        | 50  = ₹50
        |
        | discount:
        | final calculated discount amount
        |
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
        | Total
        |--------------------------------------------------------------------------
        */

        'line_total',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'quantity' =>
        'decimal:3',

        'free_quantity' =>
        'decimal:3',

        'sale_price' =>
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
    ];


    /*
    |--------------------------------------------------------------------------
    | Sale
    |--------------------------------------------------------------------------
    */

    public function sale()
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
    */

    public function godown()
    {
        return $this->belongsTo(
            Godown::class
        );
    }
}
