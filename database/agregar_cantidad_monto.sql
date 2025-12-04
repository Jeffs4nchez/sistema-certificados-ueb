-- ============================================================
-- Migración: Agregar columna cantidad_monto a detalle_certificados
-- ============================================================
-- Descripción: Agregamos una columna para almacenar el monto
-- original del certificado (igual a monto en el momento de creación)
-- ============================================================

ALTER TABLE detalle_certificados ADD COLUMN cantidad_monto DECIMAL(15, 2) DEFAULT 0 AFTER monto;

-- Inicializar con el valor del monto actual (para certificados existentes)
UPDATE detalle_certificados SET cantidad_monto = monto;

-- Crear índice para optimizar búsquedas
CREATE INDEX idx_cantidad_monto ON detalle_certificados(cantidad_monto);

COMMIT;
