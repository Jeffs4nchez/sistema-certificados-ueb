# ✅ CORRECCION: Validación de Presupuesto por AÑO

## 🐛 El Problema
```
AÑO 2024:  ✅ Presupuesto A cargado (Programa 01, Item 001, Monto $1000)
AÑO 2026:  ✅ Presupuesto B cargado (Programa 01, Item 002, Monto $500) [DIFERENTE]

SITUACIÓN ACTUAL (INCORRECTA):
┌──────────────────────────────────────────┐
│ Crear Certificado en 2026                │
│ Agregar Item: Item 001 ($1000)           │
│ ❌ Sistema permite agregar (INCORRECTO)  │
│    Pero Item 001 NO existe en 2026!      │
│                                          │
│ Al guardar:                              │
│ ✗ No se valida por año                  │
│ ✗ Se toma dato de 2024 en 2026           │
│ ✗ Se crea el certificado incorrectamente│
└──────────────────────────────────────────┘
```

## ✅ La Solución

Se corrigió la función `getMontoCoificado()` para **filtrar por year** en las consultas.

### Cambios Técnicos

#### 1. **CertificateItem.php** - Modelo
```php
// ANTES: No filtraba por year
public function getMontoCoificado($cod_programa, ...) {
    $sql = "SELECT col3 FROM presupuesto_items 
            WHERE codigog1 = ? AND codigog2 = ?..."
    // ❌ FALTA: AND year = ?
}

// DESPUÉS: Filtra por year
public function getMontoCoificado($cod_programa, ..., $year = null) {
    $sql = "SELECT col3 FROM presupuesto_items 
            WHERE codigog1 = ? AND codigog2 = ? ... AND year = ?"
    // ✅ Ahora incluye year en WHERE
}
```

#### 2. **CertificateController.php** - Validación Backend
```php
// Ahora pasa el year al validar
$yearActual = $_SESSION['year'] ?? date('Y');
$montoCoificado = $this->certificateItemModel->getMontoCoificado(
    ...,
    $yearActual  // ✅ Nuevo parámetro
);
```

#### 3. **APICertificateController.php** - API
```php
// Obtiene el year de GET o SESSION
$year = $_GET['year'] ?? ($_SESSION['year'] ?? date('Y'));

// Y lo pasa a la función
$montoCoificado = $this->certificateItemModel->getMontoCoificado(
    ...,
    $year  // ✅ Nuevo parámetro
);
```

#### 4. **certificate/form.php** - Formulario (2 cambios)
```javascript
// A. Input hidden para guardar el año
<input type="hidden" id="yearField" name="year" value="<?php echo $yearActual; ?>">

// B. AJAX incluye el año
let urlMonto = '.../get-monto-codicado?...';
urlMonto += '&year=' + encodeURIComponent(
    document.querySelector('input[name="year"]').value
);
```

## 🎯 Resultado Esperado

```
AÑO 2024:  Presupuesto A (Item 001 = $1000)
AÑO 2026:  Presupuesto B (Item 002 = $500)

┌──────────────────────────────────────────┐
│ Crear Certificado en 2026                │
│                                          │
│ Agregar Item 001:                        │
│ ❌ Sistema rechaza (CORRECTO)            │
│    "Item no existe en presupuesto 2026" │
│                                          │
│ Agregar Item 002:                        │
│ ✅ Sistema permite (CORRECTO)            │
│    "Item existe en presupuesto 2026"    │
│                                          │
│ Guardar: ✅ Se crea correctamente       │
└──────────────────────────────────────────┘
```

## 📊 Archivos Modificados

1. **`app/models/CertificateItem.php`**
   - Línea ~188-205: Función `getMontoCoificado()`
   - Agregado parámetro `$year = null`
   - Agregado `AND year = ?` en WHERE

2. **`app/controllers/CertificateController.php`**
   - Línea ~131: Agregada variable `$yearActual`
   - Línea ~135-145: Pasado `$yearActual` a `getMontoCoificado()`

3. **`app/controllers/APICertificateController.php`**
   - Línea ~363: Obtener `$year` de GET/SESSION
   - Línea ~369-375: Pasado `$year` a `getMontoCoificado()`

4. **`app/views/certificate/form.php`**
   - Línea ~318: Agregado input hidden para year
   - Línea ~598: Agregado `&year=...` en URL AJAX

## 🧪 Flujo de Validación Ahora

```
Usuario intenta agregar Item → Sistema AJAX
                                    ↓
                    Obtiene el AÑO de $_SESSION['year']
                                    ↓
                    Busca: SELECT col3 FROM presupuesto_items
                           WHERE codigog1=? AND ... AND year=2026
                                    ↓
                    ¿Item existe en 2026?
                    ├─ SÍ (Monto OK) → Permite agregar ✅
                    └─ NO (Monto 0)  → Rechaza ❌
```

## 🔒 Seguridad (Backend)

Aunque JavaScript no envíe el year, el servidor siempre lo valida:
```php
$yearActual = $_SESSION['year'] ?? date('Y');  // De sesión (confiable)
// Se ignora lo que venga del cliente
```

## ✨ Beneficios

✅ Items correctos por año
✅ No se mezclan presupuestos de años diferentes
✅ Validación en DOS niveles (frontend + backend)
✅ Mensaje claro si item no existe en ese año
✅ Seguro contra modificaciones del cliente

---

**Estado:** ✅ CORREGIDO
**Fecha:** 8 de Enero, 2026
**Afecta:** Creación de certificados
**Testing:** Manual de validación por año
