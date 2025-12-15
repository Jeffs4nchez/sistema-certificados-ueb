<?php
/**
 * Controlador de Liquidaciones - API REST
 * 
 * Maneja las peticiones HTTP para liquidaciones:
 * POST   /api/liquidaciones          - Crear nueva liquidación
 * GET    /api/liquidaciones/{id}     - Obtener una liquidación
 * PUT    /api/liquidaciones/{id}     - Actualizar liquidación
 * DELETE /api/liquidaciones/{id}     - Eliminar liquidación
 * GET    /api/detalles/{id}/liquidaciones - Ver todas las de un detalle
 */

namespace App\Controllers;

use App\Models\Liquidacion;

class LiquidacionController
{
    private $liquidacionModel;
    private $db;
    
    public function __construct($database)
    {
        $this->db = $database;
        $this->liquidacionModel = new Liquidacion($database);
    }
    
    /**
     * CREAR UNA NUEVA LIQUIDACIÓN
     * POST /api/liquidaciones
     */
    public function crear()
    {
        try {
            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                return $this->response([
                    'exito' => false,
                    'error' => 'Método no permitido. Use POST'
                ]);
            }
            
            // Obtener datos del request
            $data = json_decode(file_get_contents("php://input"), true);
            
            // Validar datos obligatorios
            if (!isset($data['detalle_certificado_id']) || !isset($data['cantidad_liquidacion'])) {
                http_response_code(400);
                return $this->response([
                    'exito' => false,
                    'error' => 'Faltan datos: detalle_certificado_id y cantidad_liquidacion son obligatorios'
                ]);
            }
            
            // Crear liquidación
            $resultado = $this->liquidacionModel->crearLiquidacion(
                $data['detalle_certificado_id'],
                $data['cantidad_liquidacion'],
                $data['descripcion'] ?? '',
                $data['usuario'] ?? $_SESSION['usuario'] ?? 'SISTEMA'
            );
            
            if (!$resultado['exito']) {
                http_response_code(400);
            }
            
            return $this->response($resultado);
            
        } catch (\Exception $e) {
            http_response_code(500);
            return $this->response([
                'exito' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * OBTENER LIQUIDACIONES DE UN DETALLE
     * GET /api/detalles/{detalle_id}/liquidaciones
     */
    public function obtenerPorDetalle($detalle_id)
    {
        try {
            $liquidaciones = $this->liquidacionModel->obtenerLiquidacionesPorDetalle($detalle_id);
            $resumen = $this->liquidacionModel->obtenerResumen($detalle_id);
            
            return $this->response([
                'exito' => true,
                'liquidaciones' => $liquidaciones,
                'resumen' => $resumen[0] ?? null,
                'total' => count($liquidaciones)
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            return $this->response([
                'exito' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * OBTENER UNA LIQUIDACIÓN ESPECÍFICA
     * GET /api/liquidaciones/{id}
     */
    public function obtenerUna($id)
    {
        try {
            $liquidacion = $this->liquidacionModel->obtenerLiquidacion($id);
            
            if (!$liquidacion) {
                http_response_code(404);
                return $this->response([
                    'exito' => false,
                    'error' => 'Liquidación no encontrada'
                ]);
            }
            
            return $this->response([
                'exito' => true,
                'data' => $liquidacion
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            return $this->response([
                'exito' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * ACTUALIZAR UNA LIQUIDACIÓN
     * PUT /api/liquidaciones/{id}
     */
    public function actualizar($id)
    {
        try {
            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                http_response_code(405);
                return $this->response([
                    'exito' => false,
                    'error' => 'Método no permitido. Use PUT'
                ]);
            }
            
            // Obtener datos
            $data = json_decode(file_get_contents("php://input"), true);
            
            if (!isset($data['cantidad_liquidacion'])) {
                http_response_code(400);
                return $this->response([
                    'exito' => false,
                    'error' => 'cantidad_liquidacion es obligatorio'
                ]);
            }
            
            // Actualizar
            $resultado = $this->liquidacionModel->actualizarLiquidacion(
                $id,
                $data['cantidad_liquidacion'],
                $data['descripcion'] ?? ''
            );
            
            if (!$resultado['exito']) {
                http_response_code(400);
            }
            
            return $this->response($resultado);
            
        } catch (\Exception $e) {
            http_response_code(500);
            return $this->response([
                'exito' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * ELIMINAR UNA LIQUIDACIÓN
     * DELETE /api/liquidaciones/{id}
     */
    public function eliminar($id)
    {
        try {
            // Validar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                return $this->response([
                    'exito' => false,
                    'error' => 'Método no permitido. Use DELETE'
                ]);
            }
            
            // Eliminar
            $resultado = $this->liquidacionModel->eliminarLiquidacion($id);
            
            return $this->response($resultado);
            
        } catch (\Exception $e) {
            http_response_code(500);
            return $this->response([
                'exito' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * OBTENER HISTORIAL DE CAMBIOS (AUDITORÍA)
     * GET /api/liquidaciones/{id}/auditoria
     */
    public function obtenerAuditoria($id)
    {
        try {
            $auditoria = $this->liquidacionModel->obtenerAuditoria($id);
            
            return $this->response([
                'exito' => true,
                'cambios' => $auditoria,
                'total' => count($auditoria)
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            return $this->response([
                'exito' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Enviar respuesta JSON
     */
    private function response($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
?>
