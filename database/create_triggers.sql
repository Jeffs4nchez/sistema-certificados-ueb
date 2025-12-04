-- ===============================================
-- TRIGGERS PARA ACTUALIZAR PRESUPUESTO_ITEMS
-- Base de datos: PostgreSQL
-- ===============================================

-- Tabla de referencia:
-- detalle_certificados columns:
--   - id (PK)
--   - codigo_completo (FK para presupuesto_items)
--   - monto (monto del item)
--   - cantidad_liquidacion (cuánto se liquidó)
--
-- presupuesto_items columns:
--   - codigo_item (codigo_completo de detalle_certificados)
--   - col4 = Total Certificado
--   - col5 = Total Comprometido
--   - col6 = Total Devengado
--   - col7 = Total Liquidado
--   - col8 = Saldo Disponible

-- ===============================================
-- TRIGGER 1: Cuando se INSERT un detalle_certificados
-- Actualiza col4 (Total Certificado) en presupuesto_items
-- ===============================================

CREATE OR REPLACE FUNCTION trigger_insert_detalle_certificados()
RETURNS TRIGGER AS $$
BEGIN
    -- Actualizar el col4 (Total Certificado) en presupuesto_items
    UPDATE presupuesto_items
    SET col4 = COALESCE(col4, 0) + NEW.monto,
        col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) + NEW.monto) - COALESCE(col5, 0) - COALESCE(col6, 0) - COALESCE(col7, 0),
        fecha_actualizacion = NOW()
    WHERE codigo_item = NEW.codigo_completo;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_insert_detalle_certificados ON detalle_certificados CASCADE;
CREATE TRIGGER trigger_insert_detalle_certificados
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trigger_insert_detalle_certificados();

-- ===============================================
-- TRIGGER 2: Cuando se UPDATE cantidad_liquidacion
-- Actualiza col7 (Total Liquidado) en presupuesto_items
-- ===============================================

CREATE OR REPLACE FUNCTION trigger_update_liquidacion()
RETURNS TRIGGER AS $$
DECLARE
    diferencia NUMERIC;
BEGIN
    -- Calcular la diferencia de liquidación (new - old)
    diferencia := NEW.cantidad_liquidacion - COALESCE(OLD.cantidad_liquidacion, 0);
    
    -- Actualizar col4 (restar liquidación del certificado) y col7 (sumar liquidación)
    UPDATE presupuesto_items
    SET col4 = COALESCE(col4, 0) - diferencia,
        col7 = COALESCE(col7, 0) + diferencia,
        col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) - diferencia) - COALESCE(col5, 0) - COALESCE(col6, 0) - (COALESCE(col7, 0) + diferencia),
        fecha_actualizacion = NOW()
    WHERE codigo_item = NEW.codigo_completo;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_update_liquidacion ON detalle_certificados CASCADE;
CREATE TRIGGER trigger_update_liquidacion
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
EXECUTE FUNCTION trigger_update_liquidacion();

-- ===============================================
-- TRIGGER 3: Cuando se DELETE un detalle_certificados
-- Revierte los cambios en presupuesto_items
-- ===============================================

CREATE OR REPLACE FUNCTION trigger_delete_detalle_certificados()
RETURNS TRIGGER AS $$
BEGIN
    -- Revertir col4 (restar el monto) y col7 (restar lo liquidado)
    UPDATE presupuesto_items
    SET col4 = COALESCE(col4, 0) - OLD.monto,
        col7 = COALESCE(col7, 0) - COALESCE(OLD.cantidad_liquidacion, 0),
        col8 = COALESCE(col1, 0) - (COALESCE(col4, 0) - OLD.monto) - COALESCE(col5, 0) - COALESCE(col6, 0) - (COALESCE(col7, 0) - COALESCE(OLD.cantidad_liquidacion, 0)),
        fecha_actualizacion = NOW()
    WHERE codigo_item = OLD.codigo_completo;
    
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_delete_detalle_certificados ON detalle_certificados CASCADE;
CREATE TRIGGER trigger_delete_detalle_certificados
BEFORE DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trigger_delete_detalle_certificados();

-- ===============================================
-- VERIFICACIÓN: Mostrar triggers creados
-- ===============================================

SELECT 
    trigger_name,
    event_manipulation,
    event_object_table,
    action_timing
FROM information_schema.triggers
WHERE trigger_schema = 'public' AND event_object_table IN ('detalle_certificados', 'presupuesto_items')
ORDER BY event_object_table, trigger_name;
