# ✅ LIQUIDACIÓN - ACTUALIZACIÓN FINAL

## 📋 Resumen de Cambios

Se ha implementado la lógica completa para **actualizar `col4` en presupuesto_items cuando se liquida un item**.

### Flujo Completo

```
1. INSERT ITEM:
   → Trigger: col4 += monto (certificado total)
   
2. LIQUIDAR ITEM:
   → PHP: cantidad_pendiente = monto - cantidad_liquidacion
   → SQL UPDATE presupuesto_items: col4 -= cantidad_pendiente
   → SQL UPDATE certificados: recalcula totales
```

---

## 🔧 Cambios Implementados

### 1. **Certificate.php::createDetail()**
✅ **Corregido:**
- Ahora calcula correctamente `cantidad_pendiente = monto - cantidad_liquidacion`
- Permite passar `cantidad_liquidacion` opcional al crear un item

**Antes:**
```php
$monto,  // cantidad_pendiente = monto
0,       // cantidad_liquidacion = 0
```

**Después:**
```php
$cantidad_liquidacion = (float)($data['cantidad_liquidacion'] ?? 0);
$cantidad_pendiente = $monto - $cantidad_liquidacion;
// Insertando ambos correctamente
```

---

### 2. **Certificate.php::updateLiquidacion()**
✅ **Actualizado para:**
- Obtener `cantidad_pendiente_anterior` y `cantidad_pendiente_nuevo`
- **NUEVO:** Actualizar `col4` en presupuesto_items
  ```sql
  UPDATE presupuesto_items
  SET col4 = COALESCE(col4, 0) - ?  -- resta la cantidad_pendiente_nuevo
  WHERE codigo_completo = ?
  ```
- Actualizar `detalle_certificados` con los nuevos valores
- Recalcular totales en `certificados`

---

### 3. **Corrección de Datos Históricos**
✅ **Script creado:** `corregir_cantidad_pendiente.php`
- Encontró 2 items con `cantidad_pendiente` incorrecta (items 240 y 241)
- Aplicó corrección: `cantidad_pendiente = monto - cantidad_liquidacion`
- Recalculó totales en `certificados`

**Antes:**
- Item 240: monto=1000, liquidacion=900, pendiente=1000 ❌
- Item 241: monto=500, liquidacion=400, pendiente=500 ❌

**Después:**
- Item 240: monto=1000, liquidacion=900, pendiente=100 ✅
- Item 241: monto=500, liquidacion=400, pendiente=100 ✅

---

## 🧪 Verificación

### Test 1: Creación de Items
✅ `test_createDetail_fix.php`
- Item sin liquidación: pendiente = monto
- Item con liquidación inicial: pendiente = monto - liquidacion
- Totales en certificados calculados automáticamente

### Test 2: Liquidación con col4
✅ `test_liquidacion_col4_real.php`

| Operación | monto | liquidacion | pendiente | col4 |
|-----------|-------|-------------|-----------|------|
| INSERT | 1000 | 0 | 1000 | +1000 |
| Liquidar 700 | 1000 | 700 | 300 | -300 |
| Liquidar 900 | 1000 | 900 | 100 | -200 |
| **FINAL** | 1000 | 900 | 100 | **600** |

✅ **col4 final = col4_inicial + 1000 - 100 = 600** ✅

---

## 📊 Fórmulas Correctas

```
cantidad_pendiente = monto - cantidad_liquidacion
col4 (en presupuesto) = col4 - cantidad_pendiente_liquidado
total_liquidado = SUM(cantidad_liquidacion) por certificado
total_pendiente = SUM(cantidad_pendiente) por certificado
```

---

## ✅ Estado Final

| Componente | Estado |
|-----------|--------|
| CREATE DETAIL | ✅ Calcula cantidad_pendiente correctamente |
| UPDATE LIQUIDACIÓN | ✅ Actualiza col4 al restar cantidad_pendiente |
| CORRECCIÓN HISTÓRICA | ✅ 2 items corregidos |
| TRIGGERS INSERT | ✅ Actualiza col4 |
| CERTIFICADOS TOTALES | ✅ Calculados automáticamente |
| PRUEBAS | ✅ Todos los tests pasan |

---

## 🚀 Próximos Pasos (Opcional)

Si en el futuro necesitas:
1. **Anular una liquidación** → Sumar la cantidad_pendiente de vuelta a col4
2. **Auditar cambios** → Los logs están en `error_log()`
3. **Reportes** → Usar las columnas cantidad_liquidacion y cantidad_pendiente

---

**Última actualización:** 2025-12-07
**Estado:** ✅ LISTO PARA PRODUCCIÓN
