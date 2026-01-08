<?php
class CertificateItem {
    private $db;
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener todos los programas únicos (tabla plana)
     */
    public function getProgramas() {
        $sql = "SELECT DISTINCT cod_programa, desc_programa FROM estructura_presupuestaria WHERE cod_programa IS NOT NULL AND TRIM(cod_programa) <> '' ORDER BY cod_programa";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'codigo' => $row['cod_programa'],
                'descripcion' => $row['desc_programa']
            ];
        }
        return $result;
    }

    /**
     * Obtener subprogramas por programa (tabla plana)
     */
    public function getSubprogramasByPrograma($cod_programa) {
        $sql = "SELECT DISTINCT cod_subprograma, desc_subprograma FROM estructura_presupuestaria WHERE cod_programa = ? AND cod_subprograma IS NOT NULL AND TRIM(cod_subprograma) <> '' ORDER BY cod_subprograma";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cod_programa]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'codigo' => $row['cod_subprograma'],
                'descripcion' => $row['desc_subprograma']
            ];
        }
        return $result;
    }

    /**
     * Obtener fuentes por programa, subprograma, proyecto y actividad (tabla plana)
     */
    public function getFuentesByActividad($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad) {
        $sql = "SELECT DISTINCT cod_fuente, desc_fuente FROM estructura_presupuestaria WHERE cod_programa = ? AND cod_subprograma = ? AND cod_proyecto = ? AND cod_actividad = ? ORDER BY cod_fuente";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'codigo' => $row['cod_fuente'],
                'descripcion' => $row['desc_fuente']
            ];
        }
        return $result;
    }

    /**
     * Obtener ubicaciones por programa, subprograma, proyecto, actividad y fuente (tabla plana)
     */
    public function getUbicacionesByFuente($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente) {
        $sql = "SELECT DISTINCT cod_ubicacion, desc_ubicacion FROM estructura_presupuestaria WHERE cod_programa = ? AND cod_subprograma = ? AND cod_proyecto = ? AND cod_actividad = ? AND cod_fuente = ? ORDER BY cod_ubicacion";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'codigo' => $row['cod_ubicacion'],
                'descripcion' => $row['desc_ubicacion']
            ];
        }
        return $result;
    }

    /**
     * Obtener items por programa, subprograma, proyecto, actividad, fuente y ubicación (tabla plana)
     */
    public function getItemsByActividad($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente, $cod_ubicacion) {
        $sql = "SELECT DISTINCT cod_item, desc_item FROM estructura_presupuestaria WHERE cod_programa = ? AND cod_subprograma = ? AND cod_proyecto = ? AND cod_actividad = ? AND cod_fuente = ? AND cod_ubicacion = ? ORDER BY cod_item";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente, $cod_ubicacion]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'codigo' => $row['cod_item'],
                'descripcion' => $row['desc_item']
            ];
        }
        return $result;
    }

    /**
     * Obtener proyectos por programa y subprograma (tabla plana)
     */
    public function getProyectosBySubprograma($cod_programa, $cod_subprograma) {
        $sql = "SELECT DISTINCT cod_proyecto, desc_proyecto FROM estructura_presupuestaria WHERE cod_programa = ? AND cod_subprograma = ? ORDER BY cod_proyecto";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cod_programa, $cod_subprograma]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'codigo' => $row['cod_proyecto'],
                'descripcion' => $row['desc_proyecto']
            ];
        }
        return $result;
    }

    /**
     * Obtener actividades por programa, subprograma y proyecto (tabla plana)
     */
    public function getActividadesByProyecto($cod_programa, $cod_subprograma, $cod_proyecto) {
        $sql = "SELECT DISTINCT cod_actividad, desc_actividad FROM estructura_presupuestaria WHERE cod_programa = ? AND cod_subprograma = ? AND cod_proyecto = ? ORDER BY cod_actividad";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cod_programa, $cod_subprograma, $cod_proyecto]);
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[] = [
                'codigo' => $row['cod_actividad'],
                'descripcion' => $row['desc_actividad']
            ];
        }
        return $result;
    }

    public function getUbicaciones() {
        $result = $this->db->query(
            "SELECT DISTINCT cod_ubicacion AS codigo, desc_ubicacion AS descripcion FROM estructura_presupuestaria WHERE cod_ubicacion IS NOT NULL AND TRIM(cod_ubicacion) <> '' ORDER BY cod_ubicacion ASC"
        );
        $rows = $result->fetchAll();
        $id = 1;
        foreach ($rows as &$row) {
            $row['id'] = $id++;
        }
        return $rows;
    }

    /**
     * Obtener todas las fuentes de financiamiento
     */
    public function getFuentes() {
        $result = $this->db->query(
            "SELECT DISTINCT cod_fuente AS codigo, desc_fuente AS descripcion FROM estructura_presupuestaria WHERE cod_fuente IS NOT NULL AND TRIM(cod_fuente) <> '' ORDER BY cod_fuente ASC"
        );
        $rows = $result->fetchAll();
        $id = 1;
        foreach ($rows as &$row) {
            $row['id'] = $id++;
        }
        return $rows;
    }

    /**
     * Obtener todos los organismos
     */
    public function getOrganismos() {
        $result = $this->db->query(
            "SELECT DISTINCT cod_organismo AS codigo, desc_organismo AS descripcion FROM estructura_presupuestaria WHERE cod_organismo IS NOT NULL AND TRIM(cod_organismo) <> '' ORDER BY cod_organismo ASC"
        );
        $rows = $result->fetchAll();
        $id = 1;
        foreach ($rows as &$row) {
            $row['id'] = $id++;
        }
        return $rows;
    }

    /**
     * Obtener todas las naturalezas
     */
    public function getNaturalezas() {
        $result = $this->db->query(
            "SELECT DISTINCT cod_nprest AS codigo, desc_nprest AS descripcion FROM estructura_presupuestaria WHERE cod_nprest IS NOT NULL AND TRIM(cod_nprest) <> '' ORDER BY cod_nprest ASC"
        );
        $rows = $result->fetchAll();
        $id = 1;
        foreach ($rows as &$row) {
            $row['id'] = $id++;
        }
        return $rows;
    }

    /**
     * Obtener el monto codificado (col3) de presupuesto_items
     * basado en los códigos del item y el año
     */
    public function getMontoCoificado($cod_programa, $cod_subprograma, $cod_proyecto, $cod_actividad, $cod_fuente, $cod_ubicacion, $cod_item, $year = null) {
        // Si no se proporciona year, usar el actual
        if ($year === null) {
            $year = date('Y');
        }
        
        // Buscar en presupuesto_items basado en los códigos Y EL AÑO
        $sql = "SELECT col3 as monto_codificado FROM presupuesto_items 
                WHERE codigog1 = ? AND codigog2 = ? AND codigog3 = ? AND codigog4 = ? AND codigog5 = ? AND year = ?
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cod_programa, $cod_actividad, $cod_fuente, $cod_ubicacion, $cod_item, $year]);
        $row = $stmt->fetch();
        
        return $row ? (float)$row['monto_codificado'] : 0;
    }
}
