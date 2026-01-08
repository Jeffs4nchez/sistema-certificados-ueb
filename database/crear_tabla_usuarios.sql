-- Tabla de Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo_institucional VARCHAR(255) NOT NULL UNIQUE,
    cargo VARCHAR(255),
    tipo_usuario VARCHAR(50) DEFAULT 'usuario',
    contraseña VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    es_root INTEGER DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_usuarios_correo ON usuarios(correo_institucional);
CREATE INDEX IF NOT EXISTS idx_usuarios_tipo ON usuarios(tipo_usuario);
