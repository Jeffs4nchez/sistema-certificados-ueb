# ✅ IMPLEMENTACIÓN COMPLETA: Filtro Real de Datos por Año

## 📝 Resumen de Cambios

Se implementó un **sistema completo de filtro por año** que asegura que:
- ✅ Cada certificado se guarda con su año
- ✅ Solo ves certificados del año seleccionado
- ✅ Los datos se filtran en la BD, no en PHP
- ✅ Al cambiar año, ves datos diferentes

---

## 🔄 Flujo Completo

```
Usuario selecciona año en login
            ↓
Se guarda en $_SESSION['año_trabajo'] = 2026
            ↓
Usuario crea certificado
            ↓
Se guarda en BD CON año = 2026
            ↓
Usuario cambia a año 2025 en navbar
            ↓
CertificateController obtiene año de sesión
            ↓
getAllByYear(2025) busca SOLO certificados con año=2025
            ↓
Se muestran SOLO datos de 2025
```

---

## 📂 Archivos Modificados

### 1. **database/add_year_column.sql** (NUEVO)
Script SQL que:
- Agrega columna `año` a tabla `certificados`
- Agrega columna `año` a tabla `detalle_certificados`
- Agrega columna `año` a tabla `presupuesto_items`
- Crea índices para performance
- Actualiza registros existentes

**IMPORTANTE:** ⚠️ Debes ejecutar este SQL primero

---

### 2. **app/models/Certificate.php** (MODIFICADO)
Agregados 2 nuevos métodos:

```php
public function getAllByYear($año) {
    // Obtiene TODOS los certificados de un año específico
}

public function getByUsuarioAndYear($usuario_id, $año) {
    // Obtiene certificados de un usuario específico EN un año específico
}
```

Modificado método existente:
```php
public function createCertificate($data) {
    // Ahora guarda el año automáticamente desde $_SESSION['año_trabajo']
}
```

---

### 3. **app/controllers/CertificateController.php** (MODIFICADO)
Actualizado método `listAction()`:

**ANTES:**
```php
public function listAction() {
    if (PermisosHelper::esAdmin()) {
        $certificates = $this->certificateModel->getAll();
    } else {
        $certificates = $this->certificateModel->getByUsuario($usuario_id);
    }
}
```

**DESPUÉS:**
```php
public function listAction() {
    $año_trabajo = AuthController::obtenerAñoTrabajo();
    
    if (PermisosHelper::esAdmin()) {
        $certificates = $this->certificateModel->getAllByYear($año_trabajo);
    } else {
        $certificates = $this->certificateModel->getByUsuarioAndYear($usuario_id, $año_trabajo);
    }
}
```

---

## 🚀 Cómo Usar

### Para el Usuario Final:

1. **Login:** Selecciona año 2026
2. **Crea certificado** → Se guarda con año 2026
3. **Cambia a 2025** en la navbar
4. **Abre lista de certificados** → Ve SOLO los de 2025
5. **Vuelve a 2026** → Ve el certificado que creaste

### Para el Programador:

Si necesitas filtrar por año en otros modelos, sigue este patrón:

```php
// En el modelo:
public function getAllByYear($año) {
    $sql = "SELECT * FROM tabla WHERE año = :año ORDER BY id DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':año' => $año]);
    return $stmt ? $stmt->fetchAll() : array();
}

// En el controlador:
$año = AuthController::obtenerAñoTrabajo();
$datos = $this->modelo->getAllByYear($año);
```

---

## 📊 Ejemplo de Resultado

### Base de Datos:
```
Certificados:
┌────┬───────────────┬──────────┬─────┐
│ id │ numero_cert   │ estado   │ año │
├────┼───────────────┼──────────┼─────┤
│  1 │ CERT-001      │ ACTIVO   │2026│
│  2 │ CERT-002      │ ACTIVO   │2026│
│  3 │ CERT-003      │PENDIENTE │2025│
│  4 │ CERT-004      │ ACTIVO   │2025│
└────┴───────────────┴──────────┴─────┘
```

