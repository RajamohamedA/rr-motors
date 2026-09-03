<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Unit extends Model
{
    protected $fillable = [

        'company_id',
        'store_id',

        'unit_name',
        'short_name',

        'description',

        'is_system_unit',

        'status',
    ];


    protected $casts = [

        'company_id' =>
        'integer',

        'store_id' =>
        'integer',

        'is_system_unit' =>
        'boolean',

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


    public function scopeSystemUnits(
        $query
    ) {

        return $query->where(
            'is_system_unit',
            true
        );
    }


    public function scopeCustomUnits(
        $query
    ) {

        return $query->where(
            'is_system_unit',
            false
        );
    }
}
