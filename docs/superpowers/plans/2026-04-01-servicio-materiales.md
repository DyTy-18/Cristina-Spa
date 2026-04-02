# Servicio Materiales Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a many-to-many relationship between services and inventory products, with quantity and unit of measure, manageable inline from the service edit page via AJAX.

**Architecture:** New `servicio_materiales` pivot table links `servicios` to `productos`. A dedicated `ServicioMaterialController` handles JSON AJAX endpoints (store/update/destroy). The service edit view gets a new section below the existing form with a vanilla-JS powered table for listing, editing inline, and adding materials.

**Tech Stack:** Laravel 12, PHP 8.2, MySQL, Blade, vanilla JS (`fetch` API), PHPUnit feature tests with `RefreshDatabase`.

---

## File Map

| File | Action | Purpose |
|---|---|---|
| `database/migrations/2026_04_01_000000_create_servicio_materiales_table.php` | Create | New pivot table |
| `app/Models/ServicioMaterial.php` | Create | Pivot model |
| `app/Models/Servicio.php` | Modify | Add `materiales()` relation |
| `app/Models/Producto.php` | Modify | Add `servicioMateriales()` relation |
| `app/Http/Controllers/Admin/ServicioMaterialController.php` | Create | AJAX store/update/destroy |
| `routes/web.php` | Modify | Register nested routes |
| `app/Http/Controllers/Admin/ServicioController.php` | Modify | Pass materiales+productos to edit view |
| `resources/views/admin/servicios/edit.blade.php` | Modify | Add materiales section with JS |
| `tests/Feature/ServicioMaterialTest.php` | Create | Feature tests |

---

## Task 1: Migration and Model

**Files:**
- Create: `database/migrations/2026_04_01_000000_create_servicio_materiales_table.php`
- Create: `app/Models/ServicioMaterial.php`
- Modify: `app/Models/Servicio.php`
- Modify: `app/Models/Producto.php`

- [ ] **Step 1: Create migration**

Create `database/migrations/2026_04_01_000000_create_servicio_materiales_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_materiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->decimal('cantidad', 8, 2);
            $table->string('unidad', 30);
            $table->timestamps();

            $table->unique(['servicio_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_materiales');
    }
};
```

- [ ] **Step 2: Run migration**

```bash
php artisan migrate
```

Expected: `Migrating: 2026_04_01_000000_create_servicio_materiales_table` then `Migrated`.

- [ ] **Step 3: Create ServicioMaterial model**

Create `app/Models/ServicioMaterial.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioMaterial extends Model
{
    protected $table = 'servicio_materiales';

    protected $fillable = [
        'servicio_id',
        'producto_id',
        'cantidad',
        'unidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
```

- [ ] **Step 4: Add relation to Servicio model**

In `app/Models/Servicio.php`, add after the `comisionesEmpleado()` method (around line 53):

```php
public function materiales()
{
    return $this->hasMany(ServicioMaterial::class);
}
```

- [ ] **Step 5: Add relation to Producto model**

In `app/Models/Producto.php`, add after the `salidas()` method (around line 31):

```php
public function servicioMateriales()
{
    return $this->hasMany(ServicioMaterial::class);
}
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_04_01_000000_create_servicio_materiales_table.php app/Models/ServicioMaterial.php app/Models/Servicio.php app/Models/Producto.php
git commit -m "feat: add servicio_materiales table and models"
```

---

## Task 2: Controller and Routes

