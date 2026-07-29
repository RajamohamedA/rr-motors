<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{

    protected $fillable = [
        'company_id',
        'store_id',
        'adjustment_date',
        'adjustment_no',
        'type',
        'remarks',
        'created_by'
    ];


    public function items()
    {
        return $this->hasMany(
            StockAdjustmentItem::class
        );
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
