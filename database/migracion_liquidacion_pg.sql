-- ============================================================
-- Migración PostgreSQL: Agregar columnas a detalle_certificados y certificados
-- ============================================================

-- Agregar cantidad_monto a detalle_certificados (si no existe)
DO $$ 
BEGIN 
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'detalle_certificados' AND column_name = 'cantidad_monto'
    ) THEN
        ALTER TABLE detalle_certificados ADD COLUMN cantidad_monto DECIMAL(15, 2) DEFAULT 0;
        CREATE INDEX idx_cantidad_monto ON detalle_certificados(cantidad_monto);
        UPDATE detalle_certificados SET cantidad_monto = monto;
    END IF;
END $$;

-- Agregar total_liquidado a certificados (si no existe)
DO $$ 
BEGIN 
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'certificados' AND column_name = 'total_liquidado'
    ) THEN
        ALTER TABLE certificados ADD COLUMN total_liquidado DECIMAL(15, 2) DEFAULT 0;
        CREATE INDEX idx_total_liquidado ON certificados(total_liquidado);
    END IF;
END $$;

-- Agregar total_pendiente a certificados (si no existe)
DO $$ 
BEGIN 
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'certificados' AND column_name = 'total_pendiente'
    ) THEN
        ALTER TABLE certificados ADD COLUMN total_pendiente DECIMAL(15, 2) DEFAULT 0;
        CREATE INDEX idx_total_pendiente ON certificados(total_pendiente);
        UPDATE certificados SET total_pendiente = monto_total;
    END IF;
END $$;

-- Inicializar valores
UPDATE certificados c
SET 
    total_liquidado = COALESCE((
        SELECT SUM(dc.cantidad_liquidacion)
        FROM detalle_certificados dc
        WHERE dc.certificado_id = c.id
    ), 0),
    total_pendiente = c.monto_total;

COMMIT;
