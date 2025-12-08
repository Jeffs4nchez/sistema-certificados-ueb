# 🎯 RESUMEN VISUAL: Cómo Funcionan las Actualizaciones

## 📐 Diagrama de Flujo

```
┌─────────────────────────────────────────────────────────────────┐
│ AGREGAR ITEM A CERTIFICADO                                       │
├─────────────────────────────────────────────────────────────────┤
│ Action: createDetail($data)                                      │
│                                                                   │
│ 1. INSERT detalle_certificados                                   │
│    ├─ monto = 1000                                               │
│    ├─ cantidad_liquidacion = 0                                   │
│    └─ cantidad_pendiente = 1000                                  │
│                                                                   │
│ 2. updatePresupuestoAddCertificado(codigo, 1000)                 │
│    ├─ SELECT col3, col4 FROM presupuesto_items                   │
│    ├─ col4_nuevo = col4 + 1000                                   │
│    ├─ saldo_nuevo = col3 - col4_nuevo                            │
│    └─ UPDATE presupuesto_items                                   │
│                                                                   │
│ Resultado:                                                        │
│ • col4: 0 → 1000 ✅                                              │
│ • saldo_disponible: 5000 → 4000 ✅                               │
└─────────────────────────────────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────────┐
│ EDITAR MONTO DEL ITEM                                            │
├─────────────────────────────────────────────────────────────────┤
│ Action: update($id, $data)                                       │
│                                                                   │
│ 1. Obtener monto anterior = 1000                                 │
│    Nuevo monto = 1500                                            │
│    Diferencia = 500                                              │
│                                                                   │
│ 2. UPDATE detalle_certificados                                   │
│    ├─ monto = 1500                                               │
│    └─ cantidad_pendiente = 1500                                  │
│                                                                   │
│ 3. IF diferencia > 0:                                            │
│    └─ updatePresupuestoAddCertificado(codigo, 500)               │
│       ├─ col4 += 500                                             │
│       └─ saldo -= 500                                            │
│    ELSE:                                                         │
│    └─ updatePresupuestoRemoveCertificado(codigo, diferencia)     │
│       ├─ col4 -= diferencia                                      │
│       └─ saldo += diferencia                                     │
│                                                                   │
│ Resultado:                                                        │
│ • col4: 1000 → 1500 ✅                                           │
│ • saldo_disponible: 4000 → 3500 ✅                               │
└─────────────────────────────────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────────┐
│ LIQUIDAR UN ITEM                                                 │
├─────────────────────────────────────────────────────────────────┤
│ Action: updateLiquidacion($detalle_id, 500)                      │
│                                                                   │
│ 1. cantidad_liquidacion_anterior = 0                             │
│    cantidad_liquidacion_nueva = 500                              │
│                                                                   │
│ 2. cantidad_pendiente_antigua = 1000 - 0 = 1000                  │
│    cantidad_pendiente_nueva = 1000 - 500 = 500                   │
│    diferencia_pendiente = 500 - 1000 = -500                      │
│                                                                   │
│ 3. updatePresupuestoAddCertificado(codigo, -500)                 │
│    (Llama a ADD con -500, que suma -500 = resta)                 │
│    ├─ col4 += (-500) = col4 - 500                                │
│    └─ saldo += 500                                               │
│                                                                   │
│ 4. UPDATE detalle_certificados                                   │
│    ├─ cantidad_liquidacion = 500                                 │
│    └─ cantidad_pendiente = 500                                   │
│                                                                   │
│ Resultado:                                                        │
│ • col4: 1000 → 500 ✅  (menos por certificar)                    │
│ • saldo_disponible: 3500 → 4000 ✅  (más disponible)             │
└─────────────────────────────────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────────┐
│ ELIMINAR UN ITEM                                                 │
├─────────────────────────────────────────────────────────────────┤
│ Action: deleteDetail($id)                                        │
│                                                                   │
│ 1. SELECT monto FROM detalle_certificados WHERE id = ?           │
│    monto = 1000                                                  │
│                                                                   │
│ 2. DELETE FROM detalle_certificados WHERE id = ?                 │
│                                                                   │
│ 3. updatePresupuestoRemoveCertificado(codigo, 1000)              │
│    ├─ col4 -= 1000                                               │
│    └─ saldo += 1000                                              │
│                                                                   │
│ Resultado:                                                        │
│ • col4: 500 → 0 ✅                                               │
│ • saldo_disponible: 4000 → 5000 ✅  (completamente recuperado)   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Estado Antes vs Después

### Escenario: Presupuesto de $5000, Certificar $1000, Liquidar $500, Eliminar

| Paso | col3 | col4 | saldo | Descripción |
|------|------|------|-------|-------------|
| 0 | 5000 | 0 | 5000 | Inicial (sin certificados) |
| 1 | 5000 | 1000 | 4000 | ✅ Agregó item de $1000 |
| 2 | 5000 | 500 | 4500 | ✅ Liquidó $500 (queda $500 por liquidar) |
| 3 | 5000 | 0 | 5000 | ✅ Eliminó el item (se recuperó todo) |

---

## 📝 Código Generado

### Métodos Privados

```php
// updatePresupuestoAddCertificado($codigo, $monto)
SELECT col3, col4 FROM presupuesto_items WHERE codigo_completo = ?
col4_nuevo = col4 + monto
saldo_nuevo = col3 - col4_nuevo
UPDATE presupuesto_items SET col4 = ?, saldo_disponible = ? WHERE codigo_completo = ?

