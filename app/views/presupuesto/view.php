<?php
/**
 * Vista: Ver Detalles de Presupuesto
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">Detalles del Presupuesto</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=presupuesto-list" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Volver a Lista
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información General -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class="fas fa-info-circle"></i> Información General</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">PROGRAMA</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($item['descripciong1'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">CÓDIGO PROGRAMA</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($item['codigog1'] ?? ''); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">ACTIVIDAD</label>
                            <p><?php echo htmlspecialchars($item['descripciong2'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">CÓDIGO ACTIVIDAD</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($item['codigog2'] ?? ''); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">FUENTE DE FINANCIAMIENTO</label>
                            <p><?php echo htmlspecialchars($item['descripciong3'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">CÓDIGO FUENTE</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($item['codigog3'] ?? ''); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">ÁREA GEOGRÁFICA</label>
                            <p><?php echo htmlspecialchars($item['descripciong4'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">CÓDIGO GEOGRÁFICO</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($item['codigog4'] ?? ''); ?></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="text-muted small">ITEM</label>
                            <p><?php echo htmlspecialchars($item['descripciong5'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Financiera -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-line"></i> Información Financiera</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="text-center">
                                <label class="text-muted small">PRESUPUESTO ASIGNADO (Col1)</label>
                                <h3 class="text-primary">
                                    $<?php echo number_format($item['col1'], 0, '.', ','); ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="text-center">
                                <label class="text-muted small">PRESUPUESTO CODIFICADO (Col3)</label>
                                <h3 class="text-info">
                                    $<?php echo number_format($item['col3'], 0, '.', ','); ?>
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="text-center">
                                <label class="text-muted small">PRESUPUESTO COMPROMETIDO (Col5)</label>
                                <h3 class="text-warning">
                                    $<?php echo number_format($item['col5'], 0, '.', ','); ?>
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="text-center">
                                <label class="text-muted small">SALDO DISPONIBLE</label>
                                <?php 
                                $saldo = ($item['col3'] ?? 0) - ($item['col5'] ?? 0);
                                $class = $saldo >= 0 ? 'text-success' : 'text-danger';
                                ?>
                                <h3 class="<?php echo $class; ?>">
                                    $<?php echo number_format($saldo, 0, '.', ','); ?>
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Devengado (Col6)</small>
                                <p class="fw-bold mb-0">$<?php echo number_format($item['col6'], 0, '.', ','); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">Pagado (Col7)</small>
                                <p class="fw-bold mb-0">$<?php echo number_format($item['col7'], 0, '.', ','); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted">% Ejecución (Col20)</small>
                                <p class="fw-bold mb-0"><?php echo number_format($item['col20'], 1); ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0 text-white"><i class="fas fa-clock"></i> Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">ID</label>
                        <p class="fw-bold"><?php echo htmlspecialchars($item['id']); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Creado</label>
                        <p><?php echo date('d/m/Y H:i', strtotime($item['fecha_creacion'] ?? '')); ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Actualizado</label>
                        <p><?php echo date('d/m/Y H:i', strtotime($item['fecha_actualizacion'] ?? '')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Códigos de Clasificación -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0 text-white"><i class="fas fa-tags"></i> Códigos</h5>
                </div>
                <div class="card-body">
                    <?php
                    $codigos = [
                        'codigog1' => 'Programa',
                        'codigog2' => 'Actividad',
                        'codigog3' => 'Fuente',
                        'codigog4' => 'Geográfico',
                        'codigog5' => 'Item'
                    ];
                    ?>
                    <?php foreach ($codigos as $key => $label): ?>
                        <div class="mb-2">
                            <small class="text-muted"><?php echo $label; ?></small>
                            <div class="badge bg-secondary" style="font-size: 0.9rem;">
                                <?php echo htmlspecialchars($item[$key] ?? ''); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body">
                    <a href="index.php?action=presupuesto-list" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                    
                    <!-- Botones de Exportación: Admin, Operador y Consultor -->
                    <?php if (isset($_SESSION['usuario_tipo']) && ($_SESSION['usuario_tipo'] === 'admin' || $_SESSION['usuario_tipo'] === 'operador' || $_SESSION['usuario_tipo'] === 'consultor')): ?>
                    <a href="index.php?action=presupuesto-export-excel" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                    <a href="index.php?action=presupuesto-export-pdf" class="btn btn-danger w-100 mb-2">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <?php endif; ?>
                    
                    <!-- Solo Admin puede eliminar -->
                    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                    <form method="POST" action="index.php?action=presupuesto-delete&id=<?php echo $item['id']; ?>" 
                          onsubmit="return confirm('¿Estás seguro? Esta acción no se puede deshacer.');">
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
