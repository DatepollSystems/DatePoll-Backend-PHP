<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersMemberOfGroupsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('users_member_of_groups', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('group_id')->unsigned();
      ;
      $table->foreign('group_id')
        ->references('id')->on('groups')
        ->onDelete('cascade');

      $table->integer('user_id')->unsigned();
      ;
      $table->foreign('user_id')
        ->references('id')->on('users')
        ->onDelete('cascade');

      $table->string('role')->nullable(true);

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('users_member_of_groups');
  }
}
