<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    public const TYPE_CASH = 'cash';
    public const TYPE_BANK = 'bank';
    public const TYPE_UPI = 'upi';
    public const TYPE_CARD = 'card';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'company_id',
        'store_id',
        'account_code',
        'account_name',
        'account_type',
        'payment_mode',
        'bank_name',
        'account_number',
        'upi_id',
        'opening_balance',
        'opening_balance_date',
        'is_system',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'opening_balance_date' => 'date',
            'is_system' => 'boolean',
            'status' => 'boolean',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AccountTransaction::class);
    }

    public function salePayments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function customerPayments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function balanceUntil(?string $date = null): float
    {
        $transactions = $this->transactions();

        if ($date !== null) {
            $transactions->whereDate('transaction_date', '<=', $date);
        }

        $movement = $transactions
            ->selectRaw('COALESCE(SUM(debit - credit), 0) AS movement')
            ->value('movement');

        return round(
            (float) $this->opening_balance + (float) $movement,
            2
        );
    }
}
