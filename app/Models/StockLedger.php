<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLedger extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'company_id',
        'store_id',
        'godown_id',
        'product_id',
        'transaction_date',
        'transaction_type',
        'reference_id',
        'reference_no',
        'qty_in',
        'qty_out',
        'balance_qty',
        'unit_cost',
        'total_cost',
        'remarks',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'company_id' => 'integer',
        'store_id' => 'integer',
        'godown_id' => 'integer',
        'product_id' => 'integer',
        'reference_id' => 'integer',
        'transaction_date' => 'date',
        'qty_in' => 'decimal:2',
        'qty_out' => 'decimal:2',
        'balance_qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class,
            'company_id'
        );
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(
            Store::class,
            'store_id'
        );
    }

    public function godown(): BelongsTo
    {
        return $this->belongsTo(
            Godown::class,
            'godown_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Company and Store Context
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

    /*
    |--------------------------------------------------------------------------
    | Company Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        Builder $query,
        int $companyId
    ): Builder {
        return $query->where(
            'company_id',
            $companyId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForStore(
        Builder $query,
        int $storeId
    ): Builder {
        return $query->where(
            'store_id',
            $storeId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Product Scope
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

    /*
    |--------------------------------------------------------------------------
    | Shop Stock
    |--------------------------------------------------------------------------
    |
    | A NULL godown_id represents stock physically available in the Shop.
    |
    */

    public function scopeShop(Builder $query): Builder
    {
        return $query->whereNull('godown_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Godown Stock
    |--------------------------------------------------------------------------
    */

    public function scopeGodown(
        Builder $query,
        ?int $godownId = null
    ): Builder {
        return $query
            ->whereNotNull('godown_id')
            ->when(
                $godownId,
                fn(Builder $query) => $query->where(
                    'godown_id',
                    $godownId
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Location Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForLocation(
        Builder $query,
        ?int $godownId
    ): Builder {
        return $query->when(
            $godownId === null,
            fn(Builder $query) => $query->whereNull(
                'godown_id'
            ),
            fn(Builder $query) => $query->where(
                'godown_id',
                $godownId
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Transaction Type Scope
    |--------------------------------------------------------------------------
    */

    public function scopeOfType(
        Builder $query,
        string $transactionType
    ): Builder {
        return $query->where(
            'transaction_type',
            $transactionType
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Date Range Scope
    |--------------------------------------------------------------------------
    */

    public function scopeBetweenDates(
        Builder $query,
        ?string $dateFrom,
        ?string $dateTo
    ): Builder {
        return $query
            ->when(
                $dateFrom,
                fn(Builder $query) => $query->whereDate(
                    'transaction_date',
                    '>=',
                    $dateFrom
                )
            )
            ->when(
                $dateTo,
                fn(Builder $query) => $query->whereDate(
                    'transaction_date',
                    '<=',
                    $dateTo
                )
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Inward Transactions
    |--------------------------------------------------------------------------
    */

    public function scopeWithStockIn(Builder $query): Builder
    {
        return $query->where(
            'qty_in',
            '>',
            0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Outward Transactions
    |--------------------------------------------------------------------------
    */

    public function scopeWithStockOut(Builder $query): Builder
    {
        return $query->where(
            'qty_out',
            '>',
            0
        );
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

    public function locationName(): string
    {
        return $this->isShopStock()
            ? 'Shop'
            : ($this->godown?->godown_name ?? 'Godown');
    }

    /*
    |--------------------------------------------------------------------------
    | Quantity Helpers
    |--------------------------------------------------------------------------
    */

    public function hasStock(): bool
    {
        return (float) $this->balance_qty > 0;
    }

    public function isOutOfStock(): bool
    {
        return (float) $this->balance_qty <= 0;
    }
}
