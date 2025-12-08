<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST: Duplicar Certificado y Borrar para Test           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // PASO 1: Obtener último certificado
    echo "📌 PASO 1: Obtener último certificado...\n";
    $stmt = $db->query("
        SELECT id, numero_certificado, institucion, seccion_memorando, descripcion, 
               fecha_elaboracion, monto_total, unid_ejecutora, unid_desc, 
               clase_registro, clase_gasto, tipo_doc_respaldo, clase_doc_respaldo, usuario_id
        FROM certificados 
        ORDER BY id DESC 
        LIMIT 1
    ");
    
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cert) {
        echo "❌ No hay certificados para duplicar\n";
        exit;
    }
    
    echo "  ✓ Certificado encontrado: {$cert['numero_certificado']}\n";
    $orig_cert_id = $cert['id'];
    
    // PASO 2: Duplicar certificado
    echo "\n📌 PASO 2: Duplicar certificado...\n";
    
    $new_numero = 'DUP-' . date('YmdHis');
    $stmt = $db->prepare("
        INSERT INTO certificados (
            numero_certificado, institucion, seccion_memorando, descripcion, 
            fecha_elaboracion, monto_total, unid_ejecutora, unid_desc, 
            clase_registro, clase_gasto, tipo_doc_respaldo, clase_doc_respaldo, usuario_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $new_numero,
        $cert['institucion'],
        $cert['seccion_memorando'],
        $cert['descripcion'],
        $cert['fecha_elaboracion'],
        $cert['monto_total'],
        $cert['unid_ejecutora'],
        $cert['unid_desc'],
        $cert['clase_registro'],
        $cert['clase_gasto'],
        $cert['tipo_doc_respaldo'],
        $cert['clase_doc_respaldo'],
        $cert['usuario_id']
    ]);
    
    $new_cert_id = $db->lastInsertId();
    echo "  ✓ Nuevo certificado creado: ID=$new_cert_id, Número=$new_numero\n";
    
    // PASO 3: Duplicar items
    echo "\n📌 PASO 3: Duplicar items...\n";
    
    $stmt = $db->prepare("
        SELECT * FROM detalle_certificados WHERE certificado_id = ?
    ");
    $stmt->execute([$orig_cert_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $codigos = [];
    $item_count = 0;
    
    foreach ($items as $item) {
        $insert_stmt = $db->prepare("
            INSERT INTO detalle_certificados (
                certificado_id, programa_codigo, subprograma_codigo, proyecto_codigo,
                actividad_codigo, item_codigo, ubicacion_codigo, fuente_codigo,
                organismo_codigo, naturaleza_codigo, descripcion_item, monto, 
                cantidad_liquidacion, codigo_completo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insert_stmt->execute([
            $new_cert_id,
            $item['programa_codigo'],
            $item['subprograma_codigo'],
            $item['proyecto_codigo'],
            $item['actividad_codigo'],
            $item['item_codigo'],
            $item['ubicacion_codigo'],
            $item['fuente_codigo'],
            $item['organismo_codigo'],
            $item['naturaleza_codigo'],
            $item['descripcion_item'],
            $item['monto'],
            0, // cantidad_liquidacion = 0
            $item['codigo_completo']
        ]);
        
        echo "  ✓ Item copiado: {$item['codigo_completo']} - \${$item['monto']}\n";
        $codigos[] = $item['codigo_completo'];
        $item_count++;
    }
    
    // PASO 4: Verificar col4 antes de borrar
    echo "\n📌 PASO 4: Verificar col4 ANTES de borrar certificado:\n";
    
    $col4_antes = [];
    foreach ($codigos as $cod) {
        $stmt = $db->prepare("SELECT codigo_completo, col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$cod]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            $col4_antes[$cod] = floatval($pres['col4']);
            echo "  {$cod}:\n";
            echo "    col3: \${$pres['col3']}, col4: \${$pres['col4']}, saldo: \${$pres['saldo_disponible']}\n";
        }
    }
    
    // PASO 5: BORRAR certificado
    echo "\n📌 PASO 5: BORRANDO certificado ID=$new_cert_id...\n";
    $stmt = $db->prepare("DELETE FROM certificados WHERE id = ?");
    $stmt->execute([$new_cert_id]);
    echo "  ✓ Certificado borrado\n";
    
    // PASO 6: Verificar col4 después de borrar
    echo "\n📌 PASO 6: Verificar col4 DESPUÉS de borrar certificado:\n";
    
    $problema_encontrado = false;
    foreach ($codigos as $cod) {
        $stmt = $db->prepare("SELECT codigo_completo, col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?");
        $stmt->execute([$cod]);
        $pres = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pres) {
            $col4_despues = floatval($pres['col4']);
            $col4_antes_val = $col4_antes[$cod] ?? 0;
            
            echo "  {$cod}:\n";
            echo "    Antes: col4=\${$col4_antes_val}\n";
            echo "    Ahora: col4=\${$col4_despues}, col3: \${$pres['col3']}, saldo: \${$pres['saldo_disponible']}\n";
            
            if ($col4_despues >= $col4_antes_val) {
                echo "    ❌ PROBLEMA: col4 NO se redujo (debería haber disminuido)\n";
                $problema_encontrado = true;
            } else {
                echo "    ✓ col4 correctamente reducido\n";
            }
        }
    }
    
    echo "\n╔════════════════════════════════════════════════════════════╗\n";
    if ($problema_encontrado) {
        echo "║  ⚠️  PROBLEMA: col4 NO se resta al borrar certificado    ║\n";
    } else {
        echo "║  ✓ Todo funciona correctamente                          ║\n";
    }
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
