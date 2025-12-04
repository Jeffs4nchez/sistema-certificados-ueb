<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== PRUEBA: LIQUIDACIÓN → col4 DISMINUYE ===\n\n";

// 1. Crear certificado test
echo "1️⃣  Creando certificado test de \$1000...\n";
$createCertStmt = $conn->prepare("INSERT INTO certificados 
(numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_id, usuario_creacion)
VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");

$certNum = 'TEST-PENDIENTE-' . date('YmdHis');
$createCertStmt->execute([$certNum, 'TEST', 'TEST', 'Certificado pendiente', 1000, 1, 'test']);
$certId = $conn->lastInsertId('certificados_id_seq');
echo "✓ Certificado ID: $certId\n";
echo "  Monto Total: \$1000\n";
echo "  Total Pendiente: \$1000 (sin liquidar)\n\n";

// 2. Crear item en detalle_certificados
echo "2️⃣  Agregando item a presupuesto_items...\n";
$getItemStmt = $conn->query("SELECT codigo_completo FROM presupuesto_items LIMIT 1");
$testItem = $getItemStmt->fetch();
$codigoCompleto = $testItem['codigo_completo'];

$createDetailStmt = $conn->prepare("INSERT INTO detalle_certificados 
(certificado_id, programa_codigo, descripcion_item, monto, codigo_completo, fecha_actualizacion)
VALUES (?, '01', 'Prueba', ?, ?, NOW())");
$createDetailStmt->execute([$certId, 1000, $codigoCompleto]);
$detailId = $conn->lastInsertId('detalle_certificados_id_seq');
echo "✓ Código: $codigoCompleto\n\n";

// 3. Ver col4 ANTES de liquidación
echo "3️⃣  Col4 ANTES de liquidación:\n";
$beforeStmt = $conn->prepare("SELECT col4, col8, col1 FROM presupuesto_items WHERE codigo_completo = ?");
$beforeStmt->execute([$codigoCompleto]);
$before = $beforeStmt->fetch();
$col4Before = $before['col4'];
echo "  Col4: \${$col4Before}\n";
echo "  Col1: \${$before['col1']}\n";
echo "  Col8: \${$before['col8']}\n\n";

// 4. Guardar liquidación de $300
echo "4️⃣  Guardando liquidación de \$300...\n";
$updateLiqStmt = $conn->prepare("UPDATE detalle_certificados 
SET cantidad_liquidacion = ? WHERE id = ?");
$updateLiqStmt->execute([300, $detailId]);

// Esperar a que el trigger se ejecute
sleep(1);

echo "✓ Liquidación guardada\n";
echo "  Total Liquidado ahora: \$300\n";
echo "  Total Pendiente ahora: \$700 (1000 - 300)\n\n";

// 5. Ver col4 DESPUÉS de liquidación
echo "5️⃣  Col4 DESPUÉS de liquidación:\n";
$afterStmt = $conn->prepare("SELECT col4, col8, col1 FROM presupuesto_items WHERE codigo_completo = ?");
$afterStmt->execute([$codigoCompleto]);
$after = $afterStmt->fetch();
$col4After = $after['col4'];
echo "  Col4: \${$col4After}\n";
echo "  Col1: \${$after['col1']}\n";
echo "  Col8: \${$after['col8']}\n\n";

// 6. Comparar
echo "6️⃣  Comparación:\n";
$col4Diff = $col4Before - $col4After;
echo "  Col4 disminuyó en: \$$col4Diff\n";
echo "  Esperado: \$300\n";

if($col4Diff == 300) {
    echo "  ✅ PERFECTO! El trigger funcionó correctamente\n\n";
} else {
    echo "  ⚠️  No cambió como se esperaba\n\n";
}

// 7. Ver certificado actualizado
echo "7️⃣  Estado del certificado:\n";
$certStmt = $conn->prepare("SELECT monto_total, total_liquidado, total_pendiente FROM certificados WHERE id = ?");
$certStmt->execute([$certId]);
$cert = $certStmt->fetch();
echo "  Monto Total: \${$cert['monto_total']}\n";
echo "  Total Liquidado: \${$cert['total_liquidado']}\n";
echo "  Total Pendiente: \${$cert['total_pendiente']}\n\n";

// 8. Limpiar
echo "8️⃣  Limpiando...\n";
$deleteDetailStmt = $conn->prepare("DELETE FROM detalle_certificados WHERE id = ?");
$deleteDetailStmt->execute([$detailId]);
$deleteCertStmt = $conn->prepare("DELETE FROM certificados WHERE id = ?");
$deleteCertStmt->execute([$certId]);
echo "✓ Limpieza completada\n\n";

echo "=== RESULTADO ===\n";
if($col4Diff == 300) {
    echo "✅ SISTEMA FUNCIONANDO PERFECTAMENTE\n";
    echo "✅ col4 = total_pendiente funciona automáticamente\n";
    echo "✅ Cuando se liquida, col4 disminuye automáticamente\n";
} else {
    echo "❌ Hay un problema\n";
}
?>
