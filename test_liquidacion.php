<?php
/**
 * Script de prueba para liquidación con memorando
 */

// Cargar bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "=== PRUEBA DE LIQUIDACIÓN CON MEMORANDO ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // 1. Obtener último certificado
    echo "1. Buscando último certificado...\n";
    $stmt = $db->query("SELECT * FROM certificados ORDER BY id DESC LIMIT 1");
    $certificado = $stmt->fetch();
    
    if (!$certificado) {
        echo "❌ No hay certificados en la BD\n";
        exit(1);
    }
    
    echo "✓ Certificado encontrado: ID={$certificado['id']}, Número={$certificado['numero_certificado']}\n\n";
    
    // 2. Obtener items del certificado
    echo "2. Buscando items del certificado...\n";
    $stmt = $db->prepare("SELECT * FROM detalle_certificados WHERE certificado_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$certificado['id']]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo "❌ El certificado no tiene items\n";
        exit(1);
    }
    
    echo "✓ Item encontrado: ID={$item['id']}, Descripción={$item['descripcion_item']}\n";
    echo "   Monto: {$item['monto']}\n";
    echo "   Cantidad Liquidación ANTES: {$item['cantidad_liquidacion']}\n";
    echo "   Memorando ANTES: {$item['memorando']}\n\n";
    
    // 3. Simular liquidación con memorando
    echo "3. Actualizando liquidación con memorando...\n";
    $cantidadLiquidacion = floatval($item['monto']);
    $memorando = "PRUEBA - Comprobante #" . date('YmdHis');
    
    $updateStmt = $db->prepare("UPDATE detalle_certificados SET cantidad_liquidacion = ?, memorando = ? WHERE id = ?");
    $resultado = $updateStmt->execute([$cantidadLiquidacion, $memorando, $item['id']]);
    
    if ($resultado) {
        echo "✓ Actualización exitosa\n\n";
    } else {
        echo "❌ Error en la actualización\n";
        exit(1);
    }
    
    // 4. Verificar que se guardó
    echo "4. Verificando que se guardó correctamente...\n";
    $stmt = $db->prepare("SELECT * FROM detalle_certificados WHERE id = ?");
    $stmt->execute([$item['id']]);
    $itemActualizado = $stmt->fetch();
    
    echo "   Cantidad Liquidación DESPUÉS: {$itemActualizado['cantidad_liquidacion']}\n";
    echo "   Memorando DESPUÉS: {$itemActualizado['memorando']}\n\n";
    
    // 5. Validar resultados
    echo "5. Validación final:\n";
    $cantidadOk = abs($itemActualizado['cantidad_liquidacion'] - $cantidadLiquidacion) < 0.01;
    $memorandoOk = $itemActualizado['memorando'] === $memorando;
    
    if ($cantidadOk && $memorandoOk) {
        echo "✅ ¡¡¡ TODO FUNCIONA CORRECTAMENTE !!!\n";
        echo "   ✓ Cantidad liquidación guardada: $" . number_format($itemActualizado['cantidad_liquidacion'], 2) . "\n";
        echo "   ✓ Memorando guardado: {$itemActualizado['memorando']}\n";
    } else {
        echo "❌ Hay problemas:\n";
        if (!$cantidadOk) echo "   ✗ Cantidad liquidación NO coincide\n";
        if (!$memorandoOk) echo "   ✗ Memorando NO se guardó\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== FIN DE LA PRUEBA ===\n";
?>
