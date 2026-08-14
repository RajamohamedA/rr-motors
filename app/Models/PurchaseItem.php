<?php

namespace App\Models;

use App\Models\Unit;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Godown;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [

        'purchase_id',

        'product_id',
        
        'godown_id',

        'quantity',

        'free_quantity',

        'purchase_price',

        'discount',

        'gst_percent',

        'gst_amount',

        'line_total'

    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }
}