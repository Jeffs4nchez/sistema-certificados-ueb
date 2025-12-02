<?php
/**
 * Vista: Formulario de Importación de Parámetros
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">Importar Parámetros desde CSV</h1>
            <p class="text-muted">Sube un archivo CSV con múltiples parámetros</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=parameter-list" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important; padding: 0.75rem 1rem;">
                    <h5 class="mb-0" style="color: white !important;"><i class="fas fa-file-csv"></i> Formato del CSV</h5>
                </div>
                <div class="card-body p-4">
                    <p class="mb-3"><strong>El archivo CSV debe tener las siguientes columnas:</strong></p>
                    
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <thead style="background-color: #0B283F !important; color: white !important;">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Columna 1</th>
                                    <th>Columna 2</th>
                                    <th>Columna 3</th>
                                    <th>Ejemplo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-info">PG</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>-</td>
                                    <td><code>01,ADMINISTRACION CENTRAL</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">SP</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>Programa_ID</td>
                                    <td><code>00,SIN SUBPROGRAMA,1</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">PY</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>Subprograma_ID</td>
                                    <td><code>000,SIN PROYECTO,1</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">ACT</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>Proyecto_ID</td>
                                    <td><code>001,ADMINISTRACION,1</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">ITEM</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>Actividad_ID</td>
                                    <td><code>0001,RECURSOS,1</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">UBG</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>-</td>
                                    <td><code>01,LA PAZ</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">FTE</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>-</td>
                                    <td><code>100,RECURSOS FISCALES</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">ORG</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>-</td>
                                    <td><code>1000,MINISTERIO</code></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">N.PREST</span></td>
                                    <td>Código</td>
                                    <td>Descripción</td>
                                    <td>-</td>
                                    <td><code>01,FORMACION</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Nota:</strong> La primera fila del CSV será considerada como encabezado y será ignorada.
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important; padding: 0.75rem 1rem;">
                    <h5 class="mb-0" style="color: white !important;"><i class="fas fa-upload"></i> Subir Archivo</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="index.php?action=import-upload" enctype="multipart/form-data">
                        <!-- Tipo de Parámetro -->
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Parámetro <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">-- Selecciona un tipo --</option>
                                <option value="PG">PG - Programas</option>
                                <option value="SP">SP - Subprogramas</option>
                                <option value="PY">PY - Proyectos</option>
                                <option value="ACT">ACT - Actividades</option>
                                <option value="ITEM">ITEM - Items Presupuestarios</option>
                                <option value="UBG">UBG - Ubicaciones Geográficas</option>
                                <option value="FTE">FTE - Fuentes de Financiamiento</option>
                                <option value="ORG">ORG - Organismos</option>
                                <option value="N.PREST">N.PREST - Naturaleza de Prestación</option>
                            </select>
                        </div>

                        <!-- Archivo CSV -->
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Archivo CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" 
                                   accept=".csv" required>
                            <small class="text-muted">Máximo 5MB. Solo archivos .csv</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?action=parameter-list" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Importar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-4">
                    <h5 class="mb-3"><i class="fas fa-question-circle"></i> Ayuda</h5>
                    
                    <div class="mb-3">
                        <strong>¿Cómo crear el CSV?</strong>
                        <ol class="small mt-2">
                            <li>Abre Excel o LibreOffice Calc</li>
                            <li>Crea las columnas según el tipo</li>
                            <li>Guarda como CSV (delimitado por comas)</li>
                            <li>Sube el archivo aquí</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <strong>Códigos de Parámetros:</strong>
                        <ul class="small list-unstyled">
                            <li><span class="badge bg-info">PG</span> Programa</li>
                            <li><span class="badge bg-info">SP</span> Subprograma</li>
                            <li><span class="badge bg-info">PY</span> Proyecto</li>
                            <li><span class="badge bg-info">ACT</span> Actividad</li>
                            <li><span class="badge bg-info">ITEM</span> Item</li>
                            <li><span class="badge bg-warning">UBG</span> Ubicación</li>
                            <li><span class="badge bg-warning">FTE</span> Fuente</li>
                            <li><span class="badge bg-warning">ORG</span> Organismo</li>
                            <li><span class="badge bg-warning">N.PREST</span> Naturaleza</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-warning"></i> 
                        Verifica los datos antes de importar.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
