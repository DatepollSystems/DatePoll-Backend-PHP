<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersHavePerformanceBadgesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users_have_performance_badges', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('performance_badge_id')->unsigned();;
      $table->foreign('performance_badge_id')
        ->references('id')->on('performance_badges')
        ->onDelete('cascade');

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
    Schema::dropIfExists('users_have_performance_badges');
  }
}
