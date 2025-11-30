# Sistema de Usuarios - Guía de Implementación

## 📋 Descripción General

Se ha creado un módulo completo de gestión de usuarios para el sistema de certificados. Este módulo permite administrar los usuarios que crean certificados con un sistema de autenticación y control de acceso.

## 🗄️ Estructura de la Base de Datos

### Tabla: `usuarios`

```sql
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo_institucional VARCHAR(255) NOT NULL UNIQUE,
    cargo VARCHAR(100) NOT NULL,
    tipo_usuario VARCHAR(50) NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Campos de la Tabla:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | SERIAL | Identificador único del usuario |
| `nombre` | VARCHAR(100) | Nombre del usuario |
| `apellidos` | VARCHAR(100) | Apellidos del usuario |
| `correo_institucional` | VARCHAR(255) UNIQUE | Correo institucional (único) |
| `cargo` | VARCHAR(100) | Cargo del usuario en la institución |
| `tipo_usuario` | VARCHAR(50) | Tipo de acceso (admin, supervisor, operador) |
| `contraseña` | VARCHAR(255) | Contraseña encriptada con BCRYPT |
| `estado` | VARCHAR(20) | Estado del usuario (activo/inactivo) |
| `fecha_creacion` | TIMESTAMP | Fecha de registro |
| `fecha_actualizacion` | TIMESTAMP | Fecha de última actualización |

### Relación con Certificados

Se agregó una nueva columna a la tabla `certificados`:

```sql
ALTER TABLE certificados 
ADD COLUMN usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL;
```

Esto permite registrar qué usuario creó cada certificado.

## 📁 Archivos Creados

### 1. Base de Datos
- **`database/crear_tabla_usuarios.sql`** - Script SQL para crear la tabla y establecer relaciones

### 2. Modelo
- **`app/models/Usuario.php`** - Clase Model con métodos CRUD y autenticación

### 3. Controlador
- **`app/controllers/UsuarioController.php`** - Controlador para gestionar operaciones de usuarios

### 4. Vistas
- **`app/views/usuarios/list.php`** - Listado de usuarios
- **`app/views/usuarios/form.php`** - Formulario para crear/editar usuario
- **`app/views/usuarios/view.php`** - Vista detallada de usuario con sus certificados

## 🔧 Métodos del Modelo Usuario

### Lectura
- `obtenerTodos()` - Obtiene todos los usuarios activos
- `obtenerPorId($id)` - Obtiene un usuario por ID
- `obtenerPorCorreo($correo)` - Obtiene un usuario por correo

### Creación y Actualización
- `crear()` - Crea un nuevo usuario (encripta contraseña con BCRYPT)
- `actualizar()` - Actualiza datos del usuario
- `cambiarContraseña($id, $nueva_contraseña)` - Cambia la contraseña encriptada

### Autenticación
- `autenticar($correo, $contraseña)` - Valida credenciales y devuelve usuario
- `verificarContraseña($contraseña, $hash_contraseña)` - Verifica contraseña

### Gestión
- `eliminar($id)` - Desactiva un usuario (soft delete)
- `obtenerCertificados($usuario_id)` - Lista certificados del usuario
- `obtenerCantidadCertificados($usuario_id)` - Cuenta certificados del usuario
- `getNombreCompleto()` - Devuelve nombre y apellidos juntos

## 🎯 Tipos de Usuario

El sistema soporta tres tipos de usuario (configurables):

1. **admin** - Acceso total al sistema
2. **supervisor** - Acceso a reportes y supervisión
3. **operador** - Acceso básico para crear certificados

## 🔐 Seguridad

- Las contraseñas se encriptan con **PHP's `password_hash()`** usando algoritmo BCRYPT
- Se valida que el correo sea único
- La verificación de contraseña usa `password_verify()`
- Los usuarios inactivos pueden reactivarse (no se eliminan permanentemente)

## 📝 Instrucciones de Implementación

### 1. Ejecutar el Script SQL

```bash
# Conectarse a PostgreSQL
psql -U postgres -d certificados_sistema -f database/crear_tabla_usuarios.sql
```

O a través de PHP:
```php
require_once 'app/Database.php';
$db = Database::getInstance()->getConnection();
$sql = file_get_contents('database/crear_tabla_usuarios.sql');
$db->exec($sql);
```

### 2. Incluir las Clases

Asegúrate de que en tu `bootstrap.php` o punto de entrada se carguen:

```php
require_once 'app/models/Usuario.php';
require_once 'app/controllers/UsuarioController.php';
```

### 3. Agregar Rutas (en tu index.php o router)

```php
if ($action === 'usuario') {
    $controller = new UsuarioController();
    $method = $_GET['method'] ?? 'listar';
    
    if (method_exists($controller, $method)) {
        $controller->$method();
    }
}
```

### 4. Crear un Usuario Inicial (Admin)

```php
$usuario = new Usuario();
$usuario->nombre = 'Admin';
$usuario->apellidos = 'Sistema';
$usuario->correo_institucional = 'admin@institucion.com';
$usuario->cargo = 'Administrador';
$usuario->tipo_usuario = 'admin';
$usuario->contraseña = 'password123'; // Se encriptará automáticamente
$usuario->crear();
```

## 🎨 URLs de Acceso

| Acción | URL |
|--------|-----|
| Listar usuarios | `?action=usuario&method=listar` |
| Crear usuario | `?action=usuario&method=crear_formulario` |
| Guardar usuario | `?action=usuario&method=guardar` (POST) |
| Editar usuario | `?action=usuario&method=editar_formulario&id=X` |
| Actualizar usuario | `?action=usuario&method=actualizar` (POST) |
| Ver detalles | `?action=usuario&method=ver&id=X` |
| Eliminar usuario | `?action=usuario&method=eliminar&id=X` |

## 🔄 Integración con Certificados

El módulo de certificados debe actualizarse para:

1. **Al crear certificado**: Registrar `usuario_id` del usuario autenticado
2. **En listados**: Mostrar quién creó cada certificado
3. **En reportes**: Filtrar por usuario creador

Ejemplo de integración:

```php
// Al crear certificado
$certificado = new Certificate();
$certificado->usuario_id = $_SESSION['usuario_id']; // Del usuario autenticado
$certificado->crear();

// En vistas
<?php echo htmlspecialchars($cert['usuario_creacion']); ?>
```

## 📊 Consultas SQL Útiles

```sql
-- Certificados creados por un usuario
SELECT * FROM certificados WHERE usuario_id = 1;

-- Contar certificados por usuario
SELECT u.nombre, COUNT(c.id) as total_certificados
FROM usuarios u
LEFT JOIN certificados c ON u.id = c.usuario_id
GROUP BY u.id, u.nombre;

-- Usuarios más activos
SELECT u.*, COUNT(c.id) as certificados
FROM usuarios u
LEFT JOIN certificados c ON u.id = c.usuario_id
WHERE u.estado = 'activo'
GROUP BY u.id
ORDER BY certificados DESC;
```

## ✅ Próximos Pasos

1. Ejecutar el script SQL para crear la tabla
2. Incluir los archivos en el sistema
3. Integrar con sistema de autenticación (login)
4. Actualizar módulo de certificados para registrar usuario_id
5. Crear vistas de dashboard personalizado por usuario
6. Implementar auditoría de cambios

---

**Nota**: Los archivos están listos para usar. Solo necesitas adaptar las rutas según tu estructura de directorios.
