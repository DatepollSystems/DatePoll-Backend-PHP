<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlacesReservationsByUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('places_reservations_by_users', function (Blueprint $table) {
      $table->increments('id');

      $table->string('reason')->nullable(false);
      $table->string('description')->nullable(true);
      $table->date('start_date')->nullable(false);
      $table->date('end_date')->nullable(false);
      $table->string('state')->nullable(false)->default('WAITING');

      $table->integer('place_id')->unsigned();
      $table->foreign('place_id')
          ->references('id')->on('places')->onDelete('cascade');

      $table->integer('user_id')->unsigned()->nullable(true);
      $table->foreign('user_id')
          ->references('id')->on('users');

      $table->integer('approver_id')->unsigned()->nullable(true);
      $table->foreign('approver_id')
          ->references('id')->on('users');

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
    Schema::dropIfExists('places_reservations_by_users');
  }
}
