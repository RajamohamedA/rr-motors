<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Store;
use Illuminate\Http\Request;


class PurchaseReportController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();

        $products = Product::orderBy('product_name')->get();

        $categories = Category::orderBy('name')->get();

        $brands = Brand::orderBy('brand_name')->get();

        $stores = Store::orderBy('store_name')->get();

        return view('purchase_reports.index', compact(

            'suppliers',

            'products',

            'categories',

            'brands',

            'stores'

        ));
    }


    public function search(Request $request)
    {
        $query = Purchase::with([
            'supplier',
            'store',
            'items.product.brand',
            'items.product.category'
        ]);

        // From Date
        if (!empty($request->from_date)) {
            $query->whereDate('purchase_date', '>=', $request->from_date);
        }

        // To Date
        if (!empty($request->to_date)) {
            $query->whereDate('purchase_date', '<=', $request->to_date);
        }

        // Purchase No
        if (!empty($request->purchase_no)) {
            $query->where('purchase_no', 'LIKE', '%' . $request->purchase_no . '%');
        }

        // Supplier
        if (!empty($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Store
        if (!empty($request->store_id)) {
            $query->where('store_id', $request->store_id);
        }

        // Product
        if (!empty($request->product_id)) {

            $query->whereHas('items', function ($q) use ($request) {

                $q->where('product_id', $request->product_id);
            });
        }

        // Category
        if (!empty($request->category_id)) {

            $query->whereHas('items.product', function ($q) use ($request) {

                $q->where('category_id', $request->category_id);
            });
        }

        // Brand
        if (!empty($request->brand_id)) {

            $query->whereHas('items.product', function ($q) use ($request) {

                $q->where('brand_id', $request->brand_id);
            });
        }

        $purchases = $query
            ->orderBy('purchase_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('purchase_reports.result', compact('purchases'));
    }


    public function show($id)
    {
        $purchase = Purchase::with([
            'company',
            'supplier',
            'store',
            'items.product',
            'items.unit'
        ])->findOrFail($id);

        return view(
            'purchase_reports.show',
            compact('purchase')
        );
    }
}
