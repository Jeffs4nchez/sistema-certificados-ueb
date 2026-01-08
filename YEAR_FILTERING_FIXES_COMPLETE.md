# Resumen Completo de Correcciones de Filtrado por Año (Year-Based Isolation)

## 🎯 Objetivo Principal
Garantizar que cada año (2024, 2025, 2026, etc.) sea completamente independiente en el sistema, evitando que datos de un año afecten a presupuestos de otros años.

## 📋 Problemas Identificados y Solucionados

### 1. ❌ Problema: Columna `es_root` No Existe en Tabla `usuarios`
**Ubicación:** `app/controllers/AuthController.php`
**Error:** "no existe la columna «es_root»" al crear primer usuario admin
**Solución Implementada:**
- Creado archivo: `database/crear_tabla_usuarios.sql`
- Creado script de migración: `execute_esroot_migration.php`
- Actualizado: `bootstrap.php` (línea ~200) - INSERT con parámetro `es_root = 1`
- Actualizado: `setup_usuarios.php` - Ambos INSERT incluyen `es_root`
**Estado:** ✅ COMPLETADO

---

### 2. ❌ Problema: Crear Certificado sin Validar Presupuesto Cargado
**Ubicación:** `app/controllers/CertificateController.php` - createAction()
**Síntoma:** Se creaban certificados sin verificar que el presupuesto para ese año estuviera cargado
**Solución Implementada:**
```php
// En createAction(), antes de mostrar el formulario:
$stmtCheckPresupuesto = $db->prepare("
    SELECT COUNT(*) as cnt FROM presupuesto_items WHERE year = ?
");
$stmtCheckPresupuesto->execute([$yearActual]);
$resultPresupuesto = $stmtCheckPresupuesto->fetch();
if ($resultPresupuesto['cnt'] == 0) {
    $_SESSION['error'] = "No hay presupuesto cargado para el año $yearActual";
    header('Location: ?action=list');
    exit;
}
```
**Estado:** ✅ COMPLETADO

---

### 3. ❌ Problema: `getMontoCoificado()` No Filtraba por Año
**Ubicación:** `app/models/CertificateItem.php` - línea ~188
**Síntoma:** Al validar montos, se sumaban items de todos los años
**Solución Implementada:**
- Agregado parámetro `$year` a la función `getMontoCoificado()`
- Agregado `AND year = ?` en la consulta SELECT
- Actualizado todas las llamadas en controllers para pasar el año
**Cambios en:**
- `CertificateItem.php` - línea 188+
- `CertificateController.php` - todas las llamadas a `getMontoCoificado($item_id, $year)`
- `APICertificateController.php` - todas las llamadas a `getMontoCoificado($item_id, $year)`
**Estado:** ✅ COMPLETADO

---

### 4. ❌ Problema: Presupuesto Mostrando Totales de Todos los Años
**Ubicación:** `app/controllers/PresupuestoController.php`
**Síntoma:** Al exportar Excel/PDF, se incluían datos de presupuesto de años anteriores
**Solución Implementada:**
- `exportExcelAction()`: Ahora usa `$presupuestoModel->getByYear($year)` en lugar de `getAll()`
- `exportPdfAction()`: Ahora usa `getResumenByYear($year)` en lugar de `getResumen()`
- Asegurado que en `PresupuestoItem.php` todas las funciones filtren por `year`
**Estado:** ✅ COMPLETADO

---

### 5. ❌ Problema: Estadísticas de Operadores Incluían Todos los Años
**Ubicación:** `app/models/Certificate.php` - funciones de conteo
**Síntoma:** Dashboard mostraba estadísticas incorrectas (certificados de 2024 + 2025 + 2026 mezclados)
**Solución Implementada:**
- Función `countByOperador($operador_id, $year)`: Agregado filtro `AND year = ?`
- Función `countByOperadorAndStatus($operador_id, $status, $year)`: Agregado filtro `AND year = ?`
- Función `getTotalsByOperador($operador_id, $year)`: Agregado filtro `AND year = ?`
- Actualizado `DashboardController.php` para pasar `$year` en todas las llamadas
**Estado:** ✅ COMPLETADO

---

