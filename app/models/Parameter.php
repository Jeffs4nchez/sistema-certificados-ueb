<?php
/**
 * Modelo de Parámetros del Sistema
 * Maneja todas las tablas jerárquicas: Programas, Subprogramas, Proyectos, Actividades, Items
 * Y las tablas de dimensiones: Ubicaciones, Fuentes, Organismos, Naturaleza
 */

class Parameter {
    /**
     * Obtener parámetros únicos por tipo (sin repetidos)
     */
    public function getDistinctParametersByType($type) {
        $map = [
            'PG' => ['codigo' => 'cod_programa', 'descripcion' => 'desc_programa'],
            'SP' => ['codigo' => 'cod_subprograma', 'descripcion' => 'desc_subprograma'],
            'PY' => ['codigo' => 'cod_proyecto', 'descripcion' => 'desc_proyecto'],
            'ACT' => ['codigo' => 'cod_actividad', 'descripcion' => 'desc_actividad'],
            'ITEM' => ['codigo' => 'cod_item', 'descripcion' => 'desc_item'],
            'UBG' => ['codigo' => 'cod_ubicacion', 'descripcion' => 'desc_ubicacion'],
            'FTE' => ['codigo' => 'cod_fuente', 'descripcion' => 'desc_fuente'],
            'ORG' => ['codigo' => 'cod_organismo', 'descripcion' => 'desc_organismo'],
            'N.PREST' => ['codigo' => 'cod_nprest', 'descripcion' => 'desc_nprest']
        ];
        if (!isset($map[$type])) {
            return array();
        }
        $codigo = $map[$type]['codigo'];
        $descripcion = $map[$type]['descripcion'];
        $sql = "SELECT DISTINCT $codigo AS codigo, $descripcion AS descripcion FROM estructura_presupuestaria WHERE $codigo IS NOT NULL AND TRIM($codigo) <> '' AND $descripcion IS NOT NULL AND TRIM($descripcion) <> '' ORDER BY $codigo ASC";
        $stmt = $this->db->query($sql);
        $result = [];
        $id = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!empty($row['codigo']) && !empty($row['descripcion'])) {
                $row['id'] = $id++;
                $row['tipo'] = $type;
                $result[] = $row;
            }
        }
        return $result;
    }
        /**
         * Contar el total de parámetros de un tipo específico en la tabla plana
         */
        public function countParametersByType($type) {
            // Mapeo de tipo a columna
            $map = [
                'PG' => 'cod_programa',
                'SP' => 'cod_subprograma',
                'PY' => 'cod_proyecto',
                'ACT' => 'cod_actividad',
                'ITEM' => 'cod_item',
                'UBG' => 'cod_ubicacion',
                'FTE' => 'cod_fuente',
                'ORG' => 'cod_organismo',
                'N.PREST' => 'cod_nprest'
            ];
            if (!isset($map[$type])) {
                return 0;
            }
            $col = $map[$type];
            $sql = "SELECT COUNT(*) as total FROM estructura_presupuestaria WHERE $col IS NOT NULL AND TRIM($col) <> ''";
            $stmt = $this->db->query($sql);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        }
    private $db;
    private $tipos_validos = ['PG', 'SP', 'PY', 'ACT', 'ITEM', 'UBG', 'FTE', 'ORG', 'N.PREST'];
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

        /**
         * Crear un nuevo parámetro en la tabla plana
         */
        public function createParameter($data) {
            // Construir los campos y valores dinámicamente
            $fields = array_keys($data);
            $placeholders = array_map(function($f) { return ':' . $f; }, $fields);
            $sql = 'INSERT INTO estructura_presupuestaria (' . implode(',', $fields) . ') VALUES (' . implode(',', $placeholders) . ')';
            $stmt = $this->db->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        }

        /**
         * Obtener parámetros por tipo desde la tabla plana
         */
        public function getParametersByType($type) {
            $map = [
                'PG' => 'cod_programa',
                'SP' => 'cod_subprograma',
                'PY' => 'cod_proyecto',
                'ACT' => 'cod_actividad',
                'UBG' => 'cod_ubicacion',
                'FTE' => 'cod_fuente',
                'ITEM' => 'cod_item',
                'ORG' => 'cod_organismo',
                'N.PREST' => 'cod_nprest'
            ];
            if (!isset($map[$type])) {
                return array();
            }
            $col = $map[$type];
            $sql = "SELECT * FROM estructura_presupuestaria WHERE $col IS NOT NULL AND TRIM($col) <> '' ORDER BY $col ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Contar el total de parámetros en la tabla plana
         */
        public function countParameters() {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM estructura_presupuestaria");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        }

        /**
         * Contar el total de tipos de parámetros válidos
         */
        public function countParameterTypes() {
            return count($this->tipos_validos);
        }

    /**
     * Obtener todos los tipos de parámetros válidos
     */
    public function getParameterTypes() {
        return $this->tipos_validos;
    }

    /**
     * Obtener tabla correspondiente a tipo
     */
    private function getTableByType($type) {
        $mapa = [
            'PG'       => 'programas',
            'SP'       => 'subprogramas',
            'PY'       => 'proyectos',
            'ACT'      => 'actividades',
            'ITEM'     => 'items',
            'UBG'      => 'ubicaciones',
            'FTE'      => 'fuentes_financiamiento',
            'ORG'      => 'organismos',
            'N.PREST'  => 'naturaleza_prestacion'
        ];
        return $mapa[$type] ?? null;
    }

    /**
     * Obtener todos los parámetros (por tipo)
     */
    public function getAllParameters($type = null) {
        if ($type && !in_array($type, $this->tipos_validos)) {
            return array();
        }

        if ($type) {
            return $this->getParametersByType($type);
        }

        // Solo usar la tabla plana, ordenar por id
        $stmt = $this->db->query("SELECT * FROM estructura_presupuestaria ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Métodos para obtener cada nivel desde la tabla plana
    public function getPrograms() {
        $stmt = $this->db->query("SELECT DISTINCT cod_programa AS codigo, desc_programa AS descripcion FROM estructura_presupuestaria ORDER BY cod_programa");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubprograms($cod_programa) {
        $stmt = $this->db->prepare("SELECT DISTINCT cod_subprograma AS codigo, desc_subprograma AS descripcion FROM estructura_presupuestaria WHERE cod_programa = ? ORDER BY cod_subprograma");
        $stmt->execute([$cod_programa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProyectos($cod_subprograma) {
        $stmt = $this->db->prepare("SELECT DISTINCT cod_proyecto AS codigo, desc_proyecto AS descripcion FROM estructura_presupuestaria WHERE cod_subprograma = ? ORDER BY cod_proyecto");
        $stmt->execute([$cod_subprograma]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActividades($cod_proyecto) {
        $stmt = $this->db->prepare("SELECT DISTINCT cod_actividad AS codigo, desc_actividad AS descripcion FROM estructura_presupuestaria WHERE cod_proyecto = ? ORDER BY cod_actividad");
        $stmt->execute([$cod_proyecto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItems($cod_actividad) {
        $stmt = $this->db->prepare("SELECT DISTINCT cod_item AS codigo, desc_item AS descripcion FROM estructura_presupuestaria WHERE cod_actividad = ? ORDER BY cod_item");
        $stmt->execute([$cod_actividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFuentes($cod_actividad) {
        $stmt = $this->db->prepare("SELECT DISTINCT cod_fuente AS codigo, desc_fuente AS descripcion FROM estructura_presupuestaria WHERE cod_actividad = ? ORDER BY cod_fuente");
        $stmt->execute([$cod_actividad]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUbicaciones($cod_fuente) {
        $stmt = $this->db->prepare("SELECT DISTINCT cod_ubicacion AS codigo, desc_ubicacion AS descripcion FROM estructura_presupuestaria WHERE cod_fuente = ? ORDER BY cod_ubicacion");
        $stmt->execute([$cod_fuente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrganismos() {
        $stmt = $this->db->query("SELECT DISTINCT cod_organismo AS codigo, desc_organismo AS descripcion FROM estructura_presupuestaria ORDER BY cod_organismo");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNPrest() {
        $stmt = $this->db->query("SELECT DISTINCT cod_nprest AS codigo, desc_nprest AS descripcion FROM estructura_presupuestaria ORDER BY cod_nprest");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