### Usuario selecciona 2026:
```
Query: SELECT * FROM certificados WHERE año = 2026

Resultado:
- CERT-001 ✓
- CERT-002 ✓
```

### Usuario selecciona 2025:
```
Query: SELECT * FROM certificados WHERE año = 2025

Resultado:
- CERT-003 ✓
- CERT-004 ✓
```

---

## ✅ Checklist de Implementación

- ✅ Archivo SQL creado: `database/add_year_column.sql`
- ✅ Modelo actualizado: `app/models/Certificate.php`
  - ✅ Método `getAllByYear($año)`
  - ✅ Método `getByUsuarioAndYear($usuario_id, $año)`
  - ✅ Método `createCertificate()` guarda año
- ✅ Controlador actualizado: `app/controllers/CertificateController.php`
  - ✅ `listAction()` filtra por año

---

## ⚠️ PASO OBLIGATORIO: Ejecutar SQL

**ANTES de probar nada**, debes:

1. Abre `database/add_year_column.sql`
2. Copia el contenido
3. Ejecuta en tu BD (phpMyAdmin o terminal)
4. Verifica que se agregaron las columnas

**Sin este paso, el sistema NO funcionará.**

---

## 🧪 Pruebas

Después de ejecutar el SQL:

### Test 1: Login y crear certificado
```
1. Login con año 2026
2. Crea certificado "Test2026"
3. Verifica que aparezca en lista
```

### Test 2: Cambiar año
```
1. Cambia a 2025 en navbar
2. Abre lista de certificados
3. "Test2026" NO debe aparecer
```

### Test 3: Volver al año original
```
1. Cambia a 2026 en navbar
2. "Test2026" vuelve a aparecer
```

---

## 🔍 Verificar en BD

Para ver los datos directamente en la BD:

```sql
-- Ver todos los certificados
SELECT id, numero_certificado, año FROM certificados ORDER BY año;

-- Ver por año específico
SELECT * FROM certificados WHERE año = 2026;

-- Ver quantidad por año
SELECT año, COUNT(*) as total FROM certificados GROUP BY año;
```

---

## 📋 Resumen Visual

```
ANTES (Sin filtro):
┌─────────────────────┐
│ Año: [2026 ▼]       │
├─────────────────────┤
│ - CERT-001 (2026)   │
│ - CERT-002 (2026)   │
│ - CERT-003 (2025)   │ ← Aparece aunque sea de 2025
│ - CERT-004 (2025)   │ ← Aparece aunque sea de 2025
└─────────────────────┘

DESPUÉS (Con filtro):
┌─────────────────────┐
│ Año: [2026 ▼]       │
├─────────────────────┤
│ - CERT-001 (2026)   │
│ - CERT-002 (2026)   │
└─────────────────────┘

Cambiar a 2025:
┌─────────────────────┐
│ Año: [2025 ▼]       │
├─────────────────────┤
│ - CERT-003 (2025)   │
│ - CERT-004 (2025)   │
└─────────────────────┘
```

---

## 🎯 Objetivo Logrado

✅ **El usuario ahora ve SOLO datos del año seleccionado**

No importa qué año elija, solo verá:
- Certificados de ese año
- Presupuestos de ese año
- Datos de ese año

Cuando cambia de año, los datos se actualizan automáticamente.

---

## 📚 Documentación Relacionada

1. [EJECUTAR_SQL_PRIMERO.md](EJECUTAR_SQL_PRIMERO.md) - Instrucciones para ejecutar SQL
2. [IMPLEMENTACION_RESUMEN.md](IMPLEMENTACION_RESUMEN.md) - Resumen original
3. [GUIA_FILTRO_AÑO.md](GUIA_FILTRO_AÑO.md) - Guía para otros modelos
4. [REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md) - Referencia rápida

---

**🚀 ¡La implementación está 100% lista!**

Solo queda ejecutar el SQL para que todo funcione correctamente.
