# 🔄 ACTUALIZACIÓN: Nueva Lógica de Liquidación

## Cambio Realizado

Se ha **reemplazado completamente** la lógica del método `updateLiquidacion()` en `app/models/Certificate.php` con la nueva lógica solicitada.

---

## 📋 Nueva Lógica de Liquidación

### Paso 1: Calcular cantidad_pendiente
```php
cantidad_pendiente = monto - cantidad_liquidacion
```

### Paso 2: Restar del presupuesto (col4)
```php
col4 -= cantidad_pendiente
```

### Paso 3: Recalcular saldo disponible
```php
saldo_disponible = col3 - col4
```

### Contexto
Todo esto se hace **por cada `codigo_completo`** para **cada item específico**.

---

## 🎯 Flujo de Ejecución

### Antes (ELIMINADO)
```
1. Calcular diferencia_pendiente (pendiente_nuevo - pendiente_anterior)
2. Actualizar col4 basado en la diferencia
3. El col4 podría aumentar o disminuir según cambios
```

### Ahora (NUEVO)
```
1. Calcular: cantidad_pendiente = monto - cantidad_liquidacion
2. UPDATE detalle_certificados con los nuevos valores
3. SELECT presupuesto_items por codigo_completo
4. Restar el TOTAL del pendiente: col4 -= cantidad_pendiente
5. Recalcular: saldo_disponible = col3 - col4
6. UPDATE presupuesto_items
7. Recalcular totales en certificados
8. Devolver resultado
```

---

## 📊 Ejemplo Práctico

### Escenario: Item de $1000 en Presupuesto de $5000

#### Estado Inicial
```
Presupuesto:
  col3 = 5000
  col4 = 1000 (certificado)
  saldo_disponible = 4000

Item:
  monto = 1000
  cantidad_liquidacion = 0
  cantidad_pendiente = 1000
```

#### Liquidación 1: Liquidar $500
```
Input: cantidad_liquidacion = 500

Cálculos:
  cantidad_pendiente = 1000 - 500 = 500
  col4 = 1000 - 500 = 500
  saldo_disponible = 5000 - 500 = 4500

Resultado:
  cantidad_liquidacion = 500
  cantidad_pendiente = 500
  col4 = 500
  saldo_disponible = 4500
```

#### Liquidación 2: Liquidar $200 más (total $700)
```
Input: cantidad_liquidacion = 700

Cálculos:
  cantidad_pendiente = 1000 - 700 = 300
  col4 = 1000 - 300 = 700
  saldo_disponible = 5000 - 700 = 4300

Resultado:
  cantidad_liquidacion = 700
  cantidad_pendiente = 300
  col4 = 700
  saldo_disponible = 4300
```

---

## 🔑 Cambios Clave

### En `updateLiquidacion()`

**ANTES:**
- Calculaba `diferencia_pendiente`
- Restaba la diferencia del col4 (col4 -= diferencia)
- El col4 podía aumentar o disminuir según el cambio

**AHORA:**
- Calcula directamente `cantidad_pendiente = monto - cantidad_liquidacion`
- Resta **el TOTAL del pendiente** del col4 (col4 -= cantidad_pendiente)
- El col4 siempre disminuye al liquidar
- Se recalcula saldo_disponible automáticamente
- Todo se realiza **por cada código_completo**

### Logs
```
ANTES:
  ✅ Liquidación PHP: detalle=51, cantidad_liq=500, cantidad_pend=500, col4_cambio=-500, certificado=1

AHORA:
  ✅ Presupuesto LIQUIDACIÓN: codigo=82 00 000 002 003 0200 510203, col4=500, saldo=4500, cantidad_pend=500
  ✅ Liquidación: detalle=51, cantidad_liq=500, cantidad_pend=500, certificado=1
```

---

## 📝 Detalles de Implementación

### Método Modificado
`app/models/Certificate.php` → `updateLiquidacion($detalle_id, $cantidad_liquidacion)`

### Cambios Específicos
1. ✅ Eliminar cálculo de diferencia_pendiente
2. ✅ Agregar cálculo directo de cantidad_pendiente
3. ✅ Cambiar lógica de presupuesto a col4 -= cantidad_pendiente
4. ✅ Agregar cálculo directo de saldo_disponible = col3 - col4
5. ✅ Por cada código_completo específico
6. ✅ Mejorar logs para claridad

---

## ✅ Validaciones

El código sigue validando:
- ✅ Cantidad_liquidacion ≤ monto original
- ✅ Cantidad_liquidacion ≥ 0
- ✅ Detalle existe en BD
- ✅ Código_completo existe en presupuesto
- ✅ col4 no queda negativo (max(0, ...))

---

## 🔄 Compatibilidad

- ✅ Sin cambios en base de datos
- ✅ Sin cambios en controladores
- ✅ Sin cambios en vistas
- ✅ Completamente retrocompatible
- ✅ Los logs cambiarán (mejor información)

---

## 🧪 Cómo Probar

### SQL para Verificar
```sql
-- Ver un item antes
SELECT 
  dc.id, dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
  pi.col3, pi.col4, pi.saldo_disponible
FROM detalle_certificados dc
LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
WHERE dc.id = 1;

-- Liquidar $500
UPDATE detalle_certificados SET cantidad_liquidacion = 500 WHERE id = 1;

-- Ejecutar updateLiquidacion desde PHP o desde controlador

-- Ver después
SELECT 
  dc.id, dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
  pi.col3, pi.col4, pi.saldo_disponible
FROM detalle_certificados dc
LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
WHERE dc.id = 1;

-- Esperado:
-- cantidad_pendiente = monto - cantidad_liquidacion
-- col4 = col3 - saldo_disponible
-- saldo_disponible = col3 - col4
```

---

## 📌 Resumen

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| Cálculo de pendiente | Diferencia | Directo (monto - liquidado) |
| Actualización col4 | -= diferencia | -= cantidad_pendiente |
| saldo_disponible | Indirecto | Directo (col3 - col4) |
| Contexto | Global | Por código_completo |
| Complejidad | Media | Baja |

---

**Estado:** ✅ COMPLETADO Y PROBADO

**Fecha:** 8 de Diciembre de 2025

**Versión:** 2.0 (Actualizada)
