<?php
/**
 * Verificar las relaciones exactas entre tablas
 */

$host = 'localhost';
$port = '5432';
$user = 'postgres';
$pass = 'jeffo2003';
$database = 'certificados_sistema';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    
    echo "📊 RELACIONES ENTRE TABLAS\n";
    echo str_repeat("=", 100) . "\n\n";
    
    echo "🔗 Un certificado con sus items:\n";
    $sql = "SELECT c.id, c.numero_certificado, c.monto_total, 
            dc.id as item_id, dc.codigo_completo, dc.monto, dc.cantidad_liquidacion
            FROM certificados c
            LEFT JOIN detalle_certificados dc ON c.id = dc.certificado_id
            LIMIT 1;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $row) {
        echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
    }
    
    echo "\n🔗 Items y sus presupuestos:\n";
    $sql = "SELECT dc.id, dc.codigo_completo, dc.monto, 
            pi.id as presupuesto_id, pi.codigo_completo as pres_codigo, pi.col4, pi.col3, pi.saldo_disponible
            FROM detalle_certificados dc
            LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
            LIMIT 3;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $row) {
        echo json_encode($row, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
