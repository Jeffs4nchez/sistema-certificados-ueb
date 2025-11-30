# Sistema de Permisos por Rol

## 📋 Resumen

El sistema implementa control de acceso basado en dos roles: **Admin** y **Operador**.

---

## 👨‍💼 ADMIN - Acceso Completo

El administrador tiene acceso a **TODAS** las funciones del sistema:

### ✅ Certificados
- ✓ Ver todos los certificados
- ✓ Crear certificados
- ✓ Editar certificados
- ✓ Eliminar certificados
- ✓ Imprimir certificados
- ✓ Ver certificados de cualquier usuario

### ✅ Usuarios
- ✓ Listar usuarios
- ✓ Crear usuarios
- ✓ Editar usuarios
- ✓ Eliminar/desactivar usuarios
- ✓ Ver detalles de usuarios

### ✅ Presupuesto
- ✓ Ver presupuestos
- ✓ Importar presupuestos
- ✓ Crear liquidaciones
- ✓ Eliminar liquidaciones

### ✅ Funciones Generales
- ✓ Ver dashboard
- ✓ Ver parámetros
- ✓ Editar perfil propio
- ✓ Cambiar contraseña

---

## 👤 OPERADOR - Acceso Limitado

El operador tiene acceso restringido a funciones específicas:

### ✅ Certificados - SOLO PROPIOS
- ✓ Ver **SOLO SUS CERTIFICADOS** (creados por él)
- ✓ Crear certificados
- ✓ Imprimir sus certificados
- ✓ Ver detalles de sus certificados
- ✗ **NO PUEDE** editar certificados
- ✗ **NO PUEDE** eliminar certificados
- ✗ **NO PUEDE** ver certificados de otros usuarios

### ✗ Usuarios
- ✗ **NO TIENE ACCESO** a gestión de usuarios
- ✗ No puede ver lista de usuarios
- ✗ No puede crear usuarios
- ✗ No puede editar otros usuarios

### ✅ Presupuesto - Limitado
- ✓ Ver presupuestos
- ✓ Importar presupuestos
- ✓ Crear liquidaciones (presupuestos/liquidaciones)
- ✗ **NO PUEDE** eliminar liquidaciones

### ✅ Funciones Generales
- ✓ Ver dashboard propio
- ✓ Ver solo SU perfil
- ✓ Cambiar su propia contraseña
- ✗ No puede acceder a parámetros

---

## 🔐 Implementación Técnica

### Clase: `PermisosHelper`
**Ubicación:** `app/helpers/PermisosHelper.php`

**Métodos principales:**
```php
// Verificar rol
PermisosHelper::esAdmin()              // true si es admin
PermisosHelper::esOperador()           // true si es operador

// Obtener datos del usuario
PermisosHelper::getUsuarioIdActual()   // ID del usuario logueado
PermisosHelper::getTipoUsuarioActual() // 'admin' o 'operador'

// Verificar permisos específicos
PermisosHelper::puedeAcceder($accion)                  // ¿Puede acceder a esta acción?
PermisosHelper::puedeVerCertificado($usuario_id)       // ¿Puede ver este certificado?
PermisosHelper::puedeEditarCertificado($usuario_id)    // ¿Puede editar?
PermisosHelper::puedeEliminarCertificado()             // ¿Puede eliminar?
PermisosHelper::puedeGestionarUsuarios()               // ¿Puede gestionar usuarios?
PermisosHelper::puedeCrearLiquidacion()                // ¿Puede crear liquidación?
PermisosHelper::puedeEliminarLiquidacion()             // ¿Puede eliminar liquidación?

// Negar acceso
PermisosHelper::denegarAcceso($mensaje)   // Redirige con error
```

### Puntos de Control

#### 1. **UsuarioController**
- ✓ Verificación en: `listar()`, `crearFormulario()`, `guardar()`, `editarFormulario()`, `actualizar()`, `eliminar()`, `ver()`
- **Resultado:** Solo admin accede; operador es redirigido

