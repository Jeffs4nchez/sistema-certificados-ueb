# 🔧 FIX: Monto Pendiente no se Actualiza en Modal de Liquidación

## 🐛 Problema Identificado

Al crear una liquidación en el modal, el campo **"Monto Pendiente"** en la lista de certificados **NO se actualizaba correctamente**. 

### Síntomas:
- Se guardaba la liquidación correctamente ✓
- Pero el `total_pendiente` en la tabla de certificados mostraba un valor incorrecto ✗
- Ejemplo: Monto Total = $1.800,00 | Liquidado = $1.500,00 | Pendiente = $1.800,00 (INCORRECTO)
- Debería ser: Pendiente = $1.800,00 - $1.500,00 = **$300,00**

---

## 🔍 Causa Raíz

El problema estaba en que después de actualizar la `cantidad_liquidacion` en `detalle_certificados`, **NO se recalculaban** los campos `total_liquidado` y `total_pendiente` en la tabla `certificados`.

### Flujo Anterior (INCORRECTO):
```
1. Usuario abre modal de liquidación
2. Usuario ingresa cantidad liquidada
3. Se actualiza detalle_certificados.cantidad_liquidacion
4. Se recarga la página (location.reload())
5. Se muestran los valores de certificados.total_pendiente
   → PERO ese valor está DESACTUALIZADO
```

---

## ✅ Solución Implementada

Se agregó la recalculación automática del `total_liquidado` y `total_pendiente` en los siguientes métodos:

### 1. **CertificateController.php** - `saveLiquidacionesAction()`

```php
// Ahora actualiza los totales después de guardar cada liquidación
foreach ($certificadosActualizados as $certId) {
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = ?
        ), 0),
        total_pendiente = monto_total - COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = ?
        ), 0)
    WHERE id = ?
}
```

### 2. **APICertificateController.php** - `saveLiquidacionesAction()`

Se aplicó la misma lógica en el controlador API.

### 3. **Certificate.php** - `updateLiquidacion()`

Se corrigió para:
- Solo actualizar la `cantidad_liquidacion` (NO modificar el `monto`)
- Recalcular automáticamente `total_liquidado` y `total_pendiente` en `certificados`

---

## 📊 Flujo Corregido

```
1. Usuario abre modal de liquidación
2. Usuario ingresa cantidad liquidada
3. Se actualiza detalle_certificados.cantidad_liquidacion
4. ✓ SE RECALCULAN total_liquidado y total_pendiente en certificados
5. Se recarga la página (location.reload())
6. Se muestran los valores CORRECTOS de certificados.total_pendiente
```

---

## 🧪 Cómo Verificar que Funciona

### En la Base de Datos:
```sql
-- Ver certificado y sus liquidaciones
SELECT 
    c.id,
    c.numero_certificado,
    c.monto_total,
    c.total_liquidado,
    c.total_pendiente,
    (SELECT SUM(cantidad_liquidacion) FROM detalle_certificados WHERE certificado_id = c.id) as suma_liquidaciones
FROM certificados c
WHERE c.id = 111;
```

### En la Aplicación:
1. Abre la lista de certificados
2. Busca el certificado que editaste
3. Verifica que:
   - ✓ Liquidado = Suma de todas las cantidades liquidadas
   - ✓ Pendiente = Monto Total - Liquidado

---

## 📝 Cambios Realizados

| Archivo | Método | Cambio |
|---------|--------|--------|
| `CertificateController.php` | `saveLiquidacionesAction()` | Agregar recalculación de totales |
| `APICertificateController.php` | `saveLiquidacionesAction()` | Agregar recalculación de totales |
| `Certificate.php` | `updateLiquidacion()` | Agregar recalculación de totales |

---

## ✨ Validación

- ✅ Los totales se recalculan después de cada liquidación
- ✅ La página muestra el valor correcto sin necesidad de recargar manualmente
- ✅ Compatible con múltiples liquidaciones por certificado
- ✅ El `monto` original del item NO se modifica

---

## 📚 Referencia

- **Tabla**: `certificados`
- **Columnas actualizadas**: `total_liquidado`, `total_pendiente`
- **Condición**: Se actualiza el certificado cuyo ID se obtiene de cada detalle modificado
