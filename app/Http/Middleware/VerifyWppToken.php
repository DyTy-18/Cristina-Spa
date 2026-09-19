<?php

namespace App\Http\Middleware;

use App\Models\WppSyncLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autentica las llamadas del servidor WPP a la API de sincronización (routes/api.php)
 * comparando el Bearer token contra WPPCONNECT_TOKEN (config('services.wpp.token')).
 */
class VerifyWppToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.wpp.token');

        if (empty($token) || !hash_equals($token, (string) $request->bearerToken())) {
            WppSyncLog::create([
                'direccion' => 'entrante',
                'tipo'      => 'auth_rechazado',
                'resultado' => 'error',
                'status_code' => 401,
                'mensaje'   => 'Token faltante o inválido para ' . $request->method() . ' ' . $request->path(),
            ]);

            return response()->json(['message' => 'No autorizado.'], 401);
        }

        return $next($request);
    }
}
