<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Voucher;
use Faker\Generator as Faker;

$factory->define(Voucher::class, function (Faker $faker) {
    return [
        'points' => $faker->numberBetween($min = intval(config('constants.testing.voucher_min_points')), $max = intval(config('constants.testing.voucher_max_points'))),
        'title' => $faker->numberBetween($min=intval(config('constants.testing.min_discount')), $max=intval(config('constants.testing.max_discount')) ) .'% Discount on you next invoice',
        'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
        'image' => 'image',
        'instances' => $faker->numberBetween($min=0, $max=intval(config('constants.testing.voucher_instances_number'))),
    ];
});
