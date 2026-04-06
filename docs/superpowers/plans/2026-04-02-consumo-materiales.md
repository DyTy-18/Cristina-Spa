# Consumo Automático de Materiales — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Al completar una cita, descontar automáticamente del inventario los materiales usados en cada servicio y emitir alertas de stock bajo en el panel y como mensaje WPP preparado.

**Architecture:** Cada `ServicioMaterial` tiene `usos_por_unidad` (cuántas citas rinde 1 unidad). Al completar una cita se insertan `ConsumoMaterial` por cada material usado. Cuando el total acumulado llega al múltiplo de `usos_por_unidad` se crea una `Salida` automática y se evalúa stock bajo. Si el stock ≤ `stock_minimo` se crea una `AlertaStock` visible en inventario con botón "Copiar msg WPP".

**Tech Stack:** Laravel 12, MySQL, Blade, Vanilla JS. Sin librerías nuevas.

---

## File Map

| Archivo | Acción | Responsabilidad |
|---|---|---|
| `database/migrations/2026_04_02_000001_add_usos_por_unidad_to_servicio_materiales.php` | Crear | Columna `usos_por_unidad` en pivot |
| `database/migrations/2026_04_02_000002_create_consumos_material_table.php` | Crear | Historial de consumos |
| `database/migrations/2026_04_02_000003_create_alertas_stock_table.php` | Crear | Alertas de stock bajo |
| `app/Models/ConsumoMaterial.php` | Crear | Modelo de consumo |
| `app/Models/AlertaStock.php` | Crear | Modelo de alerta + mensaje WPP |
| `app/Models/ServicioMaterial.php` | Modificar | Agregar `usos_por_unidad` + relación `consumos()` |
| `app/Services/MaterialConsumptionService.php` | Crear | Lógica central de consumo |
| `app/Http/Controllers/Admin/AlertaStockController.php` | Crear | PATCH leer alerta |
| `app/Http/Controllers/Admin/CitaController.php` | Modificar | Llamar servicio al completar |
| `app/Http/Controllers/Admin/ClienteController.php` | Modificar | Llamar servicio al completar |
| `app/Http/Controllers/Admin/ServicioMaterialController.php` | Modificar | CRUD `usos_por_unidad` |
| `app/Http/Controllers/Admin/InventarioController.php` | Modificar | Pasar alertas a la vista |
| `app/Providers/AppServiceProvider.php` | Modificar | View composer para badge |
| `routes/web.php` | Modificar | Ruta PATCH alertas-stock |
| `resources/views/admin/servicios/edit.blade.php` | Modificar | Campo y columna `usos_por_unidad` |
| `resources/views/admin/inventario/index.blade.php` | Modificar | Bloque de alertas + JS |
| `resources/views/admin/layouts/app.blade.php` | Modificar | Badge en nav Inventario |
| `tests/Feature/MaterialConsumptionTest.php` | Crear | 7 tests de cobertura |

---

### Task 1: Tres migraciones

**Files:**
- Create: `database/migrations/2026_04_02_000001_add_usos_por_unidad_to_servicio_materiales.php`
- Create: `database/migrations/2026_04_02_000002_create_consumos_material_table.php`
- Create: `database/migrations/2026_04_02_000003_create_alertas_stock_table.php`

- [ ] **Step 1: Crear migración usos_por_unidad**

```php
<?php
// database/migrations/2026_04_02_000001_add_usos_por_unidad_to_servicio_materiales.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicio_materiales', function (Blueprint $table) {
            $table->unsignedInteger('usos_por_unidad')->default(1)->after('unidad');
        });
    }

    public function down(): void
    {
        Schema::table('servicio_materiales', function (Blueprint $table) {
            $table->dropColumn('usos_por_unidad');
        });
    }
};
```

- [ ] **Step 2: Crear migración consumos_material**

```php
<?php
// database/migrations/2026_04_02_000002_create_consumos_material_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumos_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_material_id')
                  ->constrained('servicio_materiales')
                  ->cascadeOnDelete();
            $table->foreignId('cita_id')
                  ->constrained('citas')
                  ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumos_material');
    }
};
```

- [ ] **Step 3: Crear migración alertas_stock**

```php
<?php
// database/migrations/2026_04_02_000003_create_alertas_stock_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();
            $table->integer('stock_actual');
            $table->boolean('leida')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_stock');
    }
};
```

