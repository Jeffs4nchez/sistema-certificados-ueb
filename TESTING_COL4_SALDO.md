# 🧪 Guía de Testing: col4 y saldo_disponible

## Requisitos
- Sistema de certificados activo
- Base de datos con tabla `presupuesto_items` con columnas: `col3`, `col4`, `saldo_disponible`, `codigo_completo`
- Base de datos con tabla `detalle_certificados` con columnas: `monto`, `cantidad_liquidacion`, `cantidad_pendiente`, `codigo_completo`

## Escenarios de Test

### ✅ Test 1: Agregar un Item a un Certificado

**Precondición:**
```sql
SELECT col3, col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo = '82 00 000 002 003 0200 510203';
-- Resultado esperado: col4 = 0, saldo_disponible = col3
```

**Acción:**
1. Ir a Crear/Editar Certificado
2. Agregar un item con:
   - Código: `82 00 000 002 003 0200 510203`
   - Monto: `1000`

**Verificación:**
```sql
SELECT col3, col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo = '82 00 000 002 003 0200 510203';
-- Resultado esperado: 
--   col4 = 1000 (anterior + monto)
--   saldo_disponible = col3 - 1000 (disminuyó)
```

**Logs esperados en error_log:**
```
✅ Presupuesto AGREGAR: codigo=82 00 000 002 003 0200 510203, col4=1000, saldo=4000
```

---

### ✅ Test 2: Editar Monto (Aumentar)

**Precondición:**
```sql
-- Item existente en detalle_certificados con:
-- monto = 1000, cantidad_liquidacion = 0, cantidad_pendiente = 1000
-- col4 = 1000, saldo_disponible = 4000
```

**Acción:**
1. Editar el item
2. Cambiar monto de 1000 a 1500

**Verificación:**
```sql
-- Verifica en detalle_certificados:
SELECT monto, cantidad_pendiente 
FROM detalle_certificados 
WHERE id = <item_id>;
-- Resultado esperado: monto = 1500, cantidad_pendiente = 1500

-- Verifica en presupuesto_items:
SELECT col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo = '82 00 000 002 003 0200 510203';
-- Resultado esperado: 
--   col4 = 1500 (1000 + 500 diferencia)
--   saldo_disponible = 3500 (col3 - 1500)
```

**Logs esperados:**
```
✅ Presupuesto AGREGAR: codigo=82 00 000 002 003 0200 510203, col4=1500, saldo=3500
```

---

### ✅ Test 3: Editar Monto (Disminuir)

**Precondición:**
```sql
-- Item con monto = 1500, col4 = 1500, saldo_disponible = 3500
```

**Acción:**
1. Editar el item
2. Cambiar monto de 1500 a 1000

**Verificación:**
```sql
SELECT col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo = '82 00 000 002 003 0200 510203';
-- Resultado esperado: 
--   col4 = 1000 (1500 - 500 diferencia)
--   saldo_disponible = 4000 (col3 - 1000)
```

**Logs esperados:**
```
✅ Presupuesto ELIMINAR: codigo=82 00 000 002 003 0200 510203, col4=1000, saldo=4000
```

---

### ✅ Test 4: Liquidar Parcialmente un Item

**Precondición:**
```sql
-- Item con:
-- monto = 1000
-- cantidad_liquidacion = 0
-- cantidad_pendiente = 1000
-- col4 = 1000, saldo_disponible = 4000
```

**Acción:**
1. Ir a Liquidación del certificado
2. Liquidar $500 del item

**Verificación:**
```sql
-- Detalle certificados:
SELECT cantidad_liquidacion, cantidad_pendiente 
FROM detalle_certificados 
WHERE id = <item_id>;
-- Resultado esperado: cantidad_liquidacion = 500, cantidad_pendiente = 500

-- Presupuesto:
SELECT col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo = '82 00 000 002 003 0200 510203';
-- Resultado esperado: 
--   col4 = 500 (1000 - 500, porque cantidad_pendiente disminuyó)
--   saldo_disponible = 4500 (col3 - 500)
```

**Logs esperados:**
```
✅ Liquidación PHP: detalle=<id>, cantidad_liq=500, cantidad_pend=500, col4_cambio=-500, certificado=<cert_id>
✅ Presupuesto AGREGAR: codigo=82 00 000 002 003 0200 510203, col4=500, saldo=4500
```

---

### ✅ Test 5: Liquidar Completamente un Item

**Precondición:**
```sql
-- Item con:
-- monto = 1000
-- cantidad_liquidacion = 500
-- cantidad_pendiente = 500
-- col4 = 500, saldo_disponible = 4500
```

**Acción:**
1. Ir a Liquidación
2. Liquidar $500 más (total $1000)

