<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCodesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('user_codes', function (Blueprint $table) {
      $table->increments('id');
      $table->string('purpose')->nullable(false);
      $table->string('code')->nullable(false);
      $table->integer('rate_limit')->default(0);
      $table->integer('user_id')->unsigned();

      $table->timestamps();

      $table->foreign('user_id')
        ->references('id')->on('users')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('users_codes');
  }
}