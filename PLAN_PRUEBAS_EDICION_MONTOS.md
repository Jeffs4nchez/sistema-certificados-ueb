# 🧪 Plan de Pruebas - Edición de Montos en Certificados

## Pruebas Funcionales

### Test 1: Abrir Modal de Edición
**Objetivo**: Verificar que el modal carga correctamente

**Pasos**:
1. Ir a Certificados
2. Hacer clic en ✏️ (Editar) de un certificado
3. Esperar a que cargue el modal

**Resultado Esperado**:
- ✅ Modal se abre sin errores
- ✅ Formulario está pre-llenado con datos actuales
- ✅ Tabla de items muestra todos los items
- ✅ Columna "Monto (Editable)" tiene inputs numéricos

---

### Test 2: Editar Monto Sencillo (Sin Liquidaciones)
**Objetivo**: Verificar cambio básico de monto

**Datos de Prueba**:
- Certificado con 1 item
- Item sin liquidaciones previas
- Monto inicial: $1,000

**Pasos**:
1. Abrir modal de edición
2. Cambiar monto de $1,000 a $1,500
3. Verificar que total se actualiza en modal (debe ser $1,500)
4. Hacer clic en "Guardar Cambios"

**Resultado Esperado**:
- ✅ Total se actualiza en tiempo real en modal
- ✅ Respuesta success en JSON
- ✅ Página se recarga
- ✅ En BD: detalle_certificados.monto = 1500
- ✅ En BD: certificados.monto_total = 1500
- ✅ En BD: presupuesto_items.col4 aumentó en 500

**SQL de Verificación**:
```sql
SELECT monto, cantidad_pendiente FROM detalle_certificados WHERE id = [item_id];
-- Esperado: 1500, 1500

SELECT col4, saldo_disponible FROM presupuesto_items WHERE codigo_completo = '[code]' AND year = 2025;
-- Esperado: col4 = 1500, saldo = 200 (si col3 es 1700)
```

---

### Test 3: Editar Monto con Liquidaciones Existentes
**Objetivo**: Verificar que liquidaciones se mantienen y cantidad_pendiente se recalcula

**Datos de Prueba**:
- Certificado con 1 item
- Monto: $1,000
- Liquidación: $300
- Cantidad pendiente inicial: $700

**Pasos**:
1. Abrir modal de edición
2. Cambiar monto de $1,000 a $800
3. Guardar cambios

**Resultado Esperado**:
- ✅ En BD: detalle_certificados.monto = 800
- ✅ En BD: detalle_certificados.cantidad_liquidacion = 300 (SIN CAMBIO)
- ✅ En BD: detalle_certificados.cantidad_pendiente = 500 (800 - 300)
- ✅ Presupuesto actualizado: col4 disminuyó en 200

**SQL de Verificación**:
```sql
SELECT monto, cantidad_liquidacion, cantidad_pendiente 
FROM detalle_certificados WHERE id = [item_id];
-- Esperado: 800, 300, 500

SELECT col4 FROM presupuesto_items WHERE codigo_completo = '[code]' AND year = 2025;
-- Esperado: col4 anterior - 200
```

---

### Test 4: Editar Múltiples Items
**Objetivo**: Editar varios items a la vez

**Datos de Prueba**:
- Certificado con 3 items
- Item 1: $1,000
- Item 2: $2,000
- Item 3: $1,500
- Total: $4,500

**Cambios**:
- Item 1: $1,000 → $1,200 (sube $200)
- Item 2: $2,000 → $2,000 (sin cambio)
- Item 3: $1,500 → $1,300 (baja $200)

**Pasos**:
1. Abrir modal
2. Cambiar Item 1 y 3
3. Guardar

**Resultado Esperado**:
- ✅ Total en modal: $4,500 (1200 + 2000 + 1300)
- ✅ Item 1 actualizado: monto = 1200
- ✅ Item 2 sin cambios: monto = 2000
- ✅ Item 3 actualizado: monto = 1300
- ✅ certificados.monto_total = 4500
- ✅ Presupuesto se ajustó por diferencia neta (0)

