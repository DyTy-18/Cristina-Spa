<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marca los movimientos que son transferencias entre técnico y reventa (una salida en
 * un pool + una entrada en el otro), para no contarlos como compras ni consumos.
 *
 * Backfill: las salidas se reconocen por su destino; cada una se empareja con la entrada
 * del otro pool del mismo producto, unidades y fecha, creada en el mismo momento.
 */
return new class extends Migration
{
    private const DESTINOS = [
        'Transferencia a reventa' => 'reventa',
        'Transferencia a técnico' => 'tecnico',
    ];

    public function up(): void
    {
        foreach (['entradas', 'salidas'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->boolean('es_transferencia')->default(false)->after('tipo_stock');
            });
        }

        $salidas = DB::table('salidas')->whereIn('destino', array_keys(self::DESTINOS))->orderBy('id')->get();

        foreach ($salidas as $salida) {
            DB::table('salidas')->where('id', $salida->id)->update(['es_transferencia' => true]);

            $entradaId = DB::table('entradas')
                ->where('producto_id', $salida->producto_id)
                ->where('tipo_stock', self::DESTINOS[$salida->destino])
                ->where('unidades', $salida->unidades)
                ->where('fecha', $salida->fecha)
                ->where('es_transferencia', false)
                ->when($salida->created_at, fn ($q) => $q->whereBetween('created_at', [
                    date('Y-m-d H:i:s', strtotime($salida->created_at) - 60),
                    date('Y-m-d H:i:s', strtotime($salida->created_at) + 60),
                ]))
                ->orderBy('id')
                ->value('id');

            if ($entradaId) {
                DB::table('entradas')->where('id', $entradaId)->update(['es_transferencia' => true]);
            }
        }
    }

    public function down(): void
    {
        foreach (['entradas', 'salidas'] as $tabla) {
            Schema::table($tabla, fn (Blueprint $table) => $table->dropColumn('es_transferencia'));
        }
    }
};
