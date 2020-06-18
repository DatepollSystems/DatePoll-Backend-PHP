<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersHasBadgesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users_has_badges', function (Blueprint $table) {
      $table->increments('id');

      $table->string('description')->nullable(false);
      $table->date('getDate')->nullable(true);
      $table->string('reason')->nullable(true);

      $table->integer('user_id')->unsigned();;
      $table->foreign('user_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

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
    Schema::dropIfExists('users_has_badges');
  }
}