#### 2. **CertificateController**
- ✓ `listAction()`: Admin ve todos, operador ve solo los suyos
- ✓ `editAction()`: Solo admin puede editar (operador denegado)
- ✓ `viewAction()`: Admin ve todos, operador ve solo los suyos
- ✓ `deleteAction()`: Solo admin puede eliminar

#### 3. **Navbar (header.php)**
- ✓ Menú "Usuarios" solo aparece para admin
- ✓ Las acciones permitidas se muestran según el rol

---

## 📊 Matriz de Acceso

| Acción | Admin | Operador |
|--------|-------|----------|
| Ver certificados | ✅ Todos | ✅ Solo suyos |
| Crear certificado | ✅ | ✅ |
| Editar certificado | ✅ | ❌ |
| Eliminar certificado | ✅ | ❌ |
| Imprimir certificado | ✅ Todos | ✅ Solo suyos |
| Gestionar usuarios | ✅ | ❌ |
| Ver presupuestos | ✅ | ✅ |
| Crear liquidación | ✅ | ✅ |
| Eliminar liquidación | ✅ | ❌ |
| Ver dashboard | ✅ | ✅ |
| Editar perfil | ✅ | ✅ (solo suyo) |
| Cambiar contraseña | ✅ | ✅ (solo suya) |

---

## 🔍 Flujo de Validación

### Ejemplo 1: Operador intenta editar un certificado
1. Usuario operador hace clic en "Editar" en un certificado
2. Llama a `CertificateController::editAction($id)`
3. Se ejecuta: `PermisosHelper::puedeEditarCertificado()`
4. Retorna `false` (operador NO puede editar)
5. Se ejecuta: `PermisosHelper::denegarAcceso()`
6. Se redirige a dashboard con mensaje de error

### Ejemplo 2: Operador intenta ver certificado de otro usuario
1. Usuario operador accede a URL: `?action=certificate-view&id=99`
2. Llama a `CertificateController::viewAction(99)`
3. Se obtiene el certificado (usuario_id = 5, pero operador es ID = 3)
4. Se ejecuta: `PermisosHelper::puedeVerCertificado(5)` con usuario actual = 3
5. Retorna `false` (solo admin o propietario)
6. Se redirige con error

### Ejemplo 3: Operador lista certificados
1. Usuario operador accede a: `?action=certificate-list`
2. Llama a `CertificateController::listAction()`
3. Se verifica: `PermisosHelper::esAdmin()` = false
4. Se ejecuta: `$this->certificateModel->getByUsuario($usuario_id)`
5. Solo retorna certificados donde `usuario_id = 3`
6. Se muestra lista filtrada

---

## 🚀 Uso en Código

### Verificar en Controller
```php
// Denegar acceso a operadores
if (!PermisosHelper::puedeGestionarUsuarios()) {
    PermisosHelper::denegarAcceso('No tienes permiso.');
}

// Filtrar datos
if (PermisosHelper::esAdmin()) {
    $data = $model->getAll();  // Ver todo
} else {
    $data = $model->getByUsuario(PermisosHelper::getUsuarioIdActual());
}
```

### Verificar en Vista
```php
<?php if (PermisosHelper::esAdmin()): ?>
    <a href="editar">Editar</a>  <!-- Solo admin ve esto -->
<?php endif; ?>
```

---

## 📝 Cambios Realizados

### Archivos Nuevos
- ✅ `app/helpers/PermisosHelper.php` - Sistema de permisos

### Archivos Modificados
- ✅ `bootstrap.php` - Cargar PermisosHelper
- ✅ `app/controllers/UsuarioController.php` - Restricción a admin
- ✅ `app/controllers/CertificateController.php` - Filtrado por rol
- ✅ `app/models/Certificate.php` - Nuevo método `getByUsuario()`

---

## ✅ Estado

Sistema de permisos completamente implementado y funcional:
- ✅ Admin tiene acceso a TODO
- ✅ Operador solo accede a certificados propios
- ✅ Operador NO puede editar/eliminar certificados
- ✅ Operador NO puede acceder a usuarios
- ✅ Filtros aplicados automáticamente
- ✅ Redirecciones con mensajes de error

¡Listo para usar! 🎉
