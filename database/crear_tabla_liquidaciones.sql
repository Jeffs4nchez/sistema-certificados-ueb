-- ============================================
-- TABLA DE LIQUIDACIONES (PostgreSQL)
-- ============================================
-- Esta tabla almacena cada liquidación de forma independiente
-- Permite múltiples liquidaciones por detalle_certificado
-- ============================================

CREATE TABLE IF NOT EXISTS liquidaciones (
    id SERIAL PRIMARY KEY,
    
    -- Relación con el detalle del certificado
    detalle_certificado_id INT NOT NULL,
    
    -- Monto que se liquida en esta transacción
    cantidad_liquidacion DECIMAL(15, 2) NOT NULL,
    
    -- Fecha en que se realiza la liquidación
    fecha_liquidacion DATE NOT NULL DEFAULT CURRENT_DATE,
    
    -- Observaciones o detalles de la liquidación
    descripcion TEXT,
    
    -- Quién creó la liquidación
    usuario_creacion VARCHAR(255),
    
    -- Timestamps de auditoría
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Relación con detalle_certificados
    FOREIGN KEY (detalle_certificado_id) REFERENCES detalle_certificados(id) ON DELETE CASCADE
);

CREATE INDEX idx_liquidaciones_detalle_id ON liquidaciones(detalle_certificado_id);
CREATE INDEX idx_liquidaciones_fecha ON liquidaciones(fecha_liquidacion);

-- ============================================
-- VISTA PARA VER RESUMEN DE LIQUIDACIONES
-- ============================================
-- Esta vista suma automáticamente todas las liquidaciones
-- y calcula lo que aún está pendiente

DROP VIEW IF EXISTS detalle_liquidaciones;

CREATE VIEW detalle_liquidaciones AS
SELECT 
    dc.id AS detalle_id,
    dc.certificado_id,
    dc.monto AS monto_original,
    COALESCE(SUM(l.cantidad_liquidacion), 0) AS total_liquidado,
    dc.monto - COALESCE(SUM(l.cantidad_liquidacion), 0) AS cantidad_pendiente,
    COUNT(l.id) AS num_liquidaciones,
    MAX(l.fecha_liquidacion) AS fecha_ultima_liquidacion
FROM detalle_certificados dc
LEFT JOIN liquidaciones l ON l.detalle_certificado_id = dc.id
GROUP BY dc.id, dc.certificado_id, dc.monto;

-- ============================================
-- TABLA DE AUDITORÍA (OPCIONAL pero recomendado)
-- ============================================
-- Para registrar cada cambio en liquidaciones
CREATE TABLE IF NOT EXISTS auditoria_liquidaciones (
    id SERIAL PRIMARY KEY,
    liquidacion_id INT,
    detalle_certificado_id INT,
    accion VARCHAR(50), -- INSERT, UPDATE, DELETE
    cantidad_anterior DECIMAL(15, 2),
    cantidad_nueva DECIMAL(15, 2),
    usuario VARCHAR(255),
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_auditoria_liquidacion_id ON auditoria_liquidaciones(liquidacion_id);
CREATE INDEX idx_auditoria_fecha_cambio ON auditoria_liquidaciones(fecha_cambio);