- [ ] **Step 4: Correr migraciones**

```bash
php artisan migrate
```

Expected: `Running migrations... 3 migrations DONE`

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_04_02_000001_add_usos_por_unidad_to_servicio_materiales.php
git add database/migrations/2026_04_02_000002_create_consumos_material_table.php
git add database/migrations/2026_04_02_000003_create_alertas_stock_table.php
git commit -m "feat: migrations for material consumption tracking"
```

---

### Task 2: Tests (escribir primero, deben fallar)

**Files:**
- Create: `tests/Feature/MaterialConsumptionTest.php`

- [ ] **Step 1: Escribir el archivo de tests**

```php
<?php
// tests/Feature/MaterialConsumptionTest.php
namespace Tests\Feature;

use App\Models\AlertaStock;
use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\ConsumoMaterial;
use App\Models\Entrada;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\Servicio;
use App\Models\ServicioMaterial;
use App\Models\User;
use App\Services\MaterialConsumptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialConsumptionTest extends TestCase
{
    use RefreshDatabase;

    private MaterialConsumptionService $service;
    private Producto $producto;
    private Servicio $servicio;
    private ServicioMaterial $material;
    private Cliente $cliente;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->service = new MaterialConsumptionService();

        $this->producto = Producto::create([
            'codigo_barras' => 'SH001',
            'nombre'        => 'Shampoo 1L',
            'marca'         => 'Kérastase',
            'costo'         => 50,
            'stock_minimo'  => 3,
        ]);

        Entrada::create([
            'codigo_barras' => 'SH001',
            'unidades'      => 10,
            'fecha'         => today(),
        ]);

        $this->servicio = Servicio::create([
            'nombre'           => 'Lavado',
            'precio'           => 80,
            'duracion_minutos' => 15,
            'activo'           => true,
        ]);

        $this->material = ServicioMaterial::create([
            'servicio_id'     => $this->servicio->id,
            'producto_id'     => $this->producto->id,
            'cantidad'        => 100,
            'unidad'          => 'ml',
            'usos_por_unidad' => 3,
        ]);

        $this->cliente = Cliente::create([
            'nombre'   => 'Ana',
            'apellido' => 'García',
            'telefono' => '70000000',
        ]);
    }

    private function crearCitaCompletada(): Cita
    {
        $cita = Cita::create([
            'cliente_id' => $this->cliente->id,
            'fecha'      => today(),
            'hora'       => '10:00',
            'estado'     => 'completada',
        ]);
        CitaServicio::create([
            'cita_id'     => $cita->id,
            'servicio_id' => $this->servicio->id,
        ]);
        return $cita;
    }

    public function test_completing_cita_creates_consumos(): void
    {
        $cita = $this->crearCitaCompletada();
        $this->service->procesarCita($cita);

        $this->assertDatabaseHas('consumos_material', [
            'servicio_material_id' => $this->material->id,
            'cita_id'              => $cita->id,
        ]);
    }

    public function test_reaching_usos_creates_salida(): void
    {
        // 2 consumos previos (necesitamos 3 en total para trigger)
        for ($i = 0; $i < 2; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada());
        }
        $this->assertEquals(0, Salida::count());

        // 3er consumo debe crear salida
        $this->service->procesarCita($this->crearCitaCompletada());

        $this->assertEquals(1, Salida::count());
        $this->assertDatabaseHas('salidas', [
            'codigo_barras' => 'SH001',
            'unidades'      => 1,
            'destino'       => 'consumo_servicio',
        ]);
    }

    public function test_not_reaching_multiple_no_salida(): void
    {
        $this->service->procesarCita($this->crearCitaCompletada());

        $this->assertEquals(0, Salida::count());
    }

    public function test_low_stock_creates_alerta(): void
    {
        // stock_minimo=10, stock inicial=10 → 1 salida lo deja en 9 ≤ 10
        $this->producto->update(['stock_minimo' => 10]);

        for ($i = 0; $i < 3; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada());
        }

        $this->assertEquals(1, AlertaStock::count());
        $this->assertDatabaseHas('alertas_stock', [
            'producto_id' => $this->producto->id,
            'leida'       => false,
        ]);
    }

    public function test_no_duplicate_alerta_if_unread_exists(): void
    {
        $this->producto->update(['stock_minimo' => 10]);

        // Primera alerta (3 consumos = 1 salida)
        for ($i = 0; $i < 3; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada());
        }
        $this->assertEquals(1, AlertaStock::count());

        // Segunda posible alerta (6 consumos = 2 salidas)
        for ($i = 0; $i < 3; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada());
        }

        // Solo 1 alerta no leída
        $this->assertEquals(1, AlertaStock::where('leida', false)->count());
    }

    public function test_procesarcita_is_idempotent(): void
    {
        $cita = $this->crearCitaCompletada();
        $this->service->procesarCita($cita);
        $this->service->procesarCita($cita); // segunda llamada

        $this->assertEquals(1, ConsumoMaterial::count());
    }

    public function test_leer_alerta_marks_as_read(): void
    {
        $alerta = AlertaStock::create([
            'producto_id'  => $this->producto->id,
            'stock_actual' => 2,
            'leida'        => false,
        ]);

        $this->actingAs($this->admin)
            ->patchJson("/admin/alertas-stock/{$alerta->id}/leer")
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('alertas_stock', [
            'id'    => $alerta->id,
            'leida' => true,
        ]);
    }
}
```

- [ ] **Step 2: Correr tests para verificar que fallan**

```bash
composer test -- --filter=MaterialConsumptionTest
```

Expected: FAIL (class not found errors) — confirmado que los tests están correctamente escritos antes de implementar.

---

### Task 3: Modelos

**Files:**
- Create: `app/Models/ConsumoMaterial.php`
- Create: `app/Models/AlertaStock.php`
- Modify: `app/Models/ServicioMaterial.php`

- [ ] **Step 1: Crear ConsumoMaterial**

```php
<?php
// app/Models/ConsumoMaterial.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumoMaterial extends Model
{
    protected $table = 'consumos_material';

    protected $fillable = ['servicio_material_id', 'cita_id'];

    public function servicioMaterial(): BelongsTo
    {
        return $this->belongsTo(ServicioMaterial::class);
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }
}
```

- [ ] **Step 2: Crear AlertaStock**

```php
<?php
// app/Models/AlertaStock.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaStock extends Model
{
    protected $table = 'alertas_stock';

    protected $fillable = ['producto_id', 'stock_actual', 'leida'];

    protected $casts = ['leida' => 'boolean'];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function getMensajeAttribute(): string
    {
        $p = $this->producto;
        $marca = $p->marca ? " ({$p->marca})" : '';
        return "⚠️ Stock bajo: {$p->nombre}{$marca}\nStock actual: {$this->stock_actual} unidades\nMínimo recomendado: {$p->stock_minimo}";
    }
}
```

- [ ] **Step 3: Actualizar ServicioMaterial — agregar usos_por_unidad y relación consumos**

El archivo actualmente tiene:
```php
protected $fillable = [
    'servicio_id',
    'producto_id',
    'cantidad',
    'unidad',
];

