<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesReturn extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const REFUND_PENDING = 'pending';
    public const REFUND_REFUNDED = 'refunded';
    public const REFUND_CREDITED = 'credited';
    public const REFUND_CANCELLED = 'cancelled';

    public const METHOD_CASH = 'cash';
    public const METHOD_BANK = 'bank';
    public const METHOD_ORIGINAL_PAYMENT = 'original_payment';
    public const METHOD_CUSTOMER_CREDIT = 'customer_credit';
    public const METHOD_NONE = 'none';

    protected $table = 'sales_returns';

    protected $fillable = [
        'company_id',
        'store_id',
        'sale_id',
        'customer_id',
        'account_id',
        'return_no',
        'return_date',
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

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'sale_id' => 'integer',
            'customer_id' => 'integer',
            'account_id' => 'integer',
            'return_date' => 'date',
            'sub_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'gst_total' => 'decimal:2',
            'round_off' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
        ];
    }

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

    /** Cash or bank account used when money was refunded. */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            SaleReturnItem::class,
            'sale_return_id'
        );
    }

    /** Original cash/bank outflow generated for this refund. */
    public function refundAccountTransaction(): HasOne
    {
        return $this->hasOne(
            AccountTransaction::class,
            'reference_id'
        )
            ->where('reference_type', 'sales_return_refund')
            ->where('is_reversal', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        Builder $query,
        int $companyId
    ): Builder {
        return $query->where('company_id', $companyId);
    }

    public function scopeForStore(
        Builder $query,
        int $storeId
    ): Builder {
        return $query->where('store_id', $storeId);
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
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('refund_status', self::REFUND_REFUNDED);
    }

    public function scopeCustomerCredits(Builder $query): Builder
    {
        return $query->where(function (Builder $inner): void {
            $inner
                ->where('refund_status', self::REFUND_CREDITED)
                ->orWhere('refund_method', self::METHOD_CUSTOMER_CREDIT);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
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
        return $this->refund_status === self::REFUND_REFUNDED;
    }

    public function isRefundPending(): bool
    {
        return $this->refund_status === self::REFUND_PENDING;
    }

    public function isCustomerCredit(): bool
    {
        return $this->refund_status === self::REFUND_CREDITED
            || $this->refund_method === self::METHOD_CUSTOMER_CREDIT;
    }

    public function requiresRefundAccount(): bool
    {
        return $this->isRefunded()
            && in_array(
                $this->refund_method,
                [
                    self::METHOD_CASH,
                    self::METHOD_BANK,
                    self::METHOD_ORIGINAL_PAYMENT,
                ],
                true
            );
    }

    public function hasCashRefund(): bool
    {
        return $this->isRefunded()
            && (float) $this->refund_amount > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Return Number Preview
    |--------------------------------------------------------------------------
    |
    | This is only a UI preview. The controller creates the document with a
    | temporary unique value and changes it to SRT{id} after insertion. That
    | inserted-ID approach is the concurrency-safe final return number.
    |
    */

    public static function generateReturnNo(): string
    {
        $nextId = ((int) self::query()->max('id')) + 1;

        return 'SRT' . str_pad(
            (string) $nextId,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}
