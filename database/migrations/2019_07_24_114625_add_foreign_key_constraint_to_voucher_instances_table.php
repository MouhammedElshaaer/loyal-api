<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyConstraintToVoucherInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('voucher_instances', function (Blueprint $table) {

            $table->foreign('transaction_id')->references('id')->on('transactions');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('voucher_instances', function (Blueprint $table) {

            $table->dropForeign('voucher_instances_transaction_id_foreign');

        });
    }
}
