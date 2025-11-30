<?php
// Test para verificar si el certificado se está guardando

require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/models/Certificate.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Simular datos de prueba
    $testData = [
        'numero_certificado' => 'CERT-TEST-' . date('His'),
        'institucion' => 'UEB',
        'seccion_memorando' => 'TEST',
        'descripcion' => 'Certificado de prueba',
        'fecha_elaboracion' => date('Y-m-d'),
        'monto_total' => 1000,
        'unid_ejecutora' => 'UE-001',
        'unid_desc' => 'Descripción de prueba',
        'clase_registro' => 'NORMAL',
        'clase_gasto' => 'CORRIENTE',
        'tipo_doc_respaldo' => 'FACTURA',
        'clase_doc_respaldo' => 'DOCUMENTO'
    ];
    
    echo "<pre>";
    echo "=== TEST DE GUARDADO DE CERTIFICADO ===\n";
    echo "Datos a guardar:\n";
    print_r($testData);
    echo "\n";
    
    // Intentar insertar
    $stmt = $db->prepare("
        INSERT INTO certificados (
            numero_certificado, institucion, seccion_memorando, descripcion, 
            fecha_elaboracion, monto_total, unid_ejecutora, unid_desc, 
            clase_registro, clase_gasto, tipo_doc_respaldo, clase_doc_respaldo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $testData['numero_certificado'],
        $testData['institucion'],
        $testData['seccion_memorando'],
        $testData['descripcion'],
        $testData['fecha_elaboracion'],
        $testData['monto_total'],
        $testData['unid_ejecutora'],
        $testData['unid_desc'],
        $testData['clase_registro'],
        $testData['clase_gasto'],
        $testData['tipo_doc_respaldo'],
        $testData['clase_doc_respaldo']
    ]);
    
    if ($result) {
        $id = $db->lastInsertId();
        echo "✓ Certificado insertado exitosamente\n";
        echo "ID: $id\n";
        
        // Verificar que se guardó
        $verify = $db->prepare("SELECT * FROM certificados WHERE id = ?");
        $verify->execute([$id]);
        $cert = $verify->fetch();
        
        if ($cert) {
            echo "\n✓ Verificación exitosa:\n";
            print_r($cert);
        } else {
            echo "\n✗ Error: No se encontró el certificado guardado\n";
        }
    } else {
        echo "✗ Error al insertar\n";
        echo "Errores: " . print_r($stmt->errorInfo(), true);
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<pre>";
    echo "✗ EXCEPCIÓN: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
