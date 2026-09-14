<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'from_godown_id',
        'to_godown_id',
        'quantity',
        'from_stock_before',
        'from_stock_after',
        'to_stock_before',
        'to_stock_after',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'from_stock_before' => 'decimal:3',
        'from_stock_after' => 'decimal:3',
        'to_stock_before' => 'decimal:3',
        'to_stock_after' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function fromGodown(): BelongsTo
    {
        return $this->belongsTo(
            Godown::class,
            'from_godown_id'
        );
    }

    public function toGodown(): BelongsTo
    {
        return $this->belongsTo(
            Godown::class,
            'to_godown_id'
        );
    }
}
