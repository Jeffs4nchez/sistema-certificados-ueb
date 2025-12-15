-- ============================================
-- CREAR UN CERTIFICADO DE PRUEBA CON 3 ITEMS
-- ============================================
-- Un certificado con:
-- - Item 1: $1000
-- - Item 2: $500
-- - Item 3: $300
-- Total: $1800

-- PASO 1: Insertar el certificado principal
INSERT INTO certificados 
(numero_certificado, institucion, descripcion, fecha_elaboracion, monto_total, estado, usuario_creacion)
VALUES 
('CERT-PRUEBA-001', 'Universidad Estatal de Bolívar', 'Certificado de prueba con 3 items', CURRENT_DATE, 1800.00, 'PENDIENTE', 'SISTEMA');

-- PASO 2: Insertar los 3 detalles/items
-- Item 1: $1000
INSERT INTO detalle_certificados 
(certificado_id, programa_id, item_id, organismo_id, naturaleza_id, descripcion_item, monto)
SELECT c.id, 1, 1, 1, 1, 'Item 1 - Monto: $1000.00', 1000.00
FROM certificados c WHERE c.numero_certificado = 'CERT-PRUEBA-001';

-- Item 2: $500
INSERT INTO detalle_certificados 
(certificado_id, programa_id, item_id, organismo_id, naturaleza_id, descripcion_item, monto)
SELECT c.id, 1, 1, 1, 1, 'Item 2 - Monto: $500.00', 500.00
FROM certificados c WHERE c.numero_certificado = 'CERT-PRUEBA-001';

-- Item 3: $300
INSERT INTO detalle_certificados 
(certificado_id, programa_id, item_id, organismo_id, naturaleza_id, descripcion_item, monto)
SELECT c.id, 1, 1, 1, 1, 'Item 3 - Monto: $300.00', 300.00
FROM certificados c WHERE c.numero_certificado = 'CERT-PRUEBA-001';

-- PASO 3: Ver el certificado creado con sus detalles
SELECT 
    c.id as cert_id,
    c.numero_certificado,
    c.monto_total,
    COUNT(dc.id) as num_items
FROM certificados c
LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
WHERE c.numero_certificado = 'CERT-PRUEBA-001'
GROUP BY c.id, c.numero_certificado, c.monto_total;
