<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = [
        'company_id',
        'brand_code',
        'brand_name',
        'description',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}