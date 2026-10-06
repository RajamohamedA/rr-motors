<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MechanicCommission extends Model
{
    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    public const STATUS_PENDING = 'Pending';

    public const STATUS_PARTIALLY_PAID = 'Partially Paid';

    public const STATUS_PAID = 'Paid';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'company_id',
        'store_id',
        'mechanic_id',
        'sale_id',
        'commission_type',
        'commission_percent',
        'commission_base',
        'commission_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'mechanic_id' => 'integer',
            'sale_id' => 'integer',
            'commission_percent' => 'decimal:2',
            'commission_base' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(
            Sales::class,
            'sale_id'
        );
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(
            MechanicPaymentAllocation::class,
            'mechanic_commission_id'
        );
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

    public function salesReturns(): HasMany
    {
        return $this->hasMany(
            SalesReturn::class,
            'sale_id',
            'sale_id'
        );
    }

    public function completedSalesReturns(): HasMany
    {
        return $this->salesReturns()->completed();
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

    public function scopePending(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                self::STATUS_PENDING,
                self::STATUS_PARTIALLY_PAID,
            ])
            ->where('balance_amount', '>', 0);
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where(
            'status',
            '!=',
            self::STATUS_CANCELLED
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Commission Helpers
    |--------------------------------------------------------------------------
    */

    public function calculateCommissionAmount(): float
    {
        if ($this->commission_type === self::TYPE_PERCENTAGE) {
            return round(
                (float) $this->commission_base
                    * (float) $this->commission_percent
                    / 100,
                2
            );
        }

        return round(
            (float) $this->commission_amount,
            2
        );
    }

    public function recalculateBalance(): void
    {
        $commissionAmount = round(
            (float) $this->commission_amount,
            2
        );

        $paidAmount = round(
            (float) $this->paid_amount,
            2
        );

        $balanceAmount = max(
            0,
            round($commissionAmount - $paidAmount, 2)
        );

        if ($this->status === self::STATUS_CANCELLED) {
            $this->balance_amount = 0;
            $this->save();

            return;
        }

        $status = match (true) {
            $balanceAmount <= 0 => self::STATUS_PAID,
            $paidAmount > 0 => self::STATUS_PARTIALLY_PAID,
            default => self::STATUS_PENDING,
        };

        $this->forceFill([
            'paid_amount' => min(
                $paidAmount,
                $commissionAmount
            ),
            'balance_amount' => $balanceAmount,
            'status' => $status,
        ])->save();
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function hasBalance(): bool
    {
        return !$this->isCancelled()
            && (float) $this->balance_amount > 0;
    }

    public function returnedCommissionAmount(): float
    {
        $commissionAmount = round(
            (float) $this->commission_amount,
            2
        );

        $commissionBase = round(
            (float) $this->commission_base,
            2
        );

        if ($commissionAmount <= 0 || $commissionBase <= 0) {
            return 0;
        }

        $returnedBase = $this->relationLoaded(
            'completedSalesReturns'
        )
            ? (float) $this->completedSalesReturns->sum('sub_total')
            : (float) $this->completedSalesReturns()->sum('sub_total');

        return min(
            $commissionAmount,
            round(
                $commissionAmount
                    * min(1, max(0, $returnedBase) / $commissionBase),
                2
            )
        );
    }

    public function effectiveCommissionAmount(): float
    {
        return max(
            0,
            round(
                (float) $this->commission_amount
                    - $this->returnedCommissionAmount(),
                2
            )
        );
    }

    public function excessPaidAdvanceAmount(): float
    {
        return max(
            0,
            round(
                (float) $this->paid_amount
                    - $this->effectiveCommissionAmount(),
                2
            )
        );
    }

    public static function commissionTypes(): array
    {
        return [
            self::TYPE_PERCENTAGE,
            self::TYPE_FIXED,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PARTIALLY_PAID,
            self::STATUS_PAID,
            self::STATUS_CANCELLED,
        ];
    }
}
