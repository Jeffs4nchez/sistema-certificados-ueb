<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

// Verificar estructura actual
echo "\n=== ESTRUCTURA ACTUAL ===\n";

// Checar un certificado existente
$stmt = $db->query("
    SELECT 
        c.id, c.numero_certificado, c.monto_total, c.total_pendiente,
        COUNT(dc.id) as total_items,
        SUM(dc.monto) as suma_montos,
        SUM(dc.cantidad_liquidacion) as suma_liquidaciones,
        SUM(dc.cantidad_pendiente) as suma_cantidad_pendiente
    FROM certificados c
    LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
    WHERE c.id > 0
    GROUP BY c.id
    LIMIT 5
");

$certificados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n📋 CERTIFICADOS:\n";
echo str_repeat("-", 120) . "\n";
printf("%-5s | %-15s | %-12s | %-12s | %-10s | %-12s | %-15s | %-15s\n", 
    "ID", "NUMERO", "MONTO_TOTAL", "TOTAL_PEND", "ITEMS", "SUMA_MONTOS", "SUMA_LIQUIDADO", "SUMA_PENDIENTE");
echo str_repeat("-", 120) . "\n";

foreach ($certificados as $row) {
    printf("%-5s | %-15s | %-12.2f | %-12.2f | %-10s | %-12.2f | %-15.2f | %-15.2f\n",
        $row['id'] ?? '-',
        $row['numero_certificado'] ?? '-',
        $row['monto_total'] ?? 0,
        $row['total_pendiente'] ?? 0,
        $row['total_items'] ?? 0,
        $row['suma_montos'] ?? 0,
        $row['suma_liquidaciones'] ?? 0,
        $row['suma_cantidad_pendiente'] ?? 0
    );
}

// Detalles de un certificado
echo "\n\n📝 DETALLES DE CERTIFICADOS:\n";

echo str_repeat("-", 130) . "\n";
printf("%-5s | %-5s | %-30s | %-10s | %-12s | %-12s | %-15s\n",
    "ID", "CERT", "DESCRIPCION", "MONTO", "LIQUIDADO", "PENDIENTE_BD", "PENDIENTE_CALC");
echo str_repeat("-", 130) . "\n";

foreach ($db->query("
    SELECT 
        dc.id, dc.certificado_id, dc.descripcion_item, 
        dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente,
        (dc.monto - COALESCE(dc.cantidad_liquidacion, 0)) as pendiente_calculada
    FROM detalle_certificados dc
    ORDER BY dc.certificado_id DESC, dc.id ASC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC) as $row) {
    printf("%-5s | %-5s | %-30s | %-10.2f | %-12.2f | %-12.2f | %-15.2f\n",
        $row['id'],
        $row['certificado_id'],
        substr($row['descripcion_item'] ?? '', 0, 28),
        $row['monto'],
        $row['cantidad_liquidacion'] ?? 0,
        $row['cantidad_pendiente'] ?? 0,
        $row['pendiente_calculada']
    );
}

echo "\n✅ Verificación completada.\n";
?>
