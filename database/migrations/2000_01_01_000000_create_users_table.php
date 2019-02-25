<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users', function (Blueprint $table) {
      $table->increments('id');
      $table->string('email')->unique();
      $table->boolean('force_password_change')->default(false);
      $table->string('password', '512')->nullable(false);
      $table->boolean('activated')->nullable(false);

      $table->string('title')->nullable(true);
      $table->string('firstname')->nullable(false);
      $table->string('surname')->nullable(false);

      $table->date('birthday')->nullable(false);
      $table->date('join_date')->nullable(true);

      $table->string('streetname')->nullable(false);
      $table->string('streetnumber')->nullable(false);
      $table->integer('zipcode')->nullable(false);
      $table->string('location')->nullable(false);

      $table->string('activity')->nullable(false);

      $table->string('remember_token', 512)->nullable(true);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('users');
  }
}
