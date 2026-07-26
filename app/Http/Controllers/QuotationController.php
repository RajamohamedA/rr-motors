<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sales;
use App\Models\SaleItems;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotations = Quotation::with('customer')
            ->latest()
            ->paginate(10);

        return view('quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::where('status', 1)->get();

        $stores = Store::where('status', 1)->get();

        $customers = Customer::where('status', 1)
            ->orderBy('customer_name')
            ->get();

        $quotationNo = 'QT' . str_pad(
            (Quotation::max('id') + 1),
            5,
            '0',
            STR_PAD_LEFT
        );

        return view('quotations.create', compact(
            'companies',
            'stores',
            'customers',
            'quotationNo'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([

            'quotation_no'   => 'required',

            'quotation_date' => 'required|date',

            'company_id'     => 'required',

            'store_id'       => 'required',

            'customer_id'    => 'required',

            'product_id'     => 'required|array',

            'qty'            => 'required|array',

            'price'          => 'required|array',

        ]);

        DB::beginTransaction();

        try {

            $quotation = Quotation::create([

                'company_id'    => $request->company_id,

                'store_id'      => $request->store_id,

                'customer_id'   => $request->customer_id,

                'quotation_no'  => $request->quotation_no,

                'quotation_date' => $request->quotation_date,

                'valid_until'   => $request->valid_until,

                'remarks'       => $request->remarks,

                'status'        => 'Draft',

                'sub_total'     => 0,

                'gst_total'     => 0,

                'grand_total'   => 0,

            ]);

            $subTotal = 0;

            $gstTotal = 0;

            $grandTotal = 0;

            foreach ($request->product_id as $key => $productId) {

                $qty = (float)$request->qty[$key];

                $price = (float)$request->price[$key];

                $gstPercent = (float)$request->gst[$key];

                $amount = $qty * $price;

                $gstAmount = ($amount * $gstPercent) / 100;

                $lineTotal = $amount + $gstAmount;

                QuotationItem::create([

                    'quotation_id' => $quotation->id,

                    'product_id'   => $productId,

                    'quantity'     => $qty,

                    'sale_price'   => $price,

                    'gst_percent'  => $gstPercent,

                    'gst_amount'   => $gstAmount,

                    'line_total'   => $lineTotal,

                ]);

                $subTotal += $amount;

                $gstTotal += $gstAmount;

                $grandTotal += $lineTotal;
            }

            $quotation->update([

                'sub_total'   => $subTotal,

                'gst_total'   => $gstTotal,

                'grand_total' => $grandTotal,

            ]);

            DB::commit();

            return redirect()
                ->route('quotations.index')
                ->with('success', 'Quotation Created Successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $quotation = Quotation::with([
            'company',
            'store',
            'customer',
            'items.product.unit'
        ])->findOrFail($id);

        return view('quotations.show', compact('quotation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $quotation = Quotation::with([
            'items.product',
            'customer',
            'company',
            'store'
        ])->findOrFail($id);

        $companies = Company::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();
        $customers = Customer::where('status', 1)->get();

        return view('quotations.edit', compact(
            'quotation',
            'companies',
            'stores',
            'customers'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'company_id' => 'required',
            'store_id' => 'required',
            'customer_id' => 'required',
            'quotation_date' => 'required|date',
            'product_id' => 'required|array|min:1',
            'qty' => 'required|array',
            'price' => 'required|array',
            'gst' => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            $quotation = Quotation::findOrFail($id);

            $quotation->update([
                'company_id'     => $request->company_id,
                'store_id'       => $request->store_id,
                'customer_id'    => $request->customer_id,
                'quotation_date' => $request->quotation_date,
                'valid_until'    => $request->valid_until,
                'remarks'        => $request->remarks,
            ]);

            // Delete old items
            $quotation->items()->delete();

            $subTotal = 0;
            $gstTotal = 0;
            $grandTotal = 0;

            foreach ($request->product_id as $key => $productId) {

                $qty = (float)$request->qty[$key];
                $price = (float)$request->price[$key];
                $gstPercent = (float)$request->gst[$key];

                $basic = $qty * $price;
                $gstAmount = ($basic * $gstPercent) / 100;
                $lineTotal = $basic + $gstAmount;

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id'   => $productId,
                    'quantity'     => $qty,
                    'sale_price'   => $price,
                    'gst_percent'  => $gstPercent,
                    'gst_amount'   => $gstAmount,
                    'line_total'   => $lineTotal,
                ]);

                $subTotal += $basic;
                $gstTotal += $gstAmount;
                $grandTotal += $lineTotal;
            }

            $quotation->update([
                'sub_total'  => $subTotal,
                'gst_total'  => $gstTotal,
                'grand_total' => $grandTotal,
            ]);

            DB::commit();

            return redirect()
                ->route('quotations.index')
                ->with('success', 'Quotation Updated Successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * print the quation
     */
    public function print($id)
    {
        $quotation = Quotation::with([
            'company',
            'store',
            'customer',
            'items.product.unit'
        ])->findOrFail($id);

        return view('quotations.print', compact('quotation'));
    }

    public function convertToSales($id)
    {
        DB::beginTransaction();

        try {

            $quotation = Quotation::with('items')->findOrFail($id);

            if ($quotation->status == 'Converted') {

                return back()->with('error', 'Quotation already converted.');
            }

            $sale = Sales::create([

                'company_id'   => $quotation->company_id,

                'store_id'     => $quotation->store_id,

                'customer_id'  => $quotation->customer_id,

                'invoice_no'   => Sales::generateInvoiceNo(),

                'invoice_date' => now(),

                'sub_total'    => $quotation->sub_total,

                'gst_total'    => $quotation->gst_total,

                'grand_total'  => $quotation->grand_total,

                'paid_amount'  => 0,

                'balance_amount' => $quotation->grand_total,

                'remarks'      => 'Converted from Quotation : ' . $quotation->quotation_no,

            ]);

            foreach ($quotation->items as $item) {

                SaleItems::create([

                    'sale_id' => $sale->id,

                    'product_id' => $item->product_id,

                    'quantity' => $item->quantity,

                    'sale_price' => $item->sale_price,

                    'gst_percent' => $item->gst_percent,

                    'gst_amount' => $item->gst_amount,

                    'line_total' => $item->line_total,

                ]);
            }

            $quotation->update([

                'status' => 'Converted'

            ]);

            DB::commit();

            return redirect()
                ->route('sales.edit', $sale->id)
                ->with('success', 'Quotation Converted Successfully.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $quotation = Quotation::with('items')->findOrFail($id);

            // Don't allow deleting converted quotations
            if ($quotation->status == 'Converted') {

                return back()->with(
                    'error',
                    'Converted quotation cannot be deleted.'
                );
            }

            // Delete quotation items
            $quotation->items()->delete();

            // Delete quotation
            $quotation->delete();

            DB::commit();

            return redirect()
                ->route('quotations.index')
                ->with(
                    'success',
                    'Quotation deleted successfully.'
                );
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with(
                'error',
                $e->getMessage()
            );
        }
    }
}
