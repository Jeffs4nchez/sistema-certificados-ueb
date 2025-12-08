# 📊 FLUJO COMPLETO DEL SISTEMA DE CERTIFICADOS

## 🎯 OBJETIVO GENERAL
Sincronizar los **certificados y sus liquidaciones** con el **presupuesto**, manteniendo col4 actualizado.

---

## 📈 FLUJO 1: CREAR UN CERTIFICADO CON ITEMS

### Paso 1: Usuario crea certificado
```
┌─────────────────────────────┐
│  CREAR CERTIFICADO          │
│  - Número: CERT-001         │
│  - Monto Total: $10,000     │
│  - Institución: ABC         │
└──────────────┬──────────────┘
               │
               ▼
        ✅ CERTIFICADO CREADO
         (certificados table)
            ID: 151
```

### Paso 2: Agregar items al certificado
```
┌──────────────────────────────────┐
│  CREAR ITEM 1                    │
│  - Código: 01 00 001 002 001...  │
│  - Monto: $1,000                 │
│  - cantidad_liquidacion: 0       │
│  - cantidad_pendiente: $1,000    │
└──────────────┬───────────────────┘
               │
               ▼
      📌 TRIGGER INSERT ACTIVA
      (function: trigger_item_insert)
               │
               ▼
    ┌──────────────────────────────┐
    │  PRESUPUESTO ACTUALIZADO     │
    │  col4 = col4 + 1,000         │
    │  col8 = saldo - 1,000        │
    └──────────────────────────────┘
               │
               ▼
    ✅ ITEM REGISTRADO
    detalle_certificados:
    - ID: 240
    - monto: $1,000
    - cantidad_liquidacion: $0
    - cantidad_pendiente: $1,000
```

### Paso 3: Se agregan más items
```
ITEM 2: monto $500
ITEM 3: monto $2,000
ITEM 4: monto $1,500

    ▼ CADA UNO EJECUTA TRIGGER INSERT ▼

PRESUPUESTO ACUMULADO:
col4 = $0 + $1,000 + $500 + $2,000 + $1,500 = $5,000

CERTIFICADO TOTAL:
total_pendiente = $1,000 + $500 + $2,000 + $1,500 = $5,000
```

---

## 💰 FLUJO 2: LIQUIDAR UN ITEM

### Escenario: Liquidad el Item 1 con $700

```
┌────────────────────────────────┐
│  USUARIO ABRE MODAL LIQUIDACIÓN │
│  Item 1: monto $1,000           │
│  Liquidación actual: $0         │
│  Pendiente actual: $1,000       │
└────────────────────┬────────────┘
                     │
                     ▼
         ┌─────────────────────┐
         │ INGRESA: $700       │
         └─────────┬───────────┘
                   │
                   ▼
      updateLiquidacion(item_id=240, 700)
                   │
                   ├─ PASO 1: VALIDAR
                   │  ✓ 700 ≤ 1000 (monto)
                   │  ✓ 700 ≥ 0
                   │
                   ├─ PASO 2: CALCULAR
                   │  cantidad_pendiente_nuevo = 1000 - 700 = $300
                   │
                   ├─ PASO 3: ACTUALIZAR PRESUPUESTO
                   │  col4 = col4 - 300 (RESTA cantidad_pendiente)
                   │  col4 pasó de $1,000 a $700
                   │
                   ├─ PASO 4: ACTUALIZAR ITEM
                   │  UPDATE detalle_certificados:
                   │  - cantidad_liquidacion = $700
                   │  - cantidad_pendiente = $300
                   │
                   ├─ PASO 5: RECALCULAR CERTIFICADO
                   │  total_liquidado = SUM(cantidad_liquidacion)
                   │  total_pendiente = SUM(cantidad_pendiente)
                   │  
                   │  total_liquidado = $700
                   │  total_pendiente = $300 + $500 + $2000 + $1500 = $4,300
                   │
                   └─ PASO 6: GUARDAR MEMORANDO (opcional)
                      memorando = "Comprobante #123"
                     │
                     ▼
          ✅ LIQUIDACIÓN GUARDADA
            
ESTADO FINAL ITEM 1:
┌──────────────────────────────┐
│ cantidad_liquidacion: $700   │
│ cantidad_pendiente: $300     │
│ memorando: "Comprobante#123" │
└──────────────────────────────┘

PRESUPUESTO ITEM:
┌──────────────────────────────┐
│ col4: $700 (era $1,000)      │
│ Reducción: $300              │
└──────────────────────────────┘

CERTIFICADO TOTALES:
┌────────────────────────────────┐
│ total_liquidado: $700          │
│ total_pendiente: $4,300        │
└────────────────────────────────┘
```

