<?php
/**
 * Sistema de Gestión de Certificados y Presupuesto
 * Índice Principal - Enrutador
 */

// Iniciar sesión
session_start();

// Configuración de headers
header('Content-Type: text/html; charset=utf-8');

// Cargar bootstrap para inicializar la aplicación
require_once __DIR__ . '/bootstrap.php';

// Instalar base de datos solo si no existe (primera vez)
$dbPath = __DIR__ . '/database/install.php';
$markerFile = __DIR__ . '/.db-installed';
if (file_exists($dbPath) && !file_exists($markerFile)) {
    include $dbPath;
    touch($markerFile); // Marcar que ya se instaló
}

// Obtener acción del GET o usar auth como default
$action = isset($_GET['action']) ? trim($_GET['action']) : 'auth';

// Si viene vacío, usar auth
if (empty($action)) {
    $action = 'auth';
}

// Las rutas de autenticación NO requieren sesión
$public_actions = ['auth'];
$is_public = in_array($action, $public_actions);

// Si no hay sesión iniciada y no es una ruta pública, redirigir al login
if (!isset($_SESSION['usuario_id']) && !$is_public) {
    header('Location: ?action=auth&method=login');
    exit;
}

// Rutas que pueden hacer redirecciones (acciones que hacen header() redirect)
// Estas acciones SIEMPRE redirigen después de procesar
$always_redirect_actions = ['bulk-upload', 'parameter-delete', 
                            'presupuesto-delete', 
                            'presupuesto-export-excel', 'presupuesto-export-pdf',
                            'certificate-delete', 'certificate-export'];
// Estas acciones redirigen CONDICIONALMENTE (solo ciertos métodos)
$redirect_actions = ['parameter-create', 'parameter-edit', 'certificate-create', 'usuario', 'perfil', 'bulk-import', 'presupuesto-upload'];
$redirect_methods = [
    'usuario' => ['eliminar', 'crear', 'editar'],
    'perfil' => ['cambiar_contraseña'],
    'bulk-import' => ['bulk-upload'],
    'presupuesto-upload' => [], // POST redirige, GET muestra formulario
];
$current_method = $_GET['method'] ?? 'default';
$may_redirect = in_array($action, $always_redirect_actions) ||
                (in_array($action, $redirect_actions) && $_SERVER['REQUEST_METHOD'] === 'POST') || 
                (isset($redirect_methods[$action]) && in_array($current_method, $redirect_methods[$action]));

// Iniciar output buffering si puede haber redirecciones
if ($may_redirect) {
    ob_start();
}

