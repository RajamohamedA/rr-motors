<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Godown extends Model
{
    protected $fillable = [
        'company_id',
        'store_id',
        'godown_name',
        'godown_code',
        'address',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
