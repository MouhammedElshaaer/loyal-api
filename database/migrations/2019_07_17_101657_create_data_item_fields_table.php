<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataItemFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_item_fields', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('data_item_id');
            $table->foreign('data_item_id')->references('id')->on('data_items');

            $table->text('name');

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
        Schema::dropIfExists('data_item_fields');
    }
}
