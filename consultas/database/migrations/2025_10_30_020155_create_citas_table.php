<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('citas', function (Blueprint $t) {
      $t->bigIncrements('id');
      $t->unsignedBigInteger('medico_id');
      $t->unsignedBigInteger('paciente_id');
      $t->unsignedBigInteger('especialidad_id');
      $t->dateTime('starts_at');
      $t->dateTime('ends_at');
      $t->enum('estado', ['pendiente','confirmada','cancelada','completada','no_asistio'])
        ->default('pendiente');
      $t->string('motivo', 140)->nullable();
      $t->text('notas')->nullable();
      $t->unsignedBigInteger('created_by_user_id');
      $t->unsignedBigInteger('canceled_by_user_id')->nullable();
      $t->string('cancel_reason', 180)->nullable();
      $t->timestamps();

      $t->foreign('medico_id')->references('id')->on('medicos')->cascadeOnDelete();
      $t->foreign('paciente_id')->references('id')->on('pacientes')->cascadeOnDelete();
      $t->foreign('especialidad_id')->references('id')->on('especialidades');
      $t->foreign('created_by_user_id')->references('id')->on('users');
      $t->foreign('canceled_by_user_id')->references('id')->on('users');

      $t->index(['medico_id','starts_at']);
      $t->unique(['medico_id','starts_at'], 'uq_medico_slot'); // 1 cita por médico y horario
    });
  }
  public function down(): void {
    Schema::dropIfExists('citas');
  }
};
