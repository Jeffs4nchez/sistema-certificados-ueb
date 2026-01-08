# ⚡ REFERENCIA RÁPIDA: Sistema de Año de Trabajo

## 🎯 ¿Qué se implementó?

Un sistema que permite a los usuarios:
1. ✅ Seleccionar un año al iniciar sesión
2. ✅ Cambiar el año desde la navbar
3. ✅ Trabajar únicamente con datos de ese año

---

## 📍 Dónde está cada cosa

| Componente | Ubicación | Línea | Cambio |
|------------|-----------|-------|--------|
| **Select en login** | `app/views/auth/login.php` | ~374 | Agregar campo |
| **Validación año** | `app/controllers/AuthController.php` | ~30-60 | `procesarLogin()` |
| **Guardado en sesión** | `app/controllers/AuthController.php` | ~59 | `$_SESSION['año_trabajo']` |
| **Selector en navbar** | `app/views/layout/header.php` | ~45-63 | Nuevo formulario |
| **Método obtener año** | `app/controllers/AuthController.php` | ~111 | `obtenerAñoTrabajo()` |
| **Cambiar año** | `app/controllers/AuthController.php` | ~119-133 | `cambiarAño()` |

---

## 💾 Variables de Sesión

```php
// El año se guarda aquí:
$_SESSION['año_trabajo'] = '2026';

// Acceder desde cualquier lado:
echo $_SESSION['año_trabajo'];  // Imprime: 2026

// O usar el método:
echo AuthController::obtenerAñoTrabajo();  // Imprime: 2026
```

---

## 🔗 URLs de Referencia

```
Login:                   index.php o ?action=auth&method=login
Dashboard:               ?action=dashboard
Cambiar año:             ?action=auth&method=cambiarAño
Logout:                  ?action=auth&method=logout
```

---

## 💻 Código para usar el año

### En Controladores:
```php
class MiControlador {
    public function listAction() {
        $año = AuthController::obtenerAñoTrabajo();
        // Usar $año en queries
    }
}
```

### En Vistas:
```php
<p>Trabajando en el año: <?php echo AuthController::obtenerAñoTrabajo(); ?></p>
```

### En Modelos:
```php
public function getAllByYear($año) {
    $sql = "SELECT * FROM tabla WHERE año = :año";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':año' => $año]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```

---

## 🔒 Validaciones

```php
✅ Campo obligatorio en login
✅ Formato: debe ser 4 dígitos (regex: /^\d{4}$/)
✅ No se valida contra BD (solo se guarda en sesión)
✅ Se limpia al hacer logout
```

---

## 🎨 Interfaz

```
LOGIN:                      NAVBAR:
┌──────────────┐           ┌────────────────────────────┐
│ Email        │           │ 🎓 Logo │ 📅 2026▼ │ Menú │
│ Contraseña   │           └────────────────────────────┘
│ Año: [▼]     │ ← NUEVO          ↑
│ [Iniciar]    │          NUEVO: Selector de año
└──────────────┘
```

---

## 🚀 Próximos Pasos (Recomendados)

### 1. Agregar columna en BD:
```sql
ALTER TABLE certificados ADD COLUMN año INT DEFAULT YEAR(CURRENT_DATE);
ALTER TABLE presupuesto_items ADD COLUMN año INT DEFAULT YEAR(CURRENT_DATE);
ALTER TABLE liquidaciones ADD COLUMN año INT DEFAULT YEAR(CURRENT_DATE);
```

### 2. Actualizar modelos:
```php
// En Certificate.php, agregar:
public function getAllByYear($año) {
    $sql = "SELECT * FROM certificados WHERE año = :año";
    // ...
}
```

### 3. Actualizar controladores:
```php
// En CertificateController.php:
$año = AuthController::obtenerAñoTrabajo();
$certificates = $this->model->getAllByYear($año);
```

---

## 📊 Flujo de Datos

```
Usuario ingresa año en login
    ↓
AuthController::procesarLogin() valida
    ↓
Guarda en $_SESSION['año_trabajo']
    ↓
Redirige a dashboard
    ↓
Navbar muestra el año
    ↓
Usuario puede cambiar desde navbar
    ↓
AuthController::cambiarAño() actualiza sesión
    ↓
Todos los datos se filtran por ese año
```

---

## 🔍 Debugging

### Ver qué año tiene la sesión:
```php
<?php var_dump($_SESSION['año_trabajo']); ?>
```

### Ver todos los datos de sesión:
```php
<?php var_dump($_SESSION); ?>
```

### En la consola del navegador (DevTools):
- F12 → Network
- Haz clic en cambiar año
- Verifica que envíe POST a `?action=auth&method=cambiarAño`

---

## ⚠️ Cosas Importantes

❗ El año **NO se valida** contra la base de datos
- Solo se guarda en sesión
- Puedes seleccionar años ficticios (2099, 1900, etc.)
- Si quieres validar, agrega lógica en `procesarLogin()`

❗ El año **NO filtra automáticamente** los datos
- Debes actualizar los modelos manualmente
- Ver GUIA_FILTRO_AÑO.md para más info

❗ El año se **limpia al logout**
- `session_destroy()` borra todo
- Incluido `$_SESSION['año_trabajo']`

---

## 📚 Documentación Relacionada

- [IMPLEMENTACION_RESUMEN.md](IMPLEMENTACION_RESUMEN.md) - Resumen de cambios
- [GUIA_FILTRO_AÑO.md](GUIA_FILTRO_AÑO.md) - Cómo filtrar datos por año
- [VISUAL_IMPLEMENTACION.md](VISUAL_IMPLEMENTACION.md) - Visualización de UI
- [PRUEBAS_SISTEMA.md](PRUEBAS_SISTEMA.md) - Cómo probar el sistema

---

## ✅ Checklist Final

- ✅ Select de año en login
- ✅ Validación de año
- ✅ Guardado en sesión
- ✅ Selector en navbar
- ✅ Método para obtener año
- ✅ Documentación completa

**Todo está listo para usar. ¡A filtrar datos!**
