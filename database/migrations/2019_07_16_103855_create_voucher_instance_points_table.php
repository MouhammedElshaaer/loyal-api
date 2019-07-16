<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherInstancePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_instance_points', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('voucher_instance_id');
            $table->foreign('voucher_instance_id')->references('id')->on('voucher_instances');

            $table->unsignedBigInteger('transaction_points_id');
            $table->foreign('transaction_points_id')->references('id')->on('transaction_points');

            $table->unsignedBigInteger('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('voucher_instance_points');
    }
}
