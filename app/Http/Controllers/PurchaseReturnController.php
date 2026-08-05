<?php

namespace App\Http\Controllers;

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
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase-return.view', only: ['index', 'show', 'print']),
            new Middleware('permission:purchase-return.create', only: ['create', 'store','search','getInvoice']),
            new Middleware('permission:purchase-return.edit', only: ['edit', 'update']),
            new Middleware('permission:purchase-return.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $returns = PurchaseReturn::with(['purchase', 'supplier'])
            ->when($request->search, function ($query) use ($request) {

                $search = $request->search;

                $query->where('return_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('supplier_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchase', function ($q) use ($search) {
                        $q->where('purchase_no', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchase_returns.index', compact('returns'));
    }

    public function create()
    {
        return view('purchase_returns.create');
    }

    public function search(Request $request)
    {
        $search = $request->search;

        return Purchase::with('supplier')
            ->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q2) use ($search) {
                      $q2->where('supplier_name', 'like', "%{$search}%");
                  });
            })
            ->limit(10)
            ->get();
    }

    public function getInvoice($id)
    {
        $purchase = Purchase::with([
            'supplier',
            'items.product.unit'
        ])->findOrFail($id);

        foreach ($purchase->items as $item) {

            $returned = PurchaseReturnItem::where('product_id', $item->product_id)
                ->whereHas('purchaseReturn', function ($q) use ($purchase) {
                    $q->where('purchase_id', $purchase->id);
                })
                ->sum('quantity');

            $item->returned_qty = $returned;
            $item->balance_qty = $item->quantity - $returned;
        }

        return response()->json($purchase);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id'    => 'required|exists:purchases,id',
            'supplier_id'    => 'required|exists:suppliers,id',
            'return_date'    => 'required|date',
            'product_id'     => 'required|array',
            'qty'            => 'required|array',
            'price'          => 'required|array',
            'gst'            => 'required|array',
            'gst_amount'     => 'required|array',
            'amount'         => 'required|array',
        ]);
    
        DB::beginTransaction();
    
        try {
    
            $purchase = Purchase::with('items.product')
                ->findOrFail($request->purchase_id);
    
            $companyId = $purchase->company_id;
    
            $returnNo = 'PR-' . str_pad(
                (PurchaseReturn::max('id') ?? 0) + 1,
                6,
                '0',
                STR_PAD_LEFT
            );
    
            $return = PurchaseReturn::create([
                'company_id'   => $companyId,
                'purchase_id'  => $request->purchase_id,
                'supplier_id'  => $request->supplier_id,
                'return_no'    => $returnNo,
                'return_date'  => $request->return_date,
                'sub_total'   => $request->total_sub_total,
                'gst_total'   => $request->total_gst,
                'total_amount' => $request->total_grand_total,
                'remarks'      => $request->remarks,
            ]);
    
            foreach ($request->product_id as $key => $productId) {
    
                $qty = (float) ($request->qty[$key] ?? 0);
    
                if ($qty <= 0) {
                    continue;
                }
    
                $purchaseItem = $purchase->items
                    ->where('product_id', $productId)
                    ->first();
    
                if (!$purchaseItem) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Purchase Item not found.'
                        );
                }
    
                $returnedQty = PurchaseReturnItem::where('product_id', $productId)
                    ->whereHas('purchaseReturn', function ($q) use ($purchase) {
                        $q->where('purchase_id', $purchase->id);
                    })
                    ->sum('quantity');
    
                $balanceQty = $purchaseItem->quantity - $returnedQty;
    
                if ($qty > $balanceQty) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Return quantity exceeds balance for product: '
                                . $purchaseItem->product->product_name
                        );
                }
    
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id'         => $productId,
                    'quantity'           => $qty,
                    'purchase_price'     => $request->price[$key],
                    'gst_percent'        => $request->gst[$key],
                    'gst_amount'         => $request->gst_amount[$key],
                    'line_total'         => $request->amount[$key],
                ]);
    
                // Reduce Stock
                $product = Product::find($productId);
    
                if ($product) {
                    $product->current_stock -= $qty;
    
                    if ($product->current_stock < 0) {
                        throw new Exception(
                            "Insufficient stock for "
                            . $product->product_name
                        );
                    }
    
                    $product->save();
                }
            }
    
            DB::commit();
    
            return redirect()
                ->route('purchase-returns.index')
                ->with('success', 'Purchase Return Saved Successfully.');
    
        } catch (\Exception $e) {
    
            DB::rollBack();
    
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $return = PurchaseReturn::with([
            'purchase',
            'supplier',
            'items.product.unit'
        ])->findOrFail($id);

        return view('purchase_returns.show', compact('return'));
    }

    public function edit($id)
    {
        $purchaseReturn = PurchaseReturn::with([
            'supplier',
            'purchase',
            'items.product',
        ])->findOrFail($id);

        // Calculate purchased / returned / balance for each item
        foreach ($purchaseReturn->items as $item) {

            $purchaseItem = PurchaseItem::where('purchase_id', $purchaseReturn->purchase_id)
                ->where('product_id', $item->product_id)
                ->first();

            if ($purchaseItem) {

                $alreadyReturned = PurchaseReturnItem::where('product_id', $item->product_id)
                    ->whereHas('purchaseReturn', function ($q) use ($purchaseReturn, $item) {
                        $q->where('purchase_id', $purchaseReturn->purchase_id);
                    })
                    ->where('id', '!=', $item->id) // exclude current return item
                    ->sum('quantity');

                $item->purchaseItem = (object) [
                    'quantity'      => $purchaseItem->quantity,
                    'returned_qty'  => $alreadyReturned,
                    'balance_qty'   => $purchaseItem->quantity - $alreadyReturned,
                ];
            }
        }

        return view('purchase_returns.edit', compact('purchaseReturn'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        //dd($request->all());

        try {

            $purchaseReturn = PurchaseReturn::findOrFail($id);

            // Restore stock from old return items
            foreach ($purchaseReturn->items as $oldItem) {

                $product = Product::find($oldItem->product_id);

                if ($product) {
                    $product->current_stock -= $oldItem->quantity;
                    $product->save();
                }
            }

            // Delete old items
            PurchaseReturnItem::where('purchase_return_id', $purchaseReturn->id)->delete();

            // Update header
            $purchaseReturn->update([

                'return_date' => $request->return_date,
                'remarks'      => $request->remarks,
                'sub_total'   => $request->sub_total,
                'gst_total'   => $request->gst_total,
                'total_amount' => $request->grand_total,

            ]);

            foreach ($request->product_id as $key => $productId) {

                $qty = (float)$request->qty[$key];

                if ($qty <= 0) {
                    continue;
                }

                $purchaseItem = PurchaseItem::where('purchase_id', $purchaseReturn->purchase_id)
                    ->where('product_id', $productId)
                    ->first();

                if (!$purchaseItem) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Purchase Item not found.'
                        );
                }

                // Previous returns excluding current return
                $alreadyReturned = PurchaseReturnItem::where('product_id', $productId)
                    ->whereHas('purchaseReturn', function ($q) use ($purchaseReturn) {
                        $q->where('purchase_id', $purchaseReturn->purchase_id);
                    })
                    ->sum('quantity');

                $balance = $purchaseItem->quantity - $alreadyReturned;

                if ($qty > $balance) {

                    return redirect()
                        ->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Return quantity exceeds balance for product: '
                                . $purchaseItem->product->product_name
                        );
                }

                PurchaseReturnItem::create([

                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id'         => $productId,
                    'quantity'           => $qty,
                    'purchase_price'     => $request->price[$key],
                    'gst_percent'        => $request->gst[$key],
                    'gst_amount'         => $request->gst_amount[$key],
                    'line_total'         => $request->amount[$key],

                ]);

                // Increase stock
                $product = Product::find($productId);

                if ($product) {

                    $product->current_stock += $qty;

                    $product->save();
                }
            }

            DB::commit();

            return redirect()
                ->route('purchase-returns.index')
                ->with('success', 'Purchase Return Updated Successfully.');
        } catch (\Exception $e) {

            DB::rollBack();
            dd($e->getMessage());

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $purchaseReturn = PurchaseReturn::with('items')->findOrFail($id);

            // Reverse stock
            foreach ($purchaseReturn->items as $item) {

                $product = Product::find($item->product_id);

                if ($product) {

                    // Purchase Return reduced stock,
                    // so deleting it adds the stock back.
                    $product->current_stock -= $item->quantity;

                    if ($product->current_stock < 0) {
                        $product->current_stock = 0;
                    }

                    $product->save();
                }
            }

            // Delete return items
            PurchaseReturnItem::where(
                'purchase_return_id',
                $purchaseReturn->id
            )->delete();

            // Delete return header
            $purchaseReturn->delete();

            DB::commit();

            return redirect()
                ->route('purchase-returns.index')
                ->with('success', 'Purchase Return deleted successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
    
    public function print($id)
    {
        $purchaseReturn = PurchaseReturn::with([
            'purchase',
            'supplier',
            'items.product'
        ])->findOrFail($id);

        return view('purchase_returns.print', compact('purchaseReturn'));
    }
    
}