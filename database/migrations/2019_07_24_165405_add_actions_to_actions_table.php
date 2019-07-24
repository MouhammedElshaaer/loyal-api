<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActionsToActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $actions = config('constants.actions');

        foreach($actions as $action){
            \DB::table('actions')->insert(['type' => $action]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('action_logs', function (Blueprint $table) {
            $table->dropForeign('action_logs_action_id_foreign');
        });

        \DB::table('actions')->truncate();

        Schema::table('action_logs', function (Blueprint $table) {
            $table->foreign('action_id')->references('id')->on('actions');
        });
    }
}
