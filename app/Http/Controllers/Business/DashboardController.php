<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->attributes->get('active_company');
        $store = $request->attributes->get('active_store');

        return Inertia::render('Business/Dashboard', [
            'company' => $company,
            'store' => $store,
        ]);
    }
}
