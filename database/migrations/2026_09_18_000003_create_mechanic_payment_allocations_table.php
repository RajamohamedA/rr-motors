<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'mechanic_payment_allocations',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('company_id')
                    ->constrained('companies')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreignId('store_id')
                    ->constrained('stores')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->foreignId('mechanic_payment_id')
                    ->constrained('mechanic_payments')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('mechanic_commission_id')
                    ->constrained('mechanic_commissions')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->decimal(
                    'allocated_amount',
                    15,
                    2
                );

                $table->timestamps();

                $table->unique(
                    [
                        'mechanic_payment_id',
                        'mechanic_commission_id',
                    ],
                    'mechanic_payment_commission_unique'
                );

                $table->index(
                    [
                        'company_id',
                        'store_id',
                        'mechanic_commission_id',
                    ],
                    'mechanic_allocation_context_commission_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'mechanic_payment_allocations'
        );
    }
};