### 6. ❌ Problema: DELETE Afectaba Presupuesto de Años Anteriores (SOLUCIONADO PREVIAMENTE)
**Ubicación:** `app/models/Certificate.php` - funciones `updatePresupuestoRemoveCertificado()`
**Síntoma:** Al eliminar certificado de 2026, se afectaba presupuesto de 2024
**Solución Verificada:**
- `updatePresupuestoRemoveCertificado()` ya tiene `AND year = ?` en WHERE clause
- `deleteDetail()` pasa correctamente el `$year` a la función
- DELETE chain: `deleteAction()` → `deleteDetail()` → `updatePresupuestoRemoveCertificado()` ✅ Todas filtran por año
**Estado:** ✅ VERIFICADO COMPLETO

---

### 7. ❌ Problema: UPDATE en `updateLiquidacion()` Sin Filtro de Año (CRÍTICO) - AHORA SOLUCIONADO
**Ubicación:** `app/models/Certificate.php` - línea 643-660 (ahora línea 656)
**Síntoma:** Al liquidar certificado de 2026, actualizaba presupuesto_items de 2024
**Causa Raíz:** UPDATE statement NO tenía `AND year = ?` en WHERE clause
**Solución Implementada (NUEVA):**

**Paso 1:** Obtener el año del certificado (línea 540-542)
```php
$stmtYear = $this->db->prepare("SELECT year FROM certificados WHERE id = ?");
$stmtYear->execute([$certificado_id]);
$certData = $stmtYear->fetch();
$year = $certData ? (int)$certData['year'] : (int)$_SESSION['year'];
```

**Paso 2:** Filtrar por año en suma de pendientes (línea 608-614)
```php
$stmtSumaTotal = $this->db->prepare("
    SELECT COALESCE(SUM(cantidad_pendiente), 0) as suma_total_pendiente
    FROM detalle_certificados
    WHERE codigo_completo = ? AND certificado_id IN (
        SELECT id FROM certificados WHERE year = ?
    )
");
$stmtSumaTotal->execute([$codigo_completo, $year]);
```

**Paso 3:** Filtrar por año al obtener presupuesto (línea 616-620)
```php
$stmtPresupuesto = $this->db->prepare("
    SELECT col3, col4, saldo_disponible
    FROM presupuesto_items 
    WHERE codigo_completo = ? AND year = ?
");
$stmtPresupuesto->execute([$codigo_completo, $year]);
```

**Paso 4:** Filtrar por año en UPDATE (línea 656 y 662)
```php
// UPDATE statement WITH YEAR FILTER
WHERE codigo_completo = ? AND year = ?

// Execute with year parameter
$resultado = $updatePresupuesto->execute([
    $col4_nuevo,
    $saldo_nuevo,
    $codigo_completo,
    $year  // ← NUEVO PARÁMETRO
]);
```
**Estado:** ✅ COMPLETADO

---

## 📊 Cambios por Archivo

### `app/models/Certificate.php`
- ✅ Línea 540-542: Obtener year del certificado
- ✅ Línea 608-614: Filtrar suma de pendientes por año
- ✅ Línea 616-620: Filtrar SELECT presupuesto por año
- ✅ Línea 656: UPDATE con filtro `AND year = ?`
- ✅ Línea 662: Pasar `$year` en execute()

### `app/models/CertificateItem.php`
- ✅ Línea 188+: Agregar parámetro `$year` a `getMontoCoificado()`

### `app/controllers/CertificateController.php`
- ✅ Presupuesto validation en createAction()
- ✅ Pasar `$year` a todas las llamadas `getMontoCoificado()`
- ✅ Pasar `$year` a funciones de operador (countByOperador, etc.)

### `app/controllers/APICertificateController.php`
- ✅ Pasar `$year` a `getMontoCoificado()`

### `app/controllers/PresupuestoController.php`
- ✅ exportExcelAction(): usar `getByYear($year)`
- ✅ exportPdfAction(): usar `getResumenByYear($year)`

### `app/controllers/DashboardController.php`
- ✅ Pasar `$year` a `countByOperador()`
- ✅ Pasar `$year` a `countByOperadorAndStatus()`
- ✅ Pasar `$year` a `getTotalsByOperador()`

