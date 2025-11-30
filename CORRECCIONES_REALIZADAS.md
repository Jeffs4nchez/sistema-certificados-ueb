# Correcciones Realizadas

## 🐛 Problema 1: Error "Método no encontrado: crear_formulario"

### Causa
El código estaba llamando al método con snake_case `crear_formulario` pero el método real estaba en camelCase `crearFormulario`.

### Solución
Se cambiaron todas las referencias de `crear_formulario` a `crearFormulario` en:
- ✅ `app/controllers/UsuarioController.php` (4 ubicaciones - líneas 45, 53, 73, 78)
- ✅ `app/views/usuarios/list.php` (línea 21)
- ✅ `app/views/layout/header.php` (línea 86)

### Resultado
✅ El botón "Crear Usuario" ahora funciona correctamente

---

## 🐛 Problema 2: Error "SQLSTATE[HY093]: Invalid parameter number: :contraseña"

### Causa
PostgreSQL no acepta parámetros SQL con caracteres acentuados. El parámetro `:contraseña` contenía un acento.

### Solución
Se reemplazaron todos los parámetros `:contraseña` por `:pass` en:
- ✅ `app/models/Usuario.php` (método `crear()` - línea 65)
- ✅ `app/models/Usuario.php` (método `cambiarContraseña()` - línea 120)
- ✅ `setup_usuarios.php` (2 ubicaciones - líneas 65, 73)
- ✅ `setup_usuarios.php` (2 ubicaciones - líneas 93, 101)

### Cambios en detalle

#### Usuario.php - Método crear()
```php
// Antes:
$query = "INSERT INTO ... VALUES (:..., :contraseña)";
$stmt->bindParam(':contraseña', $contraseña_encriptada);

// Ahora:
$query = "INSERT INTO ... VALUES (:..., :pass)";
$stmt->bindParam(':pass', $contraseña_encriptada);
```

#### Usuario.php - Método cambiarContraseña()
```php
// Antes:
$query = "UPDATE ... SET contraseña = :contraseña ...";
$stmt->bindParam(':contraseña', $contraseña_encriptada);

// Ahora:
$query = "UPDATE ... SET contraseña = :pass ...";
$stmt->bindParam(':pass', $contraseña_encriptada);
```

### Resultado
✅ Ahora se pueden crear usuarios sin error de SQL

---

## ✅ Cambios Completados

| Archivo | Cambio | Tipo |
|---------|--------|------|
| UsuarioController.php | crear_formulario → crearFormulario (4x) | Naming |
| Usuario.php | :contraseña → :pass (2x) | SQL Parameter |
| list.php | crear_formulario → crearFormulario | URL |
| header.php | crear_formulario → crearFormulario | URL |
| setup_usuarios.php | :contraseña → :pass (4x) | SQL Parameter |

---

## 🧪 Para Probar

1. Inicia sesión como admin
2. Haz clic en "Usuarios" → "Nuevo Usuario"
3. Rellena el formulario:
   - Nombre: `Prueba`
   - Apellidos: `Usuario`
   - Correo: `prueba@institucion.com`
   - Cargo: `Operador`
   - Tipo: `operador`
   - Contraseña: `prueba123`
4. Haz clic en "Crear Usuario"
5. ✅ Deberías ver el mensaje de éxito

---

## 📝 Notas Técnicas

- **PostgreSQL y caracteres especiales**: PostgreSQL requiere que los parámetros nombrados en consultas preparadas usen solo caracteres ASCII (sin acentos ni caracteres especiales).
- **Compatibilidad camelCase**: Los métodos de PHP usan convención camelCase, pero las URLs pueden usar snake_case. Es importante mantener consistencia.
- **Password hashing**: Las contraseñas se almacenan siempre encriptadas con `password_hash(..., PASSWORD_BCRYPT)` para seguridad.

¡Listo! El sistema debe funcionar correctamente ahora. 🎉
