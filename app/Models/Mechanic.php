<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mechanic extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',

        'mechanic_code',

        'mechanic_name',

        'mobile',

        'email',

        'address',

        'city',

        'state',

        'pincode',

        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

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
    | Stores / Branches
    |--------------------------------------------------------------------------
    */

    public function stores()
    {
        return $this->belongsToMany(
            Store::class,
            'mechanic_stores',
            'mechanic_id',
            'store_id'
        )
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    |
    | This relationship will work after we add mechanic_id to sales.
    |
    */

    public function sales()
    {
        return $this->hasMany(
            Sale::class,
            'mechanic_id'
        );
    }
}
