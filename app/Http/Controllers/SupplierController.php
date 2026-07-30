<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:supplier.view', only: ['index']),
            new Middleware('permission:supplier.create', only: ['create', 'store']),
            new Middleware('permission:supplier.edit', only: ['edit', 'update']),
            new Middleware('permission:supplier.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $suppliers = Supplier::with('company')
            ->when($request->search, function ($query) use ($request) {
                $query->where('supplier_name', 'like', '%' . $request->search . '%')
                      ->orWhere('supplier_code', 'like', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $companies = Company::where('status',1)
            ->orderBy('company_name')
            ->get();

        return view('suppliers.create', compact('companies'));
    }

    public function store(SupplierRequest $request)
    {
        $data = $request->validated();

        $last = Supplier::latest('id')->first();

        if ($last && $last->supplier_code) {
            $number = (int) substr($last->supplier_code,3);
            $number++;
        } else {
            $number = 1;
        }

        $data['supplier_code'] = 'SUP' . str_pad($number,6,'0',STR_PAD_LEFT);

        if (empty($data['country'])) {
            $data['country'] = 'India';
        }

        Supplier::create($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success','Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        $companies = Company::where('status',1)
            ->orderBy('company_name')
            ->get();

        return view('suppliers.edit', compact('supplier','companies'));
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();

        if (empty($data['country'])) {
            $data['country'] = 'India';
        }

        $supplier->update($data);

        return redirect()
            ->route('suppliers.index')
            ->with('success','Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success','Supplier deleted successfully.');
    }
}