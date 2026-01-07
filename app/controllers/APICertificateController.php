<?php
/**
 * API Controller para Certificados
 * Maneja peticiones AJAX para selects en cascada
 */

class APICertificateController {
    private $certificateItemModel;
    private $db;
    
    public function __construct() {
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/CertificateItem.php';
        $this->db = Database::getInstance()->getConnection();
        $this->certificateItemModel = new CertificateItem($this->db);
    }

    /**
     * Responder en JSON
     */
    private function jsonResponse($success, $data = null, $message = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }

    /**
     * Obtener subprogramas
     */
    public function getSubprogramasAction() {
        $cod_programa = $_GET['cod_programa'] ?? null;
        if (!$cod_programa) {
            $this->jsonResponse(false, null, 'Código de programa requerido');
        }
        $subprogramas = $this->certificateItemModel->getSubprogramasByPrograma($cod_programa);
        $this->jsonResponse(true, $subprogramas);
    }

    /**
     * Obtener proyectos
     */
    public function getProyectosAction() {
        $cod_programa = $_GET['cod_programa'] ?? null;
        $cod_subprograma = $_GET['cod_subprograma'] ?? null;
        if (!$cod_programa || !$cod_subprograma) {
            $this->jsonResponse(false, null, 'Código de programa y subprograma requeridos');
        }
        $proyectos = $this->certificateItemModel->getProyectosBySubprograma($cod_programa, $cod_subprograma);
        $this->jsonResponse(true, $proyectos);
    }

    /**
     * Obtener actividades
     */
    public function getActividadesAction() {
        $cod_programa = $_GET['cod_programa'] ?? null;
        $cod_subprograma = $_GET['cod_subprograma'] ?? null;
        $cod_proyecto = $_GET['cod_proyecto'] ?? null;
        if (!$cod_programa || !$cod_subprograma || !$cod_proyecto) {
            $this->jsonResponse(false, null, 'Códigos requeridos');
        }
        $actividades = $this->certificateItemModel->getActividadesByProyecto($cod_programa, $cod_subprograma, $cod_proyecto);
        $this->jsonResponse(true, $actividades);
    }

    /**
     * Obtener fuentes por actividad
     */
    public function getFuentesAction() {
        $cod_programa = $_GET['cod_programa'] ?? null;
        $cod_subprograma = $_GET['cod_subprograma'] ?? null;
        $cod_proyecto = $_GET['cod_proyecto'] ?? null;
        $cod_actividad = $_GET['cod_actividad'] ?? null;
        if (!$cod_programa || !$cod_subprograma || !$cod_proyecto || !$cod_actividad) {
            $this->jsonResponse(false, null, 'Códigos requeridos');
        }
        $fuentes = $this->certificateItemModel->getFuentesByActividad($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad);
        $this->jsonResponse(true, $fuentes);
    }

    /**
     * Obtener ubicaciones por fuente
     */
    public function getUbicacionesAction() {
        $cod_programa = $_GET['cod_programa'] ?? null;
        $cod_subprograma = $_GET['cod_subprograma'] ?? null;
        $cod_proyecto = $_GET['cod_proyecto'] ?? null;
        $cod_actividad = $_GET['cod_actividad'] ?? null;
        $cod_fuente = $_GET['cod_fuente'] ?? null;
        if (!$cod_programa || !$cod_subprograma || !$cod_proyecto || !$cod_actividad || !$cod_fuente) {
            $this->jsonResponse(false, null, 'Códigos requeridos');
        }
        $ubicaciones = $this->certificateItemModel->getUbicacionesByFuente($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente);
        $this->jsonResponse(true, $ubicaciones);
    }
    /**
     * Obtener items por actividad
     */
    public function getItemsByActividadAction() {
        $cod_programa = $_GET['cod_programa'] ?? null;
        $cod_subprograma = $_GET['cod_subprograma'] ?? null;
        $cod_proyecto = $_GET['cod_proyecto'] ?? null;
        $cod_actividad = $_GET['cod_actividad'] ?? null;
        $cod_fuente = $_GET['cod_fuente'] ?? null;
        $cod_ubicacion = $_GET['cod_ubicacion'] ?? null;
        if (!$cod_programa || !$cod_subprograma || !$cod_proyecto || !$cod_actividad || !$cod_fuente || !$cod_ubicacion) {
            $this->jsonResponse(false, null, 'Códigos requeridos');
        }
        $items = $this->certificateItemModel->getItemsByActividad($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente, $cod_ubicacion);
        $this->jsonResponse(true, $items);
    }