---

### Test 5: Validación de Montos Negativos
**Objetivo**: Verificar que no se permitan montos negativos

**Pasos**:
1. Abrir modal
2. Intentar cambiar monto a -500
3. Hacer clic en "Guardar Cambios"

**Resultado Esperado**:
- ✅ Modal muestra error: "El monto no puede ser negativo"
- ✅ NO se envía el formulario
- ✅ Datos en BD NO se modifican

---

### Test 6: Montos Cero
**Objetivo**: Verificar comportamiento con monto cero

**Pasos**:
1. Abrir modal
2. Cambiar monto a 0
3. Guardar

**Resultado Esperado**:
- ✅ Monto se actualiza a 0
- ✅ Cantidad pendiente = 0
- ✅ Presupuesto se actualiza correctamente

---

### Test 7: Permisos (Operator No Puede Editar)
**Objetivo**: Verificar que solo admin puede editar

**Pasos**:
1. Loguear como operador (no admin)
2. Intentar editar un certificado

**Resultado Esperado**:
- ✅ Botón "Editar" NO aparece
- ✅ Si intenta acceder por URL, ve error "No autorizado"

---

### Test 8: Presupuesto Insuficiente
**Objetivo**: Verificar que pueda aumentar monto incluso sin presupuesto (sistema permite)

**Datos de Prueba**:
- Item con monto $1,000
- col3 (asignado): $1,200
- col4 (utilizado): $1,000
- Saldo: $200

**Pasos**:
1. Editar a $1,300 (superaría el presupuesto)
2. Guardar

**Resultado Esperado**:
- ✅ Monto se actualiza a $1,300
- ✅ col4 se actualiza a $1,300
- ✅ Saldo se vuelve NEGATIVO (-$100)
- ⚠️ Nota: Sistema permite, pero presupuesto queda en rojo

---

### Test 9: Múltiples Liquidaciones en Mismo Item
**Objetivo**: Verificar que cantidad_pendiente se calcula correctamente con múltiples liquidaciones

**Datos de Prueba**:
- Item: $1,000
- Liquidación 1: $200 (fecha: 2025-01-01)
- Liquidación 2: $300 (fecha: 2025-01-05)
- Cantidad liquidación acumulada: $500
- Cantidad pendiente: $500

**Cambio**:
- Monto: $1,000 → $700

**Resultado Esperado**:
- ✅ cantidad_liquidacion total se mantiene en $500
- ✅ cantidad_pendiente se ajusta a $200 (700 - 500)
- ✅ Ambas liquidaciones permanecen intactas

---

### Test 10: Recalculación de Total del Certificado
**Objetivo**: Verificar que monto_total del certificado se recalcula correctamente

**Datos de Prueba**:
- Item 1: $1,000
- Item 2: $2,000
- Item 3: $1,500
- Total: $4,500

**Cambios**:
- Item 2: $2,000 → $2,500

**Resultado Esperado**:
- ✅ certificados.monto_total = 5,000 (1000 + 2500 + 1500)
- ✅ SQL: SUM(monto) FROM detalle_certificados = 5000

---

## Pruebas de Edge Cases

### Test 11: Certificado sin Items (Raro pero posible)
- Abrir modal
- Ver mensaje "No hay items agregados"
- No poder editar

---

### Test 12: Item con Código Completo NULL
- Item con monto pero sin código_completo
- Editar monto
- ✅ Debe actualizar item pero NO presupuesto

---

### Test 13: Año Diferente
- Editar certificado de año 2024 estando en año 2025
- ✅ Debe editar correctamente con el año correcto

---

### Test 14: Valores Muy Grandes
- Editar monto a 999999999.99
- ✅ Debe funcionar sin overflow

---

### Test 15: Valores con Decimales
- Editar monto a 1234.567
- ✅ Debe redondear a 2 decimales
- ✅ Guardar como 1234.57

