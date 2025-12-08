# 🧠 MAPA MENTAL - SISTEMA DE CERTIFICADOS

```
                          SISTEMA DE CERTIFICADOS
                                    │
                ┌───────────────────┼───────────────────┐
                │                   │                   │
            USUARIO             PROCESOS              DATOS
                │                   │                   │
                │                   │           ┌───────┴────────┐
                │                   │           │                │
    ┌───────────┴──────────┐    ┌──┴───────┐  BD           MEMORIA
    │                      │    │          │   │                │
    ▼                      ▼    │          ▼   ▼                ▼
 CREATE              LIQUIDAR   │       SYNC   │
 ITEM                  ITEM     │      AUTO   ├─ certificados
  │                     │       │       │     │
  │                     │       │       │     ├─ detalle_
  │                     │       ▼       │     │  certificados
  │                     │              │     │
  │                     └─────────────┬─┼─────┤
  │                                   │ │     │
  │                        ┌──────────┼─┼─────┤ presupuesto_
  │                        │          │ │     │ items
  │                        ▼          ▼ ▼     │
  │                    FORMULAS    TRIGGERS  │
  │                        │          │      └─────────────┘
  │    ┌───────────────────┴──────────┴────┐
  │    │                                    │
  │    ▼                                    ▼
  │  qty_pend =              col4 ACTUALIZADO
  │  monto - qty_liq         AUTOMÁTICAMENTE
  │                                │
  └────────────┬───────────────────┘
               │
               ▼
        ✅ SINCRONIZADO
           SIEMPRE
```

---

## FLUJO EN ÁRBOL

```
USUARIO CREA CERTIFICADO
├─ CREATE ITEM 1
│  ├─ Trigger INSERT
│  ├─ col4 += monto
│  ├─ qty_pend = monto
│  └─ ✅ Sincronizado
│
├─ CREATE ITEM 2
│  ├─ Trigger INSERT
│  ├─ col4 += monto
│  ├─ qty_pend = monto
│  └─ ✅ Sincronizado
│
└─ LIQUIDAR ITEMS
   ├─ qty_pend = monto - qty_liq
   ├─ col4 -= qty_pend
   ├─ Recalcula totales
   └─ ✅ Sincronizado
```

---

## CONCEPTOS HIERÁRQUICOS

```
PRESUPUESTO
├─ col1: Presupuesto inicial
├─ col3: Codificado
├─ col4: TOTAL CERTIFICADO ◄─ LO IMPORTANTE
│  ├─ Aumenta: INSERT item
│  ├─ Disminuye: Liquidar item
│  └─ Elimina: DELETE item
├─ col5: Comprometido
├─ col6: Devengado
├─ col7: Liquidado (NO TOCAMOS)
└─ col8: Saldo

CERTIFICADO
├─ total_monto: Suma de montos
├─ total_liquidado = SUM(qty_liq)
│  └─ Lo que se pagó
├─ total_pendiente = SUM(qty_pend)
│  └─ Lo que falta pagar
└─ items
   ├─ monto: Costo del item
   ├─ qty_liquidacion: Cuánto se pagó
   ├─ qty_pendiente = monto - qty_liq
   │  └─ Cuánto falta pagar
   └─ codigo_completo: Enlace a presupuesto
```

---

## OPERACIONES EN RED

```
CREATE ITEM
    ↓
┌─ Tabla: detalle_certificados
│  └─ INSERT con qty_pend = monto
├─ Trigger: trg_item_insert
│  └─ UPDATE presupuesto col4 += monto
└─ Trigger: trg_update_cert_totales_insert
   └─ UPDATE certificados totales

LIQUIDAR ITEM
    ↓
┌─ PHP: updateLiquidacion()
│  ├─ Calcula qty_pend = monto - qty_liq
│  ├─ UPDATE presupuesto col4 -= qty_pend
│  ├─ UPDATE detalle_certificados
│  └─ UPDATE certificados totales
└─ ✅ Todo sincronizado

DELETE ITEM
    ↓
┌─ Trigger: trg_item_delete
│  └─ UPDATE presupuesto col4 -= monto
└─ Trigger: trg_update_cert_totales_delete
   └─ UPDATE certificados totales
```

