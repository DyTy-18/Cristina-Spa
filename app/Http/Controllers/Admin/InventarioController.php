<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertaStock;
use App\Models\Entrada;
use App\Models\InventarioBackup;
use App\Models\Producto;
use App\Models\Salida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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
        $tipoStock = $request->input('tipo') === 'reventa' ? 'reventa' : 'tecnico';
        $sid    = session('sucursal_activa_id');
        $sidSql = $sid ? " AND sucursal_id = {$sid}" : '';

        $query = DB::table('productos as p')
            ->select([
                'p.id', 'p.codigo_barras', 'p.nombre', 'p.marca', 'p.linea', 'p.costo', 'p.stock_minimo', 'p.es_reventa',
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras AND tipo_stock = '{$tipoStock}'{$sidSql}) AS total_entradas"),
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras AND tipo_stock = '{$tipoStock}'{$sidSql}) AS total_salidas"),
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras AND tipo_stock = '{$tipoStock}'{$sidSql})
                       - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras AND tipo_stock = '{$tipoStock}'{$sidSql}) AS stock_actual"),
            ])
            ->orderBy('p.nombre');

        if ($tipoStock === 'reventa') {
            $query->where('p.es_reventa', true);
        }

        // Sucursal específica: solo mostrar productos con actividad (movimientos) en esa
        // sucursal para el pool activo. Igual regla para técnico y reventa: un producto de
        // reventa recién creado queda anclado a la sucursal activa vía asegurarPresenciaReventa().
        if ($sid) {
            $query->whereRaw(
                "EXISTS (SELECT 1 FROM entradas WHERE entradas.codigo_barras = p.codigo_barras AND entradas.sucursal_id = ? AND entradas.tipo_stock = ?)
                 OR EXISTS (SELECT 1 FROM salidas WHERE salidas.codigo_barras = p.codigo_barras AND salidas.sucursal_id = ? AND salidas.tipo_stock = ?)",
                [$sid, $tipoStock, $sid, $tipoStock]
            );
        }

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('p.nombre', 'like', "%{$q}%")
                    ->orWhere('p.codigo_barras', 'like', "%{$q}%");
            });
        }
        if ($marca) $query->where('p.marca', $marca);
        if ($linea)  $query->where('p.linea', $linea);

        $stock = $query->get();

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        $alertas = $tipoStock === 'tecnico'
            ? AlertaStock::noLeidas()->with('producto')->latest()->get()
            : collect();

        return view('admin.inventario.index', compact('stock', 'q', 'marca', 'linea', 'marcas', 'lineas', 'alertas', 'tipoStock'));
    }

    // ─── Productos ────────────────────────────────────────────────────────────

    public function productos(Request $request)
    {
        $q     = $request->input('q');
        $marca = $request->input('marca');
        $linea = $request->input('linea');
        $sid   = session('sucursal_activa_id');
        $tipoStock = $request->input('tipo') === 'reventa' ? 'reventa' : 'tecnico';

        $query = Producto::orderBy('nombre');

        if ($tipoStock === 'reventa') {
            $query->where('es_reventa', true);
        }

        // Sucursal específica: solo listar productos con movimiento del pool activo
        // registrado ahí. En "todas las sucursales" se ve el catálogo completo.
        if ($sid) {
            $query->where(function ($qb) use ($sid, $tipoStock) {
                $qb->whereExists(function ($sub) use ($sid, $tipoStock) {
                    $sub->selectRaw('1')->from('entradas')
                        ->whereColumn('entradas.codigo_barras', 'productos.codigo_barras')
                        ->where('entradas.sucursal_id', $sid)
                        ->where('entradas.tipo_stock', $tipoStock);
                })->orWhereExists(function ($sub) use ($sid, $tipoStock) {
                    $sub->selectRaw('1')->from('salidas')
                        ->whereColumn('salidas.codigo_barras', 'productos.codigo_barras')
                        ->where('salidas.sucursal_id', $sid)
                        ->where('salidas.tipo_stock', $tipoStock);
                });
            });
        }

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%");
            });
        }
        if ($marca) $query->where('marca', $marca);
        if ($linea)  $query->where('linea', $linea);

        $productos = $query->get();

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        return view('admin.inventario.productos.index', compact('productos', 'q', 'marca', 'linea', 'marcas', 'lineas', 'tipoStock'));
    }

    public function createProducto()
    {
        return view('admin.inventario.productos.create');
    }

    public function storeProducto(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras'       => 'required|string|max:100|unique:productos,codigo_barras',
            'nombre'              => 'required|string|max:255',
            'marca'               => 'nullable|string|max:100',
            'linea'               => 'nullable|string|max:100',
            'costo'               => 'required|numeric|min:0',
            'precio_venta'        => 'nullable|numeric|min:0',
            'stock_minimo'        => 'required|integer|min:0',
            'es_reventa'          => 'nullable|boolean',
            'unidades_iniciales'  => 'nullable|integer|min:0',
            'fecha_inicial'       => 'nullable|date',
        ], [
            'codigo_barras.unique' => 'Ese código de barras ya está registrado en el catálogo (puede pertenecer a otra sucursal, por eso no aparece en tus filtros). No se puede duplicar.',
        ]);
        $esReventa         = $request->boolean('es_reventa');
        $unidadesIniciales = (int) ($request->input('unidades_iniciales') ?? 0);
        $fechaInicial      = $request->input('fecha_inicial') ?: now()->toDateString();

        $producto = Producto::create([
            'codigo_barras' => $validated['codigo_barras'],
            'nombre'        => $validated['nombre'],
            'marca'         => $validated['marca'] ?? null,
            'linea'         => $validated['linea'] ?? null,
            'costo'         => $validated['costo'],
            'precio_venta'  => $validated['precio_venta'] ?? null,
            'stock_minimo'  => $validated['stock_minimo'],
            'es_reventa'    => $esReventa,
        ]);

        $sid = session('sucursal_activa_id');
        if ($unidadesIniciales > 0 && $sid) {
            // Ya viene con stock real: esa entrada ancla el producto a la sucursal.
            Entrada::create([
                'codigo_barras' => $producto->codigo_barras,
                'sucursal_id'   => $sid,
                'tipo_stock'    => 'tecnico',
                'unidades'      => $unidadesIniciales,
                'fecha'         => $fechaInicial,
            ]);
        } else {
            $this->asegurarPresenciaSucursal($producto, 'tecnico');
        }

        if ($esReventa) {
            $this->asegurarPresenciaSucursal($producto, 'reventa');
        }

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
            'precio_venta'  => 'nullable|numeric|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'es_reventa'    => 'nullable|boolean',
        ]);
        $validated['es_reventa'] = $request->boolean('es_reventa');

        $producto->update($validated);

        $this->asegurarPresenciaSucursal($producto, 'tecnico');
        if ($producto->es_reventa) {
            $this->asegurarPresenciaSucursal($producto, 'reventa');
        }

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
        $tipoStock = $request->input('tipo') === 'reventa' ? 'reventa' : 'tecnico';

        $sid = session('sucursal_activa_id');

        $query = Entrada::with('producto')
            ->where('tipo_stock', $tipoStock)
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
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

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        return view('admin.inventario.entradas.index', compact(
            'entradas', 'q', 'marca', 'linea', 'desde', 'hasta', 'marcas', 'lineas', 'tipoStock'
        ));
    }

    public function createEntrada()
    {
        $productos  = $this->productosConStock();
        $sucursales = \App\Models\Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.inventario.entradas.create', compact('productos', 'sucursales'));
    }

    public function storeEntrada(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|exists:productos,codigo_barras',
            'unidades'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
        ]);

        $sid = session('sucursal_activa_id') ?? $request->integer('sucursal_id') ?: auth()->user()->sucursal_id;
        Entrada::create(array_merge($validated, ['sucursal_id' => $sid, 'tipo_stock' => 'tecnico']));

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
        $tipoStock = $request->input('tipo') === 'reventa' ? 'reventa' : 'tecnico';

        $sid = session('sucursal_activa_id');

        $query = Salida::with('producto')
            ->where('tipo_stock', $tipoStock)
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
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

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        return view('admin.inventario.salidas.index', compact(
            'salidas', 'q', 'marca', 'linea', 'desde', 'hasta', 'destino', 'marcas', 'lineas', 'tipoStock'
        ));
    }

    public function createSalida()
    {
        $productos  = $this->productosConStock();
        $sucursales = \App\Models\Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.inventario.salidas.create', compact('productos', 'sucursales'));
    }

    public function storeSalida(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|exists:productos,codigo_barras',
            'unidades'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
            'destino'       => 'nullable|string|max:255',
        ]);

        $sid = session('sucursal_activa_id') ?? $request->integer('sucursal_id') ?: auth()->user()->sucursal_id;
        Salida::create(array_merge($validated, ['sucursal_id' => $sid, 'tipo_stock' => 'tecnico']));

        return redirect()->route('admin.inventario.salidas')
            ->with('success', 'Salida registrada correctamente.');
    }

    // ─── Reventa (segundo inventario, alimentado por transferencias) ──────────

    public function createTransferencia()
    {
        // Cualquier producto técnico con stock en la sucursal activa se puede transferir;
        // no hace falta que ya esté marcado como "de reventa" (la transferencia lo habilita).
        $productos  = $this->productosConStock('tecnico');
        $sucursales = \App\Models\Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.inventario.reventa.transferir', compact('productos', 'sucursales'));
    }

    public function storeTransferencia(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|exists:productos,codigo_barras',
            'unidades'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
        ]);

        $producto = Producto::where('codigo_barras', $validated['codigo_barras'])->firstOrFail();

        $sid = session('sucursal_activa_id') ?? $request->integer('sucursal_id') ?: auth()->user()->sucursal_id;

        DB::transaction(function () use ($validated, $sid) {
            Salida::create([
                'codigo_barras' => $validated['codigo_barras'],
                'sucursal_id'   => $sid,
                'tipo_stock'    => 'tecnico',
                'unidades'      => $validated['unidades'],
                'fecha'         => $validated['fecha'],
                'destino'       => 'Transferencia a reventa',
            ]);

            Entrada::create([
                'codigo_barras' => $validated['codigo_barras'],
                'sucursal_id'   => $sid,
                'tipo_stock'    => 'reventa',
                'unidades'      => $validated['unidades'],
                'fecha'         => $validated['fecha'],
            ]);
        });

        // La transferencia habilita el pool de reventa para este producto.
        if (! $producto->es_reventa) {
            $producto->update(['es_reventa' => true]);
        }

        return redirect()->route('admin.inventario.index', ['tipo' => 'reventa'])
            ->with('success', 'Transferencia a reventa registrada correctamente.');
    }

    public function createEntradaReventa()
    {
        $productos  = $this->productosConStock('reventa');
        $sucursales = \App\Models\Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.inventario.reventa.entrada', compact('productos', 'sucursales'));
    }

    public function storeEntradaReventa(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|exists:productos,codigo_barras',
            'unidades'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
        ]);

        $producto = Producto::where('codigo_barras', $validated['codigo_barras'])->firstOrFail();
        if (! $producto->es_reventa) {
            return back()->withErrors(['codigo_barras' => 'Este producto no está habilitado para reventa.'])->withInput();
        }

        $sid = session('sucursal_activa_id') ?? $request->integer('sucursal_id') ?: auth()->user()->sucursal_id;
        Entrada::create(array_merge($validated, ['sucursal_id' => $sid, 'tipo_stock' => 'reventa']));

        return redirect()->route('admin.inventario.entradas', ['tipo' => 'reventa'])
            ->with('success', 'Entrada de reventa registrada correctamente.');
    }

    public function createSalidaReventa()
    {
        $productos  = $this->productosConStock('reventa');
        $sucursales = \App\Models\Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
        return view('admin.inventario.reventa.salida', compact('productos', 'sucursales'));
    }

    public function storeSalidaReventa(Request $request)
    {
        $validated = $request->validate([
            'codigo_barras' => 'required|string|exists:productos,codigo_barras',
            'unidades'      => 'required|integer|min:1',
            'fecha'         => 'required|date',
            'destino'       => 'nullable|string|max:255',
        ]);

        $producto = Producto::where('codigo_barras', $validated['codigo_barras'])->firstOrFail();
        if (! $producto->es_reventa) {
            return back()->withErrors(['codigo_barras' => 'Este producto no está habilitado para reventa.'])->withInput();
        }

        $sid = session('sucursal_activa_id') ?? $request->integer('sucursal_id') ?: auth()->user()->sucursal_id;
        Salida::create(array_merge($validated, ['sucursal_id' => $sid, 'tipo_stock' => 'reventa']));

        return redirect()->route('admin.inventario.salidas', ['tipo' => 'reventa'])
            ->with('success', 'Venta de reventa registrada correctamente.');
    }

    // ─── Análisis por producto ────────────────────────────────────────────────

    public function analisis(Request $request)
    {
        $marca     = $request->input('marca');
        $linea     = $request->input('linea');
        $temporada = $request->input('temporada');
        $año       = (int) $request->input('año', now()->year);

        [$desde, $hasta] = $this->temporadaRango($temporada, $año);

        $sid       = session('sucursal_activa_id');
        $sidFilter = $sid ? " AND sucursal_id = {$sid}" : '';
        $entFiltro = $desde ? " AND fecha BETWEEN '{$desde}' AND '{$hasta}'" : '';
        $salFiltro = $desde ? " AND fecha BETWEEN '{$desde}' AND '{$hasta}'" : '';

        $query = DB::table('productos as p')
            ->select([
                'p.id', 'p.codigo_barras', 'p.nombre', 'p.marca', 'p.linea', 'p.stock_minimo',
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras AND tipo_stock = 'tecnico'{$sidFilter}{$entFiltro}) AS total_entradas"),
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras AND tipo_stock = 'tecnico'{$sidFilter}{$salFiltro}) AS total_salidas"),
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = p.codigo_barras AND tipo_stock = 'tecnico'{$sidFilter})
                       - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = p.codigo_barras AND tipo_stock = 'tecnico'{$sidFilter}) AS stock_actual"),
                DB::raw("(SELECT MAX(fecha) FROM salidas WHERE codigo_barras = p.codigo_barras AND tipo_stock = 'tecnico'{$sidFilter}) AS ultima_salida"),
            ])
            ->orderBy('p.nombre');

        if ($marca) $query->where('p.marca', $marca);
        if ($linea)  $query->where('p.linea', $linea);

        $productos = $query->get()->map(function ($p) {
            $p->dias_sin_salida = $p->ultima_salida
                ? now()->diffInDays(\Carbon\Carbon::parse($p->ultima_salida))
                : null;
            return $p;
        });

        $topEntradas   = $productos->sortByDesc('total_entradas')->where('total_entradas', '>', 0)->take(10)->values();
        $topSalidas    = $productos->sortByDesc('total_salidas')->where('total_salidas', '>', 0)->take(10)->values();
        $masEstancados = $productos->filter(fn($p) => $p->dias_sin_salida !== null)
                                   ->sortByDesc('dias_sin_salida')->take(10)->values();

        [$marcas, $lineas] = $this->filterOptions($marca);

        $minFecha = DB::table('entradas')->min('fecha');
        $añoMin = $minFecha ? (int) substr($minFecha, 0, 4) : now()->year;
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
                    ->selectRaw("s.codigo_barras, SUM(s.unidades) as hist_total, COUNT(DISTINCT substr(s.fecha, 1, 4)) as hist_años")
                    ->where('s.tipo_stock', 'tecnico')
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
            'excel' => 'required|file|mimes:xlsx,xls|max:20480',
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

        $stats      = ['productos_nuevos' => 0, 'entradas' => 0, 'salidas' => 0, 'errores' => []];
        $sucursalId = session('sucursal_activa_id');

        // INVENTARIO se ignora: es un dashboard con fórmulas, no aporta datos al import.

        // Paso 1: REGISTRO → catálogo de productos (sin generar stock inicial)
        if ($regSheet = $sheets['REGISTRO'] ?? null) {
            $cols        = $this->buildHeaderMap($regSheet);
            $colCodigo   = $this->findColumn($cols, ['CODIGO DE BARRA', 'CODIGO DE BARRAS']);
            $colNombre   = $cols['NOMBRE'] ?? null;
            $colMarca    = $cols['MARCA'] ?? null;
            $colLinea    = $cols['LINEA'] ?? null;
            $colUso      = $cols['USO'] ?? null;

            for ($r = 2; $r <= $regSheet->getHighestRow(); $r++) {
                $codigo = $this->normalizeCodigo($this->cellValue($regSheet, $colCodigo, $r));
                if (!$codigo) continue;
                if (!ctype_digit($codigo)) {
                    $stats['errores'][] = "REGISTRO fila {$r}: código no numérico, omitida";
                    continue;
                }

                $producto = Producto::updateOrCreate(
                    ['codigo_barras' => $codigo],
                    [
                        'nombre' => trim((string) $this->cellValue($regSheet, $colNombre, $r)),
                        'marca'  => trim((string) $this->cellValue($regSheet, $colMarca, $r)) ?: null,
                        'linea'  => trim((string) $this->cellValue($regSheet, $colLinea, $r)) ?: null,
                        'uso'    => trim((string) $this->cellValue($regSheet, $colUso, $r)) ?: null,
                        'costo'  => 0,
                    ]
                );
                if ($producto->wasRecentlyCreated) {
                    $stats['productos_nuevos']++;
                }
            }
        }

        // Paso 2: ENTRADA / ENTRADAS → movimientos de entrada
        $entSheet = $sheets['ENTRADA'] ?? $sheets['ENTRADAS'] ?? null;
        if ($entSheet) {
            $cols        = $this->buildHeaderMap($entSheet);
            $colCodigo   = $this->findColumn($cols, ['CODIGO DE BARRAS', 'CODIGO DE BARRA']);
            $colNombre   = $cols['NOMBRE'] ?? null;
            $colMarca    = $cols['MARCA'] ?? null;
            $colLinea    = $cols['LINEA'] ?? null;
            $colUnidades = $cols['UNIDADES'] ?? null;
            $colFecha    = $cols['FECHA'] ?? null;

            for ($r = 2; $r <= $entSheet->getHighestRow(); $r++) {
                $codigo   = $this->normalizeCodigo($this->cellValue($entSheet, $colCodigo, $r));
                $unidades = (int) $this->cellValue($entSheet, $colUnidades, $r);
                if (!$codigo || !$unidades) continue;
                if (!ctype_digit($codigo)) {
                    $stats['errores'][] = "ENTRADA fila {$r}: código no numérico, omitida";
                    continue;
                }

                $fecha = $this->parseDateOrNull($this->cellValue($entSheet, $colFecha, $r));

                $producto = Producto::firstOrCreate(
                    ['codigo_barras' => $codigo],
                    [
                        'nombre' => trim((string) $this->cellValue($entSheet, $colNombre, $r)) ?: $codigo,
                        'marca'  => trim((string) $this->cellValue($entSheet, $colMarca, $r)) ?: null,
                        'linea'  => trim((string) $this->cellValue($entSheet, $colLinea, $r)) ?: null,
                        'costo'  => 0,
                    ]
                );
                if ($producto->wasRecentlyCreated) {
                    $stats['productos_nuevos']++;
                }

                Entrada::create(['codigo_barras' => $codigo, 'sucursal_id' => $sucursalId, 'unidades' => $unidades, 'fecha' => $fecha]);
                $stats['entradas']++;
            }
        }

        // Paso 3: SALIDAS → movimientos de salida
        if ($salSheet = $sheets['SALIDAS'] ?? null) {
            $cols        = $this->buildHeaderMap($salSheet);
            $colCodigo   = $this->findColumn($cols, ['CODIGO DE BARRAS', 'CODIGO DE BARRA']);
            $colNombre   = $cols['NOMBRE'] ?? null;
            $colMarca    = $cols['MARCA'] ?? null;
            $colLinea    = $cols['LINEA'] ?? null;
            $colUnidades = $cols['UNIDADES'] ?? null;
            $colFecha    = $cols['FECHA'] ?? null;
            $colDestino  = $cols['DESTINO'] ?? null;

            for ($r = 2; $r <= $salSheet->getHighestRow(); $r++) {
                $codigo   = $this->normalizeCodigo($this->cellValue($salSheet, $colCodigo, $r));
                $unidades = (int) $this->cellValue($salSheet, $colUnidades, $r);
                if (!$codigo || !$unidades) continue;
                if (!ctype_digit($codigo)) {
                    $stats['errores'][] = "SALIDAS fila {$r}: código no numérico, omitida";
                    continue;
                }

                $fecha   = $this->parseDateOrNull($this->cellValue($salSheet, $colFecha, $r));
                $destino = trim((string) $this->cellValue($salSheet, $colDestino, $r)) ?: null;

                $producto = Producto::firstOrCreate(
                    ['codigo_barras' => $codigo],
                    [
                        'nombre' => trim((string) $this->cellValue($salSheet, $colNombre, $r)) ?: $codigo,
                        'marca'  => trim((string) $this->cellValue($salSheet, $colMarca, $r)) ?: null,
                        'linea'  => trim((string) $this->cellValue($salSheet, $colLinea, $r)) ?: null,
                        'costo'  => 0,
                    ]
                );
                if ($producto->wasRecentlyCreated) {
                    $stats['productos_nuevos']++;
                }

                Salida::create(['codigo_barras' => $codigo, 'sucursal_id' => $sucursalId, 'unidades' => $unidades, 'fecha' => $fecha, 'destino' => $destino]);
                $stats['salidas']++;
            }
        }

        return redirect()->route('admin.inventario.importar')
            ->with('import_stats', $stats);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Un producto solo "existe" en una sucursal cuando tiene movimientos ahí (mismo
     * criterio para técnico y reventa). Al crear/editar un producto mientras hay una
     * sucursal activa, se ancla con un movimiento de 0 unidades en el/los pool(es)
     * correspondientes, para que aparezca de inmediato en esa sucursal (y solo en esa)
     * sin necesidad de registrar stock real primero.
     */
    private function asegurarPresenciaSucursal(Producto $producto, string $tipoStock): void
    {
        $sid = session('sucursal_activa_id');
        if (! $sid) {
            return;
        }

        $yaExiste = Entrada::where('codigo_barras', $producto->codigo_barras)
            ->where('tipo_stock', $tipoStock)
            ->where('sucursal_id', $sid)
            ->exists()
            || Salida::where('codigo_barras', $producto->codigo_barras)
                ->where('tipo_stock', $tipoStock)
                ->where('sucursal_id', $sid)
                ->exists();

        if (! $yaExiste) {
            Entrada::create([
                'codigo_barras' => $producto->codigo_barras,
                'sucursal_id'   => $sid,
                'tipo_stock'    => $tipoStock,
                'unidades'      => 0,
                'fecha'         => now(),
            ]);
        }
    }

    /**
     * Productos con su stock_actual calculado (entradas - salidas), respetando
     * la sucursal activa en sesión, para mostrarlo en los formularios de movimiento.
     */

    /**
     * Restringe un query de productos a los que tienen algún movimiento del pool
     * indicado en la sucursal activa. Sin sucursal activa (modo global) no filtra:
     * eso permite alcanzar cualquier producto para, por ejemplo, activarlo en una
     * sucursal nueva registrándole ahí su primera entrada.
     */
    private function scopeSucursal($query, string $tipoStock, string $tabla = 'productos')
    {
        $sid = session('sucursal_activa_id');
        if (! $sid) {
            return $query;
        }

        return $query->where(function ($qb) use ($sid, $tipoStock, $tabla) {
            $qb->whereExists(function ($sub) use ($sid, $tipoStock, $tabla) {
                $sub->selectRaw('1')->from('entradas')
                    ->whereColumn('entradas.codigo_barras', "{$tabla}.codigo_barras")
                    ->where('entradas.sucursal_id', $sid)
                    ->where('entradas.tipo_stock', $tipoStock);
            })->orWhereExists(function ($sub) use ($sid, $tipoStock, $tabla) {
                $sub->selectRaw('1')->from('salidas')
                    ->whereColumn('salidas.codigo_barras', "{$tabla}.codigo_barras")
                    ->where('salidas.sucursal_id', $sid)
                    ->where('salidas.tipo_stock', $tipoStock);
            });
        });
    }

    private function productosConStock(string $tipoStock = 'tecnico'): \Illuminate\Support\Collection
    {
        $sid    = session('sucursal_activa_id');
        $sidSql = $sid ? " AND sucursal_id = {$sid}" : '';

        $query = Producto::query()
            ->select('productos.*')
            ->selectRaw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE codigo_barras = productos.codigo_barras AND tipo_stock = '{$tipoStock}'{$sidSql})
                       - (SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE codigo_barras = productos.codigo_barras AND tipo_stock = '{$tipoStock}'{$sidSql}) AS stock_actual")
            ->when($tipoStock === 'reventa', fn ($q) => $q->where('es_reventa', true));

        return $this->scopeSucursal($query, $tipoStock)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Devuelve [marcas, lineas] para los dropdowns de filtro, restringidos a la
     * sucursal activa (mismo criterio que las listas de productos/movimientos).
     * Si se selecciona una marca, las lineas se filtran a las de esa marca.
     */
    private function filterOptions(?string $marcaSeleccionada, string $tipoStock = 'tecnico'): array
    {
        $marcas = $this->scopeSucursal(Producto::whereNotNull('marca'), $tipoStock)
            ->distinct()
            ->orderBy('marca')
            ->pluck('marca');

        $lineas = $this->scopeSucursal(Producto::whereNotNull('linea'), $tipoStock)
            ->when($marcaSeleccionada, fn ($q) => $q->where('marca', $marcaSeleccionada))
            ->distinct()
            ->orderBy('linea')
            ->pluck('linea');

        return [$marcas, $lineas];
    }

    /**
     * Mapea encabezados normalizados (trim + mayúsculas) de la fila 1 a su letra de columna.
     */
    private function buildHeaderMap($sheet): array
    {
        $map             = [];
        $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        for ($col = 1; $col <= $highestColIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $header    = strtoupper(trim((string) $sheet->getCell($colLetter . '1')->getValue()));
            if ($header !== '') {
                $map[$header] = $colLetter;
            }
        }

        return $map;
    }

    /**
     * Busca la primera cabecera candidata presente en el mapa (permite variantes como singular/plural).
     */
    private function findColumn(array $headerMap, array $candidatos): ?string
    {
        foreach ($candidatos as $c) {
            if (isset($headerMap[$c])) {
                return $headerMap[$c];
            }
        }

        return null;
    }

    private function cellValue($sheet, ?string $colLetter, int $row): mixed
    {
        if ($colLetter === null) {
            return null;
        }

        return $sheet->getCell($colLetter . $row)->getValue();
    }

    private function normalizeCodigo(mixed $value): string
    {
        if ($value === null || $value === '') return '';
        if (is_float($value) || is_int($value)) {
            return number_format((float) $value, 0, '.', '');
        }
        return trim((string) $value);
    }

    /**
     * Igual que parseDate() pero devuelve null en vez de lanzar excepción cuando
     * la celda está vacía o no se puede interpretar (movimiento "sin fecha").
     */
    private function parseDateOrNull(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            return $this->parseDate($value);
        } catch (\Exception $e) {
            return null;
        }
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

    public function limpiar()
    {
        $productos = DB::table('productos')->get();
        $entradas  = DB::table('entradas')->get();
        $salidas   = DB::table('salidas')->get();

        InventarioBackup::create([
            'user_id'         => auth()->id(),
            'productos_count' => $productos->count(),
            'entradas_count'  => $entradas->count(),
            'salidas_count'   => $salidas->count(),
            'datos'           => [
                'productos' => $productos,
                'entradas'  => $entradas,
                'salidas'   => $salidas,
            ],
        ]);

        DB::table('consumos_material')->delete();
        DB::table('alertas_stock')->delete();
        DB::table('salidas')->delete();
        DB::table('entradas')->delete();
        DB::table('servicio_materiales')->delete();
        DB::table('productos')->delete();

        return redirect()->route('admin.inventario.index')
            ->with('success', 'Todos los datos de inventario han sido eliminados. Se guardó un backup con lo eliminado.');
    }

    public function backups()
    {
        $backups = InventarioBackup::with('user')->latest()->paginate(20);

        return view('admin.inventario.backups.index', compact('backups'));
    }

    public function verBackup(InventarioBackup $backup)
    {
        return view('admin.inventario.backups.show', compact('backup'));
    }
}