protected $casts = [
    'cantidad' => 'decimal:2',
];
```

Reemplazar con:
```php
protected $fillable = [
    'servicio_id',
    'producto_id',
    'cantidad',
    'unidad',
    'usos_por_unidad',
];

protected $casts = [
    'cantidad'        => 'decimal:2',
    'usos_por_unidad' => 'integer',
];
```

Y agregar la relación al final, antes del cierre de clase:
```php
public function consumos(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(ConsumoMaterial::class);
}
```

- [ ] **Step 4: Correr tests**

```bash
composer test -- --filter=MaterialConsumptionTest
```

Expected: algunos tests aún fallan (service no existe), pero los de modelos básicos ya pasan.

- [ ] **Step 5: Commit**

```bash
git add app/Models/ConsumoMaterial.php app/Models/AlertaStock.php app/Models/ServicioMaterial.php
git commit -m "feat: models ConsumoMaterial, AlertaStock; add usos_por_unidad to ServicioMaterial"
```

---

### Task 4: MaterialConsumptionService

**Files:**
- Create: `app/Services/MaterialConsumptionService.php`

- [ ] **Step 1: Crear el servicio**

```php
<?php
// app/Services/MaterialConsumptionService.php
namespace App\Services;

use App\Models\AlertaStock;
use App\Models\Cita;
use App\Models\ConsumoMaterial;
use App\Models\Entrada;
use App\Models\Salida;

