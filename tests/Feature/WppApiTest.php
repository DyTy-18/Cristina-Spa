<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\WppSyncLog;
use App\Services\WppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WppApiTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-wpp-token';

    private Cliente $cliente;
    private Servicio $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.wpp.token' => self::TOKEN]);

        Sucursal::create(['nombre' => 'Sucursal Central', 'es_principal' => true]);

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
    }

    private function crearCita(string $fecha, string $estado = 'pendiente'): Cita
    {
        $cita = Cita::create([
            'cliente_id' => $this->cliente->id,
            'fecha'      => $fecha,
            'hora'       => '10:00',
            'estado'     => $estado,
        ]);

        CitaServicio::create([
            'cita_id'     => $cita->id,
            'servicio_id' => $this->servicio->id,
        ]);

        return $cita;
    }

    public function test_rechaza_sin_token(): void
    {
        $this->getJson('/api/wpp/citas?desde=2026-01-01&hasta=2026-01-31')
            ->assertUnauthorized();

        $this->assertDatabaseHas('wpp_sync_logs', [
            'direccion' => 'entrante',
            'tipo'      => 'auth_rechazado',
            'resultado' => 'error',
        ]);
    }

    public function test_rechaza_token_incorrecto(): void
    {
        $this->withToken('token-invalido')
            ->getJson('/api/wpp/citas?desde=2026-01-01&hasta=2026-01-31')
            ->assertUnauthorized();

        $this->assertSame(1, WppSyncLog::where('tipo', 'auth_rechazado')->count());
    }

    public function test_consulta_por_rango_devuelve_solo_lo_pedido(): void
    {
        $dentro = $this->crearCita('2026-05-10');
        $this->crearCita('2026-06-01'); // fuera del rango

        $response = $this->withToken(self::TOKEN)
            ->getJson('/api/wpp/citas?desde=2026-05-01&hasta=2026-05-31')
            ->assertOk();

        $citas = $response->json('citas');
        $this->assertCount(1, $citas);
        $this->assertSame($dentro->id, $citas[0]['id']);
        $this->assertSame($this->cliente->telefono, $citas[0]['cliente']['telefono']);

        $this->assertDatabaseHas('wpp_sync_logs', [
            'direccion' => 'entrante',
            'tipo'      => 'consulta',
            'resultado' => 'ok',
        ]);
    }

    public function test_webhook_confirma_estado_sin_reenviar_a_wpp(): void
    {
        Http::fake();

        $cita = $this->crearCita('2026-05-10', 'pendiente');

        $this->withToken(self::TOKEN)
            ->patchJson("/api/wpp/citas/{$cita->id}/estado", ['estado' => 'confirmada'])
            ->assertOk()
            ->assertJson(['id' => $cita->id, 'estado' => 'confirmada']);

        $this->assertSame('confirmada', $cita->fresh()->estado);
        // El webhook no debe volver a llamar a WppService (evita el eco WPP → Laravel → WPP).
        Http::assertNothingSent();

        $this->assertDatabaseHas('wpp_sync_logs', [
            'direccion' => 'entrante',
            'tipo'      => 'webhook_estado',
            'cita_id'   => $cita->id,
            'resultado' => 'ok',
        ]);
    }

    public function test_webhook_no_permite_modificar_cita_completada(): void
    {
        $cita = $this->crearCita('2026-05-10', 'completada');

        $this->withToken(self::TOKEN)
            ->patchJson("/api/wpp/citas/{$cita->id}/estado", ['estado' => 'cancelada'])
            ->assertStatus(422);

        $this->assertSame('completada', $cita->fresh()->estado);

        $this->assertDatabaseHas('wpp_sync_logs', [
            'direccion' => 'entrante',
            'tipo'      => 'webhook_estado',
            'cita_id'   => $cita->id,
            'resultado' => 'error',
        ]);
    }

    public function test_webhook_crea_cita_resolviendo_cliente_por_telefono(): void
    {
        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/wpp/citas', [
                'telefono'  => '70000000',
                'fecha'     => '2026-05-15',
                'hora'      => '11:00',
                'servicios' => [$this->servicio->id],
            ])
            ->assertCreated();

        $this->assertSame(1, Cliente::count()); // reutilizó el cliente existente, no creó uno nuevo
        $this->assertDatabaseHas('citas', [
            'id'         => $response->json('id'),
            'cliente_id' => $this->cliente->id,
            'estado'     => 'pendiente',
        ]);

        $this->assertDatabaseHas('wpp_sync_logs', [
            'direccion' => 'entrante',
            'tipo'      => 'webhook_crear',
            'cita_id'   => $response->json('id'),
            'resultado' => 'ok',
        ]);
    }

    public function test_webhook_reprograma_fecha_y_hora(): void
    {
        $cita = $this->crearCita('2026-05-10', 'confirmada');

        $this->withToken(self::TOKEN)
            ->patchJson("/api/wpp/citas/{$cita->id}", ['fecha' => '2026-05-20', 'hora' => '15:30'])
            ->assertOk();

        $cita->refresh();
        $this->assertSame('2026-05-20', $cita->fecha->format('Y-m-d'));

        $this->assertDatabaseHas('wpp_sync_logs', [
            'direccion' => 'entrante',
            'tipo'      => 'webhook_reprogramar',
            'cita_id'   => $cita->id,
            'resultado' => 'ok',
        ]);
    }

    public function test_push_saliente_registra_resultado_ok_y_error(): void
    {
        Http::fake([
            '*/api/citas/citas/process-cita' => Http::sequence()
                ->push(['ok' => true], 200)
                ->push(['error' => 'boom'], 500),
        ]);

        $cita = $this->crearCita('2026-05-10', 'pendiente');

        app(WppService::class)->notificarSegunEstado($cita);
        app(WppService::class)->notificarSegunEstado($cita);

        $this->assertSame(1, WppSyncLog::where('cita_id', $cita->id)
            ->where('direccion', 'saliente')->where('resultado', 'ok')->count());
        $this->assertSame(1, WppSyncLog::where('cita_id', $cita->id)
            ->where('direccion', 'saliente')->where('resultado', 'error')
            ->where('status_code', 500)->count());
    }
}
