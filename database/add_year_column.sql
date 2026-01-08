-- Script: Add year column to main tables
-- Agregue la columna year a las tablas principales para filtrar por ano de trabajo

-- 1. Add year column to certificados
ALTER TABLE certificados 
ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE);

CREATE INDEX idx_certificados_year ON certificados(year);

-- 2. Add year column to detalle_certificados
ALTER TABLE detalle_certificados 
ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE);

CREATE INDEX idx_detalle_certificados_year ON detalle_certificados(year);

-- 3. Add year column to presupuesto_items
ALTER TABLE presupuesto_items 
ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE);

CREATE INDEX idx_presupuesto_items_year ON presupuesto_items(year);

-- 4. Add year column to estructura_presupuestaria (DONDE ESTAN LOS PARAMETROS)
ALTER TABLE estructura_presupuestaria 
ADD COLUMN year INT DEFAULT EXTRACT(YEAR FROM CURRENT_DATE);

CREATE INDEX idx_estructura_year ON estructura_presupuestaria(year);

-- 5. Update existing records
UPDATE certificados SET year = EXTRACT(YEAR FROM fecha_elaboracion) WHERE year IS NULL OR year = 0;
UPDATE detalle_certificados SET year = EXTRACT(YEAR FROM fecha_creacion) WHERE year IS NULL OR year = 0;
UPDATE presupuesto_items SET year = EXTRACT(YEAR FROM CURRENT_DATE) WHERE year IS NULL OR year = 0;
UPDATE estructura_presupuestaria SET year = EXTRACT(YEAR FROM CURRENT_DATE) WHERE year IS NULL OR year = 0;

-- Verify
SELECT COUNT(*) as total, year FROM certificados GROUP BY year;
