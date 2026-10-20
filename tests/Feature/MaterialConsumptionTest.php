<?php

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
use App\Models\Sucursal;
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
            'producto_id'   => $this->producto->id,
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

    private function crearCitaCompletada(?Sucursal $sucursal = null): Cita
    {
        $cita = Cita::create([
            'sucursal_id' => $sucursal?->id,
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
        for ($i = 0; $i < 2; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada());
        }
        $this->assertEquals(0, Salida::count());

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

        for ($i = 0; $i < 3; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada());
        }
        $this->assertEquals(1, AlertaStock::count());

        for ($i = 0; $i < 3; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada());
        }

        $this->assertEquals(1, AlertaStock::where('leida', false)->count());
    }

    public function test_procesarcita_is_idempotent(): void
    {
        $cita = $this->crearCitaCompletada();
        $this->service->procesarCita($cita);
        $this->service->procesarCita($cita);

        $this->assertEquals(1, ConsumoMaterial::count());
    }

    /** Producto SH001 en dos sucursales, cada uno con su stock; el material apunta al de A. */
    private function dosSucursales(): array
    {
        $a = Sucursal::create(['nombre' => 'Central', 'es_principal' => true]);
        $b = Sucursal::create(['nombre' => 'Norte']);
        $this->producto->update(['sucursal_id' => $a->id]);

        $enB = Producto::create([
            'sucursal_id' => $b->id, 'codigo_barras' => 'SH001', 'nombre' => 'Shampoo 1L',
            'costo' => 50, 'stock_minimo' => 0,
        ]);
        Entrada::create(['producto_id' => $enB->id, 'unidades' => 10, 'fecha' => today()]);

        return [$a, $b, $enB];
    }

    public function test_usos_de_distintas_sucursales_no_se_mezclan(): void
    {
        [$a, $b, $enB] = $this->dosSucursales();

        // usos_por_unidad = 3: dos usos en A + dos en B no completan una unidad en ninguna.
        foreach ([$a, $a, $b, $b] as $sucursal) {
            $this->service->procesarCita($this->crearCitaCompletada($sucursal));
        }

        $this->assertEquals(0, Salida::count());
        $this->assertEquals(10, $this->producto->stock());
        $this->assertEquals(10, $enB->stock());
        $this->assertEquals(1, $this->service->calcularUsosRestantesEnUnidad($this->material, $a->id));
    }

    public function test_cada_sucursal_descuenta_de_su_propio_producto(): void
    {
        [$a, $b, $enB] = $this->dosSucursales();

        for ($i = 0; $i < 3; $i++) {
            $this->service->procesarCita($this->crearCitaCompletada($b));
        }

        $this->assertEquals(10, $this->producto->stock());
        $this->assertEquals(9, $enB->stock());
        $this->assertDatabaseHas('salidas', ['producto_id' => $enB->id, 'sucursal_id' => $b->id, 'unidades' => 1]);
        $this->assertEquals(3, $this->service->calcularUsosRestantesEnUnidad($this->material, $a->id));
    }

    public function test_bajar_usos_devuelve_la_unidad_a_la_misma_sucursal(): void
    {
        [, $b, $enB] = $this->dosSucursales();

        $citas = collect(range(1, 3))->map(fn () => $this->crearCitaCompletada($b));
        $citas->each(fn ($c) => $this->service->procesarCita($c));
        $this->assertEquals(9, $enB->stock());

        $consumo = ConsumoMaterial::where('cita_id', $citas->last()->id)->first();
        $this->service->actualizarUsos($consumo, 0);

        $this->assertEquals(10, $enB->stock());
        $this->assertEquals(10, $this->producto->stock());
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
