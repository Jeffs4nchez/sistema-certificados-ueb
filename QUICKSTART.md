# ⚡ QUICK START - 2 MINUTOS

## LA ESENCIA EN 1 GRÁFICO

```
USUARIO                CERTIFICADO              PRESUPUESTO
────────────────────────────────────────────────────────────
                                              col4 = $0
│
├─ Crea item          cantidad_pendiente      col4 += $1000
│  monto $1000        = $1000                 col4 = $1000
│
├─ Liquida $700       cantidad_pendiente      col4 -= $300
│                     = $300                  col4 = $700
│
├─ Liquida $200 más   cantidad_pendiente      col4 -= $100
│  (total $900)       = $100                  col4 = $600
│
└─ RESULTADO FINAL
   ✓ cantidad_pendiente = $100
   ✓ col4 = $600
   ✓ TODO SINCRONIZADO
```

---

## TRES REGLAS (Eso es TODO)

### Regla 1: Crear item
```
monto = $1000
INSERT → col4 += $1000
```

### Regla 2: Liquidar
```
liquidas $700
cantidad_pendiente = 1000 - 700 = 300
col4 -= 300
```

### Regla 3: Eliminar
```
DELETE item
col4 -= 1000
```

**¡Eso es TODA la lógica!**

---

## VERIFICACIÓN

```
¿Funciona bien si...?

✓ col4 = SUM(cantidad_pendiente) por código
✓ cantidad_pendiente = monto - cantidad_liquidacion
✓ No hay números negativos

SI ESTO ES VERDAD → SISTEMA CORRECTO ✅
```

---

## ARCHIVOS IMPORTANTES

```
Documentación:
  └─ INDICE_DOCUMENTACION.md ← EMPIEZA POR AQUÍ

Quick reference:
  ├─ FLUJO_VISUAL.md (mejor para visualizar)
  ├─ ESTRUCTURA_DATOS.md (mejor para entender tablas)
  └─ DIAGRAMA_OPERATIVO.md (mejor para paso a paso)

Código:
  └─ app/models/Certificate.php
      ├─ createDetail() línea ~76
      └─ updateLiquidacion() línea ~261

Tests:
  └─ test_liquidacion_col4_real.php
```

---

## TL;DR (Demasiado largo; no lo leí)

Sistema que:
1. ✅ Suma monto a col4 cuando creas item
2. ✅ Resta cantidad_pendiente de col4 cuando liquidas
3. ✅ Todo automático con triggers

**Resultado:** Presupuesto siempre sincronizado

---

**Para entender mejor, lee `INDICE_DOCUMENTACION.md`**
