<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\TransactionPoints;
use Faker\Generator as Faker;

$factory->define(TransactionPoints::class, function (Faker $faker) {
    $transactionId = $faker->unique()->numberBetween($min = 1, $max = intval(config('constants.testing.transactions_number')));
    return [
        'transaction_id' => $transactionId,
        'original' => $faker->numberBetween($min = intval(config('constants.testing.points_min_value')), $max = intval(config('constants.testing.points_max_value'))),
        'created_at' => \DB::connection('mysql_testing')->table('transactions')->find($transactionId)->created_at
    ];
});
