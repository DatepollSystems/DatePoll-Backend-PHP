<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('logs', function (Blueprint $table) {
      $table->increments('id');

      $table->string('type')->nullable(false);
      $table->text('message')->nullable(true);

      $table->integer('user_id')->nullable(true)->unsigned();
      $table->foreign('user_id')
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
  public function down() {
    Schema::dropIfExists('logs');
  }
}
