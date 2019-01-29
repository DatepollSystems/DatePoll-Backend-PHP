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

$factory->define(App\User::class, function (Faker\Generator $faker) {
  return [
    'firstname'     => $faker->firstName,
    'surname' => $faker->lastName,
    'email'    => $faker->unique()->email,
    'password' => app('hash')->make('12345'),
    'rank' => 'user'
  ];
});
