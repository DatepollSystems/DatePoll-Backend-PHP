<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('broadcasts', function (Blueprint $table) {
      $table->increments('id');

      $table->string('subject')->nullable(false);
      $table->longText('bodyHTML')->nullable(false);
      $table->longText('body')->nullable(false);

      $table->integer('writer_user_id')->unsigned();
      $table->boolean('forEveryone')->nullable(false);

      $table->timestamps();

      $table->foreign('writer_user_id')
        ->references('id')->on('users');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('broadcasts');
  }
}
