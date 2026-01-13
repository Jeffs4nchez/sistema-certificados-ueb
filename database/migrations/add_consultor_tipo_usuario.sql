-- =====================================================
-- MIGRACIÓN: Agregar nuevo tipo de usuario "consultor"
-- Fecha: 2026-01-13
-- Descripción: Agrega soporte para usuarios consultores
-- que solo pueden ver presupuestos
-- =====================================================

-- Si necesitas agregar una restricción de CHECK a la columna tipo_usuario,
-- descomenta la siguiente sección (requiere migración de datos):

-- ALTER TABLE public.usuarios
-- ADD CONSTRAINT check_tipo_usuario 
-- CHECK (tipo_usuario IN ('admin', 'operador', 'consultor'));

-- Nota: El tipo_usuario ahora soporta los valores:
-- - 'admin': Acceso completo al sistema
-- - 'operador': Puede crear y gestionar certificados
-- - 'consultor': Solo visualización de presupuestos

-- Ejemplo de creación de usuario consultor:
-- INSERT INTO public.usuarios (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña, estado, es_root)
-- VALUES ('Juan', 'Consultor', 'consultor@ueb.gob.ar', 'Consultor', 'consultor', 'hashed_password_here', 'activo', 0);

COMMIT;
