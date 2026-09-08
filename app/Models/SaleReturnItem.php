<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
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
        'sale_return_id' => 'integer',
        'sale_item_id' => 'integer',
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

    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(
            SalesReturn::class,
            'sale_return_id'
        );
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(
            SaleItems::class,
            'sale_item_id'
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Quantity Helpers
    |--------------------------------------------------------------------------
    */

    public function totalStockQuantity(): float
    {
        return round(
            (float) $this->quantity
                + (float) $this->free_quantity,
            2
        );
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
    | Amount Helpers
    |--------------------------------------------------------------------------
    */

    public function grossAmount(): float
    {
        return round(
            (float) $this->quantity
                * (float) $this->sale_price,
            2
        );
    }

    public function calculatedTaxableAmount(): float
    {
        return round(
            max(
                0,
                $this->grossAmount()
                    - (float) $this->discount_amount
            ),
            2
        );
    }

    public function calculatedGstAmount(): float
    {
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
