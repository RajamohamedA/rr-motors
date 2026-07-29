<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'qty',
        'unit_cost',
        'total_cost'
    ];
}
