<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPayment extends Model
{
    use HasFactory;

    protected $fillable = [

        'company_id',
        'store_id',
        'customer_id',
        'sale_id',

        'receipt_no',

        'payment_date',

        'amount',

        'payment_mode',

        'reference_no',

        'remarks',

    ];

    protected $casts = [

        'payment_date' => 'date',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sales::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Receipt Number
    |--------------------------------------------------------------------------
    */

    public static function generateReceiptNo()
    {
        $last = self::latest()->first();

        if (!$last) {
            return 'RCPT000001';
        }

        $number = (int) substr($last->receipt_no, 4);

        return 'RCPT' . str_pad($number + 1, 6, '0', STR_PAD_LEFT);
    }
}
