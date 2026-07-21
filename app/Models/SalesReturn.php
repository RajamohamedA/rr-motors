<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{

    protected $fillable = [
                'company_id',
                'sale_id',
                'customer_id',
                'return_no',
                'return_date',
                'total_amount',
                'remarks'
             ];

    public function sale()
    {
        return $this->belongsTo(Sales::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function items()
    {
        return $this->hasMany(SaleReturnItem::class,'sale_return_id');
    }
}
