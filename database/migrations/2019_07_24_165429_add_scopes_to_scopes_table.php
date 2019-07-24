<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddScopesToScopesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $scopes = config('constants.scopes');

        foreach($scopes as $scope){
            \DB::table('scopes')->insert(['name' => $scope]);
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
            $table->dropForeign('action_logs_scope_id_foreign');
        });

        \DB::table('scopes')->truncate();

        Schema::table('action_logs', function (Blueprint $table) {
            $table->foreign('scope_id')->references('id')->on('scopes');
        });
    }
}
