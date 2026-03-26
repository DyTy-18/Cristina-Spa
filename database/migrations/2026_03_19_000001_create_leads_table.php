<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono', 30);
            $table->string('sucursal', 100)->nullable();
            $table->string('servicio', 150)->nullable();
            $table->text('mensaje')->nullable();
            $table->enum('estado', ['nuevo', 'contactado', 'convertido', 'perdido'])->default('nuevo');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
