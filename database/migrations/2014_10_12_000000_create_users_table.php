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
      $table->boolean('email_verified')->default(false);
      $table->string('email_verify_token', 6)->nullable(true);
      $table->string('password', '512')->nullable(false);
      $table->string('rank')->nullable(false);

      $table->string('firstname')->nullable(false);
      $table->string('surname')->nullable(false);


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
