<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      Model::unguard();
      // Register the user seeder
      factory(App\User::class, 10)->create();
      Model::reguard();
      $user = new \App\User([
        'firstname' => "Franz",
        'surname' => "Huber",
        'email' => 'contact@email.at',
        'password' => app('hash')->make('123456'),
        'rank' => 'admin'
      ]);
      $user->save();
    }
}
