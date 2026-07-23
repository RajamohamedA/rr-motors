<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('purchase_returns', function (Blueprint $table) {

            $table->decimal('sub_total', 15, 2)->default(0)->after('return_date');

            $table->decimal('gst_total', 15, 2)->default(0)->after('sub_total');
        });
    }

    public function down()
    {
        Schema::table('purchase_returns', function (Blueprint $table) {

            $table->dropColumn([
                'sub_total',
                'gst_total'
            ]);
        });
    }
};
