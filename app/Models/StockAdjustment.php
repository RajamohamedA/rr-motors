<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAdjustment extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'company_id',
        'store_id',
        'adjustment_date',
        'adjustment_no',
        'type',
        'remarks',
        'status',
        'created_by',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'company_id' => 'integer',
        'store_id' => 'integer',
        'adjustment_date' => 'date',
        'created_by' => 'integer',
        'cancelled_at' => 'datetime',
        'cancelled_by' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            StockAdjustmentItem::class,
            'stock_adjustment_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    public function canBeCancelled(): bool
    {
        return $this->isCompleted();
    }

    /*
    |--------------------------------------------------------------------------
    | Adjustment Type Helpers
    |--------------------------------------------------------------------------
    */

    public function isIncrease(): bool
    {
        return $this->type === 'increase';
    }

    public function isDecrease(): bool
    {
        return $this->type === 'decrease';
    }

    public function typeLabel(): string
    {
        return $this->isIncrease()
            ? 'Stock Increase'
            : 'Stock Decrease';
    }

    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */

    public function getTotalQuantityAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return round(
                (float) $this->items->sum('qty'),
                2
            );
        }

        return round(
            (float) $this->items()->sum('qty'),
            2
        );
    }

    public function getTotalCostAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return round(
                (float) $this->items->sum('total_cost'),
                2
            );
        }

        return round(
            (float) $this->items()->sum('total_cost'),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForContext(
        Builder $query,
        int $companyId,
        int $storeId
    ): Builder {
        return $query
            ->where('company_id', $companyId)
            ->where('store_id', $storeId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'Completed');
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'Cancelled');
    }

    public function scopeIncreases(Builder $query): Builder
    {
        return $query->where('type', 'increase');
    }

    public function scopeDecreases(Builder $query): Builder
    {
        return $query->where('type', 'decrease');
    }
}
