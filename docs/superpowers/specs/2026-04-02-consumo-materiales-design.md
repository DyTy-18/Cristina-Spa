# Consumo Automático de Materiales por Servicio — Diseño

## Goal

Cuando una cita se completa, descontar automáticamente del inventario los materiales usados en cada servicio, y emitir una alerta (panel + mensaje WPP preparado) cuando el stock de un producto cae por debajo del mínimo.

## Architecture

Cada servicio tiene materiales con un campo `usos_por_unidad` que indica cuántas veces rinde 1 unidad del producto (ej: shampoo 1L → 10 usos). Cada vez que una cita se completa, se registran consumos en `consumos_material`. Cuando el acumulado de consumos alcanza un múltiplo de `usos_por_unidad`, se crea una `Salida` automática de 1 unidad. Si el stock resultante cae ≤ `stock_minimo`, se crea una alerta en `alertas_stock`.

## Tech Stack

Laravel 12, MySQL, Blade, Vanilla JS. Sin librerías nuevas.

---

## Modelo de Datos

### Modificación: `servicio_materiales`
Agregar columna:
- `usos_por_unidad` `integer` `default 1` — cuántos servicios rinde 1 unidad del producto

### Nueva tabla: `consumos_material`
```
id
servicio_material_id  FK → servicio_materiales (cascadeOnDelete)
cita_id               FK → citas (cascadeOnDelete)
created_at
updated_at
```
Un registro por cada servicio completado que tiene ese material. No se duplica si la cita ya fue procesada (verificar existencia antes de insertar).

### Nueva tabla: `alertas_stock`
```
id
producto_id    FK → productos (cascadeOnDelete)
stock_actual   integer
leida          boolean default false
created_at
updated_at
```
Se crea una nueva alerta cada vez que se detecta stock bajo tras una salida automática. No se crea si ya existe una alerta no leída para ese producto.

---

## Lógica de consumo: `MaterialConsumptionService`

Archivo: `app/Services/MaterialConsumptionService.php`

Método público: `procesarCita(Cita $cita): void`

Pasos:
1. Si `$cita->estado !== 'completada'` → retornar
2. Para cada `CitaServicio` de la cita (con `servicio.materiales`):
   - Para cada `ServicioMaterial` del servicio:
     a. Si ya existe un `ConsumoMaterial` con este `servicio_material_id` + `cita_id` → skip (idempotente)
     b. Insertar registro en `consumos_material`
     c. Contar total de consumos para ese `servicio_material_id`
     d. Si `total % usos_por_unidad == 0` → crear `Salida` (unidades=1, destino='consumo_servicio', fecha=today)
     e. Calcular stock actual del producto: `SUM(entradas) - SUM(salidas)`
     f. Si stock ≤ `stock_minimo` Y no existe alerta no leída para ese producto → crear `AlertaStock`

---

## Puntos de integración

El servicio se llama en exactamente dos lugares, después de guardar `estado = 'completada'`:

- `app/Http/Controllers/Admin/CitaController.php` — método `update()`
- `app/Http/Controllers/Admin/ClienteController.php` — método `updateCitaEstado()`

Solo se invoca cuando el estado nuevo es `completada` y el estado anterior era distinto.

---

## Modelo `ConsumoMaterial`

`app/Models/ConsumoMaterial.php`
- `$fillable`: `servicio_material_id`, `cita_id`
- `belongsTo` ServicioMaterial
- `belongsTo` Cita

## Modelo `AlertaStock`

`app/Models/AlertaStock.php`
- `$fillable`: `producto_id`, `stock_actual`, `leida`
- `$casts`: `leida => boolean`
- `belongsTo` Producto
- Scope `noLeidas()`: `where('leida', false)`
- Accessor `mensaje`: retorna string formateado para WPP:
  ```
  ⚠️ Stock bajo: {producto.nombre} ({producto.marca})
  Stock actual: {stock_actual} unidades
  Mínimo recomendado: {producto.stock_minimo}
  ```

---

## Modelo `ServicioMaterial` — cambio

Agregar `usos_por_unidad` a `$fillable` y `$casts` (integer).

---

## Rutas nuevas

Dentro del grupo `admin`:
```
PATCH  /admin/alertas-stock/{alerta}/leer   → AlertaStockController@leer
```

---

## Controlador `AlertaStockController`

`app/Http/Controllers/Admin/AlertaStockController.php`

Método `leer(AlertaStock $alerta)`:
- Marca `leida = true`
- Retorna `response()->json(['ok' => true])`

---

## Interfaz

### `resources/views/admin/servicios/edit.blade.php`
- Columna "Usos/unidad" en la tabla de materiales
- Campo en el formulario de agregar material
- JS actualizado para enviar y mostrar `usos_por_unidad`

### `resources/views/admin/inventario/index.blade.php` (o el layout admin)
- Bloque de alertas no leídas en la página de inventario, encima de la tabla:
  ```
  ⚠️ [Nombre producto] — Stock: N uds. · Mín: M
     [Copiar mensaje WPP]  [Marcar leída]
  ```
- Badge con conteo en el menú lateral junto a "Inventario"

### Badge en menú lateral
- `resources/views/admin/layouts/app.blade.php` — pasar `$alertasStockCount` desde un View Composer o Share en AppServiceProvider

---

## Testing

- `tests/Feature/MaterialConsumptionTest.php`
  - Completar una cita crea registros en `consumos_material`
  - Al llegar al múltiplo exacto de `usos_por_unidad`, se crea una `Salida`
  - No se crea `Salida` si aún no se alcanzó el múltiplo
  - Stock bajo crea `AlertaStock`
  - No se duplica `AlertaStock` si ya existe una no leída
  - `procesarCita` es idempotente (llamar dos veces no duplica consumos)
  - `PATCH /alertas-stock/{id}/leer` marca la alerta como leída
