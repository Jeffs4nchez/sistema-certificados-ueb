# 🐛 DEBUG: Liquidación con Logs Detallados

## Problema

Cuando se hacía liquidación, no se estaba actualizando correctamente `cantidad_pendiente` en `detalle_certificados`.

## Solución Implementada

Se agregaron **logs detallados** en cada paso de `updateLiquidacion()` para rastrear exactamente qué está pasando y dónde puede estar el problema.

---

## 📝 Logs Agregados

### 1. INICIO
```
📌 Liquidación INICIO: id=51, monto=1000, codigo=82 00 000 002 003 0200 510203, cantidad_liq_input=500
```

### 2. CÁLCULO
```
📌 Calculado: cantidad_pendiente=500 (monto=1000 - liq=500)
```

### 3. UPDATE DETALLE_CERTIFICADOS
```
✅ detalle_certificados actualizado: id=51, cantidad_liq=500, cantidad_pend=500
```

### 4. VERIFICACIÓN (NUEVO)
```
✅ Verificación: cantidad_liq_en_bd=500, cantidad_pend_en_bd=500
```
**IMPORTANTE:** Este paso verifica que el UPDATE se guardó en la BD.

### 5. SUMA TOTAL
```
✅ Suma total pendiente obtenida: 1500 para codigo=82 00 000 002 003 0200 510203
```

### 6. PRESUPUESTO ANTES/DESPUÉS
```
📌 Presupuesto ANTES: col3=5000, col4=2000, saldo=3000
📌 Presupuesto NUEVO: col3=5000, col4=1500, saldo=3500
✅ presupuesto_items actualizado: codigo=82 00 000 002 003 0200 510203, col4=1500, saldo=3500
```

### 7. CERTIFICADOS
```
📌 Certificados ANTES: total_liq_anterior, total_pend_anterior
✅ Certificados NUEVO: total_liq=1500, total_pend=2500
✅ Certificado actualizado: id=1, total_liq=1500, total_pend=2500
```

### 8. ERRORES
```
❌ ERROR en liquidación: La liquidación (1500) no puede superar el monto (1000)
❌ TRACE: [stack trace completo]
```

---

## 🔍 Cómo Debuggear

### Paso 1: Revisar los logs
```bash
tail -f /path/to/error_log | grep "Liquidación"
```

Verás un flujo como:
```
📌 Liquidación INICIO: ...
📌 Calculado: ...
✅ detalle_certificados actualizado: ...
✅ Verificación: ...
✅ Suma total pendiente obtenida: ...
✅ presupuesto_items actualizado: ...
✅ Certificado actualizado: ...
```

### Paso 2: Si hay error
Si ves un `❌ ERROR`, sabrás exactamente dónde falló:
- ❌ En validación
- ❌ En UPDATE detalle_certificados
- ❌ En UPDATE presupuesto_items
- ❌ En UPDATE certificados

### Paso 3: Verificar en BD
```sql
SELECT id, monto, cantidad_liquidacion, cantidad_pendiente 
FROM detalle_certificados 
WHERE id = 51;
```

Deberías ver:
```
id | monto | cantidad_liquidacion | cantidad_pendiente
51 | 1000  | 500                  | 500
```

---

## ✅ Mejoras Realizadas

1. ✅ **Validación de UPDATE**: Se verifica que el UPDATE se ejecutó correctamente
2. ✅ **Verificación POST-UPDATE**: Se consulta la BD para confirmar que se guardó
3. ✅ **Logs por etapa**: Cada paso tiene su propio log
4. ✅ **Error info**: Si falla, muestra el error de PDO
5. ✅ **Trace completo**: Incluye el stack trace del error

---

## 📊 Flujo Completo con Logs

