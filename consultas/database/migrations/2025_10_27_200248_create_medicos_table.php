<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();

            // Identidad
            $table->string('id_doc_tipo', 20);      // DNI/CE/PAS
            $table->string('id_doc_numero', 30);
            $table->string('id_doc_file', 255)->nullable();

            // Licencia/Cédula
            $table->string('lic_tipo', 40);         // CMP / Cédula
            $table->string('lic_numero', 40);
            $table->string('lic_pais', 2);          // ISO-2
            $table->string('lic_file', 255)->nullable();

            // Política B (provisional limitado)
            $table->enum('verif_status', ['provisional','verificado','rechazado','sospechoso'])
                  ->default('provisional');
            $table->string('verif_notas', 255)->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->boolean('is_searchable')->default(false);
            $table->dateTime('provisional_expires_at')->nullable();
            $table->unsignedSmallInteger('provisional_max_citas')->default(5);
            $table->string('invite_code', 32)->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['is_searchable', 'verif_status'], 'idx_med_verif');
            $table->index(['lic_pais', 'lic_tipo', 'lic_numero'], 'idx_med_lic');
        });
    }
    public function down(): void {
        Schema::dropIfExists('medicos');
    }
};
