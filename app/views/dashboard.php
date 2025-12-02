<?php
/**
 * Vista: Dashboard Principal
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-4"><i class="fas fa-home"></i> Inicio</h1>
            <p class="text-muted">Bienvenido al Sistema de Gestión de Certificados y Presupuesto</p>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjetas KPI con tamaño uniforme -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-diploma fa-2x text-primary"></i>
                    </div>
                    <p class="text-muted mb-2"><small><i class="fas fa-list"></i> Total Certificados</small></p>
                    <h3 class="mb-1"><?php echo $totalCertificates; ?></h3>
                    <small class="text-success d-block">
                        <i class="fas fa-check"></i> <?php echo $completados; ?> completados
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-coins fa-2x text-info"></i>
                    </div>
                    <p class="text-muted mb-2"><small><i class="fas fa-coins"></i> Total Codificado</small></p>
                    <h4 class="mb-0">
                        $<?php echo number_format($resumenPresupuesto['total_codificado'] ?? 0, 0, '.', ','); ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                    <p class="text-muted mb-2"><small><i class="fas fa-check-circle"></i> Total Certificado</small></p>
                    <h4 class="mb-0">
                        $<?php echo number_format($resumenPresupuesto['total_certificado'] ?? 0, 0, '.', ','); ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 mt-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-balance-scale fa-2x text-warning"></i>
                    </div>
                    <p class="text-muted mb-2"><small><i class="fas fa-balance-scale"></i> Saldo Disponible</small></p>
                    <h4 class="mb-0">
                        $<?php echo number_format($resumenPresupuesto['total_saldo_disponible'] ?? 0, 0, '.', ','); ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 mt-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-list fa-2x text-secondary"></i>
                    </div>
                    <p class="text-muted mb-2"><small><i class="fas fa-list"></i> Total Items</small></p>
                    <h4 class="mb-0"><?php echo number_format($totalPresupuestos); ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md-4 mt-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-money-bill-wave fa-2x text-danger"></i>
                    </div>
                    <p class="text-muted mb-2"><small><i class="fas fa-money-bill-wave"></i> Total Liquidado</small></p>
                    <h4 class="mb-0">
                        $<?php echo number_format($resumenPresupuesto['total_liquidado'] ?? 0, 0, '.', ','); ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección Informativa de Certificados para Operadores -->
    <?php if (($usuario_tipo ?? 'operador') === 'operador'): ?>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
                    <h5 class="mb-0" style="color: white !important;">
                        <i class="fas fa-diploma"></i> Mis Certificados
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-2"><i class="fas fa-check-circle text-success"></i> Mis Certificados Completados</p>
                                <h4 class="mb-0 text-success"><?php echo $usuarioCompletados; ?> de <?php echo $usuarioCertificates; ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-2"><i class="fas fa-check-circle text-warning"></i> Mi Total Certificado</p>
                                <h4 class="mb-0 text-warning">$<?php echo number_format($usuarioTotalCertificado, 0, '.', ','); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <p class="text-muted mb-2"><i class="fas fa-money-bill-wave text-danger"></i> Mi Total Liquidado</p>
                                <h4 class="mb-0 text-danger">$<?php echo number_format($usuarioTotalLiquidado, 0, '.', ','); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
