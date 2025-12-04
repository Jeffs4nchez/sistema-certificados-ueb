-- ===============================================
-- TRIGGER: Sincronizar col4 al insertar detalle
-- ===============================================
CREATE OR REPLACE FUNCTION trg_sync_col4_on_insert()
RETURNS TRIGGER AS $$
BEGIN
    -- Actualizar presupuesto_items.col4 con el monto del item (SIN SUMAR con otros)
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear el trigger
DROP TRIGGER IF EXISTS trg_sync_col4_on_insert ON detalle_certificados;
CREATE TRIGGER trg_sync_col4_on_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_insert();

-- ===============================================
-- TRIGGER: Sincronizar col4 al actualizar detalle
-- ===============================================
CREATE OR REPLACE FUNCTION trg_sync_col4_on_update()
RETURNS TRIGGER AS $$
BEGIN
    -- Actualizar presupuesto_items.col4 directamente con el nuevo monto (SIN SUMAR)
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear el trigger
DROP TRIGGER IF EXISTS trg_sync_col4_on_update ON detalle_certificados;
CREATE TRIGGER trg_sync_col4_on_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_update();

-- ===============================================
-- TRIGGER: Sincronizar col4 al eliminar detalle
-- ===============================================
CREATE OR REPLACE FUNCTION trg_sync_col4_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    -- Revertir col4 a 0 cuando se elimina el detalle
    UPDATE presupuesto_items
    SET col4 = 0,
        col8 = COALESCE(col1, 0),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = OLD.codigo_completo;
    
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

-- Crear el trigger
DROP TRIGGER IF EXISTS trg_sync_col4_on_delete ON detalle_certificados;
CREATE TRIGGER trg_sync_col4_on_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_delete();

-- Verificar que los triggers estén creados
SELECT trigger_name, event_manipulation, action_timing
FROM information_schema.triggers
WHERE event_object_table = 'detalle_certificados' AND trigger_name LIKE 'trg_sync%'
ORDER BY trigger_name;
