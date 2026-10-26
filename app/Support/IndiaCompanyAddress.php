<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IndiaCompanyAddress
{
    public function normalize(array $data): array
    {
        $state = DB::table('geo_states')->where('code', $data['state_id'])->first();
        if (!$state) {
            throw ValidationException::withMessages([
                'state_id' => 'Choose a state from the list.',
            ]);
        }
        $district = DB::table('geo_districts')
            ->where('code', $data['district_id'])
            ->where('state_code', $state->code)->first();
        if (!$district) {
            throw ValidationException::withMessages([
                'district_id' => 'Choose a district belonging to the selected state.',
            ]);
        }
        $cityName = trim($data['city'] ?? '');
        if (!empty($data['city_id'])) {
            $city = DB::table('geo_cities')
                ->where('id', $data['city_id'])
                ->where('district_code', $district->code)->first();
            if (!$city) {
                throw ValidationException::withMessages([
                    'city' => 'Choose a city belonging to the selected district.',
                ]);
            }
            $cityName = $city->name;
        }
        if ($cityName === '') {
            throw ValidationException::withMessages(['city' => 'Enter or choose your city / town.']);
        }
        // Existing application queries and invoices continue using name fields.
        $data['country'] = 'India';
        $data['state'] = $state->name;
        $data['district'] = $district->name;
        $data['city'] = $cityName;
        unset($data['state_id'], $data['district_id'], $data['city_id']);
        return $data;
    }
}
