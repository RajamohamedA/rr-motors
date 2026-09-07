<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MechanicCommission extends Model
{
    protected $fillable = [

        'company_id',
        'store_id',
        'mechanic_id',
        'sale_id',

        'commission_type',
        'commission_percent',
        'commission_base',
        'commission_amount',

        'paid_amount',
        'balance_amount',

        'status',

        'remarks',
    ];


    protected $casts = [

        'commission_percent' =>
        'decimal:2',

        'commission_base' =>
        'decimal:2',

        'commission_amount' =>
        'decimal:2',

        'paid_amount' =>
        'decimal:2',

        'balance_amount' =>
        'decimal:2',
    ];


    public function company()
    {
        return $this->belongsTo(
            Company::class
        );
    }


    public function store()
    {
        return $this->belongsTo(
            Store::class
        );
    }


    public function mechanic()
    {
        return $this->belongsTo(
            Mechanic::class
        );
    }


    public function sale()
    {
        return $this->belongsTo(
            Sales::class,
            'sale_id',
            'id'
        );
    }
}
