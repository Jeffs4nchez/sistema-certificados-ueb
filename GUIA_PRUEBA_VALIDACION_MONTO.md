# 🧪 Guía de Prueba - Validación de Monto Codificado

## Prerequisitos

1. Sistema de certificados funcionando
2. Al menos un presupuesto importado con items en `presupuesto_items`
3. Los códigos deben coincidir entre lo que importaste y lo que ingresarás

---

## 📝 Pasos de Prueba

### Preparar datos de prueba

Asume que tienes un item presupuestario con:
- **Programa:** `01`
- **Actividad:** `01`
- **Fuente:** `10`
- **Ubicación:** `01`
- **Item:** `01`
- **Monto Codificado (col3):** `$10,000.00`

---

## ✅ Caso 1: Monto IGUAL al Codificado (DEBE PERMITIR)

### Pasos:
1. Ve a **Certificados → Crear Certificado**
2. Completa datos básicos
3. Selecciona:
   - Programa: `01`
   - Subprograma: (según tu datos)
   - Proyecto: (según tu datos)
   - Actividad: `01`
   - Fuente: `10`
   - Ubicación: `01`
   - Item: `01`
4. En **Monto**, ingresa: `10000`
5. Haz clic en **Agregar**

### Resultado Esperado:
✅ Item se agrega a la tabla  
✅ No muestra alerta de error  
✅ Console muestra: "✓ Monto igual al codificado"

---

## ✅ Caso 2: Monto MENOR al Codificado (DEBE PERMITIR)

### Pasos:
1. Repite los mismos pasos del Caso 1
2. En **Monto**, ingresa: `8500`
3. Haz clic en **Agregar**

### Resultado Esperado:
✅ Item se agrega a la tabla  
✅ No muestra alerta de error  
✅ Console muestra: "✓ Monto menor al codificado"

---

## ❌ Caso 3: Monto MAYOR al Codificado (DEBE BLOQUEAR)

### Pasos:
1. Repite los mismos pasos del Caso 1
2. En **Monto**, ingresa: `12000`
3. Haz clic en **Agregar**

### Resultado Esperado:
❌ Alerta en pantalla:
```
❌ ERROR: El monto ingresado ($12,000.00) EXCEDE el monto codificado ($10,000.00)

No se puede agregar este item.
```
❌ Item NO se agrega a la tabla  
❌ Console muestra: "Monto invalido: 12000 Codificado: 10000"

---

## 🎯 Caso 4: Validación al Guardar (Backend)

### Pasos para burlar la validación frontend:
1. Abre Console del navegador (F12)
2. Ejecuta:
```javascript
// Agregar un item manualmente (simulando que pasó la validación)
items.push({
  programa_codigo: '01',
  subprograma_codigo: '01',
  proyecto_codigo: '01',
  actividad_codigo: '01',
  fuente_codigo: '10',
  ubicacion_codigo: '01',
  item_codigo: '01',
  monto: 15000  // Mayor al codificado (10000)
});
renderItems();
updateTotal();
```
3. Haz clic en **Guardar Certificado**

### Resultado Esperado:
❌ Error en pantalla:
```
❌ No se puede crear el certificado:
Item #1: Monto ingresado ($15,000.00) excede el monto codificado ($10,000.00)
```
❌ El certificado NO se crea  
✅ La validación backend lo bloqueó

---

## 🔍 Debugging - Verificar que todo funciona

### 1. Verificar datos en base de datos
```sql
-- Ver items presupuestarios
SELECT id, codigog1, codigog2, codigog3, codigog4, codigog5, col3 
FROM presupuesto_items 
LIMIT 10;

-- Buscar un item específico
SELECT * FROM presupuesto_items 
WHERE codigog1='01' AND codigog2='01' AND codigog3='10' 
AND codigog4='01' AND codigog5='01';
```

### 2. Verificar que el API funciona

Abre en navegador:
```
http://localhost/certificados-sistema/?action=api-certificate&action-api=get-monto-codicado&cod_programa=01&cod_subprograma=01&cod_proyecto=01&cod_actividad=01&cod_fuente=10&cod_ubicacion=01&cod_item=01
```

Debe devolver:
```json
{
  "success": true,
  "data": {
    "monto_codificado": 10000,
    "formateado": "10,000.00"
  }
}
```

### 3. Ver logs del servidor
```
# Busca en logs:
Item 1: Monto=12000, Codificado=10000
❌ No se puede crear el certificado...
```

---

## 📊 Tabla de Pruebas Rápida

| # | Acción | Monto Ingresado | Monto Codificado | Frontend | Backend | Resultado |
|---|--------|-----------------|------------------|----------|---------|-----------|
| 1 | Agregar | $10,000 | $10,000 | ✅ OK | ✅ OK | ✅ Creado |
| 2 | Agregar | $8,500 | $10,000 | ✅ OK | ✅ OK | ✅ Creado |
| 3 | Agregar | $12,000 | $10,000 | ❌ Bloq | ✅ Bloq | ❌ No agreg |
| 4 | Guardar | $15,000 | $10,000 | N/A | ❌ Bloq | ❌ Error |

---

## 💡 Notas Importantes

1. **Los códigos deben coincidir exactamente**
   - Los códigos de tu presupuesto importado deben existir en la estructura
   - Si no existen, retorna monto codificado = 0

2. **El monto se valida como número float**
   - `10,000.50` = válido
   - `10000` = válido
   - `abc` = invalido (no pasa la primera validación)

3. **Frontend vs Backend**
   - Frontend: Validación de UX (alerta amigable)
   - Backend: Validación de seguridad (no permite guardar)

4. **Si el API falla, continúa sin validación**
   - Esto es intencional para degradación elegante
   - Ver console para diagnosticar

---

## 🐛 Troubleshooting

| Problema | Solución |
|----------|----------|
| Alerta no aparece | Verificar console (F12) para errores JavaScript |
| API devuelve error | Verificar que los códigos existan en presupuesto_items |
| Certificado se crea igual | Verificar logs del servidor para ver si backend lo bloqueó |
| Monto codificado = 0 | Los códigos no existen en presupuesto_items |

---

## 📞 Contacto

Para reportar problemas, proporcionar:
- Screenshot de la alerta
- Valores de códigos utilizados
- Output de console (F12)
- Logs del servidor