**Files:**
- Create: `app/Http/Controllers/Admin/ServicioMaterialController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write failing tests first**

Create `tests/Feature/ServicioMaterialTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Servicio;
use App\Models\ServicioMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServicioMaterialTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Servicio $servicio;
    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles required by Spatie
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->servicio = Servicio::create([
            'nombre' => 'Corte',
            'precio' => 25,
            'duracion_minutos' => 30,
            'activo' => true,
        ]);

        $this->producto = Producto::create([
            'codigo_barras' => 'TEST001',
            'nombre' => 'Shampoo',
            'marca' => 'Marca X',
            'linea' => 'Pro',
            'costo' => 10,
            'stock_minimo' => 5,
        ]);
    }

    public function test_store_adds_material_to_servicio(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/admin/servicios/{$this->servicio->id}/materiales", [
                'producto_id' => $this->producto->id,
                'cantidad' => 30.5,
                'unidad' => 'ml',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'producto' => ['id', 'nombre', 'marca'], 'cantidad', 'unidad']);

        $this->assertDatabaseHas('servicio_materiales', [
            'servicio_id' => $this->servicio->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 30.5,
            'unidad' => 'ml',
        ]);
    }

    public function test_store_rejects_duplicate_producto(): void
    {
        ServicioMaterial::create([
            'servicio_id' => $this->servicio->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'unidad' => 'ml',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/admin/servicios/{$this->servicio->id}/materiales", [
                'producto_id' => $this->producto->id,
                'cantidad' => 20,
                'unidad' => 'ml',
            ]);

        $response->assertStatus(422);
    }

    public function test_update_changes_cantidad_and_unidad(): void
    {
        $material = ServicioMaterial::create([
            'servicio_id' => $this->servicio->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'unidad' => 'ml',
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/servicios/{$this->servicio->id}/materiales/{$material->id}", [
                'cantidad' => 50,
                'unidad' => 'gr',
            ]);

        $response->assertOk()
            ->assertJson(['id' => $material->id, 'cantidad' => '50.00', 'unidad' => 'gr']);

        $this->assertDatabaseHas('servicio_materiales', [
            'id' => $material->id,
            'cantidad' => 50,
            'unidad' => 'gr',
        ]);
    }

    public function test_destroy_removes_material(): void
    {
        $material = ServicioMaterial::create([
            'servicio_id' => $this->servicio->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'unidad' => 'ml',
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/admin/servicios/{$this->servicio->id}/materiales/{$material->id}");

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseMissing('servicio_materiales', ['id' => $material->id]);
    }

    public function test_guest_cannot_access_materiales(): void
    {
        $this->postJson("/admin/servicios/{$this->servicio->id}/materiales", [])
            ->assertStatus(401);
    }
}
```

- [ ] **Step 2: Run tests — expect them to fail (routes not defined)**

```bash
composer test -- --filter=ServicioMaterialTest
```

Expected: FAIL with "404 Not Found" or route-not-found errors.

- [ ] **Step 3: Create ServicioMaterialController**

Create `app/Http/Controllers/Admin/ServicioMaterialController.php`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\ServicioMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicioMaterialController extends Controller
{
    public function store(Request $request, Servicio $servicio): JsonResponse
    {
        $validated = $request->validate([
            'producto_id' => [
                'required',
                'integer',
                'exists:productos,id',
                Rule::unique('servicio_materiales')->where('servicio_id', $servicio->id),
            ],
            'cantidad' => 'required|numeric|min:0.01',
            'unidad'   => 'required|string|max:30',
        ]);

        $material = $servicio->materiales()->create($validated);
        $material->load('producto');

        return response()->json([
            'id'       => $material->id,
            'producto' => [
                'id'     => $material->producto->id,
                'nombre' => $material->producto->nombre,
                'marca'  => $material->producto->marca,
            ],
            'cantidad' => $material->cantidad,
            'unidad'   => $material->unidad,
        ], 201);
    }

    public function update(Request $request, Servicio $servicio, ServicioMaterial $material): JsonResponse
    {
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
    }

    public function destroy(Servicio $servicio, ServicioMaterial $material): JsonResponse
    {
        $material->delete();

        return response()->json(['success' => true]);
    }
}
```

- [ ] **Step 4: Register routes in web.php**

In `routes/web.php`, after the `Route::resource('servicios', ...)` line (line 81), add:

```php
// Materiales de servicio
Route::prefix('servicios/{servicio}/materiales')->name('servicios.materiales.')->group(function () {
    Route::post('/', [\App\Http\Controllers\Admin\ServicioMaterialController::class, 'store'])->name('store');
    Route::put('/{material}', [\App\Http\Controllers\Admin\ServicioMaterialController::class, 'update'])->name('update');
    Route::delete('/{material}', [\App\Http\Controllers\Admin\ServicioMaterialController::class, 'destroy'])->name('destroy');
});
```

- [ ] **Step 5: Run tests — expect them to pass**

```bash
composer test -- --filter=ServicioMaterialTest
```

Expected: All 5 tests PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/ServicioMaterialController.php routes/web.php tests/Feature/ServicioMaterialTest.php
git commit -m "feat: add ServicioMaterialController with store/update/destroy + tests"
```

---

## Task 3: Update ServicioController@edit

**Files:**
- Modify: `app/Http/Controllers/Admin/ServicioController.php`

- [ ] **Step 1: Update the edit method**

In `app/Http/Controllers/Admin/ServicioController.php`, replace the `edit` method (lines 51-54):

```php
public function edit(Servicio $servicio)
{
    $materiales = $servicio->materiales()->with('producto')->get();
    $productos = \App\Models\Producto::orderBy('nombre')->get();

    return view('admin.servicios.edit', compact('servicio', 'materiales', 'productos'));
}
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/ServicioController.php
git commit -m "feat: pass materiales and productos to servicio edit view"
```

---

## Task 4: Edit View — Materiales Section

**Files:**
- Modify: `resources/views/admin/servicios/edit.blade.php`

- [ ] **Step 1: Add the materiales section to the edit view**

In `resources/views/admin/servicios/edit.blade.php`, replace the entire `@endsection` at the end (line 85) with:

```blade
    {{-- ── Materiales del Servicio ─────────────────────────── --}}
    <div class="card" style="margin-top: 2rem;" id="materiales-section">
        <div class="card-header">
            <h3 class="card-title">Materiales del Servicio</h3>
        </div>
        <div class="card-body">

            {{-- Tabla de materiales existentes --}}
            <table class="data-table" id="materiales-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th style="width:120px;">Cantidad</th>
                        <th style="width:100px;">Unidad</th>
                        <th style="width:140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="materiales-tbody">
                    {{-- Rows injected by JS on load --}}
                </tbody>
            </table>

            <div id="materiales-empty" style="display:none; padding: 1.5rem 0; color: var(--text-light); text-align:center;">
                Sin materiales registrados aún.
            </div>

            {{-- Formulario para agregar material --}}
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.06);">
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; margin-bottom: 1rem; color: var(--primary-color);">
                    Agregar Material
                </h4>
                <div class="form-row" id="add-material-form">
                    <div class="form-group" style="flex:2;">
                        <label class="form-label">Producto</label>
                        <select class="form-control" id="nuevo-producto-id">
                            <option value="">— seleccionar producto —</option>
                            @foreach ($productos as $producto)
                                <option value="{{ $producto->id }}" data-nombre="{{ $producto->nombre }}" data-marca="{{ $producto->marca }}">
                                    {{ $producto->nombre }} ({{ $producto->marca }})
                                </option>
                            @endforeach
                        </select>
                        <small id="add-error-producto" style="color: var(--error-color); display:none;"></small>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="nuevo-cantidad" min="0.01" step="0.01" placeholder="ej: 30">
                        <small id="add-error-cantidad" style="color: var(--error-color); display:none;"></small>
                    </div>
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
                </div>
                <div id="add-general-error" style="color: var(--error-color); display:none; margin-top: 0.5rem;"></div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const SERVICIO_ID = {{ $servicio->id }};
    const BASE_URL    = '/admin/servicios/' + SERVICIO_ID + '/materiales';
    const CSRF        = '{{ csrf_token() }}';

    // State: list of materials loaded from PHP
    let materiales = @json($materiales->map(fn($m) => [
        'id'       => $m->id,
        'producto' => ['id' => $m->producto->id, 'nombre' => $m->producto->nombre, 'marca' => $m->producto->marca],
        'cantidad' => $m->cantidad,
        'unidad'   => $m->unidad,
    ]));

    // ── Render ────────────────────────────────────────────────
    function renderTable() {
        const tbody = document.getElementById('materiales-tbody');
        const empty = document.getElementById('materiales-empty');
        tbody.innerHTML = '';

        if (materiales.length === 0) {
            empty.style.display = '';
            document.getElementById('materiales-table').style.display = 'none';
        } else {
            empty.style.display = 'none';
            document.getElementById('materiales-table').style.display = '';
            materiales.forEach(m => tbody.appendChild(buildRow(m)));
        }

        refreshProductoSelect();
    }

    function buildRow(m) {
        const tr = document.createElement('tr');
        tr.dataset.id = m.id;
        tr.innerHTML = `
            <td><strong>${escHtml(m.producto.nombre)}</strong></td>
            <td>${escHtml(m.producto.marca)}</td>
            <td>
                <input type="number" class="form-control form-control-sm edit-cantidad"
                       value="${m.cantidad}" min="0.01" step="0.01" style="width:90px;">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm edit-unidad"
                       value="${escHtml(m.unidad)}" list="unidades-list" style="width:80px;">
            </td>
            <td>
                <div class="actions">
                    <button type="button" class="btn btn-outline btn-sm btn-save">Guardar</button>
                    <button type="button" class="btn btn-danger btn-sm btn-delete">Eliminar</button>
                </div>
                <small class="row-error" style="color:var(--error-color); display:none;"></small>
            </td>
        `;

        tr.querySelector('.btn-save').addEventListener('click', () => saveRow(tr, m));
        tr.querySelector('.btn-delete').addEventListener('click', () => deleteRow(tr, m));
        return tr;
    }

    function refreshProductoSelect() {
        const usedIds = new Set(materiales.map(m => m.producto.id));
        document.querySelectorAll('#nuevo-producto-id option[value]').forEach(opt => {
            opt.disabled = usedIds.has(parseInt(opt.value));
        });
    }

    // ── Save inline edit ──────────────────────────────────────
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
        .catch(() => {
            errEl.textContent = 'Error al guardar.';
            errEl.style.display = '';
        });
    }

    // ── Delete row ────────────────────────────────────────────
    function deleteRow(tr, m) {
        if (!confirm('¿Eliminar este material del servicio?')) return;

        fetch(`${BASE_URL}/${m.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                materiales = materiales.filter(x => x.id !== m.id);
                renderTable();
            }
        });
    }

    // ── Add new material ──────────────────────────────────────
    document.getElementById('btn-agregar-material').addEventListener('click', () => {
        const productoId = document.getElementById('nuevo-producto-id').value;
        const cantidad   = document.getElementById('nuevo-cantidad').value;
        const unidad     = document.getElementById('nuevo-unidad').value.trim();

        // Clear previous errors
        ['add-error-producto', 'add-error-cantidad', 'add-error-unidad', 'add-general-error']
            .forEach(id => { const el = document.getElementById(id); el.style.display = 'none'; el.textContent = ''; });

        fetch(BASE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ producto_id: productoId, cantidad, unidad }),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, status: r.status, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                const errors = data.errors || {};
                if (errors.producto_id) showError('add-error-producto', errors.producto_id[0]);
                if (errors.cantidad)    showError('add-error-cantidad', errors.cantidad[0]);
                if (errors.unidad)      showError('add-error-unidad', errors.unidad[0]);
                if (!Object.keys(errors).length) showError('add-general-error', data.message || 'Error al agregar.');
                return;
            }
            materiales.push(data);
            renderTable();
            document.getElementById('nuevo-producto-id').value = '';
            document.getElementById('nuevo-cantidad').value = '';
            document.getElementById('nuevo-unidad').value = '';
        })
        .catch(() => showError('add-general-error', 'Error de conexión.'));
    });

    function showError(id, msg) {
        const el = document.getElementById(id);
        el.textContent = msg;
        el.style.display = '';
    }

    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Initial render
    renderTable();
})();
</script>
@endpush
```

- [ ] **Step 2: Verify the page loads without errors**

Run the dev server and navigate to `http://localhost:8000/admin/servicios/{id}/editar` (replace `{id}` with an existing service ID). Check:
- No PHP errors in the page
- The "Materiales del Servicio" section appears below the form
- The empty state message shows if no materials exist

```bash
php artisan serve
```

- [ ] **Step 3: Manual smoke test — add a material**

In the browser:
1. Select a product from the dropdown, enter a quantity (e.g. 30) and unit (e.g. ml)
2. Click "Agregar"
3. Expected: the row appears in the table immediately, the dropdown refreshes and the product is now disabled

- [ ] **Step 4: Manual smoke test — edit and delete**

1. Change the cantidad or unidad of the row you just added and click "Guardar" — verify it saves without page reload
2. Click "Eliminar" → confirm → row disappears

- [ ] **Step 5: Commit**

```bash
git add resources/views/admin/servicios/edit.blade.php
git commit -m "feat: add materiales section to servicio edit view"
```

---

## Task 5: Final check

- [ ] **Step 1: Run full test suite**

```bash
composer test
```

Expected: All tests pass including `ServicioMaterialTest` (5 tests).

- [ ] **Step 2: Verify cascade delete works**

In MySQL or Tinker, confirm that deleting a `Servicio` also removes its `servicio_materiales` rows (FK cascadeOnDelete). You can verify with:

```bash
php artisan tinker
```

```php
$s = \App\Models\Servicio::first();
$s->materiales()->create(['producto_id' => \App\Models\Producto::first()->id, 'cantidad' => 5, 'unidad' => 'ml']);
$s->delete();
\App\Models\ServicioMaterial::where('servicio_id', $s->id)->count(); // should be 0
```

- [ ] **Step 3: Final commit**

```bash
git add -A
git commit -m "feat: servicio materiales — complete implementation"
```
