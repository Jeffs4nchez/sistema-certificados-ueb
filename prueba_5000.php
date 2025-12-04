<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== PRUEBA: Crear certificado de \$5000 ===\n\n";

// 1. Crear certificado test
echo "1️⃣  Creando certificado de \$5000...\n";
$createCertStmt = $conn->prepare("INSERT INTO certificados 
(numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_id, usuario_creacion)
VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");

$certNum = 'TEST-5K-' . date('YmdHis');
$createCertStmt->execute([$certNum, 'TEST', 'TEST', 'Certificado de 5000', 5000, 1, 'test']);
$certId = $conn->lastInsertId('certificados_id_seq');
echo "✓ Certificado ID: $certId\n";
echo "  Monto Total: \$5000\n";
echo "  Total Pendiente: \$5000\n\n";

// 2. Obtener código sin certificados
echo "2️⃣  Obteniendo código sin certificados...\n";
$getCodeStmt = $conn->query("SELECT id, codigo_completo, col1 FROM presupuesto_items 
WHERE col4 = 0 OR col4 IS NULL LIMIT 1");
$item = $getCodeStmt->fetch();
$itemId = $item['id'];
$codigoCompleto = $item['codigo_completo'];
echo "✓ Código: $codigoCompleto\n";
echo "  Col1 inicial: \${$item['col1']}\n\n";

// 3. Ver col4 ANTES
echo "3️⃣  Col4 ANTES de agregar item:\n";
$beforeStmt = $conn->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
$beforeStmt->execute([$itemId]);
$col4Before = $beforeStmt->fetch()['col4'] ?? 0;
echo "  Col4: \$$col4Before\n\n";

// 4. Crear detalle de $5000
echo "4️⃣  Agregando detalle de \$5000...\n";
$createDetailStmt = $conn->prepare("INSERT INTO detalle_certificados 
(certificado_id, programa_codigo, descripcion_item, monto, codigo_completo, fecha_actualizacion)
VALUES (?, '01', 'Prueba \$5000', ?, ?, NOW())");
$createDetailStmt->execute([$certId, 5000, $codigoCompleto]);
$detailId = $conn->lastInsertId('detalle_certificados_id_seq');
echo "✓ Detalle ID: $detailId\n";
echo "  Monto: \$5000\n\n";

sleep(1);

// 5. Ver col4 DESPUÉS
echo "5️⃣  Col4 DESPUÉS de agregar item:\n";
$afterStmt = $conn->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
$afterStmt->execute([$itemId]);
$col4After = $afterStmt->fetch()['col4'] ?? 0;
echo "  Col4: \$$col4After\n\n";

// 6. Comparar
echo "6️⃣  RESULTADO:\n";
echo "  Col4 cambió de \$$col4Before a \$$col4After\n";
echo "  Aumento: \$" . ($col4After - $col4Before) . "\n";
echo "  Esperado: \$5000 (no \$10000)\n\n";

if($col4After == 5000) {
    echo "✅ PERFECTO! No hay duplicación\n";
    echo "   col4 = \$5000 (exactamente el monto)\n";
} elseif($col4After == 10000) {
    echo "❌ DUPLICADO! col4 = \$10000 (el doble)\n";
} else {
    echo "⚠️  VALOR INESPERADO: col4 = \$$col4After\n";
}

// 7. Limpiar
echo "\n7️⃣  Limpiando...\n";
$deleteDetailStmt = $conn->prepare("DELETE FROM detalle_certificados WHERE id = ?");
$deleteDetailStmt->execute([$detailId]);
$deleteCertStmt = $conn->prepare("DELETE FROM certificados WHERE id = ?");
$deleteCertStmt->execute([$certId]);
echo "✓ Limpieza completada\n";
?>
