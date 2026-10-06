<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mechanic extends Model
{
    protected $fillable = [
        'company_id',
        'mechanic_code',
        'mechanic_name',
        'mobile',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'status' => 'boolean',
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

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(
            Store::class,
            'mechanic_stores',
            'mechanic_id',
            'store_id'
        )->withTimestamps();
    }

    public function sales(): HasMany
    {
        return $this->hasMany(
            Sales::class,
            'mechanic_id'
        );
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(
            MechanicCommission::class,
            'mechanic_id'
        );
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(
            MechanicLedgerEntry::class,
            'mechanic_id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            MechanicPayment::class,
            'mechanic_id'
        );
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
        return $query->where(
            'company_id',
            $companyId
        );
    }

    public function scopeForStore(
        Builder $query,
        int $storeId
    ): Builder {
        return $query->whereHas(
            'stores',
            function (Builder $storeQuery) use ($storeId) {
                $storeQuery->where(
                    'stores.id',
                    $storeId
                );
            }
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(
            function (Builder $searchQuery) use ($search) {
                $searchQuery
                    ->where(
                        'mechanic_name',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'mechanic_code',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'mobile',
                        'like',
                        '%' . $search . '%'
                    );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Branch Helpers
    |--------------------------------------------------------------------------
    */

    public function isAvailableInStore(int $storeId): bool
    {
        return $this->stores()
            ->where('stores.id', $storeId)
            ->exists();
    }

    public function attachToStore(int $storeId): void
    {
        $this->stores()->syncWithoutDetaching([
            $storeId,
        ]);
    }

    public function detachFromStore(int $storeId): void
    {
        $this->stores()->detach($storeId);
    }

    /*
    |--------------------------------------------------------------------------
    | Balance Helpers
    |--------------------------------------------------------------------------
    |
    | Credit increases the amount payable to the mechanic.
    | Debit decreases the amount payable to the mechanic.
    |
    */

    public function ledgerBalance(
        ?int $storeId = null
    ): float {
        $query = $this->ledgerEntries();

        if ($storeId !== null) {
            $query->where('store_id', $storeId);
        }

        $totals = $query
            ->selectRaw(
                'COALESCE(SUM(credit), 0) AS total_credit, '
                    . 'COALESCE(SUM(debit), 0) AS total_debit'
            )
            ->first();

        return round(
            (float) $totals->total_credit
                - (float) $totals->total_debit,
            2
        );
    }
}
