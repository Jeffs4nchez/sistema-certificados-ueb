-- Modificar tabla detalle_certificados para permitir NULL en algunos campos
ALTER TABLE detalle_certificados 
MODIFY COLUMN programa_id INT NULL,
MODIFY COLUMN subprograma_id INT NULL,
MODIFY COLUMN proyecto_id INT NULL,
MODIFY COLUMN actividad_id INT NULL,
MODIFY COLUMN item_id INT NULL,
MODIFY COLUMN ubicacion_id INT NULL,
MODIFY COLUMN fuente_id INT NULL,
MODIFY COLUMN organismo_id INT NULL,
MODIFY COLUMN naturaleza_id INT NULL,
MODIFY COLUMN descripcion_linea VARCHAR(255) NULL;
