<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display customers
     */
    public function index()
    {
        $customers = Customer::with('company')
            ->latest()
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $companies = Company::where('status',1)->get();

        $lastCustomer = Customer::latest('id')->first();

        $nextNumber = $lastCustomer ? $lastCustomer->id + 1 : 1;

        $customerCode = 'CUS' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return view('customers.create', compact(
            'companies',
            'customerCode'
        ));
    }

    /**
     * Store customer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'company_id' => 'required|exists:companies,id',

            'customer_code' => 'required|unique:customers,customer_code',

            'customer_name' => 'required|max:150',

            'mobile' => 'required|max:20',

            'email' => 'nullable|email',

            'gst_number' => 'nullable|unique:customers,gst_number',

            'billing_address' => 'required',

        ]);

        Customer::create($request->all());

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show customer
     */
    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Edit form
     */
    public function edit(Customer $customer)
    {
        $companies = Company::where('status',1)->get();

        return view('customers.edit', compact(
            'customer',
            'companies'
        ));
    }

    /**
     * Update customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([

            'company_id' => 'required|exists:companies,id',

            'customer_code' => 'required|unique:customers,customer_code,' . $customer->id,

            'customer_name' => 'required|max:150',

            'mobile' => 'required|max:20',

            'email' => 'nullable|email',

            'gst_number' => 'nullable|unique:customers,gst_number,' . $customer->id,

            'billing_address' => 'required',

        ]);

        $customer->update($request->all());

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Delete customer
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}