class MaterialConsumptionService
{
    public function procesarCita(Cita $cita): void
    {
        if ($cita->estado !== 'completada') {
            return;
        }

        $cita->load(['citaServicios.servicio.materiales.producto']);

        foreach ($cita->citaServicios as $citaServicio) {
            if (!$citaServicio->servicio) {
                continue;
            }

            foreach ($citaServicio->servicio->materiales as $material) {
                // Idempotente: skip si ya se procesó esta cita para este material
                if (ConsumoMaterial::where('servicio_material_id', $material->id)
                                   ->where('cita_id', $cita->id)
                                   ->exists()) {
                    continue;
                }

                ConsumoMaterial::create([
                    'servicio_material_id' => $material->id,
                    'cita_id'              => $cita->id,
                ]);

                $totalConsumos = ConsumoMaterial::where('servicio_material_id', $material->id)->count();
                $usosPorUnidad = max(1, (int) ($material->usos_por_unidad ?? 1));

                if ($totalConsumos % $usosPorUnidad === 0) {
                    Salida::create([
                        'codigo_barras' => $material->producto->codigo_barras,
                        'unidades'      => 1,
                        'fecha'         => today(),
                        'destino'       => 'consumo_servicio',
                    ]);

                    $stockActual = $this->calcularStock($material->producto->codigo_barras);

                    if ($stockActual <= $material->producto->stock_minimo) {
                        $yaExiste = AlertaStock::where('producto_id', $material->producto_id)
                                              ->where('leida', false)
                                              ->exists();
                        if (!$yaExiste) {
                            AlertaStock::create([
                                'producto_id'  => $material->producto_id,
                                'stock_actual' => $stockActual,
                            ]);
                        }
                    }
                }
            }
        }
    }

    private function calcularStock(string $codigoBarras): int
    {
        $entradas = Entrada::where('codigo_barras', $codigoBarras)->sum('unidades');
        $salidas  = Salida::where('codigo_barras', $codigoBarras)->sum('unidades');
        return (int) ($entradas - $salidas);
    }
}
```

- [ ] **Step 2: Correr tests para verificar**

```bash
composer test -- --filter=MaterialConsumptionTest
```

Expected: los 6 primeros tests pasan. El séptimo (`test_leer_alerta_marks_as_read`) falla porque la ruta no existe aún.

- [ ] **Step 3: Commit**

```bash
git add app/Services/MaterialConsumptionService.php
git commit -m "feat: MaterialConsumptionService — core consumption logic"
```

---

### Task 5: AlertaStockController y ruta

**Files:**
- Create: `app/Http/Controllers/Admin/AlertaStockController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Crear AlertaStockController**

```php
<?php
// app/Http/Controllers/Admin/AlertaStockController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertaStock;

class AlertaStockController extends Controller
{
    public function leer(AlertaStock $alerta)
    {
        $alerta->update(['leida' => true]);
        return response()->json(['ok' => true]);
    }
}
```

- [ ] **Step 2: Agregar ruta en routes/web.php**

Dentro del grupo `Route::prefix('admin')->name('admin.')->middleware(['auth'])`, agregar junto a las rutas de inventario:

```php
Route::patch('alertas-stock/{alerta}/leer', [AlertaStockController::class, 'leer'])
     ->name('alertas-stock.leer');
```

También agregar el use al tope del archivo donde están los otros controllers:
```php
use App\Http\Controllers\Admin\AlertaStockController;
```

- [ ] **Step 3: Correr todos los tests**

```bash
composer test -- --filter=MaterialConsumptionTest
```

Expected: los 7 tests pasan. Output: `Tests: 7 passed`

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Admin/AlertaStockController.php routes/web.php
git commit -m "feat: AlertaStockController PATCH leer + route"
```

---

### Task 6: Integrar servicio en CitaController y ClienteController

**Files:**
- Modify: `app/Http/Controllers/Admin/CitaController.php`
- Modify: `app/Http/Controllers/Admin/ClienteController.php`

- [ ] **Step 1: CitaController — agregar use y llamada en update()**

Al tope del archivo, junto a los otros `use`:
```php
use App\Services\MaterialConsumptionService;
```

En el método `update()`, el bloque actual al final es (línea ~265):
```php
if ($estadoAnterior !== $cita->estado) {
    app(WppService::class)->notificarSegunEstado($cita);
}

return redirect()->route('admin.citas.show', $cita)
    ->with('success', 'Cita actualizada exitosamente.');
