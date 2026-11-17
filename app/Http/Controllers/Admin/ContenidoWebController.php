<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContenidoWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContenidoWebController extends Controller
{
    public function servicios()
    {
        return view('admin.contenido-web.servicios', [
            'contenido' => ContenidoWeb::obtener(ContenidoWeb::SERVICIOS),
        ]);
    }

    public function updateServicios(Request $request)
    {
        $request->validate([
            'contenido'       => 'required|json',
            'catalogo_nuevo'  => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $data = json_decode($request->input('contenido'), true);

        $validator = Validator::make($data, [
            'etiqueta'                                    => 'nullable|string|max:100',
            'titulo'                                      => 'required|string|max:150',
            'descripcion'                                 => 'nullable|string|max:1000',
            'catalogo_texto'                              => 'nullable|string|max:100',
            'catalogo_pdf'                                => 'nullable|string|max:255',
            'categorias'                                  => 'present|array',
            'categorias.*.nombre'                         => 'required|string|max:100',
            'categorias.*.icono'                          => 'nullable|string|max:20',
            'categorias.*.nota'                           => 'nullable|string|max:255',
            'categorias.*.secciones'                      => 'present|array',
            'categorias.*.secciones.*.titulo'             => 'nullable|string|max:150',
            'categorias.*.secciones.*.columnas'           => 'required|array|min:1|max:8',
            'categorias.*.secciones.*.columnas.*.nombre'  => 'required|string|max:60',
            'categorias.*.secciones.*.columnas.*.detalle' => 'nullable|string|max:60',
            'categorias.*.secciones.*.items'              => 'present|array',
            'categorias.*.secciones.*.items.*.nombre'     => 'required|string|max:150',
            'categorias.*.secciones.*.items.*.precios'    => 'present|array',
            'categorias.*.secciones.*.items.*.precios.*'  => 'nullable|string|max:60',
        ], [
            'categorias.*.nombre.required'                         => 'Cada categoría necesita un nombre.',
            'categorias.*.secciones.*.columnas.*.nombre.required'  => 'Cada columna de precio necesita un nombre.',
            'categorias.*.secciones.*.items.*.nombre.required'     => 'Cada servicio necesita un nombre.',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages(['contenido' => $validator->errors()->all()]);
        }

        $data = $this->normalizar($validator->validated());

        if ($request->hasFile('catalogo_nuevo')) {
            $nombre = 'catalogo-' . now()->format('YmdHis') . '-' . Str::random(6) . '.pdf';
            $request->file('catalogo_nuevo')->move(public_path('pdf'), $nombre);
            $data['catalogo_pdf'] = 'pdf/' . $nombre;
        }

        ContenidoWeb::guardar(ContenidoWeb::SERVICIOS, $data);

        return redirect()->route('admin.contenido-web.servicios')
            ->with('success', 'Sección "Nuestros Servicios" actualizada.');
    }

    public function restaurarServicios()
    {
        ContenidoWeb::where('clave', ContenidoWeb::SERVICIOS)->delete();

        return redirect()->route('admin.contenido-web.servicios')
            ->with('success', 'Se restauró el contenido original de "Nuestros Servicios".');
    }

    /** Recorta textos y ajusta cada fila al número de columnas de su sección. */
    private function normalizar(array $data): array
    {
        $texto = fn ($v) => trim((string) ($v ?? ''));

        foreach (['etiqueta', 'titulo', 'descripcion', 'catalogo_texto', 'catalogo_pdf'] as $campo) {
            $data[$campo] = $texto($data[$campo] ?? '');
        }

        $data['categorias'] = array_map(function ($cat) use ($texto) {
            return [
                'nombre'    => $texto($cat['nombre']),
                'icono'     => $texto($cat['icono'] ?? ''),
                'nota'      => $texto($cat['nota'] ?? ''),
                'secciones' => array_map(function ($sec) use ($texto) {
                    $columnas = array_map(fn ($c) => [
                        'nombre'  => $texto($c['nombre']),
                        'detalle' => $texto($c['detalle'] ?? ''),
                    ], array_values($sec['columnas']));

                    return [
                        'titulo'   => $texto($sec['titulo'] ?? ''),
                        'columnas' => $columnas,
                        'items'    => array_map(fn ($item) => [
                            'nombre'  => $texto($item['nombre']),
                            'precios' => array_map(
                                fn ($i) => $texto($item['precios'][$i] ?? ''),
                                array_keys($columnas)
                            ),
                        ], array_values($sec['items'])),
                    ];
                }, array_values($cat['secciones'])),
            ];
        }, array_values($data['categorias']));

        return $data;
    }
}
