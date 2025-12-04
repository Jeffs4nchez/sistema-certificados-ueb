<?php
require_once __DIR__ . '/app/Database.php';
$db = Database::getInstance();
$conn = $db->getConnection();

echo "=== PRUEBA DE TRIGGERS SIN DUPLICADOS ===\n\n";

// 1. Crear un certificado test
echo "1️⃣  Creando certificado test...\n";
$createCertStmt = $conn->prepare("INSERT INTO certificados 
(numero_certificado, institucion, seccion_memorando, descripcion, fecha_elaboracion, monto_total, usuario_id, usuario_creacion)
VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)");

$certNum = 'TEST-' . date('YmdHis');
$createCertStmt->execute([$certNum, 'TEST', 'TEST', 'Certificado de prueba', 500, 1, 'test']);
$certId = $conn->lastInsertId('certificados_id_seq');

echo "✓ Certificado creado: ID=" . $certId . "\n\n";

// 2. Obtener un presupuesto_items válido
echo "2️⃣  Buscando presupuesto_items para el test...\n";
$itemQuery = "SELECT codigo_completo, col4, col1 FROM presupuesto_items LIMIT 1";
$itemStmt = $conn->query($itemQuery);
$testItem = $itemStmt->fetch();

$codigoCompleto = $testItem['codigo_completo'];
$col4Before = $testItem['col4'] ?? 0;
$col1 = $testItem['col1'] ?? 0;

echo "✓ Item seleccionado: " . $codigoCompleto . "\n";
echo "  Col4 antes: " . $col4Before . "\n";
echo "  Col1 (Disponible): " . $col1 . "\n\n";

// 3. Crear un detalle_certificados (esto dispara el trigger INSERT)
echo "3️⃣  Agregando item de $500 al certificado...\n";
$createDetailStmt = $conn->prepare("INSERT INTO detalle_certificados 
(certificado_id, programa_codigo, descripcion_item, monto, codigo_completo, fecha_actualizacion)
VALUES (?, '01', 'Prueba unitaria', ?, ?, NOW())");

$createDetailStmt->execute([$certId, 500, $codigoCompleto]);
$detailId = $conn->lastInsertId('detalle_certificados_id_seq');

echo "✓ Item agregado: ID=" . $detailId . "\n";

// Verificar col4 después de INSERT
$checkAfterInsert = $conn->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
$checkAfterInsert->execute([$codigoCompleto]);
$col4After = $checkAfterInsert->fetch()['col4'];

echo "  Col4 después de INSERT: " . $col4After . " (esperado: " . ($col4Before + 500) . ")\n";

if($col4After == $col4Before + 500) {
    echo "  ✓ INSERT trigger funcionó correctamente\n\n";
} else {
    echo "  ✗ ERROR: INSERT trigger no funcionó\n";
    echo "    Diferencia esperada: 500, obtenida: " . ($col4After - $col4Before) . "\n\n";
}

// 4. Eliminar el detalle (esto dispara el trigger DELETE)
echo "4️⃣  Eliminando el item del certificado...\n";
$deleteDetailStmt = $conn->prepare("DELETE FROM detalle_certificados WHERE id = ?");
$deleteDetailStmt->execute([$detailId]);

echo "✓ Item eliminado\n";

// Verificar col4 después de DELETE
$checkAfterDelete = $conn->prepare("SELECT col4 FROM presupuesto_items WHERE codigo_completo = ?");
$checkAfterDelete->execute([$codigoCompleto]);
$col4Final = $checkAfterDelete->fetch()['col4'];

echo "  Col4 después de DELETE: " . $col4Final . " (esperado: " . $col4Before . ")\n";

if($col4Final == $col4Before) {
    echo "  ✓ DELETE trigger funcionó correctamente\n\n";
} else if($col4Final < 0) {
    echo "  ✗ ERROR: Col4 es negativa! (" . $col4Final . ")\n";
    echo "    Esto significa que el trigger DELETE está restando cuando no debería\n\n";
} else {
    echo "  ✗ ERROR: DELETE trigger no restauró el valor correctamente\n";
    echo "    Diferencia: " . ($col4Final - $col4Before) . "\n\n";
}

// 5. Limpiar: eliminar el certificado test
echo "5️⃣  Limpiando certificado test...\n";
$deleteCertStmt = $conn->prepare("DELETE FROM certificados WHERE id = ?");
$deleteCertStmt->execute([$certId]);
echo "✓ Certificado eliminado\n\n";

// Resultado final
echo "=== RESULTADO ===\n";
if($col4After == $col4Before + 500 && $col4Final == $col4Before) {
    echo "✅ TODOS LOS TRIGGERS FUNCIONAN CORRECTAMENTE\n";
    echo "✅ El problema de col4 negativa ha sido RESUELTO\n";
} else {
    echo "❌ Aún hay problemas con los triggers\n";
}
?>
