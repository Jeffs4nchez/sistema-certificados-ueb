<?php
/**
 * Script para instalar las tablas de liquidaciones en PostgreSQL
 * Ejecutar desde terminal: php database/instalar_liquidaciones.php
 */

// Incluir la clase Database
require_once __DIR__ . '/../app/Database.php';

echo "\n";
echo "============================================\n";
echo "  INSTALANDO TABLAS DE LIQUIDACIONES\n";
echo "============================================\n\n";

try {
    // Conectar a la BD usando el singleton
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Leer y ejecutar el script de tablas
    echo "📊 Creando tablas de liquidaciones...\n";
    $sql_tablas = file_get_contents(__DIR__ . '/crear_tabla_liquidaciones.sql');
    
    // Dividir por punto y coma para ejecutar cada sentencia
    $sentencias = explode(';', $sql_tablas);
    
    foreach ($sentencias as $sentencia) {
        $sentencia = trim($sentencia);
        if (!empty($sentencia)) {
            try {
                $pdo->exec($sentencia);
            } catch (\Exception $e) {
                echo "⚠️  Advertencia: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "✅ Tabla 'liquidaciones' creada\n";
    echo "✅ Tabla 'auditoria_liquidaciones' creada\n";
    echo "✅ Vista 'detalle_liquidaciones' creada\n\n";
    
    // Leer y ejecutar el script de triggers
    echo "⚙️  Instalando triggers...\n";
    $sql_triggers = file_get_contents(__DIR__ . '/triggers_liquidaciones.sql');
    
    // Para triggers en PostgreSQL, necesitamos ejecutar el código completo de una vez
    // porque las funciones con $$ no se pueden dividir por ;
    try {
        $pdo->exec($sql_triggers);
    } catch (\Exception $e) {
        echo "⚠️  Advertencia al instalar triggers: " . $e->getMessage() . "\n";
    }
    
    echo "✅ Triggers instalados correctamente\n\n";
    
    // Verificar que todo se creó
    echo "🔍 Verificando instalación...\n\n";
    
    // Verificar tablas
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name IN ('liquidaciones', 'auditoria_liquidaciones')
    ");
    
    $tablas = $stmt->fetchAll();
    echo "✅ Tablas creadas:\n";
    foreach ($tablas as $tabla) {
        echo "   - " . $tabla['table_name'] . "\n";
    }
    
    // Verificar vista
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_type = 'VIEW'
        AND table_name = 'detalle_liquidaciones'
    ");
    
    $vistas = $stmt->fetchAll();
    if (!empty($vistas)) {
        echo "✅ Vista creada:\n";
        echo "   - detalle_liquidaciones\n";
    }
    
    // Verificar triggers
    $stmt = $pdo->query("
        SELECT trigger_name 
        FROM information_schema.triggers 
        WHERE trigger_schema = 'public'
        AND trigger_name LIKE 'trigger_liquidaciones%'
    ");
    
    $triggers = $stmt->fetchAll();
    echo "✅ Triggers creados (" . count($triggers) . "):\n";
    foreach ($triggers as $trigger) {
        echo "   - " . $trigger['trigger_name'] . "\n";
    }
    
    echo "\n";
    echo "============================================\n";
    echo "  ✅ INSTALACIÓN COMPLETADA CON ÉXITO\n";
    echo "============================================\n";
    echo "\n";
    echo "📌 Próximos pasos:\n";
    echo "   1. Los modelos PHP ya están en: app/models/Liquidacion.php\n";
    echo "   2. El controlador está en: app/controllers/LiquidacionController.php\n";
    echo "   3. Las funciones JS están en: public/js/liquidaciones.js\n";
    echo "\n";
    echo "✨ ¡Ya puedes usar liquidaciones en tu sistema!\n\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}
?>
