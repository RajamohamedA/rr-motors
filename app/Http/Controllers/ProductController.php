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
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller  implements HasMiddleware
{
    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:product.view',
                only: ['index']
            ),
            new Middleware(
                'permission:product.show',
                only: ['show']
            ),
            new Middleware(
                'permission:product.create',
                only: ['create', 'store']
            ),

            new Middleware(
                'permission:product.edit',
                only: ['edit', 'update']
            ),

            new Middleware(
                'permission:product.delete',
                only: ['destroy']
            ),

        ];
    }
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
        $stores     = [];
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
        //dd($product);
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

        $hasStock = Stock::where('product_id', $product->id)->exists();

        if ($hasStock) {

            return redirect()
                ->route('products.index')
                ->with(
                    'error',
                    'This product cannot be deleted because stock transactions exist. Please deactivate the product instead.'
                );
        }

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
        abort_unless(
            auth()->user()->can('product.view') ||
                auth()->user()->can('purchase.create') ||
                auth()->user()->can('purchase.edit') ||
                auth()->user()->can('sales.create') ||
                auth()->user()->can('sales.edit') ||
                auth()->user()->can('quotation.create') ||
                auth()->user()->can('quotation.edit') ||
                auth()->user()->can('stock-adjustment.create'),
            403
        );
        $search = $request->search;

        /*
        |--------------------------------------------------------------------------
        | Determine Company
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            // Super Admin can select company
            $companyId = $request->company_id;
        } else {

            // Normal Admin/User uses assigned company
            $companyId = auth()->user()->company_id;
        }


        /*
        |--------------------------------------------------------------------------
        | Company Required
        |--------------------------------------------------------------------------
        */

        if (!$companyId) {

            return response()->json([
                'message' => 'Please select a company first.'
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Product Search
        |--------------------------------------------------------------------------
        */

        $products = Product::with([
            'unit',
            'brand',
            'category'
        ])
            ->where('status', 1)
            ->where('company_id', $companyId)

            ->where(function ($query) use ($search) {

                $query->where('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%");
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