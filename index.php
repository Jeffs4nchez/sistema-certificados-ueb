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

// Obtener acción del GET o usar dashboard como default
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// No cargar layout para peticiones API
if ($action !== 'api-certificate') {
    // Iniciar la vista layout
    require_once __DIR__ . '/app/views/layout/header.php';
}

// Enrutador
try {
    switch ($action) {
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

// Cerrar la vista layout (solo si no es API)
if ($action !== 'api-certificate') {
    require_once __DIR__ . '/app/views/layout/footer.php';
}
?>
