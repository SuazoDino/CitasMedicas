<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paciente_favoritos', function (Blueprint $table) {
            $table->unsignedBigInteger('paciente_id');
            $table->unsignedBigInteger('medico_id');
            $table->timestamps();

            $table->primary(['paciente_id', 'medico_id']);
            $table->foreign('paciente_id')->references('id')->on('pacientes')->cascadeOnDelete();
            $table->foreign('medico_id')->references('id')->on('medicos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_favoritos');
    }
};