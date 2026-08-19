<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\SaleItems;
use App\Models\Product;
use App\Models\Company;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Godown;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SalesController extends Controller implements HasMiddleware
{
    protected StockService $stockService;

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:sales.view',
                only: ['index']
            ),

            new Middleware(
                'permission:sales.create',
                only: ['create', 'store',  'checkStock']
            ),

            new Middleware(
                'permission:sales.edit',
                only: ['edit', 'update']
            ),

            new Middleware(
                'permission:sales.delete',
                only: ['destroy']
            ),

            new Middleware(
                'permission:sales.show',
                only: ['show']
            ),

            new Middleware(
                'permission:sales.print',
                only: ['print']
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $sales = Sales::with([
            'company',
            'customer',
            'store',
        ])
            ->latest()
            ->paginate(15);

        return view(
            'sales.index',
            compact('sales')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $companies = Company::where('status', 1)->get();

        $stores = [];

        $customers = [];

        $godowns = [];

        $invoiceNo = 'select company first';

        return view('sales.create', compact(
            'companies',
            'stores',
            'customers',
            'godowns',
            'invoiceNo'
        ));
    }
    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'invoice_no' => [
                'required',
                'unique:sales,invoice_no',
            ],

            'invoice_date' => [
                'required',
                'date',
            ],

            'company_id' => [
                'required',
                'exists:companies,id',
            ],

            'store_id' => [
                'required',
                'exists:stores,id',
            ],

            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'product_id' => [
                'required',
                'array',
                'min:1',
            ],

            'product_id.*' => [
                'required',
                'exists:products,id',
            ],

            'qty' => [
                'required',
                'array',
                'min:1',
            ],

            'qty.*' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'free_quantity' => [
                'nullable',
                'array',
            ],

            'free_quantity.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'price' => [
                'required',
                'array',
            ],

            'price.*' => [
                'required',
                'numeric',
                'min:0',
            ],

            'gst' => [
                'required',
                'array',
            ],

            'gst.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'discount' => [
                'nullable',
                'array',
            ],

            'discount.*' => [
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
                'max:1000',
            ],
        ]);


        try {

            DB::transaction(function () use ($request) {

                $subTotal = 0;
                $gstTotal = 0;
                $grandTotal = 0;


                /*
                |--------------------------------------------------------------------------
                | Calculate Totals
                |--------------------------------------------------------------------------
                */

                foreach ($request->product_id as $key => $productId) {

                    $qty =
                        (float) $request->qty[$key];

                    $price =
                        (float) $request->price[$key];

                    $gst =
                        (float) (
                            $request->gst[$key] ?? 0
                        );

                    $discount =
                        (float) (
                            $request->discount[$key] ?? 0
                        );


                    $lineSubTotal =
                        ($qty * $price) - $discount;


                    if ($lineSubTotal < 0) {

                        throw new \Exception(
                            'Discount cannot be greater than line amount.'
                        );
                    }


                    $gstAmount =
                        $lineSubTotal * $gst / 100;


                    $lineTotal =
                        $lineSubTotal + $gstAmount;


                    $subTotal += $lineSubTotal;

                    $gstTotal += $gstAmount;

                    $grandTotal += $lineTotal;
                }


                /*
                |--------------------------------------------------------------------------
                | Payment
                |--------------------------------------------------------------------------
                */

                $paidAmount =
                    (float) (
                        $request->paid_amount ?? 0
                    );


                if ($paidAmount > $grandTotal) {

                    throw new \Exception(
                        'Paid amount cannot be greater than Grand Total.'
                    );
                }


                $balanceAmount =
                    $grandTotal - $paidAmount;


                /*
                |--------------------------------------------------------------------------
                | Create Sale
                |--------------------------------------------------------------------------
                */

                $sale = Sales::create([

                    'company_id' =>
                    $request->company_id,

                    'store_id' =>
                    $request->store_id,

                    'customer_id' =>
                    $request->customer_id,

                    'invoice_no' =>
                    $request->invoice_no,

                    'invoice_date' =>
                    $request->invoice_date,

                    'sub_total' =>
                    $subTotal,

                    'discount' =>
                    0,

                    'gst_total' =>
                    $gstTotal,

                    'grand_total' =>
                    $grandTotal,

                    'paid_amount' =>
                    $paidAmount,

                    'balance_amount' =>
                    $balanceAmount,

                    'remarks' =>
                    $request->remarks,

                    'status' =>
                    1,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Create Items + Deduct SHOP Stock
                |--------------------------------------------------------------------------
                */

                foreach ($request->product_id as $key => $productId) {

                    $product =
                        Product::findOrFail($productId);


                    $qty =
                        (float) $request->qty[$key];


                    $freeQuantity =
                        (float) (
                            $request->free_quantity[$key]
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Total Stock Out
                    |--------------------------------------------------------------------------
                    */

                    $stockOut =
                        $qty + $freeQuantity;


                    $price =
                        (float) $request->price[$key];


                    $gst =
                        (float) (
                            $request->gst[$key]
                            ?? 0
                        );


                    $discount =
                        (float) (
                            $request->discount[$key]
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Sale is ALWAYS from SHOP
                    |--------------------------------------------------------------------------
                    |
                    | godown_id = NULL means Shop.
                    |
                    */

                    $godownId = null;


                    /*
                    |--------------------------------------------------------------------------
                    | Calculate Line
                    |--------------------------------------------------------------------------
                    */

                    $lineSubTotal =
                        ($qty * $price) - $discount;


                    if ($lineSubTotal < 0) {

                        throw new \Exception(
                            "Discount cannot be greater than sale amount for {$product->product_name}."
                        );
                    }


                    $gstAmount =
                        $lineSubTotal * $gst / 100;


                    $lineTotal =
                        $lineSubTotal + $gstAmount;


                    /*
                    |--------------------------------------------------------------------------
                    | Create Sale Item
                    |--------------------------------------------------------------------------
                    */

                    SaleItems::create([

                        'sale_id' =>
                        $sale->id,

                        'product_id' =>
                        $productId,

                        /*
                        |--------------------------------------------------------------------------
                        | NULL = SHOP
                        |--------------------------------------------------------------------------
                        */

                        'godown_id' =>
                        null,

                        'quantity' =>
                        $qty,

                        'free_quantity' =>
                        $freeQuantity,

                        'sale_price' =>
                        $price,

                        'discount' =>
                        $discount,

                        'gst_percent' =>
                        $gst,

                        'gst_amount' =>
                        $gstAmount,

                        'line_total' =>
                        $lineTotal,
                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Decrease SHOP Stock
                    |--------------------------------------------------------------------------
                    */

                    $this->stockService->decreaseStock(

                        $product,

                        $stockOut,

                        'Sale',

                        $sale->company_id,

                        $sale->store_id,

                        /*
                        |--------------------------------------------------------------------------
                        | NULL = SHOP
                        |--------------------------------------------------------------------------
                        */

                        null,

                        $sale->id,

                        $sale->invoice_no,

                        $price,

                        'Sales Invoice'
                    );
                }
            });
        } catch (\Throwable $e) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }


        return redirect()
            ->route('sales.index')
            ->with(
                'success',
                'Sales Invoice Saved Successfully'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $sale = Sales::with([

            'customer',

            'company',

            'store',

            'items.product',

            'items.godown',

        ])->findOrFail($id);


        return view(
            'sales.show',
            compact('sale')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $sale = Sales::with([
            'company',
            'store',
            'customer',
            'items.product',
            'items.godown',
        ])->findOrFail($id);

        $customers = Customer::where('status', 1)->get();
        $companies = Company::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();
        $godowns = Godown::where('status', 1)->get();

        return view('sales.form', compact(
            'sale',
            'customers',
            'companies',
            'stores',
            'godowns'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    ) {

        $request->validate([

            'company_id' =>
            'required|exists:companies,id',

            'store_id' =>
            'required|exists:stores,id',

            'customer_id' =>
            'required|exists:customers,id',

            'invoice_date' =>
            'required|date',

            'product_id' =>
            'required|array|min:1',

            'product_id.*' =>
            'required|exists:products,id',

            'godown_id' =>
            'required|array',

            'godown_id.*' =>
            'nullable|exists:godowns,id',

            'qty' =>
            'required|array',

            'qty.*' =>
            'required|numeric|min:0.01',

            'free_quantity' =>
            'nullable|array',

            'free_quantity.*' =>
            'nullable|numeric|min:0',

            'price' =>
            'required|array',

            'price.*' =>
            'required|numeric|min:0',

            'gst' =>
            'required|array',

            'gst.*' =>
            'nullable|numeric|min:0',

            'discount' =>
            'nullable|array',

            'discount.*' =>
            'nullable|numeric|min:0',

            'paid_amount' =>
            'nullable|numeric|min:0',

            'remarks' =>
            'nullable|string|max:1000',
        ]);


        DB::transaction(function () use (
            $request,
            $id
        ) {

            $sale = Sales::with(
                'items'
            )->findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Restore Old Stock
            |--------------------------------------------------------------------------
            */

            foreach ($sale->items as $oldItem) {

                $product =
                    Product::find(
                        $oldItem->product_id
                    );


                if (!$product) {
                    continue;
                }


                $oldStockOut =
                    (float) $oldItem->quantity
                    +
                    (float) $oldItem->free_quantity;


                $this->stockService->increaseStock(

                    $product,

                    $oldStockOut,

                    'Sale Edit Restore',

                    $sale->company_id,

                    $sale->store_id,

                    $oldItem->godown_id,

                    $sale->id,

                    $sale->invoice_no,

                    (float) $oldItem->sale_price,

                    'Sales Invoice Edit Restore'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Old Items
            |--------------------------------------------------------------------------
            */

            SaleItems::where(
                'sale_id',
                $sale->id
            )->delete();


            /*
            |--------------------------------------------------------------------------
            | Calculate New Totals
            |--------------------------------------------------------------------------
            */

            $subTotal = 0;

            $gstTotal = 0;

            $grandTotal = 0;


            foreach (
                $request->product_id
                as $key => $productId
            ) {

                $qty =
                    (float) $request->qty[$key];

                $price =
                    (float) $request->price[$key];

                $gst =
                    (float) (
                        $request->gst[$key]
                        ?? 0
                    );

                $discount =
                    (float) (
                        $request->discount[$key]
                        ?? 0
                    );


                $lineSubTotal =
                    ($qty * $price) - $discount;


                if ($lineSubTotal < 0) {

                    throw new \Exception(
                        'Discount cannot be greater than line amount.'
                    );
                }


                $gstAmount =
                    $lineSubTotal * $gst / 100;


                $lineTotal =
                    $lineSubTotal + $gstAmount;


                $subTotal += $lineSubTotal;

                $gstTotal += $gstAmount;

                $grandTotal += $lineTotal;
            }


            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $paidAmount =
                (float) (
                    $request->paid_amount
                    ?? 0
                );


            if ($paidAmount > $grandTotal) {

                throw new \Exception(
                    'Paid amount cannot be greater than Grand Total.'
                );
            }


            $balanceAmount =
                $grandTotal - $paidAmount;


            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */

            $sale->update([

                'company_id' =>
                $request->company_id,

                'store_id' =>
                $request->store_id,

                'customer_id' =>
                $request->customer_id,

                'invoice_date' =>
                $request->invoice_date,

                'sub_total' =>
                $subTotal,

                'discount' =>
                0,

                'gst_total' =>
                $gstTotal,

                'grand_total' =>
                $grandTotal,

                'paid_amount' =>
                $paidAmount,

                'balance_amount' =>
                $balanceAmount,

                'remarks' =>
                $request->remarks,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create New Items + Deduct Stock
            |--------------------------------------------------------------------------
            */

            foreach (
                $request->product_id
                as $key => $productId
            ) {

                $product =
                    Product::findOrFail(
                        $productId
                    );


                $qty =
                    (float) $request->qty[$key];


                $freeQuantity =
                    (float) (
                        $request->free_quantity[$key]
                        ?? 0
                    );


                $stockOut =
                    $qty + $freeQuantity;


                $price =
                    (float) $request->price[$key];


                $gst =
                    (float) (
                        $request->gst[$key]
                        ?? 0
                    );


                $discount =
                    (float) (
                        $request->discount[$key]
                        ?? 0
                    );


                $godownId =
                    $request->godown_id[$key]
                    ?? null;


                if (!$godownId) {

                    throw new \Exception(
                        "Please select a godown for {$product->product_name}."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Calculate Line
                |--------------------------------------------------------------------------
                */

                $lineSubTotal =
                    ($qty * $price) - $discount;


                if ($lineSubTotal < 0) {

                    throw new \Exception(
                        "Discount cannot be greater than sale amount for {$product->product_name}."
                    );
                }


                $gstAmount =
                    $lineSubTotal * $gst / 100;


                $lineTotal =
                    $lineSubTotal + $gstAmount;


                /*
                |--------------------------------------------------------------------------
                | Create Item
                |--------------------------------------------------------------------------
                */

                SaleItems::create([

                    'sale_id' =>
                    $sale->id,

                    'product_id' =>
                    $productId,

                    'godown_id' =>
                    $godownId,

                    'quantity' =>
                    $qty,

                    'free_quantity' =>
                    $freeQuantity,

                    'sale_price' =>
                    $price,

                    'discount' =>
                    $discount,

                    'gst_percent' =>
                    $gst,

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

                $this->stockService->decreaseStock(

                    $product,

                    $stockOut,

                    'Sale',

                    $sale->company_id,

                    $sale->store_id,

                    $godownId,

                    $sale->id,

                    $sale->invoice_no,

                    $price,

                    'Sales Invoice Edit'
                );
            }
        });


        return redirect()
            ->route('sales.index')
            ->with(
                'success',
                'Sales Invoice Updated Successfully'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    public function print($id)
    {
        $sale = Sales::with([

            'company',

            'store',

            'customer',

            'items.product',

            'items.godown',

        ])->findOrFail($id);


        return view(
            'sales.print',
            compact('sale')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $sale = Sales::with(
                'items'
            )->findOrFail($id);


            /*
            |--------------------------------------------------------------------------
            | Restore Stock
            |--------------------------------------------------------------------------
            */

            foreach ($sale->items as $item) {

                $product =
                    Product::find(
                        $item->product_id
                    );


                if (!$product) {
                    continue;
                }


                $stockOut =
                    (float) $item->quantity
                    +
                    (float) $item->free_quantity;


                $this->stockService->increaseStock(

                    $product,

                    $stockOut,

                    'Sale Delete Restore',

                    $sale->company_id,

                    $sale->store_id,

                    $item->godown_id,

                    $sale->id,

                    $sale->invoice_no,

                    (float) $item->sale_price,

                    'Sales Invoice Delete Restore'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Delete Items
            |--------------------------------------------------------------------------
            */

            SaleItems::where(
                'sale_id',
                $sale->id
            )->delete();


            /*
            |--------------------------------------------------------------------------
            | Delete Sale
            |--------------------------------------------------------------------------
            */

            $sale->delete();
        });


        return redirect()
            ->route('sales.index')
            ->with(
                'success',
                'Sales Invoice Deleted Successfully'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | customer details based on company
    |--------------------------------------------------------------------------
    */
    public function companyData($companyId)
    {
        abort_unless(
            auth()->user()->can('sales.create') ||
                auth()->user()->can('sales.edit'),
            403
        );

    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

        $company = Company::where('id', $companyId)
            ->where('status', 1)
            ->firstOrFail();


    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

        if (
            !auth()->user()->is_super_user &&
            auth()->user()->company_id != $company->id
        ) {
            abort(403);
        }


    /*
    |--------------------------------------------------------------------------
    | Stores
    |--------------------------------------------------------------------------
    */

        $stores = Store::where('company_id', $company->id)
            ->where('status', 1)
            ->orderBy('store_name')
            ->get([
                'id',
                'store_name'
            ]);


    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */

        $customers = Customer::where('company_id', $company->id)
            ->where('status', 1)
            ->orderBy('customer_name')
            ->get([
                'id',
                'customer_name'
            ]);

        //get invoice number
        $invoiceNum = $this->nextInvoiceNo($company->id);

        return response()->json([

            'stores' => $stores,

            'customers' => $customers,

            'invoiceNo' => $invoiceNum

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate the sales invoice number
    |--------------------------------------------------------------------------
    */

    public function nextInvoiceNo($companyId)
    {
        abort_unless(
            auth()->user()->can('sales.create'),
            403
        );

        // Normal user can only access their company
        if (
            !auth()->user()->is_super_user &&
            auth()->user()->company_id != $companyId
        ) {
            abort(403);
        }

        $last = Sales::where('company_id', $companyId)
            ->latest('id')
            ->first();

        $next = $last
            ? ((int) preg_replace('/[^0-9]/', '', $last->invoice_no) + 1)
            : 1;

        $invoiceNo = 'SAL' . str_pad(
            $next,
            6,
            '0',
            STR_PAD_LEFT
        );

        return $invoiceNo;
    
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK STOCK
    |--------------------------------------------------------------------------
    |
    | Check stock available in SHOP for Sales.
    |
    | godown_id = NULL
    | means SHOP.
    |
    */

    public function checkStock(
        Request $request,
        StockService $stockService
    ) {
        /*
        |--------------------------------------------------------------------------
        | VALIDATE REQUEST
        |--------------------------------------------------------------------------
        */

        try {

            $validated = $request->validate([

                'company_id' => [
                    'required',
                    'exists:companies,id',
                ],

                'store_id' => [
                    'required',
                    'exists:stores,id',
                ],

                'product_id' => [
                    'required',
                    'exists:products,id',
                ],

                'quantity' => [
                    'required',
                    'numeric',
                    'min:0.01',
                ],

                'sale_id' => [
                    'nullable',
                    'exists:sales,id',
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid stock check request.',
                'errors' => $e->errors(),
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCT
        |--------------------------------------------------------------------------
        */

        $product = Product::findOrFail(
            $validated['product_id']
        );


        /*
        |--------------------------------------------------------------------------
        | REQUIRED QUANTITY
        |--------------------------------------------------------------------------
        */

        $requiredQty =
            (float) $validated['quantity'];


        /*
        |--------------------------------------------------------------------------
        | SHOP STOCK
        |--------------------------------------------------------------------------
        |
        | godown_id = NULL means Shop.
        |
        */

        $shopStock =
            $stockService->getStock(
                (int) $validated['company_id'],
                (int) $validated['store_id'],
                null,
                (int) $validated['product_id']
            );


        /*
        |--------------------------------------------------------------------------
        | EDIT SALE
        |--------------------------------------------------------------------------
        |
        | Add old sale quantity back temporarily.
        |
        */

        if (!empty($validated['sale_id'])) {

            $sale = Sales::with('items')
                ->where(
                    'id',
                    $validated['sale_id']
                )
                ->where(
                    'company_id',
                    $validated['company_id']
                )
                ->where(
                    'store_id',
                    $validated['store_id']
                )
                ->first();

            if ($sale) {

                $oldQuantity = $sale->items
                    ->where(
                        'product_id',
                        $validated['product_id']
                    )
                    ->sum(function ($item) {

                        return
                            (float) $item->quantity
                            +
                            (float) (
                                $item->free_quantity ?? 0
                            );
                    });

                $shopStock += $oldQuantity;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK SHOP STOCK
        |--------------------------------------------------------------------------
        */

        $available =
            $shopStock >= $requiredQty;


        $shortage =
            max(
                0,
                $requiredQty - $shopStock
            );


        /*
        |--------------------------------------------------------------------------
        | SHOP HAS ENOUGH STOCK
        |--------------------------------------------------------------------------
        */

        if ($available) {

            return response()->json([

                'success' => true,

                'available' => true,

                'product_id' =>
                $product->id,

                'product_name' =>
                $product->product_name,

                'required' =>
                $requiredQty,

                'required_qty' =>
                $requiredQty,

                'shop_stock' =>
                $shopStock,

                'shortage' =>
                0,

                'can_fulfill' =>
                true,

                'godowns' =>
                [],

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | GET GODOWNS
        |--------------------------------------------------------------------------
        */

        $godowns = Godown::where(
            'company_id',
            $validated['company_id']
        )
            ->where(
                'store_id',
                $validated['store_id']
            )
            ->where(
                'status',
                1
            )
            ->orderBy(
                'godown_name'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GET GODOWN STOCK
        |--------------------------------------------------------------------------
        */

        $remaining =
            $shortage;


        $godownStocks = $godowns
            ->map(
                function ($godown) use (
                    $stockService,
                    $validated,
                    &$remaining
                ) {

                    $stock =
                        (float) $stockService->getStock(
                            (int) $validated['company_id'],
                            (int) $validated['store_id'],
                            (int) $godown->id,
                            (int) $validated['product_id']
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | How much should be transferred from this godown?
                    |--------------------------------------------------------------------------
                    */

                    $transferQuantity =
                        min(
                            $stock,
                            $remaining
                        );


                    $remaining -=
                        $transferQuantity;


                    return [

                        'id' =>
                        $godown->id,

                        'name' =>
                        $godown->godown_name,

                        /*
                        | Current Godown stock
                        */
                        'stock' =>
                        $stock,

                        /*
                        | JS uses available
                        */
                        'available' =>
                        $stock,

                        /*
                        | Suggested transfer quantity
                        */
                        'transfer_quantity' =>
                        $transferQuantity,
                    ];
                }
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | CAN FULLY FULFILL?
        |--------------------------------------------------------------------------
        */

        $canFulfill =
            $remaining <= 0.000001;


        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' =>
            true,

            'available' =>
            false,

            'product_id' =>
            $product->id,

            'product_name' =>
            $product->product_name,

            'required' =>
            $requiredQty,

            'required_qty' =>
            $requiredQty,

            'shop_stock' =>
            $shopStock,

            'shortage' =>
            $shortage,

            'can_fulfill' =>
            $canFulfill,

            'godowns' =>
            $godownStocks,

        ]);
    }

    public function quickTransfer(
        Request $request,
        StockService $stockService
    ) {
        $validated = $request->validate([

            'company_id' => [
                'required',
                'exists:companies,id',
            ],

            'from_godown_id' => [
                'required',
                'exists:godowns,id',
            ],

            'to_store_id' => [
                'required',
                'exists:stores,id',
            ],

            'product_id' => [
                'required',
                'exists:products,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'min:0.01',
            ],

        ]);


        /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

        $product = Product::findOrFail(
            $validated['product_id']
        );


        /*
    |--------------------------------------------------------------------------
    | Validate Godown
    |--------------------------------------------------------------------------
    |
    | Make sure the selected godown actually belongs to
    | the selected company and store.
    |
    */

        $godown = Godown::where(
            'id',
            $validated['from_godown_id']
        )
            ->where(
                'company_id',
                $validated['company_id']
            )
            ->where(
                'store_id',
                $validated['to_store_id']
            )
            ->where(
                'status',
                1
            )
            ->first();


        if (!$godown) {

            return response()->json([

                'message' =>
                'Invalid godown selected.',

            ], 422);
        }


        /*
    |--------------------------------------------------------------------------
    | Transfer Godown → Shop
    |--------------------------------------------------------------------------
    */

        $stockService->transferStock(

            $product,

            (float) $validated['quantity'],

            (int) $validated['company_id'],

            (int) $validated['to_store_id'],

            (int) $validated['from_godown_id'],

            null,

            null,

            null,

            0,

            'Quick transfer from godown to shop'

        );


        /*
    |--------------------------------------------------------------------------
    | Get Updated Shop Stock
    |--------------------------------------------------------------------------
    */

        $shopStock =
            $stockService->getStock(

                (int) $validated['company_id'],

                (int) $validated['to_store_id'],

                null,

                (int) $validated['product_id']

            );


        /*
    |--------------------------------------------------------------------------
    | Get Updated Godown Stock
    |--------------------------------------------------------------------------
    */

        $godownStock =
            $stockService->getStock(

                (int) $validated['company_id'],

                (int) $validated['to_store_id'],

                (int) $validated['from_godown_id'],

                (int) $validated['product_id']

            );


        /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

        return response()->json([

            'success' =>
            true,

            'message' =>
            'Stock transferred to shop successfully.',

            'product_id' =>
            $product->id,

            'product_name' =>
            $product->product_name,

            'transferred_qty' =>
            (float) $validated['quantity'],

            'shop_stock' =>
            $shopStock,

            'godown_stock' =>
            $godownStock,

        ]);
    }

}
