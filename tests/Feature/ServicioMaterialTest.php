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
