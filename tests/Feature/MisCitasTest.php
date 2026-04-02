<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\ConsumoMaterial;
use App\Models\Empleado;
use App\Models\Entrada;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\Servicio;
use App\Models\ServicioMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MisCitasTest extends TestCase
{
    use RefreshDatabase;

    private User $estilista;
    private Empleado $empleado;
    private Cliente $cliente;
    private Servicio $servicio;
    private ServicioMaterial $material;
    private Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->estilista = User::factory()->create();
        $this->estilista->assignRole('estilista');

        $this->empleado = Empleado::create([
            'nombre'   => 'Maria',
            'apellido' => 'Lopez',
            'telefono' => '70000001',
            'cargo'    => 'estilista',
            'activo'   => true,
            'user_id'  => $this->estilista->id,
        ]);

        $this->cliente = Cliente::create([
            'nombre'   => 'Ana',
            'apellido' => 'García',
            'telefono' => '70000000',
        ]);

        $this->servicio = Servicio::create([
            'nombre'           => 'Corte',
            'precio'           => 80,
            'duracion_minutos' => 30,
            'activo'           => true,
        ]);

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

        $this->material = ServicioMaterial::create([
            'servicio_id'     => $this->servicio->id,
            'producto_id'     => $this->producto->id,
            'cantidad'        => 100,
            'unidad'          => 'ml',
            'usos_por_unidad' => 3,
        ]);
    }

    private function crearCitaHoy(string $estado = 'completada', ?Empleado $empleado = null): Cita
    {
        $emp = $empleado ?? $this->empleado;

        $cita = Cita::create([
            'cliente_id' => $this->cliente->id,
            'fecha'      => today(),
            'hora'       => '10:00',
            'estado'     => $estado,
        ]);

        CitaServicio::create([
            'cita_id'     => $cita->id,
            'servicio_id' => $this->servicio->id,
            'empleado_id' => $emp->id,
        ]);

        return $cita;
    }

    private function crearConsumoParaCita(Cita $cita): ConsumoMaterial
    {
        return ConsumoMaterial::create([
            'servicio_material_id' => $this->material->id,
            'cita_id'              => $cita->id,
        ]);
    }

    /** Empleado ve solo sus citas de hoy */
    public function test_estilista_ve_sus_citas_de_hoy(): void
    {
        $citaPropia = $this->crearCitaHoy();

        // Cita de otro empleado
        $otroEmpleado = Empleado::create([
            'nombre'   => 'Pedro',
            'apellido' => 'Ramirez',
            'telefono' => '70000002',
            'cargo'    => 'estilista',
            'activo'   => true,
        ]);
        $this->crearCitaHoy('completada', $otroEmpleado);

        $response = $this->actingAs($this->estilista)->get('/admin/mis-citas');

        $response->assertOk();
        $response->assertSee($this->cliente->nombre);
        // Solo debe ver 1 fila de cita propia
        $this->assertCount(1, $response->viewData('citasHoy'));
    }

    /** Empleado no ve citas de otros días */
    public function test_estilista_no_ve_citas_de_otros_dias(): void
    {
        $citaAyer = Cita::create([
            'cliente_id' => $this->cliente->id,
            'fecha'      => today()->subDay(),
            'hora'       => '10:00',
            'estado'     => 'completada',
        ]);
        CitaServicio::create([
            'cita_id'     => $citaAyer->id,
            'servicio_id' => $this->servicio->id,
            'empleado_id' => $this->empleado->id,
        ]);

        $response = $this->actingAs($this->estilista)->get('/admin/mis-citas');

        $response->assertOk();
        $this->assertCount(0, $response->viewData('citasHoy'));
    }

    /** No puede acceder a consumo de una cita que no le pertenece */
    public function test_no_puede_acceder_a_consumo_de_otra_cita(): void
    {
        $otroEmpleado = Empleado::create([
            'nombre'   => 'Pedro',
            'apellido' => 'Ramirez',
            'telefono' => '70000002',
            'cargo'    => 'estilista',
            'activo'   => true,
        ]);
        $citaAjena = $this->crearCitaHoy('completada', $otroEmpleado);

        $response = $this->actingAs($this->estilista)->get("/admin/mis-citas/{$citaAjena->id}/consumo");

        $response->assertForbidden();
    }

    /** No puede registrar consumo en cita no completada */
    public function test_no_puede_registrar_consumo_en_cita_no_completada(): void
    {
        $cita = $this->crearCitaHoy('pendiente');

        $response = $this->actingAs($this->estilista)->get("/admin/mis-citas/{$cita->id}/consumo");

        $response->assertForbidden();
    }

    /** guardarConsumo con usos_reales=1 actualiza el campo, no crea consumos extra */
    public function test_guardar_consumo_con_usos_1_no_crea_extras(): void
    {
        $cita = $this->crearCitaHoy();
        $consumo = $this->crearConsumoParaCita($cita);

        $this->actingAs($this->estilista)->post("/admin/mis-citas/{$cita->id}/consumo", [
            'materiales' => [
                ['consumo_id' => $consumo->id, 'usos_reales' => 1],
            ],
        ])->assertRedirect('/admin/mis-citas');

        $this->assertEquals(1, ConsumoMaterial::count());
        $this->assertEquals(1, $consumo->fresh()->usos_reales);
    }

    /** guardarConsumo con usos_reales=3 crea 2 ConsumoMaterial adicionales */
    public function test_guardar_consumo_con_usos_3_crea_2_extras(): void
    {
        $cita = $this->crearCitaHoy();
        $consumo = $this->crearConsumoParaCita($cita);

        $this->actingAs($this->estilista)->post("/admin/mis-citas/{$cita->id}/consumo", [
            'materiales' => [
                ['consumo_id' => $consumo->id, 'usos_reales' => 3],
            ],
        ])->assertRedirect('/admin/mis-citas');

        $this->assertEquals(3, ConsumoMaterial::count());
        $this->assertEquals(3, $consumo->fresh()->usos_reales);
    }

    /** Consumos extra gatillan Salida cuando alcanzan el umbral */
    public function test_consumos_extra_gatillan_salida(): void
    {
        $cita = $this->crearCitaHoy();
        $consumo = $this->crearConsumoParaCita($cita);

        // usos_reales = 3 → 2 extras → total consumos = 3 → 3 % 3 == 0 → Salida
        $this->actingAs($this->estilista)->post("/admin/mis-citas/{$cita->id}/consumo", [
            'materiales' => [
                ['consumo_id' => $consumo->id, 'usos_reales' => 3],
            ],
        ]);

        $this->assertEquals(1, Salida::count());
    }

    /** Usuario sin empleado vinculado es redirigido con error */
    public function test_usuario_sin_empleado_es_redirigido(): void
    {
        $userSinEmpleado = User::factory()->create();
        $userSinEmpleado->assignRole('estilista');

        $response = $this->actingAs($userSinEmpleado)->get('/admin/mis-citas');

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('error');
    }
}