// updatePresupuestoRemoveCertificado($codigo, $monto)
SELECT col3, col4 FROM presupuesto_items WHERE codigo_completo = ?
col4_nuevo = max(0, col4 - monto)  // Evita negativos
saldo_nuevo = col3 - col4_nuevo
UPDATE presupuesto_items SET col4 = ?, saldo_disponible = ? WHERE codigo_completo = ?
```

### Método Público Nuevo

```php
// deleteDetail($id)
SELECT monto, codigo_completo FROM detalle_certificados WHERE id = ?
DELETE FROM detalle_certificados WHERE id = ?
updatePresupuestoRemoveCertificado(codigo_completo, monto)
```

### Métodos Públicos Modificados

```php
// createDetail($data)
... INSERT ...
updatePresupuestoAddCertificado(codigoCompleto, monto)  ← NUEVO

// update($id, $data)
... SELECT monto_anterior ...
... UPDATE ...
if diferencia > 0: updatePresupuestoAddCertificado(...)  ← NUEVO
else: updatePresupuestoRemoveCertificado(...)             ← NUEVO

// delete($id)
SELECT id FROM detalle_certificados WHERE certificado_id = ?
foreach item: deleteDetail(item['id'])  ← NUEVO (llamaba directo)
DELETE certificado

// updateLiquidacion($detalle_id, $cantidad_liq)
... calcular diferencia_pendiente ...
updatePresupuestoAddCertificado(codigo, diferencia_pendiente)  ← MODIFICADO
... UPDATE detalle_certificados ...
```

---

## ✅ Checklist de Implementación

- [x] Métodos privados `updatePresupuestoAddCertificado()` creados
- [x] Métodos privados `updatePresupuestoRemoveCertificado()` creados
- [x] Método `createDetail()` llamando a updatePresupuestoAddCertificado()
- [x] Método `update()` detectando cambio de monto
- [x] Método `deleteDetail()` creado y llamando a updatePresupuestoRemoveCertificado()
- [x] Método `delete()` iterando sobre items
- [x] Método `updateLiquidacion()` calculando diferencia de pendiente
- [x] Logs agregados para debugging
- [x] Sin errores PHP
- [x] Documentación completada

---

## 🎓 Cómo Entender la Lógica

### Principio Fundamental

**col4 = Suma de lo que falta por liquidar de todos los items**

Por lo tanto:
- Cuando agregas un item: col4 aumenta (hay más para certificar)
- Cuando liquidás: col4 disminuye (queda menos para liquidar)
- Cuando eliminas: col4 baja (ese item ya no necesita certificarse)

### saldo_disponible = Lo que queda disponible del presupuesto

- Si col4 aumenta → saldo_disponible disminuye
- Si col4 disminuye → saldo_disponible aumenta
- Nunca puede ser negativo (col3 es el máximo disponible)

---

## 📊 Ejemplo Completo

```
Presupuesto: $5000
Proyecto: 82 00 000 002 003 0200 510203

OPERACIÓN 1: Agregar Item A ($1000)
├─ INSERT: monto=1000, cantidad_liquidacion=0, cantidad_pendiente=1000
├─ col4: 0 → 1000 (+1000)
└─ saldo: 5000 → 4000

OPERACIÓN 2: Agregar Item B ($1500)
├─ INSERT: monto=1500, cantidad_liquidacion=0, cantidad_pendiente=1500
├─ col4: 1000 → 2500 (+1500)
└─ saldo: 4000 → 2500

OPERACIÓN 3: Liquidar Item A $500
├─ UPDATE: cantidad_liquidacion=500, cantidad_pendiente=500
├─ Diferencia pendiente: 1000 → 500 = -500
├─ col4: 2500 → 2000 (-500)
└─ saldo: 2500 → 3000

OPERACIÓN 4: Eliminar Item B
├─ DELETE: cantidad_pendiente=1500
├─ col4: 2000 → 500 (-1500)
└─ saldo: 3000 → 4500

ESTADO FINAL:
├─ Item A: monto=1000, liquidado=500, pendiente=500
├─ col4: 500 (solo el pendiente de A)
├─ col3: 5000
└─ saldo: 4500 (5000 - 500)
```

---

**¡LISTO PARA USAR!** ✨
