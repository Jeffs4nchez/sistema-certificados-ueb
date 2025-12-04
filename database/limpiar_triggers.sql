-- ============================================================
-- Script para LIMPIAR SOLO los triggers de col4 (presupuesto)
-- MANTIENE los triggers de liquidación intactos
-- ============================================================

-- PASO 1: Eliminar SOLO los triggers de col4
DROP TRIGGER IF EXISTS trg_sync_col4_on_insert ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trg_sync_col4_on_update ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trg_sync_col4_on_delete ON detalle_certificados CASCADE;

-- PASO 2: Eliminar SOLO las funciones de col4
DROP FUNCTION IF EXISTS trg_sync_col4_on_insert() CASCADE;
DROP FUNCTION IF EXISTS trg_sync_col4_on_update() CASCADE;
DROP FUNCTION IF EXISTS trg_sync_col4_on_delete() CASCADE;

-- PASO 3: Crear SOLO el trigger nuevo para col4 (sin sumar)
CREATE FUNCTION trg_sync_col4_on_insert()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sync_col4_on_insert
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_insert();

-- TRIGGER UPDATE
CREATE FUNCTION trg_sync_col4_on_update()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE presupuesto_items
    SET col4 = NEW.monto,
        col8 = COALESCE(col1, 0) - NEW.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sync_col4_on_update
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_update();

-- TRIGGER DELETE
CREATE FUNCTION trg_sync_col4_on_delete()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE presupuesto_items
    SET col4 = 0,
        col8 = COALESCE(col1, 0),
        fecha_actualizacion = NOW()
    WHERE codigo_completo = OLD.codigo_completo;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_sync_col4_on_delete
AFTER DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION trg_sync_col4_on_delete();

-- PASO 4: Verificar que los triggers de col4 se crearon
SELECT trigger_name, event_manipulation, action_timing 
FROM information_schema.triggers 
WHERE trigger_schema = 'public' AND event_object_table = 'detalle_certificados'
ORDER BY trigger_name;
