<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerLedgerEntry extends Model
{
    public const TYPE_OPENING_BALANCE = 'opening_balance';

    public const TYPE_SALE = 'sale';

    public const TYPE_SALE_REVERSAL = 'sale_reversal';

    public const TYPE_SALES_RETURN = 'sales_return';

    public const TYPE_SALES_RETURN_REVERSAL = 'sales_return_reversal';

    public const TYPE_CUSTOMER_PAYMENT = 'customer_payment';

    public const TYPE_PAYMENT_REVERSAL = 'payment_reversal';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'company_id',
        'store_id',
        'customer_id',
        'transaction_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'reference_no',
        'debit',
        'credit',
        'is_reversal',
        'reversal_of_id',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'customer_id' => 'integer',
            'transaction_date' => 'date',
            'reference_id' => 'integer',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'is_reversal' => 'boolean',
            'reversal_of_id' => 'integer',
            'created_by' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(
            Store::class
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(
            Customer::class
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'reversal_of_id'
        );
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(
            self::class,
            'reversal_of_id'
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
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            );
    }

    public function scopeForCustomer(
        Builder $query,
        int $customerId
    ): Builder {
        return $query->where(
            'customer_id',
            $customerId
        );
    }

    public function scopeBetweenDates(
        Builder $query,
        string $fromDate,
        string $toDate
    ): Builder {
        return $query->whereBetween(
            'transaction_date',
            [
                $fromDate,
                $toDate,
            ]
        );
    }

    public function scopeForReference(
        Builder $query,
        string $referenceType,
        int $referenceId
    ): Builder {
        return $query
            ->where(
                'reference_type',
                $referenceType
            )
            ->where(
                'reference_id',
                $referenceId
            );
    }

    public function scopeOriginal(
        Builder $query
    ): Builder {
        return $query->where(
            'is_reversal',
            false
        );
    }

    public function scopeReversal(
        Builder $query
    ): Builder {
        return $query->where(
            'is_reversal',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accounting Helpers
    |--------------------------------------------------------------------------
    |
    | Positive balance means the customer owes the business (Dr).
    | Negative balance means the business holds customer advance (Cr).
    |
    */

    public function balanceEffect(): float
    {
        return round(
            (float) $this->debit
                - (float) $this->credit,
            2
        );
    }

    public function isDebit(): bool
    {
        return (float) $this->debit > 0;
    }

    public function isCredit(): bool
    {
        return (float) $this->credit > 0;
    }

    public function isReversal(): bool
    {
        return (bool) $this->is_reversal;
    }

    public function hasBeenReversed(): bool
    {
        return $this->reversals()->exists();
    }

    public static function transactionTypes(): array
    {
        return [
            self::TYPE_OPENING_BALANCE,
            self::TYPE_SALE,
            self::TYPE_SALE_REVERSAL,
            self::TYPE_SALES_RETURN,
            self::TYPE_SALES_RETURN_REVERSAL,
            self::TYPE_CUSTOMER_PAYMENT,
            self::TYPE_PAYMENT_REVERSAL,
            self::TYPE_ADJUSTMENT,
        ];
    }
}
