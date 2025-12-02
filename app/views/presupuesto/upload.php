<?php
/**
 * Vista: Subida de Presupuesto CSV
 */

// Extraer mensajes de sesión si existen
$message = '';
$type = '';

if (!empty($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $type = 'success';
    unset($_SESSION['success']);
} elseif (!empty($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $type = 'error';
    unset($_SESSION['error']);
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">Subir Presupuesto CSV</h1>
            <p class="text-muted">Importar datos de presupuesto desde archivo CSV</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=presupuesto-list" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> Ver Presupuestos
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $type === 'error' ? 'danger' : ($type === 'success' ? 'success' : 'warning'); ?> alert-dismissible fade show" role="alert">
            <strong><?php echo ucfirst($type); ?>:</strong>
            <pre class="mb-0" style="white-space: pre-wrap; font-size: 0.9rem;"><?php echo htmlspecialchars($message); ?></pre>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
                    <h5 class="mb-0" style="color: white !important;"><i class="fas fa-upload"></i> Cargar Archivo CSV</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="index.php?action=presupuesto-upload" enctype="multipart/form-data" id="uploadForm">
                        <div class="mb-4">
                            <label for="csv_file" class="form-label fw-bold">Seleccionar archivo CSV</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="csv_file" name="csv_file" 
                                       accept=".csv" required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-upload"></i> Importar
                                </button>
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                <strong>Formatos aceptados:</strong> CSV<br>
                                <strong>Tamaño máximo:</strong> 10 MB<br>
                                <strong>Codificación:</strong> UTF-8, CP1252 o ISO-8859-1
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Formato esperado del CSV:</h6>
                            <p class="mb-0">El archivo debe contener las siguientes columnas (nombres aproximados):</p>
                            <ul class="mb-0 mt-2">
                                <li><strong>PROGRAMA</strong> - Descripción del programa</li>
                                <li><strong>ACTIVIDAD</strong> - Descripción de actividad</li>
                                <li><strong>FUENTE</strong> - Fuente de financiamiento</li>
                                <li><strong>GEOGRAFICO</strong> - Área geográfica</li>
                                <li><strong>ITEM</strong> - Descripción del item</li>
                                <li><strong>Columnas numéricas</strong> - Col1-Col10, Col20 (montos)</li>
                                <li><strong>CODIGOG1-5</strong> - Códigos de clasificación</li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
                    <h5 class="mb-0" style="color: white !important;"><i class="fas fa-download"></i> Descargar Plantilla</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Usa esta plantilla como referencia para preparar tu archivo CSV:</p>
                    <table class="table table-sm table-bordered mb-3" style="font-size: 0.85rem;">
                        <thead style="background-color: #0B283F !important; color: white !important;">
                            <tr>
                                <th>PROGRAMA</th>
                                <th>COL1</th>
                                <th>COL3</th>
                                <th>COL5</th>
                                <th>CODIGOG1</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Ejemplo</td>
                                <td>100000</td>
                                <td>90000</td>
                                <td>50000</td>
                                <td>01</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="alert alert-warning alert-sm">
                        <small>
                            <strong>Notas:</strong><br>
                            • Separador de campos: coma (,)<br>
                            • Separador decimal: punto (.)<br>
                            • Los campos de texto pueden estar entre comillas<br>
                            • Las líneas vacías serán omitidas<br>
                            • Máximo 10 MB de tamaño
                        </small>
                    </div>

                    <a href="javascript:void(0)" class="btn btn-outline-secondary btn-sm w-100" 
                       onclick="downloadTemplate()">
                        <i class="fas fa-download"></i> Descargar Plantilla CSV
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
                    <h5 class="mb-0" style="color: white !important;"><i class="fas fa-check-circle"></i> Instrucciones</h5>
                </div>
                <div class="card-body p-4">
                    <ol class="mb-0" style="font-size: 0.9rem;">
                        <li class="mb-2">Prepara tu archivo CSV</li>
                        <li class="mb-2">Verifica que los encabezados coincidan</li>
                        <li class="mb-2">Haz clic en "Importar"</li>
                        <li class="mb-2">Verifica los resultados</li>
                        <li>Revisa los datos en la lista</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function downloadTemplate() {
    const csv = `PROGRAMA,ACTIVIDAD,FUENTE,GEOGRAFICO,ITEM,COL1,COL2,COL3,COL4,COL5,COL6,COL7,COL8,COL9,COL10,COL20,CODIGOG1,CODIGOG2,CODIGOG3,CODIGOG4,CODIGOG5
"Programa Ejemplo","Actividad 1","Fuente 1","Área 1","Item 1",1000000,800000,900000,700000,500000,300000,200000,100000,50000,25000,75,01,02,03,04,05`;

    const element = document.createElement('a');
    element.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv));
    element.setAttribute('download', 'plantilla_presupuesto.csv');
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}
</script>
