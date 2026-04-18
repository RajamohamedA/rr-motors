<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;

class AjaxController extends Controller
{
    public function stores($company)
    {
        return Store::where('company_id',$company)
            ->where('status',1)
            ->orderBy('store_name')
            ->get();
    }

    public function categories($company)
    {
        
    }

    public function brands($company)
    {
        
    }

    public function suppliers($company)
    {
        
    }
}
