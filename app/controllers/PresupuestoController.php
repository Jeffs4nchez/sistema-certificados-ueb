<?php
/**
 * Controlador de Presupuesto
 */

class PresupuestoController {
    private $presupuestoModel;
    
    public function __construct() {
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/PresupuestoItem.php';
        $this->presupuestoModel = new PresupuestoItem();
    }
    
    public function listAction() {
        $items = $this->presupuestoModel->getAll();
        $totalItems = $this->presupuestoModel->count();
        $resumen = $this->presupuestoModel->getResumen();
        require_once __DIR__ . '/../views/presupuesto/list.php';
    }
    
    public function uploadAction() {
        // Solo admin puede importar presupuestos
        if (!PermisosHelper::puedeGestionarUsuarios()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden importar presupuestos.');
        }

        $resultado = null;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si el archivo fue subido
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                $error_msg = 'No se subió archivo o hubo un error';
                if (isset($_FILES['csv_file']['error'])) {
                    switch ($_FILES['csv_file']['error']) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error_msg = 'El archivo es demasiado grande.';
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $error_msg = 'El archivo se subió parcialmente.';
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $error_msg = 'No se seleccionó ningún archivo.';
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $error_msg = 'Carpeta temporal no configurada.';
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $error_msg = 'No se puede escribir en el disco.';
                            break;
                    }
                }
                $_SESSION['error'] = $error_msg;
            } else {
                try {
                    $file = $_FILES['csv_file']['tmp_name'];
                    $filename = $_FILES['csv_file']['name'];
                    
                    // Validar extensión
                    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'csv') {
                        throw new Exception('El archivo debe ser un CSV válido.');
                    }
                    
                    if (!file_exists($file)) {
                        throw new Exception('No se pudo acceder al archivo temporal.');
                    }
                    
                    $resultado = $this->presupuestoModel->importCSV($file);
                    
                    if ($resultado['total'] > 0) {
                        $mensaje = "✓ Se importaron {$resultado['total']} registros correctamente.";
                        if ($resultado['errors'] > 0) {
                            $mensaje .= " ({$resultado['errors']} errores ignorados)";
                            if (!empty($resultado['errorDetails'])) {
                                $mensaje .= "\n\nDetalles de errores:\n";
                                foreach ($resultado['errorDetails'] as $detalle) {
                                    $mensaje .= "• " . $detalle . "\n";
                                }
                            }
                        }
                        $_SESSION['success'] = $mensaje;
                    } else {
                        $_SESSION['error'] = 'No se importó ningún registro. Verifique el formato del CSV.';
                    }
                    
                } catch (Exception $e) {
                    $_SESSION['error'] = 'Error al importar: ' . $e->getMessage();
                }
            }
            
            // Limpiar buffer si está abierto y redirigir al GET
            if (ob_get_level() > 0) ob_end_clean();
            header('Location: index.php?action=presupuesto-upload');
            exit;
        }
        
        require_once __DIR__ . '/../views/presupuesto/upload.php';
    }
    
    public function viewAction($id) {
        $item = $this->presupuestoModel->getById($id);
        
        if (!$item) {
            header('Location: index.php?action=presupuesto-list');
            exit;
        }
        
        require_once __DIR__ . '/../views/presupuesto/view.php';
    }
    
    public function deleteAction($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->presupuestoModel->delete($id);
                $_SESSION['success'] = 'Presupuesto eliminado correctamente.';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
        }
        
        header('Location: index.php?action=presupuesto-list');
        exit;
    }
}
?>