    /**
     * Obtener items por ubicación
     */
    public function getItemsByUbicacionAction() {
        $ubicacion_id = $_GET['ubicacion_id'] ?? null;
        
        if (!$ubicacion_id) {
            $this->jsonResponse(false, null, 'Ubicación ID requerido');
        }
        
        $items = $this->certificateItemModel->getItemsByUbicacion($ubicacion_id);
        $this->jsonResponse(true, $items);
    }

    /**
     * Obtener item completo con toda la jerarquía
     */
    public function getItemCompletoAction() {
        $item_id = $_GET['item_id'] ?? null;
        
        if (!$item_id) {
            $this->jsonResponse(false, null, 'Item ID requerido');
        }
        
        $item = $this->certificateItemModel->getItemCompleto($item_id);
        
        if (!$item) {
            $this->jsonResponse(false, null, 'Item no encontrado');
        }
        
        $this->jsonResponse(true, $item);
    }

    /**
     * Obtener el siguiente número de certificado
     */
    public function getNextCertificateNumberAction() {
        $stmt = $this->db->prepare("SELECT MAX(id) as max_id FROM certificados");
        $stmt->execute();
        $row = $stmt->fetch();
        $maxId = $row['max_id'] ?? 0;
        $nextId = $maxId + 1;
        
        // Formato: CERT-001, CERT-002, CERT-003, etc.
        $numeroCertificado = 'CERT-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
        $this->jsonResponse(true, [
            'numero_certificado' => $numeroCertificado,
            'proximo_id' => $nextId
        ]);
    }

    /**
     * Obtener códigos del presupuesto importado (CODIGOG3, CODIGOG4, CODIGOG5)
     */
    public function getPresupuestoCodigosAction() {
        require_once __DIR__ . '/../models/PresupuestoItem.php';
        $presupuestoModel = new PresupuestoItem();
        
        $codigos = $presupuestoModel->getAll();
        $this->jsonResponse(true, $codigos);
    }

