<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('events', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name')->nullable(false);
      $table->text('description')->nullable(true);
      $table->string('location')->nullable(true);
      $table->string('startDate')->nullable(false);
      $table->string('endDate')->nullable(false);

      $table->boolean('forEveryone')->nullable(false);

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
    Schema::dropIfExists('events');
  }
}
