<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [

        'company_id',
        'unit_name',
        'short_name',
        'description',
        'status'

    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}