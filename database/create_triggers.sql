-- ===============================================
-- LIMPIEZA: Eliminar todos los triggers anteriores
-- Base de datos: PostgreSQL
-- ===============================================

-- Triggers antiguos con nombres específicos
DROP TRIGGER IF EXISTS trg_sync_col4_on_insert ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trg_sync_col4_on_update ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trg_sync_col4_on_delete ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trg_update_liquidado_insert ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trg_update_liquidado_update ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trg_update_liquidado_delete ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trigger_insert_detalle_certificados ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trigger_update_liquidacion ON detalle_certificados CASCADE;
DROP TRIGGER IF EXISTS trigger_recalculate_saldo_disponible ON presupuesto_items CASCADE;
DROP TRIGGER IF EXISTS trigger_delete_detalle_certificados ON detalle_certificados CASCADE;

-- Eliminar funciones antiguas (PostgreSQL usa paréntesis)
DROP FUNCTION IF EXISTS trg_sync_col4_on_insert() CASCADE;
DROP FUNCTION IF EXISTS trg_sync_col4_on_update() CASCADE;
DROP FUNCTION IF EXISTS trg_sync_col4_on_delete() CASCADE;
DROP FUNCTION IF EXISTS trg_update_liquidado_insert() CASCADE;
DROP FUNCTION IF EXISTS trg_update_liquidado_update() CASCADE;
DROP FUNCTION IF EXISTS trg_update_liquidado_delete() CASCADE;
DROP FUNCTION IF EXISTS trigger_insert_detalle_certificados() CASCADE;
DROP FUNCTION IF EXISTS trigger_update_liquidacion() CASCADE;
DROP FUNCTION IF EXISTS trigger_recalculate_saldo_disponible() CASCADE;
DROP FUNCTION IF EXISTS trigger_delete_detalle_certificados() CASCADE;

-- ===============================================
-- TRIGGERS PARA SINCRONIZAR PRESUPUESTO CON CERTIFICADOS
-- Sistema: Gestión de Certificados y Presupuesto
-- Base de datos: MySQL/PostgreSQL
-- ===============================================

-- REFERENCIA DE COLUMNAS EN presupuesto_items:
--   col1 = Codificado (original)
--   col3 = Disponible Inicial / Reservado
--   col4 = Total Certificado
--   saldo_disponible = col3 - col4 (lo disponible después de certificar)

-- ===============================================
-- TRIGGER 1: trigger_certificados_actualiza_col4
-- Se activa: Al INSERT/UPDATE/DELETE en tabla certificados
-- Efecto: Actualiza col4 en presupuesto_items
-- ===============================================

CREATE OR REPLACE FUNCTION fn_trigger_certificados_actualiza_col4()
RETURNS TRIGGER AS $$
DECLARE
    monto_cambio NUMERIC;
BEGIN
    -- Determinar el monto a sumar o restar según la operación
    IF TG_OP = 'INSERT' THEN
        monto_cambio := NEW.monto_total;
    ELSIF TG_OP = 'DELETE' THEN
        monto_cambio := -OLD.monto_total;
    ELSE -- UPDATE
        monto_cambio := NEW.monto_total - OLD.monto_total;
    END IF;
    
    -- Actualizar col4 en presupuesto_items (si existe referencia)
    -- Nota: Se asume que certificados tiene una columna codigo_completo o similar
    -- Ajusta según tu estructura real
    
    RETURN COALESCE(NEW, OLD);
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_certificados_actualiza_col4 ON certificados CASCADE;
CREATE TRIGGER trigger_certificados_actualiza_col4
AFTER INSERT OR UPDATE OR DELETE ON certificados
FOR EACH ROW
EXECUTE FUNCTION fn_trigger_certificados_actualiza_col4();

-- ===============================================
-- TRIGGER 2A: trigger_detalle_insert_col4
-- Se activa: Al INSERT en tabla detalle_certificados
-- Efecto: Suma el monto del item a col4 en presupuesto_items
-- ===============================================

CREATE OR REPLACE FUNCTION fn_trigger_detalle_insert_col4()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE presupuesto_items
    SET col4 = COALESCE(col4, 0) + NEW.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = NEW.codigo_completo;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_detalle_insert_col4 ON detalle_certificados CASCADE;
CREATE TRIGGER trigger_detalle_insert_col4
AFTER INSERT ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION fn_trigger_detalle_insert_col4();

-- ===============================================
-- TRIGGER 2B: trigger_detalle_update_col4
-- Se activa: Al UPDATE cantidad_liquidacion en tabla detalle_certificados
-- Solo se ejecuta si cantidad_liquidacion cambió
-- Efecto: NO MODIFICA col4 (permanece como monto original)
--         El saldo se calcula: col1 (presupuesto) - col4 (certificado)
-- ===============================================

CREATE OR REPLACE FUNCTION fn_trigger_detalle_update_col4()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion THEN
        -- col4 representa el monto certificado (no cambia con liquidaciones)
        -- Las liquidaciones solo se registran en cantidad_liquidacion
        -- El saldo disponible se calcula en el nivel de presupuesto_items
        
        -- Solo actualizar timestamp
        UPDATE presupuesto_items
        SET fecha_actualizacion = NOW()
        WHERE codigo_completo = NEW.codigo_completo;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE;
CREATE TRIGGER trigger_detalle_update_col4
AFTER UPDATE ON detalle_certificados
FOR EACH ROW
WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
EXECUTE FUNCTION fn_trigger_detalle_update_col4();

-- ===============================================
-- TRIGGER 2C: trigger_detalle_delete_col4
-- Se activa: Al DELETE en tabla detalle_certificados
-- Efecto: Resta el monto del item de col4 en presupuesto_items
-- ===============================================

CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete_col4()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE presupuesto_items
    SET col4 = COALESCE(col4, 0) - OLD.monto,
        fecha_actualizacion = NOW()
    WHERE codigo_completo = OLD.codigo_completo;
    
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE;
CREATE TRIGGER trigger_detalle_delete_col4
AFTER DELETE ON detalle_certificados
FOR EACH ROW
EXECUTE FUNCTION fn_trigger_detalle_delete_col4();

-- ===============================================
-- TRIGGER 3: trigger_col4_recalcula_saldo
-- Se activa: Cuando cambia col4 en presupuesto_items
-- Efecto: Recalcula saldo_disponible = col3 - col4
-- ===============================================

CREATE OR REPLACE FUNCTION fn_trigger_col4_recalcula_saldo()
RETURNS TRIGGER AS $$
BEGIN
    -- Recalcular saldo_disponible = col3 - col4
    NEW.saldo_disponible := COALESCE(NEW.col3, 0) - COALESCE(NEW.col4, 0);
    NEW.fecha_actualizacion := NOW();
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_col4_recalcula_saldo ON presupuesto_items CASCADE;
CREATE TRIGGER trigger_col4_recalcula_saldo
BEFORE UPDATE ON presupuesto_items
FOR EACH ROW
WHEN (OLD.col4 IS DISTINCT FROM NEW.col4)
EXECUTE FUNCTION fn_trigger_col4_recalcula_saldo();

-- ===============================================
-- VERIFICACIÓN: Listar todos los triggers creados
-- ===============================================

SELECT 
    trigger_name,
    event_manipulation,
    event_object_table,
    action_timing
FROM information_schema.triggers
WHERE trigger_schema = 'public' 
  AND trigger_name LIKE 'trigger_%'
ORDER BY event_object_table, trigger_name;
