-- ============================================================
-- TRIGGERS PostgreSQL para actualizar total_liquidado y total_pendiente
-- ============================================================
-- Estos triggers aseguran que cuando se modifiquen liquidaciones
-- en detalle_certificados, se actualice automáticamente en certificados
-- ============================================================

-- ============================================================
-- TRIGGER 1: Al INSERT en detalle_certificados
-- Actualiza total_liquidado y total_pendiente en certificados
-- ============================================================
CREATE OR REPLACE FUNCTION trg_update_liquidado_insert()
RETURNS TRIGGER AS $$
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
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_liquidado_insert ON detalle_certificados CASCADE;
CREATE TRIGGER trg_update_liquidado_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_update_liquidado_insert();

-- ============================================================
-- TRIGGER 2: Al UPDATE en detalle_certificados
-- Actualiza total_liquidado y total_pendiente en certificados
-- ============================================================
CREATE OR REPLACE FUNCTION trg_update_liquidado_update()
RETURNS TRIGGER AS $$
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
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_liquidado_update ON detalle_certificados CASCADE;
CREATE TRIGGER trg_update_liquidado_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_update_liquidado_update();

-- ============================================================
-- TRIGGER 3: Al DELETE en detalle_certificados
-- Actualiza total_liquidado y total_pendiente en certificados
-- ============================================================
CREATE OR REPLACE FUNCTION trg_update_liquidado_delete()
RETURNS TRIGGER AS $$
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
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_update_liquidado_delete ON detalle_certificados CASCADE;
CREATE TRIGGER trg_update_liquidado_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_update_liquidado_delete();

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

-- ============================================================
-- Verificar que los triggers se crearon correctamente
-- ============================================================
SELECT trigger_name, event_object_table, event_manipulation 
FROM information_schema.triggers 
WHERE trigger_schema = 'public' 
  AND trigger_name LIKE 'trg_update_liquidado%';
