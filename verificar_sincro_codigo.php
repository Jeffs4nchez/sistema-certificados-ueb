<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== VERIFICANDO DATOS Y SINCRONIZACIÓN ===\n";

// Ver items con sus códigos
echo "\n📌 Items del certificado 136:\n";

$items = $db->query("
    SELECT 
        dc.id, dc.certificado_id, dc.codigo_completo, dc.descripcion_item,
        dc.monto, dc.cantidad_liquidacion, dc.cantidad_pendiente
    FROM detalle_certificados dc
    WHERE dc.certificado_id = 136
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    echo "❌ No hay items para el certificado 136\n";
} else {
    foreach ($items as $item) {
        printf("  ID %d: %s (código: %s) - pendiente: %.2f\n",
            $item['id'],
            substr($item['descripcion_item'], 0, 30),
            $item['codigo_completo'],
            $item['cantidad_pendiente']
        );
    }
}

// Verificar presupuesto con esos códigos
echo "\n📌 Presupuesto para esos códigos:\n";

foreach ($items as $item) {
    $presup = $db->query(
        "SELECT id, codigo_completo, col4, col3, saldo_disponible FROM presupuesto_items WHERE codigo_completo = ?",
        [$item['codigo_completo']]
    )->fetch(PDO::FETCH_ASSOC);
    
    if ($presup) {
        printf("  %s → col4=%.2f (debería ser pendiente=%.2f)\n",
            $item['codigo_completo'],
            $presup['col4'],
            $item['cantidad_pendiente']
        );
    } else {
        printf("  %s → ❌ NO ENCONTRADO en presupuesto_items\n",
            $item['codigo_completo']
        );
    }
}

echo "\n✅ Verificación completada.\n";
?>
