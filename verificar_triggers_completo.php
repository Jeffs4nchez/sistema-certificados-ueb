<?php
/**
 * VERIFICACIÓN COMPLETA DE TRIGGERS
 * Verifica exactamente qué triggers existen en la BD
 */

$host = 'localhost';
$port = '5432';
$database = 'certificados_sistema';
$user = 'postgres';
$pass = 'jeffo2003';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "\n" . str_repeat("=", 100) . "\n";
    echo "VERIFICACIÓN COMPLETA DE TRIGGERS EN LA BASE DE DATOS\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // 1. VER TODOS LOS TRIGGERS
    echo "1️⃣ TRIGGERS EN TABLA 'detalle_certificados':\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT 
            trigger_name,
            event_manipulation,
            action_timing,
            action_statement
        FROM information_schema.triggers
        WHERE event_object_table = 'detalle_certificados'
        ORDER BY trigger_name
    ");
    
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($triggers)) {
        echo "❌ NO HAY TRIGGERS EN detalle_certificados\n\n";
    } else {
        echo "✅ TRIGGERS ENCONTRADOS:\n\n";
        foreach ($triggers as $i => $t) {
            echo ($i + 1) . ". " . $t['trigger_name'] . "\n";
            echo "   Evento: " . $t['action_timing'] . " " . $t['event_manipulation'] . "\n";
            echo "\n";
        }
    }
    
    // 2. VER TRIGGERS EN PRESUPUESTO_ITEMS
    echo "\n2️⃣ TRIGGERS EN TABLA 'presupuesto_items':\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT 
            trigger_name,
            event_manipulation,
            action_timing
        FROM information_schema.triggers
        WHERE event_object_table = 'presupuesto_items'
        ORDER BY trigger_name
    ");
    
    $triggers_presupuesto = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($triggers_presupuesto)) {
        echo "✅ NO HAY TRIGGERS EN presupuesto_items (correcto)\n\n";
    } else {
        echo "⚠️  TRIGGERS ENCONTRADOS EN presupuesto_items:\n";
        foreach ($triggers_presupuesto as $t) {
            echo "   - " . $t['trigger_name'] . " (" . $t['action_timing'] . " " . $t['event_manipulation'] . ")\n";
        }
        echo "\n";
    }
    
    // 3. VER FUNCIONES RELACIONADAS
    echo "\n3️⃣ FUNCIONES EN SCHEMA 'public':\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->query("
        SELECT 
            routine_name,
            routine_type
        FROM information_schema.routines
        WHERE routine_schema = 'public'
        AND (
            routine_name LIKE '%trigger%' OR
            routine_name LIKE '%item%' OR
            routine_name LIKE '%liquidaci%'
        )
        ORDER BY routine_name
    ");
    
    $functions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($functions)) {
        echo "✅ NO HAY FUNCIONES DE TRIGGER\n\n";
    } else {
        echo "⚠️  FUNCIONES ENCONTRADAS:\n";
        foreach ($functions as $f) {
            echo "   - " . $f['routine_name'] . " (" . $f['routine_type'] . ")\n";
        }
        echo "\n";
    }
    
    // 4. RESUMEN ESPERADO
    echo "\n4️⃣ RESUMEN - ESTADO ESPERADO:\n";
    echo str_repeat("-", 100) . "\n";
    
    echo "TRIGGERS ESPERADOS EN detalle_certificados:\n";
    echo "   ✅ trg_item_insert  (AFTER INSERT)\n";
    echo "   ✅ trg_item_update  (AFTER UPDATE)\n";
    echo "   ✅ trg_item_delete  (BEFORE DELETE)\n\n";
    
    echo "TRIGGERS QUE DEBEN ESTAR ELIMINADOS:\n";
    echo "   ❌ trigger_update_liquidacion\n";
    echo "   ❌ trigger_update_liquidado_insert\n";
    echo "   ❌ trigger_update_liquidado_update\n";
    echo "   ❌ trigger_update_liquidado_delete\n";
    echo "   ❌ trigger_liquidacion_actualiza_col7\n";
    echo "   ❌ trigger_actualiza_total_pendiente_*\n\n";
    
    // 5. ANÁLISIS ACTUAL
    echo "\n5️⃣ ANÁLISIS ACTUAL:\n";
    echo str_repeat("-", 100) . "\n";
    
    $trigger_count = count($triggers);
    $expected_triggers = ['trg_item_insert', 'trg_item_update', 'trg_item_delete'];
    
    if ($trigger_count == 3) {
        echo "✅ Hay exactamente 3 triggers (correcto)\n";
        
        $trigger_names = array_map(function($t) { return $t['trigger_name']; }, $triggers);
        $missing = array_diff($expected_triggers, $trigger_names);
        $extra = array_diff($trigger_names, $expected_triggers);
        
        if (empty($missing) && empty($extra)) {
            echo "✅ Son EXACTAMENTE los triggers esperados\n";
            echo "✅ NO HAY TRIGGERS DE LIQUIDACIÓN\n";
            echo "\n🟢 ESTADO: CORRECTO\n";
        } else {
            echo "⚠️  Triggers esperados no encontrados o hay extras\n";
            if (!empty($missing)) {
                echo "   Faltantes: " . implode(", ", $missing) . "\n";
            }
            if (!empty($extra)) {
                echo "   Extras: " . implode(", ", $extra) . "\n";
            }
            echo "\n🟡 ESTADO: INCORRECTO\n";
        }
    } else {
        echo "❌ Hay " . $trigger_count . " triggers (esperados: 3)\n";
        echo "⚠️  POSIBLE PROBLEMA: Hay triggers adicionales o faltantes\n";
        echo "\n🔴 ESTADO: INCORRECTO\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN: " . $e->getMessage() . "\n";
}
?>
