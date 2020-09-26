<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersChangesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users_changes', function (Blueprint $table) {
      $table->increments('id');

      $table->string('property');
      $table->text('old_value')->nullable(true);
      $table->text('new_value')->nullable(true);

      $table->integer('user_id')->unsigned();
      $table->foreign('user_id')
            ->references('id')->on('users')
            ->onDelete('cascade');

      $table->integer('editor_id')->unsigned();
      $table->foreign('editor_id')
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
    Schema::dropIfExists('users_changes');
  }
}
