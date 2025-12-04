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

                // VALIDACIÓN: Verificar que cada monto NO exceda el monto codificado
                if (is_array($items) && count($items) > 0) {
                    $erroresValidacion = [];
                    foreach ($items as $index => $item) {
                        $montoItem = floatval($item['monto'] ?? 0);
                        
                        // Obtener monto codificado del presupuesto
                        $montoCoificado = $this->certificateItemModel->getMontoCoificado(
                            $item['programa_codigo'] ?? '',
                            $item['subprograma_codigo'] ?? '',
                            $item['proyecto_codigo'] ?? '',
                            $item['actividad_codigo'] ?? '',
                            $item['fuente_codigo'] ?? '',
                            $item['ubicacion_codigo'] ?? '',
                            $item['item_codigo'] ?? ''
                        );
                        
                        error_log("Item " . ($index + 1) . ": Monto=$montoItem, Codificado=$montoCoificado");
                        
                        // Si el monto ingresado es MAYOR al codificado, error
                        if ($montoItem > $montoCoificado) {
                            $erroresValidacion[] = "Item #" . ($index + 1) . ": Monto ingresado ($" . number_format($montoItem, 2) . ") excede el monto codificado ($" . number_format($montoCoificado, 2) . ")";
                        }
                    }
                    
                    // Si hay errores, lanzar excepción
                    if (!empty($erroresValidacion)) {
                        throw new Exception("❌ No se puede crear el certificado:\n" . implode("\n", $erroresValidacion));
                    }
                }

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
                        $codigoCompleto = trim(implode(' ', [
                            $item['programa_codigo'] ?? '',
                            $item['subprograma_codigo'] ?? '',
                            $item['proyecto_codigo'] ?? '',
                            $item['actividad_codigo'] ?? '',
                            $item['fuente_codigo'] ?? '',
                            $item['ubicacion_codigo'] ?? '',
                            $item['item_codigo'] ?? ''
                        ]));
                        
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
    
    public function saveLiquidacionesAction() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode($_POST['liquidaciones'] ?? '[]', true);
            
            if (empty($data)) {
                echo json_encode(['success' => false, 'message' => 'No hay liquidaciones para guardar']);
                exit;
            }
            
            $guardadas = 0;
            $certificadosActualizados = array();
            
            foreach ($data as $item) {
                $detalleId = $item['detalle_id'] ?? null;
                $cantidadLiquidacion = floatval($item['cantidad_liquidacion'] ?? 0);
                $memorando = $item['memorando'] ?? '';
                
                if (!$detalleId) continue;
                
                // Obtener el detalle completo
                $queryGetDetalle = "SELECT certificado_id, cantidad_liquidacion, codigo_completo FROM detalle_certificados WHERE id = ?";
                $stmtGetDetalle = $this->certificateModel->db->prepare($queryGetDetalle);
                $stmtGetDetalle->execute([$detalleId]);
                $detalle = $stmtGetDetalle->fetch();
                $certificadoId = $detalle['certificado_id'] ?? null;
                $codigoCompleto = $detalle['codigo_completo'] ?? null;
                
                // Obtener total_pendiente anterior
                $queryGetCertAnterior = "SELECT total_pendiente FROM certificados WHERE id = ?";
                $stmtGetCertAnterior = $this->certificateModel->db->prepare($queryGetCertAnterior);
                $stmtGetCertAnterior->execute([$certificadoId]);
                $certAnterior = $stmtGetCertAnterior->fetch();
                $totalPendienteAnterior = floatval($certAnterior['total_pendiente'] ?? 0);
                
                // Actualizar liquidación y memorando en la base de datos
                $query = "UPDATE detalle_certificados SET cantidad_liquidacion = ?, memorando = ? WHERE id = ?";
                $stmt = $this->certificateModel->db->prepare($query);
                error_log("Guardando: detalle_id=$detalleId, cantidad_liquidacion=$cantidadLiquidacion, memorando=$memorando");
                if ($stmt->execute([$cantidadLiquidacion, $memorando, $detalleId])) {
                    error_log("✓ Guardado correctamente");
                    $guardadas++;
                    
                    // Agregar certificado a la lista de actualizaciones
                    if ($certificadoId && !in_array($certificadoId, $certificadosActualizados)) {
                        $certificadosActualizados[] = $certificadoId;
                    }
                } else {
                    error_log("✗ Error al guardar");
                }
            }
            
            // Actualizar total_liquidado y total_pendiente en la tabla certificados
            // para cada certificado que fue modificado
            foreach ($certificadosActualizados as $certId) {
                $queryUpdate = "UPDATE certificados 
                    SET 
                        total_liquidado = COALESCE((
                            SELECT SUM(cantidad_liquidacion) 
                            FROM detalle_certificados 
                            WHERE certificado_id = ?
                        ), 0),
                        total_pendiente = monto_total - COALESCE((
                            SELECT SUM(cantidad_liquidacion) 
                            FROM detalle_certificados 
                            WHERE certificado_id = ?
                        ), 0)
                    WHERE id = ?";
                
                $stmtUpdate = $this->certificateModel->db->prepare($queryUpdate);
                if ($stmtUpdate->execute([$certId, $certId, $certId])) {
                    error_log("✓ Total pendiente actualizado para certificado $certId");
                    
                    // Obtener el nuevo total_pendiente
                    $queryGetCertNuevo = "SELECT total_pendiente FROM certificados WHERE id = ?";
                    $stmtGetCertNuevo = $this->certificateModel->db->prepare($queryGetCertNuevo);
                    $stmtGetCertNuevo->execute([$certId]);
                    $certNuevo = $stmtGetCertNuevo->fetch();
                    $totalPendienteNuevo = floatval($certNuevo['total_pendiente'] ?? 0);
                    
                    // Obtener codigo_completo para actualizar col4
                    $queryGetCodigo = "SELECT codigo_completo FROM detalle_certificados WHERE certificado_id = ? LIMIT 1";
                    $stmtGetCodigo = $this->certificateModel->db->prepare($queryGetCodigo);
                    $stmtGetCodigo->execute([$certId]);
                    $resultCodigo = $stmtGetCodigo->fetch();
                    $codigoCompleto = $resultCodigo['codigo_completo'] ?? null;
                    
                    // Restar la cantidad liquidada de col4 e incrementar saldo_disponible
                    if ($codigoCompleto && $totalPendienteNuevo > 0) {
                        $queryUpdatePresupuesto = "UPDATE presupuesto_items SET col4 = COALESCE(col4, 0) - ?, saldo_disponible = COALESCE(saldo_disponible, 0) + ? WHERE codigo_completo = ?";
                        $stmtUpdatePresupuesto = $this->certificateModel->db->prepare($queryUpdatePresupuesto);
                        $stmtUpdatePresupuesto->execute([$totalPendienteNuevo, $totalPendienteNuevo, $codigoCompleto]);
                        error_log("✓ Presupuesto actualizado: codigo=$codigoCompleto, col4-=$totalPendienteNuevo, saldo_disponible+=$totalPendienteNuevo");
                    }
                } else {
                    error_log("✗ Error al actualizar total pendiente para certificado $certId");
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => "Se guardaron $guardadas liquidaciones correctamente"
            ]);
        } catch (Exception $e) {
            error_log("Error en saveLiquidacionesAction: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
?>
