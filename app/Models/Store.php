<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',

        'branch_number',

        'store_code',

        'store_name',

        'contact_person',

        'phone',

        'email',

        'address',

        'city',

        'district',

        'state',

        'country',

        'pincode',

        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'company_id' =>
        'integer',

        'branch_number' =>
        'integer',

        'status' =>
        'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */

    public function purchases(): HasMany
    {
        return $this->hasMany(
            Purchase::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_stores',
            'store_id',
            'user_id'
        )
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | Godowns
    |--------------------------------------------------------------------------
    */

    public function godowns(): HasMany
    {
        return $this->hasMany(
            Godown::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    |
    | One company customer can be linked to multiple branches.
    |
    */

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(
            Customer::class,
            'customer_stores',
            'store_id',
            'customer_id'
        )
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | Suppliers
    |--------------------------------------------------------------------------
    |
    | One company supplier can be linked to multiple branches.
    |
    */

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(
            Supplier::class,
            'supplier_stores',
            'store_id',
            'supplier_id'
        )
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | Active Scope
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        $query
    ) {

        return $query->where(
            'status',
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Company Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        $query,
        int $companyId
    ) {

        return $query->where(
            'company_id',
            $companyId
        );
    }
}
