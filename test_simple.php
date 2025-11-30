<?php
/**
 * Prueba Simple de Guardado de Certificado
 */
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mostrar todos los datos recibidos
    echo "<h2>✓ Formulario Recibido</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Intentar guardar
    require_once __DIR__ . '/app/Database.php';
    require_once __DIR__ . '/app/models/Certificate.php';
    
    try {
        $cert = new Certificate();
        
        // Convertir fecha si es necesario
        $dateInput = $_POST['date'] ?? date('d/m/Y');
        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateInput, $matches)) {
            $fechaElaboracion = $matches[3] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        } else {
            $fechaElaboracion = date('Y-m-d');
        }
        
        $data = [
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
        
        echo "<h2>Datos a guardar:</h2>";
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        
        $id = $cert->createCertificate($data);
        
        if ($id) {
            echo "<h2 style='color: green;'>✓✓✓ ¡CERTIFICADO GUARDADO EXITOSAMENTE!</h2>";
            echo "<p>ID: <strong>$id</strong></p>";
            
            // Verificar que se guardó
            $verify = $cert->getById($id);
            echo "<h3>Datos guardados en BD:</h3>";
            echo "<pre>";
            print_r($verify);
            echo "</pre>";
        } else {
            echo "<h2 style='color: red;'>✗ Error: No se guardó</h2>";
        }
        
    } catch (Exception $e) {
        echo "<h2 style='color: red;'>✗ EXCEPCIÓN</h2>";
        echo "<pre>";
        echo $e->getMessage() . "\n\n";
        echo $e->getTraceAsString();
        echo "</pre>";
    }
    
} else {
    // Mostrar formulario
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Prueba de Certificado</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3>Prueba Simple de Guardado de Certificado</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Institución</label>
                            <input type="text" class="form-control" name="name" value="UEB" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Sección/Memorando</label>
                            <input type="text" class="form-control" name="seccion_memorando" value="TEST">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción General</label>
                            <textarea class="form-control" name="descripcion_general">Certificado de prueba</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Unidad Ejecutora</label>
                            <input type="text" class="form-control" name="unid_ejecutora" value="UE-001">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción UE</label>
                            <input type="text" class="form-control" name="unid_desc" value="Desc UE">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Clase de Registro</label>
                            <input type="text" class="form-control" name="clase_registro" value="NORMAL">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Clase de Gasto</label>
                            <input type="text" class="form-control" name="clase_gasto" value="CORRIENTE">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo Doc Respaldo</label>
                            <input type="text" class="form-control" name="tipo_doc_respaldo" value="FACTURA">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Clase Doc Respaldo</label>
                            <input type="text" class="form-control" name="clase_doc_respaldo" value="DOCUMENTO">
                        </div>
                        
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Certificado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
