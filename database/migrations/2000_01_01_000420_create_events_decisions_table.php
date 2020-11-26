<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsDecisionsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('events_decisions', function (Blueprint $table) {
      $table->increments('id');

      $table->string('decision')->nullable(false);
      $table->boolean('showInCalendar')->nullable(false);
      $table->string('color', 7)->nullable(false);

      $table->integer('event_id')->unsigned();
      ;
      $table->foreign('event_id')
        ->references('id')->on('events')
        ->onDelete('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('events_decisions');
  }
}
