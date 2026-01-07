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
     * Obtener todos los certificados - totales en BD
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM certificados ORDER BY id DESC");
        return $stmt ? $stmt->fetchAll() : array();
    }

    /**
     * Obtener certificados por usuario (para operadores) - totales en BD
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
     * Actualizar presupuesto_items: sumar a col4 y restar de saldo_disponible
     */
    private function updatePresupuestoAddCertificado($codigo_completo, $monto) {
        if (!$codigo_completo || $monto <= 0) {
            return true;
        }
        
        try {
            // Obtener valores actuales
            $stmt = $this->db->prepare("
                SELECT col3, col4, saldo_disponible 
                FROM presupuesto_items 
                WHERE codigo_completo = ?
            ");
            $stmt->execute([$codigo_completo]);
            $presupuesto = $stmt->fetch();
            
            if (!$presupuesto) {
                error_log("⚠️ Presupuesto no encontrado: $codigo_completo");
                return true;
            }
            
            $col3 = (float)($presupuesto['col3'] ?? 0);
            $col4_nuevo = (float)($presupuesto['col4'] ?? 0) + $monto;
            $saldo_nuevo = $col3 - $col4_nuevo;
            
            // Actualizar
            $updateStmt = $this->db->prepare("
                UPDATE presupuesto_items 
                SET col4 = ?,
                    saldo_disponible = ?,
                    fecha_actualizacion = NOW()
                WHERE codigo_completo = ?
            ");
            
            $updateStmt->execute([$col4_nuevo, $saldo_nuevo, $codigo_completo]);
            error_log("✅ Presupuesto AGREGAR: codigo=$codigo_completo, col4=$col4_nuevo, saldo=$saldo_nuevo");
            return true;
        } catch (Exception $e) {
            error_log("❌ Error actualizando presupuesto: " . $e->getMessage());
            return true;
        }
    }
    
    /**
     * Actualizar presupuesto_items: restar de col4 y sumar a saldo_disponible
     */
    private function updatePresupuestoRemoveCertificado($codigo_completo, $monto) {
        if (!$codigo_completo || $monto <= 0) {
            return true;
        }
        
        try {
            // Obtener valores actuales
            $stmt = $this->db->prepare("
                SELECT col3, col4, saldo_disponible 
                FROM presupuesto_items 
                WHERE codigo_completo = ?
            ");
            $stmt->execute([$codigo_completo]);
            $presupuesto = $stmt->fetch();
            
            if (!$presupuesto) {
                error_log("⚠️ Presupuesto no encontrado: $codigo_completo");
                return true;
            }
            
            $col3 = (float)($presupuesto['col3'] ?? 0);
            $col4_nuevo = max(0, (float)($presupuesto['col4'] ?? 0) - $monto);
            $saldo_nuevo = $col3 - $col4_nuevo;
            
            // Actualizar
            $updateStmt = $this->db->prepare("
                UPDATE presupuesto_items 
                SET col4 = ?,
                    saldo_disponible = ?,
                    fecha_actualizacion = NOW()
                WHERE codigo_completo = ?
            ");
            
            $updateStmt->execute([$col4_nuevo, $saldo_nuevo, $codigo_completo]);
            error_log("✅ Presupuesto ELIMINAR: codigo=$codigo_completo, col4=$col4_nuevo, saldo=$saldo_nuevo");
            return true;
        } catch (Exception $e) {
            error_log("❌ Error actualizando presupuesto: " . $e->getMessage());
            return true;
        }
    }

    /**
     * Crear detalle del certificado (item)
     * Inicializa: cantidad_liquidacion = 0, cantidad_pendiente = monto
     * Actualiza col4 y saldo_disponible en presupuesto_items (SIN TRIGGERS)
     */
    public function createDetail($data) {
        $monto = (float)($data['monto'] ?? 0);
        $codigoCompleto = (string)($data['codigo_completo'] ?? '');
        
        // Inicializar cantidad_liquidacion y cantidad_pendiente correctamente
        $cantidad_liquidacion = (float)($data['cantidad_liquidacion'] ?? 0);
        $cantidad_pendiente = $monto - $cantidad_liquidacion;  // SIEMPRE: monto - liquidado
        
        // Insertar en detalle_certificados
        $stmt = $this->db->prepare("
            INSERT INTO detalle_certificados (
                certificado_id, programa_codigo, subprograma_codigo, proyecto_codigo, 
                actividad_codigo, item_codigo, ubicacion_codigo, fuente_codigo, 
                organismo_codigo, naturaleza_codigo, descripcion_item, monto, codigo_completo, 
                cantidad_liquidacion, cantidad_pendiente, fecha_actualizacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
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
            $monto,
            $codigoCompleto,
            $cantidad_liquidacion,
            $cantidad_pendiente,
        ]);
        
        $detailId = $this->db->lastInsertId();
        
        // Actualizar presupuesto_items: sumar monto a col4 y restar de saldo_disponible
        $this->updatePresupuestoAddCertificado($codigoCompleto, $monto);
        
        return $detailId;
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
     * Si el monto cambia, actualiza col4 en presupuesto_items
     */
    public function update($id, $data) {
        // Obtener el detalle actual para comparar monto
        $stmtGet = $this->db->prepare("SELECT monto, codigo_completo FROM detalle_certificados WHERE id = ?");
        $stmtGet->execute([$id]);
        $detalle_actual = $stmtGet->fetch();
        
        if (!$detalle_actual) {
            throw new Exception("Detalle no encontrado: ID $id");
        }
        
        $monto_anterior = (float)($detalle_actual['monto'] ?? 0);
        $codigo_completo = (string)($detalle_actual['codigo_completo'] ?? '');
        $monto_nuevo = (float)($data['monto'] ?? 0);
        $diferencia = $monto_nuevo - $monto_anterior;
        
        // Actualizar el detalle
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
                monto = ?,
                cantidad_pendiente = ?,
                fecha_actualizacion = NOW()
            WHERE id = ?
        ");
        
        $cantidad_liquidacion = (float)($data['cantidad_liquidacion'] ?? 0);
        $cantidad_pendiente_nuevo = $monto_nuevo - $cantidad_liquidacion;
        
        $resultado = $stmt->execute([
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
            $monto_nuevo,
            $cantidad_pendiente_nuevo,
            $id
        ]);
        
        // Si el monto cambió, actualizar presupuesto_items
        if ($diferencia != 0 && $resultado) {
            if ($diferencia > 0) {
                // Monto aumentó: sumar la diferencia a col4
                $this->updatePresupuestoAddCertificado($codigo_completo, $diferencia);
            } else {
                // Monto disminuyó: restar la diferencia de col4
                $this->updatePresupuestoRemoveCertificado($codigo_completo, abs($diferencia));
            }
        }
        
        return $resultado;
    }

    /**
     * Eliminar detalle (item) del certificado
     * Actualiza presupuesto_items: resta monto de col4
     */
    public function deleteDetail($id) {
        // Obtener el detalle a eliminar
        $stmtGet = $this->db->prepare("SELECT monto, codigo_completo, cantidad_pendiente FROM detalle_certificados WHERE id = ?");
        $stmtGet->execute([$id]);
        $detalle = $stmtGet->fetch();
        
        if (!$detalle) {
            throw new Exception("Detalle no encontrado: ID $id");
        }

        $monto = (float)($detalle['monto'] ?? 0);
        $codigo_completo = (string)($detalle['codigo_completo'] ?? '');
        $cantidad_pendiente = (float)($detalle['cantidad_pendiente'] ?? 0);
        
        // Eliminar el detalle
        $stmt = $this->db->prepare("DELETE FROM detalle_certificados WHERE id = ?");
        $resultado = $stmt->execute([$id]);
        
        // Si se eliminó correctamente, actualizar presupuesto_items
        // IMPORTANTE: Solo restar lo que estaba PENDIENTE, no el monto total
        if ($resultado) {
            $this->updatePresupuestoRemoveCertificado($codigo_completo, $cantidad_pendiente);
        }
        
        return $resultado;
    }
    
    /**
     * Eliminar certificado completo
     */
    public function delete($id) {
        // Primero: obtener todos los items del certificado para actualizar presupuesto
        $stmt = $this->db->prepare("SELECT id FROM detalle_certificados WHERE certificado_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        
        // Eliminar cada item (esto actualiza presupuesto automáticamente)
        foreach ($items as $item) {
            $this->deleteDetail($item['id']);
        }
        
        // Segundo: eliminar el certificado maestro
        $stmt = $this->db->prepare("DELETE FROM certificados WHERE id = ?");
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
     * Actualizar liquidación de un detalle - CÓDIGO PURO PHP (SIN TRIGGERS)
     * 
     * LÓGICA CORRECTA:
     * 1. cantidad_pendiente = monto - cantidad_liquidacion (para el item actual)
     * 2. Obtener SUMA TOTAL de cantidad_pendiente de TODOS los items del codigo_completo
     * 3. col4 = suma_total_pendiente (es el valor final)
     * 4. saldo_disponible = col3 - col4
     * 
     * ACTUALIZA:
     * 1. detalle_certificados.cantidad_liquidacion (solo para el item actual)
     * 2. detalle_certificados.cantidad_pendiente (solo para el item actual)
     * 3. presupuesto_items.col4 (suma total de todos los pendientes del codigo_completo)
     * 4. presupuesto_items.saldo_disponible (col3 - col4)
     * 5. certificados.total_liquidado y total_pendiente
     */
    public function updateLiquidacion($detalle_id, $cantidad_liquidacion, $memorando = '') {
        try {
            $cantidad_liquidacion = (float)$cantidad_liquidacion;
            
            // 1. OBTENER DETALLE ACTUAL
            $stmt = $this->db->prepare("SELECT * FROM detalle_certificados WHERE id = ?");
            $stmt->execute([$detalle_id]);
            $detalle = $stmt->fetch();
            
            if (!$detalle) {
                throw new Exception("Detalle no encontrado: ID {$detalle_id}");
            }
            
            $certificado_id = (int)$detalle['certificado_id'];
            $monto_original = (float)$detalle['monto'];
            $codigo_completo = (string)$detalle['codigo_completo'];
            $cantidad_pendiente = (float)$detalle['cantidad_pendiente'];
            
            error_log("📌 Liquidación INICIO: id=$detalle_id, monto=$monto_original, codigo=$codigo_completo, cantidad_liq_input=$cantidad_liquidacion, pendiente=$cantidad_pendiente");
            
            // 2. VALIDAR CANTIDAD NO SEA NEGATIVA
            if ($cantidad_liquidacion < 0) {
                throw new Exception("La liquidación no puede ser negativa");
            }
            
            // 3. VALIDAR QUE NO SUPERE LA CANTIDAD PENDIENTE
            if ($cantidad_liquidacion > $cantidad_pendiente) {
                throw new Exception("La liquidación ($cantidad_liquidacion) no puede superar el saldo pendiente ($cantidad_pendiente)");
            }
            
            // 4. INSERTAR EN TABLA LIQUIDACIONES (crear histórico)
            if ($cantidad_liquidacion > 0) {
                $insertLiquidacion = $this->db->prepare("
                    INSERT INTO liquidaciones (
                        detalle_certificado_id, 
                        cantidad_liquidacion, 
                        fecha_liquidacion, 
                        memorando, 
                        usuario_creacion,
                        fecha_creacion,
                        fecha_actualizacion
                    ) VALUES (?, ?, CURRENT_DATE, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ");
                
                $usuario = $_SESSION['usuario_nombre'] ?? 'SISTEMA';
                
                $insertLiquidacion->execute([
                    $detalle_id,
                    $cantidad_liquidacion,
                    $memorando,
                    $usuario
                ]);
            }
            
            // 5. SUMAR TODAS LAS LIQUIDACIONES y actualizar detalle_certificados
            $stmtSumaLiquidaciones = $this->db->prepare("
                SELECT COALESCE(SUM(cantidad_liquidacion), 0) as total_liquidaciones
                FROM liquidaciones
                WHERE detalle_certificado_id = ?
            ");
            $stmtSumaLiquidaciones->execute([$detalle_id]);
            $resultadoSuma = $stmtSumaLiquidaciones->fetch();
            $cantidad_liquidacion_total = (float)($resultadoSuma['total_liquidaciones'] ?? 0);
            $cantidad_pendiente_nuevo = $monto_original - $cantidad_liquidacion_total;
            
            // Actualizar detalle_certificados con los TOTALES ACUMULADOS
            $updateDetalle = $this->db->prepare("
                UPDATE detalle_certificados 
                SET cantidad_liquidacion = ?,
                    cantidad_pendiente = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            
            $updateDetalle->execute([
                $cantidad_liquidacion_total,
                $cantidad_pendiente_nuevo,
                $detalle_id
            ]);
            
            error_log("✅ Actualizado: detalle_id=$detalle_id, cantidad_liq_total=$cantidad_liquidacion_total, cantidad_pend=$cantidad_pendiente_nuevo");
            
            // 6. VERIFICAR
            $verify = $this->db->prepare("SELECT cantidad_liquidacion, cantidad_pendiente FROM detalle_certificados WHERE id = ?");
            $verify->execute([$detalle_id]);
            $verificacion = $verify->fetch();
            error_log("✅ Verificación: cantidad_liq_en_bd=" . $verificacion['cantidad_liquidacion'] . ", cantidad_pend_en_bd=" . $verificacion['cantidad_pendiente']);
            
            // 6. OBTENER SUMA TOTAL DE CANTIDAD_PENDIENTE DE TODOS LOS ITEMS CON ESTE codigo_completo
            if (!empty($codigo_completo)) {
                $stmtSumaTotal = $this->db->prepare("
                    SELECT COALESCE(SUM(cantidad_pendiente), 0) as suma_total_pendiente
                    FROM detalle_certificados
                    WHERE codigo_completo = ?
                ");
                $stmtSumaTotal->execute([$codigo_completo]);
                $resultado = $stmtSumaTotal->fetch();
                $suma_total_pendiente = (float)($resultado['suma_total_pendiente'] ?? 0);
                
                error_log("✅ Suma total pendiente obtenida: $suma_total_pendiente para codigo=$codigo_completo");
                
                // Obtener presupuesto actual
                $stmtPresupuesto = $this->db->prepare("
                    SELECT col3, col4, saldo_disponible
                    FROM presupuesto_items 
                    WHERE codigo_completo = ?
                ");
                $stmtPresupuesto->execute([$codigo_completo]);
                $presupuesto = $stmtPresupuesto->fetch();
                
                if ($presupuesto) {
                    $col3 = (float)($presupuesto['col3'] ?? 0);
                    $col4_anterior = (float)($presupuesto['col4'] ?? 0);
                    // IMPORTANTE: Usar el cambio neto en liquidación (nueva_suma - cantidad anterior del detalle)
                    $cambio_liquidacion = $cantidad_liquidacion_total - (float)($detalle['cantidad_liquidacion'] ?? 0);
                    $col4_nuevo = $col4_anterior - $cambio_liquidacion;  // col4 -= cambio_liquidacion
                    $saldo_nuevo = $col3 - $col4_nuevo;  // saldo = col3 - col4
                    
                    error_log("📌 Presupuesto ANTES: col3=$col3, col4=$col4_anterior, saldo=" . ($presupuesto['saldo_disponible'] ?? 0));
                    error_log("📌 Cambio liquidación: +$cambio_liquidacion (nueva_suma=$cantidad_liquidacion_total - anterior=" . (float)($detalle['cantidad_liquidacion'] ?? 0) . ")");
                    error_log("📌 Presupuesto NUEVO: col3=$col3, col4=$col4_nuevo, saldo=$saldo_nuevo");
                    
                    $updatePresupuesto = $this->db->prepare("
                        UPDATE presupuesto_items 
                        SET col4 = ?,
                            saldo_disponible = ?,
                            fecha_actualizacion = NOW()
                        WHERE codigo_completo = ?
                    ");
                    
                    $resultado = $updatePresupuesto->execute([
                        $col4_nuevo,
                        $saldo_nuevo,
                        $codigo_completo
                    ]);
                    
                    if (!$resultado) {
                        error_log("❌ Error al actualizar presupuesto_items: " . print_r($updatePresupuesto->errorInfo(), true));
                        throw new Exception("No se pudo actualizar presupuesto_items");
                    }
                    
                    error_log("✅ presupuesto_items actualizado: codigo=$codigo_completo, col4=$col4_nuevo, saldo=$saldo_nuevo");
                } else {
                    error_log("⚠️ Presupuesto no encontrado para codigo=$codigo_completo");
                }
            }
            
            // 7. RECALCULAR TOTALES EN CERTIFICADOS (PHP PURO)
            $stmt = $this->db->prepare("
                SELECT 
                    SUM(monto) as total_monto,
                    SUM(cantidad_liquidacion) as total_liquidado,
                    SUM(cantidad_pendiente) as total_pendiente
                FROM detalle_certificados
                WHERE certificado_id = ?
            ");
            $stmt->execute([$certificado_id]);
            $totales = $stmt->fetch();
            
            $total_monto = (float)($totales['total_monto'] ?? 0);
            $total_liquidado = (float)($totales['total_liquidado'] ?? 0);
            $total_pendiente = (float)($totales['total_pendiente'] ?? 0);
            
            error_log("📌 Certificados ANTES: total_liq_anterior, total_pend_anterior");
            error_log("✅ Certificados NUEVO: total_liq=$total_liquidado, total_pend=$total_pendiente");
            
            // 8. ACTUALIZAR CERTIFICADOS
            $updateCert = $this->db->prepare("
                UPDATE certificados 
                SET 
                    total_liquidado = ?,
                    total_pendiente = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            
            $resultado = $updateCert->execute([
                $total_liquidado,
                $total_pendiente,
                $certificado_id
            ]);
            
            if (!$resultado) {
                error_log("❌ Error al actualizar certificados: " . print_r($updateCert->errorInfo(), true));
                throw new Exception("No se pudo actualizar certificados");
            }
            
            error_log("✅ Certificado actualizado: id=$certificado_id, total_liq=$total_liquidado, total_pend=$total_pendiente");
            
            // 9. DEVOLVER RESULTADO
            return [
                'success' => true,
                'detalle_id' => $detalle_id,
                'cantidad_liquidada' => $cantidad_liquidacion,
                'cantidad_pendiente' => $cantidad_pendiente_nuevo,
                'total_liquidado' => $total_liquidado,
                'total_pendiente' => $total_pendiente
            ];
            
        } catch (Exception $e) {
            error_log("❌ ERROR en liquidación: " . $e->getMessage());
            error_log("❌ TRACE: " . $e->getTraceAsString());
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

    /**
     * Contar certificados de un operador por nombre de usuario
     */
    public function countByOperador($usuario_nombre) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM certificados WHERE usuario_creacion = ?");
        $stmt->execute([$usuario_nombre]);
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    /**
     * Contar certificados de un operador por nombre de usuario y estado
     */
    public function countByOperadorAndStatus($usuario_nombre, $status) {
        if ($status === 'APROBADO') {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM certificados WHERE usuario_creacion = ? AND monto_total > 0");
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM certificados WHERE usuario_creacion = ? AND (monto_total = 0 OR monto_total IS NULL)");
        }
        $stmt->execute([$usuario_nombre]);
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    /**
     * Obtener totales globales de pendiente (no certificado) y liquidado
     * NOTA: "total_monto" ahora es la suma de total_pendiente, no monto_total
     */
    public function getTotalsGlobal() {
        // Obtener total_pendiente de certificados
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_pendiente), 0) as total_pendiente
            FROM certificados
        ");
        $stmt->execute();
        $row = $stmt->fetch();
        $total_pendiente = $row['total_pendiente'] ?? 0;
        
        // Obtener total liquidado de detalles
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(cantidad_liquidacion), 0) as total_liquidado
            FROM detalle_certificados
        ");
        $stmt->execute();
        $row = $stmt->fetch();
        $total_liquidado = $row['total_liquidado'] ?? 0;
        
        return [
            'total_monto' => $total_pendiente,  // Ahora es pendiente, no monto_total
            'total_liquidado' => $total_liquidado
        ];
    }

    /**
     * Obtener totales de pendiente y liquidado por operador
     * NOTA: "total_monto" ahora es la suma de total_pendiente, no monto_total
     */
    public function getTotalsByOperador($usuario_nombre) {
        // Obtener total_pendiente de certificados por operador
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(total_pendiente), 0) as total_pendiente
            FROM certificados
            WHERE usuario_creacion = ?
        ");
        $stmt->execute([$usuario_nombre]);
        $row = $stmt->fetch();
        $total_pendiente = $row['total_pendiente'] ?? 0;
        
        // Obtener total liquidado de detalles del operador
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(dc.cantidad_liquidacion), 0) as total_liquidado
            FROM detalle_certificados dc
            INNER JOIN certificados c ON dc.certificado_id = c.id
            WHERE c.usuario_creacion = ?
        ");
        $stmt->execute([$usuario_nombre]);
        $row = $stmt->fetch();
        $total_liquidado = $row['total_liquidado'] ?? 0;
        
        return [
            'total_monto' => $total_pendiente,  // Ahora es pendiente, no monto_total
            'total_liquidado' => $total_liquidado
        ];
    }

    /**
     * Actualizar liquidación de un item de detalle_certificados
     * Recalcula cantidad_pendiente = monto - cantidad_liquidacion
     */
    public function updateDetailLiquidacion($id, $cantidadLiquidacion) {
        // Obtener el item actual para saber el monto
        $stmtGet = $this->db->prepare("SELECT monto, cantidad_liquidacion FROM detalle_certificados WHERE id = ?");
        $stmtGet->execute([$id]);
        $item = $stmtGet->fetch();
        
        if (!$item) {
            throw new Exception("Item no encontrado");
        }
        
        $cantidadPendiente = $item['monto'] - $cantidadLiquidacion;
        
        // Actualizar el item con la nueva liquidación y cantidad_pendiente
        $stmt = $this->db->prepare("
            UPDATE detalle_certificados 
            SET 
                cantidad_liquidacion = ?,
                cantidad_pendiente = ?,
                fecha_actualizacion = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$cantidadLiquidacion, $cantidadPendiente, $id]);
    }
}
?>
