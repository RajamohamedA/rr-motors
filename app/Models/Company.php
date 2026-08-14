<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{

    protected $fillable = [
        'company_name',
        'owner_name',
        'gst_number',
        'pan_number',
        'email',
        'phone',
        'website',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'logo',
        'status',
    ];

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function godowns()
    {
        return $this->hasMany(Godown::class);
    }
}
