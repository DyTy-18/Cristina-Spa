<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Entrada;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\SeguimientoProducto;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\SeguimientoConsumoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguimientoConsumoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Sucursal $sucursal;
    private Cliente $cliente;
    private Producto $tinte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->sucursal = Sucursal::create(['nombre' => 'Central', 'es_principal' => true]);

        $this->cliente = Cliente::create(['nombre' => 'Ana', 'apellido' => 'García', 'telefono' => '70000000']);

        $this->tinte = $this->crearProductoSeguimiento('TIN100', 100, 5, $this->sucursal);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function crearProductoSeguimiento(string $codigo, float $peso, int $stock, ?Sucursal $sucursal): Producto
    {
        $producto = Producto::create([
            'codigo_barras' => $codigo,
            'nombre'        => "Producto {$codigo}",
            'costo'         => 10,
            'stock_minimo'  => 0,
            'tipo'          => Producto::TIPO_SEGUIMIENTO,
            'peso_gramos'   => $peso,
        ]);

        Entrada::create([
            'codigo_barras' => $codigo,
            'sucursal_id'   => $sucursal?->id,
            'tipo_stock'    => 'tecnico',
            'unidades'      => $stock,
            'fecha'         => today(),
        ]);

        return $producto;
    }

    private function crearCita(?Sucursal $sucursal = null): Cita
    {
        return Cita::create([
            'cliente_id'  => $this->cliente->id,
            'sucursal_id' => ($sucursal ?? $this->sucursal)->id,
            'fecha'       => today(),
            'hora'        => '10:00',
            'estado'      => 'completada',
        ]);
    }

    private function stock(Producto $producto, ?Sucursal $sucursal = null, string $tipo = 'tecnico'): int
    {
        $sid = ($sucursal ?? $this->sucursal)->id;

        return (int) Entrada::where('codigo_barras', $producto->codigo_barras)->where('tipo_stock', $tipo)->where('sucursal_id', $sid)->sum('unidades')
             - (int) Salida::where('codigo_barras', $producto->codigo_barras)->where('tipo_stock', $tipo)->where('sucursal_id', $sid)->sum('unidades');
    }

    private function registrarGramos(Cita $cita, Producto $producto, float $gramos)
    {
        return $this->actingAs($this->admin)->post(route('admin.citas.seguimiento.productos.store', $cita), [
            'modo'        => 'existente',
            'producto_id' => $producto->id,
            'gramos'      => $gramos,
        ]);
    }

    // ─── Descuento de inventario ─────────────────────────────────────────────

    public function test_usar_el_peso_completo_descuenta_una_unidad(): void
    {
        $this->registrarGramos($this->crearCita(), $this->tinte, 100)->assertRedirect();

        $this->assertSame(4, $this->stock($this->tinte));
        $this->assertDatabaseHas('salidas', [
            'codigo_barras' => 'TIN100',
            'sucursal_id'   => $this->sucursal->id,
            'tipo_stock'    => 'tecnico',
            'unidades'      => 1,
            'destino'       => SeguimientoConsumoService::DESTINO,
        ]);
    }

    public function test_uso_parcial_no_descuenta_hasta_completar_una_unidad(): void
    {
        $cita = $this->crearCita();

        $this->registrarGramos($cita, $this->tinte, 50);
        $this->assertSame(5, $this->stock($this->tinte), '50 g de 100 g no debe descontar');

        $this->registrarGramos($cita, $this->tinte, 49.99);
        $this->assertSame(5, $this->stock($this->tinte), '99.99 g de 100 g no debe descontar');

        $this->registrarGramos($cita, $this->tinte, 0.01);
        $this->assertSame(4, $this->stock($this->tinte), 'al llegar a 100 g se descuenta 1');
    }

    public function test_los_gramos_se_acumulan_entre_citas_distintas(): void
    {
        $this->registrarGramos($this->crearCita(), $this->tinte, 60);
        $this->registrarGramos($this->crearCita(), $this->tinte, 60);

        // 120 g acumulados → 1 unidad; quedan 20 g abiertos.
        $this->assertSame(4, $this->stock($this->tinte));

        $this->registrarGramos($this->crearCita(), $this->tinte, 80);
        // 200 g → 2 unidades.
        $this->assertSame(3, $this->stock($this->tinte));
    }

    public function test_un_uso_grande_descuenta_varias_unidades(): void
    {
        $this->registrarGramos($this->crearCita(), $this->tinte, 250);

        $this->assertSame(3, $this->stock($this->tinte));
    }

    public function test_quitar_el_producto_del_seguimiento_devuelve_el_inventario(): void
    {
        $cita = $this->crearCita();
        $this->registrarGramos($cita, $this->tinte, 100);
        $this->registrarGramos($cita, $this->tinte, 60);
        $this->assertSame(4, $this->stock($this->tinte));

        $registro100 = SeguimientoProducto::where('gramos', 100)->firstOrFail();
        $this->actingAs($this->admin)
            ->delete(route('admin.citas.seguimiento.productos.destroy', [$cita, $registro100]))
            ->assertRedirect();

        // Quedan 60 g → no llega a una unidad → se devuelve la unidad descontada.
        $this->assertSame(5, $this->stock($this->tinte));
        $this->assertSame(0, (int) Salida::where('destino', SeguimientoConsumoService::DESTINO)->sum('unidades'));
    }

    public function test_quitar_solo_una_parte_devuelve_solo_lo_que_corresponde(): void
    {
        $cita = $this->crearCita();
        $this->registrarGramos($cita, $this->tinte, 250); // 2 unidades
        $this->registrarGramos($cita, $this->tinte, 60);  // 310 g → 3 unidades
        $this->assertSame(2, $this->stock($this->tinte));

        $registro60 = SeguimientoProducto::where('gramos', 60)->firstOrFail();
        $this->actingAs($this->admin)->delete(route('admin.citas.seguimiento.productos.destroy', [$cita, $registro60]));

        // 250 g → 2 unidades.
        $this->assertSame(3, $this->stock($this->tinte));
    }

    public function test_cada_sucursal_acumula_sus_propios_gramos(): void
    {
        $otra = Sucursal::create(['nombre' => 'Norte']);
        Entrada::create([
            'codigo_barras' => 'TIN100', 'sucursal_id' => $otra->id,
            'tipo_stock' => 'tecnico', 'unidades' => 5, 'fecha' => today(),
        ]);

        $this->registrarGramos($this->crearCita($this->sucursal), $this->tinte, 60);
        $this->registrarGramos($this->crearCita($otra), $this->tinte, 60);

        // 60 g en cada una: ninguna completa una unidad.
        $this->assertSame(5, $this->stock($this->tinte, $this->sucursal));
        $this->assertSame(5, $this->stock($this->tinte, $otra));

        $this->registrarGramos($this->crearCita($otra), $this->tinte, 40);

        $this->assertSame(5, $this->stock($this->tinte, $this->sucursal));
        $this->assertSame(4, $this->stock($this->tinte, $otra));
    }

    public function test_el_descuento_no_toca_el_stock_de_reventa(): void
    {
        Entrada::create([
            'codigo_barras' => 'TIN100', 'sucursal_id' => $this->sucursal->id,
            'tipo_stock' => 'reventa', 'unidades' => 3, 'fecha' => today(),
        ]);

        $this->registrarGramos($this->crearCita(), $this->tinte, 200);

        $this->assertSame(3, $this->stock($this->tinte));
        $this->assertSame(3, $this->stock($this->tinte, null, 'reventa'));
    }

    public function test_recalcular_varias_veces_no_duplica_salidas(): void
    {
        $this->registrarGramos($this->crearCita(), $this->tinte, 150);

        $service = app(SeguimientoConsumoService::class);
        $service->recalcular($this->tinte, $this->sucursal->id);
        $service->recalcular($this->tinte, $this->sucursal->id);

        $this->assertSame(4, $this->stock($this->tinte));
    }

    public function test_decimales_que_suman_el_peso_exacto_cuentan_como_unidad(): void
    {
        $producto = $this->crearProductoSeguimiento('CRE999', 99.99, 5, $this->sucursal);
        $cita = $this->crearCita();

        $this->registrarGramos($cita, $producto, 33.33);
        $this->registrarGramos($cita, $producto, 33.33);
        $this->registrarGramos($cita, $producto, 33.33);

        $this->assertSame(4, $this->stock($producto));
    }

    // ─── Validaciones ─────────────────────────────────────────────────────────

    public function test_registrar_producto_sin_gramos_falla(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.citas.seguimiento.productos.store', $this->crearCita()), [
                'modo' => 'existente', 'producto_id' => $this->tinte->id,
            ])
            ->assertSessionHasErrors('gramos');

        $this->assertSame(0, SeguimientoProducto::count());
        $this->assertSame(5, $this->stock($this->tinte));
    }

    public function test_no_se_puede_registrar_en_seguimiento_un_producto_de_otro_tipo(): void
    {
        $generico = Producto::create([
            'codigo_barras' => 'GEN1', 'nombre' => 'Guantes', 'costo' => 1, 'stock_minimo' => 0,
            'tipo' => Producto::TIPO_GENERICO,
        ]);

        $this->registrarGramos($this->crearCita(), $generico, 100)->assertSessionHasErrors('producto_id');

        $this->assertSame(0, SeguimientoProducto::count());
        $this->assertSame(0, Salida::count());
    }

    public function test_apunte_libre_no_descuenta_inventario(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.citas.seguimiento.productos.store', $this->crearCita()), [
                'modo' => 'nuevo', 'nombre' => 'Mascarilla de prueba',
            ])
            ->assertRedirect();

        $this->assertSame(1, SeguimientoProducto::count());
        $this->assertSame(0, Salida::count());
    }

    // ─── Pantalla de seguimiento y alta de productos ─────────────────────────

    public function test_seguimiento_solo_lista_productos_de_tipo_seguimiento(): void
    {
        foreach (['vitrina' => 'VIT1', 'generico' => 'GEN1'] as $tipo => $codigo) {
            Producto::create([
                'codigo_barras' => $codigo, 'nombre' => "Otro {$tipo}", 'costo' => 1, 'stock_minimo' => 0,
                'tipo' => $tipo, 'es_reventa' => $tipo === 'vitrina',
            ]);
            Entrada::create([
                'codigo_barras' => $codigo, 'sucursal_id' => $this->sucursal->id,
                'tipo_stock' => 'tecnico', 'unidades' => 1, 'fecha' => today(),
            ]);
        }

        $this->actingAs($this->admin)
            ->get(route('admin.citas.seguimiento.show', $this->crearCita()))
            ->assertOk()
            ->assertSee('Producto TIN100')
            ->assertDontSee('Otro vitrina')
            ->assertDontSee('Otro generico');
    }

    public function test_crear_producto_de_seguimiento_exige_peso(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.inventario.productos.store'), [
                'codigo_barras' => 'NEW1', 'nombre' => 'Oxidante', 'costo' => 5, 'stock_minimo' => 1,
                'tipo' => 'seguimiento',
            ])
            ->assertSessionHasErrors('peso_gramos');

        $this->assertDatabaseMissing('productos', ['codigo_barras' => 'NEW1']);
    }

    public function test_crear_producto_guarda_tipo_peso_y_reventa_correctamente(): void
    {
        $base = ['costo' => 5, 'stock_minimo' => 1];

        $this->actingAs($this->admin)->post(route('admin.inventario.productos.store'),
            $base + ['codigo_barras' => 'S1', 'nombre' => 'Oxidante', 'tipo' => 'seguimiento', 'peso_gramos' => 250]);
        $this->actingAs($this->admin)->post(route('admin.inventario.productos.store'),
            $base + ['codigo_barras' => 'V1', 'nombre' => 'Shampoo', 'tipo' => 'vitrina', 'peso_gramos' => 999]);
        $this->actingAs($this->admin)->post(route('admin.inventario.productos.store'),
            $base + ['codigo_barras' => 'G1', 'nombre' => 'Guantes', 'tipo' => 'generico']);

        $this->assertDatabaseHas('productos', ['codigo_barras' => 'S1', 'tipo' => 'seguimiento', 'peso_gramos' => 250, 'es_reventa' => false]);
        // El peso solo se guarda en productos de seguimiento.
        $this->assertDatabaseHas('productos', ['codigo_barras' => 'V1', 'tipo' => 'vitrina', 'peso_gramos' => null, 'es_reventa' => true]);
        $this->assertDatabaseHas('productos', ['codigo_barras' => 'G1', 'tipo' => 'generico', 'peso_gramos' => null, 'es_reventa' => false]);
    }

    public function test_solo_los_de_vitrina_aparecen_como_reventa(): void
    {
        Producto::create(['codigo_barras' => 'V1', 'nombre' => 'Shampoo', 'costo' => 1, 'stock_minimo' => 0, 'tipo' => 'vitrina', 'es_reventa' => true]);

        $this->assertSame(['V1'], Producto::reventaConStock()->pluck('codigo_barras')->all());
    }

    // ─── Casilla "Producto de reventa" ───────────────────────────────────────

    public function test_formulario_nuevo_oculta_las_opciones_hasta_marcar_reventa(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.inventario.productos.create'))
            ->assertOk()
            ->assertSee('Producto de reventa')
            ->getContent();

        $this->assertMatchesRegularExpression('/<input type="checkbox" id="es_reventa"[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/id="es_reventa"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/id="tipoProductoGroup" style="display:none;"/', $html);
    }

    public function test_editar_producto_de_seguimiento_muestra_la_casilla_marcada_y_opciones(): void
    {
        $html = $this->actingAs($this->admin)->get(route('admin.inventario.productos.edit', $this->tinte))
            ->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="es_reventa"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/id="tipoProductoGroup" style=""/', $html);
        $this->assertMatchesRegularExpression('/value="seguimiento"\s+checked/', $html);
    }

    public function test_sin_marcar_reventa_el_producto_queda_generico(): void
    {
        // Lo que envía el navegador con la casilla sin marcar: solo el hidden tipo=generico.
        $this->actingAs($this->admin)->post(route('admin.inventario.productos.store'), [
            'codigo_barras' => 'G2', 'nombre' => 'Toallas', 'costo' => 1, 'stock_minimo' => 0,
            'tipo' => 'generico', 'peso_gramos' => 100,
        ])->assertRedirect();

        $this->assertDatabaseHas('productos', ['codigo_barras' => 'G2', 'tipo' => 'generico', 'es_reventa' => false, 'peso_gramos' => null]);
    }

    public function test_desmarcar_reventa_al_editar_pasa_el_producto_a_generico(): void
    {
        $this->actingAs($this->admin)->put(route('admin.inventario.productos.update', $this->tinte), [
            'codigo_barras' => 'TIN100', 'nombre' => 'Producto TIN100', 'costo' => 10, 'stock_minimo' => 0,
            'tipo' => 'generico',
        ])->assertRedirect();

        $this->tinte->refresh();
        $this->assertSame('generico', $this->tinte->tipo);
        $this->assertNull($this->tinte->peso_gramos);
    }
}
