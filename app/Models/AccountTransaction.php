<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountTransaction extends Model
{
    public const TYPE_SALE = 'sale';
    public const TYPE_CUSTOMER_RECEIPT = 'customer_receipt';
    public const TYPE_SUPPLIER_PAYMENT = 'supplier_payment';
    public const TYPE_MECHANIC_PAYMENT = 'mechanic_payment';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_RECEIPT_VOUCHER = 'receipt_voucher';
    public const TYPE_PAYMENT_VOUCHER = 'payment_voucher';
    public const TYPE_CONTRA = 'contra';
    public const TYPE_REVERSAL = 'reversal';

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

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'is_reversal' => 'boolean',
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
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_id');
    }
}
