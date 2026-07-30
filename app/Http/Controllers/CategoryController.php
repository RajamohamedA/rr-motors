<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:category.view', only: ['index']),
            new Middleware('permission:category.create', only: ['create', 'store']),
            new Middleware('permission:category.edit', only: ['edit', 'update']),
            new Middleware('permission:category.delete', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $categories = Category::when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('code', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $companies = Company::where('status', 1)->orderBy('company_name')->get();
        return view('categories.create', compact('companies'));
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();

        $last = Category::latest('id')->first();

        if ($last && $last->code) {
            $number = (int) substr($last->code, 3);
            $number++;
        } else {
            $number = 1;
        }

        $data['code'] = 'CAT' . str_pad($number, 6, '0', STR_PAD_LEFT);

        //dd($data);
        Category::create($data);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $companies = Company::where('status', 1)->orderBy('company_name')->get();
    
        return view('categories.edit', compact('category', 'companies'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}