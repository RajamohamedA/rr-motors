<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MechanicPaymentAllocation extends Model
{
    protected $fillable = [
        'company_id',
        'store_id',
        'mechanic_payment_id',
        'mechanic_commission_id',
        'allocated_amount',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'store_id' => 'integer',
            'mechanic_payment_id' => 'integer',
            'mechanic_commission_id' => 'integer',
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function scopeForContext(
        Builder $query,
        int $companyId,
        int $storeId
    ): Builder {
        return $query
            ->where('company_id', $companyId)
            ->where('store_id', $storeId);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(
            MechanicPayment::class,
            'mechanic_payment_id'
        );
    }

    public function commission(): BelongsTo
    {
        return $this->belongsTo(
            MechanicCommission::class,
            'mechanic_commission_id'
        );
    }
}
