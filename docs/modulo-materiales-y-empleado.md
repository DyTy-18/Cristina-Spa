# Módulo: Materiales de Servicio e Inventario

**¿Qué hace este módulo?**
Permite que el sistema sepa exactamente qué productos se usan en cada servicio del spa,
y que cada estilista reporte cuánto usó realmente. Con eso, el inventario se actualiza solo.

---

## ¿Para qué sirve?

Antes, nadie sabía con certeza cuánto shampoo, tinte o mascarilla se gastaba por día.
Ahora el sistema lleva la cuenta automáticamente:

- Cuando una cita se marca como **completada**, el sistema registra el uso de materiales.
- Si el estilista usó más de lo normal (por ejemplo, cabello muy largo), puede registrarlo y el sistema ajusta el inventario.
- Cuando un bote se agota completamente, **el sistema descuenta 1 unidad del stock** y empieza a contar el siguiente.
- Cuando el stock queda con pocas unidades, el admin recibe una **alerta automática**.

---

## Parte 1 — Configuración de Materiales por Servicio

> Esta parte la maneja el **administrador** desde _Servicios → Editar servicio_.

### ¿Qué se configura?

Para cada servicio (Corte de Mujer, Balayage, Retoque de Raíz, etc.) se define qué productos usa y en qué cantidad:

| Campo | Ejemplo | ¿Qué significa? |
|---|---|---|
| Producto | Kérastase Bain Fluidealist | El producto del inventario que se usa |
| Cantidad | 30 ml | Cuánto se usa por cada cliente |
| Unidad | ml | Mililitros, gramos, unidades, etc. |
| Usos por unidad | 33 | Cuántos clientes rinde 1 frasco/tubo/envase |

### 📸 [Foto: pantalla de edición de servicio con lista de materiales]

---

### El concepto clave: **Usos por unidad**

Imagina un frasco de shampoo de 1000 ml. Si por cada cliente se usan 30 ml, ese frasco rinde para **33 clientes** (1000 ÷ 30 ≈ 33).

El sistema lleva la cuenta de cada uso. Cuando llega a 33 usos acumulados entre todas las citas, descuenta 1 frasco del inventario. Luego empieza a contar el siguiente frasco desde cero.

```
Frasco de 1000 ml  →  33 usos
  Uso 1  (cita del 01/04)
  Uso 2  (cita del 01/04)
  ...
  Uso 33 (cita del 05/04)  →  ¡se descuenta 1 frasco del stock!
  Uso 1  del siguiente frasco...
```

---

## Parte 2 — El Indicador del Bote en Uso

En la pantalla de registro de consumo, cada material muestra:

```
Bote en uso: le quedan 29/33 usos — al agotarse se descuenta 1 del stock
```

Esto significa que el bote actual ya lleva 4 usos registrados (entre varias citas anteriores) y le quedan 29 antes de que el sistema descuente un frasco del stock.

| Lo que ves | Qué significa |
|---|---|
| `29/33` | 29 usos disponibles de los 33 totales del bote |
| Número en **negro** | Todo bien, el bote tiene suficiente |
| Número en **amarillo** | Queda poco en el bote (≤ 10 usos) |
| Número en **rojo** | El bote está por agotarse (≤ 3 usos) |
| `Stock: 7 unid.` | Hay 7 botes/frascos en el almacén |

---

## Parte 3 — Descuento Automático del Inventario

> Esto ocurre **sin que nadie haga nada**. Es automático.

### ¿Cuándo se descuenta?

Cuando una cita se marca como **Completada**, el sistema:

1. Mira qué servicios tuvo esa cita
2. Para cada servicio, busca sus materiales configurados
3. Registra 1 uso por cada material en el bote en curso
4. Si ese bote llega a su límite (ej: 33 usos), descuenta 1 unidad del stock
5. Si el stock queda bajo el mínimo configurado → genera una **alerta de stock**

El estilista puede ajustar la cantidad si usó más o menos de lo normal (ver Parte 4).

### 📸 [Foto: pantalla de cambio de estado de una cita a "Completada"]

---

## Parte 4 — Registro de Consumo Manual (vista del Estilista)

> El estilista accede desde _Mis Citas_ → botón **Registrar consumo** (solo en citas completadas).

### ¿Qué ve el estilista?

- El nombre del cliente y la fecha/hora de la cita
- Los servicios que realizó, cada uno con su lista de materiales
- Para cada material: cuánto es lo estándar y cuántas **aplicaciones** hizo realmente
- El estado del bote actual (cuántos usos le quedan)

### 📸 [Foto: formulario de consumo con materiales y campos de aplicaciones]

---

### El campo "Aplicaciones"

