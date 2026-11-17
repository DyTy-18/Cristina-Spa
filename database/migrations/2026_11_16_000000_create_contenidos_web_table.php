<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Contenido editable de la página pública (una fila por sección, ej. "servicios")
        Schema::create('contenidos_web', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->json('valor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contenidos_web');
    }
};