```
INPUT: updateLiquidacion(51, 500)
    ↓
📌 INICIO - Log de entrada
    ↓
✓ Obtener detalle
    ↓
✓ Validar cantidad
    ↓
📌 CALCULAR - Log de cálculo
    ↓
✓ UPDATE detalle_certificados
    ↓
✅ Log de éxito
    ↓
✅ VERIFICAR - SELECT para confirmar
    ↓
✓ SUMA TOTAL de cantidad_pendiente
    ↓
✅ Log de suma
    ↓
📌 PRESUPUESTO ANTES/NUEVO - Logs comparativos
    ↓
✓ UPDATE presupuesto_items
    ↓
✅ Log de éxito
    ↓
✓ Recalcular totales de certificados
    ↓
✓ UPDATE certificados
    ↓
✅ Log de éxito
    ↓
RETURN resultado
```

---

## 🧪 Ejemplo de Ejecución

### Entrada
```
Liquidar 500 del item 51 (monto=1000)
```

### Logs Esperados
```
📌 Liquidación INICIO: id=51, monto=1000, codigo=82 00 000 002 003 0200 510203, cantidad_liq_input=500
📌 Calculado: cantidad_pendiente=500 (monto=1000 - liq=500)
✅ detalle_certificados actualizado: id=51, cantidad_liq=500, cantidad_pend=500
✅ Verificación: cantidad_liq_en_bd=500, cantidad_pend_en_bd=500
✅ Suma total pendiente obtenida: 500 para codigo=82 00 000 002 003 0200 510203
📌 Presupuesto ANTES: col3=5000, col4=1000, saldo=4000
📌 Presupuesto NUEVO: col3=5000, col4=500, saldo=4500
✅ presupuesto_items actualizado: codigo=82 00 000 002 003 0200 510203, col4=500, saldo=4500
📌 Certificados ANTES: total_liq_anterior, total_pend_anterior
✅ Certificados NUEVO: total_liq=500, total_pend=500
✅ Certificado actualizado: id=1, total_liq=500, total_pend=500
```

### Salida
```
Array (
    [success] => 1
    [detalle_id] => 51
    [cantidad_liquidada] => 500
    [cantidad_pendiente] => 500
    [total_liquidado] => 500
    [total_pendiente] => 500
)
```

### Estado en BD
```sql
-- detalle_certificados
SELECT cantidad_liquidacion, cantidad_pendiente FROM detalle_certificados WHERE id=51;
-- Resultado: 500, 500 ✅

-- presupuesto_items
SELECT col4, saldo_disponible FROM presupuesto_items WHERE codigo_completo='82 00 000 002 003 0200 510203';
-- Resultado: 500, 4500 ✅

-- certificados
SELECT total_liquidado, total_pendiente FROM certificados WHERE id=1;
-- Resultado: 500, 500 ✅
```

---

## 🔑 Si Algo Falla

### Escenario 1: `cantidad_pendiente` no se actualiza
```
Busca en los logs:
❌ Error al actualizar detalle_certificados: [error details]
```

Posibles causas:
- ID de detalle incorrecto
- Permiso de BD insuficiente
- Fila no existe

### Escenario 2: col4 no cambia en presupuesto
```
Busca en los logs:
⚠️ Presupuesto no encontrado para codigo=...
```

Posibles causas:
- `codigo_completo` no existe en presupuesto_items
- `codigo_completo` es NULL en detalle_certificados

### Escenario 3: UPDATE se ejecuta pero no se guardan cambios
```
Busca en los logs:
❌ Error al actualizar detalle_certificados: [PDO error]
```

Posible causa:
- Transacción no hizo commit
- BD en modo read-only

---

## 📌 Checklist de Debugging

- [ ] ¿Aparece "📌 Liquidación INICIO"?
- [ ] ¿Aparece "✅ detalle_certificados actualizado"?
- [ ] ¿Aparece "✅ Verificación"? (confirma que se guardó)
- [ ] ¿El valor en "Verificación" es correcto?
- [ ] ¿Aparece "✅ presupuesto_items actualizado"?
- [ ] ¿Aparece "✅ Certificado actualizado"?
- [ ] ¿No hay "❌ ERROR"?

Si todas son SÍ, la liquidación está funcionando correctamente.

---

**Status:** ✅ COMPLETO CON DEBUGGING

**Versión:** 4.0 (Con Logs Detallados)

**Fecha:** 8 de Diciembre de 2025
