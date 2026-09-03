<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Godown extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',
        'store_id',

        'godown_name',
        'godown_code',
        'address',

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

        'store_id' =>
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
    | Store / Branch
    |--------------------------------------------------------------------------
    */

    public function store(): BelongsTo
    {
        return $this->belongsTo(
            Store::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Ledgers
    |--------------------------------------------------------------------------
    */

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(
            StockLedger::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Items
    |--------------------------------------------------------------------------
    */

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Return Items
    |--------------------------------------------------------------------------
    */

    public function purchaseReturnItems(): HasMany
    {
        return $this->hasMany(
            PurchaseReturnItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Sale Items
    |--------------------------------------------------------------------------
    */

    public function saleItems(): HasMany
    {
        return $this->hasMany(
            SaleItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Branch Context Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForContext(
        Builder $query,
        int $companyId,
        int $storeId
    ): Builder {

        return $query
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Scope
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {

        return $query->where(
            'status',
            true
        );
    }
}
