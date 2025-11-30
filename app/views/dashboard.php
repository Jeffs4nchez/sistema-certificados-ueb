<?php
/**
 * Vista: Dashboard Principal
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-4">Dashboard</h1>
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

    <!-- Tarjetas KPI -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Certificados</p>
                            <h3 class="mb-0"><?php echo $totalCertificates; ?></h3>
                        </div>
                        <i class="fas fa-diploma fa-2x text-primary opacity-50"></i>
                    </div>
                    <small class="text-success">
                        <i class="fas fa-check"></i> <?php echo $completados; ?> completados
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-list fa-xs"></i> Total Items</p>
                    <h5 class="mb-0"><?php echo number_format($totalPresupuestos); ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-coins fa-xs text-info"></i> Total Codificado</p>
                    <h5 class="mb-0">
                        $<?php echo number_format($resumenPresupuesto['total_codificado'] ?? 0, 0, '.', ','); ?>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-check-circle fa-xs text-success"></i> Total Certificado</p>
                    <h5 class="mb-0">
                        $<?php echo number_format($resumenPresupuesto['total_certificado'] ?? 0, 0, '.', ','); ?>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-balance-scale fa-xs text-warning"></i> Saldo Disponible</p>
                    <h5 class="mb-0">
                        $<?php echo number_format($resumenPresupuesto['total_saldo_disponible'] ?? 0, 0, '.', ','); ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-diploma"></i> Certificados</h5>
                </div>
                <div class="card-body">
                    <a href="index.php?action=certificate-list" class="btn btn-primary btn-block mb-2 w-100">
                        <i class="fas fa-list"></i> Ver Certificados
                    </a>
                    <a href="index.php?action=certificate-create" class="btn btn-success w-100">
                        <i class="fas fa-plus"></i> Crear Certificado
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-coins"></i> Presupuesto</h5>
                </div>
                <div class="card-body">
                    <a href="index.php?action=presupuesto-list" class="btn btn-success btn-block mb-2 w-100">
                        <i class="fas fa-list"></i> Ver Presupuestos
                    </a>
                    <a href="index.php?action=presupuesto-upload" class="btn btn-info w-100">
                        <i class="fas fa-upload"></i> Importar CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
