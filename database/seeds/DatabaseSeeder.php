<?php

use App\Models\User\User;
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
      factory(User::class, 400)->create();
      Model::reguard();
    }
}
