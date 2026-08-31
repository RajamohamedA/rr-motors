<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Services\WalkInCustomerService;
use Illuminate\Database\Seeder;

class WalkInCustomerSeeder extends Seeder
{
    public function run(
        WalkInCustomerService $service
    ): void {

        Company::query()

            ->where(
                'status',
                true
            )

            ->chunkById(
                100,
                function (
                    $companies
                ) use (
                    $service
                ) {

                    foreach (
                        $companies as $company
                    ) {

                        $service->getOrCreate(
                            $company
                        );
                    }
                }
            );
    }
}
