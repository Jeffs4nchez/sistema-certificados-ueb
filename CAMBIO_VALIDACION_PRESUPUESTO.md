# ✅ RESUMEN: Validación de Presupuesto para Certificados

## 🎯 El Problema
```
2024: ✅ Presupuestos cargados → Se creaban certificados ✅
2025: ❌ SIN presupuesto → Se creaban certificados IGUALMENTE ❌  ← PROBLEMA
```

Una persona creó un certificado en 2025 sin que hubiera presupuesto cargado.

## ✅ La Solución

### 🔒 Dos Capas de Validación

#### 1. FRONTEND (Interfaz Visual)
**Archivo:** `app/views/certificate/form.php`

```
┌─────────────────────────────────────────┐
│ ⚠️ Sin Presupuestos Cargados            │
│                                          │
│ No hay presupuestos para 2025           │
│ → [Ve a Presupuestos]                   │
└─────────────────────────────────────────┘

Formulario de certificado:
┌────────────────────────────┐
│ Institución: ____          │ (deshabilitado)
│ Descripción: ____          │ (deshabilitado)
│                            │
│ [❌ Guardar] [Cancelar]    │
└────────────────────────────┘
```

#### 2. BACKEND (Seguridad)
**Archivo:** `app/controllers/CertificateController.php`

```php
// Si alguien intenta enviar POST directamente:
SELECT COUNT(*) FROM presupuesto_items WHERE year = 2025
// Si resultado = 0:
throw Exception("❌ No se puede crear certificados sin presupuesto")
```

## 🚀 Cómo Funciona

### Crear Certificado Correctamente:

```
AÑO 2025 VACÍO (SIN PRESUPUESTO)
│
├─ [Certificados] Botón DESHABILITADO ❌
│
├─ [Presupuestos] > Cargar Presupuesto
│  └─ Subir CSV → Se cargan N registros
│
└─ [Certificados] Botón HABILITADO ✅ 
   └─ Se puede crear certificados
```

## 📊 Validaciones

| Año | Presupuestos | Crear Certificado | Razón |
|-----|--------------|-------------------|-------|
| 2024 | ✅ Sí (50) | ✅ HABILITADO | Hay 50 presupuestos |
| 2025 | ❌ No (0) | ❌ DESHABILITADO | Sin presupuestos |
| 2026 | ✅ Sí (30) | ✅ HABILITADO | Hay 30 presupuestos |

## 💾 Cambios Técnicos

### Archivo 1: `CertificateController.php`
```php
// Línea ~47-58
// VALIDACIÓN: Verificar que existan presupuestos para el año actual
$yearActual = $_SESSION['year'] ?? date('Y');
$db = Database::getInstance()->getConnection();
$stmtPresupuesto = $db->prepare(
    "SELECT COUNT(*) as total FROM presupuesto_items WHERE year = ?"
);
$stmtPresupuesto->execute([$yearActual]);
$resultPresupuesto = $stmtPresupuesto->fetch();

if ($resultPresupuesto['total'] == 0) {
    throw new Exception("❌ No se puede crear certificados sin presupuesto...");
}
```

### Archivo 2: `certificate/form.php`
```php
// Línea ~8-16
// Verificar si hay presupuestos cargados
$hayPresupuesto = /* verificar en BD */;

// Línea ~19-26
// Mostrar alerta si no hay presupuestos
<?php if (!$isEdit && !$hayPresupuesto): ?>
    <div class="alert alert-warning">
        ⚠️ No hay presupuestos para 2025
    </div>
<?php endif; ?>

// Línea ~65
// Desabilitar formulario
<form id="certificateForm" 
      <?php echo !$isEdit && !$hayPresupuesto ? 'disabled' : ''; ?>>

// Línea ~325
// Desabilitar botón submit
<button type="submit" id="submitBtn" 
        <?php echo !$isEdit && !$hayPresupuesto ? 'disabled' : ''; ?>>
```

## 🧪 Pruebas

### ✅ Test 1: Sin presupuesto
1. Cambiar a AÑO 2025 (vacío)
2. Ir a [Certificados] > [Crear]
3. **Resultado esperado:**
   - ✅ Alerta naranja visible
   - ✅ Formulario con opacity 0.6
   - ✅ Botón "Guardar" deshabilitado
   - ✅ Enlace a presupuestos visible

### ✅ Test 2: Con presupuesto
1. Ir a [Presupuestos] > [Cargar] > Subir CSV
2. Volver a [Certificados] > [Crear]
3. **Resultado esperado:**
   - ✅ Sin alerta
   - ✅ Formulario normal (opacity 1)
   - ✅ Botón "Guardar" habilitado
   - ✅ Se pueden agregar items

### ✅ Test 3: POST directo (hack intento)
```bash
# Intentar enviar POST sin presupuesto
curl -X POST http://localhost/... \
  -d "nombre=Test" \
  --cookie "year=2025"

# Resultado: Error 500 con mensaje:
# "❌ No se puede crear certificados sin presupuesto"
```

## 🎓 Documentación

- 📄 [VALIDACION_PRESUPUESTO.md](VALIDACION_PRESUPUESTO.md) - Documentación técnica
- 🔧 Cambios en: `CertificateController.php` + `certificate/form.php`

## ✨ Beneficios

✅ Evita certificados huérfanos (sin presupuesto)
✅ Protege integridad de datos
✅ UX clara (usuario sabe por qué no puede)
✅ Seguridad doble (frontend + backend)
✅ Por año (cada año independiente)

---

**Estado:** ✅ IMPLEMENTADO
**Fecha:** 8 de Enero, 2026
**Afecta:** Todos los usuarios
**Testing:** Manual + POST intents
