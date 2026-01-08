# ✅ VALIDACIÓN: No Crear Certificados sin Presupuesto

## El Problema
Se permitía crear certificados aunque no hubiera presupuestos cargados para ese año. Esto causaba:
- Certificados "huérfanos" sin presupuesto asociado
- Datos inconsistentes en el sistema
- Imposibilidad de validar montos

## La Solución

Se han implementado dos niveles de validación:

### 1. ✅ Validación Backend (CertificateController.php)
**Línea de activación:** Al iniciar POST en `createAction()`

```php
// VALIDACIÓN: Verificar que existan presupuestos para el año actual
$yearActual = $_SESSION['year'] ?? date('Y');
$db = Database::getInstance()->getConnection();
$stmtPresupuesto = $db->prepare("SELECT COUNT(*) as total FROM presupuesto_items WHERE year = ?");
$stmtPresupuesto->execute([$yearActual]);
$resultPresupuesto = $stmtPresupuesto->fetch();

if ($resultPresupuesto['total'] == 0) {
    throw new Exception("❌ No se puede crear certificados sin presupuesto...");
}
```

**¿Qué hace?**
- Verifica que existan presupuestos para el año actual en `presupuesto_items`
- Si no hay, lanza una excepción con mensaje claro
- Previene que se guarde el certificado

### 2. ✅ Validación Frontend (certificate/form.php)
**Vista:** Formulario de creación de certificados

**Cambios visuales:**
- ⚠️ **Alerta prominente** si no hay presupuestos
- 🚫 **Formulario deshabilitado** (pointer-events: none, opacity: 0.6)
- 🔒 **Botón "Guardar" deshabilitado**
- 📝 **Enlace directo** a "Presupuestos > Cargar Presupuesto"

## Flujo de Uso Correcto

```
1️⃣  Seleccionar año (ej: 2025)
         ↓
2️⃣  Ir a Presupuestos > Cargar Presupuesto
         ↓
3️⃣  Subir archivo CSV de presupuestos
         ↓
4️⃣  Verificar que se cargaron correctamente
         ↓
5️⃣  Ir a Certificados > Crear Certificado
         ↓
6️⃣  El formulario estará habilitado ✅
```

## Archivos Modificados

### 1. `app/controllers/CertificateController.php`
- Agregada validación de presupuestos al inicio del POST
- Se verifica `COUNT(*) FROM presupuesto_items WHERE year = ?`
- Lanza excepción si no hay presupuestos

### 2. `app/views/certificate/form.php`
- Agregada consulta a BD para verificar presupuestos
- Alerta visual con instrucciones
- Formulario deshabilitado si no hay presupuestos
- Botón submit deshabilitado

## Mensajes de Usuario

### 📋 Alerta en el formulario:
```
⚠️ Sin Presupuestos Cargados

No se puede crear certificados porque no hay presupuestos 
cargados para el año 2025.

Ve a Presupuestos y carga el archivo de presupuestos antes 
de crear certificados.
```

### ❌ Mensaje de error (si intenta hackear):
```
❌ No se puede crear certificados sin presupuesto.

Debes cargar el archivo de presupuestos para el año 2025 
antes de crear certificados.

Ve a: Presupuestos > Cargar Presupuesto
```

## Validación por Año

- Cada año funciona de forma independiente
- Cambiar de año automáticamente activa/desactiva la funcionalidad
- Si el año 2024 tiene presupuestos pero el 2025 no:
  - En 2024: Se pueden crear certificados ✅
  - En 2025: No se pueden crear certificados ❌

## Nota de Seguridad

La validación es **doble**:
1. **Frontend**: Para UX clara (desabilita formulario)
2. **Backend**: Para seguridad (rechaza petición POST directa)

Aunque alguien intente enviar un formulario deshabilitado, 
el servidor rechazará la petición.

---

**Próximas mejoras sugeridas:**
- Mostrar cantidad de presupuestos cargados
- Mostrar rango de fechas del presupuesto
- Permitir editar certificados antiguos aunque no haya presupuesto nuevo
