<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMoviesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('movies', function (Blueprint $table) {
      $table->increments('id');
      $table->string('name')->nullable(false);
      $table->date('date')->nullable(false);
      $table->string('trailerLink', '512')->nullable(true);
      $table->string('posterLink', '512')->nullable(true);

      $table->integer('bookedTickets')->nullable(false)->default(0);

      $table->integer('movie_year_id')->unsigned();
      $table->integer('worker_id')->unsigned()->nullable(true);
      $table->integer('emergency_worker_id')->unsigned()->nullable(true);

      $table->foreign('movie_year_id')
        ->references('id')->on('movie_years')
        ->onDelete('cascade');

      $table->foreign('worker_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

      $table->foreign('emergency_worker_id')
        ->references('id')->on('users')
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
    Schema::dropIfExists('movies');
  }
}
