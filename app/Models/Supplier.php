<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'company_id',
        'supplier_code',
        'supplier_name',
        'contact_person',
        'phone',
        'alternate_phone',
        'email',
        'gst_number',
        'pan_number',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'opening_balance',
        'credit_limit',
        'credit_days',
        'balance_type',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'company_id' => 'integer',
            'opening_balance' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'credit_days' => 'integer',
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

    /*
    | Supplier master belongs to one company. The same supplier can be
    | available in multiple branches through supplier_stores.
    */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(
            Store::class,
            'supplier_stores',
            'supplier_id',
            'store_id'
        )->withTimestamps();
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(
            Purchase::class,
            'supplier_id',
            'id'
        );
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(
            PurchaseReturn::class,
            'supplier_id',
            'id'
        );
    }

    public function payments(): HasMany
    {
        return $this->hasMany(
            SupplierPayment::class,
            'supplier_id',
            'id'
        );
    }

    public function completedPayments(): HasMany
    {
        return $this->hasMany(
            SupplierPayment::class,
            'supplier_id',
            'id'
        )->where(
            'status',
            SupplierPayment::STATUS_COMPLETED
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Branch Helpers
    |--------------------------------------------------------------------------
    */

    public function isAvailableInStore(
        int $storeId
    ): bool {
        return $this
            ->stores()
            ->where('stores.id', $storeId)
            ->exists();
    }

    public function attachToStore(
        int $storeId
    ): void {
        $this
            ->stores()
            ->syncWithoutDetaching([
                $storeId,
            ]);
    }

    public function detachFromStore(
        int $storeId
    ): void {
        $this
            ->stores()
            ->detach($storeId);
    }

    /*
    |--------------------------------------------------------------------------
    | Balance Helpers
    |--------------------------------------------------------------------------
    */

    public function openingCredit(): float
    {
        return $this->balance_type === 'Cr'
            ? (float) $this->opening_balance
            : 0.0;
    }

    public function openingDebit(): float
    {
        return $this->balance_type === 'Dr'
            ? (float) $this->opening_balance
            : 0.0;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->where('status', true);
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
}
