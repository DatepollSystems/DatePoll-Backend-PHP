<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsForSubgroupsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('events_for_subgroups', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('event_id')->unsigned();;
      $table->foreign('event_id')
        ->references('id')->on('events')
        ->onDelete('cascade');

      $table->integer('subgroup_id')->unsigned();;
      $table->foreign('subgroup_id')
        ->references('id')->on('subgroups')
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
    Schema::dropIfExists('events_for_subgroups');
  }
}
