<?php

namespace App\Http\Controllers;

use App\Models\Brand;
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

use App\Models\Category;
use App\Models\Company;
use App\Models\Store;

use Inertia\Inertia;
use Inertia\Response;


class BrandController extends Controller implements HasMiddleware
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
                'permission:brand.view',
                only: [
                    'index',
                    'show',
                ]
            ),

            new Middleware(
                'permission:brand.create',
                only: [
                    'create',
                    'store',
                ]
            ),

            new Middleware(
                'permission:brand.edit',
                only: [
                    'edit',
                    'update',
                    'toggleStatus',
                ]
            ),

            new Middleware(
                'permission:brand.delete',
                only: [
                    'destroy',
                ]
            ),

            new Middleware(
                'permission:product.view',
                only: [
                    'products',
                    'exportProductsCsv',
                    'exportProductsPdf',
                    'printProducts',
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


        $brands =
            Brand::query()

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
                                    'brand_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'brand_code',
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
                'brand_name'
            )

            ->paginate(
                15
            )

            ->withQueryString();


        $baseQuery =
            Brand::query()
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
            'Brands/Index',
            [
                'brands' =>
                $brands,

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
            'Brands/Create'
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
            $request->validate([

                'brand_name' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'brands',
                        'brand_name'
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


        $validated['brand_name'] =
            trim(
                $validated['brand_name']
            );


        $validated['description'] =
            filled(
                $validated['description']
                    ?? null
            )
            ? trim(
                $validated['description']
            )
            : null;


        try {

            DB::transaction(
                function () use (
                    $companyId,
                    $storeId,
                    $validated
                ) {

                    Brand::create([

                        'company_id' =>
                        $companyId,

                        'store_id' =>
                        $storeId,

                        'brand_code' =>
                        $this->nextBrandCode(
                            $companyId,
                            $storeId
                        ),

                        'brand_name' =>
                        $validated['brand_name'],

                        'description' =>
                        $validated['description'],

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
                    'A brand with this name already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Brand could not be created. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'brands.index',
            'Brand created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Brand $brand
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureBrandAvailable(
            $brand
        );


        /*
        |--------------------------------------------------------------------------
        | Product Counts
        |--------------------------------------------------------------------------
        */

        $productQuery =
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
                'brand_id',
                $brand->id
            );


        $productsCount =
            (clone $productQuery)
            ->count();


        $activeProductsCount =
            (clone $productQuery)

            ->where(
                'status',
                true
            )

            ->count();


        $inactiveProductsCount =
            (clone $productQuery)

            ->where(
                'status',
                false
            )

            ->count();


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Brands/Show',
            [

                'brand' => [

                    'id' =>
                    $brand->id,

                    'brand_code' =>
                    $brand->brand_code,

                    'brand_name' =>
                    $brand->brand_name,

                    'description' =>
                    $brand->description,

                    'status' =>
                    (bool) $brand->status,

                    'products_count' =>
                    $productsCount,

                    'active_products_count' =>
                    $activeProductsCount,

                    'inactive_products_count' =>
                    $inactiveProductsCount,

                    'created_at' =>
                    $brand->created_at
                        ?->toISOString(),

                    'updated_at' =>
                    $brand->updated_at
                        ?->toISOString(),
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Brand $brand
    ): Response {

        $this->ensureBrandAvailable(
            $brand
        );


        $brand->loadCount(
            'products'
        );


        return Inertia::render(
            'Brands/Edit',
            [
                'brand' => [
                    'id' =>
                    $brand->id,

                    'brand_code' =>
                    $brand->brand_code,

                    'brand_name' =>
                    $brand->brand_name,

                    'description' =>
                    $brand->description,

                    'status' =>
                    (bool) $brand->status,

                    'products_count' =>
                    $brand->products_count,
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
        Brand $brand
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureBrandAvailable(
            $brand
        );


        $validated =
            $request->validate([

                'brand_name' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'brands',
                        'brand_name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        )
                        ->ignore(
                            $brand->id
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


        $validated['brand_name'] =
            trim(
                $validated['brand_name']
            );


        $validated['description'] =
            filled(
                $validated['description']
                    ?? null
            )
            ? trim(
                $validated['description']
            )
            : null;


        try {

            $brand->update([
                'brand_name' =>
                $validated['brand_name'],

                'description' =>
                $validated['description'],

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
                    'Another brand with this name already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Brand could not be updated. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'brands.index',
            'Brand updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Brand $brand
    ) {

        $this->ensureBrandAvailable(
            $brand
        );


        $newStatus =
            !$brand->status;


        $brand->update([
            'status' =>
            $newStatus,
        ]);


        return AppResponse::success(
            $newStatus
                ? 'Brand activated successfully.'
                : 'Brand deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Brand $brand
    ) {

        $this->ensureBrandAvailable(
            $brand
        );


        if (
            $brand
            ->products()
            ->exists()
        ) {

            return AppResponse::error(
                'This brand is used by one or more products and cannot be deleted.'
            );
        }


        try {

            $brand->delete();
        } catch (
            QueryException $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'Brand could not be deleted because it is being used by another record.'
            );
        }


        return AppResponse::successRoute(
            'brands.index',
            'Brand deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Brand Products
    |--------------------------------------------------------------------------
    */

    public function products(
        Request $request,
        Brand $brand
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureBrandAvailable(
            $brand
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


        $categoryId =
            $request->input(
                'category_id'
            );


        $products =
            $this->brandProductsQuery(
                $brand,
                $companyId,
                $storeId,
                $search,
                $status,
                $categoryId
            )

            ->with([
                'category:id,name',
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
                'brand_id',
                $brand->id
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
        ];


        return Inertia::render(
            'Brands/Products',
            [
                'brand' => [
                    'id' =>
                    $brand->id,

                    'brand_code' =>
                    $brand->brand_code,

                    'brand_name' =>
                    $brand->brand_name,

                    'description' =>
                    $brand->description,

                    'status' =>
                    (bool) $brand->status,
                ],

                'products' =>
                $products,

                'summary' =>
                $summary,

                'filters' => [
                    'search' =>
                    $search,

                    'status' =>
                    $status ?? '',

                    'category_id' =>
                    $categoryId ?? '',
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CSV
    |--------------------------------------------------------------------------
    */

    public function exportProductsCsv(
        Request $request,
        Brand $brand
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureBrandAvailable(
            $brand
        );


        $products =
            $this->brandProductsQuery(
                $brand,
                $companyId,
                $storeId,
                trim(
                    (string) $request->input(
                        'search',
                        ''
                    )
                ),
                $request->input(
                    'status'
                ),
                $request->input(
                    'category_id'
                )
            )

            ->with([
                'category:id,name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->orderBy(
                'product_name'
            )

            ->get();


        $fileName =
            'brand-products-'
            .
            str(
                $brand->brand_name
            )->slug()
            .
            '.csv';


        return response()->streamDownload(
            function () use (
                $products,
                $brand
            ) {

                $handle =
                    fopen(
                        'php://output',
                        'w'
                    );


                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );


                fputcsv(
                    $handle,
                    [
                        'Brand',
                        'Product Code',
                        'Product Name',
                        'Category',
                        'Part Number',
                        'Unit',
                        'Supplier',
                        'Purchase Price',
                        'Selling Price',
                        'MRP',
                        'GST %',
                        'Status',
                    ]
                );


                foreach (
                    $products as $product
                ) {

                    fputcsv(
                        $handle,
                        [
                            $brand->brand_name,
                            $product->product_code,
                            $product->product_name,
                            $product->category?->name,
                            $product->part_number,
                            $product->unit?->unit_name,
                            $product->supplier?->supplier_name,
                            $product->purchase_price,
                            $product->selling_price,
                            $product->mrp,
                            $product->gst_percent,
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
            $fileName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function exportProductsPdf(
        Request $request,
        Brand $brand
    ) {

        /*
        |--------------------------------------------------------------------------
        | Business Context
        |--------------------------------------------------------------------------
        */

        [
            $companyId,
            $storeId,
        ] = $this->context();


        /*
        |--------------------------------------------------------------------------
        | Brand Access
        |--------------------------------------------------------------------------
        */

        $this->ensureBrandAvailable(
            $brand
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


        $categoryId =
            $request->input(
                'category_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products =
            $this->brandProductsQuery(
                $brand,
                $companyId,
                $storeId,
                $search,
                $status,
                $categoryId
            )

            ->with([
                'category:id,name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->orderBy(
                'product_name'
            )

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()

            ->whereKey(
                $companyId
            )

            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        */

        $store =
            Store::query()

            ->where(
                'company_id',
                $companyId
            )

            ->whereKey(
                $storeId
            )

            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Selected Category
        |--------------------------------------------------------------------------
        */

        $selectedCategory =
            null;


        if (
            $categoryId !== null
            &&
            $categoryId !== ''
        ) {

            $selectedCategory =
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
                    (int) $categoryId
                )

                ->first([
                    'id',
                    'name',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'brands.products-pdf',
                [

                    'company' =>
                    $company,

                    'store' =>
                    $store,

                    'brand' =>
                    $brand,

                    'products' =>
                    $products,

                    'selectedCategory' =>
                    $selectedCategory,

                    'filters' => [

                        'search' =>
                        $search,

                        'status' =>
                        $status,

                        'category_id' =>
                        $categoryId,
                    ],
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Paper
        |--------------------------------------------------------------------------
        */

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
            'brand-products-'
            .
            str(
                $brand->brand_name
            )->slug()
            .
            '.pdf';


        /*
        |--------------------------------------------------------------------------
        | Stream
        |--------------------------------------------------------------------------
        |
        | Stream opens the PDF in the browser instead of forcing a download.
        |
        */

        return $pdf->stream(
            $fileName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function printProducts(
        Request $request,
        Brand $brand
    ) {
        /*
        |--------------------------------------------------------------------------
        | Business Context
        |--------------------------------------------------------------------------
        */

        [
            $companyId,
            $storeId,
        ] = $this->context();


        /*
        |--------------------------------------------------------------------------
        | Brand Access
        |--------------------------------------------------------------------------
        */

        $this->ensureBrandAvailable(
            $brand
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


        $categoryId =
            $request->input(
                'category_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $products =
            $this->brandProductsQuery(
                $brand,
                $companyId,
                $storeId,
                $search,
                $status,
                $categoryId
            )

            ->with([
                'category:id,name',
                'unit:id,unit_name,short_name',
                'supplier:id,supplier_name',
            ])

            ->orderBy(
                'product_name'
            )

            ->get();


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->whereKey(
                $companyId
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Store / Branch
        |--------------------------------------------------------------------------
        */

        $store =
            Store::query()
            ->where(
                'company_id',
                $companyId
            )
            ->whereKey(
                $storeId
            )
            ->firstOrFail();


        /*
        |--------------------------------------------------------------------------
        | Selected Category
        |--------------------------------------------------------------------------
        */

        $selectedCategory =
            null;


        if (
            $categoryId !== null
            &&
            $categoryId !== ''
        ) {

            $selectedCategory =
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
                    (int) $categoryId
                )

                ->first([
                    'id',
                    'name',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Render Print View
        |--------------------------------------------------------------------------
        */

        return view(
            'brands.products-print',
            [
                'company' =>
                $company,

                'store' =>
                $store,

                'brand' =>
                $brand,

                'products' =>
                $products,

                'selectedCategory' =>
                $selectedCategory,

                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status,

                    'category_id' =>
                    $categoryId,
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Shared Product Query
    |--------------------------------------------------------------------------
    */

    private function brandProductsQuery(
        Brand $brand,
        int $companyId,
        int $storeId,
        string $search = '',
        mixed $status = null,
        mixed $categoryId = null
    ): Builder {

        return Product::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->where(
                'brand_id',
                $brand->id
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
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== null
                    &&
                    $status !== '',
                fn($query) =>
                $query->where(
                    'status',
                    (bool) ((int) $status)
                )
            )

            ->when(
                $categoryId !== null
                    &&
                    $categoryId !== '',
                fn($query) =>
                $query->where(
                    'category_id',
                    (int) $categoryId
                )
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Context
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
    | Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureBrandAvailable(
        Brand $brand
    ): void {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        if (
            (int) $brand->company_id !==
            $companyId
            ||
            (int) $brand->store_id !==
            $storeId
        ) {

            abort(
                404
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Brand Code
    |--------------------------------------------------------------------------
    */

    private function nextBrandCode(
        int $companyId,
        int $storeId
    ): string {

        $latestCode =
            Brand::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'store_id',
                $storeId
            )

            ->whereNotNull(
                'brand_code'
            )

            ->lockForUpdate()

            ->orderByDesc(
                'id'
            )

            ->value(
                'brand_code'
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


        return 'BRD'
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
    | Duplicate Error
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
}
