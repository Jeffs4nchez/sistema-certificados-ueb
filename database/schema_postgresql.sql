DROP TABLE IF EXISTS detalle_certificados CASCADE;
DROP TABLE IF EXISTS certificados CASCADE;
DROP TABLE IF EXISTS presupuesto_items CASCADE;
DROP TABLE IF EXISTS actividades CASCADE;
DROP TABLE IF EXISTS proyectos CASCADE;
DROP TABLE IF EXISTS subprogramas CASCADE;
DROP TABLE IF EXISTS programas CASCADE;
DROP TABLE IF EXISTS items CASCADE;
DROP TABLE IF EXISTS ubicaciones CASCADE;
DROP TABLE IF EXISTS fuentes_financiamiento CASCADE;
DROP TABLE IF EXISTS organismos CASCADE;
DROP TABLE IF EXISTS naturaleza_prestacion CASCADE;
DROP TABLE IF EXISTS parametros_presupuestarios CASCADE;
CREATE TABLE programas (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de Subprogramas (SP)
CREATE TABLE subprogramas (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    programa_id INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE,
    UNIQUE(codigo, programa_id)
);
CREATE INDEX idx_subprogramas_programa_id ON subprogramas(programa_id);

-- Crear tabla de Proyectos (PY)
CREATE TABLE proyectos (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    subprograma_id INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subprograma_id) REFERENCES subprogramas(id) ON DELETE CASCADE,
    UNIQUE(codigo, subprograma_id)
);
CREATE INDEX idx_proyectos_subprograma_id ON proyectos(subprograma_id);

-- Crear tabla de Actividades (ACT)
CREATE TABLE actividades (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    proyecto_id INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
    UNIQUE(codigo, proyecto_id)
);
CREATE INDEX idx_actividades_proyecto_id ON actividades(proyecto_id);

-- Crear tabla de Items Presupuestarios (ITEM)
CREATE TABLE items (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    ubicacion_id INT,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id) ON DELETE CASCADE,
    UNIQUE(codigo, ubicacion_id)
);
CREATE INDEX idx_items_ubicacion_id ON items(ubicacion_id);

-- Crear tabla de Ubicaciones Geográficas (UBG)
CREATE TABLE ubicaciones (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    fuente_id INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fuente_id) REFERENCES fuentes_financiamiento(id) ON DELETE CASCADE,
    UNIQUE(codigo, fuente_id)
);
CREATE INDEX idx_ubicaciones_fuente_id ON ubicaciones(fuente_id);

-- Crear tabla de Fuentes de Financiamiento (FTE)
CREATE TABLE fuentes_financiamiento (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    actividad_id INT NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (actividad_id) REFERENCES actividades(id) ON DELETE CASCADE,
    UNIQUE(codigo, actividad_id)
);
CREATE INDEX idx_fuentes_actividad_id ON fuentes_financiamiento(actividad_id);

