<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StockTransferController extends Controller implements HasMiddleware
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
                'permission:stock-transfer.view',
                only: [
                    'index',
                    'show',
                ]
            ),

            new Middleware(
                'permission:stock-transfer.create',
                only: [
                    'create'
                ]
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
        $query = StockTransfer::with([
            'company',
            'store',
            'creator',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Company Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('company_id')) {

            $query->where(
                'company_id',
                $request->company_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Store Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('store_id')) {

            $query->where(
                'store_id',
                $request->store_id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('from_date')) {

            $query->whereDate(
                'transfer_date',
                '>=',
                $request->from_date
            );
        }


        if ($request->filled('to_date')) {

            $query->whereDate(
                'transfer_date',
                '<=',
                $request->to_date
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Transfers
        |--------------------------------------------------------------------------
        */

        $transfers = $query
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        $companies = Company::where(
            'status',
            1
        )->get();


        $stores = Store::where(
            'status',
            1
        )->get();


        return view(
            'stock-transfers.index',
            compact(
                'transfers',
                'companies',
                'stores'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $transferNo = 'TR' . str_pad(
            (StockTransfer::max('id') ?? 0) + 1,
            6,
            '0',
            STR_PAD_LEFT
        );


        $companies = Company::where(
            'status',
            1
        )->get();


        $stores = Store::where(
            'status',
            1
        )->get();


        $godowns = Godown::where(
            'status',
            1
        )->get();


        $products = Product::where(
            'status',
            1
        )->get();


        return view(
            'stock-transfers.create',
            compact(
                'transferNo',
                'companies',
                'stores',
                'godowns',
                'products'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    |
    | Normal Stock Transfer
    |
    */

    public function store(
        Request $request,
        StockService $stockService
    ) {

        $request->validate([

            'company_id' => [
                'required',
                'exists:companies,id',
            ],

            'store_id' => [
                'required',
                'exists:stores,id',
            ],

            'transfer_no' => [
                'required',
                'string',
                'max:100',
                'unique:stock_transfers,transfer_no',
            ],

            'transfer_date' => [
                'required',
                'date',
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

            'from_godown_id' => [
                'required',
                'array',
            ],

            'from_godown_id.*' => [
                'nullable',
                'exists:godowns,id',
            ],

            'to_godown_id' => [
                'required',
                'array',
            ],

            'to_godown_id.*' => [
                'nullable',
                'exists:godowns,id',
            ],

            'quantity' => [
                'required',
                'array',
                'min:1',
            ],

            'quantity.*' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'unit_cost' => [
                'nullable',
                'array',
            ],

            'unit_cost.*' => [
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

            DB::transaction(function () use (
                $request,
                $stockService
            ) {

                /*
                |--------------------------------------------------------------------------
                | Validate Company
                |--------------------------------------------------------------------------
                */

                $company = Company::where(
                    'id',
                    $request->company_id
                )
                    ->where('status', 1)
                    ->first();


                if (!$company) {

                    throw new \Exception(
                        'Invalid or inactive company selected.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Validate Store
                |--------------------------------------------------------------------------
                */

                $store = Store::where(
                    'id',
                    $request->store_id
                )
                    ->where('status', 1)
                    ->first();


                if (!$store) {

                    throw new \Exception(
                        'Invalid or inactive store selected.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Create Transfer Header
                |--------------------------------------------------------------------------
                */

                $transfer = StockTransfer::create([

                    'company_id' =>
                    $request->company_id,

                    'store_id' =>
                    $request->store_id,

                    'transfer_no' =>
                    $request->transfer_no,

                    'transfer_date' =>
                    $request->transfer_date,

                    'remarks' =>
                    $request->remarks,

                    'status' =>
                    1,

                    'created_by' =>
                    auth()->id(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | Process Items
                |--------------------------------------------------------------------------
                */

                foreach (
                    $request->product_id
                    as $key => $productId
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Product
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::where(
                        'id',
                        $productId
                    )
                        ->where(
                            'company_id',
                            $request->company_id
                        )
                        ->where('status', 1)
                        ->first();


                    if (!$product) {

                        throw new \Exception(
                            'Invalid product selected.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Source
                    |--------------------------------------------------------------------------
                    */

                    $fromGodownId =
                        $request->from_godown_id[$key]
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | Destination
                    |--------------------------------------------------------------------------
                    */

                    $toGodownId =
                        $request->to_godown_id[$key]
                        ?? null;


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    $quantity =
                        (float) $request->quantity[$key];


                    /*
                    |--------------------------------------------------------------------------
                    | Unit Cost
                    |--------------------------------------------------------------------------
                    */

                    $unitCost =
                        (float) (
                            $request->unit_cost[$key]
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Source + Destination Validation
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $fromGodownId === null &&
                        $toGodownId === null
                    ) {

                        throw new \Exception(
                            "Source and destination cannot both be Shop for {$product->product_name}."
                        );
                    }


                    if (
                        $fromGodownId !== null &&
                        $toGodownId !== null &&
                        (int) $fromGodownId ===
                        (int) $toGodownId
                    ) {

                        throw new \Exception(
                            "Source and destination cannot be the same for {$product->product_name}."
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Source Godown
                    |--------------------------------------------------------------------------
                    */

                    if ($fromGodownId !== null) {

                        $fromGodown = Godown::where(
                            'id',
                            $fromGodownId
                        )
                            ->where(
                                'company_id',
                                $request->company_id
                            )
                            ->where(
                                'store_id',
                                $request->store_id
                            )
                            ->where(
                                'status',
                                1
                            )
                            ->first();


                        if (!$fromGodown) {

                            throw new \Exception(
                                "Invalid source godown for {$product->product_name}."
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Destination Godown
                    |--------------------------------------------------------------------------
                    */

                    if ($toGodownId !== null) {

                        $toGodown = Godown::where(
                            'id',
                            $toGodownId
                        )
                            ->where(
                                'company_id',
                                $request->company_id
                            )
                            ->where(
                                'store_id',
                                $request->store_id
                            )
                            ->where(
                                'status',
                                1
                            )
                            ->first();


                        if (!$toGodown) {

                            throw new \Exception(
                                "Invalid destination godown for {$product->product_name}."
                            );
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Create Transfer Item
                    |--------------------------------------------------------------------------
                    */

                    $transfer->items()->create([

                        'product_id' =>
                        $productId,

                        'from_godown_id' =>
                        $fromGodownId,

                        'to_godown_id' =>
                        $toGodownId,

                        'quantity' =>
                        $quantity,

                        'unit_cost' =>
                        $unitCost,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Execute Stock Transfer
                    |--------------------------------------------------------------------------
                    */

                    $stockService->transferStock(

                        $product,

                        $quantity,

                        (int) $request->company_id,

                        (int) $request->store_id,

                        $fromGodownId !== null
                            ? (int) $fromGodownId
                            : null,

                        $toGodownId !== null
                            ? (int) $toGodownId
                            : null,

                        $transfer->id,

                        $transfer->transfer_no,

                        $unitCost,

                        'Stock Transfer'
                    );
                }
            });


            return redirect()
                ->route('stock-transfers.index')
                ->with(
                    'success',
                    'Stock transferred successfully.'
                );
        } catch (\Throwable $e) {

            return redirect()
                ->back()
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
        $transfer = StockTransfer::with([

            'company',

            'store',

            'creator',

            'items.product',

            'items.fromGodown',

            'items.toGodown',

        ])->findOrFail($id);


        return view(
            'stock-transfers.show',
            compact('transfer')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | QUICK TRANSFER
    |--------------------------------------------------------------------------
    |
    | Sales Popup:
    |
    | Godown → Shop
    |
    */

    public function quickTransfer(
        Request $request,
        StockService $stockService
    ) {

        $validator = validator($request->all(), [
            'company_id' => [
                'required',
                'exists:companies,id',
            ],
            'to_store_id' => [
                'required',
                'exists:stores,id',
            ],
            'product_id' => [
                'required',
                'exists:products,id',
            ],
            'from_godown_id' => [
                'required',
                'exists:godowns,id',
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'received' => $request->all(),
            ], 422);
        }

        try {

            $result = DB::transaction(
                function () use (
                    $request,
                    $stockService
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Validate Company
                    |--------------------------------------------------------------------------
                    */

                    $company = Company::where(
                        'id',
                        $request->company_id
                    )
                        ->where(
                            'status',
                            1
                        )
                        ->first();


                    if (!$company) {

                        throw new \Exception(
                            'Invalid or inactive company.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Store
                    |--------------------------------------------------------------------------
                    */

                    $store = Store::where(
                        'id',
                        $request->to_store_id
                    )
                        ->where(
                            'status',
                            1
                        )
                        ->first();


                    if (!$store) {

                        throw new \Exception(
                            'Invalid or inactive store.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Product
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::where(
                        'id',
                        $request->product_id
                    )
                        ->where(
                            'company_id',
                            $request->company_id
                        )
                        ->where(
                            'status',
                            1
                        )
                        ->first();


                    if (!$product) {

                        throw new \Exception(
                            'Invalid product selected.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Source Godown
                    |--------------------------------------------------------------------------
                    */

                    $fromGodown = Godown::where(
                        'id',
                        $request->from_godown_id
                    )
                        ->where(
                            'company_id',
                            $request->company_id
                        )
                        ->where(
                            'store_id',
                            $request->to_store_id
                        )
                        ->where(
                            'status',
                            1
                        )
                        ->first();


                    if (!$fromGodown) {

                        throw new \Exception(
                            'Invalid source godown selected.'
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    $quantity =
                        (float) $request->quantity;


                    /*
                    |--------------------------------------------------------------------------
                    | Generate Transfer Number
                    |--------------------------------------------------------------------------
                    */

                    $transferNo = 'TR' . str_pad(

                        (StockTransfer::max('id') ?? 0) + 1,

                        6,

                        '0',

                        STR_PAD_LEFT
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Transfer Header
                    |--------------------------------------------------------------------------
                    */

                    $transfer = StockTransfer::create([

                        'company_id' =>
                        $request->company_id,

                        'store_id' =>
                        $request->to_store_id,

                        'transfer_no' =>
                        $transferNo,

                        'transfer_date' =>
                        now()->toDateString(),

                        'remarks' =>
                        'Quick transfer from Sales',

                        'status' =>
                        1,

                        'created_by' =>
                        auth()->id(),

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Create Transfer Item
                    |--------------------------------------------------------------------------
                    */

                    $transfer->items()->create([

                        'product_id' =>
                        $product->id,

                        'from_godown_id' =>
                        $fromGodown->id,

                        'to_godown_id' =>
                        null,

                        'quantity' =>
                        $quantity,

                        'unit_cost' =>
                        0,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Godown → Shop
                    |--------------------------------------------------------------------------
                    */

                    $stockService->transferStock(

                        $product,

                        $quantity,

                        (int) $request->company_id,

                        (int) $request->to_store_id,

                        (int) $fromGodown->id,

                        null,

                        $transfer->id,

                        $transfer->transfer_no,

                        0,

                        'Quick transfer from Sales'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Get New Shop Stock
                    |--------------------------------------------------------------------------
                    */

                    $shopStock =
                        $stockService->getStock(

                            (int) $request->company_id,

                            (int) $request->store_id,

                            null,

                            $product->id

                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Get Remaining Godown Stock
                    |--------------------------------------------------------------------------
                    */

                    $godownStock =
                        $stockService->getStock(

                            (int) $request->company_id,

                            (int) $request->to_store_id,

                            (int) $fromGodown->id,

                            $product->id

                        );


                    return [

                        'transfer_no' =>
                        $transfer->transfer_no,

                        'shop_stock' =>
                        $shopStock,

                        'godown_stock' =>
                        $godownStock,

                    ];
                }
            );


            /*
            |--------------------------------------------------------------------------
            | JSON Response
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                'message' =>
                'Stock transferred to Shop successfully.',

                'transfer_no' =>
                $result['transfer_no'],

                'shop_stock' =>
                $result['shop_stock'],

                'godown_stock' =>
                $result['godown_stock'],

            ]);
        } catch (\Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                $e->getMessage(),

            ], 422);
        }
    }
}
