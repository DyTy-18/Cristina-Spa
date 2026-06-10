<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_sueldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('tipo')->default('sueldo'); // sueldo, comision, adelanto, bono
            $table->decimal('monto', 10, 2);
            $table->date('periodo_desde')->nullable();
            $table->date('periodo_hasta')->nullable();
            $table->date('fecha_pago');
            $table->string('metodo_pago')->nullable(); // efectivo, transferencia
            $table->text('notas')->nullable();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_sueldos');
    }
};
