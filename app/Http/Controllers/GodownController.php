<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Store;
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

use Symfony\Component\HttpFoundation\StreamedResponse;


class GodownController extends Controller implements HasMiddleware
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
                'permission:godown.view',
                only: [
                    'index',
                    'show',
                    'products',
                    'printProducts',
                    'exportProductsPdf',
                    'exportProductsCsv',
                ]
            ),

            new Middleware(
                'permission:godown.create',
                only: [
                    'create',
                    'store',
                ]
            ),

            new Middleware(
                'permission:godown.edit',
                only: [
                    'edit',
                    'update',
                    'toggleStatus',
                ]
            ),

            new Middleware(
                'permission:godown.delete',
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


        $status =
            $request->input(
                'status'
            );


        /*
        |--------------------------------------------------------------------------
        | Godowns
        |--------------------------------------------------------------------------
        */

        $godowns =
            Godown::query()

            ->forContext(
                $companyId,
                $storeId
            )

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
                                    'godown_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'godown_code',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'address',
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
                    Builder $query
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
                'godown_name'
            )

            ->paginate(
                20
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Add Inventory Summary Per Godown
        |--------------------------------------------------------------------------
        */

        $godowns->getCollection()
            ->transform(
                function (
                    Godown $godown
                ) use (
                    $companyId,
                    $storeId
                ) {

                    $inventory =
                        $this->godownInventorySummary(
                            $godown,
                            $companyId,
                            $storeId
                        );


                    return [
                        'id' =>
                        $godown->id,

                        'godown_name' =>
                        $godown->godown_name,

                        'godown_code' =>
                        $godown->godown_code,

                        'address' =>
                        $godown->address,

                        'status' =>
                        (bool) $godown->status,

                        'products_count' =>
                        $inventory['products_count'],

                        'stock_quantity' =>
                        $inventory['stock_quantity'],

                        'estimated_cost' =>
                        $inventory['estimated_cost'],

                        'estimated_selling_value' =>
                        $inventory['estimated_selling_value'],

                        'low_stock_count' =>
                        $inventory['low_stock_count'],

                        'created_at' =>
                        $godown->created_at
                            ?->toISOString(),

                        'updated_at' =>
                        $godown->updated_at
                            ?->toISOString(),
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            Godown::query()
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


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Godowns/Index',
            [

                'godowns' =>
                $godowns,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

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
        return Inertia::render(
            'Godowns/Create'
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

                'godown_name' => [
                    'required',
                    'string',
                    'max:255',

                    Rule::unique(
                        'godowns',
                        'godown_name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        ),
                ],

                'godown_code' => [
                    'nullable',
                    'string',
                    'max:255',

                    Rule::unique(
                        'godowns',
                        'godown_code'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        ),
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:255',
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

        $validated['godown_name'] =
            trim(
                $validated['godown_name']
            );


        $validated['godown_code'] =
            filled(
                $validated['godown_code']
                    ?? null
            )
            ? strtoupper(
                trim(
                    $validated['godown_code']
                )
            )
            : null;


        $validated['address'] =
            filled(
                $validated['address']
                    ?? null
            )
            ? trim(
                $validated['address']
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Generate Code
        |--------------------------------------------------------------------------
        */

        if (
            empty($validated['godown_code'])
        ) {

            $validated['godown_code'] =
                $this->generateGodownCode(
                    $storeId
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        try {

            Godown::create([

                'company_id' =>
                $companyId,

                'store_id' =>
                $storeId,

                'godown_name' =>
                $validated['godown_name'],

                'godown_code' =>
                $validated['godown_code'],

                'address' =>
                $validated['address'],

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
                    'A godown with this name or code already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Godown could not be created. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'godowns.index',
            'Godown created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Godown $godown
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureGodownAvailable(
            $godown
        );


        $inventory =
            $this->godownInventorySummary(
                $godown,
                $companyId,
                $storeId
            );


        return Inertia::render(
            'Godowns/Show',
            [

                'godown' => [

                    'id' =>
                    $godown->id,

                    'godown_name' =>
                    $godown->godown_name,

                    'godown_code' =>
                    $godown->godown_code,

                    'address' =>
                    $godown->address,

                    'status' =>
                    (bool) $godown->status,

                    'products_count' =>
                    $inventory['products_count'],

                    'stock_quantity' =>
                    $inventory['stock_quantity'],

                    'estimated_cost' =>
                    $inventory['estimated_cost'],

                    'estimated_selling_value' =>
                    $inventory['estimated_selling_value'],

                    'potential_margin' =>
                    $inventory['potential_margin'],

                    'low_stock_count' =>
                    $inventory['low_stock_count'],

                    'created_at' =>
                    $godown->created_at
                        ?->toISOString(),

                    'updated_at' =>
                    $godown->updated_at
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
        Godown $godown
    ): Response {

        $this->ensureGodownAvailable(
            $godown
        );


        return Inertia::render(
            'Godowns/Edit',
            [

                'godown' => [

                    'id' =>
                    $godown->id,

                    'godown_name' =>
                    $godown->godown_name,

                    'godown_code' =>
                    $godown->godown_code,

                    'address' =>
                    $godown->address,

                    'status' =>
                    (bool) $godown->status,
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
        Godown $godown
    ) {

        [,
            $storeId,
        ] = $this->context();


        $this->ensureGodownAvailable(
            $godown
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'godown_name' => [
                    'required',
                    'string',
                    'max:255',

                    Rule::unique(
                        'godowns',
                        'godown_name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        )
                        ->ignore(
                            $godown->id
                        ),
                ],

                'godown_code' => [
                    'nullable',
                    'string',
                    'max:255',

                    Rule::unique(
                        'godowns',
                        'godown_code'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        )
                        ->ignore(
                            $godown->id
                        ),
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:255',
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

        $validated['godown_name'] =
            trim(
                $validated['godown_name']
            );


        $validated['godown_code'] =
            filled(
                $validated['godown_code']
                    ?? null
            )
            ? strtoupper(
                trim(
                    $validated['godown_code']
                )
            )
            : $godown->godown_code;


        $validated['address'] =
            filled(
                $validated['address']
                    ?? null
            )
            ? trim(
                $validated['address']
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        try {

            $godown->update([

                'godown_name' =>
                $validated['godown_name'],

                'godown_code' =>
                $validated['godown_code'],

                'address' =>
                $validated['address'],

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
                    'Another godown with this name or code already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Godown could not be updated. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'godowns.index',
            'Godown updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Godown $godown
    ) {

        $this->ensureGodownAvailable(
            $godown
        );


        $newStatus =
            !$godown->status;


        $godown->update([

            'status' =>
            $newStatus,
        ]);


        return AppResponse::success(
            $newStatus
                ? 'Godown activated successfully.'
                : 'Godown deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Godown $godown
    ) {

        $this->ensureGodownAvailable(
            $godown
        );


        /*
        |--------------------------------------------------------------------------
        | Stock Protection
        |--------------------------------------------------------------------------
        */

        if (
            $this->godownHasCurrentStock(
                $godown
            )
        ) {

            return AppResponse::error(
                'This godown still contains stock and cannot be deleted. Transfer or adjust the stock first.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Transaction History Protection
        |--------------------------------------------------------------------------
        */

        if (
            $godown
            ->stockLedgers()
            ->exists()
        ) {

            return AppResponse::error(
                'This godown has stock transaction history and cannot be deleted. Deactivate it instead.'
            );
        }


        try {

            $godown->delete();
        } catch (
            QueryException $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'Godown could not be deleted because it is being used by another record.'
            );
        }


        return AppResponse::successRoute(
            'godowns.index',
            'Godown deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Godown Products / Inventory
    |--------------------------------------------------------------------------
    */

    public function products(
        Request $request,
        Godown $godown
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureGodownAvailable(
            $godown
        );


        [
            $search,
            $status,
            $categoryId,
            $brandId,
            $stockStatus,
        ] = $this->inventoryFilters(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        $inventory =
            $this->godownProductsQuery(
                $godown,
                $companyId,
                $storeId,
                $search,
                $status,
                $categoryId,
                $brandId,
                $stockStatus
            )

            ->orderBy(
                'products.product_name'
            )

            ->paginate(
                25
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Convert Numerical Values
        |--------------------------------------------------------------------------
        */

        $inventory->getCollection()
            ->transform(
                fn($product) =>
                $this->inventoryProductData(
                    $product
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Filter Lists
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
        | Overall Godown Summary
        |--------------------------------------------------------------------------
        */

        $summary =
            $this->godownInventorySummary(
                $godown,
                $companyId,
                $storeId
            );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Godowns/Products',
            [

                'godown' => [

                    'id' =>
                    $godown->id,

                    'godown_name' =>
                    $godown->godown_name,

                    'godown_code' =>
                    $godown->godown_code,

                    'address' =>
                    $godown->address,

                    'status' =>
                    (bool) $godown->status,
                ],

                'products' =>
                $inventory,

                'summary' =>
                $summary,

                'categories' =>
                $categories,

                'brands' =>
                $brands,

                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status ?? '',

                    'category_id' =>
                    $categoryId ?? '',

                    'brand_id' =>
                    $brandId ?? '',

                    'stock_status' =>
                    $stockStatus ?? '',
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
        Godown $godown
    ): StreamedResponse {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureGodownAvailable(
            $godown
        );


        [
            $search,
            $status,
            $categoryId,
            $brandId,
            $stockStatus,
        ] = $this->inventoryFilters(
            $request
        );


        $products =
            $this->godownProductsQuery(
                $godown,
                $companyId,
                $storeId,
                $search,
                $status,
                $categoryId,
                $brandId,
                $stockStatus
            )

            ->orderBy(
                'products.product_name'
            )

            ->get();


        $fileName =
            'godown-inventory-'
            .
            str(
                $godown->godown_name
            )->slug()
            .
            '.csv';


        return response()->streamDownload(
            function () use (
                $products
            ) {

                $handle =
                    fopen(
                        'php://output',
                        'w'
                    );


                fputcsv(
                    $handle,
                    [
                        'Product Code',
                        'Product Name',
                        'Category',
                        'Brand',
                        'Part Number',
                        'Unit',
                        'Quantity',
                        'Unit Cost',
                        'Estimated Cost',
                        'Selling Price',
                        'Estimated Selling Value',
                        'Potential Margin',
                        'Status',
                    ]
                );


                foreach (
                    $products as $product
                ) {

                    $row =
                        $this->inventoryProductData(
                            $product
                        );


                    fputcsv(
                        $handle,
                        [
                            $row['product_code'],
                            $row['product_name'],
                            $row['category']['name'] ?? '',
                            $row['brand']['brand_name'] ?? '',
                            $row['part_number'],
                            $row['unit']['short_name']
                                ?? $row['unit']['unit_name']
                                ?? '',
                            $row['balance_qty'],
                            $row['unit_cost'],
                            $row['estimated_cost'],
                            $row['selling_price'],
                            $row['estimated_selling_value'],
                            $row['potential_margin'],
                            $row['status']
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
    | Print
    |--------------------------------------------------------------------------
    */

    public function printProducts(
        Request $request,
        Godown $godown
    ) {

        $data =
            $this->inventoryExportData(
                $request,
                $godown
            );


        return view(
            'godowns.products-print',
            $data
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function exportProductsPdf(
        Request $request,
        Godown $godown
    ) {

        $data =
            $this->inventoryExportData(
                $request,
                $godown
            );


        $pdf =
            Pdf::loadView(
                'godowns.products-pdf',
                $data
            );


        $pdf->setPaper(
            'a4',
            'landscape'
        );


        $fileName =
            'godown-inventory-'
            .
            str(
                $godown->godown_name
            )->slug()
            .
            '.pdf';


        return $pdf->stream(
            $fileName
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Export Data
    |--------------------------------------------------------------------------
    */

    private function inventoryExportData(
        Request $request,
        Godown $godown
    ): array {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureGodownAvailable(
            $godown
        );


        [
            $search,
            $status,
            $categoryId,
            $brandId,
            $stockStatus,
        ] = $this->inventoryFilters(
            $request
        );


        $products =
            $this->godownProductsQuery(
                $godown,
                $companyId,
                $storeId,
                $search,
                $status,
                $categoryId,
                $brandId,
                $stockStatus
            )

            ->orderBy(
                'products.product_name'
            )

            ->get();


        $company =
            Company::query()
            ->whereKey(
                $companyId
            )
            ->firstOrFail();


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


        $selectedCategory =
            null;


        if (
            filled(
                $categoryId
            )
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
                    $categoryId
                )

                ->first([
                    'id',
                    'name',
                ]);
        }


        $selectedBrand =
            null;


        if (
            filled(
                $brandId
            )
        ) {

            $selectedBrand =
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
                    $brandId
                )

                ->first([
                    'id',
                    'brand_name',
                ]);
        }


        $summary =
            $this->calculateInventorySummaryFromCollection(
                $products
            );


        return [

            'company' =>
            $company,

            'store' =>
            $store,

            'godown' =>
            $godown,

            'products' =>
            $products,

            'summary' =>
            $summary,

            'selectedCategory' =>
            $selectedCategory,

            'selectedBrand' =>
            $selectedBrand,

            'filters' => [

                'search' =>
                $search,

                'status' =>
                $status,

                'category_id' =>
                $categoryId,

                'brand_id' =>
                $brandId,

                'stock_status' =>
                $stockStatus,
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Godown Products Query
    |--------------------------------------------------------------------------
    |
    | We use the latest stock ledger row for each product inside this Godown.
    | balance_qty from that row represents current Godown stock.
    |
    */

    private function godownProductsQuery(
        Godown $godown,
        int $companyId,
        int $storeId,
        string $search = '',
        mixed $status = null,
        mixed $categoryId = null,
        mixed $brandId = null,
        mixed $stockStatus = null
    ): Builder {

        /*
        |--------------------------------------------------------------------------
        | Latest Ledger Row Per Product
        |--------------------------------------------------------------------------
        */

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
                'godown_id',
                $godown->id
            )

            ->groupBy(
                'product_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Product Query
        |--------------------------------------------------------------------------
        */

        $query =
            Product::query()

            ->select([
                'products.id',
                'products.company_id',
                'products.store_id',
                'products.category_id',
                'products.brand_id',
                'products.unit_id',
                'products.product_code',
                'products.barcode',
                'products.product_name',
                'products.part_number',
                'products.manufacturer',
                'products.minimum_stock',
                'products.purchase_price',
                'products.selling_price',
                'products.mrp',
                'products.gst_percent',
                'products.status',

                'stock_ledgers.balance_qty',
                'stock_ledgers.unit_cost',
            ])

            ->join(
                'stock_ledgers',
                'stock_ledgers.product_id',
                '=',
                'products.id'
            )

            ->whereIn(
                'stock_ledgers.id',
                $latestLedgerIds
            )

            ->where(
                'products.company_id',
                $companyId
            )

            ->where(
                'products.store_id',
                $storeId
            )

            ->where(
                'stock_ledgers.company_id',
                $companyId
            )

            ->where(
                'stock_ledgers.store_id',
                $storeId
            )

            ->where(
                'stock_ledgers.godown_id',
                $godown->id
            )

            ->with([
                'category:id,name',
                'brand:id,brand_name',
                'unit:id,unit_name,short_name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $search !== ''
        ) {

            $query->where(
                function (
                    Builder $subQuery
                ) use (
                    $search
                ) {

                    $subQuery
                        ->where(
                            'products.product_name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'products.product_code',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'products.barcode',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'products.part_number',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'products.manufacturer',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Product Status
        |--------------------------------------------------------------------------
        */

        if (
            $status !== null
            &&
            $status !== ''
        ) {

            $query->where(
                'products.status',
                (bool) ((int) $status)
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        */

        if (
            filled(
                $categoryId
            )
        ) {

            $query->where(
                'products.category_id',
                (int) $categoryId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Brand
        |--------------------------------------------------------------------------
        */

        if (
            filled(
                $brandId
            )
        ) {

            $query->where(
                'products.brand_id',
                (int) $brandId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Stock Filter
        |--------------------------------------------------------------------------
        */

        if (
            $stockStatus === 'in_stock'
        ) {

            $query->where(
                'stock_ledgers.balance_qty',
                '>',
                0
            );
        }


        if (
            $stockStatus === 'out_of_stock'
        ) {

            $query->where(
                'stock_ledgers.balance_qty',
                '<=',
                0
            );
        }


        if (
            $stockStatus === 'low_stock'
        ) {

            $query
                ->where(
                    'stock_ledgers.balance_qty',
                    '>',
                    0
                )

                ->whereColumn(
                    'stock_ledgers.balance_qty',
                    '<=',
                    'products.minimum_stock'
                );
        }


        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | Inventory Product Data
    |--------------------------------------------------------------------------
    */

    private function inventoryProductData(
        Product $product
    ): array {

        $quantity =
            (float) (
                $product->balance_qty
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | Unit Cost
        |--------------------------------------------------------------------------
        |
        | Prefer ledger unit cost.
        | Fall back to product purchase price if ledger cost is zero.
        |
        */

        $unitCost =
            (float) (
                $product->unit_cost
                ?? 0
            );


        if (
            $unitCost <= 0
        ) {

            $unitCost =
                (float) (
                    $product->purchase_price
                    ?? 0
                );
        }


        $sellingPrice =
            (float) (
                $product->selling_price
                ?? 0
            );


        $estimatedCost =
            $quantity
            *
            $unitCost;


        $estimatedSellingValue =
            $quantity
            *
            $sellingPrice;


        $potentialMargin =
            $estimatedSellingValue
            -
            $estimatedCost;


        $minimumStock =
            (float) (
                $product->minimum_stock
                ?? 0
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

            'category' =>
            $product->category
                ? [
                    'id' =>
                    $product->category->id,

                    'name' =>
                    $product->category->name,
                ]
                : null,

            'brand' =>
            $product->brand
                ? [
                    'id' =>
                    $product->brand->id,

                    'brand_name' =>
                    $product->brand->brand_name,
                ]
                : null,

            'unit' =>
            $product->unit
                ? [
                    'id' =>
                    $product->unit->id,

                    'unit_name' =>
                    $product->unit->unit_name,

                    'short_name' =>
                    $product->unit->short_name,
                ]
                : null,

            'balance_qty' =>
            round(
                $quantity,
                3
            ),

            'minimum_stock' =>
            $minimumStock,

            'unit_cost' =>
            round(
                $unitCost,
                2
            ),

            'estimated_cost' =>
            round(
                $estimatedCost,
                2
            ),

            'selling_price' =>
            round(
                $sellingPrice,
                2
            ),

            'estimated_selling_value' =>
            round(
                $estimatedSellingValue,
                2
            ),

            'potential_margin' =>
            round(
                $potentialMargin,
                2
            ),

            'mrp' =>
            (float) (
                $product->mrp
                ?? 0
            ),

            'gst_percent' =>
            (float) (
                $product->gst_percent
                ?? 0
            ),

            'is_low_stock' =>
            $quantity > 0
                &&
                $minimumStock > 0
                &&
                $quantity <= $minimumStock,

            'is_out_of_stock' =>
            $quantity <= 0,

            'status' =>
            (bool) $product->status,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Godown Inventory Summary
    |--------------------------------------------------------------------------
    */

    private function godownInventorySummary(
        Godown $godown,
        int $companyId,
        int $storeId
    ): array {

        $products =
            $this->godownProductsQuery(
                $godown,
                $companyId,
                $storeId
            )
            ->get();


        return $this->calculateInventorySummaryFromCollection(
            $products
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Summary
    |--------------------------------------------------------------------------
    */

    private function calculateInventorySummaryFromCollection(
        $products
    ): array {

        $productCount =
            0;


        $stockQuantity =
            0.0;


        $estimatedCost =
            0.0;


        $estimatedSellingValue =
            0.0;


        $potentialMargin =
            0.0;


        $lowStockCount =
            0;


        $outOfStockCount =
            0;


        foreach (
            $products as $product
        ) {

            $row =
                $this->inventoryProductData(
                    $product
                );


            $productCount++;


            $stockQuantity +=
                $row['balance_qty'];


            $estimatedCost +=
                $row['estimated_cost'];


            $estimatedSellingValue +=
                $row['estimated_selling_value'];


            $potentialMargin +=
                $row['potential_margin'];


            if (
                $row['is_low_stock']
            ) {

                $lowStockCount++;
            }


            if (
                $row['is_out_of_stock']
            ) {

                $outOfStockCount++;
            }
        }


        return [

            'products_count' =>
            $productCount,

            'stock_quantity' =>
            round(
                $stockQuantity,
                3
            ),

            'estimated_cost' =>
            round(
                $estimatedCost,
                2
            ),

            'estimated_selling_value' =>
            round(
                $estimatedSellingValue,
                2
            ),

            'potential_margin' =>
            round(
                $potentialMargin,
                2
            ),

            'low_stock_count' =>
            $lowStockCount,

            'out_of_stock_count' =>
            $outOfStockCount,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Has Current Stock
    |--------------------------------------------------------------------------
    */

    private function godownHasCurrentStock(
        Godown $godown
    ): bool {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $latestLedgerIds =
            StockLedger::query()

            ->selectRaw(
                'MAX(id)'
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
                'godown_id',
                $godown->id
            )

            ->groupBy(
                'product_id'
            );


        return StockLedger::query()

            ->whereIn(
                'id',
                $latestLedgerIds
            )

            ->where(
                'balance_qty',
                '>',
                0
            )

            ->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    private function inventoryFilters(
        Request $request
    ): array {

        return [

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
            ),

            $request->input(
                'brand_id'
            ),

            $request->input(
                'stock_status'
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Godown Code
    |--------------------------------------------------------------------------
    */

    private function generateGodownCode(
        int $storeId
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Locking
        |--------------------------------------------------------------------------
        |
        | Code generation is protected with transaction/row lock semantics.
        |
        */

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


                $lastId =
                    Godown::query()

                    ->where(
                        'store_id',
                        $storeId
                    )

                    ->max(
                        'id'
                    )

                    ?? 0;


                $next =
                    ((int) $lastId)
                    + 1;


                return strtoupper(
                    $store->store_code
                        ?? 'GDN'
                )
                    .
                    '-G'
                    .
                    str_pad(
                        $next,
                        3,
                        '0',
                        STR_PAD_LEFT
                    );
            }
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
            !$companyId
            ||
            !$storeId
        ) {

            abort(
                403,
                'No active company or branch is selected.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Store Context
        |--------------------------------------------------------------------------
        */

        $valid =
            Store::query()

            ->where(
                'company_id',
                $companyId
            )

            ->whereKey(
                $storeId
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
    | Godown Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureGodownAvailable(
        Godown $godown
    ): void {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        abort_unless(
            (int) $godown->company_id ===
                $companyId
                &&
                (int) $godown->store_id ===
                $storeId,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Exception
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
