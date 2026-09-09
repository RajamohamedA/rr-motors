<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'company_id',
        'store_id',
        'customer_id',
        'document_type',
        'sale_source',
        'mechanic_id',
        'commission_type',
        'commission_percent',
        'commission_amount',
        'quotation_no',
        'quotation_date',
        'valid_until',
        'billing_type',
        'sub_total',
        'gst_total',
        'final_discount',
        'round_off',
        'grand_total',
        'terms_conditions',
        'remarks',
        'status',
        'converted_at',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'company_id' => 'integer',
        'store_id' => 'integer',
        'customer_id' => 'integer',
        'mechanic_id' => 'integer',
        'created_by' => 'integer',
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'converted_at' => 'datetime',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'gst_total' => 'decimal:2',
        'final_discount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            QuotationItem::class,
            'quotation_id'
        );
    }

    public function convertedSale(): HasOne
    {
        return $this->hasOne(
            Sales::class,
            'quotation_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Context Scopes
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
        return $query->where(
            'store_id',
            $storeId
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

    public function scopeQuotationDocuments(Builder $query): Builder
    {
        return $query->where(
            'document_type',
            'quotation'
        );
    }

    public function scopeProformaInvoices(Builder $query): Builder
    {
        return $query->where(
            'document_type',
            'proforma_invoice'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn(
            'status',
            [
                'Rejected',
                'Converted',
                'Expired',
                'Cancelled',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Document Type Helpers
    |--------------------------------------------------------------------------
    */

    public function isQuotation(): bool
    {
        return $this->document_type === 'quotation';
    }

    public function isProformaInvoice(): bool
    {
        return $this->document_type === 'proforma_invoice';
    }

    public function documentTitle(): string
    {
        return $this->isProformaInvoice()
            ? 'Proforma Invoice'
            : 'Quotation';
    }

    /*
    |--------------------------------------------------------------------------
    | Billing Helpers
    |--------------------------------------------------------------------------
    */

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
    | Source Helpers
    |--------------------------------------------------------------------------
    */

    public function isCustomerQuotation(): bool
    {
        return $this->sale_source === 'customer';
    }

    public function isMechanicQuotation(): bool
    {
        return $this->sale_source === 'mechanic'
            && $this->mechanic_id !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'Draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }

    public function isConverted(): bool
    {
        return $this->status === 'Converted';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'Cancelled';
    }

    public function hasExpired(): bool
    {
        return $this->status === 'Expired'
            || (
                $this->valid_until !== null
                && $this->valid_until->isBefore(today())
            );
    }

    public function canBeEdited(): bool
    {
        return in_array(
            $this->status,
            ['Draft', 'Pending', 'Approved'],
            true
        ) && !$this->hasExpired();
    }

    public function canBeConverted(): bool
    {
        return $this->isApproved()
            && !$this->hasExpired()
            && !$this->convertedSale()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Document Number
    |--------------------------------------------------------------------------
    |
    | This method should be called inside the controller's database
    | transaction while creating the quotation.
    |
    */

    public static function generateDocumentNo(
        int $storeId,
        string $documentType = 'quotation'
    ): string {
        $isProforma = $documentType === 'proforma_invoice';
        $prefix = $isProforma ? 'PI' : 'QTN';

        $lastDocument = self::query()
            ->where('store_id', $storeId)
            ->where('document_type', $documentType)
            ->where('quotation_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $lastNumber = $lastDocument
            ? (int) preg_replace(
                '/\D/',
                '',
                (string) $lastDocument->quotation_no
            )
            : 0;

        return $prefix
            . str_pad(
                (string) ($lastNumber + 1),
                6,
                '0',
                STR_PAD_LEFT
            );
    }
}
