<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wpp_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('direccion', ['entrante', 'saliente']);
            $table->string('tipo', 40);
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->enum('resultado', ['ok', 'error']);
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->text('mensaje')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wpp_sync_logs');
    }
};
