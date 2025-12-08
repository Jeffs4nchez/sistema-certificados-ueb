<?php
/**
 * Script para analizar estructura de tablas y relaciones
 */

$host = 'localhost';
$port = '5432';
$user = 'postgres';
$pass = 'jeffo2003';
$database = 'certificados_sistema';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "📊 ANÁLISIS DE ESTRUCTURA DE TABLAS\n";
    echo str_repeat("=", 100) . "\n\n";
    
    // Tabla: certificados
    echo "1️⃣ TABLA: certificados\n";
    echo str_repeat("-", 100) . "\n";
    $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'certificados' ORDER BY ordinal_position;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $col) {
        echo sprintf("   %-30s %s (%s)\n", $col['column_name'], $col['data_type'], $col['is_nullable']);
    }
    
    // Tabla: detalle_certificados
    echo "\n2️⃣ TABLA: detalle_certificados\n";
    echo str_repeat("-", 100) . "\n";
    $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'detalle_certificados' ORDER BY ordinal_position;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $col) {
        echo sprintf("   %-30s %s (%s)\n", $col['column_name'], $col['data_type'], $col['is_nullable']);
    }
    
    // Tabla: presupuesto_items
    echo "\n3️⃣ TABLA: presupuesto_items\n";
    echo str_repeat("-", 100) . "\n";
    $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'presupuesto_items' ORDER BY ordinal_position;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $col) {
        echo sprintf("   %-30s %s (%s)\n", $col['column_name'], $col['data_type'], $col['is_nullable']);
    }
    
    // Ejemplos de datos
    echo "\n\n📋 DATOS DE EJEMPLO\n";
    echo str_repeat("=", 100) . "\n\n";
    
    echo "Certificados recientes:\n";
    $sql = "SELECT id, numero_certificado, monto_total, codigo_completo FROM certificados LIMIT 3;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $row) {
        echo "  ID: {$row['id']}, Cert: {$row['numero_certificado']}, Monto: {$row['monto_total']}, Código: {$row['codigo_completo']}\n";
    }
    
    echo "\nItems de certificados:\n";
    $sql = "SELECT id, certificado_id, codigo_completo, monto, cantidad_liquidacion FROM detalle_certificados LIMIT 5;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $row) {
        echo "  ID: {$row['id']}, Cert: {$row['certificado_id']}, Código: {$row['codigo_completo']}, Monto: {$row['monto']}, Liquidado: {$row['cantidad_liquidacion']}\n";
    }
    
    echo "\nPresupuestos:\n";
    $sql = "SELECT id, codigo_completo, col1, col3, col4, saldo_disponible FROM presupuesto_items LIMIT 5;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $row) {
        echo "  ID: {$row['id']}, Código: {$row['codigo_completo']}, Col1: {$row['col1']}, Col3: {$row['col3']}, Col4: {$row['col4']}, Saldo: {$row['saldo_disponible']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
