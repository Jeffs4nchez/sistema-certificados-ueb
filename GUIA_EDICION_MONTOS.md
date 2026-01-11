# 🎯 RESUMEN EJECUTIVO - Edición de Montos en Certificados

## ¿Qué se implementó?

Ya puedes **editar los montos de los items** directamente desde el modal de edición del certificado. 

## ¿Cómo funciona?

1. **Abre un certificado** y haz clic en el botón ✏️ (Editar)
2. **En el modal**, verás una tabla con todos los items
3. **El último columna "Monto (Editable)"** ahora tiene campos que puedes cambiar
4. **Cambia los valores** que necesites
5. **Haz clic en "Guardar Cambios"**
6. El sistema actualiza automáticamente:
   - ✅ Los montos de los items
   - ✅ El total del certificado
   - ✅ El presupuesto (col4 y saldo disponible)
   - ✅ Las cantidades pendientes (si hay liquidaciones)

## Casos Cubiertos

### 1️⃣ Certificado sin liquidaciones
- Editas el monto de un item
- Se actualiza el monto y el total
- Listo ✓

### 2️⃣ Certificado con liquidaciones previas
- Editas el monto de un item que ya tiene liquidaciones
- Las liquidaciones **se mantienen**
- Se recalcula automáticamente: `cantidad_pendiente = nuevo_monto - liquidacion_existente`
- Ejemplo: Item con monto $1000 y liquidación $300
  - Cambias a $800
  - La liquidación sigue siendo $300
  - Cantidad pendiente ahora es $500 (800 - 300)

### 3️⃣ Certificado con múltiples items
- Cada item es editable independientemente
- El total se recalcula en tiempo real
- Todos los cálculos se actualizan al guardar

## Información Técnica

| Aspecto | Detalles |
|---------|----------|
| **Archivos Modificados** | 3 archivos (list.php, Certificate.php, CertificateController.php) |
| **Método Nuevo** | `Certificate::updateItemMonto()` |
| **Permisos** | Solo administradores |
| **Liquidaciones** | Se mantienen intactas, se recalculan pendientes |
| **Presupuesto** | Se actualiza col4 y saldo_disponible |

## 🔍 Validaciones

- ✅ Montos no pueden ser negativos
- ✅ Solo admin puede editar
- ✅ Se calcula automáticamente la diferencia para presupuesto
- ✅ Cantidad pendiente se ajusta con liquidaciones existentes

## 📊 Ejemplo Práctico

**Situación Inicial:**
```
Certificado ABC-001
├─ Item 1: Monto $1,000 (Liquidado $300, Pendiente $700)
├─ Item 2: Monto $2,000 (Sin liquidación, Pendiente $2,000)
└─ TOTAL: $3,000
```

**Editas:**
- Item 1: De $1,000 → $800
- Item 2: De $2,000 → $2,200

**Resultado:**
```
Certificado ABC-001
├─ Item 1: Monto $800 (Liquidado $300, Pendiente $500) ← Se ajustó
├─ Item 2: Monto $2,200 (Sin liquidación, Pendiente $2,200)
└─ TOTAL: $3,000 → $3,000 ✓
```

El presupuesto se ajusta automáticamente:
- Item 1 diferencia: -$200
- Item 2 diferencia: +$200
- Neto: 0 (pero actualizado en BD)

## ⚠️ Restricciones

- No puedes editar certificados que no hayan creado
- No puedes hacer el monto negativo
- Los cambios se guardan permanentemente en BD
- Se recomienda verificar presupuesto disponible antes de aumentar montos

## ❓ Preguntas Frecuentes

**P: ¿Si cambio el monto, se pierden las liquidaciones?**
R: No. Las liquidaciones se mantienen. Solo se actualiza la cantidad pendiente.

**P: ¿Puedo editar un item sin afectar a los otros?**
R: Sí. Cada item es independiente. Cambias solo lo que necesitas.

**P: ¿Se actualiza automáticamente el presupuesto?**
R: Sí. El sistema ajusta col4 y saldo_disponible automáticamente.

**P: ¿Quién puede editar?**
R: Solo administradores.

