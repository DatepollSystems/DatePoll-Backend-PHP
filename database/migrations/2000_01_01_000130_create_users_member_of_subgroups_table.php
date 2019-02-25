<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersMemberOfSubgroupsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users_member_of_subgroups', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('subgroup_role_id')->nullable(true)->unsigned();;
      $table->foreign('subgroup_role_id')
        ->references('id')->on('subgroup_roles')
        ->onDelete('cascade');

      $table->integer('subgroup_id')->unsigned();;
      $table->foreign('subgroup_id')
        ->references('id')->on('subgroups')
        ->onDelete('cascade');

      $table->integer('user_id')->unsigned();;
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
  public function down()
  {
    Schema::dropIfExists('users_member_of_subgroups');
  }
}
