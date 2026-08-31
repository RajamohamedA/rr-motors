<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',

        'customer_code',

        'customer_name',

        'customer_type',

        'is_system_customer',

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

        'remarks',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'company_id' =>
        'integer',

        'credit_limit' =>
        'decimal:2',

        'credit_days' =>
        'integer',

        'opening_balance' =>
        'decimal:2',

        'is_system_customer' =>
        'boolean',

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
    | Branches / Stores
    |--------------------------------------------------------------------------
    |
    | A customer belongs to one company, but can be used by multiple branches
    | of that company.
    |
    */

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(
            Store::class,
            'customer_stores',
            'customer_id',
            'store_id'
        )
            ->withTimestamps();
    }


    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    public function payments(): HasMany
    {
        return $this->hasMany(
            CustomerPayment::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */

    public function sales(): HasMany
    {
        return $this->hasMany(
            Sales::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */

    public function quotations(): HasMany
    {
        return $this->hasMany(
            Quotation::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Walk-In Customer
    |--------------------------------------------------------------------------
    */

    public function isWalkInCustomer(): bool
    {
        return $this->customer_type ===
            'walk_in';
    }


    /*
    |--------------------------------------------------------------------------
    | System Customer
    |--------------------------------------------------------------------------
    */

    public function isSystemCustomer(): bool
    {
        return (bool)
        $this->is_system_customer;
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Available In Branch
    |--------------------------------------------------------------------------
    */

    public function isAvailableInStore(
        int $storeId
    ): bool {

        return $this
            ->stores()
            ->where(
                'stores.id',
                $storeId
            )
            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Attach Customer To Branch
    |--------------------------------------------------------------------------
    */

    public function attachToStore(
        int $storeId
    ): void {

        $this
            ->stores()
            ->syncWithoutDetaching([
                $storeId,
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Remove Customer From Branch
    |--------------------------------------------------------------------------
    |
    | This removes only branch access.
    | It does NOT delete the actual customer record.
    |
    */

    public function detachFromStore(
        int $storeId
    ): void {

        $this
            ->stores()
            ->detach(
                $storeId
            );
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


    /*
    |--------------------------------------------------------------------------
    | Branch Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForStore(
        $query,
        int $storeId
    ) {

        return $query->whereHas(
            'stores',
            function (
                $storeQuery
            ) use (
                $storeId
            ) {

                $storeQuery->where(
                    'stores.id',
                    $storeId
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Regular Customers Scope
    |--------------------------------------------------------------------------
    */

    public function scopeRegular(
        $query
    ) {

        return $query->where(
            'customer_type',
            'regular'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Walk-In Customer Scope
    |--------------------------------------------------------------------------
    */

    public function scopeWalkIn(
        $query
    ) {

        return $query->where(
            'customer_type',
            'walk_in'
        );
    }
}
