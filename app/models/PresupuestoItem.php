<?php
/**
 * Modelo de Presupuesto Items
 * Maneja la tabla presupuesto_items
 */

class PresupuestoItem {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los items de presupuesto
     */
    public function getAll() {
        $result = $this->db->query("SELECT * FROM presupuesto_items ORDER BY id ASC");
        return $result ? $result->fetchAll() : array();
    }

    /**
     * Obtener item por ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM presupuesto_items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crear nuevo item
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO presupuesto_items (
                descripciong1, descripciong2, descripciong3, descripciong4, descripciong5,
                col1, col2, col3, col4, col5, col6, col7, col8, col9, col10, col20,
                codigog1, codigog2, codigog3, codigog4, codigog5, codigo_completo, saldo_disponible
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['descripciong1'],
            $data['descripciong2'],
            $data['descripciong3'],
            $data['descripciong4'],
            $data['descripciong5'],
            $data['col1'],
            $data['col2'],
            $data['col3'],
            $data['col4'],
            $data['col5'],
            $data['col6'],
            $data['col7'],
            $data['col8'],
            $data['col9'],
            $data['col10'],
            $data['col20'],
            $data['codigog1'],
            $data['codigog2'],
            $data['codigog3'],
            $data['codigog4'],
            $data['codigog5'],
            $data['codigo_completo'],
            $data['saldo_disponible']
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Actualizar item
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE presupuesto_items SET
                descripciong1 = ?, descripciong2 = ?, descripciong3 = ?, descripciong4 = ?, descripciong5 = ?,
                col1 = ?, col2 = ?, col3 = ?, col4 = ?, col5 = ?, col6 = ?, col7 = ?, col8 = ?, col9 = ?, col10 = ?, col20 = ?,
                codigog1 = ?, codigog2 = ?, codigog3 = ?, codigog4 = ?, codigog5 = ?, codigo_completo = ?, saldo_disponible = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['descripciong1'],
            $data['descripciong2'],
            $data['descripciong3'],
            $data['descripciong4'],
            $data['descripciong5'],
            $data['col1'],
            $data['col2'],
            $data['col3'],
            $data['col4'],
            $data['col5'],
            $data['col6'],
            $data['col7'],
            $data['col8'],
            $data['col9'],
            $data['col10'],
            $data['col20'],
            $data['codigog1'],
            $data['codigog2'],
            $data['codigog3'],
            $data['codigog4'],
            $data['codigog5'],
            $data['codigo_completo'],
            $data['saldo_disponible'],
            $id
        ]);
    }

    /**
     * Actualizar SOLO col2 y col3 (para importación CSV)
     * IMPORTANTE: No actualiza col4 (solo se modifica por liquidaciones)
     * Recalcula saldo_disponible = col3 - col4 (mantiene col4 anterior)
     */
    public function actualizarCol3($id, $col2_nuevo, $col3_nuevo) {
        // Obtener col4 actual para mantenerlo
        $stmt = $this->db->prepare("SELECT col4 FROM presupuesto_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if (!$item) {
            return false;
        }
        
        $col4_actual = (float)$item['col4'];
        $saldo_nuevo = $col3_nuevo - $col4_actual;
        
        // Actualizar SOLO col2, col3 y saldo_disponible
        $stmt = $this->db->prepare("
            UPDATE presupuesto_items SET
                col2 = ?,
                col3 = ?,
                saldo_disponible = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$col2_nuevo, $col3_nuevo, $saldo_nuevo, $id]);
    }

    /**
     * Eliminar item
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM presupuesto_items WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Eliminar todos los items (vaciar tabla)
     */
    public function deleteAll() {
        $result = $this->db->query("TRUNCATE TABLE presupuesto_items");
        return $result ? true : false;
    }

    /**
     * Obtener total de items
     */
    public function count() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM presupuesto_items");
        $row = $result->fetch();
        return $row['total'];
    }

    /**
     * Búsqueda por código de programa
     */
    public function findByPrograma($codigog1) {
        $stmt = $this->db->prepare("SELECT * FROM presupuesto_items WHERE codigog1 = ? ORDER BY fecha_creacion DESC");
        $stmt->execute([$codigog1]);
        return $stmt->fetchAll();
    }

    /**
     * Búsqueda por código de fuente
     */
    public function findByFuente($codigog3) {
        $stmt = $this->db->prepare("SELECT * FROM presupuesto_items WHERE codigog3 = ? ORDER BY fecha_creacion DESC");
        $stmt->execute([$codigog3]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener item por código completo
     */
    public function obtenerPorCodigoCompleto($codigo_completo) {
        $stmt = $this->db->prepare("
            SELECT * FROM presupuesto_items 
            WHERE codigo_completo = ? 
            LIMIT 1
        ");
        $stmt->execute([$codigo_completo]);
        return $stmt->fetch();
    }

    /**
     * Generar hash MD5 SOLO de COL3 (campo crítico)
     * LÓGICA: Solo actualiza si col3 cambió
     * Los demás campos se ignoran
     */
    public function generarHashItem($data) {
        // SOLO COMPARAR COL3 (Codificado)
        return md5($data['col3']);
    }

    /**
     * Obtener resumen de montos
     */
    public function getResumen() {
        $query = "SELECT 
            SUM(col1) as total_asignado,
            SUM(col3) as total_codificado,
            SUM(col4) as total_certificado,
            SUM(col5) as total_comprometido,
            SUM(col6) as total_devengado,
            SUM(col7) as total_liquidado,
            SUM(saldo_disponible) as total_saldo_disponible,
            COUNT(*) as total_items,
            AVG(col20) as promedio_ejecucion
        FROM presupuesto_items";
        
        $result = $this->db->query($query);
        return $result->fetch();
    }

    /**
     * Obtener resumen de montos por operador
     */
    public function getResumenByOperador($usuario_id) {
        $query = "SELECT 
            SUM(col1) as total_asignado,
            SUM(col3) as total_codificado,
            SUM(col4) as total_certificado,
            SUM(col5) as total_comprometido,
            SUM(col6) as total_devengado,
            SUM(col7) as total_liquidado,
            SUM(saldo_disponible) as total_saldo_disponible,
            COUNT(*) as total_items,
            AVG(col20) as promedio_ejecucion
        FROM presupuesto_items
        WHERE operado_por = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch();
    }

    /**
     * Calcular saldo disponible para un item
     */
    public function calcularSaldo($id) {
        $item = $this->getById($id);
        if (!$item) {
            return 0;
        }
        
        // Saldo = Codificado - Comprometido
        $saldo = $item['col3'] - $item['col5'];
        
        // Actualizar
        $stmt = $this->db->prepare("UPDATE presupuesto_items SET saldo_disponible = ? WHERE id = ?");
        $stmt->execute([$saldo, $id]);
        
        return $saldo;
    }

    /**
     * Importar presupuesto desde CSV
     * MEJORA: Maneja inteligentemente los duplicados
     * - Si el código existe pero los datos cambiaron: ACTUALIZA
     * - Si el código existe y los datos son iguales: IGNORA (no duplica)
     * - Si es nuevo: INSERTA
     * Soporta separadores: coma (,) y punto y coma (;)
     * Extrae solo las siglas (códigos) de cada columna y crea codigo_completo
     * Formato esperado: PROGRAMA,ACTIVIDAD,FUENTE,GEOGRAFICO,ITEM,COL1,COL2,COL3,COL4,COL5,COL6,COL7,COL8,COL9,COL10,COL20,CODIGOG1,CODIGOG2,CODIGOG3,CODIGOG4,CODIGOG5
     */
    public function importCSV($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception('El archivo CSV no existe.');
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new Exception('No se pudo abrir el archivo CSV.');
        }
        
        // Leer primera línea para detectar el separador
        $firstLine = fgets($handle);
        if (!$firstLine) {
            fclose($handle);
            throw new Exception('El archivo CSV está vacío.');
        }
        
        // Detectar separador
        $separator = ',';
        if (strpos($firstLine, ';') !== false) {
            $separator = ';';
        }
        
        // Volver al inicio
        rewind($handle);
        
        // Leer encabezados
        $header = fgetcsv($handle, 0, $separator, '"');
        if (!$header) {
            fclose($handle);
            throw new Exception('El archivo CSV está vacío o tiene un formato inválido.');
        }
        
        $imported = 0;
        $updated = 0;
        $duplicated = 0;
        $errors = 0;
        $errorDetails = [];
        $lineNumber = 1;
        
        while (($row = fgetcsv($handle, 0, $separator, '"')) !== false) {
            $lineNumber++;
            
            if (empty($row[0])) {
                continue;
            }
            
            if (count($row) < 21) {
                $errors++;
                $errorDetails[] = "Línea $lineNumber: Columnas insuficientes (" . count($row) . " de 21 requeridas)";
                continue;
            }
            
            try {
                $codigog1_raw = trim($row[16]);
                $codigog2_raw = trim($row[17]);
                $codigog3_raw = trim($row[18]);
                $codigog4_raw = trim($row[19]);
                $codigog5_raw = trim($row[20]);
                
                $codigog1_digits = preg_replace('/[^0-9]/', '', $codigog1_raw);
                $codigog2_digits = preg_replace('/[^0-9]/', '', $codigog2_raw);
                $codigog3_digits = preg_replace('/[^0-9]/', '', $codigog3_raw);
                $codigog4_digits = preg_replace('/[^0-9]/', '', $codigog4_raw);
                $codigog5_digits = preg_replace('/[^0-9]/', '', $codigog5_raw);
                
                $codigog1_siglas = substr($codigog1_digits, 0, 2);
                $codigog2_siglas = substr($codigog2_digits, -3);
                $codigog3_siglas = substr($codigog3_digits, 0, 3);
                $codigog4_siglas = substr($codigog4_digits, 0, 4);
                $codigog5_siglas = substr($codigog5_digits, 0, 6);
                
                $codigo_completo = trim($codigog2_raw . ' ' . $codigog3_siglas . ' ' . $codigog4_siglas . ' ' . $codigog5_siglas);
                
                $col3 = (float)str_replace(',', '.', trim($row[7]));
                $col4 = (float)str_replace(',', '.', trim($row[8]));
                
                $data = [
                    'descripciong1' => trim($row[0]),
                    'descripciong2' => trim($row[1]),
                    'descripciong3' => trim($row[2]),
                    'descripciong4' => trim($row[3]),
                    'descripciong5' => trim($row[4]),
                    'col1'  => (float)str_replace(',', '.', trim($row[5])),
                    'col2'  => (float)str_replace(',', '.', trim($row[6])),
                    'col3'  => $col3,
                    'col4'  => $col4,
                    'col5'  => (float)str_replace(',', '.', trim($row[9])),
                    'col6'  => (float)str_replace(',', '.', trim($row[10])),
                    'col7'  => (float)str_replace(',', '.', trim($row[11])),
                    'col8'  => (float)str_replace(',', '.', trim($row[12])),
                    'col9'  => (float)str_replace(',', '.', trim($row[13])),
                    'col10' => (float)str_replace(',', '.', trim($row[14])),
                    'col20' => (float)str_replace(',', '.', trim($row[15])),
                    'codigog1' => $codigog1_siglas,
                    'codigog2' => $codigog2_siglas,
                    'codigog3' => $codigog3_siglas,
                    'codigog4' => $codigog4_siglas,
                    'codigog5' => $codigog5_siglas,
                    'codigo_completo' => $codigo_completo,
                    'saldo_disponible' => $col3 - $col4
                ];
                
                // LÓGICA DE DETECCIÓN DE DUPLICADOS - SOLO COL2 Y COL3
                $itemExistente = $this->obtenerPorCodigoCompleto($codigo_completo);
                
                if ($itemExistente) {
                    // El código ya existe, verificar SI COL2 O COL3 CAMBIARON
                    $col2Actual = (float)$itemExistente['col2'];
                    $col3Actual = (float)$itemExistente['col3'];
                    $col2Nuevo = (float)$data['col2'];
                    $col3Nuevo = (float)$data['col3'];
                    
                    if ($col2Nuevo !== $col2Actual || $col3Nuevo !== $col3Actual) {
                        // COL2 O COL3 CAMBIARON → ACTUALIZAR y RECALCULAR saldo
                        $this->actualizarCol3($itemExistente['id'], $col2Nuevo, $col3Nuevo);
                        $updated++;
                    } else {
                        // COL2 Y COL3 IGUALES → IGNORAR (sin cambios)
                        $duplicated++;
                    }
                } else {
                    // Es nuevo, insertar
                    $this->create($data);
                    $imported++;
                }
                
            } catch (Exception $e) {
                $errors++;
                $errorDetails[] = "Línea $lineNumber: " . $e->getMessage();
                continue;
            }
        }
        
        fclose($handle);
        
        return [
            'total' => $imported,
            'updated' => $updated,
            'duplicated' => $duplicated,
            'errors' => $errors,
            'errorDetails' => $errorDetails
        ];
    }
}
