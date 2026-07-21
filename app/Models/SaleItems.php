<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItems extends Model
{
    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'free_quantity',
        'sale_price',
        'discount',
        'gst_percent',
        'gst_amount',
        'line_total',
    ];

    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id','id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}