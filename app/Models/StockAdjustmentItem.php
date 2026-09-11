<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'godown_id',
        'qty',
        'unit_cost',
        'total_cost',
        'stock_before',
        'stock_after',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'stock_adjustment_id' => 'integer',
        'product_id' => 'integer',
        'godown_id' => 'integer',
        'qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'stock_before' => 'decimal:2',
        'stock_after' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(
            StockAdjustment::class,
            'stock_adjustment_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function godown(): BelongsTo
    {
        return $this->belongsTo(Godown::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Location Helpers
    |--------------------------------------------------------------------------
    */

    public function isShopStock(): bool
    {
        return $this->godown_id === null;
    }

    public function isGodownStock(): bool
    {
        return $this->godown_id !== null;
    }

    public function locationType(): string
    {
        return $this->isShopStock()
            ? 'shop'
            : 'godown';
    }

    public function locationName(): string
    {
        if ($this->isShopStock()) {
            return 'Shop';
        }

        return $this->godown?->godown_name
            ?? 'Godown';
    }

    /*
    |--------------------------------------------------------------------------
    | Calculation Helpers
    |--------------------------------------------------------------------------
    */

    public function calculateTotalCost(): float
    {
        return round(
            (float) $this->qty
                * (float) $this->unit_cost,
            2
        );
    }

    public function expectedStockAfter(string $type): float
    {
        $before = (float) $this->stock_before;
        $quantity = (float) $this->qty;

        return round(
            $type === 'increase'
                ? $before + $quantity
                : $before - $quantity,
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForProduct(
        Builder $query,
        int $productId
    ): Builder {
        return $query->where(
            'product_id',
            $productId
        );
    }

    public function scopeForLocation(
        Builder $query,
        ?int $godownId
    ): Builder {
        if ($godownId === null) {
            return $query->whereNull('godown_id');
        }

        return $query->where(
            'godown_id',
            $godownId
        );
    }
}
