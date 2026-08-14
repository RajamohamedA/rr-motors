<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = [
        'company_id',
        'store_code',
        'store_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_stores'
        );
    }
    
    public function godowns()
    {
        return $this->hasMany(Godown::class);
    }
}
