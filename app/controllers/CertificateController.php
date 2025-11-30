<?php
/**
 * Controlador de Certificados
 */

class CertificateController {
    private $certificateModel;
    private $certificateItemModel;
    
    public function __construct() {
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/Certificate.php';
        require_once __DIR__ . '/../models/CertificateItem.php';
        $this->certificateModel = new Certificate();
        $db = Database::getInstance()->getConnection();
        $this->certificateItemModel = new CertificateItem($db);
    }
    
    public function listAction() {
        // Obtener certificados según el rol del usuario
        if (PermisosHelper::esAdmin()) {
            // Admin ve todos
            $certificates = $this->certificateModel->getAll();
        } else {
            // Operador solo ve sus certificados
            $usuario_id = PermisosHelper::getUsuarioIdActual();
            $certificates = $this->certificateModel->getByUsuario($usuario_id);
        }
        require_once __DIR__ . '/../views/certificate/list.php';
    }
    
    public function createAction() {
        // Cargar datos iniciales (programas, ubicaciones, etc.)
        $programas = $this->certificateItemModel->getProgramas();
        $ubicaciones = $this->certificateItemModel->getUbicaciones();
        $fuentes = $this->certificateItemModel->getFuentes();
        $organismos = $this->certificateItemModel->getOrganismos();
        $naturalezas = $this->certificateItemModel->getNaturalezas();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                error_log('=== INICIO DE CREATE CERTIFICADO ===');
                error_log('POST data: ' . print_r($_POST, true));
                
                // Datos del certificado maestro
                // Convertir fecha de dd/mm/yyyy a yyyy-mm-dd si es necesario
                $dateInput = $_POST['date'] ?? date('d/m/Y');
                if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $dateInput, $matches)) {
                    $fechaElaboracion = $matches[3] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                } else {
                    $fechaElaboracion = date('Y-m-d');
                }
                
                $certificateData = [
                    'numero_certificado' => $_POST['numero'] ?? 'CERT-' . date('YmdHis'),
                    'institucion' => $_POST['name'] ?? '',
                    'seccion_memorando' => $_POST['seccion_memorando'] ?? '',
                    'descripcion' => $_POST['descripcion_general'] ?? '',
                    'fecha_elaboracion' => $fechaElaboracion,
                    'monto_total' => 0, // Se calculará de los items
                    'unid_ejecutora' => $_POST['unid_ejecutora'] ?? '',
                    'unid_desc' => $_POST['unid_desc'] ?? '',
                    'clase_registro' => $_POST['clase_registro'] ?? '',
                    'clase_gasto' => $_POST['clase_gasto'] ?? '',
                    'tipo_doc_respaldo' => $_POST['tipo_doc_respaldo'] ?? '',
                    'clase_doc_respaldo' => $_POST['clase_doc_respaldo'] ?? '',
                    'usuario_id' => $_SESSION['usuario_id'] ?? null,
                    'usuario_creacion' => ($_SESSION['usuario_nombre'] ?? 'Sistema')
                ];

                error_log('Datos del certificado: ' . print_r($certificateData, true));

                // Parsear los items desde JSON
                $itemsJson = $_POST['items_data'] ?? '[]';
                $items = json_decode($itemsJson, true);
                
                // DEBUG logging
                error_log('=== CERTIFICADO CREATE DEBUG ===');
                error_log('Items JSON recibido: ' . $itemsJson);
                error_log('Items decodificados: ' . print_r($items, true));
                error_log('Count items: ' . count($items ?? []));

                // Calcular monto total
                $montoTotal = 0;
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $montoTotal += floatval($item['monto'] ?? 0);
                    }
                }
                $certificateData['monto_total'] = $montoTotal;

                // Crear certificado y obtener su ID
                $certificateId = $this->certificateModel->createCertificate($certificateData);
                error_log('✓ Certificado creado con ID: ' . $certificateId);

                // Crear cada item en detalle_certificados (si existen)
                if (is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {
                        // Generar código completo: programa subprograma proyecto actividad fuente ubicacion item (con espacios)
                        $codigoCompleto = implode(' ', [
                            $item['programa_codigo'] ?? '',
                            $item['subprograma_codigo'] ?? '',
                            $item['proyecto_codigo'] ?? '',
                            $item['actividad_codigo'] ?? '',
                            $item['fuente_codigo'] ?? '',
                            $item['ubicacion_codigo'] ?? '',
                            $item['item_codigo'] ?? ''
                        ]);
                        
                        $detailData = [
                            'certificado_id' => (int)($item['certificado_id'] ?? $certificateId),
                            'programa_codigo' => (string)trim($item['programa_codigo'] ?? ''),
                            'subprograma_codigo' => (string)trim($item['subprograma_codigo'] ?? ''),
                            'proyecto_codigo' => (string)trim($item['proyecto_codigo'] ?? ''),
                            'actividad_codigo' => (string)trim($item['actividad_codigo'] ?? ''),
                            'item_codigo' => (string)trim($item['item_codigo'] ?? ''),
                            'ubicacion_codigo' => (string)trim($item['ubicacion_codigo'] ?? ''),
                            'fuente_codigo' => (string)trim($item['fuente_codigo'] ?? ''),
                            'organismo_codigo' => (string)trim($item['organismo_codigo'] ?? ''),
                            'naturaleza_codigo' => (string)trim($item['naturaleza_codigo'] ?? ''),
                            'descripcion_item' => (string)($item['item_descripcion'] ?? ''),
                            'monto' => (float)($item['monto'] ?? 0),
                            'codigo_completo' => $codigoCompleto
                        ];
                        
                        try {
                            $this->certificateModel->createDetail($detailData);
                        } catch (Exception $detailError) {
                            $_SESSION['error'] = 'Error al guardar item: ' . $detailError->getMessage();
                            error_log('Error guardando detalle: ' . $detailError->getMessage());
                        }
                    }
                }

                error_log('✓ Certificado creado exitosamente. ID: ' . $certificateId);
                $_SESSION['success'] = 'Certificado creado correctamente con ' . count($items) . ' items.';
                error_log('=== FIN DE CREATE EXITOSO ===');
                
                // Limpiar output buffer si está activo
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                
                header('Location: index.php?action=certificate-list');
                exit;
            } catch (Exception $e) {
                error_log('✗ ERROR en createAction: ' . $e->getMessage());
                error_log('Trace: ' . $e->getTraceAsString());
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
        }
        require_once __DIR__ . '/../views/certificate/form.php';
    }
    
    public function editAction($id) {
        // Solo admin puede editar certificados
        if (!PermisosHelper::puedeEditarCertificado(null)) {
            PermisosHelper::denegarAcceso('Solo administradores pueden editar certificados.');
        }

        $certificate = $this->certificateModel->getById($id);
        
        if (!$certificate) {
            header('Location: index.php?action=certificate-list');
            exit;
        }
        
        // Cargar datos iniciales
        $programas = $this->certificateItemModel->getProgramas();
        $ubicaciones = $this->certificateItemModel->getUbicaciones();
        $fuentes = $this->certificateItemModel->getFuentes();
        $organismos = $this->certificateItemModel->getOrganismos();
        $naturalezas = $this->certificateItemModel->getNaturalezas();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'numero_certificado' => $_POST['numero'] ?? '',
                    'institucion' => $_POST['name'] ?? '',
                    'seccion_memorando' => $_POST['seccion_memorando'] ?? '',
                    'descripcion' => $_POST['descripcion_general'] ?? '',
                    'fecha_elaboracion' => $_POST['date'] ?? $certificate['fecha_elaboracion'],
                    'unid_ejecutora' => $_POST['unid_ejecutora'] ?? '',
                    'unid_desc' => $_POST['unid_desc'] ?? '',
                    'clase_registro' => $_POST['clase_registro'] ?? '',
                    'clase_gasto' => $_POST['clase_gasto'] ?? '',
                    'tipo_doc_respaldo' => $_POST['tipo_doc_respaldo'] ?? '',
                    'clase_doc_respaldo' => $_POST['clase_doc_respaldo'] ?? ''
                ];
                
                $this->certificateModel->updateCertificate($id, $data);
                $_SESSION['success'] = 'Certificado actualizado correctamente.';
                header('Location: index.php?action=certificate-list');
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
        }
        
        require_once __DIR__ . '/../views/certificate/form.php';
    }
    
    public function viewAction($id) {
        $certificate = $this->certificateModel->getById($id);
        
        if (!$certificate) {
            header('Location: index.php?action=certificate-list');
            exit;
        }

        // Verificar permisos: operador solo ve los suyos
        if (!PermisosHelper::puedeVerCertificado($certificate['usuario_id'] ?? null)) {
            PermisosHelper::denegarAcceso('No tienes permiso para ver este certificado.');
        }
                // Obtener detalles del certificado
        $details = $this->certificateModel->getCertificateDetails($id);
        
        require_once __DIR__ . '/../views/certificate/report.php';
    }
    
    public function deleteAction($id) {
        // Solo admin puede eliminar certificados
        if (!PermisosHelper::puedeEliminarCertificado()) {
            PermisosHelper::denegarAcceso('Solo administradores pueden eliminar certificados.');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->certificateModel->delete($id);
                $_SESSION['success'] = 'Certificado eliminado correctamente.';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
        }
        
        header('Location: index.php?action=certificate-list');
        exit;
    }

    public function reportAction($id) {
        $certificate = $this->certificateModel->getById($id);
        
        if (!$certificate) {
            $_SESSION['error'] = 'Certificado no encontrado';
            header('Location: index.php?action=certificate-list');
            exit;
        }
        
        // Obtener detalles del certificado
        $details = $this->certificateModel->getCertificateDetails($id);
        
        require_once __DIR__ . '/../views/certificate/report.php';
    }
}
?>
