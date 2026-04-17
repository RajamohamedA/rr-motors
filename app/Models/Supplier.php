<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'company_id',
        'supplier_code',
        'supplier_name',
        'contact_person',
        'phone',
        'alternate_phone',
        'email',
        'gst_number',
        'pan_number',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'opening_balance',
        'balance_type',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}