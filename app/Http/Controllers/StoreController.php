<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StoreController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:store.view', only: ['index']),
            new Middleware('permission:store.create', only: ['create', 'store']),
            new Middleware('permission:store.edit', only: ['edit', 'update']),
            new Middleware('permission:store.delete', only: ['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $stores = Store::with('company')
                        ->when($request->search, function ($query) use ($request) {

                            $query->where('store_name', 'like', '%' . $request->search . '%')
                                ->orWhere('store_code', 'like', '%' . $request->search . '%')
                                ->orWhereHas('company', function ($q) use ($request) {
                                    $q->where('company_name', 'like', '%' . $request->search . '%');
                                });

                            })
                        ->latest()
                        ->paginate(10);

        return view('stores.index', compact('stores'));
    }

    public function create()
    {
        $companies = Company::where('status', 1)
            ->orderBy('company_name')
            ->get();

        return view('stores.create', compact('companies'));
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        // Auto Generate Store Code
        $lastStore = Store::latest('id')->first();

        if ($lastStore) {
            $number = (int) substr($lastStore->store_code, 3);
            $number++;
        } else {
            $number = 1;
        }

        $data['store_code'] = 'STR' . str_pad($number, 6, '0', STR_PAD_LEFT);

        Store::create($data);

        return redirect()
            ->route('stores.index')
            ->with('success', 'Store created successfully.');
    }

    public function edit(Store $store)
    {
        $companies = Company::where('status', 1)
            ->orderBy('company_name')
            ->get();

        return view('stores.edit', compact('store', 'companies'));
    }

    public function update(StoreRequest $request, Store $store)
    {
        $store->update($request->validated());

        return redirect()
            ->route('stores.index')
            ->with('success', 'Store updated successfully.');
    }

    public function destroy(Store $store)
    {
        $store->delete();

        return redirect()
            ->route('stores.index')
            ->with('success', 'Store deleted successfully.');
    }
}