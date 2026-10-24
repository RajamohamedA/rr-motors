<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchSelectionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_unless(
            $user,
            401,
            'Please log in to continue.'
        );

        abort_unless(
            $user->status,
            403,
            'Your account is inactive.'
        );

        $company = $user->company;

        abort_unless(
            $company,
            403,
            'No company is assigned to your account.'
        );

        abort_unless(
            $company->status,
            403,
            'Your company is inactive.'
        );

        /*
        |--------------------------------------------------------------------------
        | Assigned Branches
        |--------------------------------------------------------------------------
        |
        | Company Admin, Store Manager and other company users must
        | have explicit branch assignments through user_stores.
        |
        | Super Admin uses the separate company/branch selection flow.
        |
        */

        $stores = $user->stores()
            ->where('stores.company_id', $company->id)
            ->where('stores.status', true)
            ->orderBy('stores.store_name')
            ->get();

        abort_if(
            $stores->isEmpty(),
            403,
            'No active branch is assigned to your account.'
        );

        return Inertia::render('Branch/Select', [
            'company' => $company,
            'stores' => $stores,
        ]);
    }
}
