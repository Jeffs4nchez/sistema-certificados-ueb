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
        
        // Filtrar por año si está definido en la sesión
        $year = $_SESSION['year'] ?? null;
        if ($year) {
            $totalCertificates = $certificateModel->countByYear($year);
            $totalPresupuestos = $presupuestoModel->countByYear($year);
            $resumenPresupuesto = $presupuestoModel->getResumenByYear($year);
            $totalsCertificados = $certificateModel->getTotalsGlobalByYear($year);
            $resumenPresupuesto['total_liquidado'] = $totalsCertificados['total_liquidado'];
            $resumenPresupuesto['total_certificado'] = $totalsCertificados['total_monto'];
            $pendientes = $certificateModel->countByStatusAndYear('PENDIENTE', $year);
            $completados = $certificateModel->countByStatusAndYear('APROBADO', $year);
        } else {
            $totalCertificates = $certificateModel->count();
            $totalPresupuestos = $presupuestoModel->count();
            $resumenPresupuesto = $presupuestoModel->getResumen();
            $totalsCertificados = $certificateModel->getTotalsGlobal();
            $resumenPresupuesto['total_liquidado'] = $totalsCertificados['total_liquidado'];
            $resumenPresupuesto['total_certificado'] = $totalsCertificados['total_monto'];
            $pendientes = $certificateModel->countByStatus('PENDIENTE');
            $completados = $certificateModel->countByStatus('APROBADO');
        }
        
        // Estadísticas del usuario actual si es operador
        $usuarioCertificates = 0;
        $usuarioCompletados = 0;
        $usuarioTotalCertificado = 0;
        $usuarioTotalLiquidado = 0;
        $es_consultor = false;
        
        if ($usuario_tipo === 'operador' && $usuario_id) {
            // Obtener certificados del usuario por su nombre Y EL AÑO
            $usuarioCertificates = $certificateModel->countByOperador($usuario_nombre, $year);
            $usuarioCompletados = $certificateModel->countByOperadorAndStatus($usuario_nombre, 'APROBADO', $year);
            
            // Obtener totales de presupuesto del usuario POR AÑO
            $usuarioTotales = $certificateModel->getTotalsByOperador($usuario_nombre, $year);
            $usuarioTotalCertificado = $usuarioTotales['total_monto'] ?? 0;
            $usuarioTotalLiquidado = $usuarioTotales['total_liquidado'] ?? 0;
        }
        
        // Si es consultor, solo ver presupuestos
        if ($usuario_tipo === 'consultor') {
            $es_consultor = true;
        }
        
        // Pasar variable a la vista para filtrar contenido
        $mostrar_certificados = ($usuario_tipo === 'admin' || $usuario_tipo === 'operador');
        $mostrar_presupuesto = true;
        
        require_once __DIR__ . '/../views/dashboard.php';
    }
}
?>
