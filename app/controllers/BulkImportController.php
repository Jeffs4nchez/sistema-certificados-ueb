<?php
/**
 * Importador Especial de CSV Jerárquico Completo
 * Para archivos con estructura: Programa -> Subprograma -> Proyecto -> Actividad -> Item -> Ubicación -> Fuente -> Organismo -> Naturaleza
 */

class BulkImportController {
    private $parameterModel;
    
    public function __construct() {
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/Parameter.php';
        $this->parameterModel = new Parameter();
    }

    /**
     * Mostrar formulario de importación masiva
     */
    public function bulkImportAction() {
        require_once __DIR__ . '/../views/import/bulk_form.php';
    }

    /**
     * Procesar archivo CSV con estructura jerárquica completa
     */
    public function bulkUploadAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: index.php?action=parameter-list');
            exit;
        }

        try {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se subió archivo o hubo un error en la carga.');
            }

            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, 'r');
            if (!$handle) {
                throw new Exception('No se pudo abrir el archivo CSV.');
            }

            // Leer encabezados
            $headers = fgetcsv($handle, 10000, ';');
            // Normalización y mapeo flexible
            function normalize_header($header) {
                $header = strtolower(trim($header));
                $header = str_replace([
                    'á','é','í','ó','ú','ñ',
                    'Á','É','Í','Ó','Ú','Ñ'
                ],[
                    'a','e','i','o','u','n',
                    'a','e','i','o','u','n'
                ],$header);
                $header = str_replace([' ', '_', '-'], '', $header); // quita espacios, guiones y guiones bajos
                $header = preg_replace('/[^a-z0-9\.]/','', $header); // solo letras, números y punto
                return $header;
            }
            $map = [
                'c.programa' => 'cod_programa',
                'd.programa' => 'desc_programa',
                'c.subprograma' => 'cod_subprograma',
                'd.subprograma' => 'desc_subprograma',
                'c.subprog' => 'cod_subprograma',
                'd.subprog' => 'desc_subprograma',
                'c.proyecto' => 'cod_proyecto',
                'd.proyecto' => 'desc_proyecto',
                'c.activ' => 'cod_actividad',
                'd.actividad' => 'desc_actividad',
                'c.item' => 'cod_item',
                'd.item' => 'desc_item',
                'c.ubicaciongeografica' => 'cod_ubicacion',
                'd.ubicaciongeografica' => 'desc_ubicacion',
                'c.ubicacion' => 'cod_ubicacion',
                'd.ubicacion' => 'desc_ubicacion',
                'c.fuentedefinanciamiento' => 'cod_fuente',
                'd.fuentedefinanciamiento' => 'desc_fuente',
                'c.fuenteded' => 'cod_fuente',
                'fuentedec' => 'desc_fuente',
                'organismo' => 'cod_organismo',
                'c.organismo' => 'cod_organismo',
                'd.organismo' => 'desc_organismo',
                'organism' => 'cod_organismo',
                'd.organism' => 'desc_organismo',
                'npest' => 'cod_nprest',
                'n.prest' => 'cod_nprest',
                'descripcion' => 'desc_nprest',
                'd.n.prest' => 'desc_nprest'
            ];

            $rowCount = 0;
            $inserted = 0;
            while (($row = fgetcsv($handle, 10000, ';')) !== FALSE) {
                $rowCount++;
                $data = [];
                foreach ($headers as $i => $header) {
                    $norm = normalize_header($header);
                    if (isset($map[$norm])) {
                        $valor = trim($row[$i]);
                        // Limpieza especial para ubicación
                        if ($map[$norm] === 'cod_ubicacion' || $map[$norm] === 'desc_ubicacion') {
                            $valor = preg_replace('/\s+/', ' ', $valor); // Quita espacios extra
                        }
                        // Recorte de códigos según nivel
                        if ($map[$norm] === 'cod_programa') {
                            $valor = substr($valor, -2); // últimos 2 dígitos
                        }
                        if ($map[$norm] === 'cod_subprograma') {
                            $valor = substr($valor, -2); // últimos 2 dígitos
                        }
                        if ($map[$norm] === 'cod_proyecto') {
                            $valor = substr($valor, -3); // últimos 3 dígitos
                        }
                        if ($map[$norm] === 'cod_actividad') {
                            $valor = substr($valor, -3); // últimos 3 dígitos
                        }
                        $data[$map[$norm]] = $valor;
                    }
                }
                // Depuración: mostrar valores de ubicación
                if (isset($data['cod_ubicacion']) || isset($data['desc_ubicacion'])) {
                    error_log('[IMPORT UBICACION] cod_ubicacion=' . ($data['cod_ubicacion'] ?? 'NULL') . ', desc_ubicacion=' . ($data['desc_ubicacion'] ?? 'NULL'));
                }
                $data['codigo_completo'] = trim(
                    ($data['cod_programa'] ?? '') . ' ' .
                    ($data['cod_subprograma'] ?? '') . ' ' .
                    ($data['cod_proyecto'] ?? '') . ' ' .
                    ($data['cod_actividad'] ?? '') . ' ' .
                    ($data['cod_fuente'] ?? '') . ' ' .
                    ($data['cod_ubicacion'] ?? '') . ' ' .
                    ($data['cod_item'] ?? '')
                );
                if (!empty($data['cod_programa'])) {
                    $this->parameterModel->createParameter($data);
                    $inserted++;
                }
            }
            fclose($handle);
            $_SESSION['success'] = "✓ Se importaron correctamente: $inserted registros";
            header('Location: index.php?action=parameter-list');
            exit;
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header('Location: index.php?action=parameter-list');
            exit;
        }
    }
}
?>
