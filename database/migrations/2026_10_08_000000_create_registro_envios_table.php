<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Respaldo crudo de todo lo que se envía al sistema (POST/PUT/PATCH/DELETE),
     * independiente de lo que el controlador termine guardando.
     */
    public function up(): void
    {
        Schema::create('registro_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('metodo', 10);
            $table->string('ruta')->nullable()->index();
            $table->string('url', 2048);
            $table->string('ip', 45)->nullable();
            $table->json('datos')->nullable();
            $table->unsignedSmallInteger('status')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_envios');
    }
};