```

Reemplazar con:
```php
if ($estadoAnterior !== $cita->estado) {
    app(WppService::class)->notificarSegunEstado($cita);

    if ($cita->estado === 'completada') {
        app(MaterialConsumptionService::class)->procesarCita($cita);
    }
}

return redirect()->route('admin.citas.show', $cita)
    ->with('success', 'Cita actualizada exitosamente.');
```

- [ ] **Step 2: ClienteController — agregar use y llamada en updateCitaEstado()**

Al tope del archivo, junto a los otros `use`:
```php
use App\Services\MaterialConsumptionService;
```

En el método `updateCitaEstado()`, el bloque actual es:
```php
if ($estadoAnterior !== $cita->estado) {
    app(WppService::class)->notificarSegunEstado($cita);
}

return response()->json(['estado' => $cita->estado]);
```

Reemplazar con:
```php
if ($estadoAnterior !== $cita->estado) {
    app(WppService::class)->notificarSegunEstado($cita);

    if ($cita->estado === 'completada') {
        app(MaterialConsumptionService::class)->procesarCita($cita);
    }
}

return response()->json(['estado' => $cita->estado]);
```

- [ ] **Step 3: Verificar que los tests siguen pasando**

```bash
composer test -- --filter=MaterialConsumptionTest
```

Expected: `Tests: 7 passed`

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Admin/CitaController.php app/Http/Controllers/Admin/ClienteController.php
git commit -m "feat: trigger material consumption on cita completada"
```

---

### Task 7: ServicioMaterialController — soporte usos_por_unidad

**Files:**
- Modify: `app/Http/Controllers/Admin/ServicioMaterialController.php`

- [ ] **Step 1: Agregar usos_por_unidad en store()**

En `store()`, la validación actualmente es:
```php
$validated = $request->validate([
    'producto_id' => [...],
    'cantidad' => 'required|numeric|min:0.01',
    'unidad'   => 'required|string|max:30',
]);
```

Agregar campo y actualizar respuesta:
```php
$validated = $request->validate([
    'producto_id' => [
        'required',
        'integer',
        'exists:productos,id',
        Rule::unique('servicio_materiales')->where('servicio_id', $servicio->id),
    ],
    'cantidad'        => 'required|numeric|min:0.01',
    'unidad'          => 'required|string|max:30',
    'usos_por_unidad' => 'required|integer|min:1',
]);

$material = $servicio->materiales()->create($validated);
$material->load('producto');

return response()->json([
    'id'             => $material->id,
    'producto'       => [
        'id'     => $material->producto->id,
        'nombre' => $material->producto->nombre,
        'marca'  => $material->producto->marca,
    ],
    'cantidad'        => $material->cantidad,
    'unidad'          => $material->unidad,
    'usos_por_unidad' => $material->usos_por_unidad,
], 201);
```

- [ ] **Step 2: Agregar usos_por_unidad en update()**

La validación actualmente es:
```php
$validated = $request->validate([
    'cantidad' => 'required|numeric|min:0.01',
    'unidad'   => 'required|string|max:30',
]);

$material->update($validated);

return response()->json([
    'id'       => $material->id,
    'cantidad' => $material->cantidad,
    'unidad'   => $material->unidad,
]);
```

Reemplazar con:
```php
$validated = $request->validate([
    'cantidad'        => 'required|numeric|min:0.01',
    'unidad'          => 'required|string|max:30',
    'usos_por_unidad' => 'required|integer|min:1',
]);

$material->update($validated);

return response()->json([
    'id'              => $material->id,
    'cantidad'        => $material->cantidad,
    'unidad'          => $material->unidad,
    'usos_por_unidad' => $material->usos_por_unidad,
]);
```

- [ ] **Step 3: Verificar que los tests siguen pasando**

```bash
composer test -- --filter=MaterialConsumptionTest
```

