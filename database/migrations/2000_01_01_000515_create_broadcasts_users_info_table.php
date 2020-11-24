<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastsUsersInfoTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('broadcasts_users_info', function (Blueprint $table) {
      $table->increments('id');

      $table->boolean('sent')->nullable(false)->default(false);

      $table->integer('broadcast_id')->unsigned();
      $table->foreign('broadcast_id')
        ->references('id')->on('broadcasts')
        ->onDelete('cascade');

      $table->integer('user_id')->unsigned();
      ;
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
    Schema::dropIfExists('broadcasts_users_info');
  }
}
