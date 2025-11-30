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
        
        // Estadísticas
        $totalCertificates = $certificateModel->count();
        $totalPresupuestos = $presupuestoModel->count();
        $resumenPresupuesto = $presupuestoModel->getResumen();
        
        // Certificados por estado
        $pendientes = $certificateModel->countByStatus('PENDIENTE');
        $completados = $certificateModel->countByStatus('APROBADO');
        
        require_once __DIR__ . '/../views/dashboard.php';
    }
}
?>
