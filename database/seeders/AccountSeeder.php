<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'account_code' => 'CASH',
                'account_name' => 'Cash in Hand',
                'account_type' => Account::TYPE_CASH,
                'payment_mode' => 'cash',
            ],
            [
                'account_code' => 'GPAY',
                'account_name' => 'GPay / UPI',
                'account_type' => Account::TYPE_UPI,
                'payment_mode' => 'gpay',
            ],
            [
                'account_code' => 'BANK',
                'account_name' => 'Bank Account',
                'account_type' => Account::TYPE_BANK,
                'payment_mode' => 'bank_transfer',
            ],
            [
                'account_code' => 'CARD',
                'account_name' => 'Card Collections',
                'account_type' => Account::TYPE_CARD,
                'payment_mode' => 'card',
            ],
        ];

        Store::query()
            ->where('status', true)
            ->whereNotNull('company_id')
            ->orderBy('id')
            ->chunkById(100, function ($stores) use ($definitions) {
                foreach ($stores as $store) {
                    DB::transaction(function () use ($store, $definitions) {
                        foreach ($definitions as $definition) {
                            Account::query()->updateOrCreate(
                                [
                                    'company_id' => $store->company_id,
                                    'store_id' => $store->id,
                                    'account_code' => $definition['account_code'],
                                ],
                                [
                                    'account_name' => $definition['account_name'],
                                    'account_type' => $definition['account_type'],
                                    'payment_mode' => $definition['payment_mode'],
                                    'opening_balance' => 0,
                                    'opening_balance_date' => now()->toDateString(),
                                    'is_system' => true,
                                    'status' => true,
                                    'created_by' => null,
                                ]
                            );
                        }
                    });
                }
            });
    }
}
