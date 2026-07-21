<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'product_id',
        'quantity',
        'sale_price',
        'gst_percent',
        'gst_amount',
        'line_total'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class,'sale_return_id');
    }
}
