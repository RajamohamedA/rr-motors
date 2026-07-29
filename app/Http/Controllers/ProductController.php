<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Company;
use App\Models\Store;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with([
                'company',
                'store',
                'category',
                'brand',
                'unit',
                'supplier'
            ])
            ->when($request->search, function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {

                    $q->where('product_code','like',"%$search%")
                    ->orWhere('product_name','like',"%$search%")
                    ->orWhere('barcode','like',"%$search%")
                    ->orWhere('part_number','like',"%$search%")
                    ->orWhere('oem_number','like',"%$search%")
                    ->orWhere('manufacturer','like',"%$search%");

                });

            })
            ->latest()
            ->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $product = new Product();
        $companies = Company::where('status',1)->orderBy('company_name')->get();
        $stores     = Store::where('status',1)->orderBy('store_name')->get();
        $categories = Category::where('status',1)->orderBy('name')->get();
        $brands     = Brand::where('status',1)->orderBy('brand_name')->get();
        $units      = Unit::where('status',1)->orderBy('unit_name')->get();
        $suppliers  = Supplier::where('status',1)->orderBy('supplier_name')->get();

        return view('products.create', compact(
            'companies',
            'stores',
            'categories',
            'brands',
            'units',
            'suppliers'
        ));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            $data['image'] = $request
                ->file('image')
                ->store('products', 'public');

        }

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success','Product created successfully.');
    }

    public function edit(Product $product)
    {
        $companies = Company::where('status',1)->orderBy('company_name')->get();
        $stores     = Store::where('status',1)->orderBy('store_name')->get();
        $categories = Category::where('status',1)->orderBy('name')->get();
        $brands     = Brand::where('status',1)->orderBy('brand_name')->get();
        $units      = Unit::where('status',1)->orderBy('unit_name')->get();
        $suppliers  = Supplier::where('status',1)->orderBy('supplier_name')->get();

        return view('products.edit', compact(
            'product',
            'companies',
            'stores',
            'categories',
            'brands',
            'units',
            'suppliers'
        ));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $request
                ->file('image')
                ->store('products','public');

        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success','Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success','Product deleted successfully.');
    }

    public function search(Request $request)
    {
        $search = $request->search;

        $products = Product::with([
                'unit',
                'brand',
                'category'
            ])
            ->where('status',1)
            ->where(function($query) use ($search){

                $query->where('product_code','like',"%{$search}%")
                    ->orWhere('barcode','like',"%{$search}%")
                    ->orWhere('product_name','like',"%{$search}%")
                    ->orWhere('part_number','like',"%{$search}%");

            })
            ->limit(20)
            ->get();

        return response()->json($products);
    }


    public function show($id)
    {
        $product = Product::with([
            'company',
            'category',
            'unit'
        ])->findOrFail($id);

        return view('products.show', compact('product'));
    }
}