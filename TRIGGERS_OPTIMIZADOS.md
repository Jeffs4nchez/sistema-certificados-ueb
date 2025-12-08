# 🎯 TRIGGERS OPTIMIZADOS - SISTEMA DE CERTIFICADOS Y PRESUPUESTO

## ✅ Estado: ACTIVOS

**Fecha:** 7 de Diciembre 2025  
**BD:** PostgreSQL - certificados_sistema  
**Total Triggers:** 6 activos

---

## 📊 FLUJO DE SINCRONIZACIÓN

```
┌─────────────────────────────────────────────────────────────────┐
│ USUARIO CREA UN CERTIFICADO CON ITEMS                           │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                    INSERT en detalle_certificados
                    (monto, codigo_completo)
                              ↓
        ┌─────────────────────────────────────────────┐
        │ TRIGGER 1: cantidad_pendiente (BEFORE)      │
        │ Calcula: cantidad_pendiente =               │
        │          monto - cantidad_liquidacion       │
        └─────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────┐
        │ TRIGGER 2: insert_col4 (AFTER)              │
        │ Busca presupuesto_items por codigo_completo │
        │ Suma: col4 = col4 + monto                   │
        └─────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────────────────────────────┐
        │ TRIGGER 5: recalcula_saldo (BEFORE UPDATE)  │
        │ Recalcula: saldo_disponible = col3 - col4   │
        └─────────────────────────────────────────────┘
                              ↓
                     ✅ PRESUPUESTO ACTUALIZADO
```

---

## 🔧 DESCRIPCIÓN DE TRIGGERS

### TABLA: detalle_certificados (5 triggers)

#### 1️⃣ **trigger_detalle_cantidad_pendiente** 
- **Tipo:** BEFORE INSERT / BEFORE UPDATE
- **Función:** `fn_trigger_detalle_cantidad_pendiente()`
- **Acción:**
  ```sql
  cantidad_pendiente := monto - cantidad_liquidacion
  ```
- **Propósito:** Mantener actualizado el campo `cantidad_pendiente` cada vez que se crea o modifica un item

#### 2️⃣ **trigger_detalle_insert_col4**
- **Tipo:** AFTER INSERT
- **Función:** `fn_trigger_detalle_insert_col4()`
- **Acción:**
  1. Busca `presupuesto_items` por `codigo_completo`
  2. Suma el monto a `col4`: `col4 = col4 + monto`
  3. Actualiza timestamp
- **Propósito:** Sincronizar presupuesto cuando se crea un nuevo item

#### 3️⃣ **trigger_detalle_update_col4**
- **Tipo:** AFTER UPDATE
- **Función:** `fn_trigger_detalle_update_col4()`
- **Acción:**
  1. Si el `monto` cambió: calcula diferencia
  2. Suma/resta la diferencia en `presupuesto_items.col4`
  3. Actualiza timestamp
- **Propósito:** Mantener sincronizado col4 cuando se modifica el monto del item

#### 4️⃣ **trigger_detalle_delete_col4**
- **Tipo:** AFTER DELETE
- **Función:** `fn_trigger_detalle_delete_col4()`
- **Acción:**
  1. Busca `presupuesto_items` por `codigo_completo`
  2. Resta el monto de `col4`: `col4 = col4 - monto`
  3. Actualiza timestamp
- **Propósito:** Devolver el monto al presupuesto cuando se elimina un item

### TABLA: presupuesto_items (1 trigger)

#### 5️⃣ **trigger_col4_recalcula_saldo**
- **Tipo:** BEFORE UPDATE (cuando col3 o col4 cambian)
- **Función:** `fn_trigger_col4_recalcula_saldo()`
- **Acción:**
  ```sql
  saldo_disponible := col3 - col4
  ```
- **Propósito:** Garantizar que siempre `saldo_disponible = col3 - col4`

---

## 📋 CASOS DE USO

### ✅ Caso 1: Crear un Certificado con 3 Items

```
INSERT INTO detalle_certificados VALUES
(1, "1.2.3.4.5", 1000);  -- Código completo, monto 1000

⚡ AUTOMÁTICAMENTE:
  1. cantidad_pendiente = 1000 - 0 = 1000
  2. presupuesto_items[1.2.3.4.5].col4 += 1000
  3. presupuesto_items[1.2.3.4.5].saldo_disponible = col3 - col4
```

### ✅ Caso 2: Liquidar un Item (cambiar cantidad_liquidacion)

```
UPDATE detalle_certificados 
SET cantidad_liquidacion = 500 
WHERE id = 1;

⚡ AUTOMÁTICAMENTE:
  1. cantidad_pendiente = 1000 - 500 = 500
  2. col4 NO CAMBIA (sigue siendo el monto original)
  3. saldo_disponible se mantiene igual
```

### ✅ Caso 3: Modificar el Monto de un Item

```
UPDATE detalle_certificados 
SET monto = 1500 
WHERE id = 1;

⚡ AUTOMÁTICAMENTE:
  1. cantidad_pendiente = 1500 - cantidad_liquidacion
  2. col4 += (1500 - 1000) = +500
  3. saldo_disponible = col3 - nuevo_col4
```

### ✅ Caso 4: Eliminar un Item

```
DELETE FROM detalle_certificados WHERE id = 1;

⚡ AUTOMÁTICAMENTE:
  1. col4 -= 1500 (resta el monto)
  2. saldo_disponible = col3 - nuevo_col4
```

---

## 🔒 INTEGRIDAD DE DATOS

✅ **Garantías:**

- Los montos certificados SIEMPRE se sincronizán con presupuesto_items.col4
- El saldo disponible SIEMPRE = col3 - col4
- Los cambios en liquidación NO afectan a col4 (solo a cantidad_pendiente)
- Operaciones atómicas = sin inconsistencias

✅ **Ventajas para Finanzas:**

- Imposible que los presupuestos se desincronicen
- Datos consistentes incluso si hay error en la aplicación
- Auditoría automática del timestamp en cada cambio
- Protegido contra actualizaciones directas en BD

---

## 📝 INSTALACIÓN

```bash
php aplicar_triggers_v2.php
```

**Output esperado:**
```
✅ Eliminando triggers antiguos...
✅ Creando funciones de triggers...
✅ Creando triggers...
✅ Total de triggers activos: 6
✅ Triggers optimizados aplicados correctamente!
```

---

## 🧪 VERIFICACIÓN

```sql
SELECT trigger_name, event_object_table, event_manipulation, action_timing
FROM information_schema.triggers
WHERE trigger_schema = 'public'
ORDER BY event_object_table, trigger_name;
```

**Resultado esperado:**
```
├─ detalle_certificados
│  ├─ trigger_detalle_cantidad_pendiente (BEFORE INSERT)
│  ├─ trigger_detalle_cantidad_pendiente (BEFORE UPDATE)
│  ├─ trigger_detalle_insert_col4 (AFTER INSERT)
│  ├─ trigger_detalle_update_col4 (AFTER UPDATE)
│  └─ trigger_detalle_delete_col4 (AFTER DELETE)
│
└─ presupuesto_items
   └─ trigger_col4_recalcula_saldo (BEFORE UPDATE)
```

---

## 🚀 PRÓXIMOS PASOS (RECOMENDADO)

1. **Documentar en models/controllers** que estos triggers existen
2. **Agregar validaciones en PHP** antes de INSERT/UPDATE
3. **Crear logs de auditoría** para rastrear cambios
4. **Hacer test** de casos de uso críticos

---

**Creado por:** Sistema Automático  
**Última actualización:** 7 de Diciembre 2025
