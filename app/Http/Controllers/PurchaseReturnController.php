<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PurchaseReturnController extends Controller implements HasMiddleware
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
    | Get Current Company
    |--------------------------------------------------------------------------
    |
    | Super Admin:
    |     company_id comes from request
    |
    | Normal Admin/User:
    |     company_id comes from logged-in user
    |
    */

    private function getCompanyId(Request $request)
    {
        if (auth()->user()->is_super_user) {

            return $request->company_id;
        }

        return auth()->user()->company_id;
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $companyId = $this->getCompanyId($request);

        $returns = PurchaseReturn::with([
            'purchase',
            'supplier',
        ])

            /*
            |--------------------------------------------------------------------------
            | Company Filter
            |--------------------------------------------------------------------------
            */

            ->when($companyId, function ($query) use ($companyId) {

                $query->where('company_id', $companyId);
            })

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            ->when($request->search, function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where(
                        'return_no',
                        'like',
                        "%{$search}%"
                    )

                        ->orWhereHas('supplier', function ($q2) use ($search) {

                            $q2->where(
                                'supplier_name',
                                'like',
                                "%{$search}%"
                            );
                        })

                        ->orWhereHas('purchase', function ($q3) use ($search) {

                            $q3->where(
                                'purchase_no',
                                'like',
                                "%{$search}%"
                            );
                        });
                });
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Companies for Super Admin
        |--------------------------------------------------------------------------
        */

        $companies = collect();

        if (auth()->user()->is_super_user) {

            $companies = Company::where('status', 1)
                ->orderBy('company_name')
                ->get();
        }


        return view(
            'purchase_returns.index',
            compact(
                'returns',
                'companies'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $companies = Company::where('status', 1)
            ->orderBy('company_name')
            ->get();

        return view(
            'purchase_returns.create',
            compact('companies')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Search Purchase Invoice
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $companyId = $this->getCompanyId($request);


        if (!$companyId) {

            return response()->json([
                'message' => 'Please select company first.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Search Purchase
        |--------------------------------------------------------------------------
        */

        $purchases = Purchase::with([
            'supplier',
        ])

            ->where('company_id', $companyId)

            ->where(function ($query) use ($request) {

                $search = $request->search;

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

            ->latest()
            ->limit(10)
            ->get();


        return response()->json($purchases);
    }


    /*
    |--------------------------------------------------------------------------
    | Get Purchase Invoice
    |--------------------------------------------------------------------------
    */

    public function getInvoice($id, Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $companyId = $this->getCompanyId($request);


        if (!$companyId) {

            return response()->json([
                'message' => 'Company is required.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Load Purchase
        |--------------------------------------------------------------------------
        */

        $purchase = Purchase::with([
            'supplier',
            'store',
            'items.product.unit',
            'items.godown',
        ])

            ->where('company_id', $companyId)

            ->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Calculate Return Balance
        |--------------------------------------------------------------------------
        */

        foreach ($purchase->items as $item) {

            $returnedQty = PurchaseReturnItem::where(
                'product_id',
                $item->product_id
            )

                ->where(
                    'godown_id',
                    $item->godown_id
                )

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


            $item->returned_qty = $returnedQty;

            $item->balance_qty =
                max(
                    0,
                    $item->quantity - $returnedQty
                );
        }


        return response()->json($purchase);
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'purchase_id' => [
                'required',
                'exists:purchases,id',
            ],

            'supplier_id' => [
                'required',
                'exists:suppliers,id',
            ],

            'return_date' => [
                'required',
                'date',
            ],

            'product_id' => [
                'required',
                'array',
            ],

            'godown_id' => [
                'required',
                'array',
            ],

            'qty' => [
                'required',
                'array',
            ],

            'price' => [
                'required',
                'array',
            ],

            'gst' => [
                'required',
                'array',
            ],

            'gst_amount' => [
                'required',
                'array',
            ],

            'amount' => [
                'required',
                'array',
            ],

        ]);


        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Get Purchase
            |--------------------------------------------------------------------------
            */

            $purchase = Purchase::with([
                'items.product',
                'items.godown',
            ])

                ->findOrFail(
                    $request->purchase_id
                );


            /*
            |--------------------------------------------------------------------------
            | Company Security
            |--------------------------------------------------------------------------
            */

            if (
                !auth()->user()->is_super_user &&
                $purchase->company_id != auth()->user()->company_id
            ) {

                abort(403);
            }


            $companyId = $purchase->company_id;


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
            | Create Return Header
            |--------------------------------------------------------------------------
            */

            $return = PurchaseReturn::create([

                'company_id' => $companyId,

                'purchase_id' => $purchase->id,

                'supplier_id' => $purchase->supplier_id,

                'return_no' => $returnNo,

                'return_date' => $request->return_date,

                'sub_total' =>
                $request->total_sub_total ?? 0,

                'gst_total' =>
                $request->total_gst ?? 0,

                'total_amount' =>
                $request->total_grand_total ?? 0,

                'remarks' =>
                $request->remarks,

            ]);


            /*
            |--------------------------------------------------------------------------
            | Return Items
            |--------------------------------------------------------------------------
            */

            foreach ($request->product_id as $key => $productId) {

                $qty = (float) (
                    $request->qty[$key] ?? 0
                );


                if ($qty <= 0) {
                    continue;
                }


                $godownId = $request->godown_id[$key] ?? null;


                /*
                |--------------------------------------------------------------------------
                | Find Original Purchase Item
                |--------------------------------------------------------------------------
                */

                $purchaseItem = $purchase->items
                    ->where('product_id', $productId)
                    ->where(
                        'godown_id',
                        $godownId
                    )
                    ->first();


                if (!$purchaseItem) {

                    throw new Exception(
                        'Purchase item not found for selected product/godown.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Already Returned Quantity
                |--------------------------------------------------------------------------
                */

                $returnedQty = PurchaseReturnItem::where(
                    'product_id',
                    $productId
                )

                    ->where(
                        'godown_id',
                        $godownId
                    )

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
                | Balance
                |--------------------------------------------------------------------------
                */

                $balanceQty =
                    $purchaseItem->quantity -
                    $returnedQty;


                if ($qty > $balanceQty) {

                    throw new Exception(
                        'Return quantity exceeds balance for product: '
                            . (
                                $purchaseItem->product->product_name
                                ?? 'Unknown Product'
                            )
                    );
                }


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
                    $request->price[$key] ?? 0,

                    'gst_percent' =>
                    $request->gst[$key] ?? 0,

                    'gst_amount' =>
                    $request->gst_amount[$key] ?? 0,

                    'line_total' =>
                    $request->amount[$key] ?? 0,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Stock
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                | For now this keeps your existing Product stock logic.
                | Replace this section with StockService once your
                | godown stock ledger is fully connected.
                |
                */

                $product = Product::find(
                    $productId
                );


                if ($product) {

                    if (
                        $product->current_stock < $qty
                    ) {

                        throw new Exception(
                            'Insufficient stock for '
                                . $product->product_name
                        );
                    }


                    $product->current_stock -= $qty;

                    $product->save();
                }
            }


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
    | Show
    |--------------------------------------------------------------------------
    */

    public function show($id, Request $request)
    {
        $companyId = $this->getCompanyId($request);


        $query = PurchaseReturn::with([
            'purchase',
            'supplier',
            'items.product.unit',
            'items.godown',
        ]);


        if ($companyId) {

            $query->where(
                'company_id',
                $companyId
            );
        }


        $return = $query->findOrFail($id);


        return view(
            'purchase_returns.show',
            compact('return')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit($id, Request $request)
    {
        $companyId = $this->getCompanyId($request);


        $query = PurchaseReturn::with([
            'supplier',
            'purchase',
            'items.product',
            'items.godown',
        ]);


        if ($companyId) {

            $query->where(
                'company_id',
                $companyId
            );
        }


        $purchaseReturn = $query->findOrFail($id);


        /*
        |--------------------------------------------------------------------------
        | Calculate Balance
        |--------------------------------------------------------------------------
        */

        foreach ($purchaseReturn->items as $item) {

            $purchaseItem = PurchaseItem::where(
                'purchase_id',
                $purchaseReturn->purchase_id
            )

                ->where(
                    'product_id',
                    $item->product_id
                )

                ->where(
                    'godown_id',
                    $item->godown_id
                )

                ->first();


            if ($purchaseItem) {

                $alreadyReturned =
                    PurchaseReturnItem::where(
                        'product_id',
                        $item->product_id
                    )

                    ->where(
                        'godown_id',
                        $item->godown_id
                    )

                    ->whereHas(
                        'purchaseReturn',
                        function ($query) use (
                            $purchaseReturn
                        ) {

                            $query->where(
                                'purchase_id',
                                $purchaseReturn->purchase_id
                            );
                        }
                    )

                    ->where(
                        'id',
                        '!=',
                        $item->id
                    )

                    ->sum('quantity');


                $item->purchaseItem = (object) [

                    'quantity' =>
                    $purchaseItem->quantity,

                    'returned_qty' =>
                    $alreadyReturned,

                    'balance_qty' =>
                    max(
                        0,
                        $purchaseItem->quantity
                            - $alreadyReturned
                    ),

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
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Load Return
            |--------------------------------------------------------------------------
            */

            $purchaseReturn = PurchaseReturn::with(
                'items'
            )->findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Company Security
            |--------------------------------------------------------------------------
            */

            if (
                !auth()->user()->is_super_user &&
                $purchaseReturn->company_id !=
                auth()->user()->company_id
            ) {

                abort(403);
            }


            /*
            |--------------------------------------------------------------------------
            | Restore Previous Stock
            |--------------------------------------------------------------------------
            */

            foreach (
                $purchaseReturn->items
                as $oldItem
            ) {

                $product = Product::find(
                    $oldItem->product_id
                );


                if ($product) {

                    // Reverse old return
                    $product->current_stock +=
                        $oldItem->quantity;

                    $product->save();
                }
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
            | Update Header
            |--------------------------------------------------------------------------
            */

            $purchaseReturn->update([

                'return_date' =>
                $request->return_date,

                'remarks' =>
                $request->remarks,

                'sub_total' =>
                $request->sub_total ?? 0,

                'gst_total' =>
                $request->gst_total ?? 0,

                'total_amount' =>
                $request->grand_total ?? 0,

            ]);


            /*
            |--------------------------------------------------------------------------
            | Create New Items
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->product_id
                as $key => $productId
            ) {

                $qty = (float) (
                    $request->qty[$key] ?? 0
                );


                if ($qty <= 0) {
                    continue;
                }


                $godownId =
                    $request->godown_id[$key]
                    ?? null;


                /*
                |--------------------------------------------------------------------------
                | Purchase Item
                |--------------------------------------------------------------------------
                */

                $purchaseItem =
                    PurchaseItem::where(
                        'purchase_id',
                        $purchaseReturn->purchase_id
                    )

                    ->where(
                        'product_id',
                        $productId
                    )

                    ->where(
                        'godown_id',
                        $godownId
                    )

                    ->first();


                if (!$purchaseItem) {

                    throw new Exception(
                        'Purchase item not found.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Already Returned
                |--------------------------------------------------------------------------
                */

                $alreadyReturned =
                    PurchaseReturnItem::where(
                        'product_id',
                        $productId
                    )

                    ->where(
                        'godown_id',
                        $godownId
                    )

                    ->whereHas(
                        'purchaseReturn',
                        function ($query) use (
                            $purchaseReturn
                        ) {

                            $query->where(
                                'purchase_id',
                                $purchaseReturn->purchase_id
                            );
                        }
                    )

                    ->sum('quantity');


                $balance =
                    $purchaseItem->quantity
                    - $alreadyReturned;


                if ($qty > $balance) {

                    throw new Exception(
                        'Return quantity exceeds balance for product: '
                            . (
                                $purchaseItem
                                ->product
                                ->product_name
                                ?? 'Unknown Product'
                            )
                    );
                }


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
                    $request->price[$key] ?? 0,

                    'gst_percent' =>
                    $request->gst[$key] ?? 0,

                    'gst_amount' =>
                    $request->gst_amount[$key] ?? 0,

                    'line_total' =>
                    $request->amount[$key] ?? 0,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Reduce Stock
                |--------------------------------------------------------------------------
                */

                $product = Product::find(
                    $productId
                );


                if ($product) {

                    if (
                        $product->current_stock < $qty
                    ) {

                        throw new Exception(
                            'Insufficient stock for '
                                . $product->product_name
                        );
                    }


                    $product->current_stock -=
                        $qty;

                    $product->save();
                }
            }


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
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        $id,
        Request $request
    ) {

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Load Return
            |--------------------------------------------------------------------------
            */

            $companyId =
                $this->getCompanyId($request);


            $query = PurchaseReturn::with(
                'items'
            );


            if ($companyId) {

                $query->where(
                    'company_id',
                    $companyId
                );
            }


            $purchaseReturn =
                $query->findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Reverse Stock
            |--------------------------------------------------------------------------
            */

            foreach (
                $purchaseReturn->items
                as $item
            ) {

                $product = Product::find(
                    $item->product_id
                );


                if ($product) {

                    /*
                    | Purchase Return reduced stock.
                    | Deleting the return restores stock.
                    */

                    $product->current_stock +=
                        $item->quantity;

                    $product->save();
                }
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
    | Print
    |--------------------------------------------------------------------------
    */

    public function print(
        $id,
        Request $request
    ) {

        $companyId =
            $this->getCompanyId($request);


        $query = PurchaseReturn::with([
            'purchase',
            'supplier',
            'items.product',
            'items.godown',
        ]);


        if ($companyId) {

            $query->where(
                'company_id',
                $companyId
            );
        }


        $purchaseReturn =
            $query->findOrFail($id);


        return view(
            'purchase_returns.print',
            compact('purchaseReturn')
        );
    }
}
