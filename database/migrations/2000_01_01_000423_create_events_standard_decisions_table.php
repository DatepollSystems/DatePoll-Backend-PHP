<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsStandardDecisionsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('events_standard_decisions', function (Blueprint $table) {
      $table->increments('id');

      $table->string('decision')->nullable(false);
      $table->string('color', 7)->nullable(false);
      $table->boolean('showInCalendar')->nullable(false);

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
    Schema::dropIfExists('events_standard_decisions');
  }
}
