<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeletedUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users_deleted', function (Blueprint $table) {
      $table->increments('id');
      $table->string('firstname')->nullable(false);
      $table->string('surname')->nullable(false);
      $table->date('join_date')->nullable(true);

      $table->text('internal_comment')->nullable(true);

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
    Schema::dropIfExists('users_deleted');
  }
}
