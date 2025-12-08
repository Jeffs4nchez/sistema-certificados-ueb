<?php
// Crear/actualizar trigger para mantener cantidad_pendiente sincronizado
require_once 'bootstrap.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== CREAR TRIGGER: cantidad_pendiente ===\n\n";
    
    // Primero, ver si el trigger ya existe
    $check = $db->query("
        SELECT EXISTS(
            SELECT 1 FROM information_schema.triggers 
            WHERE trigger_name = 'trigger_detalle_cantidad_pendiente'
        )
    ");
    $exists = $check->fetchColumn();
    
    if ($exists) {
        echo "✓ Trigger ya existe, eliminando versión anterior...\n";
        $db->exec("DROP TRIGGER IF EXISTS trigger_detalle_cantidad_pendiente ON detalle_certificados");
    }
    
    // Crear función para el trigger
    echo "✓ Creando función del trigger...\n";
    $functionSQL = "
    CREATE OR REPLACE FUNCTION fn_trigger_detalle_cantidad_pendiente()
    RETURNS TRIGGER AS \$\$
    BEGIN
        -- Actualizar cantidad_pendiente = monto - cantidad_liquidacion
        NEW.cantidad_pendiente := NEW.monto - COALESCE(NEW.cantidad_liquidacion, 0);
        
        -- Retornar el registro modificado
        RETURN NEW;
    END;
    \$\$ LANGUAGE plpgsql;
    ";
    
    $db->exec($functionSQL);
    echo "✓ Función creada: fn_trigger_detalle_cantidad_pendiente()\n\n";
    
    // Crear trigger BEFORE INSERT OR UPDATE
    echo "✓ Creando trigger BEFORE INSERT OR UPDATE...\n";
    $triggerSQL = "
    CREATE TRIGGER trigger_detalle_cantidad_pendiente
    BEFORE INSERT OR UPDATE OF monto, cantidad_liquidacion
    ON detalle_certificados
    FOR EACH ROW
    EXECUTE FUNCTION fn_trigger_detalle_cantidad_pendiente();
    ";
    
    $db->exec($triggerSQL);
    echo "✓ Trigger creado exitosamente\n\n";
    
    // Verificar que el trigger existe
    $verify = $db->prepare("
        SELECT trigger_name, event_manipulation, event_object_table
        FROM information_schema.triggers 
        WHERE trigger_name = 'trigger_detalle_cantidad_pendiente'
    ");
    $verify->execute();
    $triggerInfo = $verify->fetch(PDO::FETCH_ASSOC);
    
    if ($triggerInfo) {
        echo "=== VERIFICACIÓN ===\n";
        echo "✓ Trigger Name: " . $triggerInfo['trigger_name'] . "\n";
        echo "✓ Event: " . $triggerInfo['event_manipulation'] . "\n";
        echo "✓ Table: " . $triggerInfo['event_object_table'] . "\n\n";
    }
    
    // Verificar que la columna existe
    $colCheck = $db->prepare("
        SELECT column_name, data_type
        FROM information_schema.columns
        WHERE table_name = 'detalle_certificados'
        AND column_name = 'cantidad_pendiente'
    ");
    $colCheck->execute();
    $colInfo = $colCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($colInfo) {
        echo "✓ Columna cantidad_pendiente: " . $colInfo['data_type'] . "\n\n";
    }
    
    // Resincronizar todos los datos
    echo "=== RESINCRONIZANDO DATOS ===\n";
    $syncSQL = "
    UPDATE detalle_certificados
    SET cantidad_pendiente = monto - COALESCE(cantidad_liquidacion, 0)
    WHERE cantidad_pendiente IS NULL
       OR cantidad_pendiente != (monto - COALESCE(cantidad_liquidacion, 0))
    ";
    
    $syncResult = $db->exec($syncSQL);
    echo "✓ Registros actualizados: $syncResult\n\n";
    
    // Mostrar estado final
    echo "=== ESTADO FINAL ===\n";
    $final = $db->query("
        SELECT 
            id,
            codigo_completo,
            monto,
            cantidad_liquidacion,
            cantidad_pendiente,
            (monto - COALESCE(cantidad_liquidacion, 0)) as debe_ser
        FROM detalle_certificados
        ORDER BY id
    ");
    
    $totalCorrect = 0;
    $totalIncorrect = 0;
    
    while ($row = $final->fetch(PDO::FETCH_ASSOC)) {
        $isCorrect = ($row['cantidad_pendiente'] == $row['debe_ser']);
        $status = $isCorrect ? "✓" : "✗";
        
        echo "$status ID {$row['id']}: {$row['codigo_completo']}\n";
        echo "  Monto: \${$row['monto']}\n";
        echo "  Liquidado: \${$row['cantidad_liquidacion']}\n";
        echo "  Pendiente: \${$row['cantidad_pendiente']} (Correcto: \${$row['debe_ser']})\n\n";
        
        $isCorrect ? $totalCorrect++ : $totalIncorrect++;
    }
    
    echo "=== RESUMEN ===\n";
    echo "✓ Registros correctos: $totalCorrect\n";
    echo "✗ Registros incorrectos: $totalIncorrect\n";
    echo "✓ TRIGGER CREADO EXITOSAMENTE\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
