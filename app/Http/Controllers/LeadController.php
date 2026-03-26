<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'   => 'required|string|max:150',
            'telefono' => 'required|string|max:30',
            'sucursal' => 'nullable|string|max:100',
            'servicio' => 'nullable|string|max:150',
            'mensaje'  => 'nullable|string|max:1000',
        ]);

        $ip = $request->ip();
        $data['ip_address'] = $ip;

        // Resolver país a partir de la IP (IPs locales se ignoran)
        if ($ip && ! in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            try {
                $geo = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'country,countryCode',
                        'lang'   => 'es',
                    ])
                    ->json();

                $data['pais']       = $geo['country']     ?? null;
                $data['pais_codigo'] = $geo['countryCode'] ?? null;
            } catch (\Throwable) {
                // No bloquear el lead si la API falla
            }
        }

        Lead::create($data);

        return response()->json(['ok' => true]);
    }
}
