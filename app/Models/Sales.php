<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',

        'store_id',

        'customer_id',

        /*
        |--------------------------------------------------------------------------
        | Sale Source / Mechanic
        |--------------------------------------------------------------------------
        */

        'sale_source',

        'mechanic_id',

        /*
        |--------------------------------------------------------------------------
        | Mechanic Commission
        |--------------------------------------------------------------------------
        */

        'commission_type',

        'commission_percent',

        'commission_amount',

        'commission_status',

        /*
        |--------------------------------------------------------------------------
        | Invoice
        |--------------------------------------------------------------------------
        */

        'invoice_no',

        'invoice_date',

        /*
        |--------------------------------------------------------------------------
        | Billing Type
        |--------------------------------------------------------------------------
        |
        | gst
        | non_gst
        |
        */

        'billing_type',

        /*
        |--------------------------------------------------------------------------
        | Amounts
        |--------------------------------------------------------------------------
        */

        'sub_total',

        /*
        |--------------------------------------------------------------------------
        | Legacy Sale-Level Discount
        |--------------------------------------------------------------------------
        |
        | Keep for database compatibility.
        |
        | New Sales UI will not use this.
        | Controller can save this as 0.
        |
        */

        'discount',

        'gst_total',

        /*
        |--------------------------------------------------------------------------
        | Final Discount
        |--------------------------------------------------------------------------
        */

        'final_discount',

        /*
        |--------------------------------------------------------------------------
        | Round Off
        |--------------------------------------------------------------------------
        */

        'round_off',

        /*
        |--------------------------------------------------------------------------
        | Final Net Amount
        |--------------------------------------------------------------------------
        */

        'grand_total',

        /*
        |--------------------------------------------------------------------------
        | Payment
        |--------------------------------------------------------------------------
        */

        'paid_amount',

        'balance_amount',

        'payment_status',

        /*
        |--------------------------------------------------------------------------
        | Other
        |--------------------------------------------------------------------------
        */

        'remarks',

        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'invoice_date' =>
        'date',

        'sub_total' =>
        'decimal:2',

        'discount' =>
        'decimal:2',

        'gst_total' =>
        'decimal:2',

        'final_discount' =>
        'decimal:2',

        'round_off' =>
        'decimal:2',

        'grand_total' =>
        'decimal:2',

        'paid_amount' =>
        'decimal:2',

        'balance_amount' =>
        'decimal:2',

        'commission_percent' =>
        'decimal:2',

        'commission_amount' =>
        'decimal:2',
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
    | Customer
    |--------------------------------------------------------------------------
    */

    public function customer()
    {
        return $this->belongsTo(
            Customer::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mechanic
    |--------------------------------------------------------------------------
    */

    public function mechanic()
    {
        return $this->belongsTo(
            Mechanic::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mechanic Commission Ledger
    |--------------------------------------------------------------------------
    */

    public function mechanicCommission()
    {
        return $this->hasOne(
            MechanicCommission::class,
            'sale_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Sale Items
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(
            SaleItems::class,
            'sale_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Payments
    |--------------------------------------------------------------------------
    */

    public function payments()
    {
        return $this->hasMany(
            CustomerPayment::class,
            'sale_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Payment Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return
            $this->payment_status
            ===
            'Paid';
    }


    public function isPartiallyPaid(): bool
    {
        return
            $this->payment_status
            ===
            'Partially Paid';
    }


    public function isUnpaid(): bool
    {
        return
            $this->payment_status
            ===
            'Unpaid';
    }


    public function hasBalance(): bool
    {
        return
            (float) $this->balance_amount
            >
            0;
    }


    /*
    |--------------------------------------------------------------------------
    | Mechanic Sale Helper
    |--------------------------------------------------------------------------
    */

    public function isMechanicSale(): bool
    {
        return
            $this->sale_source
            ===
            'mechanic'
            &&
            $this->mechanic_id !== null;
    }


    /*
    |--------------------------------------------------------------------------
    | GST Billing Helper
    |--------------------------------------------------------------------------
    */

    public function isGstBill(): bool
    {
        return
            $this->billing_type
            ===
            'gst';
    }


    /*
    |--------------------------------------------------------------------------
    | Non-GST Billing Helper
    |--------------------------------------------------------------------------
    */

    public function isNonGstBill(): bool
    {
        return
            $this->billing_type
            ===
            'non_gst';
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Invoice Number
    |--------------------------------------------------------------------------
    |
    | Invoice number is unique PER STORE.
    |
    | Example:
    |
    | Branch 1 -> INV000001
    | Branch 2 -> INV000001
    |
    */

    public static function generateInvoiceNo(
        int $storeId
    ): string {

        $lastSale =
            self::query()
            ->where(
                'store_id',
                $storeId
            )
            ->latest('id')
            ->first();


        if (
            !$lastSale
        ) {

            return 'INV000001';
        }


        /*
        |--------------------------------------------------------------------------
        | Extract Numeric Portion
        |--------------------------------------------------------------------------
        |
        | INV000125 -> 125
        |
        */

        $number =
            (int) preg_replace(
                '/\D/',
                '',
                (string) $lastSale->invoice_no
            );


        $nextNumber =
            $number + 1;


        return
            'INV'
            .
            str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
    }
}
