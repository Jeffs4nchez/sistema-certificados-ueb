<?php
/**
 * Script para verificar validación de presupuestos
 * Accede a: http://localhost/programas/certificados-sistema/verificar_presupuesto.php
 */

require_once __DIR__ . '/app/Database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Presupuestos por Año</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .card { margin-bottom: 20px; }
        .badge-presupuesto { font-size: 14px; padding: 8px 12px; }
        .estado-ok { background-color: #198754; }
        .estado-error { background-color: #dc3545; }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card shadow-lg border-0">
                <div class="card-header" style="background-color: #0B283F; color: white;">
                    <h2 class="mb-0"><i class="fas fa-chart-bar"></i> Verificación de Presupuestos por Año</h2>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-4">
                        Esta página verifica cuántos presupuestos están cargados en cada año.
                        Determina si se pueden crear certificados o no.
                    </p>

                    <?php
                    try {
                        $db = Database::getInstance()->getConnection();
                        
                        // Obtener años únicos en presupuesto_items
                        $stmt = $db->query("SELECT DISTINCT year FROM presupuesto_items ORDER BY year DESC");
                        $anos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        if (empty($anos)) {
                            echo '<div class="alert alert-info"><i class="fas fa-info-circle"></i> No hay presupuestos cargados en el sistema.</div>';
                        } else {
                            // Obtener rango de años posibles
                            $añoActual = date('Y');
                            $años = range(2020, $añoActual + 1);
                            
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped table-hover">';
                            echo '<thead style="background-color: #f0f0f0;">';
                            echo '<tr>';
                            echo '<th><i class="fas fa-calendar"></i> Año</th>';
                            echo '<th><i class="fas fa-list"></i> Presupuestos Cargados</th>';
                            echo '<th><i class="fas fa-check"></i> Crear Certificados</th>';
                            echo '<th>Acciones</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            foreach ($años as $year) {
                                // Contar presupuestos del año
                                $stmtCount = $db->prepare("SELECT COUNT(*) as total FROM presupuesto_items WHERE year = ?");
                                $stmtCount->execute([$year]);
                                $result = $stmtCount->fetch();
                                $totalPresupuestos = $result['total'];
                                
                                // Determinar estado
                                $puedeCrearCertificados = $totalPresupuestos > 0;
                                $estado = $puedeCrearCertificados ? '✅ Habilitado' : '❌ Deshabilitado';
                                $badgeClass = $puedeCrearCertificados ? 'estado-ok' : 'estado-error';
                                $badgeIcon = $puedeCrearCertificados ? 'fa-check' : 'fa-times';
                                
                                echo '<tr>';
                                echo '<td><strong>' . htmlspecialchars($year) . '</strong></td>';
                                echo '<td>';
                                
                                if ($totalPresupuestos > 0) {
                                    echo '<span class="badge bg-success badge-presupuesto">';
                                    echo '<i class="fas fa-check"></i> ' . $totalPresupuestos . ' presupuestos';
                                    echo '</span>';
                                } else {
                                    echo '<span class="badge bg-secondary badge-presupuesto">';
                                    echo '<i class="fas fa-ban"></i> Sin presupuestos';
                                    echo '</span>';
                                }
                                
                                echo '</td>';
                                echo '<td>';
                                echo '<span class="badge badge-presupuesto ' . $badgeClass . '">';
                                echo '<i class="fas ' . $badgeIcon . '"></i> ' . $estado;
                                echo '</span>';
                                echo '</td>';
                                echo '<td>';
                                echo '<a href="index.php?action=certificate-list&year=' . $year . '" class="btn btn-sm btn-outline-primary">';
                                echo '<i class="fas fa-file-invoice"></i> Certificados';
                                echo '</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            
                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">';
                        echo '<i class="fas fa-exclamation-circle"></i> Error: ' . htmlspecialchars($e->getMessage());
                        echo '</div>';
                    }
                    ?>

                    <hr class="my-4">

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-question-circle"></i> ¿Qué significa esto?</h5>
                            <ul>
                                <li><strong>Presupuestos Cargados:</strong> Cantidad de registros en la tabla presupuesto_items</li>
                                <li><strong>Habilitado (✅):</strong> Se pueden crear certificados para ese año</li>
                                <li><strong>Deshabilitado (❌):</strong> No se pueden crear certificados sin presupuesto</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-lightbulb"></i> ¿Cómo habilitar?</h5>
                            <ol>
                                <li>Ir a <strong>Presupuestos</strong></li>
                                <li>Haz clic en <strong>Cargar Presupuesto</strong></li>
                                <li>Sube el archivo CSV del presupuesto</li>
                                <li>Verifica que aparezca en esta tabla</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
