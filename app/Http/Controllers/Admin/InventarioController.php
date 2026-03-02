<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entrada;
use App\Models\Producto;
use App\Models\Salida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InventarioController extends Controller
{
    /**
     * Stock actual usando subconsultas correlacionadas (evita duplicación por JOIN cruzado)
     */
    public function index(Request $request)
    {
        $q     = $request->input('q');
        $marca = $request->input('marca');
        $linea = $request->input('linea');

        $query = DB::table('productos as p')
            ->select([
                'p.id', 'p.codigo_barras', 'p.nombre', 'p.marca', 'p.linea', 'p.costo', 'p.stock_minimo',
                DB::raw('(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras) AS total_entradas'),
                DB::raw('(SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras) AS total_salidas'),
                DB::raw('(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras)
                       - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras) AS stock_actual'),
            ])
            ->orderBy('p.nombre');

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('p.nombre', 'like', "%{$q}%")
                    ->orWhere('p.codigo_barras', 'like', "%{$q}%");
            });
        }
        if ($marca) $query->where('p.marca', $marca);
        if ($linea)  $query->where('p.linea', $linea);

        $stock = $query->get();

        [$marcas, $lineas] = $this->filterOptions($marca);

        return view('admin.inventario.index', compact('stock', 'q', 'marca', 'linea', 'marcas', 'lineas'));
    }

    // ─── Productos ────────────────────────────────────────────────────────────

    public function productos(Request $request)
    {
        $q     = $request->input('q');
        $marca = $request->input('marca');
        $linea = $request->input('linea');

        $query = Producto::orderBy('nombre');

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%");
            });
        }
        if ($marca) $query->where('marca', $marca);
        if ($linea)  $query->where('linea', $linea);

        $productos = $query->get();

        [$marcas, $lineas] = $this->filterOptions($marca);

        return view('admin.inventario.productos.index', compact('productos', 'q', 'marca', 'linea', 'marcas', 'lineas'));
    }

    public function createProducto()
    {
        return view('admin.inventario.productos.create');
    }

    public function storeProducto(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|max:100|unique:productos,codigo_barras',
            'nombre'        => 'required|string|max:255',
            'marca'         => 'nullable|string|max:100',
            'linea'         => 'nullable|string|max:100',
            'costo'         => 'required|numeric|min:0',
            'stock_minimo'  => 'required|integer|min:0',
        ]);

        Producto::create($validated);

        return redirect()->route('admin.inventario.productos')
            ->with('success', 'Producto registrado correctamente.');
    }

    public function editProducto(Producto $producto)
    {
        return view('admin.inventario.productos.edit', compact('producto'));
    }

    public function updateProducto(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|max:100|unique:productos,codigo_barras,' . $producto->id,
            'nombre'        => 'required|string|max:255',
            'marca'         => 'nullable|string|max:100',
            'linea'         => 'nullable|string|max:100',
            'costo'         => 'required|numeric|min:0',
            'stock_minimo'  => 'required|integer|min:0',
        ]);

        $producto->update($validated);

        return redirect()->route('admin.inventario.productos')
            ->with('success', 'Producto actualizado correctamente.');
    }

    // ─── Entradas ─────────────────────────────────────────────────────────────

    public function entradas(Request $request)
    {
        $q     = $request->input('q');
        $marca = $request->input('marca');
        $linea = $request->input('linea');
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');

        $query = Entrada::with('producto')
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($q || $marca || $linea) {
            $prodQ = Producto::query();
            if ($q) {
                $prodQ->where(function ($qb) use ($q) {
                    $qb->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo_barras', 'like', "%{$q}%");
                });
            }
            if ($marca) $prodQ->where('marca', $marca);
            if ($linea)  $prodQ->where('linea', $linea);
            $query->whereIn('codigo_barras', $prodQ->pluck('codigo_barras'));
        }

        if ($desde) $query->where('fecha', '>=', $desde);
        if ($hasta)  $query->where('fecha', '<=', $hasta);

        $entradas = $query->paginate(20)->withQueryString();

        [$marcas, $lineas] = $this->filterOptions($marca);

        return view('admin.inventario.entradas.index', compact(
            'entradas', 'q', 'marca', 'linea', 'desde', 'hasta', 'marcas', 'lineas'
        ));
    }

    public function createEntrada()
    {
        $productos = Producto::orderBy('nombre')->get();
        return view('admin.inventario.entradas.create', compact('productos'));
    }

    public function storeEntrada(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|exists:productos,codigo_barras',
            'unidades'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
        ]);

        Entrada::create($validated);

        return redirect()->route('admin.inventario.entradas')
            ->with('success', 'Entrada registrada correctamente.');
    }

    // ─── Salidas ──────────────────────────────────────────────────────────────

    public function salidas(Request $request)
    {
        $q       = $request->input('q');
        $marca   = $request->input('marca');
        $linea   = $request->input('linea');
        $desde   = $request->input('desde');
        $hasta   = $request->input('hasta');
        $destino = $request->input('destino');

        $query = Salida::with('producto')
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($q || $marca || $linea) {
            $prodQ = Producto::query();
            if ($q) {
                $prodQ->where(function ($qb) use ($q) {
                    $qb->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo_barras', 'like', "%{$q}%");
                });
            }
            if ($marca) $prodQ->where('marca', $marca);
            if ($linea)  $prodQ->where('linea', $linea);
            $query->whereIn('codigo_barras', $prodQ->pluck('codigo_barras'));
        }

        if ($desde)   $query->where('fecha', '>=', $desde);
        if ($hasta)    $query->where('fecha', '<=', $hasta);
        if ($destino)  $query->where('destino', 'like', "%{$destino}%");

        $salidas = $query->paginate(20)->withQueryString();

        [$marcas, $lineas] = $this->filterOptions($marca);

        return view('admin.inventario.salidas.index', compact(
            'salidas', 'q', 'marca', 'linea', 'desde', 'hasta', 'destino', 'marcas', 'lineas'
        ));
    }

    public function createSalida()
    {
        $productos = Producto::orderBy('nombre')->get();
        return view('admin.inventario.salidas.create', compact('productos'));
    }

    public function storeSalida(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|exists:productos,codigo_barras',
            'unidades'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
            'destino'       => 'nullable|string|max:255',
        ]);

        Salida::create($validated);

        return redirect()->route('admin.inventario.salidas')
            ->with('success', 'Salida registrada correctamente.');
    }

    // ─── Análisis por producto ────────────────────────────────────────────────

    public function analisis(Request $request)
    {
        $marca     = $request->input('marca');
        $linea     = $request->input('linea');
        $temporada = $request->input('temporada');
        $año       = (int) $request->input('año', now()->year);

        [$desde, $hasta] = $this->temporadaRango($temporada, $año);

        $entFiltro = $desde ? " AND fecha BETWEEN '{$desde}' AND '{$hasta}'" : '';
        $salFiltro = $desde ? " AND fecha BETWEEN '{$desde}' AND '{$hasta}'" : '';

        $query = DB::table('productos as p')
            ->select([
                'p.id', 'p.codigo_barras', 'p.nombre', 'p.marca', 'p.linea', 'p.stock_minimo',
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras{$entFiltro}) AS total_entradas"),
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras{$salFiltro}) AS total_salidas"),
                DB::raw('(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras)
                       - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras) AS stock_actual'),
                DB::raw('(SELECT MAX(fecha) FROM salidas WHERE codigo_barras = p.codigo_barras) AS ultima_salida'),
                DB::raw('DATEDIFF(CURDATE(),(SELECT MAX(fecha) FROM salidas WHERE codigo_barras = p.codigo_barras)) AS dias_sin_salida'),
            ])
            ->orderBy('p.nombre');

        if ($marca) $query->where('p.marca', $marca);
        if ($linea)  $query->where('p.linea', $linea);

        $productos = $query->get();

        $topEntradas   = $productos->sortByDesc('total_entradas')->where('total_entradas', '>', 0)->take(10)->values();
        $topSalidas    = $productos->sortByDesc('total_salidas')->where('total_salidas', '>', 0)->take(10)->values();
        $masEstancados = $productos->filter(fn($p) => $p->dias_sin_salida !== null)
                                   ->sortByDesc('dias_sin_salida')->take(10)->values();

        [$marcas, $lineas] = $this->filterOptions($marca);

        $añoMin = (int) (DB::table('entradas')->selectRaw('YEAR(MIN(fecha)) as y')->value('y') ?? now()->year);
        $años   = range($añoMin, now()->year + 1);

        // ── Sugerencias de compra basadas en histórico de la misma temporada ──
        $sugerencias   = collect();
        $histRangos    = [];
        $histAñosLabel = [];

        if ($temporada) {
            // Hasta 5 años atrás de la temporada seleccionada
            for ($y = $año - 1; $y >= max($año - 5, $añoMin); $y--) {
                [$hD, $hH] = $this->temporadaRango($temporada, $y);
                if ($hD) {
                    $histRangos[$y]    = ['desde' => $hD, 'hasta' => $hH];
                    $histAñosLabel[$y] = $y;
                }
            }

            if (!empty($histRangos)) {
                // Una sola query: total salidas de esos rangos por producto
                $condiciones = collect($histRangos)
                    ->map(fn($r) => "(fecha BETWEEN '{$r['desde']}' AND '{$r['hasta']}')")
                    ->implode(' OR ');

                // Filtro por marca/linea si aplica
                $histQuery = DB::table('salidas as s')
                    ->selectRaw('s.codigo_barras, SUM(s.unidades) as hist_total, COUNT(DISTINCT YEAR(s.fecha)) as hist_años')
                    ->whereRaw("({$condiciones})")
                    ->groupBy('s.codigo_barras');

                if ($marca || $linea) {
                    $histQuery->join('productos as p', 'p.codigo_barras', '=', 's.codigo_barras');
                    if ($marca) $histQuery->where('p.marca', $marca);
                    if ($linea) $histQuery->where('p.linea', $linea);
                }

                $histVentas = $histQuery->get()->keyBy('codigo_barras');

                foreach ($histVentas as $codigo => $hist) {
                    $prod = $productos->firstWhere('codigo_barras', $codigo);
                    if (!$prod) continue;

                    $avgVentas       = round($hist->hist_total / $hist->hist_años, 1);
                    $stockActual     = (int) $prod->stock_actual;
                    $comprarSugerido = max(0, (int) ceil($avgVentas - $stockActual));

                    // Solo mostrar si hay algo que sugerir (ventas históricas > 0)
                    if ($avgVentas <= 0) continue;

                    // Nivel de urgencia
                    if ($stockActual <= 0 || $stockActual < $prod->stock_minimo) {
                        $urgencia = 'alta';
                    } elseif ($stockActual < $avgVentas * 0.5) {
                        $urgencia = 'media';
                    } elseif ($stockActual < $avgVentas) {
                        $urgencia = 'baja';
                    } else {
                        $urgencia = 'ok'; // stock suficiente, mostrar de todas formas como referencia
                    }

                    $sugerencias->push((object) [
                        'codigo_barras'   => $codigo,
                        'nombre'          => $prod->nombre,
                        'marca'           => $prod->marca,
                        'linea'           => $prod->linea,
                        'stock_minimo'    => $prod->stock_minimo,
                        'avg_ventas'      => $avgVentas,
                        'hist_total'      => $hist->hist_total,
                        'hist_años'       => $hist->hist_años,
                        'stock_actual'    => $stockActual,
                        'comprar_sugerido'=> $comprarSugerido,
                        'urgencia'        => $urgencia,
                    ]);
                }

                // Ordenar: urgencia alta primero, luego por cantidad sugerida desc
                $orden = ['alta' => 0, 'media' => 1, 'baja' => 2, 'ok' => 3];
                $sugerencias = $sugerencias->sort(function ($a, $b) use ($orden) {
                    $cmp = $orden[$a->urgencia] <=> $orden[$b->urgencia];
                    return $cmp !== 0 ? $cmp : $b->comprar_sugerido <=> $a->comprar_sugerido;
                })->values();
            }
        }

        return view('admin.inventario.analisis', compact(
            'productos', 'topEntradas', 'topSalidas', 'masEstancados',
            'marca', 'linea', 'marcas', 'lineas',
            'temporada', 'año', 'desde', 'hasta', 'años',
            'sugerencias', 'histRangos', 'histAñosLabel'
        ));
    }

    /**
     * Devuelve [desde, hasta] en formato Y-m-d para la temporada solicitada.
     * Estaciones para Bolivia (Hemisferio Sur):
     *   Verano    dic–feb  |  Otoño   mar–may
     *   Invierno  jun–ago  |  Primavera sep–nov
     */
    private function temporadaRango(?string $temporada, int $año): array
    {
        return match ($temporada) {
            'verano'    => [($año - 1) . '-12-01',
                            \Carbon\Carbon::createFromDate($año, 2, 1)->endOfMonth()->format('Y-m-d')],
            'otono'     => ["{$año}-03-01", "{$año}-05-31"],
            'invierno'  => ["{$año}-06-01", "{$año}-08-31"],
            'primavera' => ["{$año}-09-01", "{$año}-11-30"],
            default     => [null, null],
        };
    }

    // ─── Importación Excel ────────────────────────────────────────────────────

    public function showImport()
    {
        return view('admin.inventario.importar');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel'          => 'required|file|mimes:xlsx,xls|max:20480',
            'fecha_registro' => 'required|date',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('excel')->getPathname());
        } catch (\Exception $e) {
            return back()->withErrors(['excel' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
        }

        $sheets = [];
        foreach ($spreadsheet->getSheetNames() as $name) {
            $sheets[strtoupper(trim($name))] = $spreadsheet->getSheetByName($name);
        }

        $stats         = ['productos_nuevos' => 0, 'entradas' => 0, 'salidas' => 0, 'errores' => []];
        $fechaRegistro = $request->input('fecha_registro');

        // Paso 1: extraer costos de INVENTARIO
        $costos = [];
        if ($invSheet = $sheets['INVENTARIO'] ?? null) {
            for ($r = 2; $r <= $invSheet->getHighestRow(); $r++) {
                $codigo = $this->normalizeCodigo($invSheet->getCell('A' . $r)->getValue());
                $costo  = (float) $invSheet->getCell('H' . $r)->getValue();
                if ($codigo) {
                    $costos[$codigo] = $costo;
                }
            }
        }

        // Paso 2: REGISTRO → productos + entradas iniciales
        if ($regSheet = $sheets['REGISTRO'] ?? null) {
            for ($r = 2; $r <= $regSheet->getHighestRow(); $r++) {
                $codigo = $this->normalizeCodigo($regSheet->getCell('A' . $r)->getValue());
                if (!$codigo) continue;

                $nombre   = trim((string) $regSheet->getCell('B' . $r)->getValue());
                $marca    = trim((string) $regSheet->getCell('C' . $r)->getValue()) ?: null;
                $linea    = trim((string) $regSheet->getCell('D' . $r)->getValue()) ?: null;
                $unidades = (int) $regSheet->getCell('E' . $r)->getValue();

                $producto = Producto::updateOrCreate(
                    ['codigo_barras' => $codigo],
                    ['nombre' => $nombre, 'marca' => $marca, 'linea' => $linea, 'costo' => $costos[$codigo] ?? 0]
                );
                if ($producto->wasRecentlyCreated) {
                    $stats['productos_nuevos']++;
                }

                if ($unidades > 0) {
                    Entrada::create(['codigo_barras' => $codigo, 'unidades' => $unidades, 'fecha' => $fechaRegistro]);
                    $stats['entradas']++;
                }
            }
        }

        // Paso 3: ENTRADA / ENTRADAS
        $entSheet = $sheets['ENTRADAS'] ?? $sheets['ENTRADA'] ?? null;
        if ($entSheet) {
            for ($r = 2; $r <= $entSheet->getHighestRow(); $r++) {
                $codigo   = $this->normalizeCodigo($entSheet->getCell('A' . $r)->getValue());
                $unidades = (int) $entSheet->getCell('E' . $r)->getValue();
                if (!$codigo || !$unidades) continue;

                try {
                    $fecha = $this->parseDate($entSheet->getCell('F' . $r)->getValue());
                } catch (\Exception $e) {
                    $stats['errores'][] = "Entrada fila {$r}: fecha inválida";
                    continue;
                }

                Producto::firstOrCreate(
                    ['codigo_barras' => $codigo],
                    [
                        'nombre' => trim((string) $entSheet->getCell('B' . $r)->getValue()),
                        'marca'  => trim((string) $entSheet->getCell('C' . $r)->getValue()) ?: null,
                        'linea'  => trim((string) $entSheet->getCell('D' . $r)->getValue()) ?: null,
                        'costo'  => $costos[$codigo] ?? 0,
                    ]
                );

                Entrada::create(['codigo_barras' => $codigo, 'unidades' => $unidades, 'fecha' => $fecha]);
                $stats['entradas']++;
            }
        }

        // Paso 4: SALIDAS
        if ($salSheet = $sheets['SALIDAS'] ?? null) {
            for ($r = 2; $r <= $salSheet->getHighestRow(); $r++) {
                $codigo   = $this->normalizeCodigo($salSheet->getCell('A' . $r)->getValue());
                $unidades = (int) $salSheet->getCell('E' . $r)->getValue();
                if (!$codigo || !$unidades) continue;

                try {
                    $fecha = $this->parseDate($salSheet->getCell('F' . $r)->getValue());
                } catch (\Exception $e) {
                    $stats['errores'][] = "Salida fila {$r}: fecha inválida";
                    continue;
                }

                $destino = trim((string) $salSheet->getCell('G' . $r)->getValue()) ?: null;

                Producto::firstOrCreate(
                    ['codigo_barras' => $codigo],
                    [
                        'nombre' => trim((string) $salSheet->getCell('B' . $r)->getValue()),
                        'marca'  => trim((string) $salSheet->getCell('C' . $r)->getValue()) ?: null,
                        'linea'  => trim((string) $salSheet->getCell('D' . $r)->getValue()) ?: null,
                        'costo'  => $costos[$codigo] ?? 0,
                    ]
                );

                Salida::create(['codigo_barras' => $codigo, 'unidades' => $unidades, 'fecha' => $fecha, 'destino' => $destino]);
                $stats['salidas']++;
            }
        }

        return redirect()->route('admin.inventario.importar')
            ->with('import_stats', $stats);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Devuelve [marcas, lineas] para los dropdowns de filtro.
     * Si se selecciona una marca, las lineas se filtran a las de esa marca.
     */
    private function filterOptions(?string $marcaSeleccionada): array
    {
        $marcas = Producto::whereNotNull('marca')
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        $lineas = Producto::whereNotNull('linea')
            ->when($marcaSeleccionada, fn ($q) => $q->where('marca', $marcaSeleccionada))
            ->distinct()
            ->orderBy('linea')
            ->pluck('linea');

        return [$marcas, $lineas];
    }

    private function normalizeCodigo(mixed $value): string
    {
        if ($value === null || $value === '') return '';
        if (is_float($value) || is_int($value)) {
            return number_format((float) $value, 0, '.', '');
        }
        return trim((string) $value);
    }

    private function parseDate(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_int($value) || is_float($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        $str = trim((string) $value);
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $str)) {
            return \Carbon\Carbon::createFromFormat('d/m/Y', $str)->format('Y-m-d');
        }
        return \Carbon\Carbon::parse($str)->format('Y-m-d');
    }
}
