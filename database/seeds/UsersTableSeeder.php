<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $connection = 'mysql_testing';
        factory(App\User::class, intval(config('constants.testing.users_number')))
            ->make()
            ->each(function ($model) use($connection) {
                $model->setConnection($connection);
                $model->save();
                $model->roles()->attach(2);
        });
    }
}
