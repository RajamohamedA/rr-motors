<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    use HasFactory;

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'company_id',
        'store_id',
        'supplier_id',
        'purchase_id',
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
            'supplier_id' => 'integer',
            'purchase_id' => 'integer',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
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

    public function scopeCompleted(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_COMPLETED
        );
    }

    public function scopeCancelled(
        Builder $query
    ): Builder {
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
            'payment_date',
            [$fromDate, $toDate]
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

    public function isAdvancePayment(): bool
    {
        return $this->purchase_id === null;
    }

    public function canBeCancelled(): bool
    {
        return $this->isCompleted()
            && $this->cancelled_at === null;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Payment Number
    |--------------------------------------------------------------------------
    |
    | Call this method from inside the controller's DB::transaction(). The
    | unique database constraint remains the final protection against two
    | simultaneous requests generating the same number.
    |
    */

    public static function generatePaymentNo(
        int $companyId,
        int $storeId
    ): string {
        $lastPaymentNo = self::query()
            ->forContext($companyId, $storeId)
            ->lockForUpdate()
            ->latest('id')
            ->value('payment_no');

        $lastNumber = $lastPaymentNo
            ? (int) preg_replace(
                '/\D+/',
                '',
                $lastPaymentNo
            )
            : 0;

        return 'SPAY' . str_pad(
            (string) ($lastNumber + 1),
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}
