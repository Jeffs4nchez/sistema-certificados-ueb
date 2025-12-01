<?php
/**
 * Controlador de Dashboard
 */

class DashboardController {
    
    public function indexAction() {
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/Certificate.php';
        require_once __DIR__ . '/../models/Parameter.php';
        require_once __DIR__ . '/../models/PresupuestoItem.php';
        
        $certificateModel = new Certificate();
        $parameterModel = new Parameter();
        $presupuestoModel = new PresupuestoItem();
        
        // Obtener tipo de usuario
        $usuario_tipo = $_SESSION['usuario_tipo'] ?? 'operador';
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
        
        // Estadísticas generales (admin ve todo)
        $totalCertificates = $certificateModel->count();
        $totalPresupuestos = $presupuestoModel->count();
        $resumenPresupuesto = $presupuestoModel->getResumen();
        
        // Agregar totales de certificados (liquidado) al resumen
        $totalsCertificados = $certificateModel->getTotalsGlobal();
        $resumenPresupuesto['total_liquidado'] = $totalsCertificados['total_liquidado'];
        $resumenPresupuesto['total_certificado'] = $totalsCertificados['total_monto'];
        
        // Certificados por estado (global)
        $pendientes = $certificateModel->countByStatus('PENDIENTE');
        $completados = $certificateModel->countByStatus('APROBADO');
        
        // Estadísticas del usuario actual si es operador
        $usuarioCertificates = 0;
        $usuarioCompletados = 0;
        $usuarioTotalCertificado = 0;
        $usuarioTotalLiquidado = 0;
        
        if ($usuario_tipo === 'operador' && $usuario_id) {
            // Obtener certificados del usuario por su nombre
            $usuarioCertificates = $certificateModel->countByOperador($usuario_nombre);
            $usuarioCompletados = $certificateModel->countByOperadorAndStatus($usuario_nombre, 'APROBADO');
            
            // Obtener totales de presupuesto del usuario
            $usuarioTotales = $certificateModel->getTotalsByOperador($usuario_nombre);
            $usuarioTotalCertificado = $usuarioTotales['total_monto'] ?? 0;
            $usuarioTotalLiquidado = $usuarioTotales['total_liquidado'] ?? 0;
        }
        
        require_once __DIR__ . '/../views/dashboard.php';
    }
}
?>
