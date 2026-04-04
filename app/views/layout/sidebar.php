<?php 
/**
 * Layout Moderno 2026 - Sidebar + Header Premium
 * Colores corporativos UEB con diseno glassmorphism
 */
if (!isset($_SESSION['usuario_id']) && isset($_GET['action']) && $_GET['action'] !== 'auth') {
    header('Location: ?action=auth&method=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestion - UEB</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="public/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --azul-1: #0B283F;
            --azul-2: #0B0E3F;
            --azul-3: #0B3F3C;
            --azul-light: #1a4a6e;
            --rojo-1: #C1272D;
            --gris-1: #F8FAFC;
            --gris-2: #F1F5F9;
            --gris-3: #E2E8F0;
            --gris-4: #94A3B8;
            --gris-5: #64748B;
            --texto-principal: #0F172A;
            --texto-secundario: #475569;
            --sidebar-width: 260px;
            --sidebar-collapsed: 72px;
            --header-height: 64px;
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        body {
            background: linear-gradient(135deg, var(--gris-1) 0%, var(--gris-2) 100%);
            background-attachment: fixed;
        }

        /* ==================== HEADER MODERNO ==================== */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 1001;
            box-shadow: 0 4px 20px rgba(11, 40, 63, 0.15);
        }

        .top-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 50%, rgba(255,255,255,0.05) 0%, transparent 50%),
                radial-gradient(circle at 90% 50%, rgba(11, 63, 60, 0.3) 0%, transparent 50%);
            pointer-events: none;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-logo img {
            height: 38px;
            width: auto;
            filter: brightness(0) invert(1);
            opacity: 0.95;
        }

        .header-logo-text {
            display: none;
            flex-direction: column;
        }

        @media (min-width: 768px) {
            .header-logo-text {
                display: flex;
            }
        }

        .header-logo-text span:first-child {
            font-size: 0.9375rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.02em;
        }

        .header-logo-text span:last-child {
            font-size: 0.6875rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
        }

        .menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 10px;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .header-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            display: none;
        }

        @media (min-width: 992px) {
            .header-center {
                display: block;
            }
        }

        .header-title {
            color: white;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        /* Year Selector */
        .year-selector {
            display: none;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (min-width: 768px) {
            .year-selector {
                display: flex;
            }
        }

        .year-selector i {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
        }

        .year-selector select {
            background: transparent;
            border: none;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            appearance: none;
        }

        .year-selector select option {
            background: var(--azul-1);
            color: white;
        }

        /* User Profile Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.375rem 0.75rem 0.375rem 0.375rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-info {
            display: none;
            flex-direction: column;
            text-align: left;
        }

        @media (min-width: 576px) {
            .user-info {
                display: flex;
            }
        }

        .user-info .name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: white;
        }

        .user-info .role {
            font-size: 0.6875rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .user-dropdown-toggle i.fa-chevron-down {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.625rem;
            transition: transform 0.2s ease;
        }

        /* ==================== SIDEBAR MODERNO ==================== */
        .sidebar {
            position: fixed;
            left: 0;
            top: var(--header-height);
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: white;
            border-right: 1px solid var(--gris-3);
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar.collapsed .nav-label,
        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .sidebar-footer-content span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .sidebar.collapsed .sidebar-menu a {
            justify-content: center;
            padding: 0.75rem;
        }

        .sidebar.collapsed .sidebar-menu a i {
            margin: 0;
        }

        /* Mobile sidebar */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }

            .sidebar.active {
                transform: translateX(0);
                box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                backdrop-filter: blur(4px);
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Sidebar Menu */
        .sidebar-menu {
            list-style: none;
            padding: 1rem 0.75rem;
            margin: 0;
            flex: 1;
        }

        .nav-section {
            margin-bottom: 0.5rem;
        }

        .nav-section:not(:first-child) {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gris-3);
        }

        .nav-label {
            display: block;
            padding: 0.5rem 0.75rem;
            font-size: 0.6875rem;
            font-weight: 700;
            color: var(--gris-5);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            transition: opacity 0.2s ease;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: var(--texto-secundario);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 0.25rem;
            position: relative;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            color: var(--gris-5);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .menu-text {
            white-space: nowrap;
            transition: opacity 0.2s ease, width 0.2s ease;
        }

        .sidebar-menu a:hover {
            background: linear-gradient(135deg, rgba(11, 40, 63, 0.05) 0%, rgba(11, 40, 63, 0.02) 100%);
            color: var(--azul-1);
        }

        .sidebar-menu a:hover i {
            color: var(--azul-1);
            transform: scale(1.1);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(11, 40, 63, 0.25);
        }

        .sidebar-menu a.active i {
            color: white;
        }

        .sidebar-menu a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: white;
            border-radius: 0 3px 3px 0;
            opacity: 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--gris-3);
            background: var(--gris-1);
        }

        .sidebar-footer-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: white;
            border-radius: 10px;
            border: 1px solid var(--gris-3);
        }

        .sidebar-footer-content i {
            color: var(--azul-1);
            font-size: 1rem;
        }

        .sidebar-footer-content span {
            font-size: 0.75rem;
            color: var(--texto-secundario);
            transition: opacity 0.2s ease, width 0.2s ease;
        }

        /* ==================== MAIN CONTENT ==================== */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed);
        }

        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
            }
        }

        main {
            flex: 1;
            padding: 1.5rem;
        }

        @media (min-width: 768px) {
            main {
                padding: 2rem;
            }
        }

        /* Footer */
        .main-footer {
            padding: 1.25rem 2rem;
            background: white;
            border-top: 1px solid var(--gris-3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .main-footer p {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--texto-secundario);
        }

        .main-footer strong {
            color: var(--azul-1);
            font-weight: 600;
        }

        /* Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--gris-4);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--gris-5);
        }

        /* Print */
        @media print {
            .sidebar,
            .top-header,
            .main-footer,
            .sidebar-overlay {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                margin-top: 0 !important;
            }

            main {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="top-header">
        <div class="header-left">
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-logo">
                <img src="public/img/logo-finanzas.png" alt="Logo UEB" onerror="this.style.display='none'">
                <div class="header-logo-text">
                    <span>Sistema de Gestion</span>
                    <span>Universidad Estatal de Bolivar</span>
                </div>
            </div>
        </div>

        <div class="header-center">
            <span class="header-title">Sistema de Gestion Presupuestaria</span>
        </div>

        <div class="header-right">
            <?php if (isset($_SESSION['usuario_id'])): ?>
            <!-- Year Selector -->
            <form method="POST" action="?action=auth&method=cambiarAno" id="formCambiarAno" class="year-selector">
                <i class="fas fa-calendar-alt"></i>
                <select name="año_trabajo" onchange="document.getElementById('formCambiarAno').submit();" title="Cambiar ano">
                    <?php 
                        $currentYear = date('Y');
                        $selectedYear = $_SESSION['year'] ?? $currentYear;
                        for ($i = $currentYear; $i >= $currentYear - 5; $i--) {
                            $selected = ($i == $selectedYear) ? 'selected' : '';
                            echo "<option value=\"$i\" $selected>$i</option>";
                        }
                    ?>
                </select>
            </form>

            <!-- User Dropdown -->
            <div class="dropdown user-dropdown">
                <button class="user-dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <span class="name"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
                        <span class="role"><?php echo ucfirst($_SESSION['usuario_tipo']); ?></span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="?action=perfil&method=ver">
                            <i class="fas fa-user"></i> Mi Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="?action=perfil&method=cambiarContraseña">
                            <i class="fas fa-key"></i> Cambiar Contrasena
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="?action=auth&method=logout">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesion
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li class="nav-section">
                <a href="index.php?action=dashboard" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <!-- Certificados -->
            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] !== 'consultor'): ?>
            <li class="nav-section">
                <div class="nav-label">Certificados</div>
                <a href="index.php?action=certificate-list" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'certificate-list' ? 'active' : ''; ?>">
                    <i class="fas fa-list-ul"></i>
                    <span class="menu-text">Ver Certificados</span>
                </a>
                <a href="index.php?action=certificate-create" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'certificate-create' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span class="menu-text">Crear Certificado</span>
                </a>
                <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <a href="index.php?action=parameter-list" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'parameter-list' ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h"></i>
                    <span class="menu-text">Parametros</span>
                </a>
                <?php endif; ?>
            </li>
            <?php endif; ?>

            <!-- Presupuesto -->
            <li class="nav-section">
                <div class="nav-label">Presupuesto</div>
                <a href="index.php?action=presupuesto-list" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'presupuesto-list' ? 'active' : ''; ?>">
                    <i class="fas fa-coins"></i>
                    <span class="menu-text">Ver Presupuestos</span>
                </a>
                <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <a href="index.php?action=presupuesto-upload" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'presupuesto-upload' ? 'active' : ''; ?>">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span class="menu-text">Importar CSV</span>
                </a>
                <?php endif; ?>
            </li>

            <!-- Administracion (Solo Admin) -->
            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
            <li class="nav-section">
                <div class="nav-label">Administracion</div>
                <a href="index.php?action=usuario&method=listar" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'usuario' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i>
                    <span class="menu-text">Gestionar Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="sidebar-footer-content">
                <i class="fas fa-info-circle"></i>
                <span>Version 2.0 - 2026</span>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <main>
