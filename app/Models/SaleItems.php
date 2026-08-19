<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Godown;

class SaleItems extends Model
{
    protected $table = 'sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'godown_id',
        'quantity',
        'free_quantity',
        'sale_price',
        'discount',
        'gst_percent',
        'gst_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity'     => 'decimal:3',
        'free_quantity' => 'decimal:3',
        'sale_price'   => 'decimal:2',
        'discount'     => 'decimal:2',
        'gst_percent'  => 'decimal:2',
        'gst_amount'   => 'decimal:2',
        'line_total'   => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sale_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }
}