### `app/models/PresupuestoItem.php`
- ✅ Verificado: todas las funciones filtran por year

### `app/views/certificate/form.php`
- ✅ Presupuesto check alert
- ✅ Input hidden con year: `<input type="hidden" name="year" value="..."`
- ✅ AJAX con parámetro `&year=`

### `bootstrap.php` y `setup_usuarios.php`
- ✅ INSERT incluye `es_root`

---

## 🔍 Matriz de Verificación - Filtrado por Año

| Operación | Archivo | Línea | Filtro Año | Estado |
|-----------|---------|-------|-----------|--------|
| CREATE certificado | CertificateController.php | ~150 | ✅ Valida presupuesto de año | ✅ |
| Validar monto | CertificateItem.php | 188 | ✅ Filtra por año | ✅ |
| DELETE certificado | Certificate.php | 429-475 | ✅ updatePresupuestoRemoveCertificado filtra | ✅ |
| UPDATE liquidacion | Certificate.php | 656 | ✅ `WHERE ... AND year = ?` | ✅ NUEVO |
| Suma pendientes | Certificate.php | 608 | ✅ `certificado_id IN (SELECT WHERE year=?)` | ✅ NUEVO |
| Get presupuesto | Certificate.php | 616 | ✅ `WHERE ... AND year = ?` | ✅ NUEVO |
| Export Excel | PresupuestoController.php | ~200 | ✅ `getByYear($year)` | ✅ |
| Export PDF | PresupuestoController.php | ~250 | ✅ `getResumenByYear($year)` | ✅ |
| Count operador | Certificate.php | ~350 | ✅ `AND year = ?` | ✅ |
| Totals operador | Certificate.php | ~400 | ✅ `AND year = ?` | ✅ |

---

## 🧪 Pruebas Recomendadas

### Prueba 1: Liquidación en Diferentes Años
1. Cargar presupuesto para 2024 y 2026
2. Crear certificado en 2024 con item A, monto $1000
3. Crear certificado en 2026 con item A, monto $2000
4. Liquidar certificado 2024: $500
5. Verificar: Presupuesto 2024 item A col4=$500, presupuesto 2026 item A col4=$0
6. Liquidar certificado 2026: $1500
7. Verificar: Presupuesto 2024 sigue col4=$500, presupuesto 2026 col4=$1500

### Prueba 2: Eliminar Certificado
1. Crear certificado 2024 item B, monto $1000, liquidar $800
2. Crear certificado 2026 item B, monto $3000, liquidar $2000
3. Eliminar certificado 2024
4. Verificar: Presupuesto 2024 item B col4=$0, presupuesto 2026 item B col4=$2000

### Prueba 3: Dashboard Stats
1. Crear 3 certificados en 2024
2. Crear 2 certificados en 2026
3. Ver dashboard 2026
4. Verificar: Mostrar solo 2 certificados (no los 3 de 2024)

### Prueba 4: Exports
1. Cargar presupuestos para 2024 y 2026
2. Cambiar a año 2026
3. Exportar a Excel/PDF
4. Verificar: Solo datos de 2026 aparecen

---

## ✅ Status Final

**TODAS LAS CORRECCIONES COMPLETADAS**

El sistema ahora garantiza aislamiento completo por año:
- ✅ Presupuestos no se mezclan entre años
- ✅ Certificados solo se crean si presupuesto existe para ese año
- ✅ Liquidaciones solo afectan presupuesto del mismo año
- ✅ Eliminaciones solo afectan presupuesto del mismo año
- ✅ Estadísticas muestran solo datos del año actual
- ✅ Exports contienen solo datos del año actual
- ✅ Validaciones incluyen contexto de año

**Última corrección:** 
- Actualizado `updateLiquidacion()` en `Certificate.php` para filtrar por año en UPDATE presupuesto_items
- Línea 540-542: Obtener año del certificado
- Línea 608-614: Suma de pendientes filtra por año
- Línea 616-620: SELECT presupuesto filtra por año
- Línea 656: UPDATE con `AND year = ?`
- Línea 662: Pasar year en execute()

---

**Fecha:** 2024
**Sistema:** Sistema de Gestión de Certificados
**Versión:** Aislamiento Completo por Año
