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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php?action=dashboard">
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
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?action=parameter-list">
                                <i class="fas fa-sliders-h"></i> Parámetros
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="presupuestoDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-coins"></i> Presupuesto
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="presupuestoDropdown">
                            <li><a class="dropdown-item" href="index.php?action=presupuesto-list">
                                <i class="fas fa-list"></i> Ver Presupuestos
                            </a></li>
                            <li><a class="dropdown-item" href="index.php?action=presupuesto-upload">
                                <i class="fas fa-upload"></i> Importar CSV
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-4 bg-light min-vh-100">
