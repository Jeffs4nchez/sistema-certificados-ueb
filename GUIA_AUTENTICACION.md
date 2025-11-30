# Sistema de Autenticación y Gestión de Usuarios - Guía de Implementación

## 🚀 Implementación Completada

Se ha creado un sistema completo de autenticación y gestión de usuarios. Sigue estos pasos:

## 📋 Paso 1: Configurar la Base de Datos y Crear Usuarios

Accede a la siguiente URL en tu navegador para ejecutar el setup:

```
http://localhost/programas/certificados-sistema/setup_usuarios.php
```

Este script:
- ✅ Verifica si la tabla `usuarios` existe
- ✅ La crea si no existe
- ✅ Inserta dos usuarios de prueba (si están vacíos)

**Usuarios creados:**
1. **Admin**
   - Correo: `admin@institucion.com`
   - Contraseña: `admin123`
   - Tipo: admin

2. **Encargado**
   - Correo: `encargado@institucion.com`
   - Contraseña: `encargado123`
   - Tipo: encargado

---

## 🔐 Paso 2: Acceder al Sistema

Después de ejecutar el setup, accede a:

```
http://localhost/programas/certificados-sistema/
```

Serás redirigido automáticamente a la página de login si no estás autenticado.

---

## 📁 Archivos Creados/Modificados

### Nuevos Archivos:

#### Controladores
- `app/controllers/AuthController.php` - Gestión de autenticación
- `app/controllers/UsuarioController.php` - CRUD de usuarios
- `app/controllers/PerfilController.php` - Perfil y cambio de contraseña

#### Modelos
- `app/models/Usuario.php` - Modelo de usuario

#### Vistas
- `app/views/auth/login.php` - Formulario de login
- `app/views/usuarios/list.php` - Listado de usuarios
- `app/views/usuarios/form.php` - Formulario crear/editar usuario
- `app/views/usuarios/view.php` - Ver detalle de usuario
- `app/views/perfil/ver.php` - Ver perfil del usuario
- `app/views/perfil/cambiar_contraseña.php` - Cambiar contraseña

#### Setup
- `setup_usuarios.php` - Script de configuración inicial

#### Base de Datos
- `database/crear_tabla_usuarios.sql` - Script para crear tabla

### Archivos Modificados:
- `index.php` - Agregadas rutas de autenticación
- `bootstrap.php` - Carga automática de modelos y controladores
- `app/views/layout/header.php` - Navbar con menú de usuario

---

## 🔄 Flujo de Autenticación

```
Usuario intenta acceder
        ↓
¿Está autenticado? → No → Redirige a login.php
        ↓ Si
Muestra el contenido solicitado
        ↓
Usuario hace logout → Destruye sesión → Redirige a login
```

---

## 🛠️ Funcionalidades Implementadas

### 1. **Login**
- ✅ Formulario con validación de email y contraseña
- ✅ Mensajes de error personalizados
- ✅ Credenciales de prueba visibles en la página
- ✅ Diseño responsive y moderno

### 2. **Sesiones**
- ✅ Sesiones PHP con datos del usuario
- ✅ Protección de rutas (requiere autenticación)
- ✅ Variables de sesión disponibles en toda la app

### 3. **Gestión de Usuarios**
- ✅ Listar usuarios activos
- ✅ Crear nuevos usuarios
- ✅ Editar usuarios
- ✅ Desactivar usuarios (soft delete)
- ✅ Ver detalle de usuario con certificados creados

### 4. **Perfil de Usuario**
- ✅ Ver información personal
- ✅ Cambiar contraseña
- ✅ Mostrar tipo y cargo

### 5. **Navbar Dinámico**
- ✅ Menú contextual según tipo de usuario
- ✅ Solo admin puede ver menú de usuarios
- ✅ Menú desplegable con opciones de usuario
- ✅ Botón de logout

---

## 👥 Permisos por Tipo de Usuario

### Admin
- ✅ Ver/Crear/Editar/Eliminar usuarios
- ✅ Ver presupuestos
- ✅ Ver certificados
- ✅ Acceso completo

### Encargado
- ✅ Ver su perfil
- ✅ Cambiar contraseña
- ✅ Ver/Crear certificados
- ❌ Ver usuarios
- ❌ Ver presupuestos

---

## 🔑 Variables de Sesión Disponibles

Después del login, tienes acceso a:

```php
$_SESSION['usuario_id']        // ID del usuario
$_SESSION['usuario_nombre']    // Nombre completo
$_SESSION['usuario_correo']    // Correo institucional
$_SESSION['usuario_tipo']      // Tipo (admin, encargado)
$_SESSION['usuario_cargo']     // Cargo
```

---

## 🔐 Seguridad Implementada

- ✅ Contraseñas encriptadas con BCRYPT
- ✅ Validación de datos en servidor
- ✅ Protección CSRF (con sesiones)
- ✅ SQL Injection prevention (prepared statements)
- ✅ XSS prevention (htmlspecialchars)
- ✅ Autenticación requerida para rutas protegidas
- ✅ Passwords nunca se muestran en el navegador

---

## 📝 SQL de la Tabla Usuarios

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

-- Relación con certificados
ALTER TABLE certificados 
ADD COLUMN usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL;
```

---

## ⚙️ Cómo Usar en el Código

### Verificar si usuario está autenticado:
```php
if (isset($_SESSION['usuario_id'])) {
    echo "Bienvenido " . $_SESSION['usuario_nombre'];
}
```

### Obtener datos del usuario:
```php
$usuario_actual = AuthController::obtenerUsuarioActual();
echo $usuario_actual['nombre'];
echo $usuario_actual['tipo'];
```

### Verificar permisos:
```php
if ($_SESSION['usuario_tipo'] === 'admin') {
    // Solo para admin
}
```

### Logout manual:
```php
header('Location: ?action=auth&method=logout');
```

---

## 🐛 Troubleshooting

### No puedo acceder al setup
- Verifica que XAMPP está corriendo
- Asegúrate de que PostgreSQL está activo
- Verifica la conexión en `app/Database.php`

### Error de sesión
- Limpia las cookies del navegador
- Intenta en una ventana de incógnito
- Reinicia XAMPP

### Usuario no se crea
- Verifica que el correo sea único
- Comprueba que la tabla existe ejecutando setup_usuarios.php
- Revisa los logs de PostgreSQL

---

## ✅ Checklist Final

Antes de usar en producción:

- [ ] Ejecutar `setup_usuarios.php`
- [ ] Verificar tabla `usuarios` creada
- [ ] Probar login con admin
- [ ] Probar login con encargado
- [ ] Probar cambio de contraseña
- [ ] Crear nuevo usuario
- [ ] Editar usuario
- [ ] Cambiar permisos según necesidad
- [ ] Actualizar contraseñas por defecto

---

**¡Sistema listo para usar!** 🎉
