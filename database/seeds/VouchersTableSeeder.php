<?php

use Illuminate\Database\Seeder;

class VouchersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $connection = 'mysql_testing';
        factory(App\Models\Voucher::class, intval(config('constants.testing.vouchers_number')))
            ->make()
            ->each(function ($model) use($connection) {
                $model->setConnection($connection);
                $model->save();
        });
    }
}
