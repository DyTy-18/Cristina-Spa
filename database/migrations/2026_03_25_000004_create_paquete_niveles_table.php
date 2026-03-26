<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquete_niveles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->integer('orden')->default(0);
            $table->string('color', 7)->default('#c9a96e');   // hex
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Seed por defecto
        DB::table('paquete_niveles')->insert([
            ['nombre' => 'Esmeralda', 'orden' => 1, 'color' => '#50C878', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Rubí',      'orden' => 2, 'color' => '#9B111E', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Diamante',  'orden' => 3, 'color' => '#5B8DD9', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('paquete_niveles');
    }
};
