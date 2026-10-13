<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class BranchSelectionController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        abort_if(
            ! $user,
            401,
            'Please log in to continue.'
        );

        $company = $user->company;

        abort_if(
            ! $company,
            403,
            'No company is assigned to your account.'
        );

        /*
        |--------------------------------------------------------------------------
        | Company Admin / Company Owner
        |--------------------------------------------------------------------------
        |
        | Access all active branches belonging to their company.
        |
        */

        if ($user->hasAnyRole(['Company Admin', 'company-owner'])) {
            $stores = $company->stores()
                ->where('stores.status', 1)
                ->orderBy('stores.store_name')
                ->get();
        } else {
            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            |
            | Access only assigned active branches within their company.
            |
            */

            $stores = $user->stores()
                ->where('stores.company_id', $company->id)
                ->where('stores.status', 1)
                ->orderBy('stores.store_name')
                ->get();
        }

        abort_if(
            $stores->isEmpty(),
            403,
            'No active branch is available for your account.'
        );

        return Inertia::render('Branch/Select', [
            'company' => $company,
            'stores' => $stores,
        ]);
    }
}
