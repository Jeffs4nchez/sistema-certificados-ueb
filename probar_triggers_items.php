<?php
/**
 * PRUEBA DE TRIGGERS - INSERT/UPDATE/DELETE
 * Inserta un item de prueba y verifica si presupuesto_items se actualiza
 */

$host = 'localhost';
$port = '5432';
$database = 'certificados_sistema';
$user = 'postgres';
$pass = 'jeffo2003';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "PRUEBA DE TRIGGERS - INSERT/UPDATE/DELETE\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Seleccionar un presupuesto existente para la prueba
    echo "1️⃣ SELECCIONANDO PRESUPUESTO DE PRUEBA...\n";
    echo str_repeat("-", 80) . "\n";
    
    $presupuesto = $db->query("
        SELECT id, codigo_completo, col4, col1 FROM presupuesto_items 
        WHERE col1 > 1000 LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    
    if (!$presupuesto) {
        echo "❌ No hay presupuestos disponibles\n";
        exit(1);
    }
    
    $presupuesto_id = $presupuesto['id'];
    $codigo_completo = $presupuesto['codigo_completo'];
    $col4_antes = (float)$presupuesto['col4'];
    
    echo "   Presupuesto seleccionado:\n";
    echo "   ID: {$presupuesto_id}\n";
    echo "   Código: {$codigo_completo}\n";
    echo "   col4 ANTES: " . number_format($col4_antes, 2) . "\n\n";
    
    // Obtener un certificado o crear uno
    echo "2️⃣ SELECCIONANDO CERTIFICADO...\n";
    echo str_repeat("-", 80) . "\n";
    
    $certificado = $db->query("SELECT id FROM certificados LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if (!$certificado) {
        echo "   Creando certificado de prueba...\n";
        $stmt = $db->prepare("
            INSERT INTO certificados (numero_certificado, institucion, descripcion, fecha_elaboracion, monto_total)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'CERT_TEST_' . time(),
            'Institución Test',
            'Certificado de prueba para verificar triggers',
            date('Y-m-d'),
            1000
        ]);
        $certificado_id = $db->lastInsertId();
        echo "   ✅ Certificado creado: ID {$certificado_id}\n";
    } else {
        $certificado_id = $certificado['id'];
        echo "   Usando certificado existente: ID {$certificado_id}\n";
    }
    echo "\n";
    
    // TEST 1: INSERT
    echo "3️⃣ TEST INSERT - Insertando item con monto 500...\n";
    echo str_repeat("-", 80) . "\n";
    
    $monto_insert = 500;
    
    $stmt = $db->prepare("
        INSERT INTO detalle_certificados (
            certificado_id, monto, codigo_completo, descripcion_item,
            cantidad_pendiente, cantidad_liquidacion
        ) VALUES (?, ?, ?, ?, ?, ?)
        RETURNING id
    ");
    
    $stmt->execute([
        $certificado_id,
        $monto_insert,
        $codigo_completo,
        'Item de prueba',
        $monto_insert,
        0
    ]);
    
    $detail_id = $stmt->fetchColumn();
    echo "   ✅ Item insertado: ID {$detail_id}\n";
    
    // Verificar si col4 se actualizó
    $presupuesto_despues_insert = $db->query("
        SELECT col4 FROM presupuesto_items WHERE id = {$presupuesto_id}
    ")->fetch(PDO::FETCH_ASSOC);
    
    $col4_despues = (float)$presupuesto_despues_insert['col4'];
    $diferencia = $col4_despues - $col4_antes;
    
    echo "   col4 DESPUÉS: " . number_format($col4_despues, 2) . "\n";
    echo "   Diferencia: " . number_format($diferencia, 2) . "\n";
    
    if (abs($diferencia - $monto_insert) < 0.01) {
        echo "   ✅ TRIGGER INSERT FUNCIONA CORRECTAMENTE\n";
    } else {
        echo "   ❌ TRIGGER INSERT NO FUNCIONÓ (esperaba +{$monto_insert}, cambió {$diferencia})\n";
    }
    echo "\n";
    
    // TEST 2: UPDATE
    echo "4️⃣ TEST UPDATE - Actualizando monto a 750...\n";
    echo str_repeat("-", 80) . "\n";
    
    $monto_nuevo = 750;
    $diferencia_update = $monto_nuevo - $monto_insert; // 750 - 500 = 250
    
    $stmt = $db->prepare("UPDATE detalle_certificados SET monto = ? WHERE id = ?");
    $stmt->execute([$monto_nuevo, $detail_id]);
    echo "   ✅ Item actualizado a {$monto_nuevo}\n";
    
    // Verificar si col4 se recalculó
    $presupuesto_despues_update = $db->query("
        SELECT col4 FROM presupuesto_items WHERE id = {$presupuesto_id}
    ")->fetch(PDO::FETCH_ASSOC);
    
    $col4_despues_update = (float)$presupuesto_despues_update['col4'];
    $col4_esperado = $col4_antes + $monto_nuevo;
    
    echo "   col4 ACTUAL: " . number_format($col4_despues_update, 2) . "\n";
    echo "   col4 ESPERADO: " . number_format($col4_esperado, 2) . "\n";
    
    if (abs($col4_despues_update - $col4_esperado) < 0.01) {
        echo "   ✅ TRIGGER UPDATE FUNCIONA CORRECTAMENTE\n";
    } else {
        echo "   ❌ TRIGGER UPDATE NO FUNCIONÓ (esperaba " . number_format($col4_esperado, 2) . ")\n";
    }
    echo "\n";
    
    // TEST 3: DELETE
    echo "5️⃣ TEST DELETE - Eliminando item...\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->prepare("DELETE FROM detalle_certificados WHERE id = ?");
    $stmt->execute([$detail_id]);
    echo "   ✅ Item eliminado\n";
    
    // Verificar si col4 se restauró
    $presupuesto_despues_delete = $db->query("
        SELECT col4 FROM presupuesto_items WHERE id = {$presupuesto_id}
    ")->fetch(PDO::FETCH_ASSOC);
    
    $col4_despues_delete = (float)$presupuesto_despues_delete['col4'];
    
    echo "   col4 ACTUAL: " . number_format($col4_despues_delete, 2) . "\n";
    echo "   col4 ESPERADO: " . number_format($col4_antes, 2) . "\n";
    
    if (abs($col4_despues_delete - $col4_antes) < 0.01) {
        echo "   ✅ TRIGGER DELETE FUNCIONA CORRECTAMENTE\n";
    } else {
        echo "   ❌ TRIGGER DELETE NO FUNCIONÓ (esperaba volver a " . number_format($col4_antes, 2) . ")\n";
    }
    echo "\n";
    
    // RESUMEN
    echo str_repeat("=", 80) . "\n";
    echo "RESUMEN DE PRUEBAS\n";
    echo str_repeat("=", 80) . "\n";
    echo "Los triggers están " . (abs($col4_despues_delete - $col4_antes) < 0.01 ? "✅ FUNCIONANDO" : "❌ CON PROBLEMAS") . "\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n";
}
?>
