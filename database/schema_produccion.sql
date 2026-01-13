-- =====================================================
-- SCRIPT DE CREACIÓN DE BASE DE DATOS - PRODUCCIÓN
-- Sistema de Gestión de Certificados y Liquidaciones
-- Fecha: 2026-01-13
-- Versión: v1.3 - Incluye tipos de usuario: admin, operador, consultor
-- =====================================================

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

-- =====================================================
-- TABLA: usuarios
-- Tipos válidos: 'admin', 'operador', 'consultor'
-- =====================================================
CREATE TABLE public.usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo_institucional VARCHAR(255) NOT NULL UNIQUE,
    cargo VARCHAR(100) NOT NULL,
    tipo_usuario VARCHAR(50) NOT NULL,
    contraseña VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    es_root INTEGER DEFAULT 0
);

CREATE INDEX idx_usuarios_correo ON public.usuarios(correo_institucional);

-- =====================================================
-- TABLA: presupuesto_items
-- =====================================================
CREATE TABLE public.presupuesto_items (
    id SERIAL PRIMARY KEY,
    descripciong1 VARCHAR(100),
    descripciong2 VARCHAR(150),
    descripciong3 VARCHAR(150),
    descripciong4 VARCHAR(100),
    descripciong5 VARCHAR(200),
    col1 NUMERIC(14,2),
    col2 NUMERIC(14,2),
    col3 NUMERIC(14,2),
    col4 NUMERIC(14,2),
    col5 NUMERIC(14,2),
    col6 NUMERIC(14,2),
    col7 NUMERIC(14,2),
    col8 NUMERIC(14,2),
    col9 NUMERIC(14,2),
    col10 NUMERIC(14,2),
    col20 NUMERIC(7,2),
    codigog1 VARCHAR(20),
    codigog2 VARCHAR(20),
    codigog3 VARCHAR(20),
    codigog4 VARCHAR(20),
    codigog5 VARCHAR(20),
    saldo_disponible NUMERIC(14,2) DEFAULT 0.00,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    codigo_completo VARCHAR(255),
    year INTEGER DEFAULT EXTRACT(year FROM CURRENT_DATE)
);

CREATE INDEX idx_presupuesto_codigog3 ON public.presupuesto_items(codigog3);
CREATE INDEX idx_presupuesto_items_year ON public.presupuesto_items(year);

-- =====================================================
-- TABLA: estructura_presupuestaria
-- =====================================================
CREATE TABLE public.estructura_presupuestaria (
    id SERIAL PRIMARY KEY,
    cod_programa VARCHAR(10),
    desc_programa VARCHAR(255),
    cod_subprograma VARCHAR(10),
    desc_subprograma VARCHAR(255),
    cod_proyecto VARCHAR(15),
    desc_proyecto VARCHAR(255),
    cod_actividad VARCHAR(20),
    desc_actividad VARCHAR(255),
    cod_fuente VARCHAR(10),
    desc_fuente VARCHAR(255),
    cod_ubicacion VARCHAR(10),
    desc_ubicacion VARCHAR(255),
    cod_item VARCHAR(20),
    desc_item VARCHAR(255),
    codigo_completo VARCHAR(255),
    cod_organismo VARCHAR(10),
    desc_organismo VARCHAR(255),
    cod_nprest VARCHAR(10),
    desc_nprest VARCHAR(255),
    year INTEGER DEFAULT EXTRACT(year FROM CURRENT_DATE)
);

CREATE INDEX idx_estructura_year ON public.estructura_presupuestaria(year);

