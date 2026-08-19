<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'company_id',
        'store_id',
        'transfer_no',
        'transfer_date',
        'remarks',
        'status',
        'created_by',
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }


    public function store()
    {
        return $this->belongsTo(Store::class);
    }


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function items()
    {
        return $this->hasMany(
            StockTransferItem::class
        );
    }
}
