<?php

namespace App\Http\Controllers;

use App\Models\Godown;
use App\Models\Company;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GodownController extends Controller
{
    /**
     * Display godowns.
     */
    public function index(Request $request)
    {
        $godowns = Godown::with([
            'company',
            'store'
        ]);

        /*
        |--------------------------------------------------------------------------
        | Company restriction
        |--------------------------------------------------------------------------
        */

        if (!auth()->user()->is_super_user) {

            $godowns->where(
                'company_id',
                auth()->user()->company_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $godowns->where(function ($query) use ($search) {

                $query->where(
                    'godown_name',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'godown_code',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Store filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('store_id')) {

            $godowns->where(
                'store_id',
                $request->store_id
            );
        }

        $godowns = $godowns
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'godowns.index',
            compact('godowns')
        );
    }


    /**
     * Show create form.
     */
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $companies = Company::where('status', 1)
                ->orderBy('company_name')
                ->get();
        } else {

            $companies = Company::where(
                'id',
                auth()->user()->company_id
            )
                ->where('status', 1)
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Stores
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $stores = Store::where('status', 1)
                ->orderBy('store_name')
                ->get();
        } else {

            $stores = Store::where(
                'company_id',
                auth()->user()->company_id
            )
                ->where('status', 1)
                ->orderBy('store_name')
                ->get();
        }


        return view(
            'godowns.create',
            compact(
                'companies',
                'stores'
            )
        );
    }


    /**
     * Store godown.
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->is_super_user
            ? $request->company_id
            : auth()->user()->company_id;


        $request->validate([

            'company_id' => auth()->user()->is_super_user
                ? 'required|exists:companies,id'
                : 'nullable',

            'store_id' => [
                'required',
                'exists:stores,id',
            ],

            'godown_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('godowns', 'godown_name')
                    ->where(function ($query) use ($request) {

                        return $query->where(
                            'store_id',
                            $request->store_id
                        );
                    }),
            ],

            'godown_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
                'required',
                'boolean',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Verify Store belongs to Company
        |--------------------------------------------------------------------------
        */

        $store = Store::where('id', $request->store_id)
            ->where('company_id', $companyId)
            ->firstOrFail();


        DB::transaction(function () use (
            $request,
            $companyId
        ) {

            Godown::create([

                'company_id' => $companyId,

                'store_id' => $request->store_id,

                'godown_name' => $request->godown_name,

                'godown_code' => $request->godown_code,

                'address' => $request->address,

                'status' => $request->status,

            ]);
        });


        return redirect()
            ->route('godowns.index')
            ->with(
                'success',
                'Godown created successfully.'
            );
    }


    /**
     * Display godown.
     */
    public function show(Godown $godown)
    {
        /*
        |--------------------------------------------------------------------------
        | Company security
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->user()->is_super_user &&
            $godown->company_id != auth()->user()->company_id
        ) {

            abort(403);
        }


        $godown->load([
            'company',
            'store'
        ]);


        return view(
            'godowns.show',
            compact('godown')
        );
    }


    /**
     * Edit godown.
     */
    public function edit(Godown $godown)
    {
        /*
        |--------------------------------------------------------------------------
        | Company security
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->user()->is_super_user &&
            $godown->company_id != auth()->user()->company_id
        ) {

            abort(403);
        }


        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $companies = Company::where('status', 1)
                ->orderBy('company_name')
                ->get();
        } else {

            $companies = Company::where(
                'id',
                auth()->user()->company_id
            )
                ->where('status', 1)
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Stores
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->is_super_user) {

            $stores = Store::where('status', 1)
                ->orderBy('store_name')
                ->get();
        } else {

            $stores = Store::where(
                'company_id',
                auth()->user()->company_id
            )
                ->where('status', 1)
                ->orderBy('store_name')
                ->get();
        }


        return view(
            'godowns.edit',
            compact(
                'godown',
                'companies',
                'stores'
            )
        );
    }


    /**
     * Update godown.
     */
    public function update(
        Request $request,
        Godown $godown
    ) {

        /*
        |--------------------------------------------------------------------------
        | Company security
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->user()->is_super_user &&
            $godown->company_id != auth()->user()->company_id
        ) {

            abort(403);
        }


        $companyId = auth()->user()->is_super_user
            ? $request->company_id
            : auth()->user()->company_id;


        $request->validate([

            'company_id' => auth()->user()->is_super_user
                ? 'required|exists:companies,id'
                : 'nullable',

            'store_id' => [
                'required',
                'exists:stores,id',
            ],

            'godown_name' => [
                'required',
                'string',
                'max:255',

                Rule::unique(
                    'godowns',
                    'godown_name'
                )
                    ->where(function ($query) use ($request) {

                        return $query->where(
                            'store_id',
                            $request->store_id
                        );
                    })
                    ->ignore($godown->id),
            ],

            'godown_code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
                'required',
                'boolean',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | Verify Store belongs to Company
        |--------------------------------------------------------------------------
        */

        Store::where('id', $request->store_id)
            ->where('company_id', $companyId)
            ->firstOrFail();


        DB::transaction(function () use (
            $request,
            $godown,
            $companyId
        ) {

            $godown->update([

                'company_id' => $companyId,

                'store_id' => $request->store_id,

                'godown_name' => $request->godown_name,

                'godown_code' => $request->godown_code,

                'address' => $request->address,

                'status' => $request->status,

            ]);
        });


        return redirect()
            ->route('godowns.index')
            ->with(
                'success',
                'Godown updated successfully.'
            );
    }


    /**
     * Delete godown.
     */
    public function destroy(Godown $godown)
    {
        /*
        |--------------------------------------------------------------------------
        | Company security
        |--------------------------------------------------------------------------
        */

        if (
            !auth()->user()->is_super_user &&
            $godown->company_id != auth()->user()->company_id
        ) {

            abort(403);
        }


        $godown->delete();


        return redirect()
            ->route('godowns.index')
            ->with(
                'success',
                'Godown deleted successfully.'
            );
    }
}
