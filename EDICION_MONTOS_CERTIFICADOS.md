# Edición de Montos en Certificados - Implementación Completa

## 📋 Resumen de Cambios

Se implementó la funcionalidad para **editar montos de items directamente desde el modal de edición** de certificados con los siguientes alcances:

### ✅ Características Implementadas

1. **Modal de Edición Mejorado**
   - Los montos de items ahora son **editables directamente** en el modal
   - Cada item tiene un campo input donde se puede cambiar el monto
   - El total se recalcula automáticamente en tiempo real

2. **Edición por Item**
   - Si un certificado tiene **múltiples items**, cada uno es editable independientemente
   - El monto se puede cambiar sin afectar a otros items

3. **Actualización Automática de Cálculos**
   - **Presupuesto**: Se actualiza col4 (monto utilizado) y saldo_disponible
   - **Certificado**: Se recalcula el monto_total automáticamente
   - **Liquidaciones**: Si hay liquidaciones anteriores, se mantienen y se recalcula cantidad_pendiente

4. **Integridad de Datos**
   - Las liquidaciones existentes se **mantienen intactas**
   - Los cálculos de cantidad_pendiente se actualizan según: `cantidad_pendiente = nuevo_monto - liquidacion_existente`

---

## 🔧 Archivos Modificados

### 1. [app/views/certificate/list.php](app/views/certificate/list.php)

**Cambios:**
- Modificada función `loadEditModalItems()` para mostrar montos editables
- Actualizada función `updateEditTotal()` para recalcular en tiempo real
- Mejorada función `saveEditCertificate()` para enviar los montos editados

**Detalles:**
```javascript
// Los inputs de monto ahora son editables
<input type="number" 
       class="form-control form-control-sm edit-monto-input" 
       value="${item.monto.toFixed(2)}"
       data-item-index="${index}"
       data-original-monto="${item.monto.toFixed(2)}"
       step="0.01" 
       min="0"
       onchange="updateEditTotal()">
```

---

### 2. [app/models/Certificate.php](app/models/Certificate.php)

**Nuevo Método: `updateItemMonto($item_id, $monto_nuevo, $certificado_id, $year)`**

Realiza las siguientes operaciones en cascada:
- ✅ Actualiza el monto en `detalle_certificados`
- ✅ Recalcula `cantidad_pendiente` si hay liquidaciones
- ✅ Actualiza `col4` y `saldo_disponible` en `presupuesto_items`
- ✅ Recalcula `monto_total` del certificado maestro
- ✅ Actualiza `total_pendiente` del certificado

**Ejemplo de uso:**
```php
$resultado = $certificateModel->updateItemMonto($item_id, 5000.00, $certificado_id, 2025);
if ($resultado['success']) {
    echo "Monto actualizado: " . $resultado['total_certificado'];
}
```

---

### 3. [app/controllers/CertificateController.php](app/controllers/CertificateController.php)

**Modificado Método: `updateAction($id)`**

Cambios:
- Ahora procesa la actualización de montos si existen en `items_editados`
- Itera sobre cada item editado y llama a `updateItemMonto()`
- Maneja errores individuales pero continúa con otros items
- Retorna resumen de operación

**Flujo:**
```
1. Valida permisos (solo admin)
2. Actualiza datos maestros del certificado
3. Si hay items_editados en POST:
   - Para cada item editado:
     - Llama a updateItemMonto()
     - Recalcula presupuesto, liquidaciones, totales
4. Retorna éxito o errores parciales
```

---

## 📊 Ejemplo de Uso

### Escenario: Editar monto de un item con liquidaciones previas

**Inicial:**
- Item 1: Monto = $1,000
- Liquidación existente: $300
- Cantidad pendiente: $700

**Usuario edita:**
- Nuevo monto: $800

**Sistema actualiza automáticamente:**
- ✅ Item monto: $800
- ✅ Liquidación: Se mantiene en $300
- ✅ Cantidad pendiente: $500 (800 - 300)
- ✅ Presupuesto: col4 se ajusta por la diferencia (-$200)
- ✅ Certificado total: Se recalcula con todos los items

---

## 🔄 Flujo de Datos

```
┌─────────────────────────────────────────────┐
│ Usuario edita monto en modal y hace click   │
│ en "Guardar Cambios"                        │
└────────────┬────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────┐
│ saveEditCertificate()                       │
│ - Recopila montos editados                  │
│ - Valida que sean positivos                 │
│ - Envía POST a certificate-update           │
└────────────┬────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────┐
│ CertificateController::updateAction()       │
│ - Valida permisos                           │
│ - Actualiza datos maestros                  │
│ - Procesa items_editados                    │
└────────────┬────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────┐
│ Certificate::updateItemMonto() (por c/item) │
│ - Obtiene monto anterior                    │
│ - Calcula diferencia                        │
│ - Actualiza detalle_certificados            │
│ - Recalcula cantidad_pendiente              │
│ - Actualiza presupuesto_items               │
│ - Actualiza certificado maestro             │
└────────────┬────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────┐
│ JSON Response: success = true               │
│ Recarga la página                           │
└─────────────────────────────────────────────┘
```

---

## 🧪 Pruebas Recomendadas

1. **Prueba Básica: Cambiar monto sin liquidaciones**
   - Abrir certificado con 1 item
   - Editar monto
   - Verificar que se actualice el total
   - Guardar y verificar en BD

2. **Prueba Avanzada: Múltiples items con liquidaciones**
   - Certificado con 3 items
   - Cada item con liquidaciones diferentes
   - Editar 2 items
   - Verificar que liquidaciones se mantengan
   - Verificar que cantidad_pendiente se recalcule

3. **Prueba de Presupuesto**
   - Editar item que aumenta monto
   - Verificar que col4 aumente en presupuesto_items
   - Verificar que saldo_disponible disminuya

4. **Prueba de Errores**
   - Intentar guardar monto negativo
   - Intentar guardar sin cambios
   - Verificar validaciones en modal

---

## 📝 Notas Técnicas

### Validaciones
- ✅ Solo administradores pueden editar
- ✅ Montos deben ser ≥ 0
- ✅ Se calcula diferencia para presupuesto

### Cálculos en Cascada
```sql
-- Actualización de item
UPDATE detalle_certificados 
SET monto = ?, 
    cantidad_pendiente = monto_nuevo - cantidad_liquidacion_existente
WHERE id = ?

-- Actualización de presupuesto
UPDATE presupuesto_items 
SET col4 = col4 + diferencia_monto,
    saldo_disponible = col3 - col4_nuevo
WHERE codigo_completo = ? AND year = ?

-- Actualización de certificado
UPDATE certificados 
SET monto_total = SUM(montos_items),
    total_pendiente = SUM(cantidades_pendientes)
WHERE id = ?
```

### Manejo de Transacciones
- Cada operación en updateItemMonto() es independiente
- Los errores se capturan sin afectar otros items
- Se retorna resumen de lo que se logró actualizar

---

## 🐛 Debugging

Los logs se registran en el error log del servidor con el prefijo `=== UPDATE ITEM MONTO ===`

```php
error_log("Item ID: $item_id, Monto Nuevo: $monto_nuevo");
error_log("Monto anterior: $monto_anterior");
error_log("✓ Presupuesto actualizado: codigo=$codigo_completo");
```

---

## ✨ Ventajas del Diseño

1. **Edición Rápida**: No requiere crear nuevo certificado
2. **Integridad**: Mantiene liquidaciones existentes
3. **Flexibilidad**: Editable por item individualmente
4. **Trazabilidad**: Registra cambios en logs
5. **Robustez**: Maneja errores sin perder datos

