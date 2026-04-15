<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Http\Requests\UnitRequest;
use Illuminate\Http\Request;
use App\Models\Company;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::query();

        if ($request->filled('search')) {
            $query->where('unit_name', 'like', '%' . $request->search . '%')
                  ->orWhere('short_name', 'like', '%' . $request->search . '%');
        }

        $units = $query->orderBy('unit_name')
                       ->paginate(10)
                       ->withQueryString();

        return view('units.index', compact('units'));
    }

    public function create()
    {
        $companies = Company::orderBy('company_name')->get();

        return view('units.create',compact('companies'));
    }

    public function store(UnitRequest $request)
    {
        Unit::create($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit created successfully.');
    }

    public function edit(Unit $unit)
    {
        $companies = Company::orderBy('company_name')->get();
    
        return view('units.edit', compact('unit', 'companies'));
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $unit->update($request->validated());

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit deleted successfully.');
    }
}