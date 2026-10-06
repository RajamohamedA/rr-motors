<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MechanicPayment extends Model
{
    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'company_id',
        'store_id',
        'mechanic_id',
        'account_id',
        'payment_no',
        'payment_date',
        'amount',
        'payment_mode',
        'reference_no',
        'status',
        'remarks',
        'created_by',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'mechanic_id' => 'integer',
            'account_id' => 'integer',
            'payment_date' => 'date',
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

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(
            MechanicPaymentAllocation::class,
            'mechanic_payment_id'
        );
    }

    public function commissions(): BelongsToMany
    {
        return $this->belongsToMany(
            MechanicCommission::class,
            'mechanic_payment_allocations',
            'mechanic_payment_id',
            'mechanic_commission_id'
        )
            ->withPivot('allocated_amount')
            ->withTimestamps();
    }

    public function ledgerEntries(): MorphMany
    {
        return $this->morphMany(
            MechanicLedgerEntry::class,
            'reference',
            'reference_type',
            'reference_id'
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

    public function scopeForMechanic(
        Builder $query,
        int $mechanicId
    ): Builder {
        return $query->where(
            'mechanic_id',
            $mechanicId
        );
    }

    public function scopeForAccount(
        Builder $query,
        int $accountId
    ): Builder {
        return $query->where(
            'account_id',
            $accountId
        );
    }

    public function scopeBetweenDates(
        Builder $query,
        string $fromDate,
        string $toDate
    ): Builder {
        return $query->whereBetween(
            'payment_date',
            [
                $fromDate,
                $toDate,
            ]
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

    /*
    |--------------------------------------------------------------------------
    | Payment Helpers
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

    public function allocatedAmount(): float
    {
        if ($this->relationLoaded('allocations')) {
            return round(
                $this->allocations->sum(
                    fn(MechanicPaymentAllocation $allocation) =>
                    (float) $allocation->allocated_amount
                ),
                2
            );
        }

        return round(
            (float) $this->allocations()->sum(
                'allocated_amount'
            ),
            2
        );
    }

    public function unallocatedAmount(): float
    {
        return max(
            0,
            round(
                (float) $this->amount
                    - $this->allocatedAmount(),
                2
            )
        );
    }

    public function isAdvancePayment(): bool
    {
        return $this->unallocatedAmount() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Number
    |--------------------------------------------------------------------------
    |
    | Call inside the same database transaction that creates the payment.
    |
    */

    public static function generatePaymentNo(
        int $companyId,
        int $storeId
    ): string {
        $lastPaymentNo = self::query()
            ->forContext($companyId, $storeId)
            ->where('payment_no', 'like', 'MPY%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('payment_no');

        $nextNumber = 1;

        if ($lastPaymentNo) {
            $number = preg_replace(
                '/\D+/',
                '',
                (string) $lastPaymentNo
            );

            $nextNumber = ((int) $number) + 1;
        }

        do {
            $paymentNo = 'MPY' . str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );

            $exists = self::query()
                ->forContext($companyId, $storeId)
                ->where('payment_no', $paymentNo)
                ->exists();

            $nextNumber++;
        } while ($exists);

        return $paymentNo;
    }
}
