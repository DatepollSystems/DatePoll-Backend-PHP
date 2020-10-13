<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBroadcastAttachmentsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('broadcast_attachments', function (Blueprint $table) {
      $table->increments('id');

      $table->string('path')->nullable(false);
      $table->string('name')->nullable(false);
      $table->string('token')->nullable(false);

      $table->integer('broadcast_id')->unsigned()->nullable(true);
      $table->foreign('broadcast_id')
        ->references('id')->on('broadcasts')->onDelete('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('broadcast_attachments');
  }
}
