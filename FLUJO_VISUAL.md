# 🎯 FLUJO VISUAL SIMPLIFICADO

## LA IDEA PRINCIPAL EN 10 SEGUNDOS

```
PRESUPUESTO                    CERTIFICADOS
(col4)                         (total_pendiente)

  1000 ←─────────── INSERT ITEM monto 1000
                    
  1000 ┐
   700 │ LIQUIDAR 700  
   600 │ LIQUIDAR 200  ←── Automático: col4 -= cantidad_pendiente
       │
       └─ col4 = 600  (porque quedan 100 pendientes)

EXPLICACIÓN:
- col4 = lo que falta por liquidar
- Cuando liquidas, col4 baja
- Col4 final = monto - lo_que_liquidaste
```

---

## CASO DE USO REAL

### 📋 SITUACIÓN:
```
Item de presupuesto:
  Código: 01 00 001 002 001...
  Monto: $10,000
  Col4 (Total Certificado): $0
```

### ➕ PASO 1: CREAR CERTIFICADO CON ITEM
```
Certificado nuevo:
├─ Item 1: monto $5,000
│  ✓ INSERT → trigger → col4 += $5,000
│  Resultado: col4 = $5,000
│
├─ Item 2: monto $3,000  
│  ✓ INSERT → trigger → col4 += $3,000
│  Resultado: col4 = $8,000
│
└─ Item 3: monto $2,000
   ✓ INSERT → trigger → col4 += $2,000
   Resultado: col4 = $10,000 ✅ (lleno!)
```

### 💰 PASO 2: LIQUIDAR ITEMS
```
Liquidación Item 1 ($5,000 monto):

Usuario ingresa: $3,000 liquidados

Sistema calcula:
  cantidad_pendiente = $5,000 - $3,000 = $2,000
  
Actualiza presupuesto:
  col4 = col4 - cantidad_pendiente
  col4 = $10,000 - $2,000 = $8,000 ✅
  
Significado:
  ✓ Col4 bajó $2,000 (porque eso es lo que falta por liquidar)
```

### 📊 PASO 3: ESTADO INTERMEDIO
```
PRESUPUESTO:
  col4 anterior: $10,000
  col4 actual:   $8,000 (Item 1 tiene $2000 pendientes)
  
CERTIFICADO:
  total_liquidado: $3,000 (Item 1 liquidado)
  total_pendiente: $2,000 + $3,000 + $2,000 = $7,000
```

### 💰 PASO 4: LIQUIDAR ITEM 1 COMPLETAMENTE
```
Usuario ingresa: $5,000 liquidados (TOTAL en Item 1)

Sistema calcula:
  cantidad_pendiente = $5,000 - $5,000 = $0
  
Actualiza presupuesto:
  col4 = col4 - cantidad_pendiente
  col4 = $8,000 - $2,000 = $6,000 ✅
  (Resta lo que faltaba: $2000)
  
RESULTADO:
  ✓ Item 1 completamente liquidado
  ✓ Col4 ahora = $6,000 (Items 2 y 3 sin liquidar)
```

### 📊 PASO 5: LIQUIDAR ITEM 2
```
Item 2: monto $3,000

Usuario ingresa: $3,000 liquidados (TOTAL)

Sistema calcula:
  cantidad_pendiente = $3,000 - $3,000 = $0
  
Actualiza presupuesto:
  col4 = $6,000 - $3,000 = $3,000 ✅
  
RESULTADO:
  ✓ Items 1 y 2 completamente liquidados  
  ✓ Solo Item 3 ($2000) pendiente
  ✓ Col4 = $3,000
```

### 📊 ESTADO FINAL
```
CERTIFICADO COMPLETAMENTE LIQUIDADO:

Item 1: $5,000 monto → $5,000 liquidado → $0 pendiente
Item 2: $3,000 monto → $3,000 liquidado → $0 pendiente  
Item 3: $2,000 monto → $0 liquidado    → $2,000 pendiente

TOTALES:
  total_liquidado = $5,000 + $3,000 + $0 = $8,000
  total_pendiente = $0 + $0 + $2,000 = $2,000
  
PRESUPUESTO:
  col4 = $2,000 (solo Item 3 sin liquidar)
```

---

## 🔄 CICLO DE VIDA DE UN ITEM

```
CREAR ITEM (monto $1000)
        │
        ├─ cantidad_liquidacion = $0
        ├─ cantidad_pendiente = $1000  
        ├─ col4 += $1000
        │
        ▼
   ITEM CREADO
        │
        ├─ Usuario liquida $300
        │  ├─ cantidad_liquidacion = $300
        │  ├─ cantidad_pendiente = $700
        │  ├─ col4 -= $700 (lo que falta)
        │
        ├─ Usuario liquida $200 más (total $500)
        │  ├─ cantidad_liquidacion = $500
        │  ├─ cantidad_pendiente = $500
        │  ├─ col4 -= $200 (la diferencia)
        │
        ├─ Usuario liquida $500 más (total $1000)
        │  ├─ cantidad_liquidacion = $1000
        │  ├─ cantidad_pendiente = $0
        │  ├─ col4 -= $500 (lo que faltaba)
        │
        ▼
   ITEM COMPLETAMENTE LIQUIDADO
        │
        ├─ cantidad_pendiente = $0
        ├─ col4 ya no incluye este item
        │
        ▼
   USUARIO PUEDE ELIMINAR O DEJAR COMO COMPLETADO
```

---

## ⚡ RESUMIENDO: 3 REGLAS SIMPLES

### REGLA 1: Cuando creas un item
```
col4 = col4 + monto
cantidad_pendiente = monto (sin liquidar aún)
```

### REGLA 2: Cuando liquidas
```
cantidad_pendiente = monto - lo_liquidado
col4 = col4 - cantidad_pendiente (la nueva cantidad_pendiente)
```

### REGLA 3: Cuando eliminas
```
col4 = col4 - monto
cantidad_liquidacion = 0
cantidad_pendiente = 0
```

---

## ✅ VERIFICACIÓN RÁPIDA

¿Col4 es correcto si...?

```
✓ col4 = SUM(cantidad_pendiente) de todos los items del código
✓ cantidad_pendiente siempre = monto - cantidad_liquidacion  
✓ total_pendiente = SUM(cantidad_pendiente) del certificado
✓ No hay valores negativos
✓ cantidad_liquidacion nunca > monto
```

Si todo esto es verdad → SISTEMA CORRECTO ✅
