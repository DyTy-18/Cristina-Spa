<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumos_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_material_id')
                  ->constrained('servicio_materiales')
                  ->cascadeOnDelete();
            $table->foreignId('cita_id')
                  ->constrained('citas')
                  ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumos_material');
    }
};