---

## FÓRMULAS CLAVE

```
NIVEL ITEM:
  qty_pendiente = monto - qty_liquidacion
  
NIVEL PRESUPUESTO:
  col4 = SUM(qty_pendiente) de ese código
  
NIVEL CERTIFICADO:
  total_liquidado = SUM(qty_liquidacion)
  total_pendiente = SUM(qty_pendiente)
  
VERIFICACIÓN:
  col4 = SUM(qty_pendiente) POR CÓDIGO
  total_pendiente = SUM(qty_pendiente) DEL CERT
  No hay números negativos
```

---

## TABLAS RELACIONADAS

```
            certificados
                 │
                 │ FK: certificado_id
                 ▼
        detalle_certificados
             ├─ id
             ├─ certificado_id
             ├─ monto
             ├─ qty_liquidacion
             ├─ qty_pendiente ◄─ CLAVE
             └─ codigo_completo ◄─ FK
                             │
                             │ JOIN
                             ▼
                      presupuesto_items
                             │
                             ├─ id
                             ├─ codigo_completo
                             ├─ col4 ◄─ ACTUALIZADO
                             └─ otras cols
```

---

## ESTADO EN TIEMPO

```
T=0: CREATE ITEM
  detalle: qty_pend = 1000
  presupuesto: col4 = 1000

T=1: LIQUIDA 700
  detalle: qty_pend = 300
  presupuesto: col4 = 300

T=2: LIQUIDA 200 MÁS
  detalle: qty_pend = 100
  presupuesto: col4 = 100

T=3: LIQUIDA RESTO
  detalle: qty_pend = 0
  presupuesto: col4 = 0
  ✅ Item completamente liquidado
```

---

## VALIDACIONES EN CASCADA

```
updateLiquidacion()
  ├─ ¿Item existe?
  ├─ ¿qty_liq ≤ monto?
  ├─ ¿qty_liq ≥ 0?
  ├─ ¿Código en presupuesto?
  ├─ ¿qty_pend calculado?
  └─ Si todo OK:
      ├─ UPDATE presupuesto
      ├─ UPDATE detalle
      ├─ UPDATE certificados
      └─ ✅ COMMIT
      
Si falla algo:
  └─ ❌ ROLLBACK (no actualiza nada)
```

---

## PUNTOS CRÍTICOS

```
🔴 CRÍTICO 1: cantidad_pendiente
   └─ DEBE ser = monto - cantidad_liquidacion
   └─ Si no → col4 está mal

🔴 CRÍTICO 2: codigo_completo
   └─ DEBE existir en presupuesto
   └─ Si no → No actualiza col4

🔴 CRÍTICO 3: Triggers
   └─ DEBEN estar activos
   └─ Si no → Nada se sincroniza

✅ VERIFICACIÓN FINAL:
   col4 = SUM(cantidad_pendiente)
```

---

## CICLO COMPLETO

```
USER
 │
 ├─ Crea certificado → ✅ CERTIFICADO CREADO
 │
 ├─ Agrega item $5k → ✅ Item + col4 += 5k
 │
 ├─ Agrega item $3k → ✅ Item + col4 += 3k
 │  (col4 total = $8k)
 │
 ├─ Liquida Item 1 $2k → ✅ qty_pend=3k, col4-=3k
 │
 ├─ Liquida Item 1 $1k → ✅ qty_pend=2k, col4-=1k
 │  más (total $3k)
 │
 ├─ Liquida Item 2 $3k → ✅ qty_pend=0, col4-=3k
 │
 └─ ESTADO FINAL:
     ├─ Item 1: qty_pend = 2k
     ├─ Item 2: qty_pend = 0
     ├─ col4 presupuesto = 2k
     ├─ total_liquidado = 6k
     └─ total_pendiente = 2k
        ✅ TODO SINCRONIZADO
```

---

**Este mapa te ayuda a ver cómo todo se conecta.**
**Usa INDICE_DOCUMENTACION.md para profundizar en cada parte.**
