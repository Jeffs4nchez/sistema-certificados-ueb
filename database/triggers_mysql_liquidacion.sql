-- ============================================================
-- TRIGGERS MySQL para actualizar total_liquidado y total_pendiente
-- ============================================================
-- Estos triggers aseguran que cuando se modifiquen liquidaciones
-- en detalle_certificados, se actualice automáticamente en certificados
-- ============================================================

DELIMITER //

-- ============================================================
-- TRIGGER 1: Al INSERT en detalle_certificados
-- Actualiza total_liquidado en certificados
-- ============================================================
DROP TRIGGER IF EXISTS trg_update_liquidado_insert //

CREATE TRIGGER trg_update_liquidado_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0),
        total_pendiente = monto_total
    WHERE id = NEW.certificado_id;
END //

-- ============================================================
-- TRIGGER 2: Al UPDATE en detalle_certificados
-- Actualiza total_liquidado en certificados
-- ============================================================
DROP TRIGGER IF EXISTS trg_update_liquidado_update //

CREATE TRIGGER trg_update_liquidado_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = NEW.certificado_id
        ), 0),
        total_pendiente = monto_total
    WHERE id = NEW.certificado_id;
END //

-- ============================================================
-- TRIGGER 3: Al DELETE en detalle_certificados
-- Actualiza total_liquidado en certificados
-- ============================================================
DROP TRIGGER IF EXISTS trg_update_liquidado_delete //

CREATE TRIGGER trg_update_liquidado_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
BEGIN
    UPDATE certificados 
    SET 
        total_liquidado = COALESCE((
            SELECT SUM(cantidad_liquidacion) 
            FROM detalle_certificados 
            WHERE certificado_id = OLD.certificado_id
        ), 0),
        total_pendiente = monto_total
    WHERE id = OLD.certificado_id;
END //

DELIMITER ;

-- ============================================================
-- Inicializar los valores correctamente en certificados existentes
-- ============================================================
UPDATE certificados c
SET 
    total_liquidado = COALESCE((
        SELECT SUM(dc.cantidad_liquidacion)
        FROM detalle_certificados dc
        WHERE dc.certificado_id = c.id
    ), 0),
    total_pendiente = c.monto_total;

-- Verificar que los triggers se crearon correctamente
SELECT TRIGGER_NAME, EVENT_MANIPULATION, ACTION_TIMING 
FROM INFORMATION_SCHEMA.TRIGGERS 
WHERE TRIGGER_SCHEMA = DATABASE() 
  AND TRIGGER_NAME LIKE 'trg_update_liquidado%';
