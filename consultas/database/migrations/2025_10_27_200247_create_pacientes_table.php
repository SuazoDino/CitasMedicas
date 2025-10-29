
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('doc_tipo', 20)->nullable();   // DNI/CE/PAS (opcional)
            $table->string('doc_numero', 30)->nullable();
            $table->date('birthdate')->nullable();
            $table->enum('gender', ['M','F','X'])->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('pacientes');
    }
};
