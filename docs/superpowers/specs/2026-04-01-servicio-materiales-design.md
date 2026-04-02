# Diseño: Materiales por Servicio

**Fecha:** 2026-04-01  
**Estado:** Aprobado

## Resumen

Añadir gestión de materiales (productos del inventario) que se usan en cada servicio. La relación es muchos-a-muchos con cantidad y unidad de medida en el pivot. La interfaz vive dentro de la página de edición del servicio como una sección independiente con operaciones AJAX.

---

## Base de Datos

### Nueva tabla: `servicio_materiales`

| columna | tipo | notas |
|---|---|---|
| `id` | bigint PK auto-increment | |
| `servicio_id` | bigint FK → servicios.id | cascade delete |
| `producto_id` | bigint FK → productos.id | cascade delete |
| `cantidad` | decimal(8,2) | ej: 30.5 |
| `unidad` | varchar(30) | ej: "ml", "gr", "unidades", "oz" |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Restricción unique:** `(servicio_id, producto_id)` — un producto no se repite en el mismo servicio.

### Nuevos modelos

- **`ServicioMaterial`** — modelo pivot con `fillable: [servicio_id, producto_id, cantidad, unidad]`
- **`Servicio`** — añadir relación `hasMany(ServicioMaterial::class)`
- **`Producto`** — añadir relación `hasMany(ServicioMaterial::class)`

---

## Rutas

Anidadas bajo el resource de servicios, dentro del middleware `auth`:

```
POST   /admin/servicios/{servicio}/materiales                    → ServicioMaterialController@store
PUT    /admin/servicios/{servicio}/materiales/{material}         → ServicioMaterialController@update
DELETE /admin/servicios/{servicio}/materiales/{material}         → ServicioMaterialController@destroy
```

---

## Controller: `ServicioMaterialController`

### `store(Request $request, Servicio $servicio)`
- Valida: `producto_id` (required, exists:productos,id, unique en el servicio), `cantidad` (required, numeric, min:0.01), `unidad` (required, string, max:30)
- Crea `ServicioMaterial`
- Retorna JSON: `{ id, producto: { id, nombre, marca }, cantidad, unidad }`

### `update(Request $request, Servicio $servicio, ServicioMaterial $material)`
- Valida: `cantidad` (required, numeric, min:0.01), `unidad` (required, string, max:30)
- Actualiza el registro
- Retorna JSON: `{ id, cantidad, unidad }`

### `destroy(Servicio $servicio, ServicioMaterial $material)`
- Elimina el registro
- Retorna JSON: `{ success: true }`

---

## Cambios a `ServicioController@edit`

Pasar datos adicionales a la vista:

```php
$materiales = $servicio->materiales()->with('producto')->get();
$productos = Producto::orderBy('nombre')->get();
```

---

## Vista: `resources/views/admin/servicios/edit.blade.php`

Añadir sección debajo del formulario existente de edición.

### Estructura de la sección

```
[Sección: Materiales del Servicio]
  [Tabla de materiales actuales]
    Columnas: Producto | Cantidad | Unidad | Acciones
    Por fila:
      - Campos cantidad/unidad editables inline con botón "Guardar" (PUT AJAX)
      - Botón "Eliminar" (DELETE AJAX con confirmación)
  
  [Mini-form: Agregar material]
    - <select> de productos (excluye los ya agregados)
    - Input cantidad (numérico)
    - Input unidad (texto libre con datalist: ml, gr, unidades, oz)
    - Botón "Agregar" (POST AJAX)
```

### Estado Alpine.js (`x-data`)

```js
{
  materiales: [...],   // lista actual cargada desde PHP
  productos: [...],    // todos los productos disponibles
  nuevo: { producto_id: '', cantidad: '', unidad: '' },
  error: '',
  loading: false
}
```

### Comportamiento AJAX

- **Agregar:** POST → on success push nuevo material a `materiales[]`, limpiar form
- **Editar inline:** PUT → on success actualizar fila en `materiales[]`
- **Eliminar:** DELETE → on success filtrar el item de `materiales[]`
- Errores de validación se muestran inline bajo el form o la fila correspondiente

---

## Archivos a crear/modificar

| archivo | acción |
|---|---|
| `database/migrations/2026_04_01_000000_create_servicio_materiales_table.php` | crear |
| `app/Models/ServicioMaterial.php` | crear |
| `app/Models/Servicio.php` | modificar (añadir relación) |
| `app/Models/Producto.php` | modificar (añadir relación) |
| `app/Http/Controllers/Admin/ServicioMaterialController.php` | crear |
| `routes/web.php` | modificar (añadir rutas anidadas) |
| `app/Http/Controllers/Admin/ServicioController.php` | modificar (edit method) |
| `resources/views/admin/servicios/edit.blade.php` | modificar (añadir sección) |

---

## Fuera de alcance

- Descuento automático de stock al completar una cita (etapa futura)
- Validación de stock disponible al agregar un material
- Historial de cambios en materiales
