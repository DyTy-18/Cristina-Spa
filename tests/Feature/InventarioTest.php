<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\CitaProducto;
use App\Models\Cliente;
use App\Models\Entrada;
use App\Models\InventarioBackup;
use App\Models\Producto;
use App\Models\Salida;
use App\Models\SeguimientoProducto;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\SeguimientoConsumoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class InventarioTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Sucursal $central;
    private Sucursal $norte;
    private Producto $enCentral;
    private Producto $enNorte;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->central = Sucursal::create(['nombre' => 'Central', 'es_principal' => true]);
        $this->norte   = Sucursal::create(['nombre' => 'Norte']);

        // Mismo código de barras en las dos sucursales: catálogos independientes.
        $this->enCentral = $this->crearProducto($this->central, '7790001', 10);
        $this->enNorte   = $this->crearProducto($this->norte, '7790001', 4);
    }

    private function crearProducto(Sucursal $sucursal, string $codigo, int $stock, bool $reventa = false): Producto
    {
        $producto = Producto::create([
            'sucursal_id'   => $sucursal->id,
            'codigo_barras' => $codigo,
            'nombre'        => "Shampoo {$sucursal->nombre}",
            'costo'         => 50,
            'stock_minimo'  => 0,
            'es_reventa'    => $reventa,
            'tipo'          => $reventa ? Producto::TIPO_VITRINA : null,
        ]);

        if ($stock > 0) {
            Entrada::create([
                'producto_id' => $producto->id,
                'tipo_stock'  => $reventa ? 'reventa' : 'tecnico',
                'unidades'    => $stock,
                'fecha'       => today(),
            ]);
        }

        return $producto;
    }

    private function enSucursal(?Sucursal $sucursal): static
    {
        return $this->actingAs($this->admin)->withSession(['sucursal_activa_id' => $sucursal?->id]);
    }

    // ─── Aislamiento por sucursal ────────────────────────────────────────────

    public function test_cada_sucursal_ve_solo_su_stock(): void
    {
        $stock = $this->enSucursal($this->norte)->get(route('admin.inventario.index'))
            ->assertOk()->viewData('stock');

        $this->assertCount(1, $stock);
        $this->assertSame($this->enNorte->id, $stock->first()->id);
        $this->assertSame(4, (int) $stock->first()->stock_actual);
    }

    public function test_modo_global_ve_el_stock_de_todas_por_separado(): void
    {
        $stock = $this->enSucursal(null)->get(route('admin.inventario.index'))
            ->assertOk()->viewData('stock')->keyBy('id');

        $this->assertCount(2, $stock);
        $this->assertSame(10, (int) $stock[$this->enCentral->id]->stock_actual);
        $this->assertSame(4, (int) $stock[$this->enNorte->id]->stock_actual);
    }

    public function test_movimientos_listados_son_solo_de_la_sucursal(): void
    {
        $entradas = $this->enSucursal($this->central)->get(route('admin.inventario.entradas'))
            ->assertOk()->viewData('entradas');

        $this->assertSame([$this->enCentral->id], collect($entradas->items())->pluck('producto_id')->all());
    }

    public function test_entrada_queda_en_la_sucursal_del_producto(): void
    {
        $this->enSucursal($this->norte)->post(route('admin.inventario.entradas.store'), [
            'producto_id' => $this->enNorte->id, 'unidades' => 3, 'fecha' => today()->toDateString(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('entradas', ['producto_id' => $this->enNorte->id, 'sucursal_id' => $this->norte->id, 'unidades' => 3]);
        $this->assertSame(7, $this->enNorte->stock());
        $this->assertSame(10, $this->enCentral->stock());
    }

    public function test_no_se_registra_un_movimiento_con_producto_de_otra_sucursal(): void
    {
        $this->enSucursal($this->norte)->post(route('admin.inventario.entradas.store'), [
            'producto_id' => $this->enCentral->id, 'unidades' => 3, 'fecha' => today()->toDateString(),
        ])->assertSessionHasErrors('producto_id');

        // En modo global, la sucursal elegida en el formulario debe coincidir con la del producto.
        $this->enSucursal(null)->post(route('admin.inventario.salidas.store'), [
            'sucursal_id' => $this->norte->id, 'producto_id' => $this->enCentral->id, 'unidades' => 1, 'fecha' => today()->toDateString(),
        ])->assertSessionHasErrors('producto_id');

        $this->assertSame(10, $this->enCentral->stock());
    }

    public function test_un_movimiento_no_puede_quedar_en_otra_sucursal_que_su_producto(): void
    {
        $entrada = Entrada::create([
            'producto_id' => $this->enCentral->id, 'sucursal_id' => $this->norte->id, 'unidades' => 1, 'fecha' => today(),
        ]);

        $this->assertSame($this->central->id, $entrada->fresh()->sucursal_id);
    }

    public function test_no_se_edita_un_producto_de_otra_sucursal(): void
    {
        $this->enSucursal($this->norte)->get(route('admin.inventario.productos.edit', $this->enCentral))->assertNotFound();
    }

    public function test_mismo_codigo_no_se_repite_en_la_misma_sucursal(): void
    {
        $this->enSucursal($this->central)->post(route('admin.inventario.productos.store'), [
            'codigo_barras' => '7790001', 'nombre' => 'Duplicado', 'costo' => 1, 'stock_minimo' => 0,
        ])->assertSessionHasErrors('codigo_barras');

        $this->enSucursal(null)->post(route('admin.inventario.productos.store'), [
            'sucursal_id' => Sucursal::create(['nombre' => 'Sur'])->id,
            'codigo_barras' => '7790001', 'nombre' => 'Shampoo Sur', 'costo' => 1, 'stock_minimo' => 0,
            'unidades_iniciales' => 2,
        ])->assertSessionHasNoErrors();

        $sur = Producto::where('nombre', 'Shampoo Sur')->firstOrFail();
        $this->assertSame(2, $sur->stock());
    }

    // ─── Salidas ─────────────────────────────────────────────────────────────

    public function test_salida_tecnica_no_deja_stock_negativo(): void
    {
        $this->enSucursal($this->norte)->post(route('admin.inventario.salidas.store'), [
            'producto_id' => $this->enNorte->id, 'unidades' => 5, 'fecha' => today()->toDateString(),
        ])->assertSessionHasErrors('unidades');

        $this->assertSame(4, $this->enNorte->stock());

        $this->enSucursal($this->norte)->post(route('admin.inventario.salidas.store'), [
            'producto_id' => $this->enNorte->id, 'unidades' => 4, 'fecha' => today()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $this->enNorte->stock());
    }

    public function test_salida_de_reventa_no_deja_stock_negativo(): void
    {
        $vitrina = $this->crearProducto($this->norte, 'V1', 2, reventa: true);

        $this->enSucursal($this->norte)->post(route('admin.inventario.reventa.salida.store'), [
            'producto_id' => $vitrina->id, 'unidades' => 3, 'fecha' => today()->toDateString(),
        ])->assertSessionHasErrors('unidades');

        $this->assertSame(2, $vitrina->stock('reventa'));
    }

    // ─── Transferencia a reventa ─────────────────────────────────────────────

    public function test_transferencia_mueve_stock_y_convierte_en_vitrina(): void
    {
        $this->enSucursal($this->central)->post(route('admin.inventario.reventa.transferir.store'), [
            'producto_id' => $this->enCentral->id, 'unidades' => 10, 'fecha' => today()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->enCentral->refresh();
        $this->assertTrue($this->enCentral->es_reventa);
        $this->assertSame(Producto::TIPO_VITRINA, $this->enCentral->tipo);
        $this->assertSame(0, $this->enCentral->stock('tecnico'));
        $this->assertSame(10, $this->enCentral->stock('reventa'));
        // La otra sucursal no se entera.
        $this->assertFalse($this->enNorte->fresh()->es_reventa);
        $this->assertSame(4, $this->enNorte->stock());
    }

    public function test_transferencia_rechaza_mas_que_el_stock(): void
    {
        $this->enSucursal($this->norte)->post(route('admin.inventario.reventa.transferir.store'), [
            'producto_id' => $this->enNorte->id, 'unidades' => 5, 'fecha' => today()->toDateString(),
        ])->assertSessionHasErrors('unidades');

        $this->assertFalse($this->enNorte->fresh()->es_reventa);
        $this->assertSame(0, Salida::count());
    }

    public function test_transferencia_parcial_deja_el_sobrante_visible_en_tecnico(): void
    {
        $hoy = today()->toDateString();
        $this->enSucursal($this->central)->post(route('admin.inventario.reventa.transferir.store'), [
            'producto_id' => $this->enCentral->id, 'unidades' => 3, 'fecha' => $hoy,
        ])->assertSessionHasNoErrors();

        $this->assertSame(7, $this->enCentral->stock('tecnico'));
        $this->assertSame(3, $this->enCentral->stock('reventa'));

        // Aparece en los dos catálogos, cada uno con su stock.
        $tecnico = $this->enSucursal($this->central)->get(route('admin.inventario.index'))->viewData('stock')->keyBy('id');
        $reventa = $this->enSucursal($this->central)->get(route('admin.inventario.index', ['tipo' => 'reventa']))->viewData('stock')->keyBy('id');
        $this->assertSame(7, (int) $tecnico[$this->enCentral->id]->stock_actual);
        $this->assertSame(3, (int) $reventa[$this->enCentral->id]->stock_actual);
        $this->assertTrue($this->enSucursal($this->central)->get(route('admin.inventario.salidas.create'))
            ->viewData('productos')->contains('id', $this->enCentral->id));

        // El sobrante técnico se puede seguir usando y transfiriendo.
        $this->enSucursal($this->central)->post(route('admin.inventario.salidas.store'), [
            'producto_id' => $this->enCentral->id, 'unidades' => 2, 'fecha' => $hoy,
        ])->assertSessionHasNoErrors();
        $this->enSucursal($this->central)->post(route('admin.inventario.reventa.transferir.store'), [
            'producto_id' => $this->enCentral->id, 'unidades' => 5, 'fecha' => $hoy,
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $this->enCentral->stock('tecnico'));
        $this->assertSame(8, $this->enCentral->stock('reventa'));

        // Sin sobrante, sale del catálogo técnico.
        $tecnico = $this->enSucursal($this->central)->get(route('admin.inventario.index'))->viewData('stock');
        $this->assertFalse($tecnico->contains('id', $this->enCentral->id));
    }

    // ─── Cita cancelada devuelve stock ───────────────────────────────────────

    private function citaConVenta(Producto $vitrina, int $cantidad, string $estado = 'confirmada'): Cita
    {
        $cliente = Cliente::create(['nombre' => 'Ana', 'apellido' => 'García', 'telefono' => '70000000']);
        $cita = Cita::create([
            'cliente_id' => $cliente->id, 'sucursal_id' => $vitrina->sucursal_id,
            'fecha' => today(), 'hora' => '10:00', 'estado' => $estado,
        ]);
        CitaProducto::create(['cita_id' => $cita->id, 'producto_id' => $vitrina->id, 'cantidad' => $cantidad, 'precio_unitario' => 100]);
        Salida::create([
            'producto_id' => $vitrina->id, 'tipo_stock' => 'reventa', 'unidades' => $cantidad,
            'fecha' => today(), 'cita_id' => $cita->id,
        ]);

        return $cita;
    }

    public function test_cancelar_cita_devuelve_productos_vendidos_y_reactivarla_los_descuenta(): void
    {
        Http::fake();
        $vitrina = $this->crearProducto($this->central, 'V1', 5, reventa: true);
        $cita    = $this->citaConVenta($vitrina, 2);
        $this->assertSame(3, $vitrina->stock('reventa'));

        $this->enSucursal($this->central)->patch(route('admin.citas.estado', $cita), ['estado' => 'cancelada'])
            ->assertSessionHasNoErrors();
        $this->assertSame(5, $vitrina->stock('reventa'));

        $this->enSucursal($this->central)->patch(route('admin.citas.estado', $cita), ['estado' => 'confirmada'])
            ->assertSessionHasNoErrors();
        $this->assertSame(3, $vitrina->stock('reventa'));
        $this->assertSame(1, Salida::where('cita_id', $cita->id)->count());
    }

    public function test_no_se_reactiva_una_cita_cancelada_sin_stock(): void
    {
        Http::fake();
        $vitrina = $this->crearProducto($this->central, 'V1', 2, reventa: true);
        $cita    = $this->citaConVenta($vitrina, 2);

        $this->enSucursal($this->central)->patch(route('admin.citas.estado', $cita), ['estado' => 'cancelada']);
        // Mientras tanto se vende parte del stock devuelto.
        Salida::create(['producto_id' => $vitrina->id, 'tipo_stock' => 'reventa', 'unidades' => 1, 'fecha' => today()]);

        $this->enSucursal($this->central)->patch(route('admin.citas.estado', $cita), ['estado' => 'confirmada'])
            ->assertSessionHasErrors('productos');

        $this->assertSame('cancelada', $cita->fresh()->estado);
        $this->assertSame(1, $vitrina->stock('reventa'));
    }

    public function test_cancelar_cita_por_whatsapp_devuelve_productos(): void
    {
        config(['services.wpp.token' => 'tok']);
        $vitrina = $this->crearProducto($this->central, 'V1', 5, reventa: true);
        $cita    = $this->citaConVenta($vitrina, 2);

        $this->withToken('tok')->patchJson(route('api.wpp.citas.estado', $cita), ['estado' => 'cancelada'])->assertOk();

        $this->assertSame(5, $vitrina->stock('reventa'));
    }

    public function test_cancelar_cita_devuelve_gramos_de_seguimiento(): void
    {
        $tinte = Producto::create([
            'sucursal_id' => $this->central->id, 'codigo_barras' => '7790099', 'nombre' => 'Tinte', 'costo' => 1,
            'stock_minimo' => 0, 'es_reventa' => true, 'tipo' => Producto::TIPO_SEGUIMIENTO, 'peso_gramos' => 100,
        ]);
        Entrada::create(['producto_id' => $tinte->id, 'tipo_stock' => 'reventa', 'unidades' => 5, 'fecha' => today()]);

        $cita = $this->citaConVenta($this->crearProducto($this->central, 'V1', 1, reventa: true), 0, 'completada');
        SeguimientoProducto::create(['cita_id' => $cita->id, 'producto_id' => $tinte->id, 'gramos' => 150]);
        app(SeguimientoConsumoService::class)->recalcular($tinte);
        $this->assertSame(4, $tinte->stock('reventa'));

        $cita->update(['estado' => 'cancelada']);
        $this->assertSame(5, $tinte->stock('reventa'));

        $cita->update(['estado' => 'completada']);
        $this->assertSame(4, $tinte->stock('reventa'));
    }

    // ─── Importación Excel ───────────────────────────────────────────────────

    private function excel(array $hojas): UploadedFile
    {
        $libro = new Spreadsheet();
        $libro->removeSheetByIndex(0);
        foreach ($hojas as $nombre => $filas) {
            $hoja = $libro->createSheet();
            $hoja->setTitle($nombre);
            $hoja->fromArray($filas, null, 'A1', true);
        }

        $ruta = tempnam(sys_get_temp_dir(), 'inv') . '.xlsx';
        (new Xlsx($libro))->save($ruta);

        return new UploadedFile($ruta, 'inventario.xlsx', null, null, true);
    }

    public function test_importar_carga_en_la_sucursal_activa_sin_pisar_costos(): void
    {
        $this->enCentral->update(['costo' => 75, 'precio_venta' => 120]);

        $archivo = $this->excel([
            'REGISTRO' => [['CODIGO DE BARRAS', 'NOMBRE', 'MARCA'], ['7790001', '', 'Loreal'], ['777', 'Crema', '']],
            'ENTRADA'  => [['CODIGO DE BARRAS', 'UNIDADES', 'FECHA'], ['7790001', 5, '01/10/2026'], ['777', 2, '']],
            'SALIDAS'  => [['CODIGO DE BARRAS', 'UNIDADES', 'FECHA', 'DESTINO'], ['7790001', 1, '02/10/2026', 'Sala 1']],
        ]);

        $this->enSucursal($this->central)->post(route('admin.inventario.importar.store'), ['excel' => $archivo])
            ->assertRedirect()->assertSessionHas('import_stats');

        $this->enCentral->refresh();
        $this->assertSame('75.00', $this->enCentral->costo);
        $this->assertSame('120.00', $this->enCentral->precio_venta);
        $this->assertSame('Shampoo Central', $this->enCentral->nombre);
        $this->assertSame('Loreal', $this->enCentral->marca);
        $this->assertSame(14, $this->enCentral->stock());

        $this->assertDatabaseHas('productos', ['sucursal_id' => $this->central->id, 'codigo_barras' => '777', 'nombre' => 'Crema']);
        // La otra sucursal no recibe nada.
        $this->assertSame(4, $this->enNorte->stock());
        $this->assertSame(1, Producto::where('sucursal_id', $this->norte->id)->count());
    }

    public function test_importar_movimientos_de_reventa_van_a_su_stock(): void
    {
        $vitrina = $this->crearProducto($this->central, '888', 0, reventa: true);

        $archivo = $this->excel(['ENTRADA' => [['CODIGO DE BARRAS', 'UNIDADES'], ['888', 6]]]);

        $this->enSucursal($this->central)->post(route('admin.inventario.importar.store'), ['excel' => $archivo]);

        $this->assertSame(6, $vitrina->stock('reventa'));
        $this->assertSame(0, $vitrina->stock('tecnico'));
    }

    public function test_importar_exige_sucursal_activa(): void
    {
        $archivo = $this->excel(['ENTRADA' => [['CODIGO DE BARRAS', 'UNIDADES'], ['7790001', 6]]]);

        $this->enSucursal(null)->post(route('admin.inventario.importar.store'), ['excel' => $archivo])
            ->assertSessionHasErrors('excel');

        $this->assertSame(2, Entrada::count());
    }

    // ─── Limpiar ─────────────────────────────────────────────────────────────

    public function test_limpiar_solo_borra_la_sucursal_activa_y_guarda_backup(): void
    {
        $this->enSucursal($this->norte)->delete(route('admin.inventario.limpiar'))->assertRedirect();

        $this->assertNull(Producto::find($this->enNorte->id));
        $this->assertNotNull(Producto::find($this->enCentral->id));
        $this->assertSame(10, $this->enCentral->stock());

        $backup = InventarioBackup::firstOrFail();
        $this->assertSame(1, $backup->productos_count);
        $this->assertSame(1, $backup->entradas_count);
    }
}