-- =====================================================
-- TABLA: certificados
-- =====================================================
CREATE TABLE public.certificados (
    id SERIAL PRIMARY KEY,
    numero_certificado VARCHAR(50) NOT NULL,
    institucion VARCHAR(255) NOT NULL,
    seccion_memorando VARCHAR(255),
    descripcion TEXT NOT NULL,
    fecha_elaboracion DATE NOT NULL,
    monto_total NUMERIC(15,2) NOT NULL,
    estado VARCHAR(20) DEFAULT 'PENDIENTE',
    usuario_creacion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unid_ejecutora VARCHAR(50),
    unid_desc VARCHAR(50),
    clase_registro VARCHAR(50),
    clase_gasto VARCHAR(50),
    tipo_doc_respaldo VARCHAR(50),
    clase_doc_respaldo VARCHAR(50),
    usuario_id INTEGER,
    total_liquidado NUMERIC DEFAULT 0,
    total_pendiente NUMERIC DEFAULT 0,
    year INTEGER DEFAULT EXTRACT(year FROM CURRENT_DATE),
    CONSTRAINT certificados_numero_year_unique UNIQUE (numero_certificado, year),
    CONSTRAINT certificados_usuario_id_fkey FOREIGN KEY (usuario_id) 
        REFERENCES public.usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_certificados_numero ON public.certificados(numero_certificado);
CREATE INDEX idx_certificados_year ON public.certificados(year);

-- =====================================================
-- TABLA: detalle_certificados
-- =====================================================
CREATE TABLE public.detalle_certificados (
    id SERIAL PRIMARY KEY,
    certificado_id INTEGER NOT NULL,
    programa_codigo VARCHAR(50),
    subprograma_codigo VARCHAR(50),
    proyecto_codigo VARCHAR(50),
    actividad_codigo VARCHAR(50),
    item_codigo VARCHAR(50),
    ubicacion_codigo VARCHAR(50),
    fuente_codigo VARCHAR(50),
    organismo_codigo VARCHAR(50),
    naturaleza_codigo VARCHAR(50),
    descripcion_item TEXT,
    monto NUMERIC(15,2) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    codigo_completo VARCHAR(30),
    cantidad_liquidacion NUMERIC(15,2),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    cantidad_pendiente NUMERIC(15,2),
    year INTEGER DEFAULT EXTRACT(year FROM CURRENT_DATE),
    CONSTRAINT detalle_certificados_certificado_id_fkey FOREIGN KEY (certificado_id) 
        REFERENCES public.certificados(id) ON DELETE CASCADE
);

CREATE INDEX idx_detalle_certificado_id ON public.detalle_certificados(certificado_id);
CREATE INDEX idx_detalle_certificados_year ON public.detalle_certificados(year);

-- =====================================================
-- TABLA: liquidaciones
-- =====================================================
CREATE TABLE public.liquidaciones (
    id SERIAL PRIMARY KEY,
    detalle_certificado_id INTEGER NOT NULL,
    cantidad_liquidacion NUMERIC(15,2) NOT NULL,
    fecha_liquidacion DATE DEFAULT CURRENT_DATE NOT NULL,
    memorando TEXT,
    usuario_creacion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT liquidaciones_detalle_certificado_id_fkey FOREIGN KEY (detalle_certificado_id) 
        REFERENCES public.detalle_certificados(id) ON DELETE CASCADE
);

CREATE INDEX idx_liquidaciones_detalle_id ON public.liquidaciones(detalle_certificado_id);

-- =====================================================
-- TABLA: auditoria_liquidaciones
-- =====================================================
CREATE TABLE public.auditoria_liquidaciones (
    id SERIAL PRIMARY KEY,
    liquidacion_id INTEGER,
    detalle_certificado_id INTEGER,
    accion VARCHAR(50),
    cantidad_anterior NUMERIC(15,2),
    cantidad_nueva NUMERIC(15,2),
    usuario VARCHAR(255),
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_auditoria_liquidacion_id ON public.auditoria_liquidaciones(liquidacion_id);

-- =====================================================
-- TABLA: delete_tracking
-- =====================================================
CREATE TABLE public.delete_tracking (
    id SERIAL PRIMARY KEY,
    codigo_completo VARCHAR(100),
    created_at TIMESTAMP DEFAULT NOW()
);

-- =====================================================
-- TABLA: trigger_log
-- =====================================================
CREATE TABLE public.trigger_log (
    id SERIAL PRIMARY KEY,
    trigger_name VARCHAR(100),
    operacion VARCHAR(50),
    codigo_completo VARCHAR(100),
    cantidad_pendiente NUMERIC,
    resultado VARCHAR(500),
    fecha_evento TIMESTAMP DEFAULT NOW()
);

-- =====================================================
-- TABLA: trigger_logs
-- =====================================================
CREATE TABLE public.trigger_logs (
    id SERIAL PRIMARY KEY,
    trigger_name VARCHAR(100),
    action VARCHAR(50),
    codigo_completo VARCHAR(100),
    monto_amount NUMERIC(14,2),
    col4_before NUMERIC(14,2),
    col4_after NUMERIC(14,2),
    created_at TIMESTAMP DEFAULT NOW()
);

-- =====================================================
-- FUNCIONES PARA TRIGGERS DE AUDITORÍA
-- =====================================================

-- Función para auditoría de INSERT en liquidaciones
CREATE OR REPLACE FUNCTION public.tr_liquidaciones_insert() 
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    INSERT INTO auditoria_liquidaciones 
    (liquidacion_id, detalle_certificado_id, accion, cantidad_nueva, usuario, fecha_cambio)
    VALUES 
    (NEW.id, NEW.detalle_certificado_id, 'INSERT', NEW.cantidad_liquidacion, NEW.usuario_creacion, NOW());
    RETURN NEW;
END;
$$;

-- Función para auditoría de UPDATE en liquidaciones
CREATE OR REPLACE FUNCTION public.tr_liquidaciones_update() 
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    IF NEW.cantidad_liquidacion != OLD.cantidad_liquidacion THEN
        INSERT INTO auditoria_liquidaciones 
        (liquidacion_id, detalle_certificado_id, accion, cantidad_anterior, cantidad_nueva, usuario, fecha_cambio)
        VALUES 
        (NEW.id, NEW.detalle_certificado_id, 'UPDATE', OLD.cantidad_liquidacion, NEW.cantidad_liquidacion, NEW.usuario_creacion, NOW());
    END IF;
    RETURN NEW;
END;
$$;

-- Función para auditoría de DELETE en liquidaciones
CREATE OR REPLACE FUNCTION public.tr_liquidaciones_delete() 
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    INSERT INTO auditoria_liquidaciones 
    (liquidacion_id, detalle_certificado_id, accion, cantidad_anterior, usuario, fecha_cambio)
    VALUES 
    (OLD.id, OLD.detalle_certificado_id, 'DELETE', OLD.cantidad_liquidacion, OLD.usuario_creacion, NOW());
    RETURN OLD;
END;
$$;

-- =====================================================
-- TRIGGERS DE AUDITORÍA
-- =====================================================

CREATE TRIGGER trigger_liquidaciones_insert 
    AFTER INSERT ON public.liquidaciones 
    FOR EACH ROW 
    EXECUTE FUNCTION public.tr_liquidaciones_insert();

CREATE TRIGGER trigger_liquidaciones_update 
    AFTER UPDATE ON public.liquidaciones 
    FOR EACH ROW 
    EXECUTE FUNCTION public.tr_liquidaciones_update();

CREATE TRIGGER trigger_liquidaciones_delete 
    BEFORE DELETE ON public.liquidaciones 
    FOR EACH ROW 
    EXECUTE FUNCTION public.tr_liquidaciones_delete();

-- =====================================================
-- VISTA: detalle_liquidaciones
-- =====================================================
CREATE OR REPLACE VIEW public.detalle_liquidaciones AS
SELECT 
    dc.id AS detalle_id,
    dc.certificado_id,
    dc.monto AS monto_original,
    COALESCE(SUM(l.cantidad_liquidacion), 0) AS total_liquidado,
    (dc.monto - COALESCE(SUM(l.cantidad_liquidacion), 0)) AS cantidad_pendiente,
    COUNT(l.id) AS num_liquidaciones,
    MAX(l.fecha_liquidacion) AS fecha_ultima_liquidacion
FROM public.detalle_certificados dc
LEFT JOIN public.liquidaciones l ON l.detalle_certificado_id = dc.id
GROUP BY dc.id, dc.certificado_id, dc.monto;

-- =====================================================
-- FIN DEL SCRIPT
-- ===================================================== 
