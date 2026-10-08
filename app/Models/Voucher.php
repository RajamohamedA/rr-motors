<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Voucher extends Model
{
    public const TYPE_RECEIPT = 'receipt';

    public const TYPE_PAYMENT = 'payment';

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'company_id',
        'store_id',
        'account_id',
        'voucher_type',
        'voucher_no',
        'voucher_date',
        'amount',
        'payment_mode',
        'reference_no',
        'party_type',
        'party_id',
        'party_name',
        'remarks',
        'status',
        'created_by',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'account_id' => 'integer',
            'party_id' => 'integer',
            'voucher_date' => 'date',
            'amount' => 'decimal:2',
            'created_by' => 'integer',
            'cancelled_by' => 'integer',
            'cancelled_at' => 'datetime',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Optional customer, supplier or mechanic associated with the voucher.
     *
     * Save party_type using the related model's getMorphClass() value.
     */
    public function party(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'party_type',
            'party_id'
        );
    }

    public function accountTransactions(): MorphMany
    {
        return $this->morphMany(
            AccountTransaction::class,
            'reference',
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

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
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

    public function scopeReceipts(Builder $query): Builder
    {
        return $query->where(
            'voucher_type',
            self::TYPE_RECEIPT
        );
    }

    public function scopePayments(Builder $query): Builder
    {
        return $query->where(
            'voucher_type',
            self::TYPE_PAYMENT
        );
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_COMPLETED
        );
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_CANCELLED
        );
    }

    public function scopeBetweenDates(
        Builder $query,
        string $fromDate,
        string $toDate
    ): Builder {
        return $query->whereBetween(
            'voucher_date',
            [$fromDate, $toDate]
        );
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($search): void {
            $inner
                ->where('voucher_no', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('party_name', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhereHas('account', function (Builder $account) use (
                    $search
                ): void {
                    $account
                        ->where('account_name', 'like', "%{$search}%")
                        ->orWhere('account_code', 'like', "%{$search}%");
                });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Status and Type Helpers
    |--------------------------------------------------------------------------
    */

    public function isReceipt(): bool
    {
        return $this->voucher_type === self::TYPE_RECEIPT;
    }

    public function isPayment(): bool
    {
        return $this->voucher_type === self::TYPE_PAYMENT;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return $this->isCompleted();
    }

    /**
     * Debit increases a Cash/Bank account; credit decreases it.
     */
    public function accountDebit(): float
    {
        return $this->isReceipt()
            ? round((float) $this->amount, 2)
            : 0.0;
    }

    public function accountCredit(): float
    {
        return $this->isPayment()
            ? round((float) $this->amount, 2)
            : 0.0;
    }

    public function typeLabel(): string
    {
        return $this->isReceipt()
            ? 'Receipt Voucher'
            : 'Payment Voucher';
    }

    public static function types(): array
    {
        return [
            self::TYPE_RECEIPT => 'Receipt Voucher',
            self::TYPE_PAYMENT => 'Payment Voucher',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Voucher Number
    |--------------------------------------------------------------------------
    |
    | Call this method inside the same transaction that creates the voucher.
    | Receipt example: RV000001
    | Payment example: PV000001
    |
    */

    public static function generateVoucherNo(
        int $companyId,
        int $storeId,
        string $voucherType
    ): string {
        $prefix = match ($voucherType) {
            self::TYPE_RECEIPT => 'RV',
            self::TYPE_PAYMENT => 'PV',
            default => throw new \InvalidArgumentException(
                'Invalid voucher type.'
            ),
        };

        $lastVoucherNo = self::query()
            ->forContext($companyId, $storeId)
            ->where('voucher_type', $voucherType)
            ->where('voucher_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('voucher_no');

        $nextNumber = 1;

        if ($lastVoucherNo) {
            $numericPart = preg_replace(
                '/\D/',
                '',
                (string) $lastVoucherNo
            );

            $nextNumber = ((int) $numericPart) + 1;
        }

        do {
            $voucherNo = $prefix . str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );

            $exists = self::query()
                ->forContext($companyId, $storeId)
                ->where('voucher_no', $voucherNo)
                ->exists();

            $nextNumber++;
        } while ($exists);

        return $voucherNo;
    }
}
