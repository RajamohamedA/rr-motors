<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [

        'company_id',

        'customer_code',

        'customer_name',

        'contact_person',

        'mobile',

        'phone',

        'email',

        'billing_address',

        'shipping_address',

        'city',

        'state',

        'country',

        'pincode',

        'gst_number',

        'pan_number',

        'credit_limit',

        'credit_days',

        'opening_balance',

        'balance_type',

        'status',

        'remarks'

    ];
}
