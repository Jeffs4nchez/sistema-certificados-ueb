<?php
/**
 * PermisosHelper - Gestiona permisos y control de acceso por rol
 */

class PermisosHelper {
    
    /**
     * Verificar si el usuario actual es admin
     */
    public static function esAdmin() {
        return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
    }

    /**
     * Verificar si el usuario actual es operador
     */
    public static function esOperador() {
        return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'operador';
    }

    /**
     * Verificar si el usuario actual es consultor (solo ve presupuestos)
     */
    public static function esConsultor() {
        return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'consultor';
    }

    /**
     * Obtener el tipo de usuario actual
     */
    public static function getTipoUsuarioActual() {
        return $_SESSION['usuario_tipo'] ?? null;
    }

    /**
     * Obtener el ID del usuario actual
     */
    public static function getUsuarioIdActual() {
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Verificar permisos para una acción
     */
    public static function puedeAcceder($accion) {
        $tipo = self::getTipoUsuarioActual();

        // Admin tiene acceso a TODO
        if ($tipo === 'admin') {
            return true;
        }

        // Operador - acciones permitidas
        if ($tipo === 'operador') {
            $operador_acciones = [
                'certificate-create',      // Crear certificado
                'certificate-list',        // Ver sus certificados
                'certificate-view',        // Ver detalle certificado
                'certificate-print',       // Imprimir certificado
                'presupuesto-list',        // Ver presupuestos
                'presupuesto-create',      // Crear liquidación
                'dashboard',               // Ver dashboard
                'perfil',                  // Ver perfil propio
            ];
            return in_array($accion, $operador_acciones);
        }

        // Consultor - acciones permitidas (solo presupuestos)
        if ($tipo === 'consultor') {
            $consultor_acciones = [
                'presupuesto-list',        // Ver presupuestos
                'presupuesto-view',        // Ver detalle presupuesto
                'presupuesto-export',      // Exportar presupuestos
                'dashboard',               // Ver dashboard
                'perfil',                  // Ver perfil propio
            ];
            return in_array($accion, $consultor_acciones);
        }

        return false;
    }

    /**
     * Verificar si puede editar un certificado (solo admin o propietario admin)
     */
    public static function puedeEditarCertificado($usuario_id_certificado) {
        $usuario_actual = self::getUsuarioIdActual();

        // Admin puede editar todos
        if (self::esAdmin()) {
            return true;
        }

        // Operador NO puede editar
        return false;
    }

    /**
     * Verificar si puede eliminar un certificado (solo admin)
     */
    public static function puedeEliminarCertificado() {
        return self::esAdmin();
    }

    /**
     * Verificar si puede ver un certificado
     */
    public static function puedeVerCertificado($usuario_id_certificado) {
        // Admin ve todos
        if (self::esAdmin()) {
            return true;
        }

        // Operador solo ve los suyos
        if (self::esOperador()) {
            return $usuario_id_certificado == self::getUsuarioIdActual();
        }

        return false;
    }

    /**
     * Verificar si puede imprimir un certificado
     */
    public static function puedeImprimirCertificado($usuario_id_certificado) {
        // Admin imprime todos
        if (self::esAdmin()) {
            return true;
        }

        // Operador solo imprime los suyos
        if (self::esOperador()) {
            return $usuario_id_certificado == self::getUsuarioIdActual();
        }

        return false;
    }

    /**
     * Verificar si puede crear liquidación/presupuesto
     */
    public static function puedeCrearLiquidacion() {
        return true; // Tanto admin como operador pueden crear
    }

    /**
     * Verificar si puede eliminar liquidación/presupuesto (solo admin)
     */
    public static function puedeEliminarLiquidacion() {
        return self::esAdmin();
    }

    /**
     * Verificar si puede exportar presupuestos (admin, operador y consultor)
     */
    public static function puedeExportarPresupuesto() {
        return self::esAdmin() || self::esOperador() || self::esConsultor();
    }

    /**
     * Verificar acceso a gestión de usuarios (solo admin)
     */
    public static function puedeGestionarUsuarios() {
        return self::esAdmin();
    }

    /**
     * Mostrar error de acceso denegado
     */
    public static function denegarAcceso($mensaje = 'Acceso denegado. No tienes permisos para realizar esta acción.') {
        $_SESSION['error'] = $mensaje;
        header('Location: index.php?action=dashboard');
        exit;
    }
}
?>
