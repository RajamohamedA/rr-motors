<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IndiaLocationSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/india-locations.json');
        if (!is_file($file)) {
            throw new RuntimeException('Copy database/data/india-locations.json before seeding.');
        }
        $data = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($data) {
            foreach ($data['states'] as $state) {
                DB::table('geo_states')->updateOrInsert(
                    ['code' => $state['code']], ['name' => $state['name']]
                );
                foreach ($state['districts'] as $district) {
                    DB::table('geo_districts')->updateOrInsert(
                        ['code' => $district['code']],
                        ['state_code' => $state['code'], 'name' => $district['name']]
                    );
                    foreach (array_chunk($district['cities'], 500) as $names) {
                        $rows = array_map(fn ($name) => [
                            'district_code' => $district['code'], 'name' => $name,
                        ], $names);
                        // Stable IDs and repeatable import: do not truncate reference data.
                        DB::table('geo_cities')->upsert(
                            $rows, ['district_code', 'name'], ['name']
                        );
                    }
                }
            }
        });
    }
}