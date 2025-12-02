<?php 
/**
 * Nuevo Layout - Menú Lateral + Contenido
 * Con colores corporativos y tipografía Argentum Sans
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
            --azul-2: #0F3A52;
            --azul-3: #134E6D;
            --rojo-1: #C1272D;
            --rojo-2: #E63946;
            --gris-oscuro: #2E3C4F;
            --gris-claro: #F5F7FA;
            --blanco: #FFFFFF;
        }

        * {
            font-family: 'Open Sans', sans-serif;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--gris-claro);
        }

        /* SIDEBAR */
        /* Sidebar siempre a 60px */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 60px;
            height: 100vh;
            background: var(--azul-1);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            padding: 20px 0;
        }

        /* Expandir al pasar mouse */
        .sidebar:hover {
            width: 220px;
        }

        .sidebar:hover .sidebar-header h2,
        .sidebar:hover .nav-label,
        .sidebar:hover .menu-text {
            display: block;
        }

        .sidebar:hover a {
            padding-left: 15px;
        }

        .sidebar:hover a i {
            margin-right: 15px;
        }

        /* Estado collapsed por defecto */
        .sidebar-header h2,
        .nav-label,
        .menu-text {
            display: none;
            transition: all 0.3s ease;
        }

        .sidebar a {
            padding-left: 10px;
        }

        .sidebar a i {
            margin-right: 0;
            transition: all 0.3s ease;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        .sidebar.collapsed:hover {
            width: 220px;
        }

        .sidebar-header {
            padding: 0 20px 30px;
            border-bottom: 2px solid var(--rojo-1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 0 10px 30px;
            justify-content: center;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--rojo-1);
            white-space: nowrap;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: var(--rojo-1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        /* MENU */
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu .nav-section {
            padding: 20px 0;
        }

        .nav-label {
            padding: 10px 20px;
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 10px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            position: relative;
        }

        .sidebar.collapsed a {
            justify-content: center;
            padding: 12px 10px;
        }

        .sidebar-menu a:hover {
            background-color: var(--azul-2);
            color: white;
            border-left-color: var(--rojo-1);
        }

        .sidebar-menu a.active {
            background-color: var(--rojo-1);
            color: white;
            border-left-color: white;
        }

        .sidebar-menu a i {
            width: 24px;
            text-align: center;
            margin-right: 15px;
            font-size: 18px;
        }

        .sidebar.collapsed a i {
            margin-right: 0;
        }

        .menu-text {
            white-space: nowrap;
        }

        /* Dropdown */
        .submenu {
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .submenu.collapsed {
            max-height: 0;
        }

        .submenu a {
            padding-left: 50px;
            font-size: 14px;
            background-color: var(--azul-2);
        }

        .submenu a:hover {
            background-color: var(--azul-3);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 60px;
            transition: none;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Cuando sidebar hace hover, NO se ajusta el margin */
        .sidebar:hover ~ .main-content {
            margin-left: 60px;
        }

        .main-content.collapsed {
            margin-left: 60px;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
            background: transparent;
            border: none;
        }

        .user-profile:hover {
            background-color: rgba(0, 31, 63, 0.1);
        }

        .user-profile:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 31, 63, 0.1);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--rojo-1), var(--azul-1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        footer {
            background: var(--azul-1);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                position: fixed;
            }

            .sidebar:hover {
                width: 60px;
            }

            .sidebar:hover .sidebar-header h2,
            .sidebar:hover .nav-label,
            .sidebar:hover .menu-text {
                display: none;
            }

            .sidebar.active {
                width: 220px;
            }

            .sidebar.active .sidebar-header h2,
            .sidebar.active .nav-label,
            .sidebar.active .menu-text {
                display: block;
            }

            .sidebar.active a {
                padding-left: 20px;
            }

            .sidebar.active a i {
                margin-right: 15px;
            }

            .sidebar.collapsed {
                width: 60px;
                left: 0;
            }

            .main-content {
                margin-left: 60px;
            }

            .sidebar:hover ~ .main-content {
                margin-left: 60px;
            }

            .sidebar.active ~ .main-content {
                margin-left: 220px;
            }

            .main-content.collapsed {
                margin-left: 60px;
            }

            main {
                padding: 15px;
            }
        }

        @media (max-width: 576px) {
            .top-bar {
                padding: 10px 15px;
            }

            main {
                padding: 10px;
            }

            .sidebar-header {
                padding: 0 10px 20px;
            }

            .nav-label {
                padding: 8px 10px;
                font-size: 10px;
            }

            .sidebar-menu a {
                padding: 10px 10px;
                font-size: 13px;
            }

            .submenu a {
                padding-left: 30px;
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
            background: var(--azul-2);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--rojo-1);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar active" id="sidebar">
        <div class="sidebar-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="sidebar-logo">
                    <i class="fas fa-certificate"></i>
                </div>
                <h2>Gestión</h2>
            </div>
        </div>

        <ul class="sidebar-menu" id="sidebarMenu">
            <!-- DASHBOARD -->
            <li class="nav-item">
                <a href="index.php?action=dashboard" class="<?php echo isset($_GET['action']) && $_GET['action'] === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <!-- CERTIFICADOS -->
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
        <!-- TOP BAR -->
        <div class="top-bar">
            <div class="top-bar-left">
                <button class="btn btn-sm" style="background: none; border: none; color: var(--azul-1); font-size: 20px;" onclick="toggleSidebarMobile()">
                    <i class="fas fa-bars"></i>
                </button>
                <span style="color: var(--azul-1); font-weight: 600;">Sistema de Gestión</span>
            </div>
            <div class="top-bar-right">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="dropdown">
                    <button class="user-profile btn btn-link" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; color: inherit;">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['usuario_nombre'], 0, 1)); ?>
                        </div>
                        <div>
                            <div style="font-size: 13px; color: var(--azul-1); font-weight: 600;">
                                <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                            </div>
                            <div style="font-size: 11px; color: #999;">
                                <?php echo ucfirst($_SESSION['usuario_tipo']); ?>
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

        <!-- MAIN AREA -->
        <main>
