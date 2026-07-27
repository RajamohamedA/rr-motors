<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $fillable = [

        'company_id',

        'store_id',

        'customer_id',

        'invoice_no',

        'invoice_date', 

        'sub_total',

        'discount',

        'gst_total',

        'grand_total',

        'paid_amount',

        'balance_amount',

        'remarks',

        'status'

    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItems::class,'sale_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public static function generateInvoiceNo()
    {
        $last = self::latest('id')->first();

        $number = $last ? $last->id + 1 : 1;

        return 'INV' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}