<?php

namespace App\Http\Controllers;

use App\Models\Godown;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Services\StockService;
use App\Support\AppResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class StockTransferController extends Controller implements HasMiddleware
{
    protected StockService $stockService;

    public function __construct(
        StockService $stockService
    ) {
        $this->stockService =
            $stockService;
    }

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:stock-transfer.view',
                only: [
                    'index',
                ]
            ),

            new Middleware(
                'permission:stock-transfer.show',
                only: [
                    'show',
                ]
            ),

            new Middleware(
                'permission:stock-transfer.create',
                only: [
                    'create',
                    'store',
                ]
            ),

            new Middleware(
                'permission:stock-transfer.cancel',
                only: [
                    'delete',
                ]
            ),

            new Middleware(
                'permission:stock-transfer.print',
                only: [
                    'print',
                    'pdf',
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
        ] = $this->context($request);

        $filters = [
            'search' =>
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            ),

            'status' =>
            $request->input(
                'status'
            ),

            'date_from' =>
            $request->input(
                'date_from'
            ),

            'date_to' =>
            $request->input(
                'date_to'
            ),

            'per_page' =>
            (int) $request->input(
                'per_page',
                25
            ),
        ];

        $perPage = in_array(
            $filters['per_page'],
            [
                25,
                50,
                100,
                200,
            ],
            true
        )
            ? $filters['per_page']
            : 25;

        $filters['per_page'] =
            $perPage;

        $query =
            StockTransfer::query()
            ->with([
                'store:id,store_name,store_code',
                'creator:id,name',
            ])
            ->withCount(
                'items'
            )
            ->withSum(
                'items as total_quantity',
                'quantity'
            )
            ->withSum(
                'items as total_cost',
                'total_cost'
            )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            );

        if (
            $filters['search'] !== ''
        ) {
            $search =
                $filters['search'];

            $query->where(
                function ($nested) use ($search) {
                    $nested
                        ->where(
                            'transfer_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'remarks',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'items.product',
                            function ($productQuery) use ($search) {
                                $productQuery
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
                                        'part_number',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        if (
            $filters['status'] !== null &&
            $filters['status'] !== ''
        ) {
            $query->where(
                'status',
                (int) $filters['status']
            );
        }

        if (
            $filters['date_from']
        ) {
            $query->whereDate(
                'transfer_date',
                '>=',
                $filters['date_from']
            );
        }

        if (
            $filters['date_to']
        ) {
            $query->whereDate(
                'transfer_date',
                '<=',
                $filters['date_to']
            );
        }

        $transfers =
            $query
            ->orderByDesc(
                'transfer_date'
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(
                $perPage
            )
            ->withQueryString();

        $summaryQuery =
            StockTransfer::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            );

        $summary = [
            'total' => (clone $summaryQuery)
                ->count(),

            'completed' => (clone $summaryQuery)
                ->where(
                    'status',
                    StockTransfer::STATUS_COMPLETED
                )
                ->count(),

            'cancelled' => (clone $summaryQuery)
                ->where(
                    'status',
                    StockTransfer::STATUS_CANCELLED
                )
                ->count(),

            'today' => (clone $summaryQuery)
                ->whereDate(
                    'transfer_date',
                    now()->toDateString()
                )
                ->count(),
        ];

        return Inertia::render(
            'StockTransfers/Index',
            [
                'transfers' =>
                $transfers,

                'summary' =>
                $summary,

                'filters' =>
                $filters,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request
    ): Response {
        [
            $companyId,
            $storeId,
        ] = $this->context($request);

        $godowns =
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
            ->orderBy(
                'godown_name'
            )
            ->get([
                'id',
                'godown_name',
                'godown_code',
            ]);

        return Inertia::render(
            'StockTransfers/Create',
            [
                'transferNo' =>
                $this->previewTransferNumber(),

                'transferDate' =>
                now()->toDateString(),

                'godowns' =>
                $godowns,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    public function searchProduct(
        Request $request
    ) {
        $this->authorizeLookup(
            $request
        );

        [
            $companyId,
            $storeId,
        ] = $this->context($request);

        try {
            $validated =
                $request->validate([
                    'search' => [
                        'required',
                        'string',
                        'min:1',
                        'max:100',
                    ],

                    'godown_id' => [
                        'nullable',
                        'integer',
                    ],
                ]);

            $search =
                trim(
                    $validated['search']
                );

            $godownId =
                $this->nullableInteger(
                    $validated['godown_id']
                        ?? null
                );

            if (
                $godownId !== null
            ) {
                $this->findGodown(
                    $godownId,
                    $companyId,
                    $storeId
                );
            }

            $products =
                Product::query()
                ->with([
                    'unit:id,unit_name,short_name',
                    'brand:id,brand_name',
                    'category:id,name',
                ])
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
                ->where(
                    function ($query) use ($search) {
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
                ->orderBy(
                    'product_name'
                )
                ->limit(
                    20
                )
                ->get([
                    'id',
                    'category_id',
                    'brand_id',
                    'unit_id',
                    'product_code',
                    'barcode',
                    'product_name',
                    'part_number',
                    'purchase_price',
                    'mrp',
                    'current_stock',
                ])
                ->map(
                    function (
                        Product $product
                    ) use (
                        $companyId,
                        $storeId,
                        $godownId
                    ) {
                        $product->setAttribute(
                            'available_stock',
                            $this->stockService->getStock(
                                $companyId,
                                $storeId,
                                $godownId,
                                $product->id
                            )
                        );

                        return $product;
                    }
                )
                ->values();

            return response()->json(
                $products
            );
        } catch (
            ValidationException $e
        ) {
            return response()->json([
                'success' =>
                false,

                'message' =>
                $this->validationMessage(
                    $e,
                    'Unable to search products.'
                ),

                'errors' =>
                $e->errors(),
            ], 422);
        } catch (
            Throwable $e
        ) {
            return response()->json([
                'success' =>
                false,

                'message' =>
                $e->getMessage(),
            ], 422);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Product Location Stock
    |--------------------------------------------------------------------------
    */

    public function getProductStock(
        Request $request
    ) {
        $this->authorizeLookup(
            $request
        );

        [
            $companyId,
            $storeId,
        ] = $this->context($request);

        try {
            $validated =
                $request->validate([
                    'product_id' => [
                        'required',
                        'integer',
                    ],

                    'godown_id' => [
                        'nullable',
                        'integer',
                    ],
                ]);

            $product =
                $this->findProduct(
                    (int) $validated['product_id'],
                    $companyId,
                    $storeId
                );

            $godownId =
                $this->nullableInteger(
                    $validated['godown_id']
                        ?? null
                );

            $locationName =
                'Shop';

            if (
                $godownId !== null
            ) {
                $godown =
                    $this->findGodown(
                        $godownId,
                        $companyId,
                        $storeId
                    );

                $locationName =
                    $godown->godown_name;
            }

            $stock =
                $this->stockService->getStock(
                    $companyId,
                    $storeId,
                    $godownId,
                    $product->id
                );

            return response()->json([
                'success' =>
                true,

                'product_id' =>
                $product->id,

                'godown_id' =>
                $godownId,

                'location_name' =>
                $locationName,

                'stock' =>
                $stock,
            ]);
        } catch (
            ValidationException $e
        ) {
            return response()->json([
                'success' =>
                false,

                'message' =>
                $this->validationMessage(
                    $e,
                    'Unable to load the selected stock.'
                ),

                'errors' =>
                $e->errors(),
            ], 422);
        } catch (
            Throwable $e
        ) {
            return response()->json([
                'success' =>
                false,

                'message' =>
                $e->getMessage(),
            ], 422);
        }
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
        ] = $this->context($request);

        try {
            $validated =
                $this->validateTransferRequest(
                    $request
                );

            DB::transaction(
                function () use (
                    $validated,
                    $companyId,
                    $storeId,
                    $request
                ) {
                    $transferNo =
                        $this->generateTransferNumber();

                    $transfer =
                        StockTransfer::create([
                            'company_id' =>
                            $companyId,

                            'store_id' =>
                            $storeId,

                            'transfer_no' =>
                            $transferNo,

                            'transfer_date' =>
                            $validated['transfer_date'],

                            'remarks' =>
                            $this->nullableString(
                                $validated['remarks']
                                    ?? null
                            ),

                            'status' =>
                            StockTransfer::STATUS_COMPLETED,

                            'created_by' =>
                            $request->user()->id,
                        ]);

                    foreach (
                        $validated['items']
                        as $index => $inputItem
                    ) {
                        $product =
                            $this->findProduct(
                                (int) $inputItem['product_id'],
                                $companyId,
                                $storeId
                            );

                        $fromGodownId =
                            $this->nullableInteger(
                                $inputItem['from_godown_id']
                                    ?? null
                            );

                        $toGodownId =
                            $this->nullableInteger(
                                $inputItem['to_godown_id']
                                    ?? null
                            );

                        $this->validateLocations(
                            $fromGodownId,
                            $toGodownId,
                            $companyId,
                            $storeId,
                            $product->product_name,
                            $index
                        );

                        $quantity =
                            round(
                                (float) $inputItem['quantity'],
                                3
                            );

                        $unitCost =
                            round(
                                (float) (
                                    $inputItem['unit_cost']
                                    ??
                                    $product->purchase_price
                                    ??
                                    0
                                ),
                                2
                            );

                        $snapshot =
                            $this->stockService
                            ->transferStock(
                                product: $product,
                                qty: $quantity,
                                companyId: $companyId,
                                storeId: $storeId,
                                fromGodownId: $fromGodownId,
                                toGodownId: $toGodownId,
                                referenceId: $transfer->id,
                                referenceNo: $transfer->transfer_no,
                                unitCost: $unitCost,
                                remarks: 'Stock transfer'
                            );

                        $transfer
                            ->items()
                            ->create([
                                'product_id' =>
                                $product->id,

                                'from_godown_id' =>
                                $fromGodownId,

                                'to_godown_id' =>
                                $toGodownId,

                                'quantity' =>
                                $quantity,

                                'from_stock_before' =>
                                $snapshot['from_stock_before'],

                                'from_stock_after' =>
                                $snapshot['from_stock_after'],

                                'to_stock_before' =>
                                $snapshot['to_stock_before'],

                                'to_stock_after' =>
                                $snapshot['to_stock_after'],

                                'unit_cost' =>
                                $unitCost,

                                'total_cost' =>
                                round(
                                    $quantity
                                        *
                                        $unitCost,
                                    2
                                ),
                            ]);
                    }
                }
            );

            return AppResponse::successRoute(
                'stock-transfers.index',
                'Stock transferred successfully.'
            );
        } catch (
            ValidationException $e
        ) {
            return AppResponse::error(
                $this->validationMessage(
                    $e,
                    'Please check the entered transfer details.'
                ),
                true
            );
        } catch (
            Throwable $e
        ) {
            Log::error(
                'Stock transfer creation failed',
                [
                    'company_id' =>
                    $companyId,

                    'store_id' =>
                    $storeId,

                    'user_id' =>
                    $request->user()?->id,

                    'error' =>
                    $e->getMessage(),
                ]
            );

            return AppResponse::error(
                $e->getMessage(),
                true
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        StockTransfer $stockTransfer
    ): Response {
        [
            $companyId,
            $storeId,
        ] = $this->context($request);

        $this->ensureTransferContext(
            $stockTransfer,
            $companyId,
            $storeId
        );

        $stockTransfer->load([
            'company',
            'store',
            'creator',
            'items.product.unit',
            'items.fromGodown',
            'items.toGodown',
        ]);

        return Inertia::render(
            'StockTransfers/Show',
            [
                'transfer' =>
                $stockTransfer,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        StockTransfer $stockTransfer
    ) {
        [
            $companyId,
            $storeId,
        ] = $this->context($request);

        try {
            $this->ensureTransferContext(
                $stockTransfer,
                $companyId,
                $storeId
            );

            DB::transaction(
                function () use (
                    $stockTransfer,
                    $companyId,
                    $storeId
                ) {
                    $lockedTransfer =
                        StockTransfer::query()
                        ->whereKey(
                            $stockTransfer->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                    $this->ensureTransferContext(
                        $lockedTransfer,
                        $companyId,
                        $storeId
                    );

                    if (
                        $lockedTransfer->isCancelled()
                    ) {
                        throw ValidationException::withMessages([
                            'transfer' =>
                            'This stock transfer is already cancelled.',
                        ]);
                    }

                    $lockedTransfer->load([
                        'items.product',
                    ]);

                    if (
                        $lockedTransfer->items->isEmpty()
                    ) {
                        throw new RuntimeException(
                            'This stock transfer does not contain any items.'
                        );
                    }

                    foreach (
                        $lockedTransfer->items
                        as $item
                    ) {
                        if (
                            !$item->product
                        ) {
                            throw new RuntimeException(
                                'A product from this transfer is unavailable.'
                            );
                        }

                        $this->stockService
                            ->reverseTransfer(
                                product: $item->product,
                                qty: (float) $item->quantity,
                                companyId: $companyId,
                                storeId: $storeId,
                                originalFromGodownId: $item->from_godown_id !== null
                                    ? (int) $item->from_godown_id
                                    : null,
                                originalToGodownId: $item->to_godown_id !== null
                                    ? (int) $item->to_godown_id
                                    : null,
                                referenceId: $lockedTransfer->id,
                                referenceNo: $lockedTransfer->transfer_no,
                                unitCost: (float) $item->unit_cost,
                                remarks: 'Cancellation of stock transfer '
                                    .
                                    $lockedTransfer->transfer_no
                            );
                    }

                    $lockedTransfer->update([
                        'status' =>
                        StockTransfer::STATUS_CANCELLED,
                    ]);
                }
            );

            return AppResponse::successRoute(
                'stock-transfers.index',
                'Stock transfer cancelled successfully.'
            );
        } catch (
            ValidationException $e
        ) {
            return AppResponse::error(
                $this->validationMessage(
                    $e,
                    'Unable to cancel this stock transfer.'
                ),
                true
            );
        } catch (
            Throwable $e
        ) {
            Log::error(
                'Stock transfer cancellation failed',
                [
                    'transfer_id' =>
                    $stockTransfer->id,

                    'company_id' =>
                    $companyId,

                    'store_id' =>
                    $storeId,

                    'user_id' =>
                    $request->user()?->id,

                    'error' =>
                    $e->getMessage(),
                ]
            );

            return AppResponse::error(
                $e->getMessage(),
                true
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request,
        StockTransfer $stockTransfer
    ) {
        [
            $companyId,
            $storeId,
        ] = $this->context($request);

        $this->ensureTransferContext(
            $stockTransfer,
            $companyId,
            $storeId
        );

        $stockTransfer->load([
            'company',
            'store',
            'creator',
            'items.product.unit',
            'items.fromGodown',
            'items.toGodown',
        ]);

        return view(
            'stock-transfers.print',
            [
                'transfer' =>
                $stockTransfer,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    public function pdf(
        Request $request,
        StockTransfer $stockTransfer
    ) {
        [
            $companyId,
            $storeId,
        ] = $this->context($request);

        $this->ensureTransferContext(
            $stockTransfer,
            $companyId,
            $storeId
        );

        $stockTransfer->load([
            'company',
            'store',
            'creator',
            'items.product.unit',
            'items.fromGodown',
            'items.toGodown',
        ]);

        $pdf =
            Pdf::loadView(
                'stock-transfers.pdf',
                [
                    'transfer' =>
                    $stockTransfer,
                ]
            )
            ->setPaper(
                'a4',
                'portrait'
            );

        return $pdf->download(
            'stock-transfer-'
                .
                $stockTransfer->transfer_no
                .
                '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Request Validation
    |--------------------------------------------------------------------------
    */

    private function validateTransferRequest(
        Request $request
    ): array {
        return $request->validate([
            'transfer_date' => [
                'required',
                'date',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'items.*.from_godown_id' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],

            'items.*.to_godown_id' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit_cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Product Validation
    |--------------------------------------------------------------------------
    */

    private function findProduct(
        int $productId,
        int $companyId,
        int $storeId
    ): Product {
        $product =
            Product::query()
            ->where(
                'id',
                $productId
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
                'status',
                true
            )
            ->first();

        if (
            !$product
        ) {
            throw ValidationException::withMessages([
                'product_id' =>
                'The selected product is invalid for the active branch.',
            ]);
        }

        return $product;
    }

    /*
    |--------------------------------------------------------------------------
    | Godown Validation
    |--------------------------------------------------------------------------
    */

    private function findGodown(
        int $godownId,
        int $companyId,
        int $storeId
    ): Godown {
        $godown =
            Godown::query()
            ->where(
                'id',
                $godownId
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
                'status',
                true
            )
            ->first();

        if (
            !$godown
        ) {
            throw ValidationException::withMessages([
                'godown_id' =>
                'The selected godown is invalid for the active branch.',
            ]);
        }

        return $godown;
    }

    /*
    |--------------------------------------------------------------------------
    | Location Validation
    |--------------------------------------------------------------------------
    */

    private function validateLocations(
        ?int $fromGodownId,
        ?int $toGodownId,
        int $companyId,
        int $storeId,
        string $productName,
        int $itemIndex
    ): void {
        if (
            $fromGodownId === null &&
            $toGodownId === null
        ) {
            throw ValidationException::withMessages([
                "items.{$itemIndex}.to_godown_id" =>
                "Source and destination cannot both be Shop for {$productName}.",
            ]);
        }

        if (
            $fromGodownId ===
            $toGodownId
        ) {
            throw ValidationException::withMessages([
                "items.{$itemIndex}.to_godown_id" =>
                "Source and destination cannot be the same for {$productName}.",
            ]);
        }

        if (
            $fromGodownId !== null
        ) {
            $this->findGodown(
                $fromGodownId,
                $companyId,
                $storeId
            );
        }

        if (
            $toGodownId !== null
        ) {
            $this->findGodown(
                $toGodownId,
                $companyId,
                $storeId
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Company and Branch Context
    |--------------------------------------------------------------------------
    */

    private function context(
        Request $request
    ): array {
        $user =
            $request->user();

        abort_unless(
            $user &&
                (bool) $user->status,
            403
        );

        $companyId =
            (int) session(
                'active_company_id'
            );

        $storeId =
            (int) session(
                'active_store_id'
            );

        abort_unless(
            $companyId > 0 &&
                $storeId > 0,
            403,
            'No active business branch selected.'
        );

        $store =
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
            ->first();

        abort_unless(
            $store,
            403,
            'The active branch is unavailable.'
        );

        if (
            !$user->is_super_user
        ) {
            abort_unless(
                (int) $user->company_id ===
                    $companyId,
                403,
                'You cannot access this company.'
            );

            if (
                !$user->hasRole(
                    'Company Admin'
                )
            ) {
                $assigned =
                    $user
                    ->stores()
                    ->where(
                        'stores.id',
                        $storeId
                    )
                    ->where(
                        'stores.status',
                        true
                    )
                    ->exists();

                abort_unless(
                    $assigned,
                    403,
                    'You are not assigned to this branch.'
                );
            }
        }

        return [
            $companyId,
            $storeId,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Transfer Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureTransferContext(
        StockTransfer $stockTransfer,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $stockTransfer->company_id ===
                $companyId &&
                (int) $stockTransfer->store_id ===
                $storeId,
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Authorization
    |--------------------------------------------------------------------------
    */

    private function authorizeLookup(
        Request $request
    ): void {
        $user =
            $request->user();

        abort_unless(
            $user &&
                (
                    $user->can(
                        'stock-transfer.view'
                    ) ||
                    $user->can(
                        'stock-transfer.create'
                    )
                ),
            403
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Transfer Number
    |--------------------------------------------------------------------------
    */

    private function previewTransferNumber(): string
    {
        $lastTransfer =
            StockTransfer::query()
            ->orderByDesc(
                'id'
            )
            ->first([
                'transfer_no',
            ]);

        $number =
            $lastTransfer
            ? (int) preg_replace(
                '/\D/',
                '',
                (string) $lastTransfer->transfer_no
            ) + 1
            : 1;

        return 'TR'
            .
            str_pad(
                (string) $number,
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Transfer Number
    |--------------------------------------------------------------------------
    */

    private function generateTransferNumber(): string
    {
        $lastTransfer =
            StockTransfer::query()
            ->orderByDesc(
                'id'
            )
            ->lockForUpdate()
            ->first([
                'transfer_no',
            ]);

        $number =
            $lastTransfer
            ? (int) preg_replace(
                '/\D/',
                '',
                (string) $lastTransfer->transfer_no
            ) + 1
            : 1;

        return 'TR'
            .
            str_pad(
                (string) $number,
                6,
                '0',
                STR_PAD_LEFT
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Message
    |--------------------------------------------------------------------------
    */

    private function validationMessage(
        ValidationException $exception,
        string $fallback
    ): string {
        return collect(
            $exception->errors()
        )
            ->flatten()
            ->first()
            ??
            $fallback;
    }

    private function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        return (int) $value;
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if (
            $value === null
        ) {
            return null;
        }

        $value =
            trim(
                (string) $value
            );

        return $value !== ''
            ? $value
            : null;
    }
}
