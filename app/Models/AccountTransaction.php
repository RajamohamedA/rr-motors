<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountTransaction extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Transaction Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_OPENING_BALANCE = 'opening_balance';

    public const TYPE_SALE = 'sale';

    public const TYPE_CUSTOMER_RECEIPT = 'customer_receipt';

    public const TYPE_CUSTOMER_REFUND = 'customer_refund';

    public const TYPE_SUPPLIER_PAYMENT = 'supplier_payment';

    public const TYPE_MECHANIC_PAYMENT = 'mechanic_payment';

    public const TYPE_EXPENSE = 'expense';

    public const TYPE_RECEIPT_VOUCHER = 'receipt_voucher';

    public const TYPE_PAYMENT_VOUCHER = 'payment_voucher';

    public const TYPE_CONTRA = 'contra';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_REVERSAL = 'reversal';

    protected $table = 'account_transactions';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'company_id',
        'store_id',
        'account_id',
        'transaction_date',
        'transaction_type',
        'reference_type',
        'reference_id',
        'reference_no',
        'debit',
        'credit',
        'payment_mode',
        'external_reference',
        'remarks',
        'is_reversal',
        'reversal_of_id',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'account_id' => 'integer',
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

    public function scopeForAccount(
        Builder $query,
        int $accountId
    ): Builder {
        return $query->where('account_id', $accountId);
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

    public function scopeAsOf(
        Builder $query,
        string $date
    ): Builder {
        return $query->whereDate(
            'transaction_date',
            '<=',
            $date
        );
    }

    public function scopeForReference(
        Builder $query,
        string $referenceType,
        int $referenceId
    ): Builder {
        return $query
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId);
    }

    public function scopeOfType(
        Builder $query,
        string $transactionType
    ): Builder {
        return $query->where(
            'transaction_type',
            $transactionType
        );
    }

    public function scopeOriginal(Builder $query): Builder
    {
        return $query->where('is_reversal', false);
    }

    public function scopeActiveOriginal(Builder $query): Builder
    {
        return $query
            ->where('is_reversal', false)
            ->whereDoesntHave('reversals');
    }

    public function scopeReversal(Builder $query): Builder
    {
        return $query->where('is_reversal', true);
    }

    public function scopeChronological(Builder $query): Builder
    {
        return $query
            ->orderBy('transaction_date')
            ->orderBy('id');
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'reference_type',
            'reference_id'
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

    /**
     * Compatibility alias for existing project code.
     */
    public function reversedTransaction(): BelongsTo
    {
        return $this->reversalOf();
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
    | Accounting Helpers
    |--------------------------------------------------------------------------
    |
    | Debit increases the selected Cash/Bank account.
    | Credit decreases the selected Cash/Bank account.
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
            self::TYPE_CUSTOMER_RECEIPT,
            self::TYPE_CUSTOMER_REFUND,
            self::TYPE_SUPPLIER_PAYMENT,
            self::TYPE_MECHANIC_PAYMENT,
            self::TYPE_EXPENSE,
            self::TYPE_RECEIPT_VOUCHER,
            self::TYPE_PAYMENT_VOUCHER,
            self::TYPE_CONTRA,
            self::TYPE_ADJUSTMENT,
            self::TYPE_REVERSAL,
        ];
    }
}