-- Crear tabla de Organismos (ORG)
CREATE TABLE organismos (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de Naturaleza de Prestación (N.PREST)
CREATE TABLE naturaleza_prestacion (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear tabla de Parámetros Presupuestarios
CREATE TABLE parametros_presupuestarios (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL,
    valor VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(tipo, valor)
);

-- Crear tabla de Certificados
CREATE TABLE certificados (
    id SERIAL PRIMARY KEY,
    numero_certificado VARCHAR(50) NOT NULL UNIQUE,
    institucion VARCHAR(255) NOT NULL,
    seccion_memorando VARCHAR(255),
    descripcion TEXT NOT NULL,
    fecha_elaboracion DATE NOT NULL,
    monto_total DECIMAL(15, 2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'PENDIENTE',
    usuario_creacion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_documento VARCHAR(255),
    clase_documento VARCHAR(255),
    clase_registro VARCHAR(255),
    clase_gasto VARCHAR(255)
);
CREATE INDEX idx_certificados_numero ON certificados(numero_certificado);

-- Crear tabla de Detalles de Certificados
CREATE TABLE detalle_certificados (
    id SERIAL PRIMARY KEY,
    certificado_id INT NOT NULL,
    programa_id INT,
    programa_codigo VARCHAR(50),
    subprograma_id INT,
    subprograma_codigo VARCHAR(50),
    proyecto_id INT,
    proyecto_codigo VARCHAR(50),
    actividad_id INT,
    actividad_codigo VARCHAR(50),
    item_id INT,
    item_codigo VARCHAR(50),
    ubicacion_id INT,
    ubicacion_codigo VARCHAR(50),
    fuente_id INT,
    fuente_codigo VARCHAR(50),
    organismo_id INT,
    organismo_codigo VARCHAR(50),
    naturaleza_id INT,
    naturaleza_codigo VARCHAR(50),
    descripcion_item TEXT,
    monto DECIMAL(15, 2) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (certificado_id) REFERENCES certificados(id) ON DELETE CASCADE,
    FOREIGN KEY (programa_id) REFERENCES programas(id),
    FOREIGN KEY (subprograma_id) REFERENCES subprogramas(id),
    FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
    FOREIGN KEY (actividad_id) REFERENCES actividades(id),
    FOREIGN KEY (item_id) REFERENCES items(id),
    FOREIGN KEY (ubicacion_id) REFERENCES ubicaciones(id),
    FOREIGN KEY (fuente_id) REFERENCES fuentes_financiamiento(id),
    FOREIGN KEY (organismo_id) REFERENCES organismos(id),
    FOREIGN KEY (naturaleza_id) REFERENCES naturaleza_prestacion(id)
);
CREATE INDEX idx_detalle_certificado_id ON detalle_certificados(certificado_id);
CREATE INDEX idx_detalle_programa_id ON detalle_certificados(programa_id);

-- Crear tabla de Presupuesto Items (desde CSV)
CREATE TABLE presupuesto_items (
    id SERIAL PRIMARY KEY,
    
    descripciong1 VARCHAR(100),
    descripciong2 VARCHAR(150),
    descripciong3 VARCHAR(150),
    descripciong4 VARCHAR(100),
    descripciong5 VARCHAR(200),
    
    col1 DECIMAL(14,2),
    col2 DECIMAL(14,2),
    col3 DECIMAL(14,2),
    col4 DECIMAL(14,2),
    col5 DECIMAL(14,2),
    col6 DECIMAL(14,2),
    col7 DECIMAL(14,2),
    col8 DECIMAL(14,2),
    col9 DECIMAL(14,2),
    col10 DECIMAL(14,2),
    col20 DECIMAL(7,2),
    
    codigog1 VARCHAR(20),
    codigog2 VARCHAR(20),
    codigog3 VARCHAR(20),
    codigog4 VARCHAR(20),
    codigog5 VARCHAR(20),
    
    codigo_completo VARCHAR(255),
    saldo_disponible DECIMAL(14,2) DEFAULT 0.00,
    
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_presupuesto_codigog3 ON presupuesto_items(codigog3);

-- Insertar datos iniciales
INSERT INTO programas (codigo, descripcion) VALUES
    ('01', 'ADMINISTRACION CENTRAL'),
    ('82', 'FORMACION Y GESTION ACADEMICA'),
    ('83', 'GESTION DE LA INVESTIGACION');

INSERT INTO ubicaciones (codigo, descripcion) VALUES
    ('0200', 'BOLIVAR'),
    ('0201', 'GUARANDA');

INSERT INTO fuentes_financiamiento (codigo, descripcion) VALUES
    ('001', 'Recursos Fiscales'),
    ('003', 'Recursos Provenientes de Preasignaciones');

INSERT INTO organismos (codigo, descripcion) VALUES
    ('0000', 'ORGANISMO NO IDENTIFICADO');

INSERT INTO naturaleza_prestacion (codigo, descripcion) VALUES
    ('0000', 'Sin N. Prest'),
    ('0001', 'Gasto Corriente'),
    ('0002', 'Gasto de Capital'),
    ('0003', 'Servicio de Deuda');

COMMIT;
