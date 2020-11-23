<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacesTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('places', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name')->nullable(false);
      $table->double('x', 9, 6)->nullable(true);
      $table->double('y', 9, 6)->nullable(true);

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('places');
  }
}
