-- ============================================================
-- TRIGGERS OPTIMIZADOS PARA SINCRONIZACIÓN DE PRESUPUESTO
-- Sistema: Gestión de Certificados y Presupuesto - UEB Finanzas
-- Base de datos: PostgreSQL
-- ============================================================
-- FLUJO:
-- 1. Al crear un ITEM en detalle_certificados con codigo_completo
--    → Suma el monto en presupuesto_items.col4
--    → Recalcula saldo_disponible = col3 - col4
-- 
-- 2. Al actualizar el MONTO de un item
--    → Actualiza col4 (resta monto viejo, suma monto nuevo)
--    → Recalcula saldo_disponible
--
-- 3. Al liquidar un item (cantidad_liquidacion)
--    → Actualiza cantidad_pendiente = monto - cantidad_liquidacion
--    → NO afecta a col4 (que es el monto certificado total)
--
-- 4. Al eliminar un item
--    → Resta el monto de col4
--    → Recalcula saldo_disponible
-- ============================================================

-- ============================================================
-- ELIMINAR TRIGGERS ANTIGUOS
-- ============================================================
DROP TRIGGER IF EXISTS trigger_detalle_cantidad_pendiente ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trigger_detalle_insert_col4 ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trigger_col4_recalcula_saldo ON presupuesto_items CASCADE;

DROP FUNCTION IF EXISTS fn_trigger_detalle_cantidad_pendiente() CASCADE;
DROP FUNCTION IF EXISTS fn_trigger_detalle_insert_col4() CASCADE;
DROP FUNCTION IF EXISTS fn_trigger_detalle_update_col4() CASCADE;
DROP FUNCTION IF EXISTS fn_trigger_detalle_delete_col4() CASCADE;
DROP FUNCTION IF EXISTS fn_trigger_col4_recalcula_saldo() CASCADE;

-- ============================================================
-- TRIGGER 1: ANTES DE INSERTAR - Calcular cantidad_pendiente
-- Se ejecuta cuando se crea un nuevo item
-- Asegura que cantidad_pendiente = monto - 0 (inicial)
-- ============================================================
CREATE OR REPLACE FUNCTION fn_trigger_detalle_cantidad_pendiente()
RETURNS TRIGGER AS $$
BEGIN
    -- Calcular cantidad_pendiente = monto - cantidad_liquidacion
    -- En un INSERT inicial, cantidad_liquidacion es NULL o 0
    NEW.cantidad_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_detalle_cantidad_pendiente
BEFORE INSERT OR UPDATE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION fn_trigger_detalle_cantidad_pendiente();

-- ============================================================
-- TRIGGER 2: DESPUÉS DE INSERTAR - Actualizar col4 en presupuesto
-- Se ejecuta cuando se crea un nuevo item
-- Suma el monto a presupuesto_items.col4 por codigo_completo
-- ============================================================
CREATE OR REPLACE FUNCTION fn_trigger_detalle_insert_col4()
RETURNS TRIGGER AS $$
DECLARE
    presupuesto_id INTEGER;
BEGIN
    -- Buscar el presupuesto_items por codigo_completo
    SELECT id INTO presupuesto_id
    FROM presupuesto_items
    WHERE codigo_completo = NEW.codigo_completo
    LIMIT 1;
    
    -- Si existe el presupuesto, actualizar col4
    IF presupuesto_id IS NOT NULL THEN
        UPDATE presupuesto_items
        SET 
            col4 = COALESCE(col4, 0) + NEW.monto,
            fecha_actualizacion = NOW()
        WHERE id = presupuesto_id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_detalle_insert_col4
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION fn_trigger_detalle_insert_col4();

-- ============================================================
-- TRIGGER 3: DESPUÉS DE ACTUALIZAR - Actualizar col4 si cambia el monto
-- Se ejecuta cuando se modifica un item
-- Si el monto cambió: resta el monto viejo, suma el monto nuevo
-- Si solo cambió cantidad_liquidacion: solo recalcula cantidad_pendiente (hecho en BEFORE)
-- ============================================================
CREATE OR REPLACE FUNCTION fn_trigger_detalle_update_col4()
RETURNS TRIGGER AS $$
DECLARE
    presupuesto_id INTEGER;
    monto_diferencia NUMERIC;
BEGIN
    -- Solo hacer algo si el monto cambió
    IF OLD.monto IS DISTINCT FROM NEW.monto THEN
        monto_diferencia := NEW.monto - OLD.monto;
        
        -- Buscar el presupuesto_items por codigo_completo
        SELECT id INTO presupuesto_id
        FROM presupuesto_items
        WHERE codigo_completo = NEW.codigo_completo
        LIMIT 1;
        
        -- Si existe el presupuesto, actualizar col4
        IF presupuesto_id IS NOT NULL THEN
            UPDATE presupuesto_items
            SET 
                col4 = COALESCE(col4, 0) + monto_diferencia,
                fecha_actualizacion = NOW()
            WHERE id = presupuesto_id;
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_detalle_update_col4
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION fn_trigger_detalle_update_col4();

-- ============================================================
-- TRIGGER 4: DESPUÉS DE ELIMINAR - Actualizar col4 en presupuesto
-- Se ejecuta cuando se elimina un item
-- Resta el monto de presupuesto_items.col4
-- ============================================================
CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete_col4()
RETURNS TRIGGER AS $$
DECLARE
    presupuesto_id INTEGER;
BEGIN
    -- Buscar el presupuesto_items por codigo_completo
    SELECT id INTO presupuesto_id
    FROM presupuesto_items
    WHERE codigo_completo = OLD.codigo_completo
    LIMIT 1;
    
    -- Si existe el presupuesto, restar de col4
    IF presupuesto_id IS NOT NULL THEN
        UPDATE presupuesto_items
        SET 
            col4 = COALESCE(col4, 0) - OLD.monto,
            fecha_actualizacion = NOW()
        WHERE id = presupuesto_id;
    END IF;
    
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_detalle_delete_col4
AFTER DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION fn_trigger_detalle_delete_col4();

-- ============================================================
-- TRIGGER 5: ANTES DE ACTUALIZAR - Recalcular saldo_disponible
-- Se ejecuta cuando cambia col4 en presupuesto_items
-- Asegura que saldo_disponible = col3 - col4 SIEMPRE
-- ============================================================
CREATE OR REPLACE FUNCTION fn_trigger_col4_recalcula_saldo()
RETURNS TRIGGER AS $$
BEGIN
    -- Recalcular saldo_disponible = col3 - col4
    NEW.saldo_disponible := COALESCE(NEW.col3, 0) - COALESCE(NEW.col4, 0);
    NEW.fecha_actualizacion := NOW();
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_col4_recalcula_saldo
BEFORE UPDATE ON presupuesto_items
FOR EACH ROW
WHEN (OLD.col4 IS DISTINCT FROM NEW.col4 OR OLD.col3 IS DISTINCT FROM NEW.col3)
EXECUTE FUNCTION fn_trigger_col4_recalcula_saldo();

-- ============================================================
-- VERIFICACIÓN: Listar todos los triggers creados
-- ============================================================
SELECT 
    trigger_name,
    event_object_table,
    event_manipulation,
    action_timing
FROM information_schema.triggers
WHERE trigger_schema = 'public' 
  AND trigger_name IN (
      'trigger_detalle_cantidad_pendiente',
      'trigger_detalle_insert_col4',
      'trigger_detalle_update_col4',
      'trigger_detalle_delete_col4',
      'trigger_col4_recalcula_saldo'
  )
ORDER BY event_object_table, trigger_name;
