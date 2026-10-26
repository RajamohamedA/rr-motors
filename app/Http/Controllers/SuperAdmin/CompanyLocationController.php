<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyLocationController extends Controller
{
    public function states()
    {
        return response()->json(['data' => DB::table('geo_states')
            ->orderBy('name')->get(['code as id', 'name'])]);
    }

    public function districts(Request $request)
    {
        $data = $request->validate([
            'state_id' => ['required', 'string', Rule::exists('geo_states', 'code')],
        ]);
        return response()->json(['data' => DB::table('geo_districts')
            ->where('state_code', $data['state_id'])->orderBy('name')
            ->get(['code as id', 'name'])]);
    }

    public function cities(Request $request)
    {
        $data = $request->validate([
            'district_id' => ['required', 'string', Rule::exists('geo_districts', 'code')],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $query = DB::table('geo_cities')->where('district_code', $data['district_id']);
        $term = trim($data['q'] ?? '');
        // Literal prefix search; escape SQL LIKE wildcard input.
        if ($term !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
            $query->whereRaw("LOWER(name) LIKE ? ESCAPE '!'", [mb_strtolower($escaped) . '%']);
        }
        return response()->json(['data' => $query->orderBy('name')
            ->limit(60)->get(['id', 'name'])]);
    }
}
