<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

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

        'credit_limit',

        'credit_days',

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

        'opening_balance' =>
        'decimal:2',

        'credit_limit' =>
        'decimal:2',

        'credit_days' =>
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
    | Branches / Stores
    |--------------------------------------------------------------------------
    |
    | Supplier master belongs to one company.
    | The same supplier can be available in multiple branches.
    |
    */

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(
            Store::class,
            'supplier_stores',
            'supplier_id',
            'store_id'
        )
            ->withTimestamps();
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
    | Purchase Returns
    |--------------------------------------------------------------------------
    */

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(
            PurchaseReturn::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Available In Branch
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
    | Attach To Branch
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
    | Remove From Branch
    |--------------------------------------------------------------------------
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
}