---

## 📝 FLUJO 3: LIQUIDAR MÁS (ACUMULAR LIQUIDACIÓN)

### Usuario liquida otros $200 (total $900 en item 1)

```
updateLiquidacion(item_id=240, 900)

CÁLCULOS:
│
├─ cantidad_pendiente_anterior = $300
├─ cantidad_pendiente_nuevo = 1000 - 900 = $100
├─ DIFERENCIA = $300 - $100 = $200 (diferencia a restar de col4)
│
├─ RESTAR DE COL4: col4 -= $100
│  (la cantidad_pendiente_nuevo, NO la diferencia)
│  col4 pasó de $700 a $600
│
├─ ACTUALIZAR ITEM:
│  - cantidad_liquidacion = $900
│  - cantidad_pendiente = $100
│
└─ RECALCULAR CERTIFICADO:
   total_liquidado = $900
   total_pendiente = $100 + $500 + $2000 + $1500 = $4,100
```

---

## 🔄 FLUJO 4: ELIMINAR UN ITEM

### Usuario elimina Item 2 (monto $500)

```
DELETE FROM detalle_certificados WHERE id = 241

    ▼ TRIGGER DELETE ACTIVA ▼

PRESUPUESTO ACTUALIZADO:
col4 = col4 - 500 (antes del DELETE)
col4 pasó de $5,000 a $4,500

CERTIFICADO ACTUALIZADO (TRIGGER):
total_liquidado = $900 (sin Item 2)
total_pendiente = $100 + $2000 + $1500 = $3,600
(Sin el $500 de Item 2)
```

---

## 📊 TABLA RESUMEN: ESTADOS DEL SISTEMA

| Momento | Item1 Liq | Item1 Pend | Item2 Liq | Item2 Pend | col4 Cert | Cert Total Liq | Cert Total Pend |
|---------|-----------|-----------|-----------|-----------|-----------|----------------|-----------------|
| Inicial | $0 | $1,000 | $0 | $500 | $1,500 | $0 | $1,500 |
| Liq 700 en Item1 | $700 | $300 | $0 | $500 | $800 | $700 | $800 |
| Liq 900 en Item1 | $900 | $100 | $0 | $500 | $600 | $900 | $600 |
| Delete Item2 | $900 | $100 | — | — | $100 | $900 | $100 |

---

## 🔑 CONCEPTOS CLAVE

### ✅ cantidad_liquidacion
```
¿QUÉ ES? Cuánto se ha pagado/liquidado del item
EJEMPLO: Si Item cuesta $1000 y se liquidaron $700
cantidad_liquidacion = $700
```

### ✅ cantidad_pendiente
```
¿QUÉ ES? Lo que falta por liquidar
FÓRMULA: cantidad_pendiente = monto - cantidad_liquidacion
EJEMPLO: $1000 - $700 = $300
```

### ✅ col4 (en presupuesto_items)
```
¿QUÉ ES? Total Certificado para ese código de presupuesto
CÓMO SUBE: 
  - Cuando se INSERT un item: col4 += monto

CÓMO BAJA:
  - Cuando se LIQUIDA: col4 -= cantidad_pendiente
  - Cuando se DELETE un item: col4 -= monto
```

