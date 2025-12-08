<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== VERIFICANDO FLUJO DE ELIMINACION ===\n";

// 1. Ver datos antes
echo "\n📌 Paso 1: Estado ANTES de eliminar certificado...\n";

$cert_antes = $db->query("
    SELECT 
        c.id, c.numero_certificado, c.monto_total, c.total_pendiente,
        COUNT(dc.id) as items,
        SUM(dc.cantidad_pendiente) as suma_pendiente
    FROM certificados c
    LEFT JOIN detalle_certificados dc ON dc.certificado_id = c.id
    WHERE c.id = 136
    GROUP BY c.id
")->fetch(PDO::FETCH_ASSOC);

if ($cert_antes) {
    printf("Certificado %s (ID %d): %d items, pendiente total: %.2f\n",
        $cert_antes['numero_certificado'],
        $cert_antes['id'],
        $cert_antes['items'],
        $cert_antes['suma_pendiente']
    );
}

// Ver presupuesto antes
$presup_antes = $db->query("
    SELECT 
        codigo_completo, col4, col3, saldo_disponible
    FROM presupuesto_items
    WHERE codigo_completo IN (
        SELECT DISTINCT codigo_completo FROM detalle_certificados
        WHERE certificado_id = 136
    )
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nPresupuestos ANTES:\n";
foreach ($presup_antes as $p) {
    printf("  %s: col4=%.2f, col3=%.2f, saldo=%.2f\n",
        substr($p['codigo_completo'], 0, 20),
        $p['col4'],
        $p['col3'],
        $p['saldo_disponible']
    );
}

// 2. Simular eliminación (sin ejecutar)
echo "\n\n📋 Simulación: Eliminar certificado 136...\n";
echo "Debería:\n";
echo "   1. Eliminar todos los items de detalle_certificados\n";
echo "   2. Trigger DELETE en cada item resta cantidad_pendiente de col4\n";
echo "   3. Eliminar certificado de certificados\n";

// 3. Verificar si hay trigger en DELETE de certificados
echo "\n\n📌 Paso 2: Verificar triggers en tabla certificados...\n";

$triggers_cert = $db->query("
    SELECT trigger_name, event_manipulation
    FROM information_schema.triggers
    WHERE trigger_schema = 'public' AND event_object_table = 'certificados'
")->fetchAll(PDO::FETCH_ASSOC);

if (empty($triggers_cert)) {
    echo "⚠️  NO HAY TRIGGERS en tabla certificados\n";
    echo "   Cuando eliminas un certificado, NO se eliminan automáticamente los items\n";
} else {
    foreach ($triggers_cert as $t) {
        printf("✅ %s (%s)\n", $t['trigger_name'], $t['event_manipulation']);
    }
}

// 4. Verificar si hay ON DELETE CASCADE
echo "\n📌 Paso 3: Verificar constraints en detalle_certificados...\n";

$constraints = $db->query("
    SELECT constraint_name, constraint_type
    FROM information_schema.table_constraints
    WHERE table_name = 'detalle_certificados' AND constraint_schema = 'public'
")->fetchAll(PDO::FETCH_ASSOC);

echo "Constraints:\n";
foreach ($constraints as $c) {
    printf("  - %s (%s)\n", $c['constraint_name'], $c['constraint_type']);
}

// Verificar relación específica
$fk = $db->query("
    SELECT rc.constraint_name, kcu.column_name, ccu.column_name as foreign_column,
           rc.update_rule, rc.delete_rule
    FROM information_schema.referential_constraints rc
    JOIN information_schema.key_column_usage kcu ON rc.constraint_name = kcu.constraint_name
    JOIN information_schema.constraint_column_usage ccu ON rc.unique_constraint_name = ccu.constraint_name
    WHERE kcu.table_name = 'detalle_certificados'
")->fetchAll(PDO::FETCH_ASSOC);

echo "\nForeign Keys (relación con certificados):\n";
foreach ($fk as $f) {
    printf("  - %s: %s -> %s\n", 
        $f['constraint_name'],
        $f['column_name'],
        $f['delete_rule']
    );
}

echo "\n✅ Análisis completado.\n";
?>
