<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE entradas MODIFY fecha DATE NULL');
        DB::statement('ALTER TABLE salidas MODIFY fecha DATE NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE entradas SET fecha = '1970-01-01' WHERE fecha IS NULL");
        DB::statement("UPDATE salidas SET fecha = '1970-01-01' WHERE fecha IS NULL");
        DB::statement('ALTER TABLE entradas MODIFY fecha DATE NOT NULL');
        DB::statement('ALTER TABLE salidas MODIFY fecha DATE NOT NULL');
    }
};
