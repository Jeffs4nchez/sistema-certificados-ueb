<?php
/**
 * Script para actualizar el trigger en PostgreSQL
 * Corrección: col4 NO debe cambiar con liquidaciones
 * col4 = monto certificado (permanece igual)
 */

require_once __DIR__ . '/app/Database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔧 ACTUALIZANDO TRIGGER DE LIQUIDACIÓN\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$db = Database::getInstance()->getConnection();

// SQL para crear/actualizar el trigger
$sql_function = "
CREATE OR REPLACE FUNCTION fn_trigger_detalle_update_col4()
RETURNS TRIGGER AS \$\$
BEGIN
    IF OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion THEN
        -- col4 representa el monto certificado (no cambia con liquidaciones)
        -- Las liquidaciones solo se registran en cantidad_liquidacion
        -- El saldo disponible se calcula en el nivel de presupuesto_items
        
        -- Solo actualizar timestamp
        UPDATE presupuesto_items
        SET fecha_actualizacion = NOW()
        WHERE codigo_completo = NEW.codigo_completo;
    END IF;
    
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;
";

try {
    echo "1️⃣  Actualizando función fn_trigger_detalle_update_col4()...\n";
    echo "   Cambio: col4 NO se modifica con liquidaciones\n";
    echo "   col4 siempre = monto (Total Certificado)\n\n";
    
    $db->exec($sql_function);
    echo "✅ Función actualizada correctamente\n\n";
    
    echo "2️⃣  Recreando trigger trigger_detalle_update_col4...\n";
    $db->exec("DROP TRIGGER IF EXISTS trigger_detalle_update_col4 ON detalle_certificados CASCADE");
    
    $sql_trigger = "
    CREATE TRIGGER trigger_detalle_update_col4
    AFTER UPDATE ON detalle_certificados
    FOR EACH ROW
    WHEN (OLD.cantidad_liquidacion IS DISTINCT FROM NEW.cantidad_liquidacion)
    EXECUTE FUNCTION fn_trigger_detalle_update_col4();
    ";
    
    $db->exec($sql_trigger);
    echo "✅ Trigger recreado correctamente\n\n";
    
    // Verificar
    echo "3️⃣  Verificando que el trigger está activo...\n";
    $query = "
    SELECT trigger_name, event_manipulation, action_timing
    FROM information_schema.triggers
    WHERE trigger_name = 'trigger_detalle_update_col4';
    ";
    
    $stmt = $db->query($query);
    $trigger = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($trigger) {
        echo "✅ Trigger verificado:\n";
        echo "   Nombre: " . $trigger['trigger_name'] . "\n";
        echo "   Evento: " . $trigger['event_manipulation'] . "\n";
        echo "   Timing: " . $trigger['action_timing'] . "\n\n";
    } else {
        echo "❌ El trigger no se creó correctamente\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ ACTUALIZACIÓN COMPLETADA\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\nComportamiento correcto:\n";
    echo "• col4 = Monto Total Certificado (NO cambia con liquidaciones)\n";
    echo "• cantidad_liquidacion = Lo liquidado\n";
    echo "• Saldo Pendiente = monto - cantidad_liquidacion\n";
    echo "• Saldo Disponible = col3 - col4 (disponible presupuestario)\n\n";
    echo "Ejemplo:\n";
    echo "• Monto certificado: \$1,000\n";
    echo "• Liquidado: \$900\n";
    echo "• col4: \$1,000 (NO cambia)\n";
    echo "• Saldo Pendiente: \$100\n";
    echo "• Saldo Disponible: presupuesto_items.col3 - 1000\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Intenta ejecutar manualmente en PostgreSQL:\n";
    echo $sql_function . "\n";
    echo $sql_trigger . "\n";
}

?>
