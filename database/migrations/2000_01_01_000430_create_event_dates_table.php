<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventDatesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('event_dates', function (Blueprint $table) {
      $table->increments('id');

      $table->date('date')->nullable(true);
      $table->string('description')->nullable(true);
      $table->string('location')->nullable(true);

      $table->double('x', 9, 6)->nullable(true);
      $table->double('y', 9, 6)->nullable(true);

      $table->integer('event_id')->unsigned();;
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
  public function down()
  {
    Schema::dropIfExists('event_dates');
  }
}
