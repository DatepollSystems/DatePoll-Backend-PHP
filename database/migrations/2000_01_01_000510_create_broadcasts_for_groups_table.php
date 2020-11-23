<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastsForGroupsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('broadcasts_for_groups', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('broadcast_id')->unsigned();
      $table->foreign('broadcast_id')
        ->references('id')->on('broadcasts')
        ->onDelete('cascade');

      $table->integer('group_id')->unsigned();
      ;
      $table->foreign('group_id')
        ->references('id')->on('groups')
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
    Schema::dropIfExists('broadcasts_for_groups');
  }
}
