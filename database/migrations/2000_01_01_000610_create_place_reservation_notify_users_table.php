<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaceReservationNotifyUsersTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up(): void {
    Schema::create('place_reservation_notify_users', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('user_id')->unsigned();
      $table->integer('place_id')->unsigned();

      $table->foreign('user_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

      $table->foreign('place_id')
        ->references('id')->on('places')
        ->onDelete('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down(): void {
    Schema::dropIfExists('place_reservation_notify_users');
  }
}
