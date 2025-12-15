<?php
/**
 * Modelo de Liquidaciones
 * 
 * Maneja todas las operaciones de liquidaciones:
 * - Crear nuevas liquidaciones
 * - Actualizar liquidaciones existentes
 * - Eliminar liquidaciones
 * - Obtener información de liquidaciones
 */

namespace App\Models;

class Liquidacion
{
    private $db;
    
    public function __construct($database)
    {
        $this->db = $database;
    }
    
    /**
     * CREAR UNA NUEVA LIQUIDACIÓN
     * 
     * @param int $detalle_certificado_id - ID del detalle a liquidar
     * @param float $cantidad - Monto a liquidar
     * @param string $descripcion - Descripción/notas opcionales
     * @param string $usuario - Usuario que realiza la liquidación
     * @return array - Resultado con id de liquidación o error
     */
    public function crearLiquidacion($detalle_certificado_id, $cantidad, $descripcion = '', $usuario = 'SISTEMA')
    {
        try {
            // Validar que el detalle exista
            $detalle = $this->db->query(
                "SELECT monto FROM detalle_certificados WHERE id = ?",
                [$detalle_certificado_id]
            );
            
            if (empty($detalle)) {
                return [
                    'exito' => false,
                    'error' => 'El detalle de certificado no existe'
                ];
            }
            
            // Validar que no se liquide más de lo que hay
            $monto_original = $detalle[0]['monto'];
            $total_liquidado = $this->obtenerTotalLiquidado($detalle_certificado_id);
            
            if ($total_liquidado + $cantidad > $monto_original) {
                return [
                    'exito' => false,
                    'error' => "No puedes liquidar más de lo presupuestado. Máximo: " . ($monto_original - $total_liquidado)
                ];
            }
            
            // Insertar la liquidación
            $sql = "INSERT INTO liquidaciones 
                    (detalle_certificado_id, cantidad_liquidacion, descripcion, usuario_creacion, fecha_liquidacion)
                    VALUES (?, ?, ?, ?, CURDATE())";
            
            $resultado = $this->db->execute($sql, [
                $detalle_certificado_id,
                $cantidad,
                $descripcion,
                $usuario
            ]);
            
            return [
                'exito' => true,
                'id' => $this->db->lastInsertId(),
                'mensaje' => "Liquidación creada exitosamente"
            ];
            
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => 'Error al crear liquidación: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * OBTENER TOTAL LIQUIDADO DE UN DETALLE
     * 
     * @param int $detalle_certificado_id
     * @return float
     */
    public function obtenerTotalLiquidado($detalle_certificado_id)
    {
        $resultado = $this->db->query(
            "SELECT COALESCE(SUM(cantidad_liquidacion), 0) AS total 
             FROM liquidaciones 
             WHERE detalle_certificado_id = ?",
            [$detalle_certificado_id]
        );
        
        return $resultado[0]['total'] ?? 0;
    }
    
    /**
     * OBTENER CANTIDAD PENDIENTE DE UN DETALLE
     * 
     * @param int $detalle_certificado_id
     * @return float
     */
    public function obtenerPendiente($detalle_certificado_id)
    {
        $resultado = $this->db->query(
            "SELECT 
                dc.monto,
                COALESCE(SUM(l.cantidad_liquidacion), 0) AS total_liquidado
             FROM detalle_certificados dc
             LEFT JOIN liquidaciones l ON l.detalle_certificado_id = dc.id
             WHERE dc.id = ?
             GROUP BY dc.id, dc.monto",
            [$detalle_certificado_id]
        );
        
        if (empty($resultado)) {
            return 0;
        }
        
        $monto = $resultado[0]['monto'];
        $total_liquidado = $resultado[0]['total_liquidado'];
        
        return $monto - $total_liquidado;
    }
    
    /**
     * OBTENER TODAS LAS LIQUIDACIONES DE UN DETALLE
     * 
     * @param int $detalle_certificado_id
     * @return array
     */
    public function obtenerLiquidacionesPorDetalle($detalle_certificado_id)
    {
        $sql = "SELECT 
                    id,
                    detalle_certificado_id,
                    cantidad_liquidacion,
                    fecha_liquidacion,
                    descripcion,
                    usuario_creacion,
                    fecha_creacion
                FROM liquidaciones
                WHERE detalle_certificado_id = ?
                ORDER BY fecha_liquidacion DESC, id DESC";
        
        return $this->db->query($sql, [$detalle_certificado_id]);
    }
    
    /**
     * OBTENER UNA LIQUIDACIÓN ESPECÍFICA
     * 
     * @param int $liquidacion_id
     * @return array|null
     */
    public function obtenerLiquidacion($liquidacion_id)
    {
        $resultado = $this->db->query(
            "SELECT * FROM liquidaciones WHERE id = ?",
            [$liquidacion_id]
        );
        
        return $resultado[0] ?? null;
    }
    
    /**
     * ACTUALIZAR UNA LIQUIDACIÓN
     * 
     * @param int $liquidacion_id
     * @param float $cantidad - Nuevo monto
     * @param string $descripcion - Nueva descripción
     * @return array
     */
    public function actualizarLiquidacion($liquidacion_id, $cantidad, $descripcion = '')
    {
        try {
            // Obtener la liquidación actual
            $liquidacion = $this->obtenerLiquidacion($liquidacion_id);
            
            if (!$liquidacion) {
                return [
                    'exito' => false,
                    'error' => 'La liquidación no existe'
                ];
            }
            
            // Validar nuevo monto
            $detalle_id = $liquidacion['detalle_certificado_id'];
            $total_liquidado = $this->obtenerTotalLiquidado($detalle_id);
            $monto_actual = $liquidacion['cantidad_liquidacion'];
            $nuevo_total = ($total_liquidado - $monto_actual) + $cantidad;
            
            // Obtener monto original del detalle
            $detalle = $this->db->query(
                "SELECT monto FROM detalle_certificados WHERE id = ?",
                [$detalle_id]
            );
            
            if ($nuevo_total > $detalle[0]['monto']) {
                return [
                    'exito' => false,
                    'error' => 'El nuevo monto excede lo presupuestado'
                ];
            }
            
            // Actualizar
            $sql = "UPDATE liquidaciones 
                    SET cantidad_liquidacion = ?, 
                        descripcion = ?,
                        fecha_actualizacion = NOW()
                    WHERE id = ?";
            
            $this->db->execute($sql, [$cantidad, $descripcion, $liquidacion_id]);
            
            return [
                'exito' => true,
                'mensaje' => 'Liquidación actualizada exitosamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => 'Error al actualizar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * ELIMINAR UNA LIQUIDACIÓN
     * 
     * @param int $liquidacion_id
     * @return array
     */
    public function eliminarLiquidacion($liquidacion_id)
    {
        try {
            $sql = "DELETE FROM liquidaciones WHERE id = ?";
            $this->db->execute($sql, [$liquidacion_id]);
            
            return [
                'exito' => true,
                'mensaje' => 'Liquidación eliminada exitosamente'
            ];
            
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => 'Error al eliminar: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * OBTENER RESUMEN DE LIQUIDACIONES (VISTA)
     * 
     * @param int $detalle_certificado_id (opcional)
     * @return array
     */
    public function obtenerResumen($detalle_certificado_id = null)
    {
        $sql = "SELECT * FROM detalle_liquidaciones";
        $params = [];
        
        if ($detalle_certificado_id) {
            $sql .= " WHERE detalle_id = ?";
            $params[] = $detalle_certificado_id;
        }
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * OBTENER AUDITORÍA DE CAMBIOS
     * 
     * @param int $liquidacion_id (opcional)
     * @return array
     */
    public function obtenerAuditoria($liquidacion_id = null)
    {
        $sql = "SELECT * FROM auditoria_liquidaciones";
        $params = [];
        
        if ($liquidacion_id) {
            $sql .= " WHERE liquidacion_id = ?";
            $params[] = $liquidacion_id;
        }
        
        $sql .= " ORDER BY fecha_cambio DESC";
        
        return $this->db->query($sql, $params);
    }
}
?>
