<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    protected $fillable = [
        'company_id',
        'store_id',
        'godown_id',
        'product_id',
        'transaction_date',
        'transaction_type',
        'reference_id',
        'reference_no',
        'qty_in',
        'qty_out',
        'balance_qty',
        'unit_cost',
        'total_cost',
        'remarks',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function godown()
    {
        return $this->belongsTo(Godown::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
}
