<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('users', function (Blueprint $table) {
      $table->increments('id');
      $table->string('username')->unique();
      $table->boolean('force_password_change')->default(false);
      $table->string('password', '512')->nullable(false);
      $table->boolean('activated')->nullable(false);

      $table->string('member_number')->nullable(true)->default(null);

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
      $table->string('bv_member')->nullable(false);
      $table->string('bv_info')->nullable(true);

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
  public function down() {
    Schema::dropIfExists('users');
  }
}
