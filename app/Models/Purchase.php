<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [

        'company_id',
        'store_id',
        'supplier_id',

        'purchase_no',
        'purchase_date',

        'invoice_no',
        'invoice_date',

        'subtotal',
        'discount',
        'tax_amount',
        'other_charges',
        'grand_total',

        'paid_amount',
        'balance_amount',

        'remarks',

        'status'

    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}