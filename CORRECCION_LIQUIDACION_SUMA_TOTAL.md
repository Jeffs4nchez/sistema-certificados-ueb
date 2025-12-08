# 🔧 CORRECCIÓN: Liquidación Correcta con Suma Total de Pendientes

## Problema Identificado

El código anterior solo restaba `cantidad_pendiente` del **item actual** a col4, pero debería restar la **SUMA TOTAL** de `cantidad_pendiente` de **TODOS los items** que compartan el mismo `codigo_completo`.

---

## ✅ Solución Implementada

### Nueva Lógica

```
Para cada liquidación:

1. Calcular cantidad_pendiente = monto - cantidad_liquidacion (del item actual)

2. UPDATE detalle_certificados (solo para este item)

3. SELECT SUM(cantidad_pendiente) FROM detalle_certificados 
   WHERE codigo_completo = 'código del item'
   → Obtener SUMA TOTAL de pendientes de todos los items

4. col4 = suma_total_pendiente (el valor final, no resta)

5. saldo_disponible = col3 - col4

6. UPDATE presupuesto_items con los nuevos valores

7. Recalcular totales en certificados
```

---

## 📊 Ejemplo Práctico

### Presupuesto $5000 con 2 Items del mismo código

#### Estado Inicial
```
Presupuesto: codigo=82 00 000 002 003 0200 510203
  col3 = 5000
  col4 = 2000 (suma de los dos items sin liquidar)
  saldo_disponible = 3000

Item 1:
  monto = 1000
  cantidad_liquidacion = 0
  cantidad_pendiente = 1000

Item 2:
  monto = 1000
  cantidad_liquidacion = 0
  cantidad_pendiente = 1000
```

#### Liquidación 1: Liquidar $400 del Item 1
```
Input: detalle_id=1, cantidad_liquidacion=400

Paso 1:
  cantidad_pendiente (item 1) = 1000 - 400 = 600
  UPDATE detalle_certificados SET cantidad_pendiente=600 WHERE id=1

Paso 2:
  SELECT SUM(cantidad_pendiente) = 600 + 1000 = 1600
  (600 del item 1 + 1000 del item 2)

Paso 3:
  col4 = 1600 (suma total)
  saldo = 5000 - 1600 = 3400

Resultado:
  Item 1: pendiente = 600 ✅
  Item 2: pendiente = 1000 (sin cambios) ✅
  col4 = 1600 ✅
  saldo_disponible = 3400 ✅
```

#### Liquidación 2: Liquidar $500 más del Item 1 (total $900)
```
Input: detalle_id=1, cantidad_liquidacion=900

Paso 1:
  cantidad_pendiente (item 1) = 1000 - 900 = 100
  UPDATE detalle_certificados SET cantidad_pendiente=100 WHERE id=1

Paso 2:
  SELECT SUM(cantidad_pendiente) = 100 + 1000 = 1100
  (100 del item 1 + 1000 del item 2)

Paso 3:
  col4 = 1100 (suma total actualizada)
  saldo = 5000 - 1100 = 3900

Resultado:
  Item 1: pendiente = 100 ✅
  Item 2: pendiente = 1000 (sin cambios) ✅
  col4 = 1100 ✅
  saldo_disponible = 3900 ✅
```

#### Liquidación 3: Liquidar $600 del Item 2
```
Input: detalle_id=2, cantidad_liquidacion=600

Paso 1:
  cantidad_pendiente (item 2) = 1000 - 600 = 400
  UPDATE detalle_certificados SET cantidad_pendiente=400 WHERE id=2

Paso 2:
  SELECT SUM(cantidad_pendiente) = 100 + 400 = 500
  (100 del item 1 + 400 del item 2)

Paso 3:
  col4 = 500 (suma total actualizada)
  saldo = 5000 - 500 = 4500

Resultado:
  Item 1: pendiente = 100 (sin cambios) ✅
  Item 2: pendiente = 400 ✅
  col4 = 500 ✅
  saldo_disponible = 4500 ✅
```

---

## 🔑 Diferencia ANTES vs AHORA

### ANTES (Incorrecto)
```php
// Solo restaba el pendiente del item actual
col4 -= cantidad_pendiente_nuevo;  // ❌ Incorrecto si hay múltiples items

// Ejemplo: liquidar $400 del item 1 (monto=1000)
// col4 = 2000 - 600 = 1400  ❌ Falta el pendiente del item 2
```

### AHORA (Correcto)
```php
// Suma total de TODOS los pendientes del codigo_completo
$suma_total = SELECT SUM(cantidad_pendiente) 
             WHERE codigo_completo = ?

col4 = $suma_total;  // ✅ Correcto con múltiples items

// Ejemplo: liquidar $400 del item 1 (monto=1000)
// col4 = 600 (item 1) + 1000 (item 2) = 1600  ✅ Correcto
```

---

## 📝 Cambios en el Código

### En `updateLiquidacion()`

**PASO 5 - ANTES:**
```php
$col4_nuevo = max(0, $col4 - $cantidad_pendiente_nuevo);
```

**PASO 5 - AHORA:**
```php
// Obtener SUMA TOTAL de cantidad_pendiente de TODOS los items
$stmtSumaTotal = $this->db->prepare("
    SELECT COALESCE(SUM(cantidad_pendiente), 0) as suma_total_pendiente
    FROM detalle_certificados
    WHERE codigo_completo = ?
");
$stmtSumaTotal->execute([$codigo_completo]);
$resultado = $stmtSumaTotal->fetch();
$suma_total_pendiente = (float)($resultado['suma_total_pendiente'] ?? 0);

// col4 = suma total (no resta)
$col4_nuevo = $suma_total_pendiente;
```

---

## ✅ Validaciones

El código valida:
- ✅ Cantidad_liquidacion ≤ monto del item
- ✅ Cantidad_liquidacion ≥ 0
- ✅ Detalle existe en BD
- ✅ Código_completo existe en presupuesto
- ✅ Suma total de pendientes se obtiene correctamente
- ✅ saldo_disponible nunca es negativo (col3 > 0)

---

## 🧪 Cómo Verificar

### SQL de Prueba

```sql
-- Ver todos los items del código y su suma
SELECT 
  id, monto, cantidad_liquidacion, cantidad_pendiente
FROM detalle_certificados
WHERE codigo_completo = '82 00 000 002 003 0200 510203'
ORDER BY id;

-- Verificar que col4 = suma de pendientes
SELECT col4, 
       (SELECT SUM(cantidad_pendiente) 
        FROM detalle_certificados 
        WHERE codigo_completo = presupuesto_items.codigo_completo) as suma_pendientes
FROM presupuesto_items
WHERE codigo_completo = '82 00 000 002 003 0200 510203';

-- Resultado esperado:
-- col4 = suma_pendientes ✅
```

---

## 🎯 Resumen

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| Actualiza | Item actual | Item actual + todos |
| col4 calcula | Resta individual | Suma total |
| Múltiples items | ❌ Incorrecto | ✅ Correcto |
| saldo_disponible | Derivado | col3 - col4 |
| Precisión | Media | Alta |

---

**Status:** ✅ CORREGIDO

**Versión:** 3.0 (Liquidación con Suma Total)

**Fecha:** 8 de Diciembre de 2025
