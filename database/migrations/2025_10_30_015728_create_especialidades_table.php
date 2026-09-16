<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('especialidades', function (Blueprint $t) {
      $t->bigIncrements('id');
      $t->string('nombre', 120)->unique();
      $t->string('slug', 140)->unique();
      $t->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('especialidades');
  }
};
