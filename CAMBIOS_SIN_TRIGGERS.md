# 🔄 Cambios Implementados: Gestión de col4 y saldo_disponible SIN TRIGGERS

## Objetivo
Cuando se **agrega**, **edita** o **elimina** un certificado de `detalle_certificados`:
- **col4** se actualiza (suma/resta el monto)
- **saldo_disponible** se recalcula como `col3 - col4`

## Cambios en `app/models/Certificate.php`

### 1. Métodos Privados Agregados

#### `updatePresupuestoAddCertificado($codigo_completo, $monto)`
Se ejecuta cuando se **agregan** items o el monto **aumenta**.

```php
// Sumar monto a col4
// Recalcular saldo_disponible = col3 - col4
UPDATE presupuesto_items 
SET col4 = col4 + monto,
    saldo_disponible = col3 - (col4 + monto)
WHERE codigo_completo = ?
```

#### `updatePresupuestoRemoveCertificado($codigo_completo, $monto)`
Se ejecuta cuando se **eliminan** items o el monto **disminuye**.

```php
// Restar monto de col4
// Recalcular saldo_disponible = col3 - col4
UPDATE presupuesto_items 
SET col4 = col4 - monto,
    saldo_disponible = col3 - (col4 - monto)
WHERE codigo_completo = ?
```

### 2. Métodos Modificados

#### `createDetail($data)` - AGREGAR ITEM
**ANTES:** Solo insertaba en `detalle_certificados`.
**AHORA:** Además llama a `updatePresupuestoAddCertificado()`.

```
Cuando se agrega un item:
1. INSERT en detalle_certificados
2. col4 += monto (se suma el nuevo monto)
3. saldo_disponible = col3 - col4
```

#### `update($id, $data)` - EDITAR ITEM
**ANTES:** Solo actualizaba `detalle_certificados`.
**AHORA:** Detecta cambio de monto y ajusta presupuesto.

```
Cuando se edita un item:
1. Obtener monto anterior
2. UPDATE en detalle_certificados
3. Si monto_nuevo > monto_anterior:
   - col4 += diferencia
4. Si monto_nuevo < monto_anterior:
   - col4 -= diferencia
5. saldo_disponible = col3 - col4
```

#### `deleteDetail($id)` - ELIMINAR ITEM
**NUEVO método creado.**

```
Cuando se elimina un item:
1. DELETE de detalle_certificados
2. col4 -= monto (se resta el monto eliminado)
3. saldo_disponible = col3 - col4
```

#### `delete($id)` - ELIMINAR CERTIFICADO COMPLETO
**MODIFICADO:** Ahora itera sobre cada item y llama `deleteDetail()`.

```
Cuando se elimina un certificado:
1. Obtener todos los items del certificado
2. Para cada item:
   - Llamar deleteDetail() (actualiza presupuesto)
3. DELETE del certificado maestro
```

#### `updateLiquidacion($detalle_id, $cantidad_liquidacion)`
**MODIFICADO:** Ahora actualiza col4 basado en cantidad_pendiente.

```
Cuando se actualiza la liquidación:
1. Calcular nueva cantidad_pendiente = monto - cantidad_liquidacion
2. Obtener diferencia de pendiente
3. Llamar updatePresupuestoAddCertificado() con la diferencia
   (Si pendiente AUMENTA → col4 AUMENTA)
   (Si pendiente DISMINUYE → col4 DISMINUYE)
4. UPDATE detalle_certificados
5. Recalcular totales en certificados
```

## Lógica de col4 y saldo_disponible

### Fórmula
```
col4 = suma de cantidad_pendiente de todos los items del certificado
saldo_disponible = col3 - col4
```

### Ejemplos

#### Caso 1: Agregar un item de $1000
```
Antes:
  col3 = 5000
  col4 = 0
  saldo_disponible = 5000

Agregar item: monto=1000, cantidad_liquidacion=0, cantidad_pendiente=1000
  col4 = 0 + 1000 = 1000
  saldo_disponible = 5000 - 1000 = 4000

Después:
  col3 = 5000
  col4 = 1000 ✓
  saldo_disponible = 4000 ✓
```

#### Caso 2: Liquidar $500 del item
```
Antes:
  col4 = 1000
  cantidad_pendiente = 1000
  saldo_disponible = 4000

Liquidar $500:
  nueva cantidad_liquidacion = 500
  nueva cantidad_pendiente = 1000 - 500 = 500
  diferencia_pendiente = 500 - 1000 = -500
  
  col4 = 1000 + (-500) = 500
  saldo_disponible = 5000 - 500 = 4500

Después:
  col4 = 500 ✓
  cantidad_pendiente = 500 ✓
  saldo_disponible = 4500 ✓
```

#### Caso 3: Eliminar el item
```
Antes:
  col4 = 500
  saldo_disponible = 4500

Eliminar item:
  col4 = 500 - 1000 = max(0, -500) = 0
  saldo_disponible = 5000 - 0 = 5000

Después:
  col4 = 0 ✓
  saldo_disponible = 5000 ✓
```

## Validaciones

- No hay triggers en la base de datos
- Todo se maneja en PHP puro
- Se usa `max(0, ...)` para evitar col4 negativo
- Se registran logs en error_log para debugging

## Testing

Para verificar que funciona:

1. **Agregar certificado con items:**
   - Verificar que col4 aumenta
   - Verificar que saldo_disponible disminuye

2. **Editar monto de un item:**
   - Si monto aumenta: col4 aumenta, saldo_disponible disminuye
   - Si monto disminuye: col4 disminuye, saldo_disponible aumenta

3. **Liquidar un item:**
   - cantidad_pendiente disminuye
   - col4 disminuye (menos a certificar)
   - saldo_disponible aumenta (más disponible)

4. **Eliminar un item:**
   - col4 se reduce por el monto
   - saldo_disponible se recupera

## Archivos Modificados

- `app/models/Certificate.php`: +50 líneas (métodos nuevos y modificados)

## Notas

- No requiere cambios en base de datos
- No requiere crear/eliminar triggers
- Funciona con MySQL, PostgreSQL, SQLite
- Los logs se escriben en `error_log()` para debugging
