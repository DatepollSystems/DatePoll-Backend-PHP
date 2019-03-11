<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoviesBookingsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('movies_bookings', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('user_id')->unsigned();
      $table->integer('movie_id')->unsigned();
      $table->integer('amount');

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

      $table->foreign('movie_id')
        ->references('id')->on('movies')
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
    Schema::dropIfExists('movies_bookings');
  }
}
