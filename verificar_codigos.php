<?php
/**
 * Verificar si existen los códigos de presupuesto
 */

require_once 'app/Database.php';

$db = Database::getInstance()->getConnection();

$codigos = [
    '01 00 000 001 001 0200 510510',
    '01 00 000 002 001 0200 510518',
    '01 00 000 003 001 0200 510204'
];

echo "Verificando códigos de presupuesto:\n\n";

foreach ($codigos as $codigo) {
    $stmt = $db->prepare("SELECT id, col4 FROM presupuesto_items WHERE codigo_completo = ?");
    $stmt->execute([$codigo]);
    $row = $stmt->fetch();
    
    if ($row) {
        echo "✅ Código: $codigo\n";
        echo "   ID: {$row['id']}, Col4: {$row['col4']}\n\n";
    } else {
        echo "❌ Código NO EXISTE: $codigo\n\n";
    }
}
?>
