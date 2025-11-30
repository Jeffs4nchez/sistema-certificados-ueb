# Filtro de Búsqueda en Usuarios

## ✨ Nueva Funcionalidad

Se agregó un filtro avanzado en la lista de usuarios que permite buscar y filtrar por múltiples criterios.

## 🔍 Filtros Disponibles

### 1. **Búsqueda por nombre o correo**
- Busca en campos: Nombre, Apellidos, Correo Institucional
- Búsqueda parcial (ILIKE en PostgreSQL)
- Ejemplo: "Juan" encontrará "Juan Pérez", "adminJuan", etc.

### 2. **Filtro por Cargo**
- Búsqueda parcial en el campo cargo
- Ejemplo: "Admin" encontrará "Administrador del Sistema"

### 3. **Filtro por Tipo Usuario**
- Dropdown con opciones: Admin, Supervisor, Operador
- Búsqueda exacta (igualdad)

### 4. **Filtro por Estado**
- Dropdown con opciones: Activo, Inactivo
- Por defecto muestra solo activos si no se especifica

## 📝 Implementación Técnica

### Base de Datos
Se utiliza `ILIKE` (case-insensitive) para búsquedas parciales en PostgreSQL.

### Modelo - Método nuevo: `obtenerConFiltros($filtros)`
```php
$filtros = [
    'buscar' => 'Juan',        // Opcional
    'cargo' => 'Administrador', // Opcional
    'tipo' => 'admin',          // Opcional (valor exacto)
    'estado' => 'activo'        // Opcional (valor exacto)
];

$usuarios = $usuario->obtenerConFiltros($filtros);
```

### Controlador - Actualizado: `listar()`
- Recopila parámetros GET
- Pasa los filtros al modelo
- Mantiene compatibilidad hacia atrás (sin filtros = lista completa)

### Vista - Actualizado: `list.php`
- Formulario de filtros con campos de entrada
- Botón "Filtrar" para aplicar
- Botón "Limpiar" para resetear filtros
- Los valores se conservan en los campos (persistencia)

## 🎯 Casos de Uso

### Caso 1: Buscar un usuario específico
1. Ingresa "Juan" en "Buscar por nombre o correo"
2. Hace clic en "Filtrar"
3. Ve solo a Juan Pérez Admin

### Caso 2: Ver todos los supervisores
1. Selecciona "Supervisor" en "Tipo Usuario"
2. Hace clic en "Filtrar"
3. Ve solo supervisores

### Caso 3: Encontrar operadores inactivos
1. Selecciona "Operador" en "Tipo Usuario"
2. Selecciona "Inactivo" en "Estado"
3. Hace clic en "Filtrar"
4. Ve operadores desactivados

### Caso 4: Buscar por cargo
1. Ingresa "Software" en "Cargo"
2. Hace clic en "Filtrar"
3. Ve todos los usuarios con "Software" en su cargo

## 🔄 URL y Parámetros GET

Ejemplo de URL con filtros aplicados:
```
?action=usuario&method=listar&buscar=Juan&cargo=Admin&tipo=admin&estado=activo
```

Parámetros:
- `action=usuario` - Controlador
- `method=listar` - Método
- `buscar=...` - Búsqueda de nombre/correo
- `cargo=...` - Filtro de cargo
- `tipo=...` - Filtro de tipo (admin, supervisor, operador)
- `estado=...` - Filtro de estado (activo, inactivo)

## 💾 Cambios de Archivos

### app/models/Usuario.php
- Nuevo método: `obtenerConFiltros($filtros)`
- Usa consultas preparadas para seguridad SQL

### app/controllers/UsuarioController.php
- Actualizado: método `listar()`
- Recopila y procesa parámetros de filtro

### app/views/usuarios/list.php
- Nuevo: Formulario de filtros con Bootstrap 5
- Nuevo: Card con estilos mejorados
- Conserva valores en campos (usable)

## ✅ Beneficios

- ✅ Búsqueda rápida de usuarios
- ✅ Filtros múltiples combinables
- ✅ Interfaz intuitiva
- ✅ Búsqueda case-insensitive
- ✅ Parámetros URL persistentes
- ✅ Botón para limpiar filtros

¡Listo! Ya puedes filtrar usuarios de múltiples formas. 🎉
