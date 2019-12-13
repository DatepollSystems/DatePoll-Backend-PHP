<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsUsersVotedForTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('events_users_voted_for', function (Blueprint $table) {
      $table->increments('id');

      $table->string('additionalInformation', 128)->nullable(true);

      $table->integer('event_id')->unsigned();;
      $table->foreign('event_id')
        ->references('id')->on('events')
        ->onDelete('cascade');

      $table->integer('user_id')->unsigned();;
      $table->foreign('user_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

      $table->integer('decision_id')->unsigned();;
      $table->foreign('decision_id')
        ->references('id')->on('events_decisions')
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
    Schema::dropIfExists('events_users_voted_for');
  }
}
