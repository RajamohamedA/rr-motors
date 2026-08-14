<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Company;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\StockService;
use App\Models\StockLedger;
use App\Models\PurchaseReturn;
use App\Models\Godown;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;


class PurchaseController extends Controller implements HasMiddleware
{

    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase.view', only: ['index', 'show', 'search', 'print']),
            new Middleware('permission:purchase.create', only: ['create', 'store']),
            new Middleware('permission:purchase.edit', only: ['edit', 'update']),
            new Middleware('permission:purchase.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $purchases = Purchase::with([
            'company',
            'store',
            'supplier'
        ])
            ->latest()
            ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $purchaseNo = 'PUR' . str_pad((Purchase::max('id') ?? 0) + 1, 6, '0', STR_PAD_LEFT);

        $companies = Company::where('status', 1)->get();
        $stores = [];
        $suppliers = [];

        return view('purchases.create', compact(
            'purchaseNo',
            'companies',
            'stores',
            'suppliers'
        ));
    }

    public function search(Request $request)
    {
        $search = $request->search;

        $products = Product::where('status', 1)
            ->where(function ($q) use ($search) {

                $q->where('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
            })
            ->select(
                'id',
                'product_code',
                'product_name',
                'purchase_price',
                'gst_percent',
                'current_stock'
            )
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
    /*
    |--------------------------------------------------------------------------
    | Validate Basic Data
    |--------------------------------------------------------------------------
    */

        $request->validate([

            'company_id'  => 'required|exists:companies,id',
            'store_id'    => 'required|exists:stores,id',
            'supplier_id' => 'required|exists:suppliers,id',

            'purchase_no'   => 'required|string|max:255',
            'purchase_date' => 'required|date',

            'invoice_no'   => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',

            'grand_total' => 'required|numeric|min:0',

            'product_id'   => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',

            'qty'   => 'required|array|min:1',
            'qty.*' => 'required|numeric|min:0.01',

            'price'   => 'required|array|min:1',
            'price.*' => 'required|numeric|min:0',

            'gst'   => 'required|array',
            'gst.*' => 'nullable|numeric|min:0',

        /*
        |--------------------------------------------------------------------------
        | Godown Per Product
        |--------------------------------------------------------------------------
        */

            'godown_id'   => 'nullable|array',
            'godown_id.*' => 'nullable|exists:godowns,id',

            'remarks' => 'nullable|string|max:1000',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Validate Store Belongs To Company
    |--------------------------------------------------------------------------
    */

        $store = Store::where('id', $request->store_id)
            ->where('company_id', $request->company_id)
            ->where('status', 1)
            ->first();

        if (!$store) {

            return back()
                ->withInput()
                ->withErrors([
                    'store_id' => 'Selected store does not belong to the selected company.',
                ]);
        }


    /*
    |--------------------------------------------------------------------------
    | Validate Supplier Belongs To Company
    |--------------------------------------------------------------------------
    */

        $supplier = Supplier::where('id', $request->supplier_id)
            ->where('company_id', $request->company_id)
            ->where('status', 1)
            ->first();

        if (!$supplier) {

            return back()
                ->withInput()
                ->withErrors([
                    'supplier_id' => 'Selected supplier does not belong to the selected company.',
                ]);
        }


    /*
    |--------------------------------------------------------------------------
    | Start Database Transaction
    |--------------------------------------------------------------------------
    */

        DB::beginTransaction();

        try {

        /*
        |--------------------------------------------------------------------------
        | Create Purchase Header
        |--------------------------------------------------------------------------
        */

            $purchase = Purchase::create([

                'company_id'     => $request->company_id,

                'store_id'       => $request->store_id,

                'supplier_id'    => $request->supplier_id,

                'purchase_no'    => $request->purchase_no,

                'purchase_date'  => $request->purchase_date,

                'invoice_no'     => $request->invoice_no,

                'invoice_date'   => $request->invoice_date,

                'subtotal'       => $request->grand_total,

                'discount'       => 0,

                'tax_amount'     => 0,

                'other_charges'  => 0,

                'grand_total'    => $request->grand_total,

                'paid_amount'    => 0,

                'balance_amount' => $request->grand_total,

                'remarks'        => $request->remarks,

                'status'         => 1,
            ]);


        /*
        |--------------------------------------------------------------------------
        | Create Purchase Items + Stock Ledger
        |--------------------------------------------------------------------------
        */

            foreach ($request->product_id as $key => $productId) {

                $product = Product::findOrFail($productId);

                $qty = (float) $request->qty[$key];

                $price = (float) $request->price[$key];

                $gst = (float) ($request->gst[$key] ?? 0);


            /*
            |--------------------------------------------------------------------------
            | Get Godown For This Product
            |--------------------------------------------------------------------------
            */

                $godownId = null;

                if (
                    isset($request->godown_id[$key]) &&
                    $request->godown_id[$key] !== ''
                ) {

                    $godownId = (int) $request->godown_id[$key];


                /*
                |--------------------------------------------------------------------------
                | Validate Godown Belongs To Company + Store
                |--------------------------------------------------------------------------
                */

                    $godown = Godown::where('id', $godownId)
                        ->where('company_id', $request->company_id)
                        ->where('store_id', $request->store_id)
                        ->where('status', 1)
                        ->first();

                    if (!$godown) {

                        throw new \Exception(
                            'Godown selected for product "' .
                                $product->product_name .
                                '" does not belong to the selected store.'
                        );
                    }
                }


            /*
            |--------------------------------------------------------------------------
            | GST Amount
            |--------------------------------------------------------------------------
            */

                $subtotal = $qty * $price;

                $gstAmount = $subtotal * $gst / 100;

                $lineTotal = $subtotal + $gstAmount;


            /*
            |--------------------------------------------------------------------------
            | Create Purchase Item
            |--------------------------------------------------------------------------
            */

                PurchaseItem::create([

                    'purchase_id'    => $purchase->id,

                    'product_id'     => $product->id,

                    'godown_id'      => $godownId,

                    'quantity'       => $qty,

                    'free_quantity'  => 0,

                    'purchase_price' => $price,

                    'discount'       => 0,

                    'gst_percent'    => $gst,

                    'gst_amount'     => $gstAmount,

                    'line_total'     => $lineTotal,
                ]);


            /*
            |--------------------------------------------------------------------------
            | Increase Stock
            |--------------------------------------------------------------------------
            */

                $this->stockService->increaseStock(

                    $product,

                    $qty,

                    'Purchase',

                    $purchase->company_id,

                    $purchase->store_id,

                    $godownId,

                    $purchase->id,

                    $purchase->purchase_no,

                    $price,

                    $request->remarks ?? ''
                );
            }


        /*
        |--------------------------------------------------------------------------
        | Commit
        |--------------------------------------------------------------------------
        */

            DB::commit();


            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Purchase saved successfully.'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'store',
            'company',
            'items.product',
            'items.product.unit',
            'items.godown'
        ])->findOrFail($id);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load([
            'items.product',
        ]);

        $company = Company::all();

        $stores = Store::where('company_id', $purchase->company_id)
            ->where('status', 1)
            ->get();

        $suppliers = Supplier::where('company_id', $purchase->company_id)
            ->where('status', 1)
            ->get();

        $godowns = Godown::where('store_id', $purchase->store_id)
            ->where('status', 1)
            ->get();

        return view('purchases.edit', [
            'purchase'  => $purchase,
            'companies'   => $company,
            'stores'    => $stores,
            'suppliers' => $suppliers,
            'godowns'   => $godowns,
        ]);
    }

    public function update(Request $request, Purchase $purchase)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Validate Request
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'company_id' => [
                'required',
                'exists:companies,id',
            ],

            'store_id' => [
                'required',
                'exists:stores,id',
            ],

            'supplier_id' => [
                'required',
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

            'grand_total' => [
                'required',
                'numeric',
                'min:0',
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

            'price' => [
                'required',
                'array',
                'min:1',
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

            /*
            |--------------------------------------------------------------------------
            | Godown is optional for each item
            |--------------------------------------------------------------------------
            */

            'godown_id' => [
                'nullable',
                'array',
            ],

            'godown_id.*' => [
                'nullable',
                'exists:godowns,id',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | 2. Validate Store
        |--------------------------------------------------------------------------
        */

        $store = Store::where('id', $request->store_id)
            ->where('company_id', $request->company_id)
            ->where('status', 1)
            ->first();

        if (!$store) {

            return back()
                ->withInput()
                ->withErrors([
                    'store_id' => 'Selected store does not belong to the selected company.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Validate Supplier
        |--------------------------------------------------------------------------
        */

        $supplier = Supplier::where('id', $request->supplier_id)
            ->where('company_id', $request->company_id)
            ->where('status', 1)
            ->first();

        if (!$supplier) {

            return back()
                ->withInput()
                ->withErrors([
                    'supplier_id' => 'Selected supplier does not belong to the selected company.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Validate Godowns
        |--------------------------------------------------------------------------
        |
        | Each purchase item can have its own godown.
        |
        | Example:
        |
        | Product A → Shop
        | Product B → Godown 1
        | Product C → Godown 2
        |
        */

        foreach ($request->product_id as $key => $productId) {

            $godownId = $request->godown_id[$key] ?? null;

            /*
            |--------------------------------------------------------------------------
            | No godown = direct shop/store stock
            |--------------------------------------------------------------------------
            */

            if (empty($godownId)) {
                continue;
            }


            $godownExists = Godown::where('id', $godownId)
                ->where('company_id', $request->company_id)
                ->where('store_id', $request->store_id)
                ->where('status', 1)
                ->exists();


            if (!$godownExists) {

                return back()
                    ->withInput()
                    ->withErrors([
                        "godown_id.$key" =>
                        'Selected godown does not belong to the selected store.',
                    ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Start Database Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | 6. Load Existing Purchase Items
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            | We need the old godown_id before deleting the items.
            |
            */

            $purchase->load('items');


            /*
            |--------------------------------------------------------------------------
            | 7. Reverse OLD Stock
            |--------------------------------------------------------------------------
            |
            | We DO NOT delete the old stock ledger.
            |
            | Instead we create:
            |
            | Purchase Reverse
            |
            | This keeps the stock history/audit trail.
            |
            */

            foreach ($purchase->items as $oldItem) {

                $product = Product::find($oldItem->product_id);


                if (!$product) {
                    continue;
                }


                $oldGodownId = $oldItem->godown_id;


                /*
                |--------------------------------------------------------------------------
                | Reverse Previous Purchase Stock
                |--------------------------------------------------------------------------
                */

                $this->stockService->decreaseStock(

                    $product,

                    (float) $oldItem->quantity,

                    'Purchase Reverse',

                    $purchase->company_id,

                    $purchase->store_id,

                    $oldGodownId,

                    $purchase->id,

                    $purchase->purchase_no,

                    (float) $oldItem->purchase_price,

                    'Purchase Updated - Reverse Old Stock'

                );
            }


            /*
            |--------------------------------------------------------------------------
            | 8. Delete OLD Purchase Items
            |--------------------------------------------------------------------------
            |
            | We delete purchase item records.
            |
            | We DO NOT delete stock ledger records.
            |
            */

            $purchase->items()->delete();


            /*
            |--------------------------------------------------------------------------
            | 9. Update Purchase Header
            |--------------------------------------------------------------------------
            */

            $purchase->update([

                'company_id' => $request->company_id,

                'store_id' => $request->store_id,

                'supplier_id' => $request->supplier_id,

                'purchase_date' => $request->purchase_date,

                'invoice_no' => $request->invoice_no,

                'invoice_date' => $request->invoice_date,

                /*
                |--------------------------------------------------------------------------
                | Financial Values
                |--------------------------------------------------------------------------
                */

                'subtotal' => $request->grand_total,

                'discount' => 0,

                'tax_amount' => 0,

                'other_charges' => 0,

                'grand_total' => $request->grand_total,

                'paid_amount' => 0,

                'balance_amount' => $request->grand_total,

                'remarks' => $request->remarks,

            ]);


            /*
            |--------------------------------------------------------------------------
            | 10. Create NEW Purchase Items
            |--------------------------------------------------------------------------
            */

            foreach ($request->product_id as $key => $productId) {

                $product = Product::findOrFail($productId);


                /*
                |--------------------------------------------------------------------------
                | Values
                |--------------------------------------------------------------------------
                */

                $qty = (float) $request->qty[$key];

                $price = (float) $request->price[$key];

                $gstPercent = (float) ($request->gst[$key] ?? 0);

                $godownId = $request->godown_id[$key] ?? null;


                /*
                |--------------------------------------------------------------------------
                | Calculate Amounts
                |--------------------------------------------------------------------------
                */

                $discount = 0;

                $subtotal = $qty * $price;

                $gstAmount =
                    ($subtotal - $discount)
                    * $gstPercent
                    / 100;

                $lineTotal =
                    ($subtotal - $discount)
                    + $gstAmount;


                /*
                |--------------------------------------------------------------------------
                | Create Purchase Item
                |--------------------------------------------------------------------------
                */

                PurchaseItem::create([

                    'purchase_id' => $purchase->id,

                    'product_id' => $product->id,

                    'godown_id' => $godownId,

                    'quantity' => $qty,

                    'free_quantity' => 0,

                    'purchase_price' => $price,

                    'discount' => $discount,

                    'gst_percent' => $gstPercent,

                    'gst_amount' => $gstAmount,

                    'line_total' => $lineTotal,

                ]);


                /*
                |--------------------------------------------------------------------------
                | Increase NEW Stock
                |--------------------------------------------------------------------------
                */

                $this->stockService->increaseStock(

                    $product,

                    $qty,

                    'Purchase',

                    $purchase->company_id,

                    $purchase->store_id,

                    $godownId,

                    $purchase->id,

                    $purchase->purchase_no,

                    $price,

                    'Purchase Updated'

                );
            }


            /*
            |--------------------------------------------------------------------------
            | 11. Commit Transaction
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 12. Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Purchase updated successfully.'
                );
        } catch (\Exception $e) {


            /*
            |--------------------------------------------------------------------------
            | Rollback
            |--------------------------------------------------------------------------
            */

            DB::rollBack();


            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | 1. Check Purchase Return
            |--------------------------------------------------------------------------
            */

            if (PurchaseReturn::where('purchase_id', $purchase->id)->exists()) {

                DB::rollBack();

                return redirect()
                    ->route('purchases.index')
                    ->with(
                        'error',
                        'Cannot delete this purchase because a Purchase Return already exists.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 2. Load Purchase Items
            |--------------------------------------------------------------------------
            */

            $purchase->load('items');


            /*
            |--------------------------------------------------------------------------
            | 3. Reverse Stock
            |--------------------------------------------------------------------------
            |
            | Each item may belong to a different godown.
            |
            */

            foreach ($purchase->items as $item) {

                $product = Product::find($item->product_id);

                if (!$product) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | Get Item Godown
                |--------------------------------------------------------------------------
                */

                $godownId = $item->godown_id;


                /*
                |--------------------------------------------------------------------------
                | Reverse Purchased Quantity
                |--------------------------------------------------------------------------
                */

                $this->stockService->decreaseStock(

                    $product,

                    (float) $item->quantity,

                    'Purchase Reverse',

                    $purchase->company_id,

                    $purchase->store_id,

                    $godownId,

                    $purchase->id,

                    $purchase->purchase_no,

                    (float) $item->purchase_price,

                    'Purchase Deleted - Reverse Stock'

                );
            }


            /*
            |--------------------------------------------------------------------------
            | 4. Delete Purchase Items
            |--------------------------------------------------------------------------
            */

            $purchase->items()->delete();


            /*
            |--------------------------------------------------------------------------
            | 5. Delete Purchase
            |--------------------------------------------------------------------------
            */

            $purchase->delete();


            /*
            |--------------------------------------------------------------------------
            | 6. Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();


            /*
            |--------------------------------------------------------------------------
            | 7. Success
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Purchase deleted successfully.'
                );
        } catch (\Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Rollback
            |--------------------------------------------------------------------------
            */

            DB::rollBack();


            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function print(Purchase $purchase)
    {
        $purchase->load([
            'company',
            'store',
            'supplier',
            'items.product'
        ]);

        return view('purchases.print', compact('purchase'));
    }

    public function getStores($companyId)
    {
        $stores = Store::where('company_id', $companyId)
            ->where('status', 1)
            ->orderBy('store_name')
            ->get([
                'id',
                'store_name',
            ]);

        return response()->json($stores);
    }

    public function getGodowns(Store $store)
    {
        $godowns = Godown::where('company_id', $store->company_id)
            ->where('store_id', $store->id)
            ->where('status', 1)
            ->orderBy('godown_name')
            ->get([
                'id',
                'godown_name',
            ]);

        return response()->json($godowns);
    }

    public function getSuppliers(Company $company)
    {
        $suppliers = Supplier::where('company_id', $company->id)
            ->where('status', 1)
            ->orderBy('supplier_name')
            ->get([
                'id',
                'supplier_name',
            ]);

        return response()->json($suppliers);
    }

    
}
