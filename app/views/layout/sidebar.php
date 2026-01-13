<?php 
/**
 * Nuevo Layout - Menú Lateral + Contenido
 * Con colores corporativos y tipografía Argentum Sans
 */
if (!isset($_SESSION['usuario_id']) && isset($_GET['action']) && $_GET['action'] !== 'auth') {
    header('Location: ?action=auth&method=login');
    exit;
}
?><html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión - Certificados y Presupuesto</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Google Fonts - Argentum Sans equivalent (usando Open Sans como alternativa) -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="public/css/style.css" rel="stylesheet">
    
    <style>
        :root {
            --azul-1: #0B283F;
            --azul-2: #0B0E3F;
            --azul-3: #0B3F3C;
            --rojo-1: #C1272D;
            --rojo-2: #E63946;
            --gris-oscuro: #2E3C4F;
            --gris-claro: #F5F7FA;
            --gris-sidebar: #F0F2F5;
            --gris-border: #E5E7EB;
            --blanco: #FFFFFF;
        }

        * {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
                'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        code {
            font-family: source-code-pro, Menlo, Monaco, Consolas, 'Courier New', monospace;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(135deg, #F5F7FA 0%, #E8ECEF 100%);
        }

        /* ============================================
           SIDEBAR MODERNO - ESTILO META BUSINESS SUITE
           ============================================ */
        .sidebar {
            position: fixed;
            left: 0;
            top: 56px;
            width: 240px;
            height: calc(100vh - 56px);
            background: linear-gradient(180deg, #FFFFFF 0%, #F8F9FA 100%);
            border-right: 1px solid var(--gris-border);
            color: #1F2937;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 16px 0;
            box-sizing: border-box;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.08);
        }

        .sidebar-header {
            display: none;
        }

        .sidebar-logo {
            display: none;
            background: linear-gradient(135deg, var(--azul-1) 0%, var(--azul-2) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(11, 40, 63, 0.15);
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: var(--azul-1);
            white-space: nowrap;
            letter-spacing: -0.3px;
        }

        /* MENU */
        .sidebar-menu {
            list-style: none;
            padding: 8px 8px;
            margin: 0;
        }

        .sidebar-menu .nav-section {
            padding: 12px 0;
            margin-bottom: 4px;
            display: block;
        }

        .nav-label {
            display: block;
            padding: 8px 16px;
            font-size: 10px;
            font-weight: 700;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 9px 12px;
            color: #374151;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 6px;
            position: relative;
            font-size: 14px;
            font-weight: 500;
            gap: 10px;
            margin: 0 4px;
            white-space: nowrap;
            border-bottom: none;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            font-size: 16px;
            transition: all 0.25s ease;
            color: #6B7280;
        }

        .menu-text {
            white-space: nowrap;
            flex: 1;
        }

        .sidebar-menu a:hover {
            background-color: #F3F4F6;
            color: var(--azul-1);
            border-bottom-color: transparent;
        }

        .sidebar-menu a:hover i {
            color: var(--azul-1);
            transform: translateX(2px);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, rgba(11, 40, 63, 0.1) 0%, rgba(11, 40, 63, 0.05) 100%);
            color: var(--azul-1);
            font-weight: 600;
            border-left: 3px solid var(--azul-1);
            padding-left: 9px;
            border-bottom: none;
        }

        .sidebar-menu a.active i {
            color: var(--azul-1);
        }

        /* Secciones con separador visual */
        .sidebar-menu .nav-section:not(:first-child) {
            border-top: 1px solid var(--gris-border);
            padding-top: 12px;
            border-left: none;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 240px;
            margin-top: 56px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            height: auto;
            overflow-y: auto;
        }

        .main-content.collapsed {
            margin-left: 240px;
        }

        .top-bar {
            background: #0B283F;
            padding: 12px 120px;
            border-bottom: 1px solid var(--gris-border);
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            height: 65px;
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            position: absolute;
            left: 20px;
        }

        .top-bar-left img {
            height: 40px;
            width: auto;
        }

        .top-bar-right {
            position: absolute;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 6px;
            transition: all 0.25s ease;
            background: transparent;
            border: none;
            font-size: 14px;
            color: white;
        }

        .user-profile:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .user-profile:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header-title {
            color: white;
            font-weight: 600;
            font-size: 16px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        footer {
            background: white;
            color: #6B7280;
            text-align: center;
            padding: 16px;
            border-top: 1px solid var(--gris-border);
            font-size: 12px;
            margin-top: auto;
        }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                top: 56px;
            }

            .sidebar-header h2,
            .nav-label,
            .menu-text {
                display: none;
            }

            .sidebar:hover {
                width: 240px;
            }

            .sidebar:hover .sidebar-header h2,
            .sidebar:hover .nav-label,
            .sidebar:hover .menu-text {
                display: block;
            }

            .sidebar:hover a {
                padding-left: 12px;
            }

            .sidebar-menu a {
                justify-content: center;
                padding: 9px 0;
                margin: 0;
            }

            .sidebar-menu a i {
                width: 20px;
                margin: 0;
            }

            .main-content {
                margin-left: 80px;
                margin-top: 56px;
            }

            .sidebar:hover ~ .main-content {
                margin-left: 240px;
            }

            main {
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                left: 0;
                top: 56px;
            }

            .sidebar-header h2,
            .nav-label,
            .menu-text {
                display: none;
            }

            .sidebar:hover {
                width: 260px;
                box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3);
            }

            .sidebar:hover .sidebar-header h2,
            .sidebar:hover .nav-label,
            .sidebar:hover .menu-text {
                display: block;
            }

            .sidebar.active {
                width: 240px;
                box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3);
            }

            .sidebar.active .sidebar-header h2,
            .sidebar.active .nav-label,
            .sidebar.active .menu-text {
                display: block;
            }

            .main-content {
                margin-left: 60px;
                margin-top: 56px;
            }

            .sidebar.active ~ .main-content {
                margin-left: 240px;
            }

            .sidebar:hover ~ .main-content {
                margin-left: 240px;
            }

            main {
                padding: 16px;
            }

            .top-bar {
                padding: 12px 20px;
            }

            .top-bar-left span {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .top-bar {
                padding: 10px 12px;
                gap: 10px;
            }

            main {
                padding: 12px;
            }

            .sidebar-menu a {
                padding: 8px 12px;
                font-size: 13px;
            }

            .nav-label {
                padding: 6px 12px;
                font-size: 9px;
            }
        }

        /* PRINT */
        @media print {
            .sidebar,
            .top-bar,
            footer {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }

            main {
                padding: 0;
            }
        }

        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }
    </style>
