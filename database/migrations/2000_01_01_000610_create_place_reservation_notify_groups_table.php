<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlaceReservationNotifyGroupsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('place_reservation_notify_groups', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('group_id')->unsigned();;
      $table->foreign('group_id')
          ->references('id')->on('groups')
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
    Schema::dropIfExists('place_reservation_notify_groups');
  }
}
