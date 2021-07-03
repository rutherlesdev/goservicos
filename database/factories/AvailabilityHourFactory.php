<?php
/*
 * File name: AvailabilityHourFactory.php
 * Last modified: 2021.04.20 at 11:19:32
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */


use App\Models\AvailabilityHour;
use App\Models\EProvider;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(AvailabilityHour::class, function (Faker $faker) {
    return [
        'day' => Str::lower($faker->randomElement(Carbon::getDays())),
        'start_at' => str_pad($faker->numberBetween(2, 12), 2, '0', STR_PAD_LEFT) . ":00",
        'end_at' => $faker->numberBetween(13, 23) . ":00",
        'data' => $faker->text(50),
        'e_provider_id' => EProvider::all()->random()->id
    ];
});

$factory->state(AvailabilityHour::class, 'day_more_16_char', function (Faker $faker) {
    return [
        'day' => $faker->paragraph(3),
    ];
});

$factory->state(AvailabilityHour::class, 'end_at_lest_start_at', function (Faker $faker) {
    return [
        'start_at' => $faker->numberBetween(16, 21) . ":20",
        'end_at' => $faker->numberBetween(10, 13) . ":30",
    ];
});

$factory->state(AvailabilityHour::class, 'not_exist_e_provider_id', function (Faker $faker) {
    return [
        'e_provider_id' => 500000, // not exist id
    ];
});
