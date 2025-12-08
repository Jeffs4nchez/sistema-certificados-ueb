<?php
/**
 * Verificar el flujo actual de liquidaciones por item
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
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    
    echo "📊 ANÁLISIS DE LIQUIDACIONES POR ITEM\n";
    echo str_repeat("=", 120) . "\n\n";
    
    // Ver estructura de detalle_certificados
    echo "1️⃣ CAMPOS EN DETALLE_CERTIFICADOS:\n";
    $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = 'detalle_certificados' ORDER BY ordinal_position;";
    $stmt = $pdo->query($sql);
    foreach ($stmt->fetchAll() as $col) {
        echo "   • " . $col['column_name'] . "\n";
    }
    
    echo "\n2️⃣ EJEMPLO DE DATOS CON LIQUIDACIONES:\n";
    echo str_repeat("-", 120) . "\n";
    
    $sql = "SELECT 
        dc.id,
        dc.certificado_id,
        dc.codigo_completo,
        dc.monto,
        dc.cantidad_liquidacion,
        dc.cantidad_pendiente,
        dc.fecha_actualizacion,
        pi.col4,
        pi.col3,
        pi.saldo_disponible
    FROM detalle_certificados dc
    LEFT JOIN presupuesto_items pi ON dc.codigo_completo = pi.codigo_completo
    ORDER BY dc.certificado_id, dc.id
    LIMIT 10;";
    
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();
    
    if (!empty($rows)) {
        foreach ($rows as $row) {
            echo sprintf(
                "Item ID: %d | Código: %s | Monto: %.2f | Liquidado: %.2f | Pendiente: %.2f\n",
                $row['id'],
                $row['codigo_completo'],
                $row['monto'],
                $row['cantidad_liquidacion'] ?? 0,
                $row['cantidad_pendiente'] ?? 0
            );
            echo sprintf(
                "  → Presupuesto col4: %.2f | col3: %.2f | Saldo: %.2f\n\n",
                $row['col4'] ?? 0,
                $row['col3'] ?? 0,
                $row['saldo_disponible'] ?? 0
            );
        }
    } else {
        echo "Sin datos de detalle aún\n";
    }
    
    echo str_repeat("=", 120) . "\n";
    echo "❓ PREGUNTA: ¿Cuando un item tiene liquidación parcial, debería:\n";
    echo "   A) col4 = SUMA de TODOS los montos de items (sin cambiar por liquidación)\n";
    echo "   B) col4 = SUMA de TODOS los montos menos lo liquidado (col4 baja con liquidación)\n";
    echo "   C) col4 = mantener monto original, crear otra columna para liquidado\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
