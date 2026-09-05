<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use App\Support\AppResponse;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use Inertia\Inertia;
use Inertia\Response;


class ProductController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:product.view',
                only: [
                    'index',
                ]
            ),

            new Middleware(
                'permission:product.show',
                only: [
                    'show',
                ]
            ),

            new Middleware(
                'permission:product.create',
                only: [
                    'create',
                    'store',
                ]
            ),

            new Middleware(
                'permission:product.edit',
                only: [
                    'edit',
                    'update',
                    'toggleStatus',
                ]
            ),

            new Middleware(
                'permission:product.delete',
                only: [
                    'destroy',
                ]
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        $categoryId =
            $request->input(
                'category_id'
            );


        $brandId =
            $request->input(
                'brand_id'
            );


        $status =
            $request->input(
                'status'
            );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products =
            Product::query()

            ->forContext(
                $companyId,
                $storeId
            )

            ->with([
                'category:id,name',
                'brand:id,brand_name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->when(
                $search !== '',
                function (
                    Builder $query
                ) use (
                    $search
                ) {

                    $query->where(
                        function (
                            Builder $subQuery
                        ) use (
                            $search
                        ) {

                            $subQuery
                                ->where(
                                    'product_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'product_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'barcode',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
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
            )

            ->when(
                filled(
                    $categoryId
                ),
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'category_id',
                    (int) $categoryId
                )
            )

            ->when(
                filled(
                    $brandId
                ),
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'brand_id',
                    (int) $brandId
                )
            )

            ->when(
                $status !== null
                    &&
                    $status !== '',
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'status',
                    (bool) ((int) $status)
                )
            )

            ->orderBy(
                'product_name'
            )

            ->paginate(
                20
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Current Stock
        |--------------------------------------------------------------------------
        */

        $products
            ->getCollection()
            ->transform(
                function (
                    Product $product
                ) use (
                    $companyId,
                    $storeId
                ) {

                    $stock =
                        $this->stockSummary(
                            $product,
                            $companyId,
                            $storeId
                        );


                    return $this->productData(
                        $product,
                        $stock
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Filter Masters
        |--------------------------------------------------------------------------
        */

        $categories =
            Category::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'status',
                true
            )
            ->orderBy(
                'name'
            )
            ->get([
                'id',
                'name',
            ]);


        $brands =
            Brand::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'status',
                true
            )
            ->orderBy(
                'brand_name'
            )
            ->get([
                'id',
                'brand_name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            Product::query()
            ->forContext(
                $companyId,
                $storeId
            );


        $summary = [

            'total' => (clone $baseQuery)
                ->count(),

            'active' => (clone $baseQuery)
                ->where(
                    'status',
                    true
                )
                ->count(),

            'inactive' => (clone $baseQuery)
                ->where(
                    'status',
                    false
                )
                ->count(),
        ];


        return Inertia::render(
            'Products/Index',
            [

                'products' =>
                $products,

                'categories' =>
                $categories,

                'brands' =>
                $brands,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

                    'category_id' =>
                    $categoryId ?? '',

                    'brand_id' =>
                    $brandId ?? '',

                    'status' =>
                    $status ?? '',
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        [
            $companyId,
            $storeId,
        ] = $this->context();


        return Inertia::render(
            'Products/Create',
            [
                'categories' =>
                $this->categories(
                    $companyId,
                    $storeId
                ),

                'brands' =>
                $this->brands(
                    $companyId,
                    $storeId
                ),

                'units' =>
                $this->units(
                    $companyId,
                    $storeId
                ),

                'suppliers' =>
                $this->suppliers(
                    $companyId,
                    $storeId
                ),

                'godowns' =>
                $this->godowns(
                    $companyId,
                    $storeId
                ),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        [
            $companyId,
            $storeId,
        ] = $this->context();


        $validated =
            $this->validateProduct(
                $request,
                $companyId,
                $storeId
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Related Masters
        |--------------------------------------------------------------------------
        */

        $this->validateMasters(
            $validated,
            $companyId,
            $storeId
        );


        /*
        |--------------------------------------------------------------------------
        | Validate Opening Location
        |--------------------------------------------------------------------------
        */

        $openingStock =
            (float) (
                $validated['opening_stock']
                ?? 0
            );


        $openingGodownId =
            $this->resolveOpeningGodownId(
                $validated,
                $companyId,
                $storeId,
                $openingStock
            );


        /*
        |--------------------------------------------------------------------------
        | Product Code
        |--------------------------------------------------------------------------
        */

        $productCode =
            filled(
                $validated['product_code']
                    ?? null
            )
            ? strtoupper(
                trim(
                    $validated['product_code']
                )
            )
            : $this->generateProductCode(
                $storeId
            );


        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        $imagePath = null;


        if (
            $request->hasFile(
                'image'
            )
        ) {
            $imagePath =
                $request
                ->file(
                    'image'
                )
                ->store(
                    'products',
                    'public'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Create Product + Opening Stock Atomically
        |--------------------------------------------------------------------------
        */

        try {
            $product =
                DB::transaction(
                    function () use (
                        $validated,
                        $companyId,
                        $storeId,
                        $productCode,
                        $imagePath,
                        $openingStock,
                        $openingGodownId
                    ) {
                        $product =
                            Product::create([
                                'company_id' =>
                                $companyId,

                                'store_id' =>
                                $storeId,

                                'category_id' =>
                                $validated['category_id'],

                                'brand_id' =>
                                $validated['brand_id']
                                    ?? null,

                                'unit_id' =>
                                $validated['unit_id'],

                                'supplier_id' =>
                                $validated['supplier_id']
                                    ?? null,

                                'product_code' =>
                                $productCode,

                                'barcode' =>
                                $this->nullableString(
                                    $validated['barcode']
                                        ?? null
                                ),

                                'product_name' =>
                                trim(
                                    $validated['product_name']
                                ),

                                'part_number' =>
                                $this->nullableString(
                                    $validated['part_number']
                                        ?? null
                                ),

                                'manufacturer' =>
                                $this->nullableString(
                                    $validated['manufacturer']
                                        ?? null
                                ),

                                'rack_location' =>
                                $this->nullableString(
                                    $validated['rack_location']
                                        ?? null
                                ),

                                'hsn_code' =>
                                $this->nullableString(
                                    $validated['hsn_code']
                                        ?? null
                                ),

                                'gst_percent' =>
                                $validated['gst_percent'],

                                'purchase_price' =>
                                $validated['purchase_price'],

                                'selling_price' =>
                                $validated['selling_price'],

                                'wholesale_price' =>
                                $validated['wholesale_price'],

                                'mechanic_price' =>
                                $validated['mechanic_price'],

                                'mrp' =>
                                $validated['mrp'],

                                'opening_stock' =>
                                $openingStock,

                                'current_stock' =>
                                $openingStock,

                                'minimum_stock' =>
                                $validated['minimum_stock'],

                                'maximum_stock' =>
                                $validated['maximum_stock']
                                    ?? null,

                                'image' =>
                                $imagePath,

                                'description' =>
                                $this->nullableString(
                                    $validated['description']
                                        ?? null
                                ),

                                'status' =>
                                $validated['status'],
                            ]);


                        if (
                            $openingStock > 0
                        ) {
                            $this->createOpeningStockLedger(
                                $product,
                                $companyId,
                                $storeId,
                                $openingGodownId,
                                $openingStock
                            );
                        }


                        return $product;
                    }
                );
        } catch (
            QueryException $exception
        ) {
            if (
                $imagePath
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $imagePath
                );
            }


            if (
                $this->isDuplicateException(
                    $exception
                )
            ) {
                return AppResponse::error(
                    'Product code or barcode already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Product could not be created. Please try again.',
                true
            );
        } catch (
            \Throwable $exception
        ) {
            if (
                $imagePath
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $imagePath
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Product could not be created. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'products.index',
            "Product {$product->product_name} created successfully."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Product $product
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureProductAvailable(
            $product
        );


        $product->load([
            'category:id,name',
            'brand:id,brand_name',
            'unit:id,unit_name,short_name',
            'supplier:id,supplier_name',
        ]);


        $stock =
            $this->stockSummary(
                $product,
                $companyId,
                $storeId
            );


        return Inertia::render(
            'Products/Show',
            [

                'product' =>
                $this->productData(
                    $product,
                    $stock,
                    true
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Product $product
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureProductAvailable(
            $product
        );


        return Inertia::render(
            'Products/Edit',
            [

                'product' => [

                    'id' =>
                    $product->id,

                    'category_id' =>
                    $product->category_id,

                    'brand_id' =>
                    $product->brand_id,

                    'unit_id' =>
                    $product->unit_id,

                    'supplier_id' =>
                    $product->supplier_id,

                    'product_code' =>
                    $product->product_code,

                    'barcode' =>
                    $product->barcode,

                    'product_name' =>
                    $product->product_name,

                    'part_number' =>
                    $product->part_number,

                    'manufacturer' =>
                    $product->manufacturer,

                    'rack_location' =>
                    $product->rack_location,

                    'hsn_code' =>
                    $product->hsn_code,

                    'gst_percent' =>
                    $product->gst_percent,

                    'purchase_price' =>
                    $product->purchase_price,

                    'selling_price' =>
                    $product->selling_price,

                    'wholesale_price' =>
                    $product->wholesale_price,

                    'mechanic_price' =>
                    $product->mechanic_price,

                    'mrp' =>
                    $product->mrp,

                    'opening_stock' =>
                    $product->opening_stock,

                    'minimum_stock' =>
                    $product->minimum_stock,

                    'maximum_stock' =>
                    $product->maximum_stock,

                    'image' =>
                    $product->image,

                    'description' =>
                    $product->description,

                    'status' =>
                    (bool) $product->status,
                ],

                'categories' =>
                $this->categories(
                    $companyId,
                    $storeId,
                    true
                ),

                'brands' =>
                $this->brands(
                    $companyId,
                    $storeId,
                    true
                ),

                'units' =>
                $this->units(
                    $companyId,
                    $storeId,
                    true
                ),

                'suppliers' =>
                $this->suppliers(
                    $companyId,
                    $storeId,
                    true
                ),

                'godowns' =>
                $this->godowns(
                    $companyId,
                    $storeId,
                    true
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Product $product
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureProductAvailable(
            $product
        );


        $validated =
            $this->validateProduct(
                $request,
                $companyId,
                $storeId,
                $product
            );



        $this->validateMasters(
            $validated,
            $companyId,
            $storeId
        );



        /*
        |--------------------------------------------------------------------------
        | Product Code
        |--------------------------------------------------------------------------
        */

        $productCode =
            filled(
                $validated['product_code']
                    ?? null
            )
            ? strtoupper(
                trim(
                    $validated['product_code']
                )
            )
            : $product->product_code;


        /*
        |--------------------------------------------------------------------------
        | Image
        |--------------------------------------------------------------------------
        */

        $oldImage =
            $product->image;


        $newImage =
            null;


        if (
            $request->hasFile(
                'image'
            )
        ) {

            $newImage =
                $request
                ->file(
                    'image'
                )
                ->store(
                    'products',
                    'public'
                );
        }


        try {

            DB::transaction(
                function () use (
                    $product,
                    $validated,
                    $productCode,
                    $newImage
                ) {

                    $product->update([

                        'category_id' =>
                        $validated['category_id'],

                        'brand_id' =>
                        $validated['brand_id']
                            ?? null,

                        'unit_id' =>
                        $validated['unit_id'],

                        'supplier_id' =>
                        $validated['supplier_id']
                            ?? null,

                        'product_code' =>
                        $productCode,

                        'barcode' =>
                        $this->nullableString(
                            $validated['barcode']
                                ?? null
                        ),

                        'product_name' =>
                        trim(
                            $validated['product_name']
                        ),

                        'part_number' =>
                        $this->nullableString(
                            $validated['part_number']
                                ?? null
                        ),

                        'manufacturer' =>
                        $this->nullableString(
                            $validated['manufacturer']
                                ?? null
                        ),

                        'rack_location' =>
                        $this->nullableString(
                            $validated['rack_location']
                                ?? null
                        ),

                        'hsn_code' =>
                        $this->nullableString(
                            $validated['hsn_code']
                                ?? null
                        ),

                        'gst_percent' =>
                        $validated['gst_percent'],

                        'purchase_price' =>
                        $validated['purchase_price'],

                        'selling_price' =>
                        $validated['selling_price'],

                        'wholesale_price' =>
                        $validated['wholesale_price'],

                        'mechanic_price' =>
                        $validated['mechanic_price'],

                        'mrp' =>
                        $validated['mrp'],

                        /*
                        |--------------------------------------------------------------------------
                        | Opening Stock
                        |--------------------------------------------------------------------------
                        |
                        | Do not rewrite Stock Ledger from Product Edit.
                        | This field is only retained for legacy/reference use.
                        |
                        */

                        'minimum_stock' =>
                        $validated['minimum_stock'],

                        'maximum_stock' =>
                        $validated['maximum_stock']
                            ?? null,

                        'image' =>
                        $newImage
                            ?? $product->image,

                        'description' =>
                        $this->nullableString(
                            $validated['description']
                                ?? null
                        ),

                        'status' =>
                        $validated['status'],
                    ]);
                }
            );
        } catch (
            QueryException $exception
        ) {

            if (
                $newImage
            ) {

                Storage::disk(
                    'public'
                )->delete(
                    $newImage
                );
            }


            if (
                $this->isDuplicateException(
                    $exception
                )
            ) {

                return AppResponse::error(
                    'Product code or barcode already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Product could not be updated. Please try again.',
                true
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Previous Image
        |--------------------------------------------------------------------------
        */

        if (
            $newImage
            &&
            $oldImage
            &&
            $oldImage !== $newImage
        ) {

            Storage::disk(
                'public'
            )->delete(
                $oldImage
            );
        }


        return AppResponse::successRoute(
            'products.index',
            'Product updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Product $product
    ) {

        $this->ensureProductAvailable(
            $product
        );


        $product->update([

            'status' =>
            !$product->status,
        ]);


        return AppResponse::success(
            $product->status
                ? 'Product activated successfully.'
                : 'Product deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Product $product
    ) {

        $this->ensureProductAvailable(
            $product
        );


        /*
        |--------------------------------------------------------------------------
        | Stock Ledger Protection
        |--------------------------------------------------------------------------
        */

        if (
            DB::table(
                'stock_ledgers'
            )
            ->where(
                'product_id',
                $product->id
            )
            ->exists()
        ) {

            return AppResponse::error(
                'This product has stock transaction history and cannot be deleted. Deactivate it instead.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Purchase Protection
        |--------------------------------------------------------------------------
        */

        if (
            DB::table(
                'purchase_items'
            )
            ->where(
                'product_id',
                $product->id
            )
            ->exists()
        ) {

            return AppResponse::error(
                'This product has purchase history and cannot be deleted. Deactivate it instead.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Sales Protection
        |--------------------------------------------------------------------------
        */

        if (
            DB::table(
                'sale_items'
            )
            ->where(
                'product_id',
                $product->id
            )
            ->exists()
        ) {

            return $this->deleteProtectedResponse(
                'This product has sales history and cannot be deleted. Deactivate it instead.'
            );
        }


        $image =
            $product->image;


        try {

            $product->delete();
        } catch (
            QueryException $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'This product is being used by another transaction and cannot be deleted.'
            );
        }


        if (
            $image
        ) {

            Storage::disk(
                'public'
            )->delete(
                $image
            );
        }


        return AppResponse::successRoute(
            'products.index',
            'Product deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product Search API
    |--------------------------------------------------------------------------
    |
    | Used by:
    |
    | Purchase
    | Sales
    | Quotation
    | Stock Adjustment
    | Stock Transfer
    |
    */

    public function search(
        Request $request
    ): JsonResponse {

        $user =
            $request->user();


        abort_unless(
            $user
                &&
                (
                    $user->is_super_user
                    ||
                    $user->hasRole(
                        'Company Admin'
                    )
                    ||
                    $user->can(
                        'product.view'
                    )
                    ||
                    $user->can(
                        'purchase.create'
                    )
                    ||
                    $user->can(
                        'purchase.edit'
                    )
                    ||
                    $user->can(
                        'sales.create'
                    )
                    ||
                    $user->can(
                        'sales.edit'
                    )
                    ||
                    $user->can(
                        'quotation.create'
                    )
                    ||
                    $user->can(
                        'quotation.edit'
                    )
                    ||
                    $user->can(
                        'stock-adjustment.create'
                    )
                ),
            403
        );


        [
            $companyId,
            $storeId,
        ] = $this->context();


        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        if (
            mb_strlen(
                $search
            ) < 1
        ) {

            return response()->json(
                []
            );
        }


        $products =
            Product::query()

            ->forContext(
                $companyId,
                $storeId
            )

            ->where(
                'status',
                true
            )

            ->where(
                function (
                    Builder $query
                ) use (
                    $search
                ) {

                    $query
                        ->where(
                            'product_code',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'barcode',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'product_name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'part_number',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'manufacturer',
                            'like',
                            "%{$search}%"
                        );
                }
            )

            ->with([
                'unit:id,unit_name,short_name',
                'brand:id,brand_name',
                'category:id,name',
            ])

            ->orderBy(
                'product_name'
            )

            ->limit(
                20
            )

            ->get();


        $result =
            $products
            ->map(
                function (
                    Product $product
                ) use (
                    $companyId,
                    $storeId
                ) {

                    $stock =
                        $this->stockSummary(
                            $product,
                            $companyId,
                            $storeId
                        );


                    return [

                        'id' =>
                        $product->id,

                        'product_code' =>
                        $product->product_code,

                        'barcode' =>
                        $product->barcode,

                        'product_name' =>
                        $product->product_name,

                        'part_number' =>
                        $product->part_number,

                        'manufacturer' =>
                        $product->manufacturer,

                        'hsn_code' =>
                        $product->hsn_code,

                        'gst_percent' =>
                        (float) $product->gst_percent,

                        /*
                            |--------------------------------------------------------------------------
                            | Pricing
                            |--------------------------------------------------------------------------
                            */

                        'purchase_price' =>
                        (float) $product->purchase_price,

                        'selling_price' =>
                        (float) $product->selling_price,

                        'wholesale_price' =>
                        (float) $product->wholesale_price,

                        'mechanic_price' =>
                        (float) $product->mechanic_price,

                        'mrp' =>
                        (float) $product->mrp,

                        /*
                            |--------------------------------------------------------------------------
                            | MRP Discounts
                            |--------------------------------------------------------------------------
                            */

                        'retail_discount' =>
                        $this->discountData(
                            (float) $product->mrp,
                            (float) $product->selling_price
                        ),

                        'wholesale_discount' =>
                        $this->discountData(
                            (float) $product->mrp,
                            (float) (
                                $product->wholesale_price > 0
                                ? $product->wholesale_price
                                : $product->selling_price
                            )
                        ),

                        'mechanic_discount' =>
                        $product->mechanic_price > 0
                            ? $this->discountData(
                                (float) $product->mrp,
                                (float) $product->mechanic_price
                            )
                            : $this->discountData(
                                (float) $product->mrp,
                                (float) $product->selling_price
                            ),

                        /*
                            |--------------------------------------------------------------------------
                            | Stock
                            |--------------------------------------------------------------------------
                            */

                        'current_stock' =>
                        $stock['total'],

                        'shop_stock' =>
                        $stock['shop'],

                        'godown_stock' =>
                        $stock['godown'],

                        'minimum_stock' =>
                        (float) $product->minimum_stock,

                        /*
                            |--------------------------------------------------------------------------
                            | Masters
                            |--------------------------------------------------------------------------
                            */

                        'unit' =>
                        $product->unit,

                        'brand' =>
                        $product->brand,

                        'category' =>
                        $product->category,
                    ];
                }
            );


        return response()->json(
            $result
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateProduct(
        Request $request,
        int $companyId,
        int $storeId,
        ?Product $product = null
    ): array {

        $productId =
            $product?->id;


        return $request->validate([

            'category_id' => [
                'required',
                'integer',
            ],

            'brand_id' => [
                'nullable',
                'integer',
            ],

            'unit_id' => [
                'required',
                'integer',
            ],

            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'product_code' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique(
                    'products',
                    'product_code'
                )
                    ->where(
                        fn($query) =>
                        $query->where(
                            'store_id',
                            $storeId
                        )
                    )
                    ->ignore(
                        $productId
                    ),
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:100',

                Rule::unique(
                    'products',
                    'barcode'
                )
                    ->where(
                        fn($query) =>
                        $query->where(
                            'store_id',
                            $storeId
                        )
                    )
                    ->ignore(
                        $productId
                    ),
            ],

            'product_name' => [
                'required',
                'string',
                'max:255',
            ],

            'part_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'manufacturer' => [
                'nullable',
                'string',
                'max:255',
            ],

            'rack_location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'hsn_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'gst_percent' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Prices
            |--------------------------------------------------------------------------
            */

            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'selling_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'wholesale_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'mechanic_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'mrp' => [
                'required',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Stock Limits
            |--------------------------------------------------------------------------
            */

            'opening_stock' => [
                'required',
                'numeric',
                'min:0',
            ],

            'opening_location' => [
                Rule::requiredIf(
                    $product === null
                ),
                'nullable',
                Rule::in([
                    'shop',
                    'godown',
                ]),
            ],

            'godown_id' => [
                Rule::requiredIf(
                    $product === null
                        &&
                        $request->input('opening_location') === 'godown'
                        &&
                        (float) $request->input('opening_stock', 0) > 0
                ),
                'nullable',
                'integer',
            ],

            'minimum_stock' => [
                'required',
                'numeric',
                'min:0',
            ],

            'maximum_stock' => [
                'nullable',
                'numeric',
                'gte:minimum_stock',
            ],

            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:3072',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Related Masters
    |--------------------------------------------------------------------------
    */

    private function validateMasters(
        array $data,
        int $companyId,
        int $storeId
    ): void {

        /*
    |--------------------------------------------------------------------------
    | Category
    |--------------------------------------------------------------------------
    */

        $categoryExists =
            Category::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->whereKey(
                $data['category_id']
            )
            ->exists();


        if (
            !$categoryExists
        ) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                'category_id' =>
                'The selected category does not belong to the active branch.',
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Brand
    |--------------------------------------------------------------------------
    */

        if (
            filled(
                $data['brand_id']
                    ?? null
            )
        ) {

            $brandExists =
                Brand::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'store_id',
                    $storeId
                )
                ->whereKey(
                    $data['brand_id']
                )
                ->exists();


            if (
                !$brandExists
            ) {

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'brand_id' =>
                    'The selected brand does not belong to the active branch.',
                ]);
            }
        }


        /*
    |--------------------------------------------------------------------------
    | Unit
    |--------------------------------------------------------------------------
    */

        $unitExists =
            Unit::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->whereKey(
                $data['unit_id']
            )
            ->exists();


        if (
            !$unitExists
        ) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                'unit_id' =>
                'The selected unit does not belong to the active branch.',
            ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Supplier
    |--------------------------------------------------------------------------
    */

        if (
            filled(
                $data['supplier_id']
                    ?? null
            )
        ) {

            $supplierExists =
                Supplier::query()
                ->where(
                    'company_id',
                    $companyId
                )
                ->whereKey(
                    $data['supplier_id']
                )
                ->whereHas(
                    'stores',
                    function (
                        Builder $query
                    ) use (
                        $storeId
                    ) {

                        $query->where(
                            'stores.id',
                            $storeId
                        );
                    }
                )
                ->exists();


            if (
                !$supplierExists
            ) {

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'supplier_id' =>
                    'The selected supplier is not available for the active branch.',
                ]);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    private function categories(
        int $companyId,
        int $storeId,
        bool $includeInactive = false
    ) {

        return Category::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->when(
                !$includeInactive,
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'status',
                    true
                )
            )

            ->orderBy(
                'name'
            )

            ->get([
                'id',
                'name',
                'status',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Brands
    |--------------------------------------------------------------------------
    */

    private function brands(
        int $companyId,
        int $storeId,
        bool $includeInactive = false
    ) {

        return Brand::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->when(
                !$includeInactive,
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'status',
                    true
                )
            )

            ->orderBy(
                'brand_name'
            )

            ->get([
                'id',
                'brand_name',
                'status',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Units
    |--------------------------------------------------------------------------
    */

    private function units(
        int $companyId,
        int $storeId,
        bool $includeInactive = false
    ) {

        return Unit::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->when(
                !$includeInactive,
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'status',
                    true
                )
            )

            ->orderBy(
                'unit_name'
            )

            ->get([
                'id',
                'unit_name',
                'short_name',
                'is_system_unit',
                'status',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Suppliers
    |--------------------------------------------------------------------------
    */

    private function suppliers(
        int $companyId,
        int $storeId,
        bool $includeInactive = false
    ) {

        return Supplier::query()

            ->where(
                'company_id',
                $companyId
            )

            ->whereHas(
                'stores',
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'stores.id',
                    $storeId
                )
            )

            ->when(
                !$includeInactive,
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'status',
                    true
                )
            )

            ->orderBy(
                'supplier_name'
            )

            ->get([
                'id',
                'supplier_name',
                'status',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Godowns
    |--------------------------------------------------------------------------
    */

    private function godowns(
        int $companyId,
        int $storeId,
        bool $includeInactive = false
    ) {
        return Godown::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->when(
                !$includeInactive,
                fn(
                    Builder $query
                ) =>
                $query->where(
                    'status',
                    true
                )
            )
            ->orderBy(
                'godown_name'
            )
            ->get([
                'id',
                'godown_name',
                'godown_code',
                'status',
            ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Product Data
    |--------------------------------------------------------------------------
    */

    private function productData(
        Product $product,
        array $stock,
        bool $full = false
    ): array {

        $data = [

            'id' =>
            $product->id,

            'product_code' =>
            $product->product_code,

            'barcode' =>
            $product->barcode,

            'product_name' =>
            $product->product_name,

            'part_number' =>
            $product->part_number,

            'manufacturer' =>
            $product->manufacturer,

            'rack_location' =>
            $product->rack_location,

            'hsn_code' =>
            $product->hsn_code,

            'gst_percent' =>
            (float) $product->gst_percent,

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            'purchase_price' =>
            (float) $product->purchase_price,

            'selling_price' =>
            (float) $product->selling_price,

            'wholesale_price' =>
            (float) $product->wholesale_price,

            'mechanic_price' =>
            (float) $product->mechanic_price,

            'mrp' =>
            (float) $product->mrp,

            /*
            |--------------------------------------------------------------------------
            | Price Discounts
            |--------------------------------------------------------------------------
            */

            'retail_discount' =>
            $this->discountData(
                (float) $product->mrp,
                (float) $product->selling_price
            ),

            'wholesale_discount' =>
            $this->discountData(
                (float) $product->mrp,
                (float) (
                    $product->wholesale_price > 0
                    ? $product->wholesale_price
                    : $product->selling_price
                )
            ),

            'mechanic_discount' =>
            $this->discountData(
                (float) $product->mrp,
                (float) (
                    $product->mechanic_price > 0
                    ? $product->mechanic_price
                    : $product->selling_price
                )
            ),

            /*
            |--------------------------------------------------------------------------
            | Stock
            |--------------------------------------------------------------------------
            */

            'opening_stock' =>
            (float) $product->opening_stock,

            'current_stock' =>
            $stock['total'],

            'shop_stock' =>
            $stock['shop'],

            'godown_stock' =>
            $stock['godown'],

            'minimum_stock' =>
            (float) $product->minimum_stock,

            'maximum_stock' =>
            $product->maximum_stock !== null
                ? (float) $product->maximum_stock
                : null,

            'is_low_stock' =>
            $stock['total'] > 0
                &&
                (float) $product->minimum_stock > 0
                &&
                $stock['total']
                <=
                (float) $product->minimum_stock,

            'is_out_of_stock' =>
            $stock['total'] <= 0,

            /*
            |--------------------------------------------------------------------------
            | Masters
            |--------------------------------------------------------------------------
            */

            'category' =>
            $product->category,

            'brand' =>
            $product->brand,

            'unit' =>
            $product->unit,

            'supplier' =>
            $product->supplier,

            'image' =>
            $product->image,

            'status' =>
            (bool) $product->status,

            'created_at' =>
            $product->created_at
                ?->toISOString(),

            'updated_at' =>
            $product->updated_at
                ?->toISOString(),
        ];


        if (
            $full
        ) {

            $data['description'] =
                $product->description;


            $data['locations'] =
                $stock['locations'];
        }


        return $data;
    }


    /*
    |--------------------------------------------------------------------------
    | Stock Summary
    |--------------------------------------------------------------------------
    |
    | Latest StockLedger balance for:
    |
    | product + shop
    | product + each godown
    |
    */

    private function stockSummary(
        Product $product,
        int $companyId,
        int $storeId
    ): array {

        $latestLedgerIds =
            StockLedger::query()

            ->selectRaw(
                'MAX(id) AS id'
            )

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'product_id',
                $product->id
            )

            ->groupBy(
                'godown_id'
            );


        $ledgers =
            StockLedger::query()

            ->whereIn(
                'id',
                $latestLedgerIds
            )

            ->with(
                'godown:id,godown_name,godown_code'
            )

            ->get();


        $shop =
            0.0;


        $godown =
            0.0;


        $locations =
            [];


        foreach (
            $ledgers as $ledger
        ) {

            $quantity =
                (float) $ledger->balance_qty;


            if (
                $ledger->godown_id === null
            ) {

                $shop =
                    $quantity;


                $locations[] = [

                    'godown_id' =>
                    null,

                    'location_type' =>
                    'shop',

                    'name' =>
                    'Shop',

                    'code' =>
                    null,

                    'quantity' =>
                    $quantity,
                ];


                continue;
            }


            $godown +=
                $quantity;


            $locations[] = [

                'godown_id' =>
                $ledger->godown_id,

                'location_type' =>
                'godown',

                'name' =>
                $ledger->godown?->godown_name
                    ?? 'Godown',

                'code' =>
                $ledger->godown?->godown_code,

                'quantity' =>
                $quantity,
            ];
        }


        return [

            'shop' =>
            round(
                $shop,
                3
            ),

            'godown' =>
            round(
                $godown,
                3
            ),

            'total' =>
            round(
                $shop
                    +
                    $godown,
                3
            ),

            'locations' =>
            $locations,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    */

    private function discountData(
        float $mrp,
        float $price
    ): array {

        if (
            $mrp <= 0
        ) {

            return [

                'amount' =>
                0,

                'percent' =>
                0,
            ];
        }


        $amount =
            max(
                0,
                $mrp - $price
            );


        return [

            'amount' =>
            round(
                $amount,
                2
            ),

            'percent' =>
            round(
                (
                    $amount
                    /
                    $mrp
                ) * 100,
                2
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Opening Godown
    |--------------------------------------------------------------------------
    */

    private function resolveOpeningGodownId(
        array $data,
        int $companyId,
        int $storeId,
        float $openingStock
    ): ?int {
        if (
            $openingStock <= 0
            ||
            ($data['opening_location'] ?? 'shop') === 'shop'
        ) {
            return null;
        }


        $godownId =
            (int) (
                $data['godown_id']
                ?? 0
            );


        if (
            !$godownId
        ) {
            abort(
                422,
                'Please select a Godown for the opening stock.'
            );
        }


        $godown =
            Godown::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'status',
                true
            )
            ->whereKey(
                $godownId
            )
            ->first();


        if (
            !$godown
        ) {
            abort(
                422,
                'The selected Godown is unavailable for this branch.'
            );
        }


        return (int) $godown->id;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Opening Stock Ledger
    |--------------------------------------------------------------------------
    */

    private function createOpeningStockLedger(
        Product $product,
        int $companyId,
        int $storeId,
        ?int $godownId,
        float $openingStock
    ): void {
        $unitCost =
            (float) $product->purchase_price;


        StockLedger::create([
            'company_id' =>
            $companyId,

            'store_id' =>
            $storeId,

            'godown_id' =>
            $godownId,

            'product_id' =>
            $product->id,

            'transaction_date' =>
            now(),

            'transaction_type' =>
            'opening_stock',

            'reference_id' =>
            $product->id,

            'reference_no' =>
            $product->product_code,

            'qty_in' =>
            $openingStock,

            'qty_out' =>
            0,

            'balance_qty' =>
            $openingStock,

            'unit_cost' =>
            $unitCost,

            'total_cost' =>
            round(
                $openingStock
                    *
                    $unitCost,
                2
            ),

            'remarks' =>
            $godownId === null
                ? 'Opening stock added to Shop during product creation.'
                : 'Opening stock added to Godown during product creation.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Product Code
    |--------------------------------------------------------------------------
    */

    private function generateProductCode(
        int $storeId
    ): string {

        return DB::transaction(
            function () use (
                $storeId
            ) {

                $store =
                    Store::query()

                    ->whereKey(
                        $storeId
                    )

                    ->lockForUpdate()

                    ->firstOrFail();


                $lastProductId =
                    Product::query()

                    ->where(
                        'store_id',
                        $storeId
                    )

                    ->max(
                        'id'
                    )

                    ?? 0;


                $nextNumber =
                    ((int) $lastProductId)
                    + 1;


                $prefix =
                    strtoupper(
                        trim(
                            (string) (
                                $store->store_code
                                ?: 'PRD'
                            )
                        )
                    );


                return $prefix
                    .
                    '-P'
                    .
                    str_pad(
                        $nextNumber,
                        6,
                        '0',
                        STR_PAD_LEFT
                    );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureProductAvailable(
        Product $product
    ): void {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        abort_unless(
            (int) $product->company_id
                ===
                $companyId
                &&
                (int) $product->store_id
                ===
                $storeId,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Business Context
    |--------------------------------------------------------------------------
    */

    private function context(): array
    {
        $companyId =
            (int) session(
                'active_company_id'
            );


        $storeId =
            (int) session(
                'active_store_id'
            );


        if (
            !$companyId
            ||
            !$storeId
        ) {

            abort(
                403,
                'No active company or branch is selected.'
            );
        }


        $valid =
            Store::query()

            ->whereKey(
                $storeId
            )

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'status',
                true
            )

            ->exists();


        if (
            !$valid
        ) {

            abort(
                403,
                'The active branch is unavailable or inactive.'
            );
        }


        return [
            $companyId,
            $storeId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Nullable String
    |--------------------------------------------------------------------------
    */

    private function nullableString(
        mixed $value
    ): ?string {

        if (
            !is_string(
                $value
            )
        ) {

            return null;
        }


        $value =
            trim(
                $value
            );


        return $value !== ''
            ? $value
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Exception
    |--------------------------------------------------------------------------
    */

    private function isDuplicateException(
        QueryException $exception
    ): bool {

        return (
            (string) $exception->getCode()
            ===
            '23000'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Protection Response
    |--------------------------------------------------------------------------
    */

    private function deleteProtectedResponse(
        string $message
    ) {

        return AppResponse::error(
            $message
        );
    }
}
