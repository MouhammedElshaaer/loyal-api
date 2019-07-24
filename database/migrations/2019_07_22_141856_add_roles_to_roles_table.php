<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRolesToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attributesArray = [
            ['name' => "admin"],
            ['name' => "customer"],
            ['name' => "cashier"],
            ['name' => "premium"]
        ];

        foreach($attributesArray as $attributes){
            \DB::table('roles')->insert($attributes);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::table('roles')->truncate();
    }
}
