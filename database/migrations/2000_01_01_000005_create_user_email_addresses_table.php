<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserEmailAddressesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('user_email_addresses', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('user_id')->unsigned();
      $table->string('email')->nullable(false);

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
    Schema::dropIfExists('user_email_addresses');
  }
}