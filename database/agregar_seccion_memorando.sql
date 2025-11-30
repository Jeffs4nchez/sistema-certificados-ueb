-- Agregar columna seccion_memorando a la tabla certificados
ALTER TABLE certificados 
ADD COLUMN seccion_memorando VARCHAR(255) NULL AFTER institucion;
