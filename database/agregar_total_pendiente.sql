-- ============================================================
-- Migración: Agregar columna total_pendiente a certificados
-- ============================================================
-- Descripción: Agregamos una columna para almacenar el total
-- pendiente (monto_total - total_liquidado) directamente en
-- la base de datos en lugar de calcularlo en PHP.
-- ============================================================

-- Verificar si la columna no existe y agregarla
ALTER TABLE certificados ADD COLUMN total_pendiente DECIMAL(15, 2) DEFAULT 0 AFTER monto_total;

-- Inicializar con el valor del monto_total (asumiendo que no hay liquidaciones iniciales)
UPDATE certificados SET total_pendiente = monto_total;

-- Crear índice para optimizar búsquedas
CREATE INDEX idx_total_pendiente ON certificados(total_pendiente);

-- Agregar trigger para actualizar total_pendiente cuando se liquida
CREATE TRIGGER IF NOT EXISTS trigger_update_total_pendiente_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET total_pendiente = monto_total - COALESCE((
        SELECT SUM(cantidad_liquidacion) 
        FROM detalle_certificados 
        WHERE certificado_id = NEW.certificado_id
    ), 0)
    WHERE id = NEW.certificado_id;
END;

-- Trigger para actualizar cuando se modifica una liquidación
CREATE TRIGGER IF NOT EXISTS trigger_update_total_pendiente_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET total_pendiente = monto_total - COALESCE((
        SELECT SUM(cantidad_liquidacion) 
        FROM detalle_certificados 
        WHERE certificado_id = NEW.certificado_id
    ), 0)
    WHERE id = NEW.certificado_id;
END;

-- Trigger para actualizar cuando se elimina una liquidación
CREATE TRIGGER IF NOT EXISTS trigger_update_total_pendiente_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET total_pendiente = monto_total - COALESCE((
        SELECT SUM(cantidad_liquidacion) 
        FROM detalle_certificados 
        WHERE certificado_id = OLD.certificado_id
    ), 0)
    WHERE id = OLD.certificado_id;
END;

-- Inicializar correctamente: calcular total_pendiente basado en liquidaciones existentes
UPDATE certificados 
SET total_pendiente = monto_total - COALESCE((
    SELECT SUM(dc.cantidad_liquidacion)
    FROM detalle_certificados dc
    WHERE dc.certificado_id = certificados.id
), 0);

COMMIT;
