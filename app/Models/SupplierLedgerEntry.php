<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupplierLedgerEntry extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Transaction Types
    |--------------------------------------------------------------------------
    */

    public const TYPE_OPENING_BALANCE =
    'opening_balance';

    public const TYPE_PURCHASE =
    'purchase';

    public const TYPE_PURCHASE_RETURN =
    'purchase_return';

    public const TYPE_SUPPLIER_PAYMENT =
    'supplier_payment';

    public const TYPE_PAYMENT_REVERSAL =
    'payment_reversal';

    public const TYPE_ADJUSTMENT =
    'adjustment';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'company_id',
        'store_id',
        'supplier_id',

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
            'supplier_id' => 'integer',

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
    | Company
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    */

    public function store(): BelongsTo
    {
        return $this->belongsTo(
            Store::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier
    |--------------------------------------------------------------------------
    */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Created By
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Source Reference
    |--------------------------------------------------------------------------
    |
    | Can reference Purchase, PurchaseReturn or SupplierPayment.
    |
    */

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }


    /*
    |--------------------------------------------------------------------------
    | Original Entry
    |--------------------------------------------------------------------------
    */

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'reversal_of_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reversal Entries
    |--------------------------------------------------------------------------
    */

    public function reversals(): HasMany
    {
        return $this->hasMany(
            self::class,
            'reversal_of_id'
        );
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
    | Branch Scope
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
    | Supplier Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForSupplier(
        Builder $query,
        int $supplierId
    ): Builder {
        return $query->where(
            'supplier_id',
            $supplierId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Context Scope
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


    /*
    |--------------------------------------------------------------------------
    | Date Range Scope
    |--------------------------------------------------------------------------
    */

    public function scopeBetweenDates(
        Builder $query,
        ?string $fromDate,
        ?string $toDate
    ): Builder {
        return $query
            ->when(
                $fromDate,
                fn(Builder $dateQuery) =>
                $dateQuery->whereDate(
                    'transaction_date',
                    '>=',
                    $fromDate
                )
            )
            ->when(
                $toDate,
                fn(Builder $dateQuery) =>
                $dateQuery->whereDate(
                    'transaction_date',
                    '<=',
                    $toDate
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Debit Scope
    |--------------------------------------------------------------------------
    */

    public function scopeDebits(
        Builder $query
    ): Builder {
        return $query->where(
            'debit',
            '>',
            0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Credit Scope
    |--------------------------------------------------------------------------
    */

    public function scopeCredits(
        Builder $query
    ): Builder {
        return $query->where(
            'credit',
            '>',
            0
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Ordered Scope
    |--------------------------------------------------------------------------
    */

    public function scopeLedgerOrder(
        Builder $query
    ): Builder {
        return $query
            ->orderBy(
                'transaction_date'
            )
            ->orderBy(
                'id'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Amount Helpers
    |--------------------------------------------------------------------------
    */

    public function debitAmount(): float
    {
        return round(
            (float) $this->debit,
            2
        );
    }


    public function creditAmount(): float
    {
        return round(
            (float) $this->credit,
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Signed Balance Effect
    |--------------------------------------------------------------------------
    |
    | Positive value means supplier payable increases.
    | Negative value means supplier payable reduces.
    |
    */

    public function balanceEffect(): float
    {
        return round(
            $this->creditAmount()
                - $this->debitAmount(),
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Entry Type Helpers
    |--------------------------------------------------------------------------
    */

    public function isDebit(): bool
    {
        return $this->debitAmount() > 0;
    }


    public function isCredit(): bool
    {
        return $this->creditAmount() > 0;
    }


    public function isReversal(): bool
    {
        return (bool) $this->is_reversal;
    }


    public function hasBeenReversed(): bool
    {
        return $this
            ->reversals()
            ->exists();
    }
}
