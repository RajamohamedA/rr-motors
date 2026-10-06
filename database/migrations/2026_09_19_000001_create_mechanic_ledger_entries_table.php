<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mechanic_ledger_entries', function (Blueprint $table): void {
            $table->id();

            /*
            |------------------------------------------------------------------
            | Company and Branch Context
            |------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('mechanic_id')
                ->constrained('mechanics')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |------------------------------------------------------------------
            | Ledger Transaction
            |------------------------------------------------------------------
            */

            $table->date('transaction_date');
            $table->string('transaction_type', 40);

            /*
            | Polymorphic source without a database foreign key.
            |
            | Examples:
            | App\Models\MechanicCommission
            | App\Models\MechanicPayment
            */

            $table->string('reference_type', 150)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no', 100)->nullable();

            /*
            | Credit increases commission payable to the mechanic.
            | Debit reduces commission payable when payment is made.
            */

            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);

            /*
            |------------------------------------------------------------------
            | Reversal Audit Trail
            |------------------------------------------------------------------
            */

            $table->boolean('is_reversal')->default(false);
            $table->foreignId('reversal_of_id')
                ->nullable()
                ->constrained('mechanic_ledger_entries')
                ->nullOnDelete();

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |------------------------------------------------------------------
            | Reporting and Lookup Indexes
            |------------------------------------------------------------------
            */

            $table->index(
                ['company_id', 'store_id', 'transaction_date'],
                'mechanic_ledger_context_date_index'
            );

            $table->index(
                ['mechanic_id', 'transaction_date', 'id'],
                'mechanic_ledger_mechanic_date_index'
            );

            $table->index(
                ['reference_type', 'reference_id'],
                'mechanic_ledger_reference_index'
            );

            $table->index(
                ['transaction_type', 'transaction_date'],
                'mechanic_ledger_type_date_index'
            );

            $table->index(
                ['reversal_of_id', 'is_reversal'],
                'mechanic_ledger_reversal_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mechanic_ledger_entries');
    }
};
