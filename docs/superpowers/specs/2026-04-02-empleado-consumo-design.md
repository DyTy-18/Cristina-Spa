# Interfaz de Empleado — Citas y Registro de Consumo

## Goal

Dar a los empleados (rol `estilista`) una vista de sus citas del día y un formulario para reportar cuántas "cabezas" (usos) usaron por material en cada servicio completado. Si usaron más de lo estándar, el sistema ajusta el inventario y puede alertar al admin.

## Architecture

La vista `mis-citas` se construye sobre la relación existente `cita_servicios.empleado_id`. Para reportar consumo, se agrega `usos_reales` a `consumos_material`. Los usos extra crean ConsumoMaterial adicionales que pasan por el `MaterialConsumptionService` existente, garantizando que el inventario y las alertas se disparen igual que con el consumo automático.

## Tech Stack

Laravel 12, PHP 8.2, MySQL, Blade, Vanilla JS. Sin librerías nuevas.

---

## Modelo de Datos

### Modificación: `consumos_material`

Agregar columna:
- `usos_reales` `integer` `nullable` — cuántas cabezas usó el empleado realmente. `null` = no reportado aún. `1` = estándar. `2+` = exceso.

### Relación empleado → usuario

`Empleado` ya tiene `user_id`. Se agrega `hasOne` `empleado()` en `User` (si no existe) para que `auth()->user()->empleado` funcione.

---

## Rutas nuevas

Dentro del grupo `admin` con middleware `auth`:

```
GET   /admin/mis-citas                          → MisCitasController@index
GET   /admin/mis-citas/{cita}/consumo           → MisCitasController@consumo
POST  /admin/mis-citas/{cita}/consumo           → MisCitasController@guardarConsumo
```

---

## Controlador `MisCitasController`

`app/Http/Controllers/Admin/MisCitasController.php`

### `index()`
- Obtiene el `Empleado` del usuario logueado: `auth()->user()->empleado`
- Si no tiene empleado vinculado → redirect con error
- Consulta: `CitaServicio::where('empleado_id', $empleado->id)->whereHas('cita', fn($q) => $q->whereDate('fecha', today()))->with('cita.cliente', 'servicio')->get()`
- Agrupa por `cita_id` para mostrar una fila por cita
- Pasa al view: `$citasHoy` (colección de citas con sus servicios del empleado)

### `consumo(Cita $cita)`
- Verifica que el empleado esté asignado a esa cita: `abort_if` si ningún `CitaServicio` de la cita tiene `empleado_id = $empleado->id`
- Verifica que la cita esté `completada`
- Carga `consumos_material` existentes para esa cita (con `usos_reales`)
- Pasa al view: `$cita`, `$serviciosMateriales` (materiales por servicio, con consumo actual si ya fue reportado)

### `guardarConsumo(Request $request, Cita $cita)`
- Misma verificación de ownership y estado
- Valida: `materiales.*.usos_reales` → `required|integer|min:1`
- Por cada `consumo_material_id` + `usos_reales`:
  1. Actualiza `consumos_material.usos_reales = N`
  2. Si `N > 1`: llama `MaterialConsumptionService::procesarUsosExtra($consumoMaterial, $usos_extra = N - 1)`
- Redirige de vuelta a `mis-citas` con mensaje de éxito

---

## Método nuevo en `MaterialConsumptionService`

`procesarUsosExtra(ConsumoMaterial $consumo, int $usosExtra): void`

Por cada uso extra (1 a `$usosExtra`):
1. Crea un ConsumoMaterial nuevo para el mismo `servicio_material_id` + `cita_id` (sin restricción de unicidad — múltiples permitidos para ajustes)
2. Recalcula `totalConsumos % usosPorUnidad` → si cero, crea Salida + evalúa AlertaStock

**Nota:** La tabla `consumos_material` NO tiene unique constraint en `(servicio_material_id, cita_id)`, por lo que múltiples registros son posibles. El check de idempotencia en `procesarCita()` sigue intacto (solo evita duplicar el primer consumo automático).

---

## Modelo `ConsumoMaterial` — cambio

Agregar `usos_reales` a `$fillable` y `$casts` (integer nullable).

---

## Modelo `User` — cambio

Agregar relación:
```php
public function empleado(): HasOne
{
    return $this->hasOne(Empleado::class);
}
```

---

## Vistas

### `resources/views/admin/mis-citas/index.blade.php`

Tabla de citas de hoy del empleado:
- Columnas: Hora, Cliente, Servicio(s), Estado (badge color), Acciones
- Solo citas con `estado = completada` muestran botón `Registrar consumo`
- Citas pendiente/confirmada/cancelada solo se muestran informativas
- Si no hay citas: empty state "No tenés citas asignadas para hoy"

### `resources/views/admin/mis-citas/consumo.blade.php`

Formulario de consumo:
- Header: nombre del cliente, hora de la cita
- Por cada servicio del empleado en esa cita:
  - Título del servicio
  - Por cada material del servicio: nombre del producto, marca, cantidad estándar + unidad, usos_por_unidad
  - Input `number` (min=1, default=1) para `usos_reales`
  - Si `usos_reales > 1` (ya guardado): badge naranja `⚠️ exceso`
  - Si `usos_reales` ya fue reportado: mostrar valor previo como default del input
- Botón "Guardar consumo"
- Link "← Volver a mis citas"

---

## Testing

`tests/Feature/MisCitasTest.php`

- Empleado ve solo sus citas de hoy (no las de otros empleados)
- No ve citas de otros días
- No puede acceder a consumo de una cita que no le pertenece (403/404)
- No puede reportar consumo en cita no completada
- `guardarConsumo` con `usos_reales = 1` actualiza el campo, no crea consumos extra
- `guardarConsumo` con `usos_reales = 3` crea 2 ConsumoMaterial adicionales
- Los consumos extra pueden gatillar Salida si alcanzan el umbral
- Usuario sin empleado vinculado es redirigido con error
