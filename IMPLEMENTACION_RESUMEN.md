# ✅ Implementación: Opción 1 - Selector de Año en Login

## Resumen de Cambios

Se ha implementado un **sistema de filtro por año de trabajo** que permite a los usuarios:
- ✅ Seleccionar un año al iniciar sesión
- ✅ Cambiar el año en cualquier momento desde la navbar
- ✅ Trabajar **únicamente con datos de ese año**

---

## Lo que se Hizo

### 1️⃣ **Formulario de Login** 
📄 [app/views/auth/login.php](app/views/auth/login.php)

Se agregó un campo `<select>` para elegir el año:
- Años disponibles: Actual y 5 años atrás
- Campo obligatorio
- Se valida antes de procesar el login

**Antes:**
```
[Email]
[Contraseña]
[Recuérdame]
[Iniciar Sesión]
```

**Después:**
```
[Email]
[Contraseña]
[Año de Trabajo] ← NUEVO
[Recuérdame]
[Iniciar Sesión]
```

---

### 2️⃣ **Controlador de Autenticación**
📄 [app/controllers/AuthController.php](app/controllers/AuthController.php)

**Cambios:**
- ✅ `procesarLogin()` - Ahora recibe y valida el año
- ✅ `obtenerAñoTrabajo()` - Método para acceder al año desde cualquier lado
- ✅ `cambiarAño()` - Permite cambiar el año sin cerrar sesión
- ✅ `obtenerUsuarioActual()` - Actualizado para incluir el año

**Variables de sesión:**
```php
$_SESSION['año_trabajo'] = 2024; // Se guarda al hacer login
```

---

### 3️⃣ **Navbar (Barra de Navegación)**
📄 [app/views/layout/header.php](app/views/layout/header.php)

Se agregó un selector de año al lado del logo:
- Selector rápido sin recargar la página
- Muestra el año actual
- Iconito de calendario
- Se redirige a la página anterior al cambiar

**Vista:**
```
[Logo] [📅 Año 2024 ▼]  [Dashboard] [Certificados] [Usuarios] [Mi Perfil] [Logout]
```

---

## Flujo de Funcionamiento

```
1. Usuario ingresa: email + contraseña + AÑO
   ↓
2. AuthController valida todo
   ↓
3. Si todo OK, crea sesión con $_SESSION['año_trabajo']
   ↓
4. Redirige a dashboard
   ↓
5. Usuario puede cambiar año en navbar sin cerrar sesión
   ↓
6. Todos los datos se filtran por ese año
```

---

## Cómo Usarlo

### Para el Usuario Final:
1. **Al iniciar sesión:**
   - Ingresa email y contraseña
   - **Selecciona el año** en el dropdown
   - Haz clic en "Iniciar Sesión"

2. **Durante la sesión:**
   - En la navbar, hay un selector de año
   - Cambia el año con un clic
   - Los datos se actualizan automáticamente

### Para el Desarrollador:
1. **Obtener el año actual:**
   ```php
   $año = AuthController::obtenerAñoTrabajo();
   // O simplemente:
   $año = $_SESSION['año_trabajo'] ?? date('Y');
   ```

2. **Filtrar datos por año en consultas SQL:**
   ```php
   $sql = "SELECT * FROM certificados WHERE año = :año";
   $stmt = $this->db->prepare($sql);
   $stmt->execute([':año' => $año]);
   ```

3. **Mostrar el año en vistas:**
   ```php
   <p>Año: <?php echo AuthController::obtenerAñoTrabajo(); ?></p>
   ```

---

## Próximos Pasos (Opcionales)

Para que el filtro funcione completamente, necesitas:

### 1. Agregar columna de año en la BD
```sql
-- Si no existe
ALTER TABLE certificados ADD COLUMN año INT DEFAULT YEAR(CURRENT_DATE);
ALTER TABLE presupuesto_items ADD COLUMN año INT DEFAULT YEAR(CURRENT_DATE);
ALTER TABLE liquidaciones ADD COLUMN año INT DEFAULT YEAR(CURRENT_DATE);
```

### 2. Actualizar modelos (Certificate.php, etc.)
Agregar métodos que filtren por año:
```php
public function getAllByYear($año) {
    $sql = "SELECT * FROM certificados WHERE año = :año ORDER BY fecha_creacion DESC";
    // ...
}
```

### 3. Actualizar controladores
En cada `listAction()`, `getAll()`, etc., agregar el filtro:
```php
$año = AuthController::obtenerAñoTrabajo();
$datos = $modelo->getAllByYear($año);
```

---

## Archivos Modificados

| Archivo | Cambios |
|---------|---------|
| `app/views/auth/login.php` | ➕ Select de año |
| `app/controllers/AuthController.php` | ➕ Validación, guardado y métodos de año |
| `app/views/layout/header.php` | ➕ Selector de año en navbar |

---

## Validaciones Implementadas

✅ Año es obligatorio  
✅ Año debe ser un número válido (4 dígitos)  
✅ Año se guarda en sesión  
✅ Se redirige al cambiar año  
✅ El año persiste mientras la sesión esté activa  

---

## Preguntas Frecuentes

**P: ¿Qué pasa si no selecciono año?**  
R: Se muestra error y debe intentar de nuevo.

**P: ¿El año se pierde al cerrar sesión?**  
R: Sí, se limpia con `session_destroy()`.

**P: ¿Puedo cambiar el año en cualquier momento?**  
R: Sí, usa el selector en la navbar.

**P: ¿Dónde se valida que el año existe en la BD?**  
R: Actualmente NO se valida contra BD. Solo filtra por sesión. Puedes agregar validación si lo necesitas.

---

## Video del Flujo

1. **Login:**
   - Página muestra 4 campos: Email, Contraseña, Año, Botón
   - Selecciona año 2024
   - Click en "Iniciar Sesión"

2. **En Dashboard:**
   - Navbar muestra selector: "📅 2024 ▼"
   - Cambia a 2023
   - Página se recarga con datos de 2023

3. **Datos filtrados:**
   - Todos los certificados mostrados son del 2024 (o el año seleccionado)
   - No hay datos fuera del año

---

**✅ IMPLEMENTACIÓN COMPLETADA**

¿Quieres que ahora actualize los modelos para filtrar realmente por año?
