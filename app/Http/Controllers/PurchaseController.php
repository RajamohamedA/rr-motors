<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Godown;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\StockService;
use App\Support\AppResponse;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

use Inertia\Inertia;
use Inertia\Response;


class PurchaseController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Stock Service
    |--------------------------------------------------------------------------
    */

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
                'permission:purchase.view',
                only: [
                    'index',
                    'show',
                    'print',
                    'pdf',
                ]
            ),

            new Middleware(
                'permission:purchase.create',
                only: [
                    'create',
                    'store',
                ]
            ),

            new Middleware(
                'permission:purchase.edit',
                only: [
                    'edit',
                    'update',
                ]
            ),

            new Middleware(
                'permission:purchase.delete',
                only: [
                    'destroy',
                ]
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        $supplierId =
            $request->input(
                'supplier_id'
            );


        $taxType =
            $request->input(
                'tax_type'
            );


        $status =
            $request->input(
                'status'
            );


        $dateFrom =
            $request->input(
                'date_from'
            );


        $dateTo =
            $request->input(
                'date_to'
            );


        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query =
            Purchase::query()
            ->with([
                'supplier:id,supplier_name',
                'store:id,store_name',
            ])
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            );


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            $search !== ''
        ) {

            $query->where(
                function ($q) use ($search) {

                    $q
                        ->where(
                            'purchase_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'invoice_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'supplier',
                            function ($supplierQuery) use ($search) {

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


        /*
        |--------------------------------------------------------------------------
        | Supplier Filter
        |--------------------------------------------------------------------------
        */

        if (
            !empty($supplierId)
        ) {

            $query->where(
                'supplier_id',
                $supplierId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Tax Type
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $taxType,
                [
                    'gst',
                    'non_gst',
                ],
                true
            )
        ) {

            $query->where(
                'tax_type',
                $taxType
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (
            $status !== null
            &&
            $status !== ''
        ) {

            $query->where(
                'status',
                (bool) $status
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date Range
        |--------------------------------------------------------------------------
        */

        if (
            !empty($dateFrom)
        ) {

            $query->whereDate(
                'purchase_date',
                '>=',
                $dateFrom
            );
        }


        if (
            !empty($dateTo)
        ) {

            $query->whereDate(
                'purchase_date',
                '<=',
                $dateTo
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $purchases =
            $query
            ->orderByDesc(
                'purchase_date'
            )
            ->orderByDesc(
                'id'
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

        $summaryQuery =
            Purchase::query()
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

            'active' => (clone $summaryQuery)
                ->where(
                    'status',
                    true
                )
                ->count(),

            'gst' => (clone $summaryQuery)
                ->where(
                    'tax_type',
                    'gst'
                )
                ->count(),

            'non_gst' => (clone $summaryQuery)
                ->where(
                    'tax_type',
                    'non_gst'
                )
                ->count(),

            'purchase_value' =>
            round(
                (float)
                (clone $summaryQuery)
                    ->sum(
                        'grand_total'
                    ),
                2
            ),

            'balance' =>
            round(
                (float)
                (clone $summaryQuery)
                    ->sum(
                        'balance_amount'
                    ),
                2
            ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Suppliers Filter List
        |--------------------------------------------------------------------------
        */

        $suppliers =
            Supplier::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'status',
                true
            )
            ->whereHas(
                'stores',
                function ($query) use (
                    $storeId
                ) {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->orderBy(
                'supplier_name'
            )
            ->get([
                'id',
                'supplier_name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Inertia
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Purchases/Index',
            [
                'purchases' =>
                $purchases,

                'suppliers' =>
                $suppliers,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

                    'supplier_id' =>
                    $supplierId,

                    'tax_type' =>
                    $taxType,

                    'status' =>
                    $status,

                    'date_from' =>
                    $dateFrom,

                    'date_to' =>
                    $dateTo,
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Purchase Number
        |--------------------------------------------------------------------------
        */

        $purchaseNo =
            $this->previewPurchaseNumber(
                $storeId
            );


        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        */

        $suppliers =
            Supplier::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'status',
                true
            )
            ->whereHas(
                'stores',
                function ($query) use (
                    $storeId
                ) {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->orderBy(
                'supplier_name'
            )
            ->get([
                'id',
                'supplier_name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Godowns
        |--------------------------------------------------------------------------
        */

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
            'Purchases/Create',
            [
                'purchaseNo' =>
                $purchaseNo,

                'suppliers' =>
                $suppliers,

                'godowns' =>
                $godowns,

                'taxTypes' => [
                    [
                        'value' =>
                        'gst',

                        'label' =>
                        'GST Purchase',
                    ],
                    [
                        'value' =>
                        'non_gst',

                        'label' =>
                        'Without GST',
                    ],
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCT SEARCH
    |--------------------------------------------------------------------------
    */

    public function search(
        Request $request
    ) {

        $this->authorizePurchaseLookup(
            $request
        );


        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


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

                'gst_percent',

                'current_stock',
            ]);


        return response()->json(
            $products
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $this->validatePurchaseRequest(
                $request
            );


        /*
        |--------------------------------------------------------------------------
        | Validate Supplier
        |--------------------------------------------------------------------------
        */

        $supplier =
            $this->validateSupplier(
                (int) $validated['supplier_id'],
                $companyId,
                $storeId
            );


        /*
        |--------------------------------------------------------------------------
        | Duplicate Supplier Invoice
        |--------------------------------------------------------------------------
        */

        $this->validateDuplicateInvoice(
            $companyId,
            $storeId,
            $supplier->id,
            $validated['invoice_no'] ?? null
        );


        /*
        |--------------------------------------------------------------------------
        | DB Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | Generate Purchase Number
            |--------------------------------------------------------------------------
            */

            $purchaseNo =
                $this->generatePurchaseNumber(
                    $storeId
                );


            /*
            |--------------------------------------------------------------------------
            | Validate + Calculate Items
            |--------------------------------------------------------------------------
            */

            $calculated =
                $this->calculateItems(
                    $validated['items'],
                    $validated['tax_type'],
                    $companyId,
                    $storeId
                );


            /*
            |--------------------------------------------------------------------------
            | Header Totals
            |--------------------------------------------------------------------------
            */

            $subtotal =
                $calculated['subtotal'];


            $discount =
                $calculated['discount'];


            $taxAmount =
                $calculated['tax_amount'];


            $otherCharges =
                round(
                    (float)
                    (
                        $validated['other_charges']
                        ?? 0
                    ),
                    2
                );


            $grandTotal =
                round(
                    $calculated['line_total']
                        +
                        $otherCharges,
                    2
                );


            $paidAmount =
                round(
                    (float)
                    (
                        $validated['paid_amount']
                        ?? 0
                    ),
                    2
                );


            if (
                $paidAmount >
                $grandTotal
            ) {

                throw ValidationException::withMessages([
                    'paid_amount' =>
                    'Paid amount cannot be greater than the purchase total.',
                ]);
            }


            $balanceAmount =
                round(
                    $grandTotal
                        -
                        $paidAmount,
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Create Purchase
            |--------------------------------------------------------------------------
            */

            $purchase =
                Purchase::create([

                    'company_id' =>
                    $companyId,

                    'store_id' =>
                    $storeId,

                    'supplier_id' =>
                    $supplier->id,

                    'purchase_no' =>
                    $purchaseNo,

                    'purchase_date' =>
                    $validated['purchase_date'],

                    'invoice_no' =>
                    $this->nullableString(
                        $validated['invoice_no']
                            ?? null
                    ),

                    'invoice_date' =>
                    $validated['invoice_date']
                        ?? null,

                    'tax_type' =>
                    $validated['tax_type'],

                    'subtotal' =>
                    $subtotal,

                    'discount' =>
                    $discount,

                    'tax_amount' =>
                    $taxAmount,

                    'other_charges' =>
                    $otherCharges,

                    'grand_total' =>
                    $grandTotal,

                    'paid_amount' =>
                    $paidAmount,

                    'balance_amount' =>
                    $balanceAmount,

                    'remarks' =>
                    $this->nullableString(
                        $validated['remarks']
                            ?? null
                    ),

                    'status' =>
                    true,
                ]);


            /*
            |--------------------------------------------------------------------------
            | Create Items + Stock
            |--------------------------------------------------------------------------
            */

            foreach (
                $calculated['items']
                as $item
            ) {

                PurchaseItem::create([

                    'purchase_id' =>
                    $purchase->id,

                    'product_id' =>
                    $item['product']->id,

                    'godown_id' =>
                    $item['godown_id'],

                    'quantity' =>
                    $item['quantity'],

                    'free_quantity' =>
                    $item['free_quantity'],

                    'purchase_price' =>
                    $item['purchase_price'],

                    'discount' =>
                    $item['discount'],

                    'taxable_amount' =>
                    $item['taxable_amount'],

                    'gst_percent' =>
                    $item['gst_percent'],

                    'gst_amount' =>
                    $item['gst_amount'],

                    'line_total' =>
                    $item['line_total'],
                ]);


                /*
                |--------------------------------------------------------------------------
                | Physical Stock
                |--------------------------------------------------------------------------
                |
                | Qty + Free Qty both enter stock.
                |
                */

                $stockQty =
                    round(
                        $item['quantity']
                            +
                            $item['free_quantity'],
                        2
                    );


                $this->stockService
                    ->increaseStock(

                        $item['product'],

                        $stockQty,

                        'Purchase',

                        $companyId,

                        $storeId,

                        $item['godown_id'],

                        $purchase->id,

                        $purchase->purchase_no,

                        $item['purchase_price'],

                        'Purchase received'
                    );
            }


            DB::commit();


            return AppResponse::successRoute(
                'purchases.index',
                'Purchase created successfully.'
            );
        } catch (
            ValidationException $e
        ) {

            DB::rollBack();

            throw $e;
        } catch (
            \Throwable $e
        ) {

            DB::rollBack();


            Log::error(
                'Purchase creation failed',
                [
                    'company_id' =>
                    $companyId,

                    'store_id' =>
                    $storeId,

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
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Purchase $purchase
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $this->ensurePurchaseContext(
            $purchase,
            $companyId,
            $storeId
        );


        $purchase->load([
            'supplier',
            'store',
            'company',

            'items.product.unit',
            'items.product.category',
            'items.product.brand',

            'items.godown',
        ]);


        return Inertia::render(
            'Purchases/Show',
            [
                'purchase' =>
                $purchase,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        Purchase $purchase
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $this->ensurePurchaseContext(
            $purchase,
            $companyId,
            $storeId
        );


        $purchase->load([
            'supplier',
            'items.product.unit',
            'items.product.category',
            'items.product.brand',
            'items.godown',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Suppliers
        |--------------------------------------------------------------------------
        */

        $suppliers =
            Supplier::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'status',
                true
            )
            ->whereHas(
                'stores',
                function ($query) use (
                    $storeId
                ) {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->orderBy(
                'supplier_name'
            )
            ->get([
                'id',
                'supplier_name',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Godowns
        |--------------------------------------------------------------------------
        */

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
            'Purchases/Edit',
            [
                'purchase' =>
                $purchase,

                'suppliers' =>
                $suppliers,

                'godowns' =>
                $godowns,

                'taxTypes' => [
                    [
                        'value' =>
                        'gst',

                        'label' =>
                        'GST Purchase',
                    ],
                    [
                        'value' =>
                        'non_gst',

                        'label' =>
                        'Without GST',
                    ],
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Purchase $purchase
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $this->ensurePurchaseContext(
            $purchase,
            $companyId,
            $storeId
        );


        /*
        |--------------------------------------------------------------------------
        | Purchase Return Protection
        |--------------------------------------------------------------------------
        |
        | Editing a purchase after returns exist can make return quantities
        | inconsistent with the original purchase.
        |
        */

        if (
            PurchaseReturn::where(
                'purchase_id',
                $purchase->id
            )->exists()
        ) {

            return AppResponse::error(
                'This purchase cannot be edited because a purchase return already exists.'
            );
        }


        $validated =
            $this->validatePurchaseRequest(
                $request
            );


        $supplier =
            $this->validateSupplier(
                (int) $validated['supplier_id'],
                $companyId,
                $storeId
            );


        $this->validateDuplicateInvoice(
            $companyId,
            $storeId,
            $supplier->id,
            $validated['invoice_no'] ?? null,
            $purchase->id
        );


        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | Load Old Items
            |--------------------------------------------------------------------------
            */

            $purchase->load([
                'items.product',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Reverse Old Physical Stock
            |--------------------------------------------------------------------------
            */

            foreach (
                $purchase->items
                as $oldItem
            ) {

                $product =
                    $oldItem->product;


                if (
                    !$product
                ) {

                    throw new \RuntimeException(
                        'A product from the existing purchase no longer exists.'
                    );
                }


                $oldStockQty =
                    round(
                        (float) $oldItem->quantity
                            +
                            (float) $oldItem->free_quantity,
                        2
                    );


                if (
                    $oldStockQty <= 0
                ) {
                    continue;
                }


                $this->stockService
                    ->decreaseStock(

                        $product,

                        $oldStockQty,

                        'Purchase Reverse',

                        $companyId,

                        $storeId,

                        $oldItem->godown_id,

                        $purchase->id,

                        $purchase->purchase_no,

                        (float) $oldItem->purchase_price,

                        'Purchase edited - reverse previous stock'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Calculate New Items
            |--------------------------------------------------------------------------
            */

            $calculated =
                $this->calculateItems(
                    $validated['items'],
                    $validated['tax_type'],
                    $companyId,
                    $storeId
                );


            $subtotal =
                $calculated['subtotal'];


            $discount =
                $calculated['discount'];


            $taxAmount =
                $calculated['tax_amount'];


            $otherCharges =
                round(
                    (float)
                    (
                        $validated['other_charges']
                        ?? 0
                    ),
                    2
                );


            $grandTotal =
                round(
                    $calculated['line_total']
                        +
                        $otherCharges,
                    2
                );


            $paidAmount =
                round(
                    (float)
                    (
                        $validated['paid_amount']
                        ?? 0
                    ),
                    2
                );


            if (
                $paidAmount >
                $grandTotal
            ) {

                throw ValidationException::withMessages([
                    'paid_amount' =>
                    'Paid amount cannot be greater than the purchase total.',
                ]);
            }


            $balanceAmount =
                round(
                    $grandTotal
                        -
                        $paidAmount,
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */

            $purchase->update([

                'supplier_id' =>
                $supplier->id,

                'purchase_date' =>
                $validated['purchase_date'],

                'invoice_no' =>
                $this->nullableString(
                    $validated['invoice_no']
                        ?? null
                ),

                'invoice_date' =>
                $validated['invoice_date']
                    ?? null,

                'tax_type' =>
                $validated['tax_type'],

                'subtotal' =>
                $subtotal,

                'discount' =>
                $discount,

                'tax_amount' =>
                $taxAmount,

                'other_charges' =>
                $otherCharges,

                'grand_total' =>
                $grandTotal,

                'paid_amount' =>
                $paidAmount,

                'balance_amount' =>
                $balanceAmount,

                'remarks' =>
                $this->nullableString(
                    $validated['remarks']
                        ?? null
                ),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Replace Purchase Items
            |--------------------------------------------------------------------------
            */

            $purchase
                ->items()
                ->delete();


            /*
            |--------------------------------------------------------------------------
            | Create New Items + Repost Stock
            |--------------------------------------------------------------------------
            */

            foreach (
                $calculated['items']
                as $item
            ) {

                PurchaseItem::create([

                    'purchase_id' =>
                    $purchase->id,

                    'product_id' =>
                    $item['product']->id,

                    'godown_id' =>
                    $item['godown_id'],

                    'quantity' =>
                    $item['quantity'],

                    'free_quantity' =>
                    $item['free_quantity'],

                    'purchase_price' =>
                    $item['purchase_price'],

                    'discount' =>
                    $item['discount'],

                    'taxable_amount' =>
                    $item['taxable_amount'],

                    'gst_percent' =>
                    $item['gst_percent'],

                    'gst_amount' =>
                    $item['gst_amount'],

                    'line_total' =>
                    $item['line_total'],
                ]);


                $stockQty =
                    round(
                        $item['quantity']
                            +
                            $item['free_quantity'],
                        2
                    );


                $this->stockService
                    ->increaseStock(

                        $item['product'],

                        $stockQty,

                        'Purchase',

                        $companyId,

                        $storeId,

                        $item['godown_id'],

                        $purchase->id,

                        $purchase->purchase_no,

                        $item['purchase_price'],

                        'Purchase updated - new stock'
                    );
            }


            DB::commit();


            return AppResponse::successRoute(
                'purchases.index',
                'Purchase updated successfully.'
            );
        } catch (
            ValidationException $e
        ) {

            DB::rollBack();

            throw $e;
        } catch (
            \Throwable $e
        ) {

            DB::rollBack();


            Log::error(
                'Purchase update failed',
                [
                    'purchase_id' =>
                    $purchase->id,

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
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Purchase $purchase
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $this->ensurePurchaseContext(
            $purchase,
            $companyId,
            $storeId
        );


        /*
        |--------------------------------------------------------------------------
        | Purchase Return Protection
        |--------------------------------------------------------------------------
        */

        if (
            PurchaseReturn::where(
                'purchase_id',
                $purchase->id
            )->exists()
        ) {

            return AppResponse::error(
                'This purchase cannot be deleted because a purchase return exists.'
            );
        }


        DB::beginTransaction();


        try {

            $purchase->load([
                'items.product',
            ]);


            /*
            |--------------------------------------------------------------------------
            | Reverse Stock
            |--------------------------------------------------------------------------
            */

            foreach (
                $purchase->items
                as $item
            ) {

                $product =
                    $item->product;


                if (
                    !$product
                ) {

                    throw new \RuntimeException(
                        'A product from this purchase no longer exists.'
                    );
                }


                $stockQty =
                    round(
                        (float) $item->quantity
                            +
                            (float) $item->free_quantity,
                        2
                    );


                if (
                    $stockQty <= 0
                ) {
                    continue;
                }


                $this->stockService
                    ->decreaseStock(

                        $product,

                        $stockQty,

                        'Purchase Reverse',

                        $companyId,

                        $storeId,

                        $item->godown_id,

                        $purchase->id,

                        $purchase->purchase_no,

                        (float) $item->purchase_price,

                        'Purchase deleted - reverse stock'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete
            |--------------------------------------------------------------------------
            */

            $purchase
                ->items()
                ->delete();


            $purchase->delete();


            DB::commit();


            return AppResponse::successRoute(
                'purchases.index',
                'Purchase deleted successfully.'
            );
        } catch (
            \Throwable $e
        ) {

            DB::rollBack();


            Log::error(
                'Purchase deletion failed',
                [
                    'purchase_id' =>
                    $purchase->id,

                    'error' =>
                    $e->getMessage(),
                ]
            );


            return AppResponse::error(
                $e->getMessage()
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request,
        Purchase $purchase
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $this->ensurePurchaseContext(
            $purchase,
            $companyId,
            $storeId
        );


        $purchase->load([
            'company',
            'store',
            'supplier',
            'items.product.unit',
            'items.godown',
        ]);


        return view(
            'purchases.print',
            compact(
                'purchase'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET GODOWNS
    |--------------------------------------------------------------------------
    */

    public function getGodowns(
        Request $request,
        Store $store
    ) {

        $this->authorizePurchaseLookup(
            $request
        );


        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        abort_unless(
            (int) $store->id ===
                $storeId
                &&
                (int) $store->company_id ===
                $companyId,
            403
        );


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


        return response()->json(
            $godowns
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET SUPPLIERS
    |--------------------------------------------------------------------------
    |
    | Retained for compatibility with your existing route.
    |
    */

    public function getSuppliers(
        Request $request,
        Company $company
    ) {

        $this->authorizePurchaseLookup(
            $request
        );


        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        abort_unless(
            (int) $company->id ===
                $companyId,
            403
        );


        $suppliers =
            Supplier::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'status',
                true
            )
            ->whereHas(
                'stores',
                function ($query) use (
                    $storeId
                ) {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->orderBy(
                'supplier_name'
            )
            ->get([
                'id',
                'supplier_name',
            ]);


        return response()->json(
            $suppliers
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET STORES
    |--------------------------------------------------------------------------
    |
    | Business screens no longer select arbitrary branches.
    | We return only the currently active branch.
    |
    */

    public function getStores(
        Request $request,
        $companyId
    ) {

        $this->authorizePurchaseLookup(
            $request
        );


        [
            $activeCompanyId,
            $storeId,
        ] = $this->context(
            $request
        );


        abort_unless(
            (int) $companyId ===
                $activeCompanyId,
            403
        );


        $stores =
            Store::query()
            ->where(
                'id',
                $storeId
            )
            ->where(
                'company_id',
                $activeCompanyId
            )
            ->where(
                'status',
                true
            )
            ->get([
                'id',
                'store_name',
                'store_code',
            ]);


        return response()->json(
            $stores
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE PURCHASE LOOKUP
    |--------------------------------------------------------------------------
    |
    | Search and lookup endpoints are shared by create/edit/view workflows.
    |
    */

    private function authorizePurchaseLookup(
        Request $request
    ): void {

        $user =
            $request->user();


        abort_unless(
            $user
                &&
                (
                    $user->can(
                        'purchase.view'
                    )
                    ||
                    $user->can(
                        'purchase.create'
                    )
                    ||
                    $user->can(
                        'purchase.edit'
                    )
                ),
            403
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE PURCHASE REQUEST
    |--------------------------------------------------------------------------
    |
    | New React form format:
    |
    | items: [
    |   {
    |       product_id: 1,
    |       godown_id: null,
    |       quantity: 10,
    |       free_quantity: 2,
    |       purchase_price: 100,
    |       discount: 50,
    |       gst_percent: 18
    |   }
    | ]
    |
    */

    private function validatePurchaseRequest(
        Request $request
    ): array {

        return $request->validate([

            'supplier_id' => [
                'required',
                'integer',
                'exists:suppliers,id',
            ],


            'purchase_date' => [
                'required',
                'date',
            ],


            'invoice_no' => [
                'nullable',
                'string',
                'max:255',
            ],


            'invoice_date' => [
                'nullable',
                'date',
            ],


            'tax_type' => [
                'required',
                Rule::in([
                    'gst',
                    'non_gst',
                ]),
            ],


            'other_charges' => [
                'nullable',
                'numeric',
                'min:0',
            ],


            'paid_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],


            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],


            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */

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


            'items.*.godown_id' => [
                'nullable',
                'integer',
                'exists:godowns,id',
            ],


            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],


            'items.*.free_quantity' => [
                'nullable',
                'numeric',
                'min:0',
            ],


            'items.*.purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],


            'items.*.discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],


            'items.*.gst_percent' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE ITEMS
    |--------------------------------------------------------------------------
    */

    private function calculateItems(
        array $items,
        string $taxType,
        int $companyId,
        int $storeId
    ): array {

        $calculatedItems =
            [];


        $subtotal =
            0;


        $totalDiscount =
            0;


        $totalTax =
            0;


        $totalLineAmount =
            0;


        foreach (
            $items
            as $index => $item
        ) {

            /*
            |--------------------------------------------------------------------------
            | Product Branch Isolation
            |--------------------------------------------------------------------------
            */

            $product =
                Product::query()
                ->where(
                    'id',
                    $item['product_id']
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
                    "items.{$index}.product_id" =>
                    'Selected product does not belong to the active branch or is inactive.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            $godownId =
                !empty($item['godown_id'])
                ? (int) $item['godown_id']
                : null;


            if (
                $godownId !== null
            ) {

                $godownExists =
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
                    ->exists();


                if (
                    !$godownExists
                ) {

                    throw ValidationException::withMessages([
                        "items.{$index}.godown_id" =>
                        'Selected godown does not belong to the active branch.',
                    ]);
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Quantities
            |--------------------------------------------------------------------------
            */

            $quantity =
                round(
                    (float)
                    $item['quantity'],
                    2
                );


            $freeQuantity =
                round(
                    (float)
                    (
                        $item['free_quantity']
                        ?? 0
                    ),
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */

            $purchasePrice =
                round(
                    (float)
                    $item['purchase_price'],
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Gross
            |--------------------------------------------------------------------------
            |
            | Free quantity has no purchase cost.
            |
            */

            $grossAmount =
                round(
                    $quantity
                        *
                        $purchasePrice,
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Discount
            |--------------------------------------------------------------------------
            |
            | Discount is stored as line discount amount.
            |
            */

            $discount =
                round(
                    (float)
                    (
                        $item['discount']
                        ?? 0
                    ),
                    2
                );


            if (
                $discount >
                $grossAmount
            ) {

                throw ValidationException::withMessages([
                    "items.{$index}.discount" =>
                    'Line discount cannot exceed the gross line amount.',
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Taxable
            |--------------------------------------------------------------------------
            */

            $taxableAmount =
                round(
                    max(
                        0,
                        $grossAmount
                            -
                            $discount
                    ),
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | GST
            |--------------------------------------------------------------------------
            */

            $gstPercent =
                $taxType ===
                'gst'
                ? round(
                    (float)
                    (
                        $item['gst_percent']
                        ?? $product->gst_percent
                        ?? 0
                    ),
                    2
                )
                : 0;


            $gstAmount =
                $taxType ===
                'gst'
                ? round(
                    $taxableAmount
                        *
                        $gstPercent
                        /
                        100,
                    2
                )
                : 0;


            /*
            |--------------------------------------------------------------------------
            | Line Total
            |--------------------------------------------------------------------------
            */

            $lineTotal =
                round(
                    $taxableAmount
                        +
                        $gstAmount,
                    2
                );


            /*
            |--------------------------------------------------------------------------
            | Accumulate
            |--------------------------------------------------------------------------
            */

            $subtotal +=
                $grossAmount;


            $totalDiscount +=
                $discount;


            $totalTax +=
                $gstAmount;


            $totalLineAmount +=
                $lineTotal;


            /*
            |--------------------------------------------------------------------------
            | Item
            |--------------------------------------------------------------------------
            */

            $calculatedItems[] = [

                'product' =>
                $product,

                'godown_id' =>
                $godownId,

                'quantity' =>
                $quantity,

                'free_quantity' =>
                $freeQuantity,

                'purchase_price' =>
                $purchasePrice,

                'discount' =>
                $discount,

                'taxable_amount' =>
                $taxableAmount,

                'gst_percent' =>
                $gstPercent,

                'gst_amount' =>
                $gstAmount,

                'line_total' =>
                $lineTotal,
            ];
        }


        return [

            'items' =>
            $calculatedItems,

            'subtotal' =>
            round(
                $subtotal,
                2
            ),

            'discount' =>
            round(
                $totalDiscount,
                2
            ),

            'tax_amount' =>
            round(
                $totalTax,
                2
            ),

            'line_total' =>
            round(
                $totalLineAmount,
                2
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE SUPPLIER
    |--------------------------------------------------------------------------
    */

    private function validateSupplier(
        int $supplierId,
        int $companyId,
        int $storeId
    ): Supplier {

        $supplier =
            Supplier::query()
            ->where(
                'id',
                $supplierId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'status',
                true
            )
            ->whereHas(
                'stores',
                function ($query) use (
                    $storeId
                ) {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->first();


        if (
            !$supplier
        ) {

            throw ValidationException::withMessages([
                'supplier_id' =>
                'Selected supplier is not available for the active branch.',
            ]);
        }


        return $supplier;
    }


    /*
    |--------------------------------------------------------------------------
    | DUPLICATE SUPPLIER INVOICE
    |--------------------------------------------------------------------------
    */

    private function validateDuplicateInvoice(
        int $companyId,
        int $storeId,
        int $supplierId,
        ?string $invoiceNo,
        ?int $ignorePurchaseId = null
    ): void {

        $invoiceNo =
            $this->nullableString(
                $invoiceNo
            );


        if (
            $invoiceNo === null
        ) {
            return;
        }


        $query =
            Purchase::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'supplier_id',
                $supplierId
            )
            ->where(
                'invoice_no',
                $invoiceNo
            );


        if (
            $ignorePurchaseId !== null
        ) {

            $query->where(
                'id',
                '!=',
                $ignorePurchaseId
            );
        }


        if (
            $query->exists()
        ) {

            throw ValidationException::withMessages([
                'invoice_no' =>
                'This supplier invoice number already exists in the active branch.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CONTEXT
    |--------------------------------------------------------------------------
    |
    | Browser NEVER decides company_id/store_id.
    |
    | Business context comes from the authenticated branch session.
    |
    */

    private function context(
        Request $request
    ): array {

        $user =
            $request->user();


        abort_unless(
            $user,
            401
        );


        $companyId =
            (int)
            session(
                'active_company_id'
            );


        $storeId =
            (int)
            session(
                'active_store_id'
            );


        abort_unless(
            $companyId > 0
                &&
                $storeId > 0,
            403,
            'No active business branch selected.'
        );


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company =
            Company::query()
            ->where(
                'id',
                $companyId
            )
            ->where(
                'status',
                true
            )
            ->first();


        abort_unless(
            $company,
            403,
            'The active company is unavailable.'
        );


        /*
        |--------------------------------------------------------------------------
        | Store
        |--------------------------------------------------------------------------
        */

        $store =
            Store::query()
            ->where(
                'id',
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


        /*
        |--------------------------------------------------------------------------
        | Normal User Company Protection
        |--------------------------------------------------------------------------
        */

        if (
            !$user->is_super_user
            &&
            (int) $user->company_id !==
            $companyId
        ) {

            abort(
                403,
                'You cannot access this company.'
            );
        }


        return [
            $companyId,
            $storeId,
            $company,
            $store,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | PURCHASE CONTEXT
    |--------------------------------------------------------------------------
    */

    private function ensurePurchaseContext(
        Purchase $purchase,
        int $companyId,
        int $storeId
    ): void {

        abort_unless(
            (int) $purchase->company_id ===
                $companyId
                &&
                (int) $purchase->store_id ===
                $storeId,
            404
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PREVIEW PURCHASE NUMBER
    |--------------------------------------------------------------------------
    */

    private function previewPurchaseNumber(
        int $storeId
    ): string {

        $lastPurchase =
            Purchase::query()
            ->where(
                'store_id',
                $storeId
            )
            ->orderByDesc(
                'id'
            )
            ->first([
                'purchase_no',
            ]);


        $next =
            $this->extractPurchaseSequence(
                $lastPurchase?->purchase_no
            )
            +
            1;


        return 'PUR'
            .
            str_pad(
                (string) $next,
                6,
                '0',
                STR_PAD_LEFT
            );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE PURCHASE NUMBER
    |--------------------------------------------------------------------------
    */

    private function generatePurchaseNumber(
        int $storeId
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Lock the branch's latest purchase while generating the next number.
        |--------------------------------------------------------------------------
        */

        $lastPurchase =
            Purchase::query()
            ->where(
                'store_id',
                $storeId
            )
            ->orderByDesc(
                'id'
            )
            ->lockForUpdate()
            ->first([
                'purchase_no',
            ]);


        $next =
            $this->extractPurchaseSequence(
                $lastPurchase?->purchase_no
            )
            +
            1;


        return 'PUR'
            .
            str_pad(
                (string) $next,
                6,
                '0',
                STR_PAD_LEFT
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EXTRACT NUMBER
    |--------------------------------------------------------------------------
    */

    private function extractPurchaseSequence(
        ?string $purchaseNo
    ): int {

        if (
            !$purchaseNo
        ) {
            return 0;
        }


        if (
            preg_match(
                '/(\d+)$/',
                $purchaseNo,
                $matches
            )
        ) {

            return (int) $matches[1];
        }


        return 0;
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

    public function pdf(
        Request $request,
        Purchase $purchase
    ) {

        /*
        |--------------------------------------------------------------------------
        | Business Context
        |--------------------------------------------------------------------------
        */

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Purchase Isolation
        |--------------------------------------------------------------------------
        */

        $this->ensurePurchaseContext(
            $purchase,
            $companyId,
            $storeId
        );


        /*
        |--------------------------------------------------------------------------
        | Load Purchase Data
        |--------------------------------------------------------------------------
        */

        $purchase->load([
            'company',
            'store',
            'supplier',
            'items.product.unit',
            'items.godown',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'purchases.pdf',
                compact(
                    'purchase'
                )
            )
            ->setPaper(
                'a4',
                'landscape'
            );


        /*
        |--------------------------------------------------------------------------
        | Download
        |--------------------------------------------------------------------------
        */

        $filename =
            'purchase-'
            .
            $purchase->purchase_no
            .
            '.pdf';


        return $pdf->download(
            $filename
        );
    }
}
