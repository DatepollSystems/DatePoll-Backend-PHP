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
  public function up(): void {
    Schema::create('places', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name')->nullable(false);
      $table->string('location')->nullable(true);
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
  public function down(): void {
    Schema::dropIfExists('places');
  }
}
