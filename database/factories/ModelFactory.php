<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use App\Models\User\User;

$factory->define(User::class, function (Faker\Generator $faker) {
  return [
    'title' => $faker->jobTitle,
    'firstname'     => $faker->firstName,
    'surname' => $faker->lastName,
    'email'    => $faker->unique()->email,
    'password' => app('hash')->make('12345'),
    'activated' => false,
    'streetname' => $faker->streetName,
    'streetnumber' => $faker->numberBetween(1, 60),
    'zipcode' => $faker->numberBetween(1000, 9999),
    'location' => $faker->city,
    'birthday' => date('Y-m-d'),
    'join_date' => date('Y-m-d'),
    'activity' => 'aktiv'
  ];
});
