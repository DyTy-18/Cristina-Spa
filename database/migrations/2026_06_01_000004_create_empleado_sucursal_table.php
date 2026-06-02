<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleado_sucursal', function (Blueprint $table) {
            $table->foreignId('empleado_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->primary(['empleado_id', 'sucursal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado_sucursal');
    }
};
