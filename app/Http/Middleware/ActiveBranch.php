<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveBranch
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $companyId = session('active_company_id');
        $storeId = session('active_store_id');

        /*
        |--------------------------------------------------------------------------
        | No active branch
        |--------------------------------------------------------------------------
        */

        if (!$companyId || !$storeId) {

            return redirect()->route('dashboard');
        }

        /*
        |--------------------------------------------------------------------------
        | Verify company
        |--------------------------------------------------------------------------
        */

        $company = Company::where('id', $companyId)
            ->where('status', 1)
            ->first();

        abort_unless($company, 404);

        /*
        |--------------------------------------------------------------------------
        | Verify branch
        |--------------------------------------------------------------------------
        */

        $store = Store::where('id', $storeId)
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->first();

        abort_unless($store, 404);

        /*
        |--------------------------------------------------------------------------
        | Share active context with request
        |--------------------------------------------------------------------------
        */

        $request->attributes->set(
            'active_company',
            $company
        );

        $request->attributes->set(
            'active_store',
            $store
        );

        return $next($request);
    }
}
