<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('data_row_id');
            $table->text('data_type');

            $table->unsignedBigInteger('scope_id');
            $table->foreign('scope_id')->references('id')->on('scopes');

            $table->unsignedBigInteger('action_id');
            $table->foreign('action_id')->references('id')->on('actions');

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
        Schema::dropIfExists('action_logs');
    }
}
