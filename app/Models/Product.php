<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [

        'company_id',
        'store_id',
        'category_id',
        'brand_id',
        'unit_id',
        'supplier_id',

        'product_code',
        'barcode',

        'product_name',
        'part_number',

        'manufacturer',
        'rack_location',

        'hsn_code',
        'gst_percent',

        'purchase_price',
        'selling_price',
        'mrp',

        'opening_stock',
        'minimum_stock',
        'maximum_stock',

        'image',
        'description',

        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}