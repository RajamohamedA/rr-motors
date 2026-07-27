<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use App\Models\Store;
use App\Models\Sales;
use App\Models\CustomerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerPaymentController extends Controller
{
    public function index()
    {
        $payments = CustomerPayment::with([
            'customer',
            'sale',
            'company',
            'store'
        ])
            ->latest()
            ->paginate(20);

        return view('customer-payments.index', compact('payments'));
    }

    public function create()
    {
        $companies = Company::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();

        $receiptNo = CustomerPayment::generateReceiptNo();

        return view('customer-payments.create', compact(
            'companies',
            'stores',
            'receiptNo'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([

            'company_id' => 'required',
            'store_id' => 'required',
            'customer_id' => 'required',
            'sale_id' => 'required',

            'payment_date' => 'required|date',

            'amount' => 'required|numeric|min:0.01',

            'payment_mode' => 'required'

        ]);

        DB::beginTransaction();

        try {

            $sale = Sales::findOrFail($request->sale_id);

            if ($request->amount > $sale->balance_amount) {

                return back()
                    ->withInput()
                    ->with(
                        'error',
                        'Payment amount exceeds balance amount.'
                    );
            }

            CustomerPayment::create([

                'company_id' => $request->company_id,

                'store_id' => $request->store_id,

                'customer_id' => $request->customer_id,

                'sale_id' => $request->sale_id,

                'receipt_no' => CustomerPayment::generateReceiptNo(),

                'payment_date' => $request->payment_date,

                'amount' => $request->amount,

                'payment_mode' => $request->payment_mode,

                'reference_no' => $request->reference_no,

                'remarks' => $request->remarks

            ]);

            $sale->paid_amount += $request->amount;

            $sale->balance_amount =
                $sale->grand_total - $sale->paid_amount;

            $sale->save();

            DB::commit();

            return redirect()
                ->route('customer-payments.index')
                ->with(
                    'success',
                    'Payment received successfully.'
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
        $payment = CustomerPayment::with([
            'customer',
            'sale',
            'company',
            'store'
        ])->findOrFail($id);

        return view('customer-payments.show', compact('payment'));
    }

    public function edit($id)
    {
        $payment = CustomerPayment::findOrFail($id);

        $companies = Company::where('status', 1)->get();
        $stores = Store::where('status', 1)->get();

        return view('customer-payments.edit', compact(
            'payment',
            'companies',
            'stores'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company_id'     => 'required|exists:companies,id',
            'store_id'       => 'required|exists:stores,id',
            'sale_id'        => 'required|exists:sales,id',
            'customer_id'    => 'required|exists:customers,id',
            'payment_date'   => 'required|date',
            'amount'         => 'required|numeric|min:0.01',
            'payment_mode'   => 'required',
        ]);

        DB::transaction(function () use ($request, $id) {

            $payment = CustomerPayment::findOrFail($id);

            // Restore old payment
            $sale = Sales::findOrFail($payment->sale_id);

            $sale->paid_amount -= $payment->amount;

            if ($sale->paid_amount < 0) {
                $sale->paid_amount = 0;
            }

            // Update payment
            $payment->update([
                'company_id'    => $request->company_id,
                'store_id'      => $request->store_id,
                'customer_id'   => $request->customer_id,
                'sale_id'       => $request->sale_id,
                'payment_date'  => $request->payment_date,
                'amount'        => $request->amount,
                'payment_mode'  => $request->payment_mode,
                'reference_no'  => $request->reference_no,
                'remarks'       => $request->remarks,
            ]);

            // Apply new payment
            $sale = Sales::findOrFail($request->sale_id);

            $sale->paid_amount += $request->amount;

            $sale->balance_amount = $sale->grand_total - $sale->paid_amount;

            if ($sale->balance_amount < 0) {
                $sale->balance_amount = 0;
            }

            $sale->save();
        });

        return redirect()
            ->route('customer-payments.index')
            ->with('success', 'Customer payment updated successfully.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {

            $payment = CustomerPayment::findOrFail($id);

            $sale = Sales::findOrFail($payment->sale_id);

            // Restore invoice amounts
            $sale->paid_amount -= $payment->amount;

            if ($sale->paid_amount < 0) {
                $sale->paid_amount = 0;
            }

            $sale->balance_amount = $sale->grand_total - $sale->paid_amount;

            if ($sale->balance_amount < 0) {
                $sale->balance_amount = 0;
            }

            $sale->save();

            // Delete payment
            $payment->delete();
        });

        return redirect()
            ->route('customer-payments.index')
            ->with('success', 'Customer payment deleted successfully.');
    }

    public function searchInvoice(Request $request)
    {
        $sales = Sales::with('customer')
            ->where('invoice_no', 'like', '%' . $request->search . '%')
            ->where('balance_amount', '>', 0)
            ->limit(20)
            ->get();

        return response()->json($sales);
    }

    public function invoiceDetails($id)
    {
        return Sales::with([
            'customer',
            'company',
            'store'
        ])->findOrFail($id);
    }

    public function print($id)
    {
        $payment = CustomerPayment::with([
            'company',
            'store',
            'customer',
            'sale'
        ])->findOrFail($id);

        return view('customer-payments.print', compact('payment'));
    }

    public function customerLedger(Request $request)
    {
        $customers = Customer::where('status', 1)->get();

        $ledger = collect();

        if ($request->filled('customer_id')) {

            $customer = Customer::findOrFail($request->customer_id);

            // Invoice Entries
            $sales = Sales::where('customer_id', $customer->id)
                ->orderBy('invoice_date')
                ->get();

            foreach ($sales as $sale) {

                $ledger->push([
                    'date'      => $sale->invoice_date,
                    'reference' => $sale->invoice_no,
                    'type'      => 'Invoice',
                    'debit'     => (float) $sale->grand_total,
                    'credit'    => 0,
                ]);
            }

            // Payment Entries
            $payments = CustomerPayment::where('customer_id', $customer->id)
                ->orderBy('payment_date')
                ->get();

            foreach ($payments as $payment) {

                $ledger->push([
                    'date'      => $payment->payment_date,
                    'reference' => $payment->receipt_no,
                    'type'      => 'Payment',
                    'debit'     => 0,
                    'credit'    => (float) $payment->amount,
                ]);
            }

            // Sort by Date
            $ledger = $ledger->sortBy('date')->values();

            // Running Balance
            $balance = 0;

            $ledger = $ledger->map(function ($row) use (&$balance) {

                $balance += $row['debit'];
                $balance -= $row['credit'];

                $row['balance'] = $balance;

                return $row;
            });
        }

        return view(
            'customer-payments.ledger',
            compact(
                'customers',
                'ledger'
            )
        );
    }
}
