<?php
/**
 * PRUEBA CORRECTA: Certificado con 3 items VÁLIDOS y liquidaciones
 * Usando códigos que SÍ existen en presupuesto_items
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "PRUEBA CORRECTA: Certificado con 3 Items Válidos\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // Obtener códigos válidos de presupuesto_items
    $stmt = $db->query("SELECT codigo_completo FROM presupuesto_items LIMIT 3");
    $codigos_presupuesto = array_column($stmt->fetchAll(), 'codigo_completo');
    
    if (count($codigos_presupuesto) < 3) {
        echo "❌ No hay suficientes códigos en presupuesto_items\n";
        exit(1);
    }
    
    // PASO 1: Crear certificado
    echo "1️⃣  CREANDO CERTIFICADO\n";
    echo str_repeat("-", 100) . "\n";
    
    $cert_data = [
        'numero_certificado' => 'CERT-VALIDA-' . date('YmdHis'),
        'institucion' => 'TEST UEB VÁLIDO',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Prueba de 3 items VÁLIDOS con liquidaciones',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 1800,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $cert->createCertificate($cert_data);
    echo "✅ Certificado creado: ID {$cert_id}\n\n";
    
    // PASO 2: Crear 3 items con códigos VÁLIDOS
    echo "2️⃣  CREANDO 3 ITEMS CON CÓDIGOS VÁLIDOS\n";
    echo str_repeat("-", 100) . "\n";
    
    $montos = [1000, 500, 300];
    $items_ids = [];
    
    for ($i = 0; $i < 3; $i++) {
        $item_data = [
            'certificado_id' => $cert_id,
            'programa_codigo' => '01',
            'subprograma_codigo' => '00',
            'proyecto_codigo' => '000',
            'actividad_codigo' => '001',
            'item_codigo' => '510' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
            'ubicacion_codigo' => '0200',
            'fuente_codigo' => '001',
            'organismo_codigo' => '0000',
            'naturaleza_codigo' => '0000',
            'descripcion_item' => "Item $i de " . $montos[$i],
            'monto' => $montos[$i],
            'codigo_completo' => $codigos_presupuesto[$i],  // ✅ CÓDIGO VÁLIDO
            'cantidad_liquidacion' => 0
        ];
        
        $item_id = $cert->createDetail($item_data);
        $items_ids[] = $item_id;
        
        echo "✅ Item " . ($i + 1) . " creado: ID {$item_id}, Monto {$montos[$i]}, Código: {$codigos_presupuesto[$i]}\n";
    }
    
    echo "\n";
    
    // PASO 3: Ver ANTES
    echo "3️⃣  ESTADO ANTES DE LIQUIDAR\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT d.id, d.monto, d.cantidad_liquidacion, d.cantidad_pendiente, d.codigo_completo,
               p.col4, p.id as presupuesto_id
        FROM detalle_certificados d
        LEFT JOIN presupuesto_items p ON p.codigo_completo = d.codigo_completo
        WHERE d.certificado_id = ?
        ORDER BY d.id
    ");
    $stmt->execute([$cert_id]);
    $items_antes = $stmt->fetchAll();
    
    $suma_monto_antes = 0;
    $suma_col4_antes = 0;
    
    echo "Item | Monto  | Liquidado | Pendiente | Presupuesto | Col4 Antes\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($items_antes as $i => $item) {
        $suma_monto_antes += $item['monto'];
        $suma_col4_antes += $item['col4'] ?? 0;
        
        $pres_id = $item['presupuesto_id'] ?? 'NULL';
        $num = $i + 1;
        echo sprintf("%4d | %6.2f | %9.2f | %9.2f | %11s | %9.2f\n",
            $num,
            $item['monto'],
            $item['cantidad_liquidacion'],
            $item['cantidad_pendiente'],
            $pres_id,
            $item['col4'] ?? 0
        );
    }
    
    echo str_repeat("-", 100) . "\n";
    echo "TOTAL col4 ANTES: " . number_format($suma_col4_antes, 2) . "\n\n";
    
    // PASO 4: LIQUIDAR
    echo "4️⃣  LIQUIDANDO LOS 3 ITEMS\n";
    echo str_repeat("-", 100) . "\n";
    
    $liquidaciones = [[900], [400], [200]];
    
    for ($i = 0; $i < 3; $i++) {
        $item_id = $items_ids[$i];
        $cantidad = $liquidaciones[$i][0];
        
        echo "Liquidando Item $i" . ($i + 1) . " (ID {$item_id}): $" . number_format($cantidad, 2) . "\n";
        $cert->updateLiquidacion($item_id, $cantidad);
        echo "  ✅ Exitosa\n\n";
    }
    
    // PASO 5: Ver DESPUÉS
    echo "5️⃣  ESTADO DESPUÉS DE LIQUIDAR\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT d.id, d.monto, d.cantidad_liquidacion, d.cantidad_pendiente, d.codigo_completo,
               p.col4, p.id as presupuesto_id
        FROM detalle_certificados d
        LEFT JOIN presupuesto_items p ON p.codigo_completo = d.codigo_completo
        WHERE d.certificado_id = ?
        ORDER BY d.id
    ");
    $stmt->execute([$cert_id]);
    $items_despues = $stmt->fetchAll();
    
    $suma_col4_despues = 0;
    
    echo "Item | Monto  | Liquidado | Pendiente | Presupuesto | Col4 Después\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($items_despues as $i => $item) {
        $suma_col4_despues += $item['col4'] ?? 0;
        
        $pres_id = $item['presupuesto_id'] ?? 'NULL';
        $num = $i + 1;
        echo sprintf("%4d | %6.2f | %9.2f | %9.2f | %11s | %9.2f\n",
            $num,
            $item['monto'],
            $item['cantidad_liquidacion'],
            $item['cantidad_pendiente'],
            $pres_id,
            $item['col4'] ?? 0
        );
    }
    
    echo str_repeat("-", 100) . "\n";
    echo "TOTAL col4 DESPUÉS: " . number_format($suma_col4_despues, 2) . "\n";
    echo "DIFERENCIA col4: " . number_format($suma_col4_antes - $suma_col4_despues, 2) . " (debería ser " . number_format(100 + 100 + 100, 2) . ")\n\n";
    
    // PASO 6: VALIDACIONES
    echo "6️⃣  VALIDACIONES\n";
    echo str_repeat("-", 100) . "\n";
    
    $todo_ok = true;
    
    echo "1. Cantidad Pendiente = Monto - Liquidado:\n";
    foreach ($items_despues as $i => $item) {
        $esperado = $item['monto'] - $item['cantidad_liquidacion'];
        $ok = ($item['cantidad_pendiente'] == $esperado) ? "✅" : "❌";
        $num = $i + 1;
        
        if ($item['cantidad_pendiente'] != $esperado) {
            $todo_ok = false;
            echo "   $ok Item $num: Pendiente " . number_format($item['cantidad_pendiente'], 2) . " (debería ser " . number_format($esperado, 2) . ")\n";
        }
    }
    
    if ($todo_ok) {
        echo "   ✅ Todas correctas\n";
    }
    
    echo "\n2. Col4 se restó correctamente:\n";
    $diferencia_esperada = 300;  // 100 + 100 + 100
    if ($suma_col4_antes - $suma_col4_despues == $diferencia_esperada) {
        echo "   ✅ col4 se restó: " . number_format($suma_col4_antes - $suma_col4_despues, 2) . " (esperado: " . number_format($diferencia_esperada, 2) . ")\n";
    } else {
        echo "   ❌ col4 se restó: " . number_format($suma_col4_antes - $suma_col4_despues, 2) . " (esperado: " . number_format($diferencia_esperada, 2) . ")\n";
        $todo_ok = false;
    }
    
    echo "\n";
    if ($todo_ok) {
        echo "✅✅✅ TODAS LAS VALIDACIONES PASARON! ✅✅✅\n";
    } else {
        echo "❌ HAY PROBLEMAS\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
