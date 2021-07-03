<?php
/*
 * File name: AddressFactory.php
 * Last modified: 2021.04.20 at 11:19:32
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */


use App\Models\Address;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(Address::class, function (Faker $faker) {
    return [
        'description' => $faker->randomElement(['Work', 'Home', 'Office', 'Workshop', 'Building', 'Hotel']),
        'address' => $faker->address,
        'latitude' => $faker->randomFloat(8, 50, 52),
        'longitude' => $faker->randomFloat(8, 9, 12),
        'user_id' => User::all()->random()->id
    ];
});

$factory->state(Address::class, 'more_255_char', function (Faker $faker) {
    return [
        'description' => $faker->paragraph(30),
        'address' => $faker->paragraph(30),
        'latitude' => 210,
        'longitude' => -203,
    ];
});

$factory->state(Address::class, 'empty', function (Faker $faker) {
    return [
        'address' => null,
        'latitude' => null,
        'longitude' => null,
    ];
});
