# ✅ ARREGLO FINAL: cantidad_pendiente y totales del certificado

## 🔍 Problema Encontrado

Tu registro mostraba:
```
ID 240: monto=1000, cantidad_liquidacion=900, cantidad_pendiente=1000 ❌
```

**La fórmula correcta es:**
$$cantidad\_pendiente = monto - cantidad\_liquidacion = 1000 - 900 = 100$$

Pero tenías `cantidad_pendiente = 1000`, lo que significa que **NO se estaba restando la liquidación**.

---

## ✅ Soluciones Implementadas

### 1. **Arreglados 2 items con datos incorrectos**

```sql
ID 240: 1000 - 900 = 100  ✅ (era 1000)
ID 241: 500 - 400 = 100   ✅ (era 500)
```

Script: `corregir_cantidad_pendiente.php`

---

### 2. **Mejorado `Certificate.php::createDetail()`**

```php
// ANTES: Insertaba siempre cantidad_liquidacion = 0
$stmt->execute([
    ...,
    $monto,  // cantidad_pendiente
    0,       // cantidad_liquidacion (SIEMPRE 0)
]);

// DESPUÉS: Permite liquidación inicial y calcula cantidad_pendiente
$cantidad_liquidacion = (float)($data['cantidad_liquidacion'] ?? 0);
$cantidad_pendiente = $monto - $cantidad_liquidacion;  // FÓRMULA CORRECTA

$stmt->execute([
    ...,
    $cantidad_liquidacion,
    $cantidad_pendiente,
]);
```

---

### 3. **Creados Triggers para actualizar totales automáticamente**

Cuando se **INSERT, UPDATE o DELETE** un item en `detalle_certificados`, ahora se recalculan automáticamente:

```sql
certificados.total_liquidado = SUM(cantidad_liquidacion)
certificados.total_pendiente = SUM(cantidad_pendiente)
```

Triggers creados:
- `trg_update_cert_totales_insert` - AFTER INSERT
- `trg_update_cert_totales_update` - AFTER UPDATE  
- `trg_update_cert_totales_delete` - BEFORE DELETE

Script: `create_totales_triggers.php`

---

## ✅ Verificación

### Test de createDetail()

```
Item 1 (SIN liquidación):
  monto: 1,500.00
  cantidad_liquidacion: 0.00
  cantidad_pendiente: 1,500.00 ✅

Item 2 (CON liquidación inicial $800):
  monto: 2,000.00
  cantidad_liquidacion: 800.00
  cantidad_pendiente: 1,200.00 ✅

Certificado totales:
  total_liquidado: 800.00 ✅
  total_pendiente: 2,700.00 ✅
```

Script: `test_createDetail_fix.php`

---

## 📋 Resumen de Cambios

| Componente | Antes | Después |
|-----------|-------|---------|
| **createDetail()** | cantidad_pendiente siempre = monto | cantidad_pendiente = monto - liquidacion |
| **Totales certificado** | No se actualizaban | Se actualizan automáticamente con triggers |
| **Items incorrectos** | 2 items con datos mal | Todos corregidos |
| **Validación** | Sin fórmula | Siempre: cantidad_pendiente = monto - cantidad_liquidacion |

---

## 🚀 Comportamiento Actual

### Al crear un nuevo item:
```
cantidad_liquidacion = 0 (por defecto)
cantidad_pendiente = monto - 0 = monto ✅
certificados.total_liquidado se recalcula ✅
certificados.total_pendiente se recalcula ✅
```

### Al liquidar un item:
```
updateLiquidacion(item_id, 500)
→ cantidad_liquidacion = 500
→ cantidad_pendiente = monto - 500
→ certificados.total_liquidado se recalcula ✅
→ certificados.total_pendiente se recalcula ✅
```

### Al eliminar un item:
```
DELETE item
→ certificados.total_liquidado se recalcula ✅
→ certificados.total_pendiente se recalcula ✅
```

---

## 📝 Scripts Disponibles

1. **`corregir_cantidad_pendiente.php`** - Corrige items existentes
2. **`create_totales_triggers.php`** - Crea triggers para totales
3. **`test_createDetail_fix.php`** - Verifica que todo funciona

Ejecuta en orden:
```bash
php corregir_cantidad_pendiente.php      # Arreglar datos existentes
php create_totales_triggers.php          # Crear triggers
php test_createDetail_fix.php            # Verificar todo
```

✅ **PROBLEMA RESUELTO**
