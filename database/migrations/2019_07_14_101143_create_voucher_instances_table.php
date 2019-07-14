<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVoucherInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher_instances', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->unsignedInteger('voucher_id');
            $table->unsignedInteger('user_id');

            $table->boolean('used')->default(0);

            $table->unsignedBigInteger('qr_code');

            $table->boolean('deactivated')->default(0);
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
        Schema::dropIfExists('voucher_instances');
    }
}
