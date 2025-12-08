<?php
/**
 * Script para agregar columna cantidad_pendiente a detalle_certificados
 */

require_once __DIR__ . '/app/Database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔧 AGREGANDO COLUMNA cantidad_pendiente A detalle_certificados\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = Database::getInstance()->getConnection();

try {
    // Paso 1: Verificar si la columna ya existe
    echo "1️⃣  Verificando si la columna ya existe...\n";
    
    $check_query = "
    SELECT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'detalle_certificados' 
        AND column_name = 'cantidad_pendiente'
    ) as existe;
    ";
    
    $stmt = $db->query($check_query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['existe']) {
        echo "✅ La columna ya existe\n\n";
    } else {
        echo "⚠️  La columna no existe. Creando...\n\n";
        
        // Paso 2: Crear la columna
        echo "2️⃣  Creando columna cantidad_pendiente...\n";
        
        $sql = "
        ALTER TABLE detalle_certificados
        ADD COLUMN cantidad_pendiente NUMERIC(14, 2) DEFAULT 0;
        ";
        
        $db->exec($sql);
        echo "✅ Columna creada exitosamente\n\n";
    }
    
    // Paso 3: Llenar la columna con datos existentes
    echo "3️⃣  Sincronizando datos existentes...\n";
    
    $update_sql = "
    UPDATE detalle_certificados
    SET cantidad_pendiente = monto - COALESCE(cantidad_liquidacion, 0)
    WHERE cantidad_pendiente = 0 OR cantidad_pendiente IS NULL;
    ";
    
    $result = $db->exec($update_sql);
    echo "✅ Datos sincronizados\n\n";
    
    // Paso 4: Verificar resultado
    echo "4️⃣  Verificando datos...\n";
    
    $verify_query = "
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN cantidad_pendiente IS NOT NULL THEN 1 END) as con_pendiente,
        COUNT(CASE WHEN cantidad_pendiente = (monto - COALESCE(cantidad_liquidacion, 0)) THEN 1 END) as correctos
    FROM detalle_certificados;
    ";
    
    $stmt = $db->query($verify_query);
    $verify = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total registros: " . $verify['total'] . "\n";
    echo "Con cantidad_pendiente: " . $verify['con_pendiente'] . "\n";
    echo "Correctos: " . $verify['correctos'] . "\n\n";
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ COLUMNA AGREGADA EXITOSAMENTE\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\nCampo agregado: cantidad_pendiente\n";
    echo "Fórmula: cantidad_pendiente = monto - cantidad_liquidacion\n";
    echo "Se actualiza automáticamente con triggers\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nIntenta ejecutar manualmente en PostgreSQL:\n";
    echo "ALTER TABLE detalle_certificados ADD COLUMN cantidad_pendiente NUMERIC(14, 2) DEFAULT 0;\n";
}

?>
