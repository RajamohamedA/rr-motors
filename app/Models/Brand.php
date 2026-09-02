<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Brand extends Model
{
    protected $fillable = [
        'company_id',
        'store_id',
        'brand_code',
        'brand_name',
        'description',
        'status',
    ];


    protected $casts = [
        'company_id' =>
        'integer',

        'store_id' =>
        'integer',

        'status' =>
        'boolean',
    ];


    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }


    public function store(): BelongsTo
    {
        return $this->belongsTo(
            Store::class
        );
    }


    public function products(): HasMany
    {
        return $this->hasMany(
            Product::class
        );
    }


    public function scopeForContext(
        $query,
        int $companyId,
        int $storeId
    ) {

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
}
