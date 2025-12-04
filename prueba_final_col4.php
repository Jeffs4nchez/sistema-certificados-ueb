<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== PRUEBA FINAL: col4 = total_pendiente ===\n\n";

// 1. Crear certificado test
echo "1️⃣  Creando certificado de \$1000...\n";
$createCertStmt = $conn->prepare("INSERT INTO certificados 
(numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_id, usuario_creacion)
VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");

$certNum = 'TEST-FINAL-' . date('YmdHis');
$createCertStmt->execute([$certNum, 'TEST', 'TEST', 'Prueba final col4', 1000, 1, 'test']);
$certId = $conn->lastInsertId('certificados_id_seq');
echo "✓ ID: $certId\n";
echo "  Monto: \$1000\n";
echo "  Pendiente: \$1000\n\n";

// 2. Obtener un código sin certificados actuales
echo "2️⃣  Buscando código para el item...\n";
$getCodeStmt = $conn->query("SELECT codigo_completo FROM presupuesto_items WHERE col4 = 0 LIMIT 1");
$codeRow = $getCodeStmt->fetch();
$codigoCompleto = $codeRow['codigo_completo'];
echo "✓ Código: $codigoCompleto\n\n";

// 3. Crear detalle
echo "3️⃣  Agregando detalle de \$1000...\n";
$createDetailStmt = $conn->prepare("INSERT INTO detalle_certificados 
(certificado_id, programa_codigo, descripcion_item, monto, codigo_completo, fecha_actualizacion)
VALUES (?, '01', 'Prueba', ?, ?, NOW())");
$createDetailStmt->execute([$certId, 1000, $codigoCompleto]);
$detailId = $conn->lastInsertId('detalle_certificados_id_seq');
echo "✓ Detalle ID: $detailId\n\n";

// 4. Ver col4 ANTES
echo "4️⃣  Col4 ANTES de liquidación:\n";
$beforeStmt = $conn->prepare("SELECT col4, col1, col8 FROM presupuesto_items WHERE codigo_completo = ?");
$beforeStmt->execute([$codigoCompleto]);
$before = $beforeStmt->fetch();
echo "  Col4: \${$before['col4']}\n";
echo "  Col1: \${$before['col1']}\n";
echo "  Col8: \${$before['col8']}\n";
$col4Initial = $before['col4'];
echo "  (esperado: \$1000)\n\n";

// 5. Liquidar $300
echo "5️⃣  Liquidando \$300...\n";
$updateLiqStmt = $conn->prepare("UPDATE detalle_certificados 
SET cantidad_liquidacion = ? WHERE id = ?");
$updateLiqStmt->execute([300, $detailId]);
echo "✓ Liquidación guardada\n";
echo "  Total Pendiente ahora: \$700\n\n";

sleep(1);

// 6. Ver col4 DESPUÉS
echo "6️⃣  Col4 DESPUÉS de liquidación:\n";
$afterStmt = $conn->prepare("SELECT col4, col1, col8 FROM presupuesto_items WHERE codigo_completo = ?");
$afterStmt->execute([$codigoCompleto]);
$after = $afterStmt->fetch();
echo "  Col4: \${$after['col4']}\n";
echo "  Col1: \${$after['col1']}\n";
echo "  Col8: \${$after['col8']}\n";
$col4Final = $after['col4'];
echo "  (esperado: \$700)\n\n";

// 7. Comparar
echo "7️⃣  COMPARACIÓN:\n";
echo "  Col4 inicial: \$$col4Initial\n";
echo "  Col4 final: \$$col4Final\n";
echo "  Disminuyó en: \$" . ($col4Initial - $col4Final) . "\n";
echo "  Esperado: \$300\n\n";

// 8. Verificar certificado
echo "8️⃣  Estado del certificado:\n";
$certStmt = $conn->prepare("SELECT total_liquidado, total_pendiente FROM certificados WHERE id = ?");
$certStmt->execute([$certId]);
$cert = $certStmt->fetch();
echo "  Total Liquidado: \${$cert['total_liquidado']}\n";
echo "  Total Pendiente: \${$cert['total_pendiente']}\n\n";

// 9. Resultado
echo "=== RESULTADO ===\n";
if($col4Initial == 1000 && $col4Final == 700) {
    echo "✅ PERFECTO! Sistema funcionando correctamente\n";
    echo "✅ Col4 = total_pendiente se actualiza automáticamente\n";
    echo "✅ Cuando liquidamos \$300, col4 disminuye a \$700\n";
} else {
    echo "❌ Hay un problema\n";
    echo "   Col4 inicial debería ser \$1000, es \$$col4Initial\n";
    echo "   Col4 final debería ser \$700, es \$$col4Final\n";
}

// 10. Limpiar
echo "\n9️⃣  Limpiando...\n";
$deleteDetailStmt = $conn->prepare("DELETE FROM detalle_certificados WHERE id = ?");
$deleteDetailStmt->execute([$detailId]);
$deleteCertStmt = $conn->prepare("DELETE FROM certificados WHERE id = ?");
$deleteCertStmt->execute([$certId]);
echo "✓ Limpieza completada\n";
?>
