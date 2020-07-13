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
      $table->string('username')->unique();
      $table->boolean('force_password_change')->default(false);
      $table->string('password', '512')->nullable(false);
      $table->boolean('activated')->nullable(false);

      $table->integer('member_number')->default(null);

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
      $table->boolean('bv_member')->default(false);

      $table->text('internal_comment')->nullable(true);
      $table->boolean('information_denied')->default(false);

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
