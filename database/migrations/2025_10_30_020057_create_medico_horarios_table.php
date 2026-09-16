<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('medico_horarios', function (Blueprint $t) {
      $t->bigIncrements('id');
      $t->unsignedBigInteger('medico_id');
      $t->tinyInteger('dia_semana');          // 0=Dom .. 6=Sab
      $t->time('hora_inicio');                // ej. 09:00:00
      $t->time('hora_fin');                   // ej. 17:00:00
      $t->smallInteger('slot_min')->default(30);
      $t->boolean('activo')->default(true);
      $t->timestamps();

      $t->foreign('medico_id')->references('id')->on('medicos')->cascadeOnDelete();
      $t->unique(['medico_id','dia_semana','hora_inicio','hora_fin'], 'uq_medico_dia_fr');
    });
  }
  public function down(): void {
    Schema::dropIfExists('medico_horarios');
  }
};
