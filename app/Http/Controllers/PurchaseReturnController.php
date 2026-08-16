<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Services\StockService;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PurchaseReturnController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | MIDDLEWARE
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:purchase-return.view',
                only: ['index', 'show', 'print']
            ),

            new Middleware(
                'permission:purchase-return.create',
                only: ['create', 'store', 'search', 'getInvoice']
            ),

            new Middleware(
                'permission:purchase-return.edit',
                only: ['edit', 'update']
            ),

            new Middleware(
                'permission:purchase-return.delete',
                only: ['destroy']
            ),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $returns = PurchaseReturn::with([
            'purchase',
            'supplier',
        ])
            ->when($request->filled('search'), function ($query) use ($request) {

                $search = trim($request->search);

                $query->where(function ($q) use ($search) {

                    $q->where(
                        'return_no',
                        'like',
                        "%{$search}%"
                    )

                        ->orWhereHas('supplier', function ($q) use ($search) {

                            $q->where(
                                'supplier_name',
                                'like',
                                "%{$search}%"
                            );
                        })

                        ->orWhereHas('purchase', function ($q) use ($search) {

                            $q->where(
                                'purchase_no',
                                'like',
                                "%{$search}%"
                            );
                        });
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view(
            'purchase_returns.index',
            compact('returns')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Super admin can select any active company.
        |
        */

        if ($user->is_super_user) {

            $companies = Company::where('status', 1)
                ->orderBy('company_name')
                ->get();
        } else {

            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            |
            | Normal user can only work with their assigned company.
            |
            */

            $companies = Company::where('id', $user->company_id)
                ->where('status', 1)
                ->get();
        }


        return view(
            'purchase_returns.create',
            compact('companies')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH PURCHASE
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string|max:100',
            'company_id' => 'required|integer|exists:companies,id',
        ]);

        $search = trim($request->search);

        $purchases = Purchase::with([
            'supplier',
        ])
            ->where(
                'company_id',
                $request->company_id
            )
            ->where(function ($query) use ($search) {

                $query->where(
                    'purchase_no',
                    'like',
                    "%{$search}%"
                )

                    ->orWhereHas('supplier', function ($q) use ($search) {

                        $q->where(
                            'supplier_name',
                            'like',
                            "%{$search}%"
                        );
                    });
            })
            ->latest('id')
            ->limit(10)
            ->get();

        return response()->json($purchases);
    }


    /*
    |--------------------------------------------------------------------------
    | GET PURCHASE INVOICE
    |--------------------------------------------------------------------------
    */

    public function getInvoice(Request $request, $id)
    {
        $request->validate([
            'company_id' =>
            'required|integer|exists:companies,id',
        ]);

        $purchase = Purchase::with([
            'supplier',
            'items.product.unit',
            'items.godown',
        ])
            ->where(
                'company_id',
                $request->company_id
            )
            ->findOrFail($id);


        foreach ($purchase->items as $item) {

            /*
            |--------------------------------------------------------------------------
            | Already Returned
            |
            | IMPORTANT:
            | Return is calculated using:
            |
            | purchase
            | + product
            | + godown
            |--------------------------------------------------------------------------
            */

            $returnedQty = PurchaseReturnItem::where(
                'product_id',
                $item->product_id
            )
                ->where(function ($query) use ($item) {

                    if ($item->godown_id === null) {

                        $query->whereNull('godown_id');
                    } else {

                        $query->where(
                            'godown_id',
                            $item->godown_id
                        );
                    }
                })
                ->whereHas('purchaseReturn', function ($query) use ($purchase) {

                    $query->where(
                        'purchase_id',
                        $purchase->id
                    );
                })
                ->sum('quantity');


            /*
            |--------------------------------------------------------------------------
            | Returned Quantity
            |--------------------------------------------------------------------------
            */

            $item->returned_qty =
                (float) $returnedQty;


            /*
            |--------------------------------------------------------------------------
            | Balance Quantity
            |--------------------------------------------------------------------------
            */

            $item->balance_qty = max(
                0,
                (float) $item->quantity
                    - (float) $returnedQty
            );


            /*
            |--------------------------------------------------------------------------
            | Godown
            |--------------------------------------------------------------------------
            */

            $item->godown_id =
                $item->godown_id;

            $item->godown_name =
                $item->godown->godown_name
                ?? 'Shop / No Godown';
        }


        return response()->json($purchase);
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        StockService $stockService
    ) {

        $request->validate([

            'purchase_id' =>
            'required|integer|exists:purchases,id',

            'supplier_id' =>
            'required|integer|exists:suppliers,id',

            'company_id' =>
            'required|integer|exists:companies,id',

            'return_date' =>
            'required|date',

            'product_id' =>
            'required|array|min:1',

            'product_id.*' =>
            'required|integer|exists:products,id',

            'godown_id' =>
            'required|array',

            'godown_id.*' =>
            'nullable|integer|exists:godowns,id',

            'qty' =>
            'required|array',

            'qty.*' =>
            'numeric|min:0',

            'price' =>
            'required|array',

            'price.*' =>
            'numeric|min:0',

            'gst' =>
            'required|array',

            'gst.*' =>
            'numeric|min:0',

        ]);


        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | PURCHASE
            |--------------------------------------------------------------------------
            */

            $purchase = Purchase::with([
                'items.product',
            ])
                ->findOrFail(
                    $request->purchase_id
                );


            /*
            |--------------------------------------------------------------------------
            | Security Check
            |--------------------------------------------------------------------------
            */

            if (
                (int) $purchase->company_id
                !==
                (int) $request->company_id
            ) {

                throw new Exception(
                    'Selected purchase does not belong to selected company.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Supplier Check
            |--------------------------------------------------------------------------
            */

            if (
                (int) $purchase->supplier_id
                !==
                (int) $request->supplier_id
            ) {

                throw new Exception(
                    'Invalid supplier for selected purchase.'
                );
            }


            $companyId =
                (int) $purchase->company_id;

            $storeId =
                (int) $purchase->store_id;


            /*
            |--------------------------------------------------------------------------
            | Generate Return Number
            |--------------------------------------------------------------------------
            */

            $returnNo = 'PR-' . str_pad(
                (PurchaseReturn::max('id') ?? 0) + 1,
                6,
                '0',
                STR_PAD_LEFT
            );


            /*
            |--------------------------------------------------------------------------
            | Totals
            |--------------------------------------------------------------------------
            */

            $subTotal = 0;
            $gstTotal = 0;
            $grandTotal = 0;


            /*
            |--------------------------------------------------------------------------
            | Create Header
            |--------------------------------------------------------------------------
            */

            $return = PurchaseReturn::create([

                'company_id' =>
                $companyId,

                'purchase_id' =>
                $purchase->id,

                'supplier_id' =>
                $purchase->supplier_id,

                'return_no' =>
                $returnNo,

                'return_date' =>
                $request->return_date,

                'sub_total' =>
                0,

                'gst_total' =>
                0,

                'total_amount' =>
                0,

                'remarks' =>
                $request->remarks,

            ]);


            /*
            |--------------------------------------------------------------------------
            | ITEMS
            |--------------------------------------------------------------------------
            */

            foreach ($request->product_id as $key => $productId) {

                $qty = (float) (
                    $request->qty[$key] ?? 0
                );


                /*
                | Ignore zero quantity rows
                */

                if ($qty <= 0) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Godown
                |--------------------------------------------------------------------------
                */

                $godownId =
                    $request->godown_id[$key] ?? null;


                /*
                |--------------------------------------------------------------------------
                | Find Original Purchase Item
                |
                | Match PRODUCT + GODOWN
                |--------------------------------------------------------------------------
                */

                $purchaseItem = $purchase->items
                    ->first(function ($item) use (
                        $productId,
                        $godownId
                    ) {

                        return
                            (int) $item->product_id
                            ===
                            (int) $productId

                            &&

                            (
                                $item->godown_id == $godownId
                            );
                    });


                if (!$purchaseItem) {

                    throw new Exception(
                        "Purchase item not found for product ID {$productId}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Already Returned
                |
                | PRODUCT + GODOWN + PURCHASE
                |--------------------------------------------------------------------------
                */

                $returnedQty =
                    PurchaseReturnItem::where(
                        'product_id',
                        $productId
                    )
                    ->where(function ($query) use ($godownId) {

                        if ($godownId === null) {

                            $query->whereNull('godown_id');
                        } else {

                            $query->where(
                                'godown_id',
                                $godownId
                            );
                        }
                    })
                    ->whereHas(
                        'purchaseReturn',
                        function ($query) use ($purchase) {

                            $query->where(
                                'purchase_id',
                                $purchase->id
                            );
                        }
                    )
                    ->sum('quantity');


                /*
                |--------------------------------------------------------------------------
                | Available Balance
                |--------------------------------------------------------------------------
                */

                $balanceQty =
                    (float) $purchaseItem->quantity
                    -
                    (float) $returnedQty;


                $balanceQty =
                    max(0, $balanceQty);


                /*
                |--------------------------------------------------------------------------
                | Validate Quantity
                |--------------------------------------------------------------------------
                */

                if ($qty > $balanceQty) {

                    throw new Exception(

                        'Return quantity exceeds available balance for product: '
                            .
                            $purchaseItem->product->product_name
                            .
                            '. Available: '
                            .
                            number_format(
                                $balanceQty,
                                2
                            )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Product
                |--------------------------------------------------------------------------
                */

                $product =
                    Product::findOrFail(
                        $productId
                    );


                /*
                |--------------------------------------------------------------------------
                | Price
                |--------------------------------------------------------------------------
                */

                $price = (float) (
                    $request->price[$key] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | GST
                |--------------------------------------------------------------------------
                */

                $gstPercent = (float) (
                    $request->gst[$key] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | Calculate Amount Server Side
                |--------------------------------------------------------------------------
                */

                $basicAmount =
                    $qty * $price;


                $gstAmount =
                    $basicAmount
                    *
                    $gstPercent
                    /
                    100;


                $lineTotal =
                    $basicAmount
                    +
                    $gstAmount;


                $subTotal +=
                    $basicAmount;


                $gstTotal +=
                    $gstAmount;


                $grandTotal +=
                    $lineTotal;


                /*
                |--------------------------------------------------------------------------
                | Create Return Item
                |--------------------------------------------------------------------------
                */

                PurchaseReturnItem::create([

                    'purchase_return_id' =>
                    $return->id,

                    'product_id' =>
                    $productId,

                    'godown_id' =>
                    $godownId,

                    'quantity' =>
                    $qty,

                    'purchase_price' =>
                    $price,

                    'gst_percent' =>
                    $gstPercent,

                    'gst_amount' =>
                    $gstAmount,

                    'line_total' =>
                    $lineTotal,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Reduce Stock
                |--------------------------------------------------------------------------
                */

                $stockService->decreaseStock(

                    $product,

                    $qty,

                    'PURCHASE_RETURN',

                    $companyId,

                    $storeId,

                    $godownId,

                    $return->id,

                    $return->return_no,

                    $price,

                    'Purchase Return'

                );
            }


            /*
            |--------------------------------------------------------------------------
            | At Least One Item
            |--------------------------------------------------------------------------
            */

            if ($grandTotal <= 0) {

                throw new Exception(
                    'Please enter return quantity for at least one product.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update Header Totals
            |--------------------------------------------------------------------------
            */

            $return->update([

                'sub_total' =>
                $subTotal,

                'gst_total' =>
                $gstTotal,

                'total_amount' =>
                $grandTotal,

            ]);


            DB::commit();


            return redirect()
                ->route('purchase-returns.index')
                ->with(
                    'success',
                    'Purchase Return Saved Successfully.'
                );
        } catch (Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $return = PurchaseReturn::with([
            'purchase',
            'supplier',
            'items.product.unit',
            'items.godown',
        ])
            ->findOrFail($id);


        return view(
            'purchase_returns.show',
            compact('return')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $purchaseReturn = PurchaseReturn::with([
            'supplier',
            'purchase.company',
            'purchase.store',
            'items.product.unit',
            'items.godown',
        ])->findOrFail($id);

        foreach ($purchaseReturn->items as $item) {

            /*
            |--------------------------------------------------------------------------
            | Find Original Purchase Item
            |--------------------------------------------------------------------------
            */

            $purchaseItemQuery = PurchaseItem::where(
                'purchase_id',
                $purchaseReturn->purchase_id
            )
                ->where(
                    'product_id',
                    $item->product_id
                );

            /*
            |--------------------------------------------------------------------------
            | Match Godown
            |--------------------------------------------------------------------------
            */

            if ($item->godown_id === null) {

                $purchaseItemQuery->whereNull('godown_id');
            } else {

                $purchaseItemQuery->where(
                    'godown_id',
                    $item->godown_id
                );
            }

            $purchaseItem = $purchaseItemQuery->first();


            /*
            |--------------------------------------------------------------------------
            | Purchase Item Found
            |--------------------------------------------------------------------------
            */

            if ($purchaseItem) {

                /*
                |--------------------------------------------------------------------------
                | Previous Returns
                | Excluding Current Return
                |--------------------------------------------------------------------------
                */

                $alreadyReturnedQuery = PurchaseReturnItem::where(
                    'product_id',
                    $item->product_id
                )
                    ->where(
                        'purchase_return_id',
                        '!=',
                        $purchaseReturn->id
                    )
                    ->whereHas(
                        'purchaseReturn',
                        function ($q) use ($purchaseReturn) {

                            $q->where(
                                'purchase_id',
                                $purchaseReturn->purchase_id
                            );
                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | Match Godown
                |--------------------------------------------------------------------------
                */

                if ($item->godown_id === null) {

                    $alreadyReturnedQuery->whereNull('godown_id');
                } else {

                    $alreadyReturnedQuery->where(
                        'godown_id',
                        $item->godown_id
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Previous Returned Quantity
                |--------------------------------------------------------------------------
                */

                $alreadyReturned = (float) $alreadyReturnedQuery
                    ->sum('quantity');


                /*
                |--------------------------------------------------------------------------
                | Current Return Quantity
                |--------------------------------------------------------------------------
                */

                $currentReturnQty = (float) $item->quantity;


                /*
                |--------------------------------------------------------------------------
                | Maximum Quantity Available For Current Edit
                |--------------------------------------------------------------------------
                |
                | Current return is excluded above.
                |
                */

                $balanceQty = max(
                    0,
                    (float) $purchaseItem->quantity
                        - $alreadyReturned
                );


                /*
                |--------------------------------------------------------------------------
                | Attach Purchase Information
                |--------------------------------------------------------------------------
                */

                $item->purchaseItem = (object) [

                    // Original purchased quantity
                    'quantity' => (float) $purchaseItem->quantity,

                    // Previous returns excluding current return
                    'returned_qty' => $alreadyReturned,

                    // Current return being edited
                    'current_return_qty' => $currentReturnQty,

                    // Maximum allowed for current return
                    'balance_qty' => $balanceQty,

                ];
            }
        }

        return view(
            'purchase_returns.edit',
            compact('purchaseReturn')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id,
        StockService $stockService
    ) {

        $request->validate([

            'return_date' =>
            'required|date',

            'product_id' =>
            'required|array|min:1',

            'product_id.*' =>
            'required|integer|exists:products,id',

            'godown_id' =>
            'required|array',

            'godown_id.*' =>
            'nullable|integer|exists:godowns,id',

            'qty' =>
            'required|array',

            'qty.*' =>
            'numeric|min:0',

            'price' =>
            'required|array',

            'price.*' =>
            'numeric|min:0',

            'gst' =>
            'required|array',

            'gst.*' =>
            'numeric|min:0',

        ]);


        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Existing Return
            |--------------------------------------------------------------------------
            */

            $purchaseReturn =
                PurchaseReturn::with([
                    'items',
                    'purchase.items.product',
                ])
                ->findOrFail($id);


            $purchase =
                $purchaseReturn->purchase;


            $companyId =
                (int) $purchase->company_id;


            $storeId =
                (int) $purchase->store_id;


            /*
            |--------------------------------------------------------------------------
            | STEP 1
            | Restore Old Return Stock
            |--------------------------------------------------------------------------
            */

            foreach ($purchaseReturn->items as $oldItem) {

                $product =
                    Product::findOrFail(
                        $oldItem->product_id
                    );


                $stockService->increaseStock(

                    $product,

                    (float) $oldItem->quantity,

                    'PURCHASE_RETURN_EDIT_REVERSE',

                    $companyId,

                    $storeId,

                    $oldItem->godown_id,

                    $purchaseReturn->id,

                    $purchaseReturn->return_no,

                    (float) $oldItem->purchase_price,

                    'Reverse old Purchase Return'

                );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Old Items
            |--------------------------------------------------------------------------
            */

            PurchaseReturnItem::where(
                'purchase_return_id',
                $purchaseReturn->id
            )->delete();


            /*
            |--------------------------------------------------------------------------
            | New Totals
            |--------------------------------------------------------------------------
            */

            $subTotal = 0;
            $gstTotal = 0;
            $grandTotal = 0;


            /*
            |--------------------------------------------------------------------------
            | STEP 2
            | Create New Items
            |--------------------------------------------------------------------------
            */

            foreach ($request->product_id as $key => $productId) {

                $qty = (float) (
                    $request->qty[$key] ?? 0
                );


                if ($qty <= 0) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Godown
                |--------------------------------------------------------------------------
                */

                $godownId =
                    $request->godown_id[$key] ?? null;


                /*
                |--------------------------------------------------------------------------
                | Purchase Item
                |
                | PRODUCT + GODOWN
                |--------------------------------------------------------------------------
                */

                $purchaseItem =
                    $purchase->items
                    ->first(function ($item) use (
                        $productId,
                        $godownId
                    ) {

                        return
                            (int) $item->product_id
                            ===
                            (int) $productId

                            &&

                            $item->godown_id == $godownId;
                    });


                if (!$purchaseItem) {

                    throw new Exception(
                        "Purchase item not found for product ID {$productId}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Previous Returns
                |
                | Current return has already been deleted.
                |--------------------------------------------------------------------------
                */

                $alreadyReturned =
                    PurchaseReturnItem::where(
                        'product_id',
                        $productId
                    )
                    ->where(function ($query) use ($godownId) {

                        if ($godownId === null) {

                            $query->whereNull('godown_id');
                        } else {

                            $query->where(
                                'godown_id',
                                $godownId
                            );
                        }
                    })
                    ->whereHas(
                        'purchaseReturn',
                        function ($query) use ($purchaseReturn) {

                            $query->where(
                                'purchase_id',
                                $purchaseReturn->purchase_id
                            );
                        }
                    )
                    ->sum('quantity');


                /*
                |--------------------------------------------------------------------------
                | Balance
                |--------------------------------------------------------------------------
                */

                $balanceQty = max(
                    0,
                    (float) $purchaseItem->quantity
                        -
                        (float) $alreadyReturned
                );


                /*
                |--------------------------------------------------------------------------
                | Validate Quantity
                |--------------------------------------------------------------------------
                */

                if ($qty > $balanceQty) {

                    throw new Exception(

                        'Return quantity exceeds available balance for product: '
                            .
                            $purchaseItem->product->product_name
                            .
                            '. Available: '
                            .
                            number_format(
                                $balanceQty,
                                2
                            )

                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Product
                |--------------------------------------------------------------------------
                */

                $product =
                    Product::findOrFail(
                        $productId
                    );


                /*
                |--------------------------------------------------------------------------
                | Price / GST
                |--------------------------------------------------------------------------
                */

                $price = (float) (
                    $request->price[$key] ?? 0
                );


                $gstPercent = (float) (
                    $request->gst[$key] ?? 0
                );


                /*
                |--------------------------------------------------------------------------
                | Calculate
                |--------------------------------------------------------------------------
                */

                $basicAmount =
                    $qty * $price;


                $gstAmount =
                    $basicAmount
                    *
                    $gstPercent
                    /
                    100;


                $lineTotal =
                    $basicAmount
                    +
                    $gstAmount;


                $subTotal +=
                    $basicAmount;


                $gstTotal +=
                    $gstAmount;


                $grandTotal +=
                    $lineTotal;


                /*
                |--------------------------------------------------------------------------
                | Create Item
                |--------------------------------------------------------------------------
                */

                PurchaseReturnItem::create([

                    'purchase_return_id' =>
                    $purchaseReturn->id,

                    'product_id' =>
                    $productId,

                    'godown_id' =>
                    $godownId,

                    'quantity' =>
                    $qty,

                    'purchase_price' =>
                    $price,

                    'gst_percent' =>
                    $gstPercent,

                    'gst_amount' =>
                    $gstAmount,

                    'line_total' =>
                    $lineTotal,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Decrease Stock
                |--------------------------------------------------------------------------
                */

                $stockService->decreaseStock(

                    $product,

                    $qty,

                    'PURCHASE_RETURN',

                    $companyId,

                    $storeId,

                    $godownId,

                    $purchaseReturn->id,

                    $purchaseReturn->return_no,

                    $price,

                    'Purchase Return'

                );
            }


            /*
            |--------------------------------------------------------------------------
            | At Least One Item
            |--------------------------------------------------------------------------
            */

            if ($grandTotal <= 0) {

                throw new Exception(
                    'Please enter return quantity for at least one product.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */

            $purchaseReturn->update([

                'return_date' =>
                $request->return_date,

                'sub_total' =>
                $subTotal,

                'gst_total' =>
                $gstTotal,

                'total_amount' =>
                $grandTotal,

                'remarks' =>
                $request->remarks,

            ]);


            DB::commit();


            return redirect()
                ->route('purchase-returns.index')
                ->with(
                    'success',
                    'Purchase Return Updated Successfully.'
                );
        } catch (Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        $id,
        StockService $stockService
    ) {

        DB::beginTransaction();

        try {

            $purchaseReturn =
                PurchaseReturn::with([
                    'items',
                    'purchase',
                ])
                ->findOrFail($id);


            $companyId =
                (int) $purchaseReturn
                    ->purchase
                    ->company_id;


            $storeId =
                (int) $purchaseReturn
                    ->purchase
                    ->store_id;


            /*
            |--------------------------------------------------------------------------
            | Restore Stock
            |--------------------------------------------------------------------------
            */

            foreach ($purchaseReturn->items as $item) {

                $product =
                    Product::findOrFail(
                        $item->product_id
                    );


                $stockService->increaseStock(

                    $product,

                    (float) $item->quantity,

                    'PURCHASE_RETURN_DELETE',

                    $companyId,

                    $storeId,

                    $item->godown_id,

                    $purchaseReturn->id,

                    $purchaseReturn->return_no,

                    (float) $item->purchase_price,

                    'Reverse deleted Purchase Return'

                );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Items
            |--------------------------------------------------------------------------
            */

            PurchaseReturnItem::where(
                'purchase_return_id',
                $purchaseReturn->id
            )->delete();


            /*
            |--------------------------------------------------------------------------
            | Delete Header
            |--------------------------------------------------------------------------
            */

            $purchaseReturn->delete();


            DB::commit();


            return redirect()
                ->route('purchase-returns.index')
                ->with(
                    'success',
                    'Purchase Return deleted successfully.'
                );
        } catch (Exception $e) {

            DB::rollBack();

            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print($id)
    {
        $purchaseReturn = PurchaseReturn::with([
            'purchase',
            'supplier',
            'items.product.unit',
            'items.godown',
        ])
            ->findOrFail($id);


        return view(
            'purchase_returns.print',
            compact('purchaseReturn')
        );
    }
}
