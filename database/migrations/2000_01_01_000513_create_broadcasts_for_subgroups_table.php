<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastsForSubgroupsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('broadcasts_for_subgroups', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('broadcast_id')->unsigned();
      ;
      $table->foreign('broadcast_id')
        ->references('id')->on('broadcasts')
        ->onDelete('cascade');

      $table->integer('subgroup_id')->unsigned();
      ;
      $table->foreign('subgroup_id')
        ->references('id')->on('subgroups')
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
    Schema::dropIfExists('broadcasts_for_subgroups');
  }
}