### ✅ total_liquidado (en certificados)
```
¿QUÉ ES? Suma de todas las liquidaciones del certificado
FÓRMULA: SUM(cantidad_liquidacion) de todos los items
EJEMPLO: Item1($700) + Item2($0) + Item3($500) = $1200
```

### ✅ total_pendiente (en certificados)
```
¿QUÉ ES? Suma de lo que falta liquidar en el certificado
FÓRMULA: SUM(cantidad_pendiente) de todos los items
EJEMPLO: Item1($300) + Item2($500) + Item3($1500) = $2300
```

---

## ⚙️ TRIGGERS AUTOMÁTICOS

### 🔴 Cuando INSERT un item en detalle_certificados:
```
TRIGGER: trg_item_insert

ACCIÓN:
1. col4 en presupuesto += monto del item
2. col8 (saldo) -= monto del item
3. total_liquidado en certificados += 0 (nuevo item sin liquidar)
4. total_pendiente en certificados += monto del item
```

### 🟡 Cuando UPDATE un item en detalle_certificados:
```
TRIGGER: trg_item_update

ACCIÓN:
Si cambió el monto:
1. Recalcular col4 (restar monto anterior, sumar monto nuevo)
2. Recalcular totales del certificado
```

### 🟢 Cuando DELETE un item en detalle_certificados:
```
TRIGGER: trg_item_delete

ACCIÓN:
1. col4 en presupuesto -= monto del item
2. Recalcular total_liquidado del certificado
3. Recalcular total_pendiente del certificado
```

### 🔵 Cuando se LIQUIDA un item (código PHP):
```
updateLiquidacion() PHP CODE

ACCIÓN:
1. Calcular cantidad_pendiente = monto - cantidad_liquidacion
2. col4 en presupuesto -= cantidad_pendiente (IMPORTANTE!)
3. Actualizar cantidad_liquidacion y cantidad_pendiente del item
4. Recalcular total_liquidado y total_pendiente del certificado
```

---

## 📌 DIFERENCIA CLAVE: INSERT vs LIQUIDACIÓN

### ❌ ANTES (INCORRECTO):
```
INSERT ITEM:  col4 += monto = $1000
LIQUIDAR:     (no actualizaba col4)
RESULTADO:    col4 = $1000 (INCORRECTO!)
```

### ✅ AHORA (CORRECTO):
```
INSERT ITEM:  col4 += monto = $1000
LIQUIDAR $700: col4 -= (1000-700) = col4 -= $300
RESULTADO:    col4 = $700 (CORRECTO!)
```

---

## 🎓 EJEMPLO PRÁCTICO COMPLETO

```
INICIO:
- presupuesto_items col4 = $0
- certificados total_pendiente = $0

PASO 1: Crear item con monto $1000
  INSERT → trigger → col4 = $1000, total_pendiente = $1000

PASO 2: Liquidar $300
  UPDATE → cantidad_pendiente = $700
         → col4 = $1000 - $700 = $300
         → total_pendiente = $700

PASO 3: Liquidar $500 más (total $800)
  UPDATE → cantidad_pendiente = $200
         → col4 = $300 - $200 = $100
         → total_pendiente = $200

RESULTADO FINAL:
  Item:        cantidad_liquidacion = $800, cantidad_pendiente = $200
  Presupuesto: col4 = $100
  Certificado: total_liquidado = $800, total_pendiente = $200
```

---

## 🚨 VALIDACIONES

```
Al liquidar, se verifica:

1. ✓ El item existe
2. ✓ cantidad_liquidacion ≤ monto (no puedes liquidar más que el monto)
3. ✓ cantidad_liquidacion ≥ 0 (no puedes liquidar negativo)
4. ✓ El código_completo existe en presupuesto (para actualizar col4)
5. ✓ Se calcula correctamente cantidad_pendiente

Si falla alguna → Se lanza excepción → No se guarda nada
```

---

**¿Entendido? ¿Alguna parte que quieras que explique más?**
