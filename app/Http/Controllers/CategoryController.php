<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\AppResponse;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Inertia\Inertia;
use Inertia\Response;


class CategoryController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:category.view',
                only: [
                    'index',
                    'show',
                ]
            ),

            new Middleware(
                'permission:category.create',
                only: [
                    'create',
                    'store',
                ]
            ),

            new Middleware(
                'permission:category.edit',
                only: [
                    'edit',
                    'update',
                    'toggleStatus',
                ]
            ),

            new Middleware(
                'permission:category.delete',
                only: [
                    'destroy',
                ]
            ),

            /*
            |--------------------------------------------------------------------------
            | Related Products
            |--------------------------------------------------------------------------
            */

            new Middleware(
                'permission:product.view',
                only: [
                    'products',
                    'printProducts',
                    'exportProductsCsv',
                    'exportProductsPdf',
                    
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


        $status =
            $request->input(
                'status'
            );


        /*
        |--------------------------------------------------------------------------
        | Category List
        |--------------------------------------------------------------------------
        */

        $categories =
            Category::query()

            ->forContext(
                $companyId,
                $storeId
            )

            ->withCount(
                'products'
            )

            ->when(
                $search !== '',
                function (
                    $query
                ) use (
                    $search
                ) {

                    $query->where(
                        function (
                            $subQuery
                        ) use (
                            $search
                        ) {

                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== null
                    &&
                    $status !== '',
                function (
                    $query
                ) use (
                    $status
                ) {

                    $query->where(
                        'status',
                        (bool) ((int) $status)
                    );
                }
            )

            ->orderBy(
                'name'
            )

            ->paginate(
                15
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            Category::query()

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

            'with_products' => (clone $baseQuery)
                ->whereHas(
                    'products'
                )
                ->count(),
        ];


        return Inertia::render(
            'Categories/Index',
            [

                'categories' =>
                $categories,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status,
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
        return Inertia::render(
            'Categories/Create'
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


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'categories',
                        'name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        ),
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $validated['name'] =
            trim(
                $validated['name']
            );


        $validated['description'] =
            isset(
                $validated['description']
            )
            ? trim(
                $validated['description']
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(
                function () use (
                    $companyId,
                    $storeId,
                    $validated
                ) {

                    Category::create([

                        'company_id' =>
                        $companyId,

                        'store_id' =>
                        $storeId,

                        'code' =>
                        $this->nextCategoryCode(
                            $companyId,
                            $storeId
                        ),

                        'name' =>
                        $validated['name'],

                        'description' =>
                        $validated['description']
                            ?? null,

                        'status' =>
                        $validated['status'],
                    ]);
                }
            );
        } catch (
            QueryException $exception
        ) {

            if (
                $this->isDuplicateException(
                    $exception
                )
            ) {

                return AppResponse::error(
                    'A category with this name already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Category could not be created. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'categories.index',
            'Category created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Category $category
    ) {

        $this->ensureCategoryAvailable(
            $category
        );


        return redirect()->route(
            'categories.products',
            $category
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Category $category
    ): Response {

        $this->ensureCategoryAvailable(
            $category
        );


        $category->loadCount(
            'products'
        );


        return Inertia::render(
            'Categories/Edit',
            [

                'category' => [

                    'id' =>
                    $category->id,

                    'code' =>
                    $category->code,

                    'name' =>
                    $category->name,

                    'description' =>
                    $category->description,

                    'status' =>
                    (bool) $category->status,

                    'products_count' =>
                    $category->products_count,
                ],
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
        Category $category
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCategoryAvailable(
            $category
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'name' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'categories',
                        'name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        )
                        ->ignore(
                            $category->id
                        ),
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $validated['name'] =
            trim(
                $validated['name']
            );


        $validated['description'] =
            isset(
                $validated['description']
            )
            ? trim(
                $validated['description']
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        try {

            $category->update([

                'name' =>
                $validated['name'],

                'description' =>
                $validated['description']
                    ?? null,

                'status' =>
                $validated['status'],
            ]);
        } catch (
            QueryException $exception
        ) {

            if (
                $this->isDuplicateException(
                    $exception
                )
            ) {

                return AppResponse::error(
                    'Another category with this name already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Category could not be updated. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'categories.index',
            'Category updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Category $category
    ) {

        $this->ensureCategoryAvailable(
            $category
        );


        $newStatus =
            !$category->status;


        try {

            $category->update([
                'status' =>
                $newStatus,
            ]);
        } catch (
            QueryException $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'Category status could not be updated. Please try again.'
            );
        }


        return AppResponse::success(
            $newStatus
                ? 'Category activated successfully.'
                : 'Category deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Category $category
    ) {

        $this->ensureCategoryAvailable(
            $category
        );


        /*
        |--------------------------------------------------------------------------
        | Prevent Delete If Products Exist
        |--------------------------------------------------------------------------
        */

        if (
            $category
            ->products()
            ->exists()
        ) {

            return AppResponse::error(
                'This category is used by one or more products and cannot be deleted.'
            );
        }


        try {

            $category->delete();
        } catch (
            QueryException $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'Category could not be deleted because it is being used by another record.'
            );
        }


        return AppResponse::successRoute(
            'categories.index',
            'Category deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Category Related Products
    |--------------------------------------------------------------------------
    */

    public function products(
        Request $request,
        Category $category
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCategoryAvailable(
            $category
        );


        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        $status =
            $request->input(
                'status'
            );


        $brandId =
            $request->input(
                'brand_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Product List
        |--------------------------------------------------------------------------
        */

        $products =
            $this->categoryProductsQuery(
                $category,
                $companyId,
                $storeId,
                $search,
                $status,
                $brandId
            )

            ->with([
                'brand:id,brand_name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->orderBy(
                'product_name'
            )

            ->paginate(
                15
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Brands Used In Category
        |--------------------------------------------------------------------------
        */

        $brandIds =
            Product::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'category_id',
                $category->id
            )

            ->whereNotNull(
                'brand_id'
            )

            ->distinct()

            ->pluck(
                'brand_id'
            );


        $brands =
            Brand::query()

            ->where(
                'company_id',
                $companyId
            )

            ->whereIn(
                'id',
                $brandIds
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

        $summaryQuery =
            Product::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'category_id',
                $category->id
            );


        $summary = [

            'total' => (clone $summaryQuery)
                ->count(),

            'active' => (clone $summaryQuery)
                ->where(
                    'status',
                    true
                )
                ->count(),

            'inactive' => (clone $summaryQuery)
                ->where(
                    'status',
                    false
                )
                ->count(),

            /*
             * Temporary stock value.
             *
             * Later replace this with StockLedger current balance.
             */

            'stock_value' =>
            (float) (
                (clone $summaryQuery)

                ->selectRaw(
                    '
                        COALESCE(
                            SUM(
                                opening_stock * purchase_price
                            ),
                            0
                        ) AS stock_value
                        '
                )

                ->value(
                    'stock_value'
                )
                ?? 0
            ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Categories/Products',
            [

                'category' => [

                    'id' =>
                    $category->id,

                    'name' =>
                    $category->name,

                    'code' =>
                    $category->code,

                    'description' =>
                    $category->description,

                    'status' =>
                    (bool) $category->status,
                ],


                'products' =>
                $products,


                'brands' =>
                $brands,


                'summary' =>
                $summary,


                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status
                        ?? '',

                    'brand_id' =>
                    $brandId
                        ?? '',
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Export Category Products CSV
    |--------------------------------------------------------------------------
    */

    public function exportProductsCsv(
        Request $request,
        Category $category
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCategoryAvailable(
            $category
        );


        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        $status =
            $request->input(
                'status'
            );


        $brandId =
            $request->input(
                'brand_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products =
            $this->categoryProductsQuery(
                $category,
                $companyId,
                $storeId,
                $search,
                $status,
                $brandId
            )

            ->with([
                'brand:id,brand_name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->orderBy(
                'product_name'
            )

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */

        $fileName =
            'category-products-'
            .
            str(
                $category->name
            )
            ->slug()
            .
            '.csv';


        /*
        |--------------------------------------------------------------------------
        | CSV Download
        |--------------------------------------------------------------------------
        */

        return response()->streamDownload(
            function () use (
                $products,
                $category
            ) {

                $handle =
                    fopen(
                        'php://output',
                        'w'
                    );


                /*
                 * UTF-8 BOM.
                 */

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );


                fputcsv(
                    $handle,
                    [
                        'Category',
                        'Product Code',
                        'Product Name',
                        'Barcode',
                        'Part Number',
                        'Brand',
                        'Unit',
                        'Supplier',
                        'Manufacturer',
                        'Rack Location',
                        'HSN Code',
                        'GST %',
                        'Purchase Price',
                        'Selling Price',
                        'MRP',
                        'Opening Stock',
                        'Minimum Stock',
                        'Maximum Stock',
                        'Status',
                    ]
                );


                foreach (
                    $products as $product
                ) {

                    fputcsv(
                        $handle,
                        [
                            $category->name,

                            $product->product_code,

                            $product->product_name,

                            $product->barcode,

                            $product->part_number,

                            $product->brand?->brand_name,

                            $product->unit?->unit_name,

                            $product->supplier?->supplier_name,

                            $product->manufacturer,

                            $product->rack_location,

                            $product->hsn_code,

                            $product->gst_percent,

                            $product->purchase_price,

                            $product->selling_price,

                            $product->mrp,

                            $product->opening_stock,

                            $product->minimum_stock,

                            $product->maximum_stock,

                            $product->status
                                ? 'Active'
                                : 'Inactive',
                        ]
                    );
                }


                fclose(
                    $handle
                );
            },
            $fileName,
            [
                'Content-Type' =>
                'text/csv; charset=UTF-8',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Export Category Products PDF
    |--------------------------------------------------------------------------
    */

    public function exportProductsPdf(
        Request $request,
        Category $category
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCategoryAvailable(
            $category
        );


        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        $status =
            $request->input(
                'status'
            );


        $brandId =
            $request->input(
                'brand_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products =
            $this->categoryProductsQuery(
                $category,
                $companyId,
                $storeId,
                $search,
                $status,
                $brandId
            )

            ->with([
                'brand:id,brand_name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->orderBy(
                'product_name'
            )

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Selected Brand
        |--------------------------------------------------------------------------
        */

        $selectedBrand =
            null;


        if (
            $brandId !== null
            &&
            $brandId !== ''
        ) {

            $selectedBrand =
                Brand::query()

                ->where(
                    'company_id',
                    $companyId
                )

                ->where(
                    'id',
                    (int) $brandId
                )

                ->first([
                    'id',
                    'brand_name',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'categories.products-pdf',
                [

                    'category' =>
                    $category,

                    'products' =>
                    $products,

                    'selectedBrand' =>
                    $selectedBrand,

                    'filters' => [

                        'search' =>
                        $search,

                        'status' =>
                        $status,

                        'brand_id' =>
                        $brandId,
                    ],
                ]
            );


        $pdf->setPaper(
            'a4',
            'landscape'
        );


        /*
        |--------------------------------------------------------------------------
        | File Name
        |--------------------------------------------------------------------------
        */

        $fileName =
            'category-products-'
            .
            str(
                $category->name
            )
            ->slug()
            .
            '.pdf';


        /*
        |--------------------------------------------------------------------------
        | Browser PDF Preview
        |--------------------------------------------------------------------------
        |
        | stream() opens the PDF inside the browser instead of forcing
        | an attachment download.
        |
        */

        return $pdf->stream(
            $fileName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Shared Category Product Query
    |--------------------------------------------------------------------------
    |
    | Used by:
    |
    | Products page
    | CSV export
    | PDF export
    |
    */

    private function categoryProductsQuery(
        Category $category,
        int $companyId,
        int $storeId,
        string $search = '',
        mixed $status = null,
        mixed $brandId = null
    ): Builder {

        return Product::query()

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            ->where(
                'company_id',
                $companyId
            )

            /*
            |--------------------------------------------------------------------------
            | Branch
            |--------------------------------------------------------------------------
            */

            ->where(
                'store_id',
                $storeId
            )

            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */

            ->where(
                'category_id',
                $category->id
            )

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            ->when(
                $search !== '',
                function (
                    $query
                ) use (
                    $search
                ) {

                    $query->where(
                        function (
                            $subQuery
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
                                    'rack_location',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'hsn_code',
                                    'like',
                                    "%{$search}%"
                                )

                                /*
                                |--------------------------------------------------------------------------
                                | Search Brand Name
                                |--------------------------------------------------------------------------
                                */

                                ->orWhereHas(
                                    'brand',
                                    function (
                                        $brandQuery
                                    ) use (
                                        $search
                                    ) {

                                        $brandQuery->where(
                                            'brand_name',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                )

                                /*
                                |--------------------------------------------------------------------------
                                | Search Unit
                                |--------------------------------------------------------------------------
                                */

                                ->orWhereHas(
                                    'unit',
                                    function (
                                        $unitQuery
                                    ) use (
                                        $search
                                    ) {

                                        $unitQuery->where(
                                            function (
                                                $unitSubQuery
                                            ) use (
                                                $search
                                            ) {

                                                $unitSubQuery
                                                    ->where(
                                                        'unit_name',
                                                        'like',
                                                        "%{$search}%"
                                                    )

                                                    ->orWhere(
                                                        'short_name',
                                                        'like',
                                                        "%{$search}%"
                                                    );
                                            }
                                        );
                                    }
                                )

                                /*
                                |--------------------------------------------------------------------------
                                | Search Supplier
                                |--------------------------------------------------------------------------
                                */

                                ->orWhereHas(
                                    'supplier',
                                    function (
                                        $supplierQuery
                                    ) use (
                                        $search
                                    ) {

                                        $supplierQuery->where(
                                            'supplier_name',
                                            'like',
                                            "%{$search}%"
                                        );
                                    }
                                );
                        }
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            ->when(
                $status !== null
                    &&
                    $status !== '',
                function (
                    $query
                ) use (
                    $status
                ) {

                    $query->where(
                        'status',
                        (bool) ((int) $status)
                    );
                }
            )

            /*
            |--------------------------------------------------------------------------
            | Brand
            |--------------------------------------------------------------------------
            */

            ->when(
                $brandId !== null
                    &&
                    $brandId !== '',
                function (
                    $query
                ) use (
                    $brandId
                ) {

                    $query->where(
                        'brand_id',
                        (int) $brandId
                    );
                }
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Active Business Context
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
            !$companyId ||
            !$storeId
        ) {

            abort(
                403,
                'No active company or branch selected.'
            );
        }


        return [
            $companyId,
            $storeId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Category Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureCategoryAvailable(
        Category $category
    ): void {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        if (
            (int) $category->company_id !==
            $companyId
            ||
            (int) $category->store_id !==
            $storeId
        ) {

            abort(
                404
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Category Code
    |--------------------------------------------------------------------------
    */

    private function nextCategoryCode(
        int $companyId,
        int $storeId
    ): string {

        $latestCode =
            Category::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->whereNotNull(
                'code'
            )

            ->lockForUpdate()

            ->orderByDesc(
                'id'
            )

            ->value(
                'code'
            );


        $nextNumber =
            1;


        if (
            $latestCode
        ) {

            preg_match(
                '/(\d+)$/',
                $latestCode,
                $matches
            );


            if (
                isset(
                    $matches[1]
                )
            ) {

                $nextNumber =
                    ((int) $matches[1])
                    + 1;
            }
        }


        return 'CAT'
            .
            str_pad(
                (string) $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Database Error
    |--------------------------------------------------------------------------
    */

    private function isDuplicateException(
        QueryException $exception
    ): bool {

        return in_array(
            (string) $exception->getCode(),
            [
                '23000',
            ],
            true
        );
    }

    /*
|--------------------------------------------------------------------------
| Print Category Products
|--------------------------------------------------------------------------
*/

    public function printProducts(
        Request $request,
        Category $category
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureCategoryAvailable(
            $category
        );


        /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        $status =
            $request->input(
                'status'
            );


        $brandId =
            $request->input(
                'brand_id'
            );


        /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

        $products =
            $this->categoryProductsQuery(
                $category,
                $companyId,
                $storeId,
                $search,
                $status,
                $brandId
            )

            ->with([
                'brand:id,brand_name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->orderBy(
                'product_name'
            )

            ->get();


        /*
    |--------------------------------------------------------------------------
    | Selected Brand
    |--------------------------------------------------------------------------
    */

        $selectedBrand =
            null;


        if (
            $brandId !== null
            &&
            $brandId !== ''
        ) {

            $selectedBrand =
                Brand::query()

                ->where(
                    'company_id',
                    $companyId
                )

                ->where(
                    'id',
                    (int) $brandId
                )

                ->first([
                    'id',
                    'brand_name',
                ]);
        }


        /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

        $summary = [

            'total' =>
            $products->count(),

            'active' =>
            $products
                ->where(
                    'status',
                    true
                )
                ->count(),

            'inactive' =>
            $products
                ->where(
                    'status',
                    false
                )
                ->count(),
        ];


        /*
    |--------------------------------------------------------------------------
    | Blade Print View
    |--------------------------------------------------------------------------
    */

        return view(
            'categories.products-print',
            [

                'category' =>
                $category,

                'products' =>
                $products,

                'selectedBrand' =>
                $selectedBrand,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status,

                    'brand_id' =>
                    $brandId,
                ],
            ]
        );
    }
}
