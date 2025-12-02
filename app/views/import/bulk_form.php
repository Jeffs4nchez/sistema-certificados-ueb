<?php
/**
 * Vista: Importación Masiva de Parámetros desde CSV Jerárquico
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">Importación Masiva de Parámetros</h1>
            <p class="text-muted">Importa toda la estructura jerárquica desde un CSV completo</p>
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
        <div class="col-md-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important; padding: 0.75rem 1rem;">
                    <h5 class="mb-0" style="color: white !important;"><i class="fas fa-file-csv"></i> Formato Esperado del CSV</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Este importador está optimizado para archivos CSV con estructura completa.</strong>
                        Cada fila debe contener todos los niveles jerárquicos: Programa, Subprograma, Proyecto, Actividad, Item, Ubicación, Fuente, Organismo y Naturaleza.
                    </div>

                    <p class="mb-3"><strong>Las columnas esperadas son (en orden):</strong></p>
                    
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <thead style="background-color: #0B283F !important; color: white !important;">
                                <tr>
                                    <th style="width: 50px;">Col</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Ejemplo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>C. Programa</td>
                                    <td>Código del Programa (PG)</td>
                                    <td><code>01</code></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>D. Programa</td>
                                    <td>Descripción del Programa</td>
                                    <td><code>ADMINISTRACION CENTRAL</code></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>C. SUBPROG</td>
                                    <td>Código del Subprograma (SP)</td>
                                    <td><code>00</code></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>D. SUBPROG</td>
                                    <td>Descripción del Subprograma</td>
                                    <td><code>SIN SUBPROGRAMA</code></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>C. PROYECTO</td>
                                    <td>Código del Proyecto (PY)</td>
                                    <td><code>01 00 000</code></td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>D. PROYECTO</td>
                                    <td>Descripción del Proyecto</td>
                                    <td><code>SIN PROYECTO</code></td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>C. ACTIV</td>
                                    <td>Código de Actividad (ACT)</td>
                                    <td><code>01 00 000 001</code></td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>D. ACTIVIDAD</td>
                                    <td>Descripción de la Actividad</td>
                                    <td><code>ADMINISTRACION</code></td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td>C. ITEM</td>
                                    <td>Código del Item (ITEM)</td>
                                    <td><code>510106</code></td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td>D. ITEM</td>
                                    <td>Descripción del Item</td>
                                    <td><code>Remuneracion</code></td>
                                </tr>
                                <tr>
                                    <td>11</td>
                                    <td>C. UBICACION</td>
                                    <td>Código de Ubicación (UBG)</td>
                                    <td><code>0200</code></td>
                                </tr>
                                <tr>
                                    <td>12</td>
                                    <td>D. UBICACION</td>
                                    <td>Descripción de Ubicación</td>
                                    <td><code>BOLIVAR</code></td>
                                </tr>
                                <tr>
                                    <td>13</td>
                                    <td>C. FUENTE DE D.</td>
                                    <td>Código de Fuente (FTE)</td>
                                    <td><code>003</code></td>
                                </tr>
                                <tr>
                                    <td>14</td>
                                    <td>Fuente de C.</td>
                                    <td>Descripción de Fuente</td>
                                    <td><code>Recursos Prop</code></td>
                                </tr>
                                <tr>
                                    <td>15</td>
                                    <td>ORGANISM</td>
                                    <td>Código de Organismo (ORG)</td>
                                    <td><code>0000</code></td>
                                </tr>
                                <tr>
                                    <td>16</td>
                                    <td>D. ORGANISM</td>
                                    <td>Descripción de Organismo</td>
                                    <td><code>ORGANISMO</code></td>
                                </tr>
                                <tr>
                                    <td>17</td>
                                    <td>C. N.PREST</td>
                                    <td>Código de Naturaleza (N.PREST)</td>
                                    <td><code>0000</code></td>
                                </tr>
                                <tr>
                                    <td>18</td>
                                    <td>D. N.PREST</td>
                                    <td>Descripción de Naturaleza</td>
                                    <td><code>Sin N. Prest</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-warning"></i> 
                        <strong>Importante:</strong> La primera fila será considerada como encabezado y será ignorada.
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important; padding: 0.75rem 1rem;">
                    <h5 class="mb-0" style="color: white !important;"><i class="fas fa-upload"></i> Subir Archivo CSV</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="index.php?action=bulk-upload" enctype="multipart/form-data">
                        <!-- Archivo CSV -->
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Archivo CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" 
                                   accept=".csv" required>
                            <small class="text-muted">Máximo 10MB. Solo archivos .csv con estructura jerárquica completa.</small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?action=parameter-list" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Importar Ahora
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
