<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'from_godown_id',
        'to_godown_id',
        'quantity',
        'unit_cost',
    ];


    public function transfer()
    {
        return $this->belongsTo(
            StockTransfer::class,
            'stock_transfer_id'
        );
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function fromGodown()
    {
        return $this->belongsTo(
            Godown::class,
            'from_godown_id'
        );
    }


    public function toGodown()
    {
        return $this->belongsTo(
            Godown::class,
            'to_godown_id'
        );
    }
}
