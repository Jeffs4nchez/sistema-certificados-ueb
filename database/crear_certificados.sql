-- Crear tabla certificados (maestra)
CREATE TABLE IF NOT EXISTS certificados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    numero_certificado VARCHAR(50) NOT NULL UNIQUE,
    institucion VARCHAR(255) NOT NULL,
    fecha_elaboracion DATE NOT NULL,
    descripcion TEXT NOT NULL,
    monto_total DECIMAL(15, 2) NOT NULL,
    usuario_creacion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(numero_certificado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Actualizar tabla detalle_certificados para agregar foreign key a certificados
ALTER TABLE detalle_certificados 
ADD CONSTRAINT detalle_certificados_ibfk_10 
FOREIGN KEY (certificado_id) REFERENCES certificados(id) ON DELETE CASCADE;
