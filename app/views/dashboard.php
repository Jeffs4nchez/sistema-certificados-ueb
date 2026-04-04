<?php
/**
 * Vista: Dashboard Principal - Diseno Moderno 2026
 */
?>

<div class="dashboard-container">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="welcome-section">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?>. Aqui tienes un resumen de tu actividad.</p>
            </div>
            <div class="header-badge">
                <span class="badge-year">
                    <i class="fas fa-calendar-check"></i>
                    Ano <?php echo $_SESSION['year'] ?? date('Y'); ?>
                </span>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- KPI Cards Grid -->
    <div class="kpi-grid">
        <?php if ($mostrar_certificados): ?>
        <!-- Total Certificados -->
        <div class="kpi-card">
            <div class="kpi-icon primary">
                <i class="fas fa-certificate"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Certificados</span>
                <span class="kpi-value"><?php echo number_format($totalCertificates); ?></span>
                <div class="kpi-footer">
                    <span class="kpi-badge success">
                        <i class="fas fa-check"></i>
                        <?php echo $completados; ?> completados
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($mostrar_presupuesto): ?>
        <!-- Total Codificado -->
        <div class="kpi-card">
            <div class="kpi-icon info">
                <i class="fas fa-coins"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Codificado</span>
                <span class="kpi-value">$<?php echo number_format($resumenPresupuesto['total_codificado'] ?? 0, 2, ',', '.'); ?></span>
                <div class="kpi-footer">
                    <span class="kpi-trend">
                        <i class="fas fa-chart-line"></i>
                        Presupuesto asignado
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Certificado -->
        <div class="kpi-card">
            <div class="kpi-icon success">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Certificado</span>
                <span class="kpi-value">$<?php echo number_format($resumenPresupuesto['total_certificado'] ?? 0, 2, ',', '.'); ?></span>
                <div class="kpi-footer">
                    <?php 
                        $porcentajeCertificado = ($resumenPresupuesto['total_codificado'] ?? 0) > 0 
                            ? round((($resumenPresupuesto['total_certificado'] ?? 0) / ($resumenPresupuesto['total_codificado'] ?? 1)) * 100, 1) 
                            : 0;
                    ?>
                    <div class="progress-mini">
                        <div class="progress-mini-bar" style="width: <?php echo min($porcentajeCertificado, 100); ?>%"></div>
                    </div>
                    <span class="kpi-percent"><?php echo $porcentajeCertificado; ?>% del codificado</span>
                </div>
            </div>
        </div>

        <!-- Saldo Disponible -->
        <div class="kpi-card">
            <div class="kpi-icon warning">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Saldo Disponible</span>
                <span class="kpi-value">$<?php echo number_format($resumenPresupuesto['total_saldo_disponible'] ?? 0, 2, ',', '.'); ?></span>
                <div class="kpi-footer">
                    <span class="kpi-trend">
                        <i class="fas fa-balance-scale"></i>
                        Balance actual
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Items -->
        <div class="kpi-card">
            <div class="kpi-icon secondary">
                <i class="fas fa-list-ol"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Items de Presupuesto</span>
                <span class="kpi-value"><?php echo number_format($totalPresupuestos); ?></span>
                <div class="kpi-footer">
                    <span class="kpi-trend">
                        <i class="fas fa-th-list"></i>
                        Partidas registradas
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Liquidado -->
        <div class="kpi-card">
            <div class="kpi-icon danger">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Liquidado</span>
                <span class="kpi-value">$<?php echo number_format($resumenPresupuesto['total_liquidado'] ?? 0, 2, ',', '.'); ?></span>
                <div class="kpi-footer">
                    <?php 
                        $porcentajeLiquidado = ($resumenPresupuesto['total_certificado'] ?? 0) > 0 
                            ? round((($resumenPresupuesto['total_liquidado'] ?? 0) / ($resumenPresupuesto['total_certificado'] ?? 1)) * 100, 1) 
                            : 0;
                    ?>
                    <div class="progress-mini danger">
                        <div class="progress-mini-bar" style="width: <?php echo min($porcentajeLiquidado, 100); ?>%"></div>
                    </div>
                    <span class="kpi-percent"><?php echo $porcentajeLiquidado; ?>% liquidado</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Seccion de Certificados del Usuario (Solo Operadores) -->
    <?php if ($mostrar_certificados && (($usuario_tipo ?? 'operador') === 'operador')): ?>
    <div class="section-card">
        <div class="section-header">
            <div class="section-title">
                <i class="fas fa-user-certificate"></i>
                <h3>Mis Certificados</h3>
            </div>
            <a href="index.php?action=certificate-list" class="btn btn-sm btn-outline-primary">
                Ver todos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="section-body">
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-icon success">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Completados</span>
                        <span class="stat-value text-success"><?php echo $usuarioCompletados; ?> <small>de <?php echo $usuarioCertificates; ?></small></span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon warning">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Certificado</span>
                        <span class="stat-value text-warning">$<?php echo number_format($usuarioTotalCertificado, 2, ',', '.'); ?></span>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon danger">
                        <i class="fas fa-money-check-alt"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Total Liquidado</span>
                        <span class="stat-value text-danger">$<?php echo number_format($usuarioTotalLiquidado, 2, ',', '.'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h4 class="section-subtitle">Acciones Rapidas</h4>
        <div class="actions-grid">
            <?php if ($mostrar_certificados): ?>
            <a href="index.php?action=certificate-create" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <span class="action-label">Nuevo Certificado</span>
            </a>
            <a href="index.php?action=certificate-list" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-list"></i>
                </div>
                <span class="action-label">Ver Certificados</span>
            </a>
            <?php endif; ?>
            <a href="index.php?action=presupuesto-list" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <span class="action-label">Ver Presupuesto</span>
            </a>
            <a href="?action=perfil&method=ver" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <span class="action-label">Mi Perfil</span>
            </a>
        </div>
    </div>
</div>

<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-header {
        margin-bottom: 2rem;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .welcome-section {
        flex: 1;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--azul-1);
        margin-bottom: 0.25rem;
        letter-spacing: -0.02em;
    }

    .page-subtitle {
        font-size: 0.9375rem;
        color: var(--texto-secundario);
        margin: 0;
    }

    .header-badge {
        display: flex;
        align-items: center;
    }

    .badge-year {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        display: flex;
        gap: 1.25rem;
        align-items: flex-start;
        border: 1px solid var(--gris-3);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
    }

    .kpi-card:hover::before {
        opacity: 1;
    }

    .kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .kpi-icon.primary {
        background: linear-gradient(135deg, rgba(11, 40, 63, 0.1) 0%, rgba(11, 40, 63, 0.05) 100%);
        color: var(--azul-1);
    }

    .kpi-card:has(.kpi-icon.primary)::before {
        background: linear-gradient(90deg, var(--azul-1), var(--azul-light));
    }

    .kpi-icon.success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.05) 100%);
        color: #059669;
    }

    .kpi-card:has(.kpi-icon.success)::before {
        background: linear-gradient(90deg, #059669, #10B981);
    }

    .kpi-icon.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.05) 100%);
        color: #D97706;
    }

    .kpi-card:has(.kpi-icon.warning)::before {
        background: linear-gradient(90deg, #D97706, #F59E0B);
    }

    .kpi-icon.danger {
        background: linear-gradient(135deg, rgba(193, 39, 45, 0.1) 0%, rgba(193, 39, 45, 0.05) 100%);
        color: var(--rojo-1);
    }

    .kpi-card:has(.kpi-icon.danger)::before {
        background: linear-gradient(90deg, var(--rojo-1), var(--rojo-2));
    }

    .kpi-icon.info {
        background: linear-gradient(135deg, rgba(11, 63, 60, 0.1) 0%, rgba(11, 63, 60, 0.05) 100%);
        color: var(--azul-3);
    }

    .kpi-card:has(.kpi-icon.info)::before {
        background: linear-gradient(90deg, var(--azul-3), var(--azul-1));
    }

    .kpi-icon.secondary {
        background: linear-gradient(135deg, rgba(100, 116, 139, 0.1) 0%, rgba(100, 116, 139, 0.05) 100%);
        color: var(--gris-5);
    }

    .kpi-card:has(.kpi-icon.secondary)::before {
        background: linear-gradient(90deg, var(--gris-5), var(--gris-4));
    }

    .kpi-content {
        flex: 1;
        min-width: 0;
    }

    .kpi-label {
        display: block;
        font-size: 0.8125rem;
        color: var(--texto-secundario);
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .kpi-value {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--texto-principal);
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .kpi-footer {
        margin-top: 0.75rem;
    }

    .kpi-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.625rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .kpi-badge.success {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .kpi-trend {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        color: var(--texto-secundario);
    }

    .progress-mini {
        height: 4px;
        background: var(--gris-2);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0.375rem;
    }

    .progress-mini-bar {
        height: 100%;
        background: linear-gradient(90deg, #059669, #10B981);
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .progress-mini.danger .progress-mini-bar {
        background: linear-gradient(90deg, var(--rojo-1), var(--rojo-2));
    }

    .kpi-percent {
        font-size: 0.75rem;
        color: var(--texto-secundario);
    }

    /* Section Card */
    .section-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--gris-3);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
        color: white;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title i {
        font-size: 1.25rem;
    }

    .section-title h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: white;
    }

    .section-header .btn-outline-primary {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .section-header .btn-outline-primary:hover {
        background: white;
        color: var(--azul-1);
    }

    .section-body {
        padding: 1.5rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--gris-1);
        border-radius: 12px;
        border: 1px solid var(--gris-3);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-icon.success {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
    }

    .stat-icon.warning {
        background: rgba(245, 158, 11, 0.1);
        color: #D97706;
    }

    .stat-icon.danger {
        background: rgba(193, 39, 45, 0.1);
        color: var(--rojo-1);
    }

    .stat-info {
        flex: 1;
    }

    .stat-label {
        display: block;
        font-size: 0.75rem;
        color: var(--texto-secundario);
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-size: 1.125rem;
        font-weight: 700;
    }

    .stat-value small {
        font-size: 0.875rem;
        font-weight: 400;
        color: var(--texto-secundario);
    }

    /* Quick Actions */
    .quick-actions {
        margin-top: 2rem;
    }

    .section-subtitle {
        font-size: 1rem;
        font-weight: 600;
        color: var(--texto-principal);
        margin-bottom: 1rem;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
    }

    .action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        padding: 1.5rem 1rem;
        background: white;
        border: 1px solid var(--gris-3);
        border-radius: 14px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(11, 40, 63, 0.1);
        border-color: var(--azul-1);
    }

    .action-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        transition: transform 0.3s ease;
    }

    .action-card:hover .action-icon {
        transform: scale(1.1);
    }

    .action-label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--texto-principal);
        text-align: center;
    }

    @media (max-width: 768px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }

        .stats-row {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
