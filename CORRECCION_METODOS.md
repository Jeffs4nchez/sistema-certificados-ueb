# Corrección: Error "Método no encontrado: editar_formulario"

## 🐛 Problema
Se estaba llamando al método `editar_formulario` (snake_case) pero el método real es `editarFormulario` (camelCase).

## ✅ Correcciones Realizadas

### Cambios en UsuarioController.php
- Línea 123: `editar_formulario` → `editarFormulario`
- Línea 141: `editar_formulario` → `editarFormulario`

### Cambios en Vistas
- **list.php** (línea 64): Botón "Editar" → `editarFormulario`
- **view.php** (línea 40): Botón "Editar" → `editarFormulario`
- **header.php** (línea 118): Cambiar contraseña → `cambiarContraseña`

## 📋 Resumen de todas las correcciones de métodos

| Método Antiguo | Método Nuevo | Estado |
|---|---|---|
| `crear_formulario` | `crearFormulario` | ✅ Corregido |
| `editar_formulario` | `editarFormulario` | ✅ Corregido |
| `cambiar_contraseña` | `cambiarContraseña` | ✅ Corregido |

## ✅ Resultado
Todos los métodos del controlador ahora usan camelCase consistentemente.

Próxima vez que intentes editar un usuario, debe funcionar sin errores.
