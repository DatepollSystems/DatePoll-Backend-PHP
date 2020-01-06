<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsStandardLocationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('events_standard_locations', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name')->nullable(false);

      $table->string('location')->nullable(true);

      $table->double('x', 8, 6)->nullable(true);
      $table->double('y', 8, 6)->nullable(true);

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
    Schema::dropIfExists('events_standard_locations');
  }
}
