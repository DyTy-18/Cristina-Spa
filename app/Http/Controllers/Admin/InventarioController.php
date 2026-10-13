<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertaStock;
use App\Models\Entrada;
use App\Models\InventarioBackup;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\ServicioMaterial;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        $tipoProducto = $this->tipoProducto($request);
        $sid       = session('sucursal_activa_id');

        // Cada producto pertenece a una sola sucursal: su stock son sus propios movimientos.
        $query = DB::table('productos as p')
            ->leftJoin('sucursales as su', 'su.id', '=', 'p.sucursal_id')
            ->select([
                'p.id', 'p.codigo_barras', 'p.nombre', 'p.marca', 'p.linea', 'p.costo', 'p.stock_minimo', 'p.es_reventa',
                'p.tipo', 'p.peso_gramos', 'p.sucursal_id', 'su.nombre as sucursal_nombre',
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE entradas.producto_id = p.id AND tipo_stock = '{$tipoStock}') AS total_entradas"),
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE salidas.producto_id = p.id AND tipo_stock = '{$tipoStock}') AS total_salidas"),
                DB::raw(Producto::stockSql($tipoStock, 'p') . ' AS stock_actual'),
            ])
            ->orderBy('p.nombre');

        // Técnico y reventa son catálogos distintos: vitrina solo en reventa, el resto solo en técnico.
        $query->where('p.es_reventa', $tipoStock === 'reventa');

        if ($sid) {
            $query->where('p.sucursal_id', $sid);
        }

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('p.nombre', 'like', "%{$q}%")
                    ->orWhere('p.codigo_barras', 'like', "%{$q}%");
            });
        }
        if ($marca) $query->where('p.marca', $marca);
        if ($linea)  $query->where('p.linea', $linea);
        if ($tipoProducto) $query->where('p.tipo', $tipoProducto);

        $stock = $query->get();

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        $alertas = $tipoStock === 'tecnico'
            ? AlertaStock::noLeidas()
                ->with('producto')
                ->when($sid, fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('sucursal_id', $sid)))
                ->latest()->get()
            : collect();

        return view('admin.inventario.index', compact('stock', 'q', 'marca', 'linea', 'tipoProducto', 'marcas', 'lineas', 'alertas', 'tipoStock'));
    }

    // ─── Productos ────────────────────────────────────────────────────────────

    public function productos(Request $request)
    {
        $q     = $request->input('q');
        $marca = $request->input('marca');
        $linea = $request->input('linea');
        $tipoStock = $request->input('tipo') === 'reventa' ? 'reventa' : 'tecnico';
        $tipoProducto = $this->tipoProducto($request);

        // Sucursal específica: solo su propio catálogo. En "todas las sucursales" se ven
        // los catálogos de todas, cada fila con su sucursal.
        $query = $this->scopeSucursal(Producto::with('sucursal')->orderBy('nombre'), $tipoStock);

        if ($q) {
            $query->where(function ($qb) use ($q) {
                $qb->where('nombre', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%");
            });
        }
        if ($marca) $query->where('marca', $marca);
        if ($linea)  $query->where('linea', $linea);
        if ($tipoProducto) $query->where('tipo', $tipoProducto);

        $productos = $query->get();

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        return view('admin.inventario.productos.index', compact('productos', 'q', 'marca', 'linea', 'tipoProducto', 'marcas', 'lineas', 'tipoStock'));
    }

    public function createProducto()
    {
        $sucursales = $this->sucursalesActivas();
        return view('admin.inventario.productos.create', compact('sucursales'));
    }

    public function storeProducto(Request $request)
    {
        $sid = $this->sucursalDestino($request);
        if (! $sid) {
            return back()->withErrors(['sucursal_id' => 'Elige la sucursal donde registrar el producto.'])->withInput();
        }

        $validated = $request->validate([
            'codigo_barras'       => ['required', 'string', 'max:100', Rule::unique('productos', 'codigo_barras')->where('sucursal_id', $sid)],
            'nombre'              => 'required|string|max:255',
            'marca'               => 'nullable|string|max:100',
            'linea'               => 'nullable|string|max:100',
            'costo'               => 'required|numeric|min:0',
            'precio_venta'        => 'nullable|numeric|min:0',
            'stock_minimo'        => 'required|integer|min:0',
            'es_reventa'          => 'nullable|boolean',
            'tipo'                => 'nullable|required_if:es_reventa,1|in:vitrina,seguimiento,generico',
            'peso_gramos'         => 'nullable|numeric|min:0.01',
            'unidades_iniciales'  => 'nullable|integer|min:0',
            'fecha_inicial'       => 'nullable|date',
        ], [
            'codigo_barras.unique' => 'Ese código de barras ya está registrado en esta sucursal.',
            'tipo.required_if'     => 'Elige el tipo del producto de reventa (vitrina, seguimiento o genérico).',
        ]);
        [$esReventa, $tipo, $peso] = $this->catalogoYTipo($request, $validated);
        $unidadesIniciales = (int) ($request->input('unidades_iniciales') ?? 0);
        $fechaInicial      = $request->input('fecha_inicial') ?: now()->toDateString();

        $producto = Producto::create([
            'sucursal_id'   => $sid,
            'codigo_barras' => $validated['codigo_barras'],
            'nombre'        => $validated['nombre'],
            'marca'         => $validated['marca'] ?? null,
            'linea'         => $validated['linea'] ?? null,
            'costo'         => $validated['costo'],
            'precio_venta'  => $validated['precio_venta'] ?? null,
            'stock_minimo'  => $validated['stock_minimo'],
            'es_reventa'    => $esReventa,
            'tipo'          => $tipo,
            'peso_gramos'   => $peso,
        ]);

        if ($unidadesIniciales > 0) {
            Entrada::create([
                'producto_id' => $producto->id,
                'tipo_stock'  => $esReventa ? 'reventa' : 'tecnico',
                'unidades'    => $unidadesIniciales,
                'fecha'       => $fechaInicial,
            ]);
        }

        return redirect()->route('admin.inventario.productos', ['tipo' => $esReventa ? 'reventa' : 'tecnico'])
            ->with('success', 'Producto registrado correctamente.');
    }

    public function editProducto(Producto $producto)
    {
        $this->autorizarSucursal($producto);

        return view('admin.inventario.productos.edit', compact('producto'));
    }

    public function updateProducto(Request $request, Producto $producto)
    {
        $this->autorizarSucursal($producto);

        $validated = $request->validate([
            'codigo_barras' => [
                'required', 'string', 'max:100',
                Rule::unique('productos', 'codigo_barras')->where('sucursal_id', $producto->sucursal_id)->ignore($producto->id),
            ],
            'nombre'        => 'required|string|max:255',
            'marca'         => 'nullable|string|max:100',
            'linea'         => 'nullable|string|max:100',
            'costo'         => 'required|numeric|min:0',
            'precio_venta'  => 'nullable|numeric|min:0',
            'stock_minimo'  => 'required|integer|min:0',
            'es_reventa'    => 'nullable|boolean',
            'tipo'          => 'nullable|required_if:es_reventa,1|in:vitrina,seguimiento,generico',
            'peso_gramos'   => 'nullable|numeric|min:0.01',
        ], [
            'codigo_barras.unique' => 'Ese código de barras ya está registrado en esta sucursal.',
            'tipo.required_if'     => 'Elige el tipo del producto de reventa (vitrina, seguimiento o genérico).',
        ]);
        [$validated['es_reventa'], $validated['tipo'], $validated['peso_gramos']] = $this->catalogoYTipo($request, $validated);

        // Cambiar entre técnico y reventa mueve el producto de catálogo; no se permite
        // si deja stock en el catálogo que abandona (quedaría oculto).
        if ($validated['es_reventa'] !== (bool) $producto->es_reventa) {
            $poolQueDeja = $producto->es_reventa ? 'reventa' : 'tecnico';
            $restante    = $producto->stock($poolQueDeja);
            if ($restante !== 0) {
                $catalogo = $poolQueDeja === 'reventa' ? 'reventa' : 'técnico';
                return back()->withErrors([
                    'es_reventa' => "No se puede cambiar de catálogo: el producto tiene {$restante} uds. en el stock {$catalogo}. Déjalo en 0 primero.",
                ])->withInput();
            }
        }

        $producto->update($validated);

        return redirect()->route('admin.inventario.productos', ['tipo' => $producto->es_reventa ? 'reventa' : 'tecnico'])
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
        $tipoProducto = $this->tipoProducto($request);

        $sid = session('sucursal_activa_id');

        $query = Entrada::with('producto.sucursal')
            ->where('tipo_stock', $tipoStock)
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($q || $marca || $linea || $tipoProducto) {
            $prodQ = Producto::query();
            if ($q) {
                $prodQ->where(function ($qb) use ($q) {
                    $qb->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo_barras', 'like', "%{$q}%");
                });
            }
            if ($marca) $prodQ->where('marca', $marca);
            if ($linea)  $prodQ->where('linea', $linea);
            if ($tipoProducto) $prodQ->where('tipo', $tipoProducto);
            $query->whereIn('producto_id', $prodQ->deSucursal($sid)->pluck('id'));
        }

        if ($desde) $query->where('fecha', '>=', $desde);
        if ($hasta)  $query->where('fecha', '<=', $hasta);

        $entradas = $query->paginate(20)->withQueryString();

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        return view('admin.inventario.entradas.index', compact(
            'entradas', 'q', 'marca', 'linea', 'tipoProducto', 'desde', 'hasta', 'marcas', 'lineas', 'tipoStock'
        ));
    }

    public function createEntrada()
    {
        $productos  = $this->productosConStock();
        $sucursales = $this->sucursalesActivas();
        return view('admin.inventario.entradas.create', compact('productos', 'sucursales'));
    }

    public function storeEntrada(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'unidades'    => 'required|integer|min:1',
            'fecha'       => 'required|date',
        ]);

        $producto = $this->productoDelFormulario($request);
        if ($producto->es_reventa) {
            return back()->withErrors(['producto_id' => 'Es un producto de reventa: registra su entrada en reventa.'])->withInput();
        }

        Entrada::create(array_merge($validated, ['tipo_stock' => 'tecnico']));

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
        $tipoProducto = $this->tipoProducto($request);

        $sid = session('sucursal_activa_id');

        $query = Salida::with('producto.sucursal')
            ->where('tipo_stock', $tipoStock)
            ->when($sid, fn($q) => $q->where('sucursal_id', $sid))
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($q || $marca || $linea || $tipoProducto) {
            $prodQ = Producto::query();
            if ($q) {
                $prodQ->where(function ($qb) use ($q) {
                    $qb->where('nombre', 'like', "%{$q}%")
                        ->orWhere('codigo_barras', 'like', "%{$q}%");
                });
            }
            if ($marca) $prodQ->where('marca', $marca);
            if ($linea)  $prodQ->where('linea', $linea);
            if ($tipoProducto) $prodQ->where('tipo', $tipoProducto);
            $query->whereIn('producto_id', $prodQ->deSucursal($sid)->pluck('id'));
        }

        if ($desde)   $query->where('fecha', '>=', $desde);
        if ($hasta)    $query->where('fecha', '<=', $hasta);
        if ($destino)  $query->where('destino', 'like', "%{$destino}%");

        $salidas = $query->paginate(20)->withQueryString();

        [$marcas, $lineas] = $this->filterOptions($marca, $tipoStock);

        return view('admin.inventario.salidas.index', compact(
            'salidas', 'q', 'marca', 'linea', 'tipoProducto', 'desde', 'hasta', 'destino', 'marcas', 'lineas', 'tipoStock'
        ));
    }

    public function createSalida()
    {
        $productos  = $this->productosConStock();
        $sucursales = $this->sucursalesActivas();
        return view('admin.inventario.salidas.create', compact('productos', 'sucursales'));
    }

    public function storeSalida(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'unidades'    => 'required|integer|min:1',
            'fecha'       => 'required|date',
            'destino'     => 'nullable|string|max:255',
        ]);

        $producto = $this->productoDelFormulario($request);
        if ($producto->es_reventa) {
            return back()->withErrors(['producto_id' => 'Es un producto de reventa: registra su salida en reventa.'])->withInput();
        }

        Salida::create(array_merge($validated, ['tipo_stock' => 'tecnico']));

        return redirect()->route('admin.inventario.salidas')
            ->with('success', 'Salida registrada correctamente.');
    }

    // ─── Reventa (segundo inventario, alimentado por transferencias) ──────────

    public function createTransferencia()
    {
        // Cualquier producto técnico con stock en la sucursal activa se puede transferir;
        // no hace falta que ya esté marcado como "de reventa" (la transferencia lo habilita).
        $productos  = $this->productosConStock('tecnico');
        $sucursales = $this->sucursalesActivas();
        return view('admin.inventario.reventa.transferir', compact('productos', 'sucursales'));
    }

    public function storeTransferencia(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'unidades'    => 'required|integer|min:1',
            'fecha'       => 'required|date',
        ]);

        $producto = $this->productoDelFormulario($request);
        if ($producto->es_reventa) {
            return back()->withErrors(['producto_id' => 'Ese producto ya es de reventa: carga su stock con una entrada de reventa.'])->withInput();
        }

        $disponible = $producto->stock('tecnico');
        if ($validated['unidades'] > $disponible) {
            return back()->withErrors(['unidades' => "Stock técnico insuficiente: disponible {$disponible}."])->withInput();
        }

        DB::transaction(function () use ($validated, $producto) {
            Salida::create([
                'producto_id' => $producto->id,
                'tipo_stock'  => 'tecnico',
                'unidades'    => $validated['unidades'],
                'fecha'       => $validated['fecha'],
                'destino'     => 'Transferencia a reventa',
            ]);

            Entrada::create([
                'producto_id' => $producto->id,
                'tipo_stock'  => 'reventa',
                'unidades'    => $validated['unidades'],
                'fecha'       => $validated['fecha'],
            ]);
        });

        // La transferencia convierte el producto en vitrina: pasa al catálogo de reventa
        // y sale del técnico.
        $producto->update(['es_reventa' => true, 'tipo' => Producto::TIPO_VITRINA, 'peso_gramos' => null]);

        $restante = $disponible - $validated['unidades'];
        $mensaje  = 'Transferencia a reventa registrada. El producto ahora es de vitrina.'
            . ($restante > 0 ? " Le quedaron {$restante} uds. en stock técnico, que ya no se muestran en el catálogo técnico." : '');

        return redirect()->route('admin.inventario.index', ['tipo' => 'reventa'])
            ->with('success', $mensaje);
    }

    public function createEntradaReventa()
    {
        $productos  = $this->productosConStock('reventa');
        $sucursales = $this->sucursalesActivas();
        return view('admin.inventario.reventa.entrada', compact('productos', 'sucursales'));
    }

    public function storeEntradaReventa(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'unidades'    => 'required|integer|min:1',
            'fecha'       => 'required|date',
        ]);

        $producto = $this->productoDelFormulario($request);
        if (! $producto->es_reventa) {
            return back()->withErrors(['producto_id' => 'Este producto no está habilitado para reventa.'])->withInput();
        }

        Entrada::create(array_merge($validated, ['tipo_stock' => 'reventa']));

        return redirect()->route('admin.inventario.entradas', ['tipo' => 'reventa'])
            ->with('success', 'Entrada de reventa registrada correctamente.');
    }

    public function createSalidaReventa()
    {
        $productos  = $this->productosConStock('reventa');
        $sucursales = $this->sucursalesActivas();
        return view('admin.inventario.reventa.salida', compact('productos', 'sucursales'));
    }

    public function storeSalidaReventa(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'unidades'    => 'required|integer|min:1',
            'fecha'       => 'required|date',
            'destino'     => 'nullable|string|max:255',
        ]);

        $producto = $this->productoDelFormulario($request);
        if (! $producto->es_reventa) {
            return back()->withErrors(['producto_id' => 'Este producto no está habilitado para reventa.'])->withInput();
        }

        Salida::create(array_merge($validated, ['tipo_stock' => 'reventa']));

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
        $entFiltro = $desde ? " AND fecha BETWEEN '{$desde}' AND '{$hasta}'" : '';
        $salFiltro = $desde ? " AND fecha BETWEEN '{$desde}' AND '{$hasta}'" : '';

        $query = DB::table('productos as p')
            ->select([
                'p.id', 'p.codigo_barras', 'p.nombre', 'p.marca', 'p.linea', 'p.stock_minimo', 'p.tipo',
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM entradas WHERE entradas.producto_id = p.id AND tipo_stock = 'tecnico'{$entFiltro}) AS total_entradas"),
                DB::raw("(SELECT COALESCE(SUM(unidades),0) FROM salidas  WHERE salidas.producto_id = p.id AND tipo_stock = 'tecnico'{$salFiltro}) AS total_salidas"),
                DB::raw(Producto::stockSql('tecnico', 'p') . ' AS stock_actual'),
                DB::raw("(SELECT MAX(fecha) FROM salidas WHERE salidas.producto_id = p.id AND tipo_stock = 'tecnico') AS ultima_salida"),
            ])
            ->when($sid, fn ($q) => $q->where('p.sucursal_id', $sid))
            ->where('p.es_reventa', false)
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

        $minFecha = DB::table('entradas')->when($sid, fn ($q) => $q->where('sucursal_id', $sid))->min('fecha');
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
                    ->selectRaw("s.producto_id, SUM(s.unidades) as hist_total, COUNT(DISTINCT substr(s.fecha, 1, 4)) as hist_años")
                    ->where('s.tipo_stock', 'tecnico')
                    ->whereRaw("({$condiciones})")
                    ->when($sid, fn ($q) => $q->where('s.sucursal_id', $sid))
                    ->groupBy('s.producto_id');

                if ($marca || $linea) {
                    $histQuery->join('productos as p', 'p.id', '=', 's.producto_id');
                    if ($marca) $histQuery->where('p.marca', $marca);
                    if ($linea) $histQuery->where('p.linea', $linea);
                }

                $histVentas = $histQuery->get()->keyBy('producto_id');

                foreach ($histVentas as $productoId => $hist) {
                    $prod = $productos->firstWhere('id', $productoId);
                    if (!$prod) continue;
                    $codigo = $prod->codigo_barras;

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

        // El Excel se importa al catálogo de UNA sucursal (la activa en el encabezado).
        $sucursalId = session('sucursal_activa_id');
        if (! $sucursalId) {
            return back()->withErrors(['excel' => 'Elige una sucursal en el encabezado antes de importar: el inventario se carga en esa sucursal.']);
        }

        $stats = ['productos_nuevos' => 0, 'entradas' => 0, 'salidas' => 0, 'errores' => []];

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
                    ['sucursal_id' => $sucursalId, 'codigo_barras' => $codigo],
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
                    ['sucursal_id' => $sucursalId, 'codigo_barras' => $codigo],
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

                Entrada::create(['producto_id' => $producto->id, 'unidades' => $unidades, 'fecha' => $fecha]);
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
                    ['sucursal_id' => $sucursalId, 'codigo_barras' => $codigo],
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

                Salida::create(['producto_id' => $producto->id, 'unidades' => $unidades, 'fecha' => $fecha, 'destino' => $destino]);
                $stats['salidas']++;
            }
        }

        return redirect()->route('admin.inventario.importar')
            ->with('import_stats', $stats);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Filtro ?tipo_producto= (vitrina / seguimiento / generico). Solo aplica al
     * catálogo de reventa: los productos técnicos no tienen tipo.
     */
    private function tipoProducto(Request $request): ?string
    {
        $tipo = $request->input('tipo_producto');

        return $request->input('tipo') === 'reventa' && array_key_exists($tipo ?? '', Producto::TIPOS) ? $tipo : null;
    }

    /**
     * [es_reventa, tipo, peso_gramos] desde el formulario de producto. El check
     * "Producto de reventa" define el catálogo; el tipo solo existe en reventa.
     */
    private function catalogoYTipo(Request $request, array $validated): array
    {
        if (! $request->boolean('es_reventa')) {
            return [false, null, null];
        }

        $tipo = $validated['tipo'];
        if ($tipo === Producto::TIPO_SEGUIMIENTO && empty($validated['peso_gramos'])) {
            throw ValidationException::withMessages([
                'peso_gramos' => 'Indica el peso en gramos del producto (se usa para descontar el inventario en seguimiento).',
            ]);
        }

        return [true, $tipo, $tipo === Producto::TIPO_SEGUIMIENTO ? $validated['peso_gramos'] : null];
    }

    private function sucursalesActivas(): \Illuminate\Support\Collection
    {
        return Sucursal::where('activo', true)->orderBy('es_principal', 'desc')->orderBy('nombre')->get();
    }

    /**
     * Sucursal donde se registra un dato nuevo: la activa en sesión; en modo global,
     * la elegida en el formulario (admin) o la del propio usuario.
     */
    private function sucursalDestino(Request $request): ?int
    {
        $sid = session('sucursal_activa_id') ?: $request->integer('sucursal_id') ?: auth()->user()->sucursal_id;

        return $sid ? (int) $sid : null;
    }

    /** Un producto de otra sucursal no se puede ver ni editar desde la sucursal activa. */
    private function autorizarSucursal(Producto $producto): void
    {
        $sid = session('sucursal_activa_id');
        abort_if($sid && (int) $producto->sucursal_id !== (int) $sid, 404);
    }

    /**
     * Producto elegido en un formulario de movimiento. El movimiento siempre queda
     * en la sucursal del producto; se rechaza si no coincide con la sucursal activa
     * o con la elegida en el formulario (modo global).
     */
    private function productoDelFormulario(Request $request): Producto
    {
        $producto = Producto::findOrFail($request->integer('producto_id'));
        $sid      = $this->sucursalDestino($request);

        if ($sid && (int) $producto->sucursal_id !== $sid) {
            throw ValidationException::withMessages([
                'producto_id' => 'Ese producto pertenece al inventario de otra sucursal.',
            ]);
        }

        return $producto;
    }

    /**
     * Restringe un query de productos a la sucursal activa (sin filtro en modo
     * global) y al catálogo del pool: reventa = productos de vitrina, técnico = el resto.
     */
    private function scopeSucursal($query, string $tipoStock)
    {
        return $query
            ->deSucursal(session('sucursal_activa_id'))
            ->where('es_reventa', $tipoStock === 'reventa');
    }

    /**
     * Productos con su stock_actual calculado (entradas - salidas) del pool indicado,
     * de la sucursal activa, para mostrarlo en los formularios de movimiento.
     */
    private function productosConStock(string $tipoStock = 'tecnico'): \Illuminate\Support\Collection
    {
        $query = Producto::query()
            ->with('sucursal')
            ->select('productos.*')
            ->selectRaw(Producto::stockSql($tipoStock) . ' AS stock_actual');

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
        $sid = session('sucursal_activa_id');

        // Con sucursal activa solo se limpia el inventario de esa sucursal.
        $productoIds = Producto::deSucursal($sid)->pluck('id');

        $productos = DB::table('productos')->whereIn('id', $productoIds)->get();
        $entradas  = DB::table('entradas')->whereIn('producto_id', $productoIds)->get();
        $salidas   = DB::table('salidas')->whereIn('producto_id', $productoIds)->get();

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

        DB::transaction(function () use ($sid, $productoIds) {
            if ($sid) {
                // Los materiales de servicio son de todas las sucursales: si apuntan a un
                // producto de esta sucursal, se pasan al mismo código en otra sucursal.
                foreach (ServicioMaterial::with('producto')->whereIn('producto_id', $productoIds)->get() as $material) {
                    $otro = Producto::where('codigo_barras', $material->producto->codigo_barras)
                        ->whereNotIn('id', $productoIds)->value('id');
                    $yaUsado = $otro && ServicioMaterial::where('servicio_id', $material->servicio_id)->where('producto_id', $otro)->exists();
                    if ($otro && ! $yaUsado) {
                        $material->update(['producto_id' => $otro]);
                    }
                }
            }

            DB::table('consumos_material')
                ->whereIn('servicio_material_id', DB::table('servicio_materiales')->whereIn('producto_id', $productoIds)->select('id'))
                ->delete();
            DB::table('alertas_stock')->whereIn('producto_id', $productoIds)->delete();
            DB::table('salidas')->whereIn('producto_id', $productoIds)->delete();
            DB::table('entradas')->whereIn('producto_id', $productoIds)->delete();
            DB::table('servicio_materiales')->whereIn('producto_id', $productoIds)->delete();
            DB::table('productos')->whereIn('id', $productoIds)->delete();
        });

        $mensaje = $sid
            ? 'Se eliminó el inventario de esta sucursal. Se guardó un backup con lo eliminado.'
            : 'Todos los datos de inventario han sido eliminados. Se guardó un backup con lo eliminado.';

        return redirect()->route('admin.inventario.index')->with('success', $mensaje);
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
