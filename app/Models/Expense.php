<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Expense extends Model
{
    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'company_id',
        'store_id',
        'account_id',
        'expense_no',
        'expense_date',
        'expense_category',
        'payee_name',
        'amount',
        'payment_mode',
        'reference_no',
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
            'expense_date' => 'date',
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

    public function accountTransactions(): MorphMany
    {
        return $this->morphMany(
            AccountTransaction::class,
            'reference',
            'reference_type',
            'reference_id'
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
            'expense_date',
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
                        'expense_no',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'expense_category',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'payee_name',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'reference_no',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'remarks',
                        'like',
                        '%' . $search . '%'
                    );
            }
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

    public static function statuses(): array
    {
        return [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Expense Number
    |--------------------------------------------------------------------------
    |
    | Call this method from inside the database transaction that creates the
    | expense. The unique database index remains the final concurrency guard.
    |
    */

    public static function generateExpenseNo(
        int $companyId,
        int $storeId
    ): string {
        $lastExpenseNo = self::query()
            ->forContext($companyId, $storeId)
            ->where('expense_no', 'like', 'EXP%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('expense_no');

        $nextNumber = 1;

        if ($lastExpenseNo) {
            $numericPart = preg_replace(
                '/\D/',
                '',
                (string) $lastExpenseNo
            );

            $nextNumber = ((int) $numericPart) + 1;
        }

        do {
            $expenseNo = 'EXP' . str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );

            $exists = self::query()
                ->forContext($companyId, $storeId)
                ->where('expense_no', $expenseNo)
                ->exists();

            $nextNumber++;
        } while ($exists);

        return $expenseNo;
    }
}
