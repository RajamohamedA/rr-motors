<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];
}
