<?php
/**
 * Modelo de Certificados
 */

if (!class_exists('Database')) {
    require_once __DIR__ . '/../Database.php';
}

class Certificate {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los certificados
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM certificados ORDER BY id DESC");
        return $stmt ? $stmt->fetchAll() : array();
    }

    /**
     * Obtener certificados por usuario (para operadores)
     */
    public function getByUsuario($usuario_id) {
        $stmt = $this->db->prepare("SELECT * FROM certificados WHERE usuario_id = ? ORDER BY id DESC");
        $stmt->execute([$usuario_id]);
        return $stmt ? $stmt->fetchAll() : array();
    }

    /**
     * Obtener certificado por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM certificados WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crear nuevo certificado (tabla maestra)
     */
    public function createCertificate($data) {
        $stmt = $this->db->prepare("
            INSERT INTO certificados (
                numero_certificado, institucion, seccion_memorando, descripcion, 
                fecha_elaboracion, monto_total, unid_ejecutora, unid_desc, 
                clase_registro, clase_gasto, tipo_doc_respaldo, clase_doc_respaldo,
                usuario_id, usuario_creacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['numero_certificado'],
            $data['institucion'],
            $data['seccion_memorando'],
            $data['descripcion'],
            $data['fecha_elaboracion'],
            $data['monto_total'],
            $data['unid_ejecutora'] ?? '',
            $data['unid_desc'] ?? '',
            $data['clase_registro'] ?? '',
            $data['clase_gasto'] ?? '',
            $data['tipo_doc_respaldo'] ?? '',
            $data['clase_doc_respaldo'] ?? '',
            $data['usuario_id'] ?? null,
            $data['usuario_creacion'] ?? ''
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Crear detalle del certificado (item)
     * Los TRIGGERs automáticamente actualizarán presupuesto_items y certificados
     */
    public function createDetail($data) {
        $stmt = $this->db->prepare("
            INSERT INTO detalle_certificados (
                certificado_id, programa_codigo, subprograma_codigo, proyecto_codigo, 
                actividad_codigo, item_codigo, ubicacion_codigo, fuente_codigo, 
                organismo_codigo, naturaleza_codigo, descripcion_item, monto, codigo_completo, fecha_actualizacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            (int)($data['certificado_id'] ?? 0),
            (string)($data['programa_codigo'] ?? ''),
            (string)($data['subprograma_codigo'] ?? ''),
            (string)($data['proyecto_codigo'] ?? ''),
            (string)($data['actividad_codigo'] ?? ''),
            (string)($data['item_codigo'] ?? ''),
            (string)($data['ubicacion_codigo'] ?? ''),
            (string)($data['fuente_codigo'] ?? ''),
            (string)($data['organismo_codigo'] ?? ''),
            (string)($data['naturaleza_codigo'] ?? ''),
            (string)($data['descripcion_item'] ?? ''),
            (float)($data['monto'] ?? 0),
            (string)($data['codigo_completo'] ?? '')
        ]);
        
        // Los TRIGGERs se encargan de actualizar presupuesto_items y certificados automáticamente
        return $this->db->lastInsertId();
    }


    /**
     * Obtener detalles de un certificado
     */
    public function getCertificateDetails($certificado_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM detalle_certificados 
            WHERE certificado_id = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$certificado_id]);
        return $stmt->fetchAll();
    }

    /**
     * Actualizar certificado maestro
     */
    public function updateCertificate($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE certificados SET 
                numero_certificado = ?,
                institucion = ?,
                seccion_memorando = ?,
                descripcion = ?,
                fecha_elaboracion = ?,
                unid_ejecutora = ?,
                unid_desc = ?,
                clase_registro = ?,
                clase_gasto = ?,
                tipo_doc_respaldo = ?,
                clase_doc_respaldo = ?,
                fecha_actualizacion = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['numero_certificado'] ?? '',
            $data['institucion'] ?? '',
            $data['seccion_memorando'] ?? '',
            $data['descripcion'] ?? '',
            $data['fecha_elaboracion'] ?? date('Y-m-d'),
            $data['unid_ejecutora'] ?? '',
            $data['unid_desc'] ?? '',
            $data['clase_registro'] ?? '',
            $data['clase_gasto'] ?? '',
            $data['tipo_doc_respaldo'] ?? '',
            $data['clase_doc_respaldo'] ?? '',
            $id
        ]);
    }

    /**
     * Actualizar detalle (item) del certificado
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE detalle_certificados SET 
                programa_id = ?, 
                subprograma_id = ?, 
                proyecto_id = ?, 
                actividad_id = ?, 
                item_id = ?,
                ubicacion_id = ?,
                fuente_id = ?,
                organismo_id = ?,
                naturaleza_id = ?,
                descripcion_item = ?,
                monto = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['programa_id'] ?? null,
            $data['subprograma_id'] ?? null,
            $data['proyecto_id'] ?? null,
            $data['actividad_id'] ?? null,
            $data['item_id'] ?? null,
            $data['ubicacion_id'] ?? null,
            $data['fuente_id'] ?? null,
            $data['organismo_id'] ?? null,
            $data['naturaleza_id'] ?? null,
            $data['descripcion'] ?? '',
            $data['monto'] ?? 0,
            $id
        ]);
    }

    /**
     * Eliminar certificado
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM detalle_certificados WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Contar certificados por estado (requiere campo estado)
     */
    public function countByStatus($status) {
        // Contar registros con monto > 0 como "APROBADO"
        if ($status === 'APROBADO') {
            $result = $this->db->query("SELECT COUNT(*) as total FROM certificados WHERE monto_total > 0");
        } else {
            $result = $this->db->query("SELECT COUNT(*) as total FROM certificados WHERE monto_total = 0 OR monto_total IS NULL");
        }
        $row = $result->fetch();
        return $row['total'] ?? 0;
    }

    /**
     * Obtener total de certificados
     */
    public function count() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM certificados");
        $row = $result->fetch();
        return $row['total'] ?? 0;
    }

    /**
     * Actualizar liquidación de un detalle
     * (Los TRIGGERs en la BD se encargan de actualizar presupuesto_items y certificados automáticamente)
     */
    public function updateLiquidacion($detalle_id, $cantidad_liquidacion) {
        try {
            // Obtener el detalle actual
            $stmt = $this->db->prepare("SELECT * FROM detalle_certificados WHERE id = ?");
            $stmt->execute([$detalle_id]);
            $detalle = $stmt->fetch();
            
            if (!$detalle) {
                throw new Exception("Detalle no encontrado");
            }
            
            $montoOriginal = (float)$detalle['monto'];
            
            // Calcular el nuevo monto (monto original - cantidad_liquidacion)
            $nuevoMonto = $montoOriginal - $cantidad_liquidacion;
            
            // Actualizar SOLO detalle_certificados con los nuevos valores
            // Los TRIGGERs automáticamente actualizarán presupuesto_items y certificados
            $updateStmt = $this->db->prepare("
                UPDATE detalle_certificados 
                SET cantidad_liquidacion = ?, 
                    monto = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$cantidad_liquidacion, $nuevoMonto, $detalle_id]);
            
            error_log("Liquidación actualizada: detalle_id=$detalle_id, cantidad_liquidacion=$cantidad_liquidacion, nuevo_monto=$nuevoMonto");
            
            return true;
        } catch (Exception $e) {
            error_log("Error actualizando liquidación: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtener totales del presupuesto para el dashboard
     */
    public function getPresupuestoTotals() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_items,
                    SUM(col3) as total_codificado,
                    SUM(col4) as total_certificado,
                    SUM(saldo_disponible) as saldo_disponible
                FROM presupuesto_items
            ");
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error obteniendo totales de presupuesto: " . $e->getMessage());
            return [
                'total_items' => 0,
                'total_codificado' => 0,
                'total_certificado' => 0,
                'saldo_disponible' => 0
            ];
        }
    }
}
?>
