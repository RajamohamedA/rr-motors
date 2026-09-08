<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    protected $fillable = [
        'company_id',
        'store_id',
        'sale_id',
        'return_no',
        'return_date',
        'customer_id',
        'billing_type',
        'sub_total',
        'discount_total',
        'gst_total',
        'round_off',
        'total_amount',
        'refund_amount',
        'refund_method',
        'refund_status',
        'status',
        'remarks',
    ];

    protected $casts = [
        'company_id' => 'integer',
        'store_id' => 'integer',
        'sale_id' => 'integer',
        'customer_id' => 'integer',
        'return_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'gst_total' => 'decimal:2',
        'round_off' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sales::class,
            'sale_id'
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            SaleReturnItem::class,
            'sale_return_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Context Scopes
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

    public function scopeForStore(
        Builder $query,
        int $storeId
    ): Builder {
        return $query->where(
            'store_id',
            $storeId
        );
    }

    public function scopeForContext(
        Builder $query,
        int $companyId,
        int $storeId
    ): Builder {
        return $query
            ->forCompany($companyId)
            ->forStore($storeId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where(
            'status',
            'completed'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isGstBill(): bool
    {
        return $this->billing_type === 'gst';
    }

    public function isNonGstBill(): bool
    {
        return $this->billing_type === 'non_gst';
    }

    public function isRefunded(): bool
    {
        return $this->refund_status === 'refunded';
    }

    public function isRefundPending(): bool
    {
        return $this->refund_status === 'pending';
    }

    public function isCustomerCredit(): bool
    {
        return $this->refund_status === 'credited'
            || $this->refund_method === 'customer_credit';
    }

    /*
    |--------------------------------------------------------------------------
    | Return Number
    |--------------------------------------------------------------------------
    |
    | return_no currently has a global unique index. Therefore, the generated
    | number is globally unique rather than restarting for each branch.
    |
    */

    public static function generateReturnNo(): string
    {
        $lastId = (int) self::query()->max('id');

        return 'SRT'
            . str_pad(
                (string) ($lastId + 1),
                6,
                '0',
                STR_PAD_LEFT
            );
    }
}
