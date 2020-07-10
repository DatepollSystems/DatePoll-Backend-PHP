<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBroadcastDraftsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('broadcast_drafts', function (Blueprint $table) {
      $table->increments('id');

      $table->string('subject')->nullable(false);
      $table->longText('bodyHTML')->nullable(false);
      $table->longText('body')->nullable(false);

      $table->integer('writer_user_id')->unsigned();

      $table->timestamps();

      $table->foreign('writer_user_id')
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
    Schema::dropIfExists('broadcast_drafts');
  }
}
