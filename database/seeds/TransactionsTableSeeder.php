<?php

use Illuminate\Database\Seeder;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $connection = 'mysql_testing';
        factory(App\Models\Transaction::class, intval(config('constants.testing.transactions_number')))
            ->make()
            ->each(function ($model) use($connection) {
                $model->setConnection($connection);
                $model->save();
                \DB::connection($connection)
                    ->table('transaction_points')
                    ->insert([
                        'transaction_id' => $model->id,
                        'original' => intval($model->invoice_value * 4),
                        'created_at' => $model->created_at
                    ]);
        });
    }
}
