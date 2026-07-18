<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        $company = Company::first();

        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'company_name' => 'required|max:255',
            'owner_name'   => 'nullable|max:255',
            'gst_number'   => 'nullable|max:50',
            'pan_number'   => 'nullable|max:50',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|max:20',
            'website'      => 'nullable|max:255',
            'address'      => 'nullable',
            'city'         => 'nullable|max:100',
            'state'        => 'nullable|max:100',
            'country'      => 'nullable|max:100',
            'pincode'      => 'nullable|max:10',
        ]);

        $company = Company::first();

        if (!$company) {
            $company = Company::create($validated);
        } else {
            $company->update($validated);
        }

        return redirect()
            ->route('company.edit')
            ->with('success', 'Company information saved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        //
    }
}
