-- Crear tabla de Usuarios
-- Esta tabla almacena los usuarios del sistema que pueden crear certificados

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

-- Crear índices para búsquedas frecuentes
CREATE INDEX idx_usuarios_correo ON usuarios(correo_institucional);
CREATE INDEX idx_usuarios_estado ON usuarios(estado);

-- Agregar relación en la tabla certificados para vincular con usuario creador
ALTER TABLE certificados 
ADD COLUMN usuario_id INT REFERENCES usuarios(id) ON DELETE SET NULL;

-- Crear índice para esta nueva columna
CREATE INDEX idx_certificados_usuario_id ON certificados(usuario_id);

-- Tipos de usuario que se pueden usar (constraint check)
-- Ejemplos: 'admin', 'supervisor', 'operador'
COMMIT;