// Enrutador
try {
    // Cargar layout ANTES de ejecutar controladores (excepto auth y API)
    if ($action !== 'auth' && $action !== 'api-certificate' && !$may_redirect) {
        require_once __DIR__ . '/app/views/layout/sidebar.php';
    }

    switch ($action) {
        // ========== AUTENTICACIÓN ==========
        case 'auth':
            require_once __DIR__ . '/app/controllers/AuthController.php';
            $controller = new AuthController();
            $method = $_GET['method'] ?? 'login';
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                throw new Exception('Método no encontrado: ' . $method);
            }
            break;

        // ========== PERFIL ==========
        case 'perfil':
            require_once __DIR__ . '/app/controllers/PerfilController.php';
            $controller = new PerfilController();
            $method = $_GET['method'] ?? 'ver';
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                throw new Exception('Método no encontrado: ' . $method);
            }
            break;

        // ========== USUARIOS ==========
        case 'usuario':
            require_once __DIR__ . '/app/controllers/UsuarioController.php';
            $controller = new UsuarioController();
            $method = $_GET['method'] ?? 'listar';
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                throw new Exception('Método no encontrado: ' . $method);
            }
            break;

        // ========== DASHBOARD ==========
        case 'dashboard':
            require_once __DIR__ . '/app/controllers/DashboardController.php';
            $controller = new DashboardController();
            $controller->indexAction();
            break;

        // ========== CERTIFICADOS ==========
        case 'certificate-list':
            require_once __DIR__ . '/app/controllers/CertificateController.php';
            $controller = new CertificateController();
            $controller->listAction();
            break;

        case 'certificate-create':
            require_once __DIR__ . '/app/controllers/CertificateController.php';
            $controller = new CertificateController();
            $controller->createAction();
            break;

        case 'certificate-edit':
            if (!isset($_GET['id'])) throw new Exception('ID requerido');
            require_once __DIR__ . '/app/controllers/CertificateController.php';
            $controller = new CertificateController();
            $controller->editAction($_GET['id']);
            break;

        case 'certificate-view':
            if (!isset($_GET['id'])) throw new Exception('ID requerido');
            require_once __DIR__ . '/app/controllers/CertificateController.php';
            $controller = new CertificateController();
            $controller->viewAction($_GET['id']);
            break;

        case 'certificate-delete':
            if (!isset($_GET['id'])) throw new Exception('ID requerido');
            require_once __DIR__ . '/app/controllers/CertificateController.php';
            $controller = new CertificateController();
            $controller->deleteAction($_GET['id']);
            break;

        case 'certificate-export':
            require_once __DIR__ . '/app/controllers/CertificateController.php';
            $controller = new CertificateController();
            $controller->exportAction();
            break;

        // ========== API CERTIFICADOS (AJAX) ==========
        case 'api-certificate':
            require_once __DIR__ . '/app/controllers/APICertificateController.php';
            $controller = new APICertificateController();
            $apiAction = $_GET['action-api'] ?? $_GET['action'] ?? 'unknown';
            $controller->route($apiAction);
            break;

        // ========== PARÁMETROS ==========
        case 'parameter-list':
            require_once __DIR__ . '/app/controllers/ParameterController.php';
            $controller = new ParameterController();
            $controller->listAction();
            break;

        case 'parameter-create':
            require_once __DIR__ . '/app/controllers/ParameterController.php';
            $controller = new ParameterController();
            $controller->createAction();
            break;

        case 'parameter-edit':
            if (!isset($_GET['id']) || !isset($_GET['tipo'])) throw new Exception('Parámetros requeridos');
            require_once __DIR__ . '/app/controllers/ParameterController.php';
            $controller = new ParameterController();
            $controller->editAction($_GET['id']);
            break;

        case 'parameter-delete':
            if (!isset($_GET['id']) || !isset($_GET['tipo'])) throw new Exception('Parámetros requeridos');
            require_once __DIR__ . '/app/controllers/ParameterController.php';
            $controller = new ParameterController();
            $controller->deleteAction($_GET['id']);
            break;

        // ========== IMPORTACIÓN MASIVA DE PARÁMETROS ==========
        case 'bulk-import':
            require_once __DIR__ . '/app/controllers/BulkImportController.php';
            $controller = new BulkImportController();
            $controller->bulkImportAction();
            break;

        case 'bulk-upload':
            require_once __DIR__ . '/app/controllers/BulkImportController.php';
            $controller = new BulkImportController();
            $controller->bulkUploadAction();
            break;

        // ========== PRESUPUESTO ==========
        case 'presupuesto-list':
            require_once __DIR__ . '/app/controllers/PresupuestoController.php';
            $controller = new PresupuestoController();
            $controller->listAction();
            break;

        case 'presupuesto-upload':
            require_once __DIR__ . '/app/controllers/PresupuestoController.php';
            $controller = new PresupuestoController();
            $controller->uploadAction();
            break;

        case 'presupuesto-view':
            if (!isset($_GET['id'])) throw new Exception('ID requerido');
            require_once __DIR__ . '/app/controllers/PresupuestoController.php';
            $controller = new PresupuestoController();
            $controller->viewAction($_GET['id']);
            break;

        case 'presupuesto-delete':
            if (!isset($_GET['id'])) throw new Exception('ID requerido');
            require_once __DIR__ . '/app/controllers/PresupuestoController.php';
            $controller = new PresupuestoController();
            $controller->deleteAction($_GET['id']);
            break;

        case 'presupuesto-export-excel':
            require_once __DIR__ . '/app/controllers/PresupuestoController.php';
            $controller = new PresupuestoController();
            $controller->exportExcelAction();
            break;

        case 'presupuesto-export-pdf':
            require_once __DIR__ . '/app/controllers/PresupuestoController.php';
            $controller = new PresupuestoController();
            $controller->exportPdfAction();
            break;

        // ========== DEFAULT ==========
        default:
            header('Location: index.php?action=dashboard');
            exit;
    }
} catch (Exception $e) {
    if ($action === 'api-certificate') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => htmlspecialchars($e->getMessage())
        ]);
    } else {
        echo '<div class="container mt-4"><div class="alert alert-danger">';
        echo '<h4>Error</h4>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div></div>';
    }
}

// Cerrar la vista layout (solo si no es API ni auth ni redirect)
if ($action !== 'api-certificate' && !$is_public && !$may_redirect) {
    require_once __DIR__ . '/app/views/layout/sidebar-footer.php';
}

// Limpiar buffer si está abierto
if ($may_redirect && ob_get_level() > 0) {
    ob_end_flush();
}
?>
