<?php

namespace App\Http\Controllers;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BrandController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:brand.view', only: ['index']),
            new Middleware('permission:brand.create', only: ['create', 'store']),
            new Middleware('permission:brand.edit', only: ['edit', 'update']),
            new Middleware('permission:brand.delete', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $brands = Brand::with('company')
            ->when($request->search, function ($query) use ($request) {
                $query->where('brand_name', 'like', '%' . $request->search . '%')
                      ->orWhere('brand_code', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        $companies = Company::where('status', 1)
            ->orderBy('company_name')
            ->get();

        return view('brands.create', compact('companies'));
    }

    public function store(BrandRequest $request)
    {
        $data = $request->validated();

        $last = Brand::latest('id')->first();

        if ($last && $last->brand_code) {
            $number = (int) substr($last->brand_code, 3);
            $number++;
        } else {
            $number = 1;
        }

        $data['brand_code'] = 'BRD' . str_pad($number, 6, '0', STR_PAD_LEFT);

        Brand::create($data);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }

    public function edit(Brand $brand)
    {
        $companies = Company::where('status', 1)
            ->orderBy('company_name')
            ->get();

        return view('brands.edit', compact('brand', 'companies'));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}