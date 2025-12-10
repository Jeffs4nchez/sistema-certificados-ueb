<?php
/**
 * Script de instalación - Carga datos en base de datos existente
 * NO elimina ni recrea tablas - solo inserta datos de parámetros
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'certificados_sistema');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
$mysqli->set_charset("utf8mb4");

if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Verificar si la BD existe, si no crearla
$dbExists = $mysqli->select_db(DB_NAME);

if (!$dbExists) {
    echo "[INFO] Base de datos no existe, creándola...\n";
    if ($mysqli->query("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "[OK] Base de datos creada.\n";
        $mysqli->select_db(DB_NAME);
    } else {
        die("[ERROR] Error al crear BD: " . $mysqli->error);
    }
} else {
    echo "[INFO] Base de datos ya existe, usando datos existentes...\n";
}

// Crear tablas SOLO si no existen (usar CREATE TABLE IF NOT EXISTS)
$tablas = array(

    // Tabla de Parámetros Presupuestarios
    "CREATE TABLE IF NOT EXISTS parametros_presupuestarios (
        id INT PRIMARY KEY AUTO_INCREMENT,
        tipo VARCHAR(50) NOT NULL,
        valor VARCHAR(100) NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_tipo_valor (tipo, valor)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Programas (PG)
    "CREATE TABLE IF NOT EXISTS programas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL UNIQUE,
        descripcion VARCHAR(255) NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Subprogramas (SP)
    "CREATE TABLE IF NOT EXISTS subprogramas (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        programa_id INT NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (programa_id) REFERENCES programas(id) ON DELETE CASCADE,
        UNIQUE(codigo, programa_id),
        INDEX(programa_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Proyectos (PY)
    "CREATE TABLE IF NOT EXISTS proyectos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        subprograma_id INT NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subprograma_id) REFERENCES subprogramas(id) ON DELETE CASCADE,
        UNIQUE(codigo, subprograma_id),
        INDEX(subprograma_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Actividades (ACT)
    "CREATE TABLE IF NOT EXISTS actividades (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL,
        descripcion VARCHAR(255) NOT NULL,
        proyecto_id INT NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
        UNIQUE(codigo, proyecto_id),
        INDEX(proyecto_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Items Presupuestarios (ITEM)
    "CREATE TABLE IF NOT EXISTS items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL UNIQUE,
        descripcion VARCHAR(255) NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Ubicaciones Geográficas (UBG)
    "CREATE TABLE IF NOT EXISTS ubicaciones (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL UNIQUE,
        descripcion VARCHAR(255) NOT NULL,
        fuente_id INT,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (fuente_id) REFERENCES fuentes_financiamiento(id) ON DELETE SET NULL,
        INDEX(fuente_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Fuentes de Financiamiento (FTE)
    "CREATE TABLE IF NOT EXISTS fuentes_financiamiento (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL UNIQUE,
        descripcion VARCHAR(255) NOT NULL,
        actividad_id INT,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (actividad_id) REFERENCES actividades(id) ON DELETE SET NULL,
        INDEX(actividad_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Organismos (ORG)
    "CREATE TABLE IF NOT EXISTS organismos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL UNIQUE,
        descripcion VARCHAR(255) NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Naturaleza de Prestación (N.PREST)
    "CREATE TABLE IF NOT EXISTS naturaleza_prestacion (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL UNIQUE,
        descripcion VARCHAR(255) NOT NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Certificados
    "CREATE TABLE IF NOT EXISTS certificados (
        id INT PRIMARY KEY AUTO_INCREMENT,
        numero_certificado VARCHAR(50) NOT NULL UNIQUE,
        institucion VARCHAR(255) NOT NULL,
        fecha_elaboracion DATE NOT NULL,
        descripcion TEXT NOT NULL,
        monto_total DECIMAL(15, 2) NOT NULL,
        usuario_creacion VARCHAR(255),
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(numero_certificado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabla de Detalles de Certificados
    "CREATE TABLE IF NOT EXISTS detalle_certificados (
        id INT PRIMARY KEY AUTO_INCREMENT,
        certificado_id INT NOT NULL,
        programa_id INT,
        subprograma_id INT,
        proyecto_id INT,
        actividad_id INT,
        item_id INT,
        ubicacion_id INT,
        fuente_id INT,
        organismo_id INT,
        naturaleza_id INT,
        monto DECIMAL(15, 2) NOT NULL,
        descripcion_item TEXT,
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
        FOREIGN KEY (naturaleza_id) REFERENCES naturaleza_prestacion(id),
        INDEX(certificado_id),
        INDEX(programa_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // NUEVA TABLA: Presupuesto Items (desde CSV)
    "CREATE TABLE IF NOT EXISTS presupuesto_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        
        -- Descripciones
        descripciong1 VARCHAR(100),
        descripciong2 VARCHAR(150),
        descripciong3 VARCHAR(150),
        descripciong4 VARCHAR(100),
        descripciong5 VARCHAR(200),
        
        -- Montos
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
        
        -- Códigos
        codigog1 VARCHAR(20),
        codigog2 VARCHAR(20),
        codigog3 VARCHAR(20),
        codigog4 VARCHAR(20),
        codigog5 VARCHAR(20),
        
        -- Campo extra
        saldo_disponible DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        
        -- Metadata
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        INDEX(codigog1),
        INDEX(codigog2),
        INDEX(codigog3),
        INDEX(codigog4),
        INDEX(codigog5)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// Ejecutar creación de tablas (solo si no existen)
foreach ($tablas as $tabla) {
    $mysqli->query($tabla);
}

echo "\n[INFO] Insertando parámetros del sistema...\n";

// Insertar datos por defecto - Programas
$programas = array(
    array('01', 'ADMINISTRACION CENTRAL'),
    array('82', 'FORMACION Y GESTION ACADEMICA'),
    array('83', 'GESTION DE LA INVESTIGACION')
);

foreach ($programas as $prog) {
    $stmt = $mysqli->prepare("INSERT INTO programas (codigo, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $prog[0], $prog[1]);
    if ($stmt->execute()) {
        echo "[OK] Programa insertado: {$prog[1]}\n";
    } else {
        echo "[ERROR] Error al insertar programa: " . $stmt->error . "\n";
    }
}

// Insertar Ubicaciones
$ubicaciones = array(
    array('0200', 'BOLIVAR'),
    array('0201', 'GUARANDA')
);

foreach ($ubicaciones as $ubg) {
    $stmt = $mysqli->prepare("INSERT INTO ubicaciones (codigo, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $ubg[0], $ubg[1]);
    if ($stmt->execute()) {
        echo "[OK] Ubicación insertada: {$ubg[1]}\n";
    }
}

// Insertar Fuentes de Financiamiento
$fuentes = array(
    array('001', 'Recursos Fiscales'),
    array('003', 'Recursos Provenientes de Preasignaciones')
);

foreach ($fuentes as $fte) {
    $stmt = $mysqli->prepare("INSERT INTO fuentes_financiamiento (codigo, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $fte[0], $fte[1]);
    if ($stmt->execute()) {
        echo "[OK] Fuente insertada: {$fte[1]}\n";
    }
}

// Insertar Organismos
$organismos = array(
    array('0000', 'ORGANISMO NO IDENTIFICADO')
);

foreach ($organismos as $org) {
    $stmt = $mysqli->prepare("INSERT INTO organismos (codigo, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $org[0], $org[1]);
    if ($stmt->execute()) {
        echo "[OK] Organismo insertado: {$org[1]}\n";
    }
}

// Insertar Naturaleza de Prestación
$naturalezas = array(
    array('0000', 'Sin N. Prest'),
    array('0001', 'Gasto Corriente'),
    array('0002', 'Gasto de Capital'),
    array('0003', 'Servicio de Deuda')
);

foreach ($naturalezas as $nat) {
    $stmt = $mysqli->prepare("INSERT INTO naturaleza_prestacion (codigo, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nat[0], $nat[1]);
    if ($stmt->execute()) {
        echo "[OK] Naturaleza insertada: {$nat[1]}\n";
    }
}

// Insertar Parámetros Presupuestarios
$parametros = array(
    // Programas (PG)
    array('PG', '01', 'Administración General'),
    array('PG', '02', 'Educación y Cultura'),
    array('PG', '03', 'Salud Pública'),
    // Subprogramas (SP)
    array('SP', '001', 'Dirección y Coordinación Superior'),
    array('SP', '002', 'Administración Financiera'),
    array('SP', '003', 'Recursos Humanos'),
    // Proyectos (PY)
    array('PY', '000', 'Sin Proyecto Asociado'),
    array('PY', '001', 'Modernización Institucional'),
    array('PY', '002', 'Infraestructura Tecnológica'),
    // Actividades (ACT)
    array('ACT', '001', 'Gestión Administrativa'),
    array('ACT', '002', 'Supervisión y Control'),
    array('ACT', '003', 'Capacitación'),
    // Items (ITEM)
    array('ITEM', '110', 'Remuneraciones Básicas'),
    array('ITEM', '120', 'Remuneraciones Temporales'),
    array('ITEM', '230', 'Servicios Básicos'),
    array('ITEM', '340', 'Bienes de Consumo'),
    array('ITEM', '530', 'Equipamiento'),
    // Ubicaciones Geográficas (UBG)
    array('UBG', '01', 'Oficina Central'),
    array('UBG', '02', 'Departamento Administrativo'),
    array('UBG', '03', 'Departamento Financiero'),
    // Fuentes de Financiamiento (FTE)
    array('FTE', '10', 'Recursos Propios'),
    array('FTE', '20', 'Tesoro Nacional'),
    array('FTE', '30', 'Donaciones'),
    // Organismos (ORG)
    array('ORG', '001', 'Dirección General'),
    array('ORG', '002', 'Secretaría Administrativa'),
    array('ORG', '003', 'Unidad de Contabilidad'),
    // Nivel de Prestación (N.PREST)
    array('N.PREST', '1', 'Gasto Corriente'),
    array('N.PREST', '2', 'Gasto de Capital'),
    array('N.PREST', '3', 'Servicio de Deuda')
);

foreach ($parametros as $param) {
    $stmt = $mysqli->prepare("INSERT INTO parametros_presupuestarios (tipo, valor, descripcion) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE descripcion = ?");
    $stmt->bind_param("ssss", $param[0], $param[1], $param[2], $param[2]);
    if ($stmt->execute()) {
        echo "[OK] Parámetro insertado: {$param[0]} - {$param[1]}: {$param[2]}\n";
    }
}

// Crear administrador por defecto si no existe
echo "\n[INFO] Creando administrador por defecto...\n";
$adminCount = $mysqli->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'admin' AND estado = 'activo'")->fetch_assoc();

if ($adminCount['total'] == 0) {
    $nombre = "Administrador";
    $apellidos = "Sistema";
    $correo = "admin@sistema.local";
    $cargo = "Administrador del Sistema";
    $tipo_usuario = "admin";
    $contraseña = "Admin123!";
    $contraseña_hash = password_hash($contraseña, PASSWORD_BCRYPT);
    $estado = "activo";
    
    $stmt = $mysqli->prepare("
        INSERT INTO usuarios 
        (nombre, apellidos, correo_institucional, cargo, tipo_usuario, contraseña, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        "sssssss",
        $nombre,
        $apellidos,
        $correo,
        $cargo,
        $tipo_usuario,
        $contraseña_hash,
        $estado
    );
    
    if ($stmt->execute()) {
        echo "[✓] Administrador por defecto creado\n";
        echo "    Correo: {$correo}\n";
        echo "    Contraseña: {$contraseña}\n";
        echo "[⚠️] PROTECCIÓN: No puede ser eliminado si es el único administrador activo\n";
    } else {
        echo "[ERROR] Error al crear administrador: " . $stmt->error . "\n";
    }
} else {
    echo "[OK] Ya existe un administrador en el sistema\n";
}

echo "\n[✓] ¡Base de datos instalada correctamente!\n";
echo "[✓] URL: http://localhost/programas/php-certificates/\n";

$mysqli->close();
?>
