<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paquetes', function (Blueprint $table) {
            $table->decimal('precio_fijo', 10, 2)->nullable()->after('descuento_general');
        });

        // Renombrar nivel "Esmeralda" → "Express"
        DB::table('paquete_niveles')->where('nombre', 'Esmeralda')->update(['nombre' => 'Express']);
    }

    public function down(): void
    {
        Schema::table('paquetes', function (Blueprint $table) {
            $table->dropColumn('precio_fijo');
        });

        DB::table('paquete_niveles')->where('nombre', 'Express')->update(['nombre' => 'Esmeralda']);
    }
};
