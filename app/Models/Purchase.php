<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',
        'store_id',
        'supplier_id',

        'purchase_no',
        'purchase_date',

        'invoice_no',
        'invoice_date',

        'tax_type',

        'subtotal',
        'discount',
        'tax_amount',
        'other_charges',
        'grand_total',

        'paid_amount',
        'balance_amount',

        'remarks',

        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'purchase_date' =>
        'date',

        'invoice_date' =>
        'date',

        'subtotal' =>
        'decimal:2',

        'discount' =>
        'decimal:2',

        'tax_amount' =>
        'decimal:2',

        'other_charges' =>
        'decimal:2',

        'grand_total' =>
        'decimal:2',

        'paid_amount' =>
        'decimal:2',

        'balance_amount' =>
        'decimal:2',

        'status' =>
        'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(
            Company::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store / Branch
    |--------------------------------------------------------------------------
    */

    public function store()
    {
        return $this->belongsTo(
            Store::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier
    |--------------------------------------------------------------------------
    */

    public function supplier()
    {
        return $this->belongsTo(
            Supplier::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Items
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Returns
    |--------------------------------------------------------------------------
    */

    public function returns()
    {
        return $this->hasMany(
            PurchaseReturn::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GST Purchase
    |--------------------------------------------------------------------------
    */

    public function isGstPurchase(): bool
    {
        return $this->tax_type === 'gst';
    }


    /*
    |--------------------------------------------------------------------------
    | Non GST Purchase
    |--------------------------------------------------------------------------
    */

    public function isNonGstPurchase(): bool
    {
        return $this->tax_type === 'non_gst';
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Status
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return (float) $this->balance_amount <= 0;
    }


    public function isPartiallyPaid(): bool
    {
        return (
            (float) $this->paid_amount > 0
            &&
            (float) $this->balance_amount > 0
        );
    }


    public function isUnpaid(): bool
    {
        return (
            (float) $this->paid_amount <= 0
            &&
            (float) $this->balance_amount > 0
        );
    }
}
