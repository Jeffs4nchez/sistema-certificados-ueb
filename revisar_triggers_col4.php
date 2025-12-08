<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n=== REVISANDO TRIGGERS DE COL4 ===\n";

// Ver las funciones actuales
echo "\n📌 Funciones actuales:\n";

$functions = ['fn_trigger_insert_col4', 'fn_trigger_delete_col4'];

foreach ($functions as $fn) {
    echo "\n▼ $fn():\n";
    echo str_repeat("-", 100) . "\n";
    
    try {
        $result = $db->query("SELECT pg_get_functiondef('$fn'::regprocedure) as def")->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $code = $result['def'];
            // Mostrar primeras 300 caracteres
            echo substr($code, 0, 500) . "...\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n\n📌 Problema identificado:\n";
echo "  Los triggers usan:\n";
echo "    - trigger_insert_col4: += cantidad_pendiente (INCORRECTO)\n";
echo "    - trigger_delete_col4: -= cantidad_pendiente (INCORRECTO)\n";
echo "\n  Deberían usar:\n";
echo "    - trigger_insert_col4: += (monto - cantidad_pendiente) = lo liquidado\n";
echo "    - trigger_delete_col4: -= (monto - cantidad_pendiente) = lo liquidado\n";

?>
