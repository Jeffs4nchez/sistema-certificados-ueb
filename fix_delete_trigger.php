<?php
require_once __DIR__ . '/bootstrap.php';

$db = Database::getInstance()->getConnection();

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  Reparar Trigger DELETE: Agregar Logging               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

try {
    // Crear tabla de log si no existe
    $db->exec("
        CREATE TABLE IF NOT EXISTS trigger_logs (
            id SERIAL PRIMARY KEY,
            trigger_name VARCHAR(100),
            action VARCHAR(50),
            codigo_completo VARCHAR(100),
            monto_amount DECIMAL(14,2),
            col4_before DECIMAL(14,2),
            col4_after DECIMAL(14,2),
            created_at TIMESTAMP DEFAULT NOW()
        )
    ");
    
    echo "✓ Tabla trigger_logs creada\n\n";
    
    // Recr Crear nuevo trigger con logging
    echo "📌 Recre ando trigger DELETE con logging...\n";
    
    $db->exec("
        DROP TRIGGER IF EXISTS trigger_detalle_delete_col4 ON detalle_certificados CASCADE;
        DROP FUNCTION IF EXISTS fn_trigger_detalle_delete_col4() CASCADE;
    ");
    
    $db->exec("
        CREATE OR REPLACE FUNCTION fn_trigger_detalle_delete_col4()
        RETURNS TRIGGER AS \$\$
        DECLARE
            col4_val DECIMAL(14,2);
            col4_nuevo DECIMAL(14,2);
        BEGIN
            -- Obtener col4 actual
            SELECT col4 INTO col4_val FROM presupuesto_items WHERE codigo_completo = OLD.codigo_completo;
            
            -- Calcular nuevo valor
            col4_nuevo := COALESCE(col4_val, 0) - OLD.monto;
            
            -- Actualizar
            UPDATE presupuesto_items
            SET col4 = col4_nuevo,
                saldo_disponible = COALESCE(col3, 0) - col4_nuevo,
                fecha_actualizacion = NOW()
            WHERE codigo_completo = OLD.codigo_completo;
            
            -- Loguear
            INSERT INTO trigger_logs (trigger_name, action, codigo_completo, monto_amount, col4_before, col4_after)
            VALUES ('trigger_detalle_delete_col4', 'DELETE', OLD.codigo_completo, OLD.monto, col4_val, col4_nuevo);
            
            RETURN OLD;
        END;
        \$\$ LANGUAGE plpgsql;
    ");
    
    $db->exec("
        CREATE TRIGGER trigger_detalle_delete_col4
        AFTER DELETE ON detalle_certificados
        FOR EACH ROW
        EXECUTE FUNCTION fn_trigger_detalle_delete_col4();
    ");
    
    echo "✓ Trigger recreado con logging\n\n";
    
    // Crear otro certificado para test
    echo "📌 Creando certificado de test...\n";
    require_once __DIR__ . '/app/models/Certificate.php';
    $certificateModel = new Certificate();
    
    $cert_data = [
        'numero_certificado' => 'TEST-LOG-' . date('YmdHis'),
        'institucion' => 'TEST',
        'seccion_memorando' => '001',
        'descripcion' => 'Test con logging',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 1500,
        'unid_ejecutora' => 'UEB',
        'unid_desc' => 'Test',
        'clase_registro' => 'R',
        'clase_gasto' => 'G',
        'tipo_doc_respaldo' => 'D',
        'clase_doc_respaldo' => 'C',
        'usuario_id' => 1,
        'usuario_creacion' => 'admin'
    ];
    
    $cert_id = $certificateModel->createCertificate($cert_data);
    echo "✓ Certificado creado: ID=$cert_id\n\n";
    
    // Agregar items
    $items = [
        ['programa_codigo' => '01', 'subprograma_codigo' => '00', 'proyecto_codigo' => '000', 
         'actividad_codigo' => '001', 'item_codigo' => '510203', 'ubicacion_codigo' => '0200', 
         'fuente_codigo' => '001', 'organismo_codigo' => '', 'naturaleza_codigo' => '', 
         'item_descripcion' => 'Item 1', 'monto' => 1000, 
         'codigo_completo' => '01 00 000 001 001 0200 510203', 'certificado_id' => $cert_id],
        
        ['programa_codigo' => '01', 'subprograma_codigo' => '00', 'proyecto_codigo' => '000', 
         'actividad_codigo' => '001', 'item_codigo' => '510602', 'ubicacion_codigo' => '0200', 
         'fuente_codigo' => '001', 'organismo_codigo' => '', 'naturaleza_codigo' => '', 
         'item_descripcion' => 'Item 2', 'monto' => 500, 
         'codigo_completo' => '01 00 000 001 001 0200 510602', 'certificado_id' => $cert_id]
    ];
    
    foreach ($items as $item) {
        $certificateModel->createDetail($item);
    }
    
    echo "✓ Items agregados\n\n";
    
    // Borrar certificado
    echo "📌 Borrando certificado...\n";
    $stmt = $db->prepare("DELETE FROM certificados WHERE id = ?");
    $stmt->execute([$cert_id]);
    echo "✓ Certificado borrado\n\n";
    
    // Ver logs
    echo "📌 Logs del trigger:\n";
    echo "═══════════════════════════════════════════════════════════\n";
    $stmt = $db->query("SELECT * FROM trigger_logs ORDER BY created_at DESC LIMIT 10");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($logs as $log) {
        echo "  Trigger: {$log['trigger_name']}\n";
        echo "    Código: {$log['codigo_completo']}\n";
        echo "    Monto: \${$log['monto_amount']}\n";
        echo "    col4 ANTES: \${$log['col4_before']}\n";
        echo "    col4 DESPUÉS: \${$log['col4_after']}\n";
        echo "    Hora: {$log['created_at']}\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