Expected: `Tests: 7 passed`

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Admin/ServicioMaterialController.php
git commit -m "feat: add usos_por_unidad to ServicioMaterialController store/update"
```

---

### Task 8: Frontend — servicios/edit.blade.php

**Files:**
- Modify: `resources/views/admin/servicios/edit.blade.php`

La vista actualmente tiene la tabla de materiales y el JS IIFE. Hay 5 lugares a cambiar:

- [ ] **Step 1: Agregar columna en el `<thead>` de la tabla**

Encontrar:
```html
<th style="width:100px;">Unidad</th>
<th style="width:140px;">Acciones</th>
```

Reemplazar con:
```html
<th style="width:100px;">Unidad</th>
<th style="width:110px;">Usos/unidad</th>
<th style="width:140px;">Acciones</th>
```

- [ ] **Step 2: Agregar usos_por_unidad en el bloque @php $materialesData**

Encontrar:
```php
$materialesData = $materiales->map(fn($m) => [
    'id'       => $m->id,
    'producto' => ['id' => $m->producto->id, 'nombre' => $m->producto->nombre, 'marca' => $m->producto->marca],
    'cantidad' => $m->cantidad,
    'unidad'   => $m->unidad,
]);
```

Reemplazar con:
```php
$materialesData = $materiales->map(fn($m) => [
    'id'             => $m->id,
    'producto'       => ['id' => $m->producto->id, 'nombre' => $m->producto->nombre, 'marca' => $m->producto->marca],
    'cantidad'       => $m->cantidad,
    'unidad'         => $m->unidad,
    'usos_por_unidad'=> $m->usos_por_unidad,
]);
```

- [ ] **Step 3: Agregar celda en buildRow()**

Encontrar en la template string de `buildRow`:
```js
            <td>
                <input type="text" class="form-control form-control-sm edit-unidad"
                       value="${escHtml(m.unidad)}" list="unidades-list" style="width:80px;">
            </td>
            <td>
                <div class="actions">
```

Reemplazar con:
```js
            <td>
                <input type="text" class="form-control form-control-sm edit-unidad"
                       value="${escHtml(m.unidad)}" list="unidades-list" style="width:80px;">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm edit-usos"
                       value="${escHtml(m.usos_por_unidad)}" min="1" step="1" style="width:70px;">
            </td>
            <td>
                <div class="actions">
```

- [ ] **Step 4: Incluir usos_por_unidad en saveRow()**

Encontrar:
```js
    function saveRow(tr, m) {
        const cantidad = tr.querySelector('.edit-cantidad').value;
        const unidad   = tr.querySelector('.edit-unidad').value.trim();
        const errEl    = tr.querySelector('.row-error');
        errEl.style.display = 'none';

        fetch(`${BASE_URL}/${m.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ cantidad, unidad }),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                errEl.textContent = Object.values(data.errors || {}).flat().join(' ');
                errEl.style.display = '';
                return;
            }
            m.cantidad = data.cantidad;
            m.unidad   = data.unidad;
        })
```

Reemplazar con:
```js
    function saveRow(tr, m) {
        const cantidad        = tr.querySelector('.edit-cantidad').value;
        const unidad          = tr.querySelector('.edit-unidad').value.trim();
        const usos_por_unidad = tr.querySelector('.edit-usos').value;
        const errEl           = tr.querySelector('.row-error');
        errEl.style.display = 'none';

        fetch(`${BASE_URL}/${m.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ cantidad, unidad, usos_por_unidad }),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                errEl.textContent = Object.values(data.errors || {}).flat().join(' ');
                errEl.style.display = '';
                return;
            }
            m.cantidad        = data.cantidad;
            m.unidad          = data.unidad;
            m.usos_por_unidad = data.usos_por_unidad;
        })
```

- [ ] **Step 5: Agregar campo usos_por_unidad en el formulario de agregar**

Encontrar el bloque del form de agregar:
```html
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Unidad</label>
                        <input type="text" class="form-control" id="nuevo-unidad" list="unidades-list" placeholder="ej: ml">
                        <datalist id="unidades-list">
                            <option value="ml">
                            <option value="gr">
                            <option value="oz">
                            <option value="unidades">
                        </datalist>
                        <small id="add-error-unidad" style="color: var(--error-color); display:none;"></small>
                    </div>
                    <div class="form-group" style="flex:0; align-self: flex-end;">
                        <button type="button" class="btn btn-primary" id="btn-agregar-material">Agregar</button>
                    </div>
