<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('medico_especialidad', function (Blueprint $t) {
      $t->unsignedBigInteger('medico_id');
      $t->unsignedBigInteger('especialidad_id');
      $t->primary(['medico_id','especialidad_id']);
      $t->foreign('medico_id')->references('id')->on('medicos')->cascadeOnDelete();
      $t->foreign('especialidad_id')->references('id')->on('especialidades')->cascadeOnDelete();
    });
  }
  public function down(): void {
    Schema::dropIfExists('medico_especialidad');
  }
};
