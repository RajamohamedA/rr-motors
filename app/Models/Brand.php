<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = [
        'company_id',
        'brand_code',
        'brand_name',
        'description',
        'status',
    ];


    protected $casts = [
        'company_id' => 'integer',
        'status' => 'boolean',
    ];


    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }


    public function products(): HasMany
    {
        return $this->hasMany(
            Product::class
        );
    }
}