```

Reemplazar con:
```html
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Unidad</label>
                        <input type="text" class="form-control" id="nuevo-unidad" list="unidades-list" placeholder="ej: ml">
                        <datalist id="unidades-list">
                            <option value="ml">
                            <option value="gr">
                            <option value="oz">
                            <option value="unidades">
                        </datalist>
                        <small id="add-error-unidad" style="color: var(--error-color); display:none;"></small>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Usos/unidad</label>
                        <input type="number" class="form-control" id="nuevo-usos" min="1" step="1" value="1" placeholder="ej: 10">
                        <small id="add-error-usos" style="color: var(--error-color); display:none;"></small>
                    </div>
                    <div class="form-group" style="flex:0; align-self: flex-end;">
                        <button type="button" class="btn btn-primary" id="btn-agregar-material">Agregar</button>
                    </div>
```

- [ ] **Step 6: Incluir usos_por_unidad en el fetch POST y reset**

Encontrar en el event listener del botón agregar:
```js
        const productoId = document.getElementById('nuevo-producto-id').value;
        const cantidad   = document.getElementById('nuevo-cantidad').value;
        const unidad     = document.getElementById('nuevo-unidad').value.trim();
```

Reemplazar con:
```js
        const productoId      = document.getElementById('nuevo-producto-id').value;
        const cantidad        = document.getElementById('nuevo-cantidad').value;
        const unidad          = document.getElementById('nuevo-unidad').value.trim();
        const usos_por_unidad = document.getElementById('nuevo-usos').value;
```

Encontrar:
```js
        // Clear previous errors
        ['add-error-producto', 'add-error-cantidad', 'add-error-unidad', 'add-general-error']
```

Reemplazar con:
```js
        // Clear previous errors
        ['add-error-producto', 'add-error-cantidad', 'add-error-unidad', 'add-error-usos', 'add-general-error']
```

Encontrar en el cuerpo del fetch POST:
```js
            body: JSON.stringify({ producto_id: productoId, cantidad, unidad }),
```

Reemplazar con:
```js
            body: JSON.stringify({ producto_id: productoId, cantidad, unidad, usos_por_unidad }),
```

Encontrar en el handler de error:
```js
                if (errors.producto_id) showError('add-error-producto', errors.producto_id[0]);
                if (errors.cantidad)    showError('add-error-cantidad', errors.cantidad[0]);
                if (errors.unidad)      showError('add-error-unidad', errors.unidad[0]);
```

Reemplazar con:
```js
                if (errors.producto_id)    showError('add-error-producto', errors.producto_id[0]);
                if (errors.cantidad)       showError('add-error-cantidad', errors.cantidad[0]);
                if (errors.unidad)         showError('add-error-unidad', errors.unidad[0]);
                if (errors.usos_por_unidad) showError('add-error-usos', errors.usos_por_unidad[0]);
```

Encontrar el reset después de éxito:
```js
            document.getElementById('nuevo-producto-id').value = '';
            document.getElementById('nuevo-cantidad').value = '';
            document.getElementById('nuevo-unidad').value = '';
```

Reemplazar con:
```js
            document.getElementById('nuevo-producto-id').value = '';
            document.getElementById('nuevo-cantidad').value = '';
            document.getElementById('nuevo-unidad').value = '';
            document.getElementById('nuevo-usos').value = '1';
```

- [ ] **Step 7: Commit**

```bash
git add resources/views/admin/servicios/edit.blade.php
git commit -m "feat: add usos_por_unidad field to servicios/edit materials section"
```

---

### Task 9: Frontend — alertas en inventario/index.blade.php

**Files:**
- Modify: `app/Http/Controllers/Admin/InventarioController.php`
- Modify: `resources/views/admin/inventario/index.blade.php`

- [ ] **Step 1: Pasar alertas desde InventarioController::index()**

En `InventarioController.php`, agregar use al tope:
```php
use App\Models\AlertaStock;
```

En el método `index()`, el final actualmente es:
```php
return view('admin.inventario.index', compact('stock', 'q', 'marca', 'linea', 'marcas', 'lineas'));
```

Reemplazar con:
```php
$alertas = AlertaStock::noLeidas()->with('producto')->latest()->get();

