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
        // Obtener año de trabajo actual
        require_once __DIR__ . '/../controllers/AuthController.php';
        $year = AuthController::obtenerAnoTrabajo();
        
        // Obtener certificados según el rol del usuario y el año
        if (PermisosHelper::esAdmin()) {
            // Admin ve todos del año seleccionado
            $certificates = $this->certificateModel->getAllByYear($year);
        } else {
            // Operador solo ve sus certificados del año seleccionado
            $usuario_id = PermisosHelper::getUsuarioIdActual();
            $certificates = $this->certificateModel->getByUsuarioAndYear($usuario_id, $year);
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
                
                // VALIDACIÓN: Verificar que existan presupuestos para el año actual
                $yearActual = $_SESSION['year'] ?? date('Y');
                $db = Database::getInstance()->getConnection();
                $stmtPresupuesto = $db->prepare("SELECT COUNT(*) as total FROM presupuesto_items WHERE year = ?");
                $stmtPresupuesto->execute([$yearActual]);
                $resultPresupuesto = $stmtPresupuesto->fetch();
                
                if ($resultPresupuesto['total'] == 0) {
                    throw new Exception("❌ No se puede crear certificados sin presupuesto.\n\nDebes cargar el archivo de presupuestos para el año {$yearActual} antes de crear certificados.\n\nVe a: Presupuestos > Cargar Presupuesto");
                }
                
                // VALIDACIÓN: Verificar campos obligatorios
                $camposObligatorios = [
                    'name' => 'Institución',
                    'seccion_memorando' => 'Sección / Memorando',
                    'descripcion_general' => 'Descripción General',
                    'unid_ejecutora' => 'Unidad Ejecutora',
                    'unid_desc' => 'Descripción Unidad Ejecutora',
                    'clase_registro' => 'Clase de Registro',
                    'clase_gasto' => 'Clase de Gasto',
                    'tipo_doc_respaldo' => 'Tipo de Documento Respaldo',
                    'clase_doc_respaldo' => 'Clase de Documento Respaldo'
                ];
                
                $erroresValidacion = [];
                foreach ($camposObligatorios as $campo => $etiqueta) {
                    if (empty(trim($_POST[$campo] ?? ''))) {
                        $erroresValidacion[] = "El campo '{$etiqueta}' es obligatorio";
                    }
                }
                
                if (!empty($erroresValidacion)) {
                    throw new Exception("❌ Por favor completa todos los campos:\n" . implode("\n", $erroresValidacion));
                }
                
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
                    'institucion' => trim($_POST['name']),
                    'seccion_memorando' => trim($_POST['seccion_memorando']),
                    'descripcion' => trim($_POST['descripcion_general']),
                    'fecha_elaboracion' => $fechaElaboracion,
                    'monto_total' => 0, // Se calculará de los items
                    'unid_ejecutora' => trim($_POST['unid_ejecutora']),
                    'unid_desc' => trim($_POST['unid_desc']),
                    'clase_registro' => trim($_POST['clase_registro']),
                    'clase_gasto' => trim($_POST['clase_gasto']),
                    'tipo_doc_respaldo' => trim($_POST['tipo_doc_respaldo']),
                    'clase_doc_respaldo' => trim($_POST['clase_doc_respaldo']),
                    'usuario_id' => $_SESSION['usuario_id'] ?? null,
                    'usuario_creacion' => ($_SESSION['usuario_nombre'] ?? 'Sistema'),
                    'year' => $_SESSION['year'] ?? date('Y')
                ];

                error_log('Datos del certificado: ' . print_r($certificateData, true));

                // Parsear los items desde JSON
                $itemsJson = $_POST['items_data'] ?? '[]';
                $items = json_decode($itemsJson, true);
                
                // VALIDACIÓN: Verificar que haya al menos 1 item
                if (empty($items) || !is_array($items) || count($items) === 0) {
                    throw new Exception("❌ Debes agregar al menos 1 item al certificado");
                }
                
                // DEBUG logging
                error_log('=== CERTIFICADO CREATE DEBUG ===');
                error_log('Items JSON recibido: ' . $itemsJson);
                error_log('Items decodificados: ' . print_r($items, true));
                error_log('Count items: ' . count($items ?? []));

                // VALIDACIÓN: Verificar que cada monto NO exceda el monto codificado
                if (is_array($items) && count($items) > 0) {
                    $erroresValidacion = [];
                    $yearActual = $_SESSION['year'] ?? date('Y');
                    foreach ($items as $index => $item) {
                        $montoItem = floatval($item['monto'] ?? 0);
                        
                        // Obtener monto codificado del presupuesto DEL AÑO ACTUAL
                        $montoCoificado = $this->certificateItemModel->getMontoCoificado(
                            $item['programa_codigo'] ?? '',
                            $item['subprograma_codigo'] ?? '',
                            $item['proyecto_codigo'] ?? '',
                            $item['actividad_codigo'] ?? '',
                            $item['fuente_codigo'] ?? '',
                            $item['ubicacion_codigo'] ?? '',
                            $item['item_codigo'] ?? '',
                            $yearActual
                        );
                        
                        error_log("Item " . ($index + 1) . ": Monto=$montoItem, Codificado=$montoCoificado, Año=$yearActual");
                        
                        // Si el monto codificado es 0, el item NO existe en el presupuesto
                        if ($montoCoificado == 0) {
                            $erroresValidacion[] = "Item #" . ($index + 1) . " NO ENCONTRADO en el presupuesto de " . $yearActual . ". Código: " . ($item['item_codigo'] ?? 'desconocido');
                        }
                        // Si el monto ingresado es MAYOR al codificado, error
                        else if ($montoItem > $montoCoificado) {
                            $erroresValidacion[] = "Item #" . ($index + 1) . ": Monto ingresado ($" . number_format($montoItem, 2) . ") excede el presupuesto ($" . number_format($montoCoificado, 2) . ") en " . $yearActual;
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
                            'codigo_completo' => $codigoCompleto,
                            'year' => $_SESSION['year'] ?? date('Y')
                        ];
                        
                        try {
                            $this->certificateModel->createDetail($detailData);
                        } catch (Exception $detailError) {
                            $_SESSION['error'] = 'Error al guardar item: ' . $detailError->getMessage();
                            error_log('Error guardando detalle: ' . $detailError->getMessage());
                        }
                    }
                }

                // Actualizar el certificado: total_pendiente = monto_total (al inicio, sin liquidar nada)
                $db = Database::getInstance()->getConnection();
                $stmtUpdate = $db->prepare("
                    UPDATE certificados 
                    SET total_pendiente = monto_total 
                    WHERE id = ?
                ");
                $stmtUpdate->execute([$certificateId]);

                error_log('✓ Certificado creado exitosamente. ID: ' . $certificateId);
                error_log('✓ Total pendiente establecido = Monto total: ' . $montoTotal);
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
        
        // Obtener los items agregados al certificado
        $certificateItems = $this->certificateModel->getCertificateDetails($id);
        
        // Convertir items a formato esperado por el formulario
        $itemsForForm = [];
        if (is_array($certificateItems)) {
            foreach ($certificateItems as $item) {
                $itemsForForm[] = [
                    'id' => $item['id'],
                    'item_id' => $item['item_codigo'],
                    'programa_id' => 0,
                    'subprograma_id' => 0,
                    'proyecto_id' => 0,
                    'actividad_id' => 0,
                    'programa_codigo' => $item['programa_codigo'],
                    'subprograma_codigo' => $item['subprograma_codigo'],
                    'proyecto_codigo' => $item['proyecto_codigo'],
                    'actividad_codigo' => $item['actividad_codigo'],
                    'item_codigo' => $item['item_codigo'],
                    'ubicacion_id' => 0,
                    'ubicacion_codigo' => $item['ubicacion_codigo'],
                    'fuente_id' => 0,
                    'fuente_codigo' => $item['fuente_codigo'],
                    'organismo_id' => $item['organismo_id'] ?? 0,
                    'organismo_codigo' => $item['organismo_codigo'],
                    'naturaleza_id' => $item['naturaleza_id'] ?? 0,
                    'naturaleza_codigo' => $item['naturaleza_codigo'],
                    'item_descripcion' => $item['descripcion_item'],
                    'monto' => floatval($item['monto']),
                    'certificado_id' => $id
                ];
            }
        }
        
        // Convertir a JSON para pasar a la vista
        $itemsJson = json_encode($itemsForForm);
        
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
    
    /**
     * Actualizar certificado (vía AJAX desde modal)
     */
    public function updateAction($id) {
        // Limpiar output buffer
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        try {
            error_log('=== UPDATE CERTIFICATE DEBUG ===');
            error_log('ID: ' . $id);
            error_log('POST data: ' . print_r($_POST, true));
            
            // Cargar helper de permisos
            require_once __DIR__ . '/../helpers/PermisosHelper.php';
            
            // Validar que sea admin
            if (!PermisosHelper::puedeEditarCertificado(null)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Solo administradores pueden editar certificados.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Obtener el certificado actual
            $certificate = $this->certificateModel->getById($id);
            if (!$certificate) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Certificado no encontrado'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Preparar datos para actualización
            $data = [
                'numero_certificado' => isset($_POST['numero_certificado']) ? trim($_POST['numero_certificado']) : $certificate['numero_certificado'],
                'institucion' => isset($_POST['institucion']) ? trim($_POST['institucion']) : '',
                'seccion_memorando' => isset($_POST['seccion_memorando']) ? trim($_POST['seccion_memorando']) : '',
                'descripcion' => isset($_POST['descripcion_general']) ? trim($_POST['descripcion_general']) : '',
                'fecha_elaboracion' => isset($_POST['fecha_elaboracion']) ? trim($_POST['fecha_elaboracion']) : $certificate['fecha_elaboracion'],
                'unid_ejecutora' => isset($_POST['unid_ejecutora']) ? trim($_POST['unid_ejecutora']) : '',
                'unid_desc' => isset($_POST['unid_desc']) ? trim($_POST['unid_desc']) : '',
                'clase_registro' => isset($_POST['clase_registro']) ? trim($_POST['clase_registro']) : '',
                'clase_gasto' => isset($_POST['clase_gasto']) ? trim($_POST['clase_gasto']) : '',
                'tipo_doc_respaldo' => isset($_POST['tipo_doc_respaldo']) ? trim($_POST['tipo_doc_respaldo']) : '',
                'clase_doc_respaldo' => isset($_POST['clase_doc_respaldo']) ? trim($_POST['clase_doc_respaldo']) : ''
            ];
            
            error_log('Datos a actualizar: ' . json_encode($data));
            
            // Ejecutar actualización
            $result = $this->certificateModel->updateCertificate($id, $data);
            
            if ($result) {
                error_log('✓ Certificado actualizado correctamente');
                echo json_encode([
                    'success' => true,
                    'message' => 'Certificado actualizado correctamente'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                error_log('❌ updateCertificate retornó false');
                throw new Exception('No se pudo actualizar el certificado en la base de datos');
            }
            exit;
        } catch (Exception $e) {
            error_log('❌ Exception en updateAction: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
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
            $errores = [];
            
            foreach ($data as $item) {
                $detalleId = $item['detalle_id'] ?? null;
                $cantidadLiquidacion = floatval($item['cantidad_liquidacion'] ?? 0);
                $memorando = $item['memorando'] ?? '';
                
                if (!$detalleId) continue;
                
                try {
                    // USAR EL MÉTODO DEL MODELO QUE HACE TODO CORRECTAMENTE
                    $resultado = $this->certificateModel->updateLiquidacion($detalleId, $cantidadLiquidacion, $memorando);
                    
                    // Si updateLiquidacion fue exitoso
                    if ($resultado['success']) {
                        error_log("✅ Liquidación guardada correctamente: detalle_id=$detalleId, cantidad_liq=$cantidadLiquidacion, cantidad_pend=" . $resultado['cantidad_pendiente'] . ", memorando=$memorando");
                        $guardadas++;
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "Error en detalle $detalleId: " . $e->getMessage();
                    error_log("❌ Error en liquidación detalle $detalleId: " . $e->getMessage());
                    error_log("❌ TRACE: " . $e->getTraceAsString());
                }
            }
            
            $mensaje = "Se guardaron $guardadas liquidaciones correctamente";
            $exito = true;
            
            if (!empty($errores)) {
                $mensaje .= ". Errores: " . implode("; ", $errores);
                $exito = false;
            }
            
            echo json_encode([
                'success' => $exito, 
                'message' => $mensaje,
                'guardadas' => $guardadas
            ]);
        } catch (Exception $e) {
            error_log("Error en saveLiquidacionesAction: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Acción para exportar certificados a CSV
     */
    public function exportAction() {
        // Obtener año de sesión
        $year = $_SESSION['year'] ?? date('Y');
        
        // Obtener certificados según el rol del usuario Y EL AÑO
        if (PermisosHelper::esAdmin()) {
            // Admin ve todos del año actual
            $certificates = $this->certificateModel->getAllByYear($year);
        } else {
            // Operador solo ve sus certificados del año actual
            $usuario_id = PermisosHelper::getUsuarioIdActual();
            $certificates = $this->certificateModel->getByUsuarioAndYear($usuario_id, $year);
        }
        
        // Crear CSV
        $filename = 'Reporte_Certificados_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Headers para descargar como archivo
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Abrir el buffer de salida
        $output = fopen('php://output', 'w');
        
        // Escribir BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados principales del certificado
        $headers = [
            'Número Certificado',
            'Institución',
            'Usuario',
            'Fecha',
            'Monto Total',
            'Liquidado',
            'Pendiente',
            'Sección Memorando',
            'Descripción'
        ];
        
        fputcsv($output, $headers, ';');
        
        // Escribir datos de certificados y sus detalles
        foreach ($certificates as $cert) {
            // Fila del certificado principal
            $row = [
                $cert['numero_certificado'] ?? 'N/A',
                $cert['institucion'] ?? '',
                $cert['usuario_creacion'] ?? 'Sistema',
                date('d/m/Y', strtotime($cert['fecha_elaboracion'] ?? '2025-01-01')),
                number_format($cert['monto_total'] ?? 0, 2, '.', ''),
                number_format($cert['total_liquidado'] ?? 0, 2, '.', ''),
                number_format($cert['total_pendiente'] ?? 0, 2, '.', ''),
                $cert['seccion_memorando'] ?? '',
                $cert['descripcion'] ?? ''
            ];
            fputcsv($output, $row, ';');
            
            // Obtener detalles del certificado
            $db = Database::getInstance()->getConnection();
            $stmtDetails = $db->prepare("
                SELECT 
                    programa_codigo,
                    subprograma_codigo,
                    proyecto_codigo,
                    actividad_codigo,
                    item_codigo,
                    ubicacion_codigo,
                    fuente_codigo,
                    organismo_codigo,
                    naturaleza_codigo,
                    descripcion_item,
                    monto
                FROM detalle_certificados
                WHERE certificado_id = ?
                ORDER BY id ASC
            ");
            $stmtDetails->execute([$cert['id']]);
            $detalles = $stmtDetails->fetchAll();
            
            // Escribir detalles con indentación
            if (!empty($detalles)) {
                // Encabezado de detalles
                $detailHeaders = [
                    '  → Detalle - Programa',
                    'Subprograma',
                    'Proyecto',
                    'Actividad',
                    'Item',
                    'Ubicación',
                    'Fuente',
                    'Organismo',
                    'Naturaleza',
                    'Descripción Item',
                    'Monto'
                ];
                fputcsv($output, $detailHeaders, ';');
                
                // Datos de detalles
                foreach ($detalles as $detalle) {
                    $detailRow = [
                        '  ' . ($detalle['programa_codigo'] ?? ''),
                        $detalle['subprograma_codigo'] ?? '',
                        $detalle['proyecto_codigo'] ?? '',
                        $detalle['actividad_codigo'] ?? '',
                        $detalle['item_codigo'] ?? '',
                        $detalle['ubicacion_codigo'] ?? '',
                        $detalle['fuente_codigo'] ?? '',
                        $detalle['organismo_codigo'] ?? '',
                        $detalle['naturaleza_codigo'] ?? '',
                        $detalle['descripcion_item'] ?? '',
                        number_format($detalle['monto'] ?? 0, 2, '.', '')
                    ];
                    fputcsv($output, $detailRow, ';');
                }
            }
            
            // Línea en blanco entre certificados
            fputcsv($output, [], ';');
        }
        
        fclose($output);
        exit;
    }
}
?>
