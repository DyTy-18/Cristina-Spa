<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_barras');
            $table->foreign('codigo_barras')->references('codigo_barras')->on('productos')->onDelete('cascade');
            $table->integer('unidades');
            $table->date('fecha');
            $table->string('destino')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salidas');
    }
};
