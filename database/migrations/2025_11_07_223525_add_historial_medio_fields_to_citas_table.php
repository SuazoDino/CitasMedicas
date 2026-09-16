<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            // Campos para historial médico detallado
            $table->text('sintomas')->nullable()->after('notas')->comment('Síntomas reportados por el paciente');
            $table->text('diagnostico')->nullable()->after('sintomas')->comment('Diagnóstico del médico');
            $table->text('tratamiento')->nullable()->after('diagnostico')->comment('Tratamiento prescrito');
            $table->text('observaciones_medicas')->nullable()->after('tratamiento')->comment('Observaciones adicionales del médico');
            $table->timestamp('historial_completado_at')->nullable()->after('observaciones_medicas')->comment('Fecha en que se completó el historial médico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropColumn([
                'sintomas',
                'diagnostico',
                'tratamiento',
                'observaciones_medicas',
                'historial_completado_at'
            ]);
        });
    }
};
