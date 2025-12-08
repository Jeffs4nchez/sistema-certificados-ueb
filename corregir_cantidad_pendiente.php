<?php
/**
 * CORRECCIÓN: Arreglar cantidad_pendiente en todos los items existentes
 * 
 * Fórmula correcta:
 * cantidad_pendiente = monto - cantidad_liquidacion
 */

require_once 'app/Database.php';

echo "\n" . str_repeat("=", 80) . "\n";
echo "CORRECCIÓN DE cantidad_pendiente\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. OBTENER ITEMS CON cantidad_pendiente INCORRECTA
    echo "1️⃣  BUSCANDO ITEMS CON DATOS INCORRECTOS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $db->query("
        SELECT id, monto, cantidad_liquidacion, cantidad_pendiente,
               (monto - COALESCE(cantidad_liquidacion, 0)) as pendiente_correcto
        FROM detalle_certificados
        WHERE cantidad_pendiente != (monto - COALESCE(cantidad_liquidacion, 0))
           OR cantidad_pendiente IS NULL
        ORDER BY id DESC
    ");
    
    $incorrectos = $stmt->fetchAll();
    
    if (empty($incorrectos)) {
        echo "✅ TODOS LOS ITEMS TIENEN cantidad_pendiente CORRECTA\n\n";
        exit(0);
    }
    
    echo "⚠️  ENCONTRADOS: " . count($incorrectos) . " items con datos incorrectos\n\n";
    
    // 2. MOSTRAR EJEMPLOS
    echo "Ejemplos de items incorrectos:\n";
    echo str_repeat("-", 80) . "\n";
    $count = 0;
    foreach ($incorrectos as $item) {
        echo "ID {$item['id']}: monto=" . number_format($item['monto'], 2) . 
             ", liquidacion=" . number_format($item['cantidad_liquidacion'] ?? 0, 2) .
             ", pendiente_actual=" . number_format($item['cantidad_pendiente'] ?? 0, 2) .
             ", pendiente_correcto=" . number_format($item['pendiente_correcto'], 2) . "\n";
        $count++;
        if ($count >= 5) {
            echo "... y " . (count($incorrectos) - 5) . " más\n";
            break;
        }
    }
    
    echo "\n";
    
    // 3. APLICAR CORRECCIÓN
    echo "2️⃣  APLICANDO CORRECCIÓN...\n";
    echo str_repeat("-", 80) . "\n";
    
    $updateStmt = $db->prepare("
        UPDATE detalle_certificados
        SET cantidad_pendiente = monto - COALESCE(cantidad_liquidacion, 0),
            fecha_actualizacion = NOW()
        WHERE cantidad_pendiente != (monto - COALESCE(cantidad_liquidacion, 0))
           OR cantidad_pendiente IS NULL
    ");
    
    if ($updateStmt->execute()) {
        echo "✅ CORRECCIÓN APLICADA\n\n";
    } else {
        echo "❌ ERROR EN UPDATE\n";
        exit(1);
    }
    
    // 4. VERIFICAR CORRECCIÓN
    echo "3️⃣  VERIFICANDO CORRECCIÓN...\n";
    echo str_repeat("-", 80) . "\n";
    
    $checkStmt = $db->query("
        SELECT COUNT(*) as total,
               SUM(CASE WHEN cantidad_pendiente = (monto - COALESCE(cantidad_liquidacion, 0)) THEN 1 ELSE 0 END) as correctos,
               SUM(CASE WHEN cantidad_pendiente != (monto - COALESCE(cantidad_liquidacion, 0)) THEN 1 ELSE 0 END) as incorrectos
        FROM detalle_certificados
    ");
    
    $resultado = $checkStmt->fetch();
    
    echo "Total items: " . $resultado['total'] . "\n";
    echo "Items correctos: " . $resultado['correctos'] . " ✅\n";
    echo "Items incorrectos: " . ($resultado['incorrectos'] ?? 0) . " " . (($resultado['incorrectos'] ?? 0) == 0 ? "✅" : "❌") . "\n\n";
    
    // 5. RECALCULAR TOTALES EN CERTIFICADOS
    echo "4️⃣  RECALCULANDO TOTALES EN CERTIFICADOS...\n";
    echo str_repeat("-", 80) . "\n";
    
    $recalcStmt = $db->prepare("
        UPDATE certificados
        SET 
            total_liquidado = COALESCE((
                SELECT SUM(cantidad_liquidacion)
                FROM detalle_certificados
                WHERE certificado_id = certificados.id
            ), 0),
            total_pendiente = COALESCE((
                SELECT SUM(cantidad_pendiente)
                FROM detalle_certificados
                WHERE certificado_id = certificados.id
            ), 0),
            fecha_actualizacion = NOW()
        WHERE id IN (
            SELECT DISTINCT certificado_id FROM detalle_certificados
        )
    ");
    
    if ($recalcStmt->execute()) {
        echo "✅ TOTALES EN CERTIFICADOS RECALCULADOS\n\n";
    } else {
        echo "❌ ERROR EN RECALCULO\n";
        exit(1);
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "✅ CORRECCIÓN COMPLETADA EXITOSAMENTE\n";
    echo str_repeat("=", 80) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
