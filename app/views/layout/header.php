<?php 
/**
 * Verificar si está autenticado
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
    
    <!-- Custom CSS -->
    <link href="public/css/style.css" rel="stylesheet">
    
    <style>
        @media print {
            nav {
                display: none !important;
            }
            main {
                background-color: white !important;
                padding: 0 !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['usuario_id'])): ?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-certificate"></i> Sistema de Gestión
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=dashboard">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="certificatesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-diploma"></i> Certificados
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="certificatesDropdown">
                            <li><a class="dropdown-item" href="index.php?action=certificate-list">
                                <i class="fas fa-list"></i> Ver Certificados
                            </a></li>
                            <li><a class="dropdown-item" href="index.php?action=certificate-create">
                                <i class="fas fa-plus"></i> Crear Certificado
                            </a></li>
                            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?action=parameter-list">
                                <i class="fas fa-sliders-h"></i> Parámetros
                            </a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usuariosDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-users"></i> Usuarios
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="usuariosDropdown">
                            <li><a class="dropdown-item" href="?action=usuario&method=listar">
                                <i class="fas fa-list"></i> Listar Usuarios
                            </a></li>
                            <li><a class="dropdown-item" href="?action=usuario&method=crearFormulario">
                                <i class="fas fa-plus"></i> Nuevo Usuario
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="presupuestoDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-coins"></i> Presupuesto
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="presupuestoDropdown">
                            <li><a class="dropdown-item" href="index.php?action=presupuesto-list">
                                <i class="fas fa-list"></i> Ver Presupuestos
                            </a></li>
                            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="index.php?action=presupuesto-upload">
                                <i class="fas fa-upload"></i> Importar CSV
                            </a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                            <span class="badge bg-info ms-1"><?php echo ucfirst($_SESSION['usuario_tipo']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
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
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4 bg-light min-vh-100">
