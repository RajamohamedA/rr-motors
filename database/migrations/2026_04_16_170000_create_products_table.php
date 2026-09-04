<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'products',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Primary Key
                |--------------------------------------------------------------------------
                */

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Business Context
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'company_id'
                )
                    ->constrained(
                        'companies'
                    )
                    ->cascadeOnDelete();


                $table->foreignId(
                    'store_id'
                )
                    ->constrained(
                        'stores'
                    )
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Master References
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'category_id'
                )
                    ->constrained(
                        'categories'
                    )
                    ->restrictOnDelete();


                $table->foreignId(
                    'brand_id'
                )
                    ->nullable()
                    ->constrained(
                        'brands'
                    )
                    ->nullOnDelete();


                $table->foreignId(
                    'unit_id'
                )
                    ->constrained(
                        'units'
                    )
                    ->restrictOnDelete();


                $table->foreignId(
                    'supplier_id'
                )
                    ->nullable()
                    ->constrained(
                        'suppliers'
                    )
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Product Identity
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'product_code',
                    50
                );


                $table->string(
                    'barcode',
                    100
                )
                    ->nullable();


                $table->string(
                    'product_name',
                    255
                );


                $table->string(
                    'part_number',
                    255
                )
                    ->nullable();


                $table->string(
                    'manufacturer',
                    255
                )
                    ->nullable();


                $table->string(
                    'rack_location',
                    255
                )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Tax
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'hsn_code',
                    20
                )
                    ->nullable();


                $table->decimal(
                    'gst_percent',
                    5,
                    2
                )
                    ->default(
                        0
                    );


                /*
                |--------------------------------------------------------------------------
                | Pricing
                |--------------------------------------------------------------------------
                |
                | purchase_price   = buying cost
                | selling_price    = normal retail price
                | wholesale_price  = wholesale / bulk price
                | mechanic_price   = mechanic / workshop price
                | mrp              = MRP reference for billing discount
                |
                */

                $table->decimal(
                    'purchase_price',
                    15,
                    2
                )
                    ->default(
                        0
                    );


                $table->decimal(
                    'selling_price',
                    15,
                    2
                )
                    ->default(
                        0
                    );


                $table->decimal(
                    'wholesale_price',
                    15,
                    2
                )
                    ->default(
                        0
                    );


                $table->decimal(
                    'mechanic_price',
                    15,
                    2
                )
                    ->default(
                        0
                    );


                $table->decimal(
                    'mrp',
                    15,
                    2
                )
                    ->default(
                        0
                    );


                /*
                |--------------------------------------------------------------------------
                | Stock
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | stock_ledgers is the authoritative source for physical stock
                | by Shop / Godown.
                |
                | opening_stock/current_stock are kept for compatibility and
                | product-level summary purposes.
                |
                */

                $table->decimal(
                    'opening_stock',
                    15,
                    3
                )
                    ->default(
                        0
                    );


                $table->decimal(
                    'current_stock',
                    15,
                    3
                )
                    ->default(
                        0
                    );


                $table->decimal(
                    'minimum_stock',
                    15,
                    3
                )
                    ->default(
                        0
                    );


                $table->decimal(
                    'maximum_stock',
                    15,
                    3
                )
                    ->nullable();


                /*
                |--------------------------------------------------------------------------
                | Other Details
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'image',
                    255
                )
                    ->nullable();


                $table->text(
                    'description'
                )
                    ->nullable();


                $table->boolean(
                    'status'
                )
                    ->default(
                        true
                    );


                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Branch Scoped Product Code
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'store_id',
                        'product_code',
                    ],
                    'products_store_id_product_code_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Branch Scoped Barcode
                |--------------------------------------------------------------------------
                |
                | barcode is nullable.
                | Multiple NULL values are allowed.
                |
                */

                $table->unique(
                    [
                        'store_id',
                        'barcode',
                    ],
                    'products_store_id_barcode_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Company Index
                |--------------------------------------------------------------------------
                */

                $table->index(
                    'company_id',
                    'products_company_id_index'
                );


                /*
                |--------------------------------------------------------------------------
                | Business Context Index
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'status',
                    ],
                    'products_company_store_status_index'
                );


                /*
                |--------------------------------------------------------------------------
                | Product Search / Filter Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'store_id',
                        'category_id',
                    ],
                    'products_store_category_index'
                );


                $table->index(
                    [
                        'store_id',
                        'brand_id',
                    ],
                    'products_store_brand_index'
                );


                $table->index(
                    [
                        'store_id',
                        'unit_id',
                    ],
                    'products_store_unit_index'
                );


                $table->index(
                    [
                        'store_id',
                        'supplier_id',
                    ],
                    'products_store_supplier_index'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'products'
        );
    }
};
