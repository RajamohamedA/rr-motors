<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class BranchSelectionController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Company Owner
        |--------------------------------------------------------------------------
        |
        | Company owner can access every active branch belonging
        | to their own company.
        |
        */

        if ($user->hasRole('company-owner')) {

            $stores = $user->company
                ->stores()
                ->where('status', 1)
                ->orderBy('store_name')
                ->get();
        } else {

            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            |
            | Normal users can only access branches assigned through
            | user_stores.
            |
            */

            $stores = $user->stores()
                ->where('stores.status', 1)
                ->where('stores.company_id', $user->company_id)
                ->orderBy('store_name')
                ->get();
        }

        abort_if($stores->isEmpty(), 403, 'No active branch is available.');

        return Inertia::render('Branch/Select', [
            'company' => $user->company,
            'stores' => $stores,
        ]);
    }
}
