<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'product_id',
        'quantity',
        'free_quantity',
        'sale_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'taxable_amount',
        'gst_percent',
        'gst_amount',
        'line_total',
    ];

    protected $casts = [
        'quotation_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'decimal:2',
        'free_quantity' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(
            Quotation::class,
            'quotation_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Discount Helpers
    |--------------------------------------------------------------------------
    */

    public function isPercentageDiscount(): bool
    {
        return $this->discount_type === 'percentage';
    }

    public function isFixedDiscount(): bool
    {
        return $this->discount_type === 'fixed';
    }

    /*
    |--------------------------------------------------------------------------
    | Quantity Helpers
    |--------------------------------------------------------------------------
    */

    public function totalQuantity(): float
    {
        return round(
            (float) $this->quantity
                + (float) $this->free_quantity,
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Calculation Helpers
    |--------------------------------------------------------------------------
    |
    | Free quantity is not included in the billed gross amount.
    |
    */

    public function grossAmount(): float
    {
        return round(
            (float) $this->quantity
                * (float) $this->sale_price,
            2
        );
    }

    public function calculatedDiscountAmount(): float
    {
        $grossAmount = $this->grossAmount();

        if ($this->isPercentageDiscount()) {
            return round(
                min(
                    $grossAmount,
                    $grossAmount
                        * (float) $this->discount_value
                        / 100
                ),
                2
            );
        }

        return round(
            min(
                $grossAmount,
                (float) $this->discount_value
            ),
            2
        );
    }

    public function calculatedTaxableAmount(): float
    {
        return round(
            max(
                0,
                $this->grossAmount()
                    - $this->calculatedDiscountAmount()
            ),
            2
        );
    }

    public function calculatedGstAmount(): float
    {
        if (
            $this->quotation
            && $this->quotation->isNonGstBill()
        ) {
            return 0;
        }

        return round(
            $this->calculatedTaxableAmount()
                * (float) $this->gst_percent
                / 100,
            2
        );
    }

    public function calculatedLineTotal(): float
    {
        return round(
            $this->calculatedTaxableAmount()
                + $this->calculatedGstAmount(),
            2
        );
    }
}