return view('admin.inventario.index', compact('stock', 'q', 'marca', 'linea', 'marcas', 'lineas', 'alertas'));
```

- [ ] **Step 2: Agregar bloque de alertas en inventario/index.blade.php**

Localizar el comienzo de `@section('content')`. Inmediatamente después de la línea `@section('content')`, antes del `<div style="display:flex...">` de los botones, agregar:

```blade
@if($alertas->isNotEmpty())
    <div style="margin-bottom:1.5rem;">
        @foreach($alertas as $alerta)
        <div id="alerta-{{ $alerta->id }}"
             style="background:#fff8e1;border:1px solid #f59e0b;border-radius:6px;padding:.75rem 1rem;margin-bottom:.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div>
                <strong>⚠️ {{ $alerta->producto->nombre }}</strong>
                @if($alerta->producto->marca)
                    <span style="color:#888;">({{ $alerta->producto->marca }})</span>
                @endif
                — Stock actual: <strong>{{ $alerta->stock_actual }}</strong> uds.
                &nbsp;·&nbsp; Mínimo: {{ $alerta->producto->stock_minimo }}
            </div>
            <div style="display:flex;gap:.5rem;flex-shrink:0;">
                <button type="button" class="btn btn-outline btn-sm"
                        onclick="copiarMensajeWpp({{ json_encode($alerta->mensaje) }})">
                    Copiar msg WPP
                </button>
                <button type="button" class="btn btn-outline btn-sm"
                        onclick="marcarLeida({{ $alerta->id }})">
                    Marcar leída
                </button>
            </div>
        </div>
        @endforeach
    </div>
@endif
```

- [ ] **Step 3: Agregar el JS al final de la vista, antes de `@endsection`**

Al final de `@section('content')`, agregar `@push('scripts')`:

```blade
@push('scripts')
<script>
function copiarMensajeWpp(mensaje) {
    navigator.clipboard.writeText(mensaje).then(function () {
        alert('Mensaje copiado al portapapeles.');
    });
}
function marcarLeida(id) {
    fetch('/admin/alertas-stock/' + id + '/leer', {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.ok) {
            var el = document.getElementById('alerta-' + id);
            if (el) el.remove();
        }
    });
}
</script>
@endpush
```

- [ ] **Step 4: Verificar que la página de inventario carga sin errores**

```bash
php artisan route:list | grep alertas
```

Expected: `PATCH  admin/alertas-stock/{alerta}/leer`

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Admin/InventarioController.php resources/views/admin/inventario/index.blade.php
git commit -m "feat: alertas de stock bajo en inventario con copiar WPP y marcar leída"
```

---

### Task 10: Badge en sidebar — AppServiceProvider + app.blade.php

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `resources/views/admin/layouts/app.blade.php`

- [ ] **Step 1: Registrar View composer en AppServiceProvider**

El archivo actualmente tiene el método `boot()` vacío. Reemplazar con:

```php
use Illuminate\Support\Facades\View;

public function boot(): void
{
    View::composer('admin.*', function ($view) {
        if (auth()->check()) {
            $view->with('alertasStockCount', \App\Models\AlertaStock::where('leida', false)->count());
        }
    });
}
```

El `use Illuminate\Support\Facades\View;` va al tope del archivo, junto a los otros `use`.

- [ ] **Step 2: Agregar badge en el nav de Inventario en app.blade.php**

Localizar en el sidebar el enlace de inventario (alrededor de línea 139):
```html
                @if (auth()->user()->hasPermissionTo('ver inventario'))
                    <a href="{{ route('admin.inventario.index') }}"
                        class="nav-item {{ request()->routeIs('admin.inventario.*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span>
                        <span>Inventario</span>
                    </a>
                @endif
```

Reemplazar con:
```html
                @if (auth()->user()->hasPermissionTo('ver inventario'))
                    <a href="{{ route('admin.inventario.index') }}"
                        class="nav-item {{ request()->routeIs('admin.inventario.*') ? 'active' : '' }}">
                        <span class="nav-icon">📦</span>
                        <span>Inventario</span>
                        @if(($alertasStockCount ?? 0) > 0)
                            <span style="background:#f59e0b;color:#fff;border-radius:9999px;font-size:.62rem;font-weight:700;padding:.1rem .42rem;margin-left:auto;line-height:1.4;">{{ $alertasStockCount }}</span>
                        @endif
                    </a>
                @endif
```

- [ ] **Step 3: Limpiar caché de config y vistas**

```bash
php artisan config:clear && php artisan view:clear
```

- [ ] **Step 4: Correr todos los tests**

```bash
composer test -- --filter=MaterialConsumptionTest
```

Expected: `Tests: 7 passed`

- [ ] **Step 5: Commit**

```bash
git add app/Providers/AppServiceProvider.php resources/views/admin/layouts/app.blade.php
git commit -m "feat: stock alert badge in sidebar nav via View composer"
```
