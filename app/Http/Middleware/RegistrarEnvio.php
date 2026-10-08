<?php

namespace App\Http\Middleware;

use App\Models\RegistroEnvio;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guarda una copia de todo lo enviado en formularios/API ANTES de que el
 * controlador lo procese, para poder reconstruir datos que no se llegaron a
 * guardar por un error de validación, de lógica o una excepción.
 */
class RegistrarEnvio
{
    /** Campos que nunca se respaldan (se guardan como "***"). */
    private const CAMPOS_OCULTOS = [
        'password', 'password_confirmation', 'current_password', 'new_password',
        '_token', 'token', 'secret',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        $registro = $this->crearRegistro($request);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            $this->actualizar($registro, 500, $e->getMessage());
            throw $e;
        }

        $this->actualizar($registro, $response->getStatusCode(), $response->exception?->getMessage());

        return $response;
    }

    private function crearRegistro(Request $request): ?RegistroEnvio
    {
        // El respaldo nunca debe romper la operación real
        try {
            return RegistroEnvio::create([
                'user_id' => $request->user()?->id,
                'metodo'  => $request->method(),
                'ruta'    => $request->route()?->getName(),
                'url'     => mb_substr($request->fullUrl(), 0, 2048),
                'ip'      => $request->ip(),
                'datos'   => $this->limpiar(array_merge($request->all(), $request->allFiles())),
            ]);
        } catch (\Throwable $e) {
            Log::warning('RegistrarEnvio: no se pudo respaldar el envío: ' . $e->getMessage());
            return null;
        }
    }

    private function actualizar(?RegistroEnvio $registro, int $status, ?string $error): void
    {
        if (!$registro) {
            return;
        }

        try {
            $registro->update([
                'status' => $status,
                'error'  => $error ? mb_substr($error, 0, 5000) : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('RegistrarEnvio: no se pudo actualizar el estado: ' . $e->getMessage());
        }
    }

    private function limpiar(array $datos): array
    {
        foreach ($datos as $clave => $valor) {
            if (in_array(strtolower((string) $clave), self::CAMPOS_OCULTOS, true)) {
                $datos[$clave] = '***';
            } elseif ($valor instanceof UploadedFile) {
                $datos[$clave] = [
                    'archivo' => $valor->getClientOriginalName(),
                    'tamano'  => $valor->getSize(),
                ];
            } elseif (is_array($valor)) {
                $datos[$clave] = $this->limpiar($valor);
            }
        }

        return $datos;
    }
}
