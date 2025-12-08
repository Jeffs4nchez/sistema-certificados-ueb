<?php
/**
 * PRUEBA COMPLETA: Certificado con 3 items y liquidaciones
 * 
 * Item 1: Monto $1000, Liquidación $900 → Pendiente $100
 * Item 2: Monto $500,  Liquidación $400 → Pendiente $100
 * Item 3: Monto $300,  Liquidación $200 → Pendiente $100
 * 
 * Total: Monto $1800, Liquidado $1500, Pendiente $300
 */

require_once 'app/Database.php';
require_once 'app/models/Certificate.php';

echo "\n" . str_repeat("=", 100) . "\n";
echo "PRUEBA: Certificado con 3 Items y Liquidaciones\n";
echo str_repeat("=", 100) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    $cert = new Certificate();
    
    // PASO 1: Crear certificado
    echo "1️⃣  CREANDO CERTIFICADO\n";
    echo str_repeat("-", 100) . "\n";
    
    $cert_data = [
        'numero_certificado' => 'CERT-TEST-' . date('YmdHis'),
        'institucion' => 'TEST UEB',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Prueba de 3 items con liquidaciones',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 1800,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $cert->createCertificate($cert_data);
    echo "✅ Certificado creado: ID {$cert_id}, Número: {$cert_data['numero_certificado']}\n\n";
    
    // PASO 2: Crear 3 items
    echo "2️⃣  CREANDO 3 ITEMS\n";
    echo str_repeat("-", 100) . "\n";
    
    $items_crear = [
        [
            'monto' => 1000,
            'codigo_completo' => '01 00 000 001 001 0200 510510',
            'descripcion_item' => 'Item 1 - $1000',
            'certificado_id' => $cert_id
        ],
        [
            'monto' => 500,
            'codigo_completo' => '01 00 000 002 001 0200 510518',
            'descripcion_item' => 'Item 2 - $500',
            'certificado_id' => $cert_id
        ],
        [
            'monto' => 300,
            'codigo_completo' => '01 00 000 003 001 0200 510204',
            'descripcion_item' => 'Item 3 - $300',
            'certificado_id' => $cert_id
        ]
    ];
    
    $items_ids = [];
    foreach ($items_crear as $index => $item_data) {
        $item_data['programa_codigo'] = '01';
        $item_data['subprograma_codigo'] = '00';
        $item_data['proyecto_codigo'] = '000';
        $item_data['actividad_codigo'] = '001';
        $item_data['item_codigo'] = '510' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
        $item_data['ubicacion_codigo'] = '0200';
        $item_data['fuente_codigo'] = '001';
        $item_data['organismo_codigo'] = '0000';
        $item_data['naturaleza_codigo'] = '0000';
        $item_data['cantidad_liquidacion'] = 0; // Inicialmente sin liquidar
        
        $item_id = $cert->createDetail($item_data);
        $items_ids[] = $item_id;
        
        $num = $index + 1;
        echo "✅ Item $num creado: ID {$item_id}, Monto {$item_data['monto']}, Código: {$item_data['codigo_completo']}\n";
    }
    
    echo "\n";
    
    // PASO 3: Ver estado ANTES de liquidar
    echo "3️⃣  ESTADO ANTES DE LIQUIDAR\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT d.id, d.monto, d.cantidad_liquidacion, d.cantidad_pendiente, d.codigo_completo,
               p.col4
        FROM detalle_certificados d
        LEFT JOIN presupuesto_items p ON p.codigo_completo = d.codigo_completo
        WHERE d.certificado_id = ?
        ORDER BY d.id
    ");
    $stmt->execute([$cert_id]);
    $items_antes = $stmt->fetchAll();
    
    $suma_monto_antes = 0;
    $suma_liq_antes = 0;
    $suma_pend_antes = 0;
    $suma_col4_antes = 0;
    
    echo "Item | Monto  | Liquidado | Pendiente | col4\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($items_antes as $i => $item) {
        $suma_monto_antes += $item['monto'];
        $suma_liq_antes += $item['cantidad_liquidacion'];
        $suma_pend_antes += $item['cantidad_pendiente'];
        $suma_col4_antes += $item['col4'] ?? 0;
        
        $num = $i + 1;
        echo sprintf("%4d | %6.2f | %9.2f | %9.2f | %8.2f\n",
            $num,
            $item['monto'],
            $item['cantidad_liquidacion'],
            $item['cantidad_pendiente'],
            $item['col4'] ?? 0
        );
    }
    
    echo str_repeat("-", 100) . "\n";
    echo sprintf("TOTAL| %6.2f | %9.2f | %9.2f | %8.2f\n",
        $suma_monto_antes,
        $suma_liq_antes,
        $suma_pend_antes,
        $suma_col4_antes
    );
    
    // Verificar certificado ANTES
    $stmt = $db->prepare("SELECT monto_total, total_liquidado, total_pendiente FROM certificados WHERE id = ?");
    $stmt->execute([$cert_id]);
    $cert_antes = $stmt->fetch();
    
    echo "\nCertificado ANTES:\n";
    echo "  Monto Total: " . number_format($cert_antes['monto_total'], 2) . "\n";
    echo "  Liquidado: " . number_format($cert_antes['total_liquidado'], 2) . "\n";
    echo "  Pendiente: " . number_format($cert_antes['total_pendiente'], 2) . "\n\n";
    
    // PASO 4: LIQUIDAR LOS 3 ITEMS
    echo "4️⃣  LIQUIDANDO LOS 3 ITEMS\n";
    echo str_repeat("-", 100) . "\n";
    
    $liquidaciones = [
        [$items_ids[0], 900],   // Item 1: liquidar $900
        [$items_ids[1], 400],   // Item 2: liquidar $400
        [$items_ids[2], 200]    // Item 3: liquidar $200
    ];
    
    foreach ($liquidaciones as $liq) {
        $item_id = $liq[0];
        $cantidad = $liq[1];
        
        // Obtener info actual
        $stmt = $db->prepare("SELECT monto, cantidad_liquidacion FROM detalle_certificados WHERE id = ?");
        $stmt->execute([$item_id]);
        $info = $stmt->fetch();
        
        echo "Liquidando Item ID {$item_id}:\n";
        echo "  Antes: Liquidado {$info['cantidad_liquidacion']}, Pendiente " . ($info['monto'] - $info['cantidad_liquidacion']) . "\n";
        
        try {
            $cert->updateLiquidacion($item_id, $cantidad);
            echo "  ✅ Liquidación exitosa: ahora $" . number_format($cantidad, 2) . "\n";
        } catch (Exception $e) {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    // PASO 5: Ver estado DESPUÉS de liquidar
    echo "5️⃣  ESTADO DESPUÉS DE LIQUIDAR\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $db->prepare("
        SELECT d.id, d.monto, d.cantidad_liquidacion, d.cantidad_pendiente, d.codigo_completo,
               p.col4
        FROM detalle_certificados d
        LEFT JOIN presupuesto_items p ON p.codigo_completo = d.codigo_completo
        WHERE d.certificado_id = ?
        ORDER BY d.id
    ");
    $stmt->execute([$cert_id]);
    $items_despues = $stmt->fetchAll();
    
    $suma_monto_despues = 0;
    $suma_liq_despues = 0;
    $suma_pend_despues = 0;
    $suma_col4_despues = 0;
    
    echo "Item | Monto  | Liquidado | Pendiente | col4\n";
    echo str_repeat("-", 100) . "\n";
    
    foreach ($items_despues as $i => $item) {
        $suma_monto_despues += $item['monto'];
        $suma_liq_despues += $item['cantidad_liquidacion'];
        $suma_pend_despues += $item['cantidad_pendiente'];
        $suma_col4_despues += $item['col4'] ?? 0;
        
        $num = $i + 1;
        echo sprintf("%4d | %6.2f | %9.2f | %9.2f | %8.2f\n",
            $num,
            $item['monto'],
            $item['cantidad_liquidacion'],
            $item['cantidad_pendiente'],
            $item['col4'] ?? 0
        );
    }
    
    echo str_repeat("-", 100) . "\n";
    echo sprintf("TOTAL| %6.2f | %9.2f | %9.2f | %8.2f\n",
        $suma_monto_despues,
        $suma_liq_despues,
        $suma_pend_despues,
        $suma_col4_despues
    );
    
    // Verificar certificado DESPUÉS
    $stmt = $db->prepare("SELECT monto_total, total_liquidado, total_pendiente FROM certificados WHERE id = ?");
    $stmt->execute([$cert_id]);
    $cert_despues = $stmt->fetch();
    
    echo "\nCertificado DESPUÉS:\n";
    echo "  Monto Total: " . number_format($cert_despues['monto_total'], 2) . "\n";
    echo "  Liquidado: " . number_format($cert_despues['total_liquidado'], 2) . "\n";
    echo "  Pendiente: " . number_format($cert_despues['total_pendiente'], 2) . "\n\n";
    
    // PASO 6: VERIFICACIÓN FINAL
    echo "6️⃣  VERIFICACIÓN FINAL\n";
    echo str_repeat("-", 100) . "\n";
    
    $todo_ok = true;
    
    echo "Validaciones:\n\n";
    
    // 1. Verificar cantidad_pendiente = monto - liquidado
    echo "1. Cantidad Pendiente = Monto - Liquidado:\n";
    foreach ($items_despues as $i => $item) {
        $esperado = $item['monto'] - $item['cantidad_liquidacion'];
        $ok = ($item['cantidad_pendiente'] == $esperado) ? "✅" : "❌";
        $num = $i + 1;
        echo "   $ok Item $num: Pendiente " . number_format($item['cantidad_pendiente'], 2) . " = Monto " . number_format($item['monto'], 2) . " - Liquidado " . number_format($item['cantidad_liquidacion'], 2) . "\n";
        
        if ($item['cantidad_pendiente'] != $esperado) {
            $todo_ok = false;
        }
    }
    
    echo "\n2. Col4 = Monto - Cantidad Pendiente:\n";
    foreach ($items_despues as $i => $item) {
        $esperado_col4 = $item['monto'] - $item['cantidad_pendiente'];
        $ok = ($item['col4'] == $esperado_col4) ? "✅" : "❌";
        $num = $i + 1;
        echo "   $ok Item $num: Col4 " . number_format($item['col4'], 2) . " = Monto " . number_format($item['monto'], 2) . " - Pendiente " . number_format($item['cantidad_pendiente'], 2) . "\n";
        
        if ($item['col4'] != $esperado_col4) {
            $todo_ok = false;
        }
    }
    
    echo "\n3. Certificado Totales:\n";
    $ok_monto = ($cert_despues['monto_total'] == $suma_monto_despues) ? "✅" : "❌";
    $ok_liq = ($cert_despues['total_liquidado'] == $suma_liq_despues) ? "✅" : "❌";
    $ok_pend = ($cert_despues['total_pendiente'] == $suma_pend_despues) ? "✅" : "❌";
    
    echo "   $ok_monto Monto Total: " . number_format($cert_despues['monto_total'], 2) . " = Suma: " . number_format($suma_monto_despues, 2) . "\n";
    echo "   $ok_liq Liquidado Total: " . number_format($cert_despues['total_liquidado'], 2) . " = Suma: " . number_format($suma_liq_despues, 2) . "\n";
    echo "   $ok_pend Pendiente Total: " . number_format($cert_despues['total_pendiente'], 2) . " = Suma: " . number_format($suma_pend_despues, 2) . "\n";
    
    if ($cert_despues['monto_total'] != $suma_monto_despues ||
        $cert_despues['total_liquidado'] != $suma_liq_despues ||
        $cert_despues['total_pendiente'] != $suma_pend_despues) {
        $todo_ok = false;
    }
    
    echo "\n";
    if ($todo_ok) {
        echo "✅✅✅ TODAS LAS VALIDACIONES PASARON! ✅✅✅\n";
    } else {
        echo "❌ HAY PROBLEMAS EN LAS VALIDACIONES\n";
    }
    
    echo "\n" . str_repeat("=", 100) . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
