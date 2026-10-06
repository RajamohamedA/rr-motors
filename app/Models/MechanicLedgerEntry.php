<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MechanicLedgerEntry extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Transaction Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_OPENING_BALANCE = 'opening_balance';

    public const TYPE_COMMISSION = 'commission';

    public const TYPE_COMMISSION_REVERSAL = 'commission_reversal';

    public const TYPE_MECHANIC_PAYMENT = 'mechanic_payment';

    public const TYPE_PAYMENT_REVERSAL = 'payment_reversal';

    public const TYPE_SALES_RETURN_COMMISSION =
    'sales_return_commission';

    public const TYPE_SALES_RETURN_COMMISSION_REVERSAL =
    'sales_return_commission_reversal';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_ADJUSTMENT_REVERSAL = 'adjustment_reversal';

    protected $table = 'mechanic_ledger_entries';

    protected $fillable = [
        'company_id',
        'store_id',
        'mechanic_id',
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
            'mechanic_id' => 'integer',
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
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
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
            ->where('company_id', $companyId)
            ->where('store_id', $storeId);
    }

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

    public function scopeForMechanic(
        Builder $query,
        int $mechanicId
    ): Builder {
        return $query->where(
            'mechanic_id',
            $mechanicId
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
    | Accounting Helpers
    |--------------------------------------------------------------------------
    |
    | Credit increases the commission payable to the mechanic.
    | Debit decreases the commission payable to the mechanic.
    |
    */

    public function payableEffect(): float
    {
        return round(
            (float) $this->credit
                - (float) $this->debit,
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

    public function isSalesReturnCommission(): bool
    {
        return in_array(
            $this->transaction_type,
            [
                self::TYPE_SALES_RETURN_COMMISSION,
                self::TYPE_SALES_RETURN_COMMISSION_REVERSAL,
            ],
            true
        );
    }

    public static function transactionTypes(): array
    {
        return [
            self::TYPE_OPENING_BALANCE,
            self::TYPE_COMMISSION,
            self::TYPE_COMMISSION_REVERSAL,
            self::TYPE_MECHANIC_PAYMENT,
            self::TYPE_PAYMENT_REVERSAL,
            self::TYPE_SALES_RETURN_COMMISSION,
            self::TYPE_SALES_RETURN_COMMISSION_REVERSAL,
            self::TYPE_ADJUSTMENT,
            self::TYPE_ADJUSTMENT_REVERSAL,
        ];
    }
}
