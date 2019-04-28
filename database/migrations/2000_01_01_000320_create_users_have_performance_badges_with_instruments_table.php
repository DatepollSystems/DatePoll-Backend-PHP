<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersHavePerformanceBadgesWithInstrumentsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users_have_badges_with_instruments', function (Blueprint $table) {
      $table->increments('id');

      $table->string('grade')->nullable(true);
      $table->date('date')->nullable(true);
      $table->string('note')->nullable(true);

      $table->integer('user_id')->unsigned();;
      $table->foreign('user_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

      $table->integer('performance_badge_id')->unsigned();;
      $table->foreign('performance_badge_id')
        ->references('id')->on('performance_badges')
        ->onDelete('cascade');

      $table->integer('instrument_id')->unsigned();;
      $table->foreign('instrument_id')
        ->references('id')->on('instruments')
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
    Schema::dropIfExists('users_have_badges_with_instruments');
  }
}