    /**
     * Obtener detalles para liquidación
     */
    public function getLiquidacionAction() {
        $certificate_id = $_GET['certificate_id'] ?? null;
        if (!$certificate_id) {
            $this->jsonResponse(false, null, 'ID de certificado requerido');
        }
        
        try {
            require_once __DIR__ . '/../models/Certificate.php';
            $certificateModel = new Certificate();
            $details = $certificateModel->getCertificateDetails($certificate_id);
            $this->jsonResponse(true, $details);
        } catch (Exception $e) {
            $this->jsonResponse(false, null, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Obtener historial de liquidaciones de un certificado
     */
    public function getLiquidacionHistorialAction() {
        $certificate_id = (int)($_GET['certificate_id'] ?? 0);
        if (!$certificate_id) {
            $this->jsonResponse(false, null, 'ID de certificado requerido');
        }
        
        try {
            // Obtener liquidaciones agrupadas por item
            $stmt = $this->db->prepare("
                SELECT 
                    dc.id as detalle_id,
                    dc.descripcion_item,
                    dc.monto,
                    l.id as liquidacion_id,
                    l.cantidad_liquidacion,
                    l.fecha_liquidacion,
                    l.memorando,
                    l.usuario_creacion
                FROM liquidaciones l
                INNER JOIN detalle_certificados dc ON l.detalle_certificado_id = dc.id
                WHERE dc.certificado_id = ?
                ORDER BY dc.id ASC, l.fecha_liquidacion DESC, l.id DESC
            ");
            $stmt->execute([$certificate_id]);
            $liquidaciones_raw = $stmt->fetchAll();
            
            // Agrupar por item y calcular subtotales
            $liquidaciones_agrupadas = [];
            $total_general = 0;
            
            foreach ($liquidaciones_raw as $row) {
                $detalle_id = $row['detalle_id'];
                
                if (!isset($liquidaciones_agrupadas[$detalle_id])) {
                    $liquidaciones_agrupadas[$detalle_id] = [
                        'detalle_id' => $detalle_id,
                        'descripcion_item' => $row['descripcion_item'],
                        'monto' => (float)$row['monto'],
                        'subtotal' => 0,
                        'liquidaciones' => []
                    ];
                }
                
                $cantidad = (float)$row['cantidad_liquidacion'];
                $liquidaciones_agrupadas[$detalle_id]['subtotal'] += $cantidad;
                $liquidaciones_agrupadas[$detalle_id]['liquidaciones'][] = [
                    'id' => $row['liquidacion_id'],
                    'cantidad' => $cantidad,
                    'fecha' => $row['fecha_liquidacion'],
                    'memorando' => $row['memorando'],
                    'usuario' => $row['usuario_creacion']
                ];
                
                $total_general += $cantidad;
            }
            
            $this->jsonResponse(true, [
                'liquidaciones' => array_values($liquidaciones_agrupadas),
                'total_general' => $total_general
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(false, null, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar liquidación de un detalle
     */
    public function updateLiquidacionAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, null, 'POST requerido');
        }
        
        $detalle_id = $_POST['detalle_id'] ?? null;
        $cantidad_liquidacion = $_POST['cantidad_liquidacion'] ?? 0;
        
        if (!$detalle_id) {
            $this->jsonResponse(false, null, 'ID de detalle requerido');
        }
        
        try {
            require_once __DIR__ . '/../models/Certificate.php';
            $certificateModel = new Certificate();
            $certificateModel->updateLiquidacion($detalle_id, $cantidad_liquidacion);
            $this->jsonResponse(true, null, 'Liquidación actualizada correctamente');
        } catch (Exception $e) {
            $this->jsonResponse(false, null, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Guardar múltiples liquidaciones (batch)
     */
    public function saveLiquidacionesAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, null, 'POST requerido');
        }
        
        try {
            $jsonData = $_POST['liquidaciones'] ?? '[]';
            $data = json_decode($jsonData, true);
            
            if (empty($data)) {
                $this->jsonResponse(false, null, 'No hay liquidaciones para guardar');
            }

            require_once __DIR__ . '/../models/Certificate.php';
            $certificateModel = new Certificate();
            $guardadas = 0;
            $errores = [];
            
            foreach ($data as $item) {
                $detalleId = $item['detalle_id'] ?? null;
                $cantidadLiquidacion = floatval($item['cantidad_liquidacion'] ?? 0);
                $memorando = $item['memorando'] ?? '';
                
                if (!$detalleId) continue;
                
                try {
                    // USAR EL MÉTODO DEL MODELO QUE HACE TODO CORRECTAMENTE
                    $resultado = $certificateModel->updateLiquidacion($detalleId, $cantidadLiquidacion, $memorando);
                    
                    // Si updateLiquidacion fue exitoso
                    if ($resultado['success']) {
                        error_log("✅ [API] Liquidación guardada correctamente: detalle_id=$detalleId, cantidad_liq=$cantidadLiquidacion, cantidad_pend=" . $resultado['cantidad_pendiente'] . ", memorando=$memorando");
                        $guardadas++;
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "Error en detalle $detalleId: " . $e->getMessage();
                    error_log("❌ [API] Error en liquidación detalle $detalleId: " . $e->getMessage());
                    error_log("❌ [API] TRACE: " . $e->getTraceAsString());
                }
            }
            
            $mensaje = "Se guardaron $guardadas liquidaciones correctamente";
            $exito = true;
            
            if (!empty($errores)) {
                $mensaje .= ". Errores: " . implode("; ", $errores);
                $exito = false;
            }
            
            $this->jsonResponse($exito, ['guardadas' => $guardadas], $mensaje);
        } catch (Exception $e) {
            error_log("API saveLiquidacionesAction Error: " . $e->getMessage());
            $this->jsonResponse(false, null, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Obtener monto codificado de un item de presupuesto
     */
    public function getMontoCodicadoAction() {
        $cod_programa = $_GET['cod_programa'] ?? null;
        $cod_subprograma = $_GET['cod_subprograma'] ?? null;
        $cod_proyecto = $_GET['cod_proyecto'] ?? null;
        $cod_actividad = $_GET['cod_actividad'] ?? null;
        $cod_fuente = $_GET['cod_fuente'] ?? null;
        $cod_ubicacion = $_GET['cod_ubicacion'] ?? null;
        $cod_item = $_GET['cod_item'] ?? null;
        
        if (!$cod_programa || !$cod_subprograma || !$cod_proyecto || !$cod_actividad || !$cod_fuente || !$cod_ubicacion || !$cod_item) {
            $this->jsonResponse(false, null, 'Códigos incompletos');
        }
        
        try {
            $montoCoificado = $this->certificateItemModel->getMontoCoificado(
                $cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente, $cod_ubicacion, $cod_item
            );
            
            $this->jsonResponse(true, [
                'monto_codificado' => $montoCoificado,
                'formateado' => number_format($montoCoificado, 2, '.', ',')
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(false, null, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Router dinámico
     */
    public function route($action) {
        // Convertir get-subprogramas -> getSubprogramasAction
        $parts = explode('-', $action);
        $method = lcfirst(implode('', array_map('ucfirst', $parts))) . 'Action';
        
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            // Intentar con la conversión antigua por si acaso
            $oldMethod = ucfirst(str_replace('-', '', $action)) . 'Action';
            if (method_exists($this, $oldMethod)) {
                $this->$oldMethod();
            } else {
                $this->jsonResponse(false, null, 'Acción no encontrada: ' . $action . ' (buscó: ' . $method . ')');
            }
        }
    }

    /**
     * Obtener certificado completo con sus items (para modal de edición)
     */
    public function getCertificateForEditAction() {
        $certificate_id = $_GET['id'] ?? null;
        
        error_log('=== GET CERTIFICATE FOR EDIT ===');
        error_log('Certificate ID: ' . $certificate_id);
        
        if (!$certificate_id) {
            error_log('❌ ID no proporcionado');
            $this->jsonResponse(false, null, 'ID de certificado requerido');
        }
        
        try {
            require_once __DIR__ . '/../models/Certificate.php';
            $certificateModel = new Certificate();
            
            // Obtener certificado maestro
            $certificate = $certificateModel->getById($certificate_id);
            error_log('Certificado obtenido: ' . json_encode($certificate));
            
            if (!$certificate) {
                error_log('❌ Certificado no encontrado: ' . $certificate_id);
                $this->jsonResponse(false, null, 'Certificado no encontrado');
            }
            
            // Obtener items del certificado
            $items = $certificateModel->getCertificateDetails($certificate_id);
            error_log('Items obtenidos: ' . count($items) . ' items');
            
            // Convertir items al formato esperado por el formulario
            $itemsForForm = [];
            if (is_array($items)) {
                foreach ($items as $item) {
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
                        'certificado_id' => $certificate_id
                    ];
                }
            }
            
            error_log('✓ Respondiendo con éxito');
            $this->jsonResponse(true, [
                'certificate' => $certificate,
                'items' => $itemsForForm
            ]);
        } catch (Exception $e) {
            error_log('❌ Exception: ' . $e->getMessage());
            error_log('Stack: ' . $e->getTraceAsString());
            $this->jsonResponse(false, null, 'Error: ' . $e->getMessage());
        }
    }
}
?>
