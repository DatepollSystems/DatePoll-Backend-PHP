<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupPermissionsTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up(): void {
    Schema::create('group_permissions', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('group_id')->unsigned();

      $table->string('permission')->nullable(false);

      $table->timestamps();

      $table->foreign('group_id')
        ->references('id')->on('groups')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down(): void {
    Schema::dropIfExists('group_permissions');
  }
}