</head>
<body>
    <!-- TOP BAR (HEADER FIJO) -->
    <div class="top-bar">
        <div class="top-bar-left">
            <img src="public/img/logo-finanzas.png" alt="Logo UEB">
        </div>
        <span class="header-title">SISTEMA DE GESTION PRESUPUESTARIA UEB</span>
        <div class="top-bar-right">
            <?php if (isset($_SESSION['usuario_id'])): ?>
            <div class="dropdown">
                <button class="user-profile btn btn-link" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; color: white;">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-size: 13px; color: white; font-weight: 600;">
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </div>
                        <div style="font-size: 11px; color: rgba(255,255,255,0.7);">
                            <?php echo ucfirst($_SESSION['usuario_tipo']) . '-' . ($_SESSION['year'] ?? date('Y')); ?>
                        </div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="?action=perfil&method=ver">
                        <i class="fas fa-user"></i> Mi Perfil
                    </a></li>
                    <li><a class="dropdown-item" href="?action=perfil&method=cambiarContraseña">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="?action=auth&method=logout">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a></li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar active" id="sidebar">
        <ul class="sidebar-menu" id="sidebarMenu">
            <!-- DASHBOARD -->
            <li class="nav-item">
                <a href="index.php?action=dashboard" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <!-- CERTIFICADOS -->
            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] !== 'consultor'): ?>
            <li class="nav-section">
                <div class="nav-label">Certificados</div>
                <a href="index.php?action=certificate-list" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'certificate-list' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i>
                    <span class="menu-text">Ver Certificados</span>
                </a>
                <a href="index.php?action=certificate-create" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'certificate-create' ? 'active' : ''; ?>">
                    <i class="fas fa-plus"></i>
                    <span class="menu-text">Crear Certificado</span>
                </a>
                <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <a href="index.php?action=parameter-list" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'parameter-list' ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h"></i>
                    <span class="menu-text">Parámetros</span>
                </a>
                <?php endif; ?>
            </li>
            <?php endif; ?>

            <!-- PRESUPUESTO -->
            <li class="nav-section">
                <div class="nav-label">Presupuesto</div>
                <a href="index.php?action=presupuesto-list" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'presupuesto-list' ? 'active' : ''; ?>">
                    <i class="fas fa-coins"></i>
                    <span class="menu-text">Ver Presupuestos</span>
                </a>
                <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                <a href="index.php?action=presupuesto-upload" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'presupuesto-upload' ? 'active' : ''; ?>">
                    <i class="fas fa-upload"></i>
                    <span class="menu-text">Importar CSV</span>
                </a>
                <?php endif; ?>
            </li>

            <!-- USUARIOS (Solo Admin) -->
            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
            <li class="nav-section">
                <div class="nav-label">Administración</div>
                <a href="index.php?action=usuario&method=listar" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'usuario' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Gestionar Usuarios</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content" id="mainContent">
        <!-- MAIN AREA -->
        <main>