**Verificación:**
```sql
-- Detalle:
SELECT cantidad_liquidacion, cantidad_pendiente 
FROM detalle_certificados 
WHERE id = <item_id>;
-- Resultado esperado: cantidad_liquidacion = 1000, cantidad_pendiente = 0

-- Presupuesto:
SELECT col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo = '82 00 000 002 003 0200 510203';
-- Resultado esperado: 
--   col4 = 0 (500 - 500)
--   saldo_disponible = 5000 (col3 - 0, recuperado)
```

---

### ✅ Test 6: Eliminar un Item

**Precondición:**
```sql
-- Item existente con:
-- monto = 1000
-- col4 = 1000, saldo_disponible = 4000
```

**Acción:**
1. Eliminar el item del certificado

**Verificación:**
```sql
-- El item ya no existe
SELECT COUNT(*) FROM detalle_certificados 
WHERE id = <item_id>;
-- Resultado esperado: 0

-- Presupuesto:
SELECT col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo = '82 00 000 002 003 0200 510203';
-- Resultado esperado: 
--   col4 = 0 (1000 - 1000)
--   saldo_disponible = 5000 (col3 - 0)
```

**Logs esperados:**
```
✅ Presupuesto ELIMINAR: codigo=82 00 000 002 003 0200 510203, col4=0, saldo=5000
```

---

### ✅ Test 7: Eliminar Certificado Completo

**Precondición:**
```sql
-- Certificado con múltiples items
-- col4 = 5000, saldo_disponible = 0
```

**Acción:**
1. Eliminar el certificado completo

**Verificación:**
```sql
-- El certificado ya no existe
SELECT COUNT(*) FROM certificados 
WHERE id = <cert_id>;
-- Resultado esperado: 0

-- Los items ya no existen
SELECT COUNT(*) FROM detalle_certificados 
WHERE certificado_id = <cert_id>;
-- Resultado esperado: 0

-- Presupuesto se recuperó
SELECT col4, saldo_disponible 
FROM presupuesto_items 
WHERE codigo_completo IN (
    -- Códigos de los items eliminados
);
-- Resultado esperado: col4 = 0, saldo_disponible = col3 (para cada código)
```

---

## Consultas SQL Útiles para Testing

### Ver estado actual de un código presupuestario:
```sql
SELECT 
    codigo_completo,
    col3 as "Disponible Inicial",
    col4 as "Certificado",
    saldo_disponible as "Disponible Ahora",
    col3 - col4 as "Calculado (col3-col4)"
FROM presupuesto_items 
WHERE codigo_completo LIKE '82 00%'
ORDER BY codigo_completo;
```

### Ver detalles de certificados y su relación con presupuesto:
```sql
SELECT 
    dc.id,
    dc.codigo_completo,
    dc.monto,
    dc.cantidad_liquidacion,
    dc.cantidad_pendiente,
    pi.col4,
    pi.saldo_disponible
FROM detalle_certificados dc
LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
WHERE dc.certificado_id = <cert_id>
ORDER BY dc.id;
```

### Verificar integridad de datos:
```sql
-- La suma de cantidad_pendiente debe ser igual a col4
SELECT 
    pi.codigo_completo,
    SUM(dc.cantidad_pendiente) as suma_pendientes,
    pi.col4,
    SUM(dc.cantidad_pendiente) = pi.col4 as "¿Consistente?"
FROM presupuesto_items pi
LEFT JOIN detalle_certificados dc ON pi.codigo_completo = dc.codigo_completo
GROUP BY pi.codigo_completo, pi.col4
HAVING SUM(dc.cantidad_pendiente) != pi.col4 OR pi.col4 IS NULL
ORDER BY pi.codigo_completo;
```

---

## Troubleshooting

### ❌ col4 no se actualiza
1. Verificar que el `codigo_completo` sea correcto
2. Verificar que el `codigo_completo` exista en `presupuesto_items`
3. Revisar logs en `error_log` para ver mensajes de error
4. Verificar que `updatePresupuestoAddCertificado()` se está llamando

### ❌ saldo_disponible no se recalcula
1. Verificar que `col3` tenga un valor válido en `presupuesto_items`
2. La fórmula debe ser: `saldo = col3 - col4`
3. Verificar logs para ver si la actualización se ejecutó

### ❌ Valores negativos en col4
1. El código incluye `max(0, ...)` para evitar esto
2. Si ves valores negativos, hay un error de lógica
3. Revisar los logs para ver qué diferencia se está restando

---

## Notas

- Los logs se escriben en el archivo de `error_log` del servidor PHP
- Ajusta las rutas según tu instalación (XAMPP, Docker, etc.)
- Usa `tail -f php_error.log` para ver los logs en tiempo real
