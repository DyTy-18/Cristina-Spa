<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear tabla pivote
        Schema::create('cita_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained()->cascadeOnDelete();
            $table->foreignId('servicio_id')->constrained()->restrictOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('precio_unitario', 8, 2)->nullable();
            $table->timestamps();
        });

        // 2. Migrar datos existentes: cada cita con servicio_id → una fila en cita_servicios
        DB::statement('
            INSERT INTO cita_servicios (cita_id, servicio_id, empleado_id, precio_unitario, created_at, updated_at)
            SELECT id, servicio_id, empleado_id, precio_final, created_at, updated_at
            FROM citas
            WHERE servicio_id IS NOT NULL
        ');

        // 3. Quitar columnas de citas
        Schema::table('citas', function (Blueprint $table) {
            $table->dropForeign(['servicio_id']);
            $table->dropForeign(['empleado_id']);
            $table->dropColumn(['servicio_id', 'empleado_id']);
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('servicio_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('empleado_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::dropIfExists('cita_servicios');
    }
};
