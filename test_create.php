<?php
/**
 * Archivo de prueba para simular envío de formulario
 */

// Simular datos POST
$_POST = [
    'numero' => 'CERT-TEST-001',
    'name' => 'UEB',
    'seccion_memorando' => 'TEST',
    'descripcion_general' => 'Certificado de prueba',
    'date' => '28/11/2025',
    'unid_ejecutora' => 'UE-001',
    'unid_desc' => 'Descripción UE',
    'clase_registro' => 'NORMAL',
    'clase_gasto' => 'CORRIENTE',
    'tipo_doc_respaldo' => 'FACTURA',
    'clase_doc_respaldo' => 'DOCUMENTO',
    'items_data' => json_encode([
        [
            'id' => time(),
            'programa_codigo' => 'PROG-001',
            'subprograma_codigo' => 'SUBPROG-001',
            'proyecto_codigo' => 'PROY-001',
            'actividad_codigo' => 'ACT-001',
            'item_codigo' => 'ITEM-001',
            'ubicacion_codigo' => 'UBI-001',
            'fuente_codigo' => 'FUENTE-001',
            'organismo_codigo' => 'ORG-001',
            'naturaleza_codigo' => 'NAT-001',
            'item_descripcion' => 'Item de prueba',
            'monto' => 1000.00,
            'codigo_completo' => 'PROG-001 SUBPROG-001 PROY-001 ACT-001 FUENTE-001 UBI-001 ITEM-001'
        ]
    ])
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Incluir el controlador
require_once __DIR__ . '/app/Database.php';
require_once __DIR__ . '/app/models/Certificate.php';
require_once __DIR__ . '/app/models/CertificateItem.php';

// Inicializar sesión
session_start();

echo "<h2>Test de Creación de Certificado</h2>";
echo "<pre>";
echo "POST DATA:\n";
print_r($_POST);
echo "\n\n";

try {
    require_once __DIR__ . '/app/controllers/CertificateController.php';
    $controller = new CertificateController();
    
    // Simular llamada a createAction
    echo "Llamando a createAction...\n";
    
    // Copiar la lógica del createAction aquí para debug
    $certificateModel = new Certificate();
    $certificateItemModel = new CertificateItem(Database::getInstance()->getConnection());
    
    // Datos del certificado maestro
    $dateInput = $_POST['date'] ?? date('d/m/Y');
    if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateInput, $matches)) {
        $fechaElaboracion = $matches[3] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
    } else {
        $fechaElaboracion = date('Y-m-d');
    }
    
    $certificateData = [
        'numero_certificado' => $_POST['numero'] ?? 'CERT-' . date('YmdHis'),
        'institucion' => $_POST['name'] ?? '',
        'seccion_memorando' => $_POST['seccion_memorando'] ?? '',
        'descripcion' => $_POST['descripcion_general'] ?? '',
        'fecha_elaboracion' => $fechaElaboracion,
        'monto_total' => 0,
        'unid_ejecutora' => $_POST['unid_ejecutora'] ?? '',
        'unid_desc' => $_POST['unid_desc'] ?? '',
        'clase_registro' => $_POST['clase_registro'] ?? '',
        'clase_gasto' => $_POST['clase_gasto'] ?? '',
        'tipo_doc_respaldo' => $_POST['tipo_doc_respaldo'] ?? '',
        'clase_doc_respaldo' => $_POST['clase_doc_respaldo'] ?? '',
    ];
    
    echo "✓ Datos del certificado preparados:\n";
    print_r($certificateData);
    echo "\n";
    
    $itemsJson = $_POST['items_data'] ?? '[]';
    $items = json_decode($itemsJson, true);
    
    echo "✓ Items decodificados:\n";
    print_r($items);
    echo "\n";
    
    // Calcular monto
    $montoTotal = 0;
    foreach ($items as $item) {
        $montoTotal += floatval($item['monto'] ?? 0);
    }
    $certificateData['monto_total'] = $montoTotal;
    
    echo "✓ Monto total: " . $montoTotal . "\n\n";
    
    // Crear certificado
    echo "Creando certificado...\n";
    $certificateId = $certificateModel->createCertificate($certificateData);
    echo "✓ Certificado creado con ID: $certificateId\n\n";
    
    // Crear items
    foreach ($items as $item) {
        $codigoCompleto = implode(' ', [
            $item['programa_codigo'] ?? '',
            $item['subprograma_codigo'] ?? '',
            $item['proyecto_codigo'] ?? '',
            $item['actividad_codigo'] ?? '',
            $item['fuente_codigo'] ?? '',
            $item['ubicacion_codigo'] ?? '',
            $item['item_codigo'] ?? ''
        ]);
        
        $detailData = [
            'certificado_id' => $certificateId,
            'programa_codigo' => trim($item['programa_codigo'] ?? ''),
            'subprograma_codigo' => trim($item['subprograma_codigo'] ?? ''),
            'proyecto_codigo' => trim($item['proyecto_codigo'] ?? ''),
            'actividad_codigo' => trim($item['actividad_codigo'] ?? ''),
            'item_codigo' => trim($item['item_codigo'] ?? ''),
            'ubicacion_codigo' => trim($item['ubicacion_codigo'] ?? ''),
            'fuente_codigo' => trim($item['fuente_codigo'] ?? ''),
            'organismo_codigo' => trim($item['organismo_codigo'] ?? ''),
            'naturaleza_codigo' => trim($item['naturaleza_codigo'] ?? ''),
            'descripcion_item' => $item['item_descripcion'] ?? '',
            'monto' => floatval($item['monto'] ?? 0),
            'codigo_completo' => $codigoCompleto
        ];
        
        echo "Creando detalle:\n";
        print_r($detailData);
        echo "\n";
        
        $certificateModel->createDetail($detailData);
        echo "✓ Detalle creado\n\n";
    }
    
    echo "✓✓✓ ¡ÉXITO! Certificado guardado correctamente\n";
    
} catch (Exception $e) {
    echo "✗✗✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
?>
