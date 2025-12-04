<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== PRUEBA COMPLETA DE LIQUIDACIÓN ===\n\n";

// 1. Crear certificado test
echo "1️⃣  Creando certificado de prueba...\n";
$createCertStmt = $conn->prepare("INSERT INTO certificados 
(numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_id, usuario_creacion)
VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");

$certNum = 'TEST-LIQUIDACION-' . date('YmdHis');
$createCertStmt->execute([$certNum, 'TEST', 'TEST', 'Certificado para prueba de liquidación', 1000, 1, 'test']);
$certId = $conn->lastInsertId('certificados_id_seq');
echo "✓ Certificado ID: $certId\n\n";

// 2. Crear item en detalle_certificados
echo "2️⃣  Agregando item de \$1000...\n";
$getItemStmt = $conn->query("SELECT codigo_completo FROM presupuesto_items LIMIT 1");
$testItem = $getItemStmt->fetch();
$codigoCompleto = $testItem['codigo_completo'];

$createDetailStmt = $conn->prepare("INSERT INTO detalle_certificados 
(certificado_id, programa_codigo, descripcion_item, monto, codigo_completo, fecha_actualizacion)
VALUES (?, '01', 'Prueba liquidación', ?, ?, NOW())");
$createDetailStmt->execute([$certId, 1000, $codigoCompleto]);
$detailId = $conn->lastInsertId('detalle_certificados_id_seq');
echo "✓ Detalle ID: $detailId\n";
echo "  Código: $codigoCompleto\n\n";

// 3. Ver estado ANTES de liquidación
echo "3️⃣  Estado ANTES de guardar liquidación:\n";
$beforeStmt = $conn->prepare("SELECT col4, col7, col8 FROM presupuesto_items WHERE codigo_completo = ?");
$beforeStmt->execute([$codigoCompleto]);
$before = $beforeStmt->fetch();
echo "  Col4 (Certificado): \${$before['col4']}\n";
echo "  Col7 (Liquidado): \${$before['col7']}\n";
echo "  Col8 (Saldo): \${$before['col8']}\n\n";

// 4. Guardar liquidación (UPDATE cantidad_liquidacion)
echo "4️⃣  Guardando liquidación de \$300...\n";
$updateLiqStmt = $conn->prepare("UPDATE detalle_certificados 
SET cantidad_liquidacion = ? WHERE id = ?");
$updateLiqStmt->execute([300, $detailId]);
echo "✓ Liquidación guardada\n\n";

// 5. Ver estado DESPUÉS de liquidación
echo "5️⃣  Estado DESPUÉS de guardar liquidación:\n";
$afterStmt = $conn->prepare("SELECT col4, col7, col8 FROM presupuesto_items WHERE codigo_completo = ?");
$afterStmt->execute([$codigoCompleto]);
$after = $afterStmt->fetch();
echo "  Col4 (Certificado): \${$after['col4']}\n";
echo "  Col7 (Liquidado): \${$after['col7']}\n";
echo "  Col8 (Saldo): \${$after['col8']}\n\n";

// 6. Comparar
echo "6️⃣  Comparación:\n";
$col7Diff = $after['col7'] - $before['col7'];
echo "  Col7 cambió en: \$$col7Diff (esperado: \$300)\n";

if($col7Diff == 300) {
    echo "  ✅ TRIGGER UPDATE funcionó correctamente!\n\n";
} else {
    echo "  ❌ ERROR: Trigger no restó correctamente\n\n";
}

// 7. Ver certificado actualizado
echo "7️⃣  Verificando certificado:\n";
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
if($col7Diff == 300) {
    echo "✅ Los triggers funcionan perfectamente con liquidaciones nuevas\n";
} else {
    echo "❌ Hay un problema con el trigger UPDATE de liquidaciones\n";
}
?>
