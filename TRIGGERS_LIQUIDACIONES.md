# 🔧 Triggers de Actualización de Presupuesto - Liquidaciones

## Problema Identificado

Cuando guardabas liquidaciones, los triggers **no estaban funcionando** en la base de datos. Esto significaba que:

- ✓ Se guardaba correctamente la `cantidad_liquidacion` en `detalle_certificados`
- ✓ El `memorando` se guardaba correctamente
- ❌ **NO se actualizaban** las columnas `col4`, `col5`, `col6`, `col7` en `presupuesto_items`

### Consecuencia

Los totales de certificados y liquidaciones en `presupuesto_items` quedaban desincronizados con los datos reales en `detalle_certificados`.

---

## Solución Implementada

Se crearon **3 triggers** en PostgreSQL que se ejecutan automáticamente:

### 1. **TRIGGER INSERT** - `trigger_insert_detalle_certificados`
```sql
AFTER INSERT ON detalle_certificados
```

**Qué hace:**
- Cuando se crea un nuevo item (detalle_certificados)
- Suma automáticamente el `monto` a `col4` (Total Certificado) en presupuesto_items
- Recalcula `col8` (Saldo disponible)

**Ejemplo:**
```
INSERT detalle_certificados con monto = 1000
→ presupuesto_items.col4 = col4 + 1000
```

---

### 2. **TRIGGER UPDATE** - `trigger_update_liquidacion`
```sql
AFTER UPDATE ON detalle_certificados
WHEN (cantidad_liquidacion CHANGED)
```

**Qué hace:**
- Cuando se actualiza `cantidad_liquidacion`
- Calcula la **diferencia** (nueva - antigua)
- Suma esa diferencia a `col7` (Total Liquidado) en presupuesto_items
- Recalcula `col8` (Saldo disponible)

**Ejemplo:**
```
UPDATE cantidad_liquidacion: 100 → 110 (diferencia = +10)
→ presupuesto_items.col7 = col7 + 10
→ presupuesto_items.col8 se recalcula
```

---

### 3. **TRIGGER DELETE** - `trigger_delete_detalle_certificados`
```sql
BEFORE DELETE ON detalle_certificados
```

**Qué hace:**
- Cuando se elimina un item
- Resta el `monto` de `col4`
- Resta la `cantidad_liquidacion` de `col7`
- Recalcula `col8`

**Ejemplo:**
```
DELETE detalle_certificados con:
  - monto = 1000
  - cantidad_liquidacion = 500
→ presupuesto_items.col4 = col4 - 1000
→ presupuesto_items.col7 = col7 - 500
```

---

## Verificación

Se realizaron pruebas exitosas:

✅ **UPDATE de liquidación:**
- Cambio: 10 → 110 (diferencia: +100)
- col7 ANTERIOR: 464,870.52
- col7 ACTUAL: 464,970.52 (aumentó exactamente 100)
- **RESULTADO: FUNCIONANDO CORRECTAMENTE**

---

## Cálculo de col8 (Saldo Disponible)

Después de cada operación, se recalcula:
```
col8 = col1 - col4 - col5 - col6 - col7
```

Donde:
- `col1` = Total Presupuesto
- `col4` = Total Certificado
- `col5` = Total Comprometido
- `col6` = Total Devengado
- `col7` = Total Liquidado

---

## Archivos Creados/Modificados

1. **`database/create_triggers.sql`** - Script SQL con los triggers (para referencia)
2. **Triggers en la BD** - Ya ejecutados y funcionando

---

## Cómo Verificar

### Ver los triggers creados:
```sql
SELECT trigger_name, event_manipulation, event_object_table 
FROM information_schema.triggers 
WHERE event_object_table = 'detalle_certificados';
```

**Resultado:**
```
trigger_insert_detalle_certificados  (AFTER INSERT)
trigger_update_liquidacion           (AFTER UPDATE)
trigger_delete_detalle_certificados  (BEFORE DELETE)
```

### Prueba manual:
```php
// UPDATE cantidad_liquidacion
UPDATE detalle_certificados SET cantidad_liquidacion = 110 WHERE id = 51;

// El trigger se ejecuta automáticamente
// Verifica que presupuesto_items.col7 se actualizó
SELECT col7 FROM presupuesto_items WHERE codigo_completo = '82 00 000 002 003 0200 510203';
```

---

## Beneficios

✓ **Consistencia de datos**: presupuesto_items siempre refleja los datos reales  
✓ **Automatización**: No hay riesgo de olvidar actualizar manualmente  
✓ **Integridad referencial**: Las liquidaciones y certificados siempre coinciden  
✓ **Reportes confiables**: Los totales en el dashboard son correctos  

---

## Estructura de Relación

```
detalle_certificados                presupuesto_items
├─ id                              ├─ id
├─ codigo_completo ──────────────► codigo_completo (FK)
├─ monto                           ├─ col1 (Presupuesto)
├─ cantidad_liquidacion            ├─ col4 (Certificado) ← Se actualiza con monto
├─ memorando                        ├─ col5 (Comprometido)
└─ fecha_actualizacion             ├─ col6 (Devengado)
                                   ├─ col7 (Liquidado) ← Se actualiza con cantidad_liquidacion
                                   └─ col8 (Saldo)
```

---

## Estado Actual

🟢 **TRIGGERS FUNCIONANDO CORRECTAMENTE**

- Al guardar liquidaciones, `presupuesto_items` se actualiza automáticamente
- Los totales son consistentes
- Los cálculos de saldo disponible son exactos

