<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'company_id',
        'store_code',
        'store_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
