<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCompanyBranchCodes extends Command
{
    protected $signature = 'rr:backfill-company-branches';

    protected $description =
    'Generate missing company codes and branch numbers for existing data';

    public function handle(): int
    {
        $this->info('Starting company/branch backfill...');

        $companies = Company::withTrashed()
            ->orderBy('id')
            ->get();

        foreach ($companies as $company) {

            DB::transaction(function () use ($company) {

                /*
                |--------------------------------------------------------------------------
                | Company Code
                |--------------------------------------------------------------------------
                */

                if (empty($company->company_code)) {

                    $company->company_code =
                        Company::generateCompanyCode(
                            $company->company_name
                        );

                    $company->save();

                    $this->info(
                        "Company {$company->id}: generated code {$company->company_code}"
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Existing Branches
                |--------------------------------------------------------------------------
                |
                | Keep existing store_code.
                | Only populate missing branch_number.
                |
                */

                $stores = Store::where(
                    'company_id',
                    $company->id
                )
                    ->orderBy('id')
                    ->get();


                /*
                |--------------------------------------------------------------------------
                | Find numbers already assigned
                |--------------------------------------------------------------------------
                */

                $usedNumbers = $stores
                    ->pluck('branch_number')
                    ->filter(fn($number) => $number !== null)
                    ->map(fn($number) => (int) $number)
                    ->values()
                    ->all();


                $nextNumber = 1;


                foreach ($stores as $store) {

                    /*
                    |--------------------------------------------------------------------------
                    | Existing branch number - leave unchanged
                    |--------------------------------------------------------------------------
                    */

                    if ($store->branch_number !== null) {
                        continue;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Find next unused number
                    |--------------------------------------------------------------------------
                    */

                    while (
                        in_array(
                            $nextNumber,
                            $usedNumbers,
                            true
                        )
                    ) {
                        $nextNumber++;
                    }


                    $store->branch_number =
                        $nextNumber;

                    $store->save();


                    $this->line(
                        "  Branch {$store->store_name}: " .
                            "number {$nextNumber} " .
                            "(store code kept: {$store->store_code})"
                    );


                    $usedNumbers[] =
                        $nextNumber;

                    $nextNumber++;
                }
            });
        }


        $this->newLine();

        $this->info(
            'Company/branch backfill completed successfully.'
        );

        return self::SUCCESS;
    }
}