**1 aplicación = 1 uso estándar** de ese material en esa cita.

| El estilista pone | Significa |
|---|---|
| `0` | No usó ese material en esta cita |
| `1` | Usó la cantidad normal (valor por defecto) |
| `2` | Usó el doble — cabello muy largo, doble proceso, etc. |
| `3` | Usó el triple |

El sistema suma las aplicaciones al contador del bote. Cuando el bote se llena, descuenta 1 del stock.

**Ejemplo con shampoo (33 usos/frasco):**

| Cita | Aplicaciones | Total acumulado | ¿Descuenta stock? |
|---|---|---|---|
| Cita 1 | 1 | 1/33 | No |
| Cita 2 | 2 | 3/33 | No |
| Cita 3 | 1 | 4/33 | No |
| … | … | … | … |
| Cita N | 1 | 33/33 | ✅ Sí, baja 1 del stock |
| Cita N+1 | 1 | 1/33 (nuevo bote) | No |

---

### Validaciones

- Si el stock está en **0**, no se permite registrar más usos que los ya guardados.
- Si bajar el conteo requiere **devolver** unidades al stock (por corrección), el sistema lo hace automáticamente.
- No se puede guardar si el incremento requiere más stock del disponible.

### 📸 [Foto: mensaje de error de stock insuficiente]

---

## Parte 5 — Alertas de Stock Bajo

> Esta parte la ve el **administrador**.

Cuando el stock de un producto cae por debajo de su mínimo, el sistema crea una alerta automática:

- Aparece como un **badge rojo** en la barra lateral junto a "Inventario"
- El admin puede copiar el mensaje de WhatsApp para avisar al proveedor
- Se puede marcar como leída para limpiar el badge

### 📸 [Foto: badge rojo en el sidebar con número de alertas]

### 📸 [Foto: pantalla de inventario con alertas activas]

---

## Parte 6 — Informe por Cita

Desde el detalle de cualquier cita (_Citas → Ver cita → 📋 Ver informe_) se puede acceder a un resumen imprimible que incluye:

- Datos del cliente (nombre, teléfono)
- Fecha, hora y estado
- Servicios realizados con el empleado que los atendió y el precio
- Materiales utilizados con las aplicaciones registradas
- Notas de la cita

Es útil para auditorías, revisión de consumo por cliente, o simplemente como comprobante interno.

### 📸 [Foto: pantalla de informe de cita con servicios y materiales]

---

## Resumen del flujo completo

```
Admin configura materiales por servicio (una sola vez)
          ↓
Se realiza el servicio y la cita se marca como Completada
          ↓
Sistema registra 1 uso por material en el bote actual
          ↓
¿El estilista usó más o menos de lo normal?
  SÍ → Entra a "Registrar consumo" y ajusta las aplicaciones
  NO → El sistema ya registró 1 aplicación, no hay nada más que hacer
          ↓
¿El bote llegó a su límite de usos?
  SÍ → Se descuenta 1 unidad del stock. Empieza el siguiente bote.
  NO → Sigue acumulando
          ↓
¿El stock quedó por debajo del mínimo?
  SÍ → Se crea una alerta de stock bajo para el admin
  NO → Todo en orden
```

---

## Preguntas frecuentes

**¿Qué pasa si el estilista no registra el consumo?**
El sistema registra 1 aplicación por defecto. Si en realidad usó más, se pierde esa información y el stock no se descuenta correctamente. Por eso es importante registrar cuando se usa más de lo normal.

**¿Se puede corregir un consumo ya guardado?**
Sí. Se puede volver a entrar al formulario y cambiar el número. El sistema calcula la diferencia y ajusta el stock hacia arriba o hacia abajo según corresponda.

**¿Se puede poner 0 aplicaciones?**
Sí. Si un material no se usó en esa cita, se pone 0 y el sistema revierte el uso que había registrado automáticamente.

**¿Qué pasa si un servicio no tiene materiales configurados?**
No aparece nada en el formulario de consumo. El inventario no se ve afectado.

**¿Quién puede configurar los materiales de un servicio?**
Solo el rol `admin` desde la sección de Servicios.

**¿Quién puede ver y registrar consumo?**
El estilista (`estilista`) solo puede ver sus propias citas y registrar consumo de las que él atendió.

**¿Quién puede ver el inventario completo?**
Solo el rol `admin`. Los estilistas solo interactúan con el consumo a través del formulario de su cita.

**¿Puede el sistema equivocarse en el conteo?**
El sistema usa un contador acumulado (`total_usos_procesados`) que es la fuente de verdad. Si en algún momento los datos históricos tienen registros duplicados del sistema anterior, se puede recalcular ese contador desde la base de datos sin pérdida de información.
