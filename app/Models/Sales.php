<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sales extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';

    public const PAYMENT_UNPAID = 'Unpaid';
    public const PAYMENT_PARTIAL = 'Partially Paid';
    public const PAYMENT_PAID = 'Paid';

    protected $table = 'sales';

    protected $fillable = [
        'company_id',
        'store_id',
        'quotation_id',
        'customer_id',
        'sale_source',
        'mechanic_id',
        'commission_type',
        'commission_percent',
        'commission_amount',
        'commission_status',
        'invoice_no',
        'invoice_date',
        'billing_type',
        'sub_total',
        'discount',
        'gst_total',
        'final_discount',
        'round_off',
        'grand_total',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'sub_total' => 'decimal:2',
            'discount' => 'decimal:2',
            'gst_total' => 'decimal:2',
            'final_discount' => 'decimal:2',
            'round_off' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'commission_percent' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
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

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForDate(
        Builder $query,
        string $date
    ): Builder {
        return $query->whereDate('invoice_date', $date);
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

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function mechanicCommission(): HasOne
    {
        return $this->hasOne(
            MechanicCommission::class,
            'sale_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            SaleItems::class,
            'sale_id'
        );
    }

    /**
     * Payments entered directly while creating the sales bill.
     */
    public function salePayments(): HasMany
    {
        return $this->hasMany(
            SalePayment::class,
            'sale_id'
        );
    }

    /**
     * Customer receipts recorded after or during the sale.
     * Kept as payments() for compatibility with the current project.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(
            CustomerPayment::class,
            'sale_id'
        );
    }

    public function customerPayments(): HasMany
    {
        return $this->payments();
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(
            CustomerPaymentAllocation::class,
            'sale_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
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

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PARTIAL;
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === self::PAYMENT_UNPAID;
    }

    public function hasBalance(): bool
    {
        return (float) $this->balance_amount > 0;
    }

    public function isMechanicSale(): bool
    {
        return $this->sale_source === 'mechanic'
            && $this->mechanic_id !== null;
    }

    public function isGstBill(): bool
    {
        return $this->billing_type === 'gst';
    }

    public function isNonGstBill(): bool
    {
        return $this->billing_type === 'non_gst';
    }

    /*
    |--------------------------------------------------------------------------
    | Profit Helpers
    |--------------------------------------------------------------------------
    */

    public function getCostTotalAttribute(): ?float
    {
        if (!$this->relationLoaded('items')) {
            return null;
        }

        if ($this->items->contains(
            fn(SaleItems $item) => $item->cost_total === null
        )) {
            return null;
        }

        return round(
            $this->items->sum(
                fn(SaleItems $item) => (float) $item->cost_total
            ),
            2
        );
    }

    public function getGrossProfitAttribute(): ?float
    {
        if (!$this->relationLoaded('items')) {
            return null;
        }

        if ($this->items->contains(
            fn(SaleItems $item) => $item->gross_profit === null
        )) {
            return null;
        }

        return round(
            $this->items->sum(
                fn(SaleItems $item) => (float) $item->gross_profit
            ),
            2
        );
    }

    public function getNetProfitAttribute(): ?float
    {
        $grossProfit = $this->gross_profit;

        if ($grossProfit === null) {
            return null;
        }

        return round(
            $grossProfit
                - (float) $this->final_discount
                + (float) $this->round_off
                - (float) $this->commission_amount,
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Number
    |--------------------------------------------------------------------------
    |
    | Call this method inside the transaction that creates the sale.
    | The unique index on store_id + invoice_no is the final safeguard.
    |
    */

    public static function generateInvoiceNo(int $storeId): string
    {
        $lastInvoice = self::query()
            ->where('store_id', $storeId)
            ->where('invoice_no', 'like', 'INV%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('invoice_no');

        $nextNumber = 1;

        if ($lastInvoice) {
            $numericPart = preg_replace(
                '/\D/',
                '',
                (string) $lastInvoice
            );

            $nextNumber = ((int) $numericPart) + 1;
        }

        do {
            $invoiceNo = 'INV' . str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );

            $exists = self::query()
                ->where('store_id', $storeId)
                ->where('invoice_no', $invoiceNo)
                ->exists();

            $nextNumber++;
        } while ($exists);

        return $invoiceNo;
    }
}
