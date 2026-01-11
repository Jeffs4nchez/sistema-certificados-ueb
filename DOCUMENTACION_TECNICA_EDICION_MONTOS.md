# 🔌 API y Documentación Técnica - Edición de Montos

## Endpoints HTTP

### 1. Obtener Certificado para Edición
```
GET /index.php?action=api-certificate&action-api=get-certificate-for-edit&id={cert_id}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "certificate": {
      "id": 1,
      "numero_certificado": "CERT-001",
      "institucion": "UEB",
      "monto_total": 3000.00,
      ...
    },
    "items": [
      {
        "id": 1,
        "programa_codigo": "PG01",
        "monto": 1000.00,
        "cantidad_liquidacion": 300.00,
        "cantidad_pendiente": 700.00,
        ...
      }
    ]
  }
}
```

### 2. Actualizar Certificado con Montos Editados
```
POST /index.php?action=certificate-update
Content-Type: application/x-www-form-urlencoded
```

**Parámetros:**
```
id: [certificado_id]
numero_certificado: [string]
institucion: [string]
seccion_memorando: [string]
descripcion_general: [string]
fecha_elaboracion: [date]
unid_ejecutora: [string]
unid_desc: [string]
clase_registro: [string]
clase_gasto: [string]
tipo_doc_respaldo: [string]
clase_doc_respaldo: [string]
items_editados: [JSON array]
```

**Formato de `items_editados`:**
```json
[
  {
    "id": 1,
    "monto_nuevo": 800.00,
    "monto_original": 1000.00
  },
  {
    "id": 2,
    "monto_nuevo": 2200.00,
    "monto_original": 2000.00
  }
]
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Certificado actualizado correctamente"
}
```

---

## Métodos de Modelo

### Certificate::updateItemMonto()

```php
public function updateItemMonto(
    $item_id,           // ID del detalle_certificados
    $monto_nuevo,       // Nuevo monto a asignar
    $certificado_id,    // ID del certificado maestro
    $year = null        // Año (por defecto el de sesión)
)
```

**Retorna:**
```php
[
    'success' => true/false,
    'monto_nuevo' => 800.00,
    'total_certificado' => 3000.00,
    'error' => 'mensaje de error (si aplica)'
]
```

**Operaciones que realiza:**
1. Obtiene el monto anterior del item
2. Actualiza `detalle_certificados.monto`
3. Recalcula `cantidad_pendiente` si hay liquidaciones
4. Actualiza `presupuesto_items` (col4 y saldo_disponible)
5. Actualiza `certificados.monto_total` y `total_pendiente`

**Ejemplo de uso:**
```php
$resultado = $certificateModel->updateItemMonto(
    $item_id = 5,
    $monto_nuevo = 850.00,
    $certificado_id = 12,
    $year = 2025
);

if ($resultado['success']) {
    echo "Actualizado a: " . $resultado['total_certificado'];
} else {
    echo "Error: " . $resultado['error'];
}
```

---

## Flujo de JavaScript (Frontend)

### 1. Abrir Modal
```javascript
openEditModal(certificateId)
```
- Llama API para obtener datos
- Carga formulario con datos actuales
- Abre modal Bootstrap

### 2. Cargar Items
```javascript
loadEditModalItems(items)
```
- Crea tabla con inputs editables
- Guarda items en `window.editableItems`
- Muestra total inicial

### 3. Actualizar Total en Tiempo Real
```javascript
updateEditTotal()
```
- Se ejecuta en cada cambio de monto
- Suma todos los inputs de monto
- Actualiza el total visible

### 4. Guardar Cambios
```javascript
saveEditCertificate()
```
- Recopila montos editados
- Compara con montos originales
- Envía solo items que cambiaron
- Recarga página al éxito

---

## Flujo de PHP (Backend)

### CertificateController::updateAction()

```php
1. Valida headers y content-type
2. Limpia output buffer para JSON
3. Verifica permisos de administrador
4. Obtiene certificado actual
5. Actualiza datos maestros (updateCertificate)
6. Procesa items_editados:
   - Decodifica JSON
   - Para cada item:
     - Llama Certificate::updateItemMonto()
     - Captura errores
7. Retorna JSON con resultado
```

### Certificate::updateItemMonto()

```php
1. Obtiene monto anterior de detalle_certificados
2. Calcula diferencia: monto_nuevo - monto_anterior
3. Actualiza detalle_certificados.monto
4. Recalcula cantidad_pendiente = monto_nuevo - liquidacion_existente
5. Si hay código_completo:
   - Obtiene valores actuales de presupuesto_items
   - Calcula nuevo col4 = col4 + diferencia
   - Calcula nuevo saldo = col3 - col4_nuevo
   - Actualiza presupuesto_items
6. Calcula suma de montos de todos los items del certificado
7. Actualiza certificados.monto_total
8. Actualiza certificados.total_pendiente = suma de cantidad_pendiente
9. Retorna resultado con success y valores calculados
```

---

## Casos de Uso y Ejemplos