---

## Pruebas de Integración

### Test 16: Editar + Liquidación Posterior
**Objetivo**: Editar monto y luego crear liquidación

**Pasos**:
1. Crear certificado con Item $1,000
2. Editar a $800
3. Ir a "Liquidación" y liquidar $500
4. Verificar cantidad_pendiente = $300

**Resultado Esperado**:
- ✅ Liquidación funciona correctamente con nuevo monto
- ✅ cantidad_pendiente = 800 - 500 = 300

---

### Test 17: Editar + Reporte
**Objetivo**: Después de editar, generar reporte

**Pasos**:
1. Editar montos
2. Ir a "Exportar Reporte"
3. Descargar Excel

**Resultado Esperado**:
- ✅ Excel muestra montos editados
- ✅ Totales son correctos

---

### Test 18: Editar + Filtro por Año
**Objetivo**: Editar en 2025 y verificar que aparece en filtro

**Pasos**:
1. Editar certificado de 2025
2. Cambiar vista a 2024
3. Cambiar a 2025
4. Verificar cambios persisten

**Resultado Esperado**:
- ✅ Cambios se mantienen al filtrar
- ✅ Monto editado visible en 2025

---

## Pruebas de Performance

### Test 19: Editar Certificado Grande
- Certificado con 50+ items
- Editar 10 items simultáneamente
- Medir tiempo de respuesta

**Objetivo**:
- ✅ Respuesta < 2 segundos
- ✅ Sin errores de timeout

---

### Test 20: Base de Datos bajo Carga
- Editar 100 certificados en secuencia
- Verificar integridad de datos

**Objetivo**:
- ✅ Sin corrupción de datos
- ✅ Todos los cambios se aplican

---

## Checklist de Regresión

Después de editar, verificar que NO se rompió:

- [ ] Crear certificado (todavía funciona)
- [ ] Eliminar certificado (todavía funciona)
- [ ] Ver certificado (muestra datos correctos)
- [ ] Liquidación (se pueden crear liquidaciones)
- [ ] Reportes (muestran datos actuales)
- [ ] Presupuesto (col4 y saldo correctos)
- [ ] Búsqueda (encuentra certificados editados)
- [ ] Filtro por año (funciona correctamente)
- [ ] Exportar a Excel (datos correctos)
- [ ] Panel de control (estadísticas correctas)

---

## Comandos SQL para Verificación Rápida

```sql
-- Verificar integridad de montos
SELECT dc.id, dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
       (dc.monto - COALESCE(dc.cantidad_liquidacion, 0)) as expected_pendiente
FROM detalle_certificados dc
WHERE dc.certificado_id = [cert_id]
HAVING monto != expected_pendiente;  -- Debe estar vacío

-- Verificar totales
SELECT c.id, c.monto_total,
       (SELECT SUM(monto) FROM detalle_certificados WHERE certificado_id = c.id) as sum_items
FROM certificados c
WHERE c.id = [cert_id]
HAVING monto_total != sum_items;  -- Debe estar vacío

-- Verificar presupuesto
SELECT pi.codigo_completo, pi.col3, pi.col4, pi.saldo_disponible,
       (pi.col3 - pi.col4) as expected_saldo
FROM presupuesto_items pi
WHERE pi.year = 2025
HAVING saldo_disponible != expected_saldo;  -- Debe estar vacío
```

---

## Matriz de Resultados

| Test | Pasar | Notas |
|------|-------|-------|
| 1. Modal | ☐ | |
| 2. Monto sencillo | ☐ | |
| 3. Con liquidaciones | ☐ | |
| 4. Múltiples items | ☐ | |
| 5. Validación negativa | ☐ | |
| 6. Monto cero | ☐ | |
| 7. Permisos | ☐ | |
| 8. Presupuesto insuficiente | ☐ | |
| 9. Múltiples liquidaciones | ☐ | |
| 10. Total recalculado | ☐ | |
| 11-20. Edge cases | ☐ | |

