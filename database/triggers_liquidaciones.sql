-- ============================================
-- TRIGGERS PARA LIQUIDACIONES (PostgreSQL)
-- ============================================
-- Estos triggers mantienen la auditoria actualizada
-- automáticamente cuando se agrega/modifica/elimina
-- una liquidación
-- ============================================

-- ============================================
-- FUNCIÓN TRIGGER: AL INSERTAR UNA LIQUIDACIÓN
-- ============================================
CREATE OR REPLACE FUNCTION tr_liquidaciones_insert() RETURNS TRIGGER AS $tr_liquidaciones_insert$
BEGIN
    INSERT INTO auditoria_liquidaciones 
    (liquidacion_id, detalle_certificado_id, accion, cantidad_nueva, usuario, fecha_cambio)
    VALUES 
    (NEW.id, NEW.detalle_certificado_id, 'INSERT', NEW.cantidad_liquidacion, NEW.usuario_creacion, NOW());
    
    RETURN NEW;
END;
$tr_liquidaciones_insert$ LANGUAGE plpgsql;

-- Crear el trigger
DROP TRIGGER IF EXISTS trigger_liquidaciones_insert ON liquidaciones;
CREATE TRIGGER trigger_liquidaciones_insert
AFTER INSERT ON liquidaciones
FOR EACH ROW
EXECUTE FUNCTION tr_liquidaciones_insert();

-- ============================================
-- FUNCIÓN TRIGGER: AL ACTUALIZAR UNA LIQUIDACIÓN
-- ============================================
CREATE OR REPLACE FUNCTION tr_liquidaciones_update() RETURNS TRIGGER AS $tr_liquidaciones_update$
BEGIN
    IF NEW.cantidad_liquidacion != OLD.cantidad_liquidacion THEN
        INSERT INTO auditoria_liquidaciones 
        (liquidacion_id, detalle_certificado_id, accion, cantidad_anterior, cantidad_nueva, usuario, fecha_cambio)
        VALUES 
        (NEW.id, NEW.detalle_certificado_id, 'UPDATE', OLD.cantidad_liquidacion, NEW.cantidad_liquidacion, NEW.usuario_creacion, NOW());
    END IF;
    
    RETURN NEW;
END;
$tr_liquidaciones_update$ LANGUAGE plpgsql;

-- Crear el trigger
DROP TRIGGER IF EXISTS trigger_liquidaciones_update ON liquidaciones;
CREATE TRIGGER trigger_liquidaciones_update
AFTER UPDATE ON liquidaciones
FOR EACH ROW
EXECUTE FUNCTION tr_liquidaciones_update();

-- ============================================
-- FUNCIÓN TRIGGER: AL ELIMINAR UNA LIQUIDACIÓN
-- ============================================
CREATE OR REPLACE FUNCTION tr_liquidaciones_delete() RETURNS TRIGGER AS $tr_liquidaciones_delete$
BEGIN
    INSERT INTO auditoria_liquidaciones 
    (liquidacion_id, detalle_certificado_id, accion, cantidad_anterior, usuario, fecha_cambio)
    VALUES 
    (OLD.id, OLD.detalle_certificado_id, 'DELETE', OLD.cantidad_liquidacion, OLD.usuario_creacion, NOW());
    
    RETURN OLD;
END;
$tr_liquidaciones_delete$ LANGUAGE plpgsql;

-- Crear el trigger
DROP TRIGGER IF EXISTS trigger_liquidaciones_delete ON liquidaciones;
CREATE TRIGGER trigger_liquidaciones_delete
BEFORE DELETE ON liquidaciones
FOR EACH ROW
EXECUTE FUNCTION tr_liquidaciones_delete();
