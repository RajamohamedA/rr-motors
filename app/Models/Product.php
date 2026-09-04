<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Product extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

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
        'wholesale_price',
        'mechanic_price',
        'mrp',

        'opening_stock',
        'current_stock',
        'minimum_stock',
        'maximum_stock',

        'image',
        'description',

        'status',
    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [

            'company_id' =>
            'integer',

            'store_id' =>
            'integer',

            'category_id' =>
            'integer',

            'brand_id' =>
            'integer',

            'unit_id' =>
            'integer',

            'supplier_id' =>
            'integer',

            'gst_percent' =>
            'decimal:2',

            'purchase_price' =>
            'decimal:2',

            'selling_price' =>
            'decimal:2',

            'wholesale_price' =>
            'decimal:2',

            'mechanic_price' =>
            'decimal:2',

            'mrp' =>
            'decimal:2',

            'opening_stock' =>
            'decimal:2',

            'current_stock' =>
            'decimal:2',

            'minimum_stock' =>
            'decimal:2',

            'maximum_stock' =>
            'decimal:2',

            'status' =>
            'boolean',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store / Branch
    |--------------------------------------------------------------------------
    */

    public function store(): BelongsTo
    {
        return $this->belongsTo(
            Store::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Category
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            Category::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

    public function brand(): BelongsTo
    {
        return $this->belongsTo(
            Brand::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Unit
    |--------------------------------------------------------------------------
    */

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier
    |--------------------------------------------------------------------------
    */

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(
            Supplier::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Purchase Items
    |--------------------------------------------------------------------------
    */

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(
            PurchaseItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Sale Items
    |--------------------------------------------------------------------------
    */

    public function saleItems(): HasMany
    {
        return $this->hasMany(
            SaleItem::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Ledgers
    |--------------------------------------------------------------------------
    */

    public function stockLedgers(): HasMany
    {
        return $this->hasMany(
            StockLedger::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Business Context Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForContext(
        Builder $query,
        int $companyId,
        int $storeId
    ): Builder {

        return $query
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Company Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        Builder $query,
        int $companyId
    ): Builder {

        return $this->scopeForContextCompany(
            $query,
            $companyId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Scope
    |--------------------------------------------------------------------------
    */

    public function scopeForStore(
        Builder $query,
        int $storeId
    ): Builder {

        return $query->where(
            'store_id',
            $storeId
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Scope
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {

        return $query->where(
            'status',
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Scope
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(
        Builder $query,
        ?string $search
    ): Builder {

        $search =
            trim(
                (string) $search
            );


        if (
            $search === ''
        ) {

            return $query;
        }


        return $query->where(
            function (
                Builder $subQuery
            ) use (
                $search
            ) {

                $subQuery
                    ->where(
                        'product_name',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhere(
                        'product_code',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhere(
                        'barcode',
                        'like',
                        "%{$search}%"
                    )

                    ->where(
                        'part_number',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhere(
                        'manufacturer',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhere(
                        'hsn_code',
                        'like',
                        "%{$search}%"
                    );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Retail Price
    |--------------------------------------------------------------------------
    */

    public function retailPrice(): float
    {
        return (float) $this->selling_price;
    }


    /*
    |--------------------------------------------------------------------------
    | Wholesale Price
    |--------------------------------------------------------------------------
    */

    public function wholesalePrice(): float
    {
        $price =
            (float) $this->wholesale_price;


        return $price > 0
            ? $price
            : $this->retailPrice();
    }


    /*
    |--------------------------------------------------------------------------
    | Mechanic Price
    |--------------------------------------------------------------------------
    */

    public function mechanicPrice(): float
    {
        $price =
            (float) $this->mechanic_price;


        return $price > 0
            ? $price
            : $this->retailPrice();
    }


    /*
    |--------------------------------------------------------------------------
    | Price For Customer Type
    |--------------------------------------------------------------------------
    */

    public function priceFor(
        ?string $customerType
    ): float {

        return match (strtolower(
            trim(
                (string) $customerType
            )
        )) {

            'wholesale' =>
            $this->wholesalePrice(),

            'mechanic' =>
            $this->mechanicPrice(),

            default =>
            $this->retailPrice(),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Discount Amount From MRP
    |--------------------------------------------------------------------------
    */

    public function discountAmountFor(
        ?string $customerType
    ): float {

        $mrp =
            (float) $this->mrp;


        if (
            $mrp <= 0
        ) {

            return 0;
        }


        $price =
            $this->priceFor(
                $customerType
            );


        return round(
            max(
                0,
                $mrp - $price
            ),
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Discount Percentage From MRP
    |--------------------------------------------------------------------------
    */

    public function discountPercentFor(
        ?string $customerType
    ): float {

        $mrp =
            (float) $this->mrp;


        if (
            $mrp <= 0
        ) {

            return 0;
        }


        return round(
            (
                $this->discountAmountFor(
                    $this->normalizeCustomerType(
                        $customerType
                    )
                )
                /
                $mrp
            ) * 100,
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Customer Type
    |--------------------------------------------------------------------------
    */

    private function normalizeCustomerType(
        ?string $customerType
    ): string {

        return strtolower(
            trim(
                (string) $customerType
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Company Helper
    |--------------------------------------------------------------------------
    */

    private function scopeForContextCompany(
        Builder $query,
        int $companyId
    ): Builder {

        return $query->where(
            'company_id',
            $currentCompanyId = $companyId
        );
    }
}
