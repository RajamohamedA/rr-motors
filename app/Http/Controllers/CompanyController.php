<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::when($request->search, function ($query) use ($request) {
                $query->where('company_name', 'like', '%' . $request->search . '%')
                      ->orWhere('gst_number', 'like', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(CompanyRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('companies', 'public');
        }

        if (empty($data['country'])) {
            $data['country'] = 'India';
        }

        //dd($data);
        Company::create($data);

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $data = $request->validated();
    
        if ($request->hasFile('logo')) {
    
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
    
            $data['logo'] = $request->file('logo')->store('companies', 'public');
        }
    
        $company->update($data);
    
        return redirect()
                ->route('companies.index')
                ->with('success','Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }
}