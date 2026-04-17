<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index(){

        $totalSuppliers = Supplier::count();

        // return view('dashboard.index', compact(
        //     'totalCompanies',
        //     'totalStores',
        //     'totalCategories',
        //     'totalUnits',
        //     'totalSuppliers'
        // ));

        return view('dashboard.index', compact(
            'totalSuppliers'
        ));
        
    }
}
