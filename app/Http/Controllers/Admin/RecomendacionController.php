<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\RecomendacionLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecomendacionController extends Controller
{
    /**
     * Genera (o devuelve el existente) enlace de recomendación para un cliente.
     */
    public function generar(Cliente $cliente)
    {
        // Reutilizar link activo si ya existe
        $link = $cliente->recomendacionLinks()
            ->where('activo', true)
            ->latest()
            ->first();

        if (! $link) {
            $link = RecomendacionLink::create([
                'cliente_id' => $cliente->id,
                'token'      => Str::random(32),
                'clicks'     => 0,
                'activo'     => true,
            ]);
        }

        return response()->json([
            'url'          => $link->url,
            'whatsapp_url' => $link->whatsapp_url,
            'clicks'       => $link->clicks,
            'convertidos'  => $link->leadsConvertidos()->count(),
        ]);
    }

    /**
     * Desactiva un link de recomendación.
     */
    public function destroy(RecomendacionLink $link)
    {
        abort_if($link->cliente_id !== request()->route('cliente')->id, 403);
        $link->update(['activo' => false]);

        return response()->json(['ok' => true]);
    }
}
