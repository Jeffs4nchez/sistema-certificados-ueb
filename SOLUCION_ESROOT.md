# ✅ SOLUCIÓN: Error "no existe la columna «es_root»"

## El Problema
Al intentar crear el primer usuario administrador, recibes este error:
```
SQLSTATE[42703]: Undefined column: 7 ERROR: no existe la columna «es_root» en la relación «usuarios»
```

Esto ocurre porque la tabla `usuarios` no tiene la columna `es_root` que el sistema necesita.

## La Solución - Pasos a Seguir

### Opción 1: Ejecución Automática (Recomendado)

1. **Accede a la URL de migración:**
   ```
   http://localhost/programas/certificados-sistema/execute_esroot_migration.php
   ```

2. **Observa el mensaje de confirmación**
   - La columna `es_root` será agregada automáticamente
   - El primer usuario será marcado como administrador protegido

### Opción 2: Ejecución Manual (PostgreSQL)

Si prefieres ejecutar manualmente, conéctate a PostgreSQL y ejecuta:

```sql
-- Verificar si la columna existe
SELECT EXISTS (
    SELECT 1 FROM information_schema.columns 
    WHERE table_name = 'usuarios' AND column_name = 'es_root'
) as column_exists;

-- Agregar la columna si no existe
ALTER TABLE usuarios ADD COLUMN es_root INTEGER DEFAULT 0;

-- Marcar el primer usuario como root
UPDATE usuarios SET es_root = 1 WHERE id = 1;
```

## Paso 3: Crear el Administrador

Después de ejecutar la migración:

1. Accede a: `http://localhost/programas/certificados-sistema/`
2. Completa el formulario de instalación con:
   - **Nombre:** Juan
   - **Apellidos:** Pérez
   - **Correo:** admin@institucion.com
   - **Cargo:** Administrador del Sistema
   - **Contraseña:** Mínimo 6 caracteres (recomendado: caracteres especiales)

3. Haz clic en "Crear Administrador e Iniciar"

## Paso 4: Verificar la Instalación

- Inicia sesión con las credenciales del administrador creado
- Selecciona el año de trabajo (ej: 2026)
- Accede al dashboard

## ¿Qué Cambios Se Hicieron?

Se han actualizado los siguientes archivos para incluir la columna `es_root`:

1. ✅ `bootstrap.php` - Incluye `es_root` en el INSERT automático
2. ✅ `setup_usuarios.php` - Ambos usuarios de prueba ahora incluyen `es_root`
3. ✅ `app/controllers/AuthController.php` - Ya tenía el soporte (sin cambios)
4. ✅ Se creó `database/crear_tabla_usuarios.sql` con el esquema completo
5. ✅ Se creó `execute_esroot_migration.php` para ejecutar la migración

## Nota Importante

La columna `es_root` sirve para:
- Proteger el primer administrador del sistema
- Marcar usuarios con privilegios administrativos especiales
- Prevenir eliminación accidental del administrador principal

---

**¿Necesitas ayuda adicional?** Verifica que:
- PostgreSQL está ejecutándose
- La base de datos `certificados_sistema` existe
- Las credenciales en `app/Database.php` son correctas
