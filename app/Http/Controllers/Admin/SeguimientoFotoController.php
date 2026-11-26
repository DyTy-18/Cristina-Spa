<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\SeguimientoFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fotos del resultado de los servicios: se suben desde el seguimiento de una cita
 * y forman el álbum de la clienta.
 */
class SeguimientoFotoController extends Controller
{
    public function store(Request $request, Cita $cita)
    {
        abort_unless($cita->cliente_id, 422, 'La cita no tiene clienta asignada.');

        $data = $request->validate([
            'fotos'       => 'required|array|max:10',
            'fotos.*'     => 'image|mimes:jpg,jpeg,png,webp|max:15360',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'fotos.required' => 'Elige al menos una foto.',
            'fotos.max'      => 'Puedes subir hasta 10 fotos a la vez.',
            'fotos.*.image'  => 'Uno de los archivos no es una imagen.',
            'fotos.*.mimes'  => 'Formatos permitidos: JPG, PNG o WEBP.',
            'fotos.*.max'    => 'Cada foto puede pesar hasta 15 MB.',
        ]);

        foreach ($request->file('fotos') as $archivo) {
            $cita->seguimientoFotos()->create([
                'cliente_id'  => $cita->cliente_id,
                'user_id'     => auth()->id(),
                'ruta'        => $archivo->store("seguimiento-fotos/cliente-{$cita->cliente_id}", SeguimientoFoto::DISCO),
                'descripcion' => $data['descripcion'] ?? null,
            ]);
        }

        $n = count($request->file('fotos'));

        return back()->with('success', $n === 1 ? 'Foto agregada al álbum de la clienta.' : "{$n} fotos agregadas al álbum de la clienta.");
    }

    /** Sirve la foto (solo a usuarios autenticados). Con ?descargar=1 la baja como archivo. */
    public function ver(Request $request, SeguimientoFoto $foto)
    {
        $disco = Storage::disk(SeguimientoFoto::DISCO);
        abort_unless($disco->exists($foto->ruta), 404);

        if ($request->boolean('descargar')) {
            $foto->loadMissing('cliente');
            $nombre = Str::slug(($foto->cliente?->nombre ?? 'clienta') . ' ' . ($foto->cliente?->apellido ?? ''))
                . '-' . $foto->created_at->format('Y-m-d') . '-' . $foto->id
                . '.' . pathinfo($foto->ruta, PATHINFO_EXTENSION);

            return $disco->download($foto->ruta, $nombre);
        }

        return response()->file($disco->path($foto->ruta), ['Cache-Control' => 'private, max-age=86400']);
    }

    public function destroy(SeguimientoFoto $foto)
    {
        $foto->delete();

        return back()->with('success', 'Foto eliminada del álbum.');
    }

    /** Álbum completo de la clienta, agrupado por cita. */
    public function album(Cliente $cliente)
    {
        $fotos = $cliente->fotos()->with(['cita.citaServicios.servicio', 'user'])->get();

        $grupos = $fotos->groupBy(fn (SeguimientoFoto $f) => $f->cita_id ?? 'sin-cita');

        return view('admin.clientes.album', compact('cliente', 'fotos', 'grupos'));
    }
}
