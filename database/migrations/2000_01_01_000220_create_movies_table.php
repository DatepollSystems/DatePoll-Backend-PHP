<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('movies', function (Blueprint $table) {
      $table->increments('id');
      $table->string('name')->nullable(false);
      $table->date('date')->nullable(false);
      $table->string('trailerLink', '512')->nullable(true);
      $table->string('posterLink', '512')->nullable(true);

      $table->integer('bookedTickets')->nullable(false)->default(0);
      $table->integer('maximalTickets')->nullable(false)->default(20);

      $table->integer('worker_id')->unsigned()->nullable(true);
      $table->integer('emergency_worker_id')->unsigned()->nullable(true);

      $table->foreign('worker_id')
        ->references('id')->on('users');

      $table->foreign('emergency_worker_id')
        ->references('id')->on('users');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('movies');
  }
}
