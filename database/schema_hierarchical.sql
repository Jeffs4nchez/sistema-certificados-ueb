-- Estructura jerárquica completa para el sistema
CREATE TABLE IF NOT EXISTS programas (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) UNIQUE NOT NULL,
    descripcion VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS subprogramas (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255),
    programa_id INTEGER NOT NULL REFERENCES programas(id),
    UNIQUE (codigo, programa_id)
);

CREATE TABLE IF NOT EXISTS proyectos (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(15) NOT NULL,
    descripcion VARCHAR(255),
    subprograma_id INTEGER NOT NULL REFERENCES subprogramas(id),
    UNIQUE (codigo, subprograma_id)
);

CREATE TABLE IF NOT EXISTS actividades (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    descripcion VARCHAR(255),
    proyecto_id INTEGER NOT NULL REFERENCES proyectos(id),
    UNIQUE (codigo, proyecto_id)
);

CREATE TABLE IF NOT EXISTS fuentes_financiamiento (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255),
    actividad_id INTEGER NOT NULL REFERENCES actividades(id),
    UNIQUE (codigo, actividad_id)
);

CREATE TABLE IF NOT EXISTS ubicaciones (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(10) NOT NULL,
    descripcion VARCHAR(255),
    fuente_id INTEGER NOT NULL REFERENCES fuentes_financiamiento(id),
    UNIQUE (codigo, fuente_id)
);

CREATE TABLE IF NOT EXISTS items (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL,
    descripcion VARCHAR(255),
    ubicacion_id INTEGER NOT NULL REFERENCES ubicaciones(id),
    UNIQUE (codigo, ubicacion_id)
);
