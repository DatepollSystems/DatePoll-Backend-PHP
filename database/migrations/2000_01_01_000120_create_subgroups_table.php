<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubgroupsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('subgroups', function (Blueprint $table) {
      $table->increments('id');

      $table->string('name');
      $table->integer('orderN')->nullable(false)->default(0);
      $table->text('description')->nullable(true);

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
    Schema::dropIfExists('subgroups');
  }
}