### Caso 1: Aumentar Monto sin Liquidaciones

**Antes:**
```
Item 1: $1,000 (Sin liquidación, pendiente $1,000)
Presupuesto col4: $1,000
```

**Usuario edita a $1,200**

**Después:**
```
Item 1: $1,200 (Sin liquidación, pendiente $1,200)
Presupuesto col4: $1,200 (aumentó $200)
```

**SQL ejecutado:**
```sql
UPDATE detalle_certificados SET monto = 1200, cantidad_pendiente = 1200 WHERE id = 1;
UPDATE presupuesto_items SET col4 = 1200, saldo_disponible = saldo-200 WHERE codigo_completo = '...';
UPDATE certificados SET monto_total = 3200 WHERE id = 1;
```

### Caso 2: Disminuir Monto con Liquidación Existente

**Antes:**
```
Item 1: $1,000 (Liquidado $300, pendiente $700)
Presupuesto col4: $1,000
```

**Usuario edita a $800**

**Después:**
```
Item 1: $800 (Liquidado $300 - IGUAL, pendiente $500)
Presupuesto col4: $800 (disminuyó $200)
```

**Cálculos:**
- Monto nuevo: 800
- Liquidación existente: 300 (NO cambia)
- Cantidad pendiente: 800 - 300 = 500 ✓

**SQL ejecutado:**
```sql
UPDATE detalle_certificados SET monto = 800, cantidad_pendiente = 500 WHERE id = 1;
UPDATE presupuesto_items SET col4 = 800, saldo_disponible = saldo+200 WHERE codigo_completo = '...';
UPDATE certificados SET monto_total = 2800, total_pendiente = 1700 WHERE id = 1;
```

### Caso 3: Editar Múltiples Items

**Antes:**
```
Item 1: $1,000
Item 2: $2,000
Item 3: $1,500
TOTAL: $4,500
```

**Usuario edita:**
- Item 1: $1,000 → $1,200
- Item 2: $2,000 → NO CAMBIO
- Item 3: $1,500 → $1,300

**Después:**
```
Item 1: $1,200 ✓
Item 2: $2,000 (sin cambio)
Item 3: $1,300 ✓
TOTAL: $4,500
```

**Operaciones:**
- Solo se actualiza Item 1 y Item 3
- Item 2 se ignora (mismo valor)
- Presupuesto se ajusta por Item 1 (+$200) y Item 3 (-$200)

---

## Validaciones

### Frontend (JavaScript)
```javascript
// En saveEditCertificate()
- Validar ID del certificado ✓
- Validar que montos sean ≥ 0 ✓
- Comparar con montos originales ✓
- Solo enviar items modificados ✓
```

### Backend (PHP)
```php
// En CertificateController::updateAction()
- Validar headers JSON ✓
- Verificar permisos (admin) ✓
- Validar certificado existe ✓
- Capturar excepciones ✓

// En Certificate::updateItemMonto()
- Validar item existe ✓
- Validar año ✓
- Manejar casos sin código_completo ✓
```

---

## Manejo de Errores

### Errores Esperados

| Código | Error | Solución |
|--------|-------|----------|
| 403 | No autorizado | Solo admin puede editar |
| 404 | Certificado no encontrado | Verificar ID |
| 500 | Error al actualizar | Ver logs del servidor |
| - | Item no encontrado | Ver logs |

### Logs Disponibles

Se registran en el error log del servidor con prefijo:
```
=== UPDATE CERTIFICATE DEBUG ===
=== UPDATE ITEM MONTO ===
```

Ejemplo:
```
[2025-01-10 15:30:45] === UPDATE ITEM MONTO ===
[2025-01-10 15:30:45] Item ID: 5, Monto Nuevo: 850.00
[2025-01-10 15:30:45] Monto anterior: 1000.00, Diferencia: -150.00
[2025-01-10 15:30:45] ✓ Item actualizado: monto=850.00
[2025-01-10 15:30:45] ✓ Presupuesto actualizado: col4=850.00, saldo=...
```

---

## Performance

### Consideraciones

- **Queries por item**: 3-4 queries (obtener, actualizar detalle, presupuesto, certificado)
- **Total con 5 items**: ~15-20 queries
- **Sin índices**: Esperar degradación en certificados muy grandes

### Optimizaciones Recomendadas

```sql
-- Índices recomendados
CREATE INDEX idx_detalle_certificado_id ON detalle_certificados(certificado_id);
CREATE INDEX idx_presupuesto_codigo_year ON presupuesto_items(codigo_completo, year);
CREATE INDEX idx_certificados_id ON certificados(id);
```

---

## Compatibilidad

| Versión | Estado | Notas |
|---------|--------|-------|
| PHP 8.0+ | ✅ Soportado | Utiliza PDO |
| MySQL 5.7+ | ✅ Soportado | Cálculos SQL nativos |
| PostgreSQL 12+ | ✅ Soportado | Compatible |
| Bootstrap 5 | ✅ Requerido | Para modal |

