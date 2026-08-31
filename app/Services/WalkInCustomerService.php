<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class WalkInCustomerService
{
    /*
    |--------------------------------------------------------------------------
    | Get Or Create Walk-In Customer
    |--------------------------------------------------------------------------
    */

    public function getOrCreate(
        Company $company
    ): Customer {

        return DB::transaction(
            function () use (
                $company
            ) {

                /*
                |--------------------------------------------------------------------------
                | Find Existing
                |--------------------------------------------------------------------------
                */

                $customer =
                    Customer::query()

                    ->where(
                        'company_id',
                        $company->id
                    )

                    ->where(
                        'customer_type',
                        'walk_in'
                    )

                    ->where(
                        'is_system_customer',
                        true
                    )

                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Create If Missing
                |--------------------------------------------------------------------------
                */

                if (
                    !$customer
                ) {

                    $customer =
                        Customer::create([

                            'company_id' =>
                            $company->id,

                            'customer_code' =>
                            'WALKIN',

                            'customer_name' =>
                            'Walk-in Customer',

                            'customer_type' =>
                            'walk_in',

                            'is_system_customer' =>
                            true,

                            'contact_person' =>
                            null,

                            'mobile' =>
                            null,

                            'phone' =>
                            null,

                            'email' =>
                            null,

                            'billing_address' =>
                            null,

                            'shipping_address' =>
                            null,

                            'city' =>
                            null,

                            'state' =>
                            null,

                            'country' =>
                            'India',

                            'pincode' =>
                            null,

                            'gst_number' =>
                            null,

                            'pan_number' =>
                            null,

                            'credit_limit' =>
                            0,

                            'credit_days' =>
                            0,

                            'opening_balance' =>
                            0,

                            'balance_type' =>
                            'Dr',

                            'status' =>
                            true,

                            'remarks' =>
                            'System default customer for quick counter sales.',
                        ]);
                }


                /*
                |--------------------------------------------------------------------------
                | Keep System Values Correct
                |--------------------------------------------------------------------------
                */

                $customer->update([

                    'customer_type' =>
                    'walk_in',

                    'is_system_customer' =>
                    true,

                    'status' =>
                    true,
                ]);


                /*
                |--------------------------------------------------------------------------
                | Attach To All Active Branches
                |--------------------------------------------------------------------------
                */

                $storeIds =
                    Store::query()

                    ->where(
                        'company_id',
                        $company->id
                    )

                    ->where(
                        'status',
                        true
                    )

                    ->pluck(
                        'id'
                    )

                    ->map(
                        fn(
                            $id
                        ) =>
                        (int) $id
                    )

                    ->all();


                if (
                    !empty($storeIds)
                ) {

                    $customer
                        ->stores()
                        ->syncWithoutDetaching(
                            $storeIds
                        );
                }


                return $customer
                    ->fresh([
                        'stores',
                    ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Attach Walk-In Customer To New Branch
    |--------------------------------------------------------------------------
    */

    public function attachToStore(
        Company $company,
        Store $store
    ): Customer {

        /*
        |--------------------------------------------------------------------------
        | Safety
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $store->company_id ===
                (int) $company->id,
            422,
            'The branch does not belong to this company.'
        );


        /*
        |--------------------------------------------------------------------------
        | Walk-In Customer
        |--------------------------------------------------------------------------
        */

        $customer =
            $this->getOrCreate(
                $company
            );


        /*
        |--------------------------------------------------------------------------
        | Attach Branch
        |--------------------------------------------------------------------------
        */

        $customer
            ->stores()
            ->syncWithoutDetaching([
                $store->id,
            ]);


        return $customer;
    }


    /*
    |--------------------------------------------------------------------------
    | Get Walk-In Customer
    |--------------------------------------------------------------------------
    */

    public function get(
        int $companyId
    ): ?Customer {

        return Customer::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'customer_type',
                'walk_in'
            )

            ->where(
                'is_system_customer',
                true
            )

            ->where(
                'status',
                true
            )

            ->first();
    }


    /*
    |--------------------------------------------------------------------------
    | Get Walk-In Customer For Branch
    |--------------------------------------------------------------------------
    */

    public function getForStore(
        int $companyId,
        int $storeId
    ): ?Customer {

        return Customer::query()

            ->where(
                'company_id',
                $companyId
            )

            ->where(
                'customer_type',
                'walk_in'
            )

            ->where(
                'is_system_customer',
                true
            )

            ->where(
                'status',
                true
            )

            ->whereHas(
                'stores',
                function (
                    $query
                ) use (
                    $storeId
                ) {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )

            ->first();
    }
}
