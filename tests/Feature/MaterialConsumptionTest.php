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
