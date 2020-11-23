<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTokensTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('user_tokens', function (Blueprint $table) {
      $table->increments('id');
      $table->string('token')->nullable(false);

      $table->string('purpose')->nullable(false);
      $table->string('description')->nullable(true);

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
  public function down() {
    Schema::dropIfExists('user_tokens');
  }
}
