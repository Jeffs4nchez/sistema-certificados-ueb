# 📚 ÍNDICE DE DOCUMENTACIÓN

## 🎯 ¿QUÉ QUIERO ENTENDER?

### Soy usuario y quiero entender qué hace el sistema
👉 **Leer:** `RESUMEN_QUE_HACE.md`
- Explicación simple en 30 segundos
- Caso de uso real
- Lo que se previene

---

### Quiero ver el flujo operativo paso a paso
👉 **Leer:** `FLUJO_VISUAL.md`
- Flujo simplificado
- Ciclo de vida de un item
- 3 reglas simples
- Verificación rápida

---

### Quiero entender cómo se conectan todas las tablas
👉 **Leer:** `ESTRUCTURA_DATOS.md`
- Las 3 tablas principales
- Relaciones y conexiones
- Flujo de datos con valores numéricos
- Ejemplo numérico completo

---

### Quiero ver un diagrama visual con valores reales
👉 **Leer:** `DIAGRAMA_OPERATIVO.md`
- Paso a paso con valores
- Tabla de estados en cada operación
- Validaciones en cada paso
- Triggers que se activan

---

### Quiero el flujo completo detallado
👉 **Leer:** `FLUJO_COMPLETO.md`
- Flujo 1: Crear certificado con items
- Flujo 2: Liquidar un item
- Flujo 3: Liquidar más (acumular)
- Flujo 4: Eliminar items
- Tabla resumen
- Conceptos clave
- Triggers automáticos

---

### Quiero entender qué se arregló (historial)
👉 **Leer:** `LIQUIDACION_FINAL_COL4.md`
- Cambios implementados
- Corrección de createDetail()
- Actualización de updateLiquidacion()
- Corrección de datos históricos
- Tests y verificación

---

## 🔧 ¿QUÉ NECESITO IMPLEMENTAR?

### Quiero validar que todo funciona
👉 **Ejecutar:**
```bash
php corregir_cantidad_pendiente.php      # Arregla datos históricos
php create_totales_triggers.php          # Crea triggers de totales
php test_liquidacion_col4_real.php       # Valida que todo funciona
```

---

### Quiero entender el código PHP
👉 **Ver archivo:** `app/models/Certificate.php`
- Método: `createDetail()` (línea ~76)
  - Inicializa cantidad_pendiente = monto - liquidacion
- Método: `updateLiquidacion()` (línea ~261)
  - Actualiza col4 en presupuesto

---

### Quiero ver los triggers SQL
👉 **Ver archivos:**
- `database/create_triggers.sql` - Triggers de items (INSERT/UPDATE/DELETE)
- O ejecutar: `create_totales_triggers.php` - Crea triggers de certificados

---

## 📊 TABLAS DE REFERENCIA

### cantidad_pendiente
```
¿QUÉ ES? Lo que falta liquidar
FÓRMULA: monto - cantidad_liquidacion
DÓNDE: detalle_certificados.cantidad_pendiente
ACTUALIZADO: Automáticamente al liquidar
```

### col4
```
¿QUÉ ES? Total certificado en presupuesto
CÓMO CRECE: INSERT item → col4 += monto
CÓMO DECRECE: LIQUIDA item → col4 -= cantidad_pendiente
DÓNDE: presupuesto_items.col4
AUTOMÁTICO: Sí, por triggers
```

### total_liquidado
```
¿QUÉ ES? Suma de todo liquidado en certificado
FÓRMULA: SUM(cantidad_liquidacion)
DÓNDE: certificados.total_liquidado
ACTUALIZADO: Automáticamente por triggers
```

### total_pendiente
```
¿QUÉ ES? Suma de todo pendiente en certificado
FÓRMULA: SUM(cantidad_pendiente)
DÓNDE: certificados.total_pendiente
ACTUALIZADO: Automáticamente por triggers
```

---

## 🎓 EJEMPLOS PRÁCTICOS

### Ejemplo 1: Crear y liquidar un item
```
1. Crear item de $1,000
   → col4 += $1,000

2. Liquidar $700
   → cantidad_pendiente = $1,000 - $700 = $300
   → col4 -= $300
   → col4 = $700 ✅

3. Liquidar $200 más (total $900)
   → cantidad_pendiente = $1,000 - $900 = $100
   → col4 -= $200
   → col4 = $500 ✅
```

Ver: `test_liquidacion_col4_real.php`

---

### Ejemplo 2: Múltiples items
```
Item 1: $5,000
Item 2: $3,000
Total presupuesto: $8,000

Liquidas Item 1 con $3,000:
  → Item 1 pendiente = $2,000
  → col4 -= $2,000
  → col4 = $6,000

Liquidas Item 2 completamente:
  → Item 2 pendiente = $0
  → col4 -= $3,000
  → col4 = $3,000
```

Ver: `DIAGRAMA_OPERATIVO.md`

---

## ✅ VALIDACIÓN (¿Funciona?)

Ejecuta cualquiera de estos scripts:

```bash
# Verifica items correctos
php corregir_cantidad_pendiente.php

# Verifica triggers correctos
php verificar_triggers_completo.php

# Hace un test completo
php test_liquidacion_col4_real.php

# Verifica estado de base de datos
php check_columns_detalle.php
```

---

## 🚨 ERRORES COMUNES

### ❌ col4 no cambia al liquidar
**Causa:** No está ejecutado `updateLiquidacion()` correctamente
**Solución:** Verificar que el código_completo existe en presupuesto

### ❌ cantidad_pendiente no se calcula
**Causa:** No actualizó `createDetail()`
**Solución:** Ejecutar `corregir_cantidad_pendiente.php`

### ❌ total_pendiente no se actualiza
**Causa:** Triggers de certificados no creados
**Solución:** Ejecutar `create_totales_triggers.php`

### ❌ Los datos históricos están mal
**Causa:** Items viejos creados antes de la corrección
**Solución:** Ejecutar `corregir_cantidad_pendiente.php`

---

## 📞 PREGUNTAS FRECUENTES

### ¿Por qué col4 baja cuando liquido?
Porque col4 representa lo que FALTA liquidar, no lo total certificado.

### ¿Puedo liquidar parcialmente?
Sí, puedes liquidar $700 de $1000. Se recalcula automáticamente.

### ¿Qué pasa si elimino un item?
Se resta el monto de col4 (vuelve a estado anterior).

### ¿Se puede anular una liquidación?
Sí, actualizando la cantidad_liquidacion a un valor menor.

### ¿Dónde se guardas los cambios?
En las tablas: detalle_certificados, certificados, presupuesto_items

---

## 🎯 FLUJO RECOMENDADO DE LECTURA

Para entender de 0:
1. `RESUMEN_QUE_HACE.md` (5 min)
2. `FLUJO_VISUAL.md` (5 min)
3. `ESTRUCTURA_DATOS.md` (10 min)
4. `DIAGRAMA_OPERATIVO.md` (10 min)
5. Ejecutar: `test_liquidacion_col4_real.php`

**Total: ~30 minutos para entender completamente**

---

## 📝 NOTAS IMPORTANTES

- **Todas las actualizaciones son automáticas** - No toques manualmente las tablas
- **Los triggers hacen el trabajo pesado** - Son necesarios para sincronización
- **PHP valida todo antes de actualizar** - No hay riesgo de datos inconsistentes
- **Verificación siempre:** col4 = SUM(cantidad_pendiente)

---

**¿Necesitas algo específico? Pregunta aquí.**
