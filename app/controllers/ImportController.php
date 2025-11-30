<?php
/**
 * Controlador de Importación de Parámetros
 * Permite subir CSV con datos de parámetros
 */

class ImportController {
    private $parameterModel;
    
    public function __construct() {
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/Parameter.php';
        $this->parameterModel = new Parameter();
    }

    /**
     * Mostrar formulario de importación
     */
    public function importAction() {
        require_once __DIR__ . '/../views/import/form.php';
    }

    /**
     * Procesar archivo CSV
     */
    public function uploadAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: index.php?action=import-form');
            exit;
        }

        try {
            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se subió archivo o hubo un error en la carga.');
            }

            $file = $_FILES['csv_file']['tmp_name'];
            $tipo = $_POST['tipo'] ?? null;

            if (!$tipo) {
                throw new Exception('Debe seleccionar un tipo de parámetro.');
            }

            $tipos_validos = $this->parameterModel->getParameterTypes();
            if (!in_array($tipo, $tipos_validos)) {
                throw new Exception('Tipo de parámetro inválido.');
            }

            // Leer y procesar CSV
            $handle = fopen($file, 'r');
            if (!$handle) {
                throw new Exception('No se pudo abrir el archivo CSV.');
            }

            $importados = 0;
            $errores = [];
            $fila = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $fila++;
                
                // Saltar encabezado (primera fila)
                if ($fila === 1) {
                    continue;
                }

                // Validar que hay datos
                if (empty($data[0]) && empty($data[1])) {
                    continue;
                }

                try {
                    $codigo = trim($data[0] ?? '');
                    $descripcion = trim($data[1] ?? '');

                    if (empty($codigo) || empty($descripcion)) {
                        $errores[] = "Fila $fila: Código y descripción son requeridos.";
                        continue;
                    }

                    $parametro = [
                        'tipo' => $tipo,
                        'codigo' => $codigo,
                        'descripcion' => $descripcion
                    ];

                    // Para tipos jerárquicos, procesar padre
                    if ($tipo === 'SP' && isset($data[2])) {
                        // SP: programa_id en columna 3
                        $programa_id = intval(trim($data[2]));
                        if ($programa_id === 0) {
                            $errores[] = "Fila $fila: Programa_id inválido.";
                            continue;
                        }
                        $parametro['programa_id'] = $programa_id;
                    } elseif ($tipo === 'PY' && isset($data[2])) {
                        // PY: subprograma_id en columna 3
                        $subprograma_id = intval(trim($data[2]));
                        if ($subprograma_id === 0) {
                            $errores[] = "Fila $fila: Subprograma_id inválido.";
                            continue;
                        }
                        $parametro['subprograma_id'] = $subprograma_id;
                    } elseif ($tipo === 'ACT' && isset($data[2])) {
                        // ACT: proyecto_id en columna 3
                        $proyecto_id = intval(trim($data[2]));
                        if ($proyecto_id === 0) {
                            $errores[] = "Fila $fila: Proyecto_id inválido.";
                            continue;
                        }
                        $parametro['proyecto_id'] = $proyecto_id;
                    } elseif ($tipo === 'ITEM' && isset($data[2])) {
                        // ITEM: actividad_id en columna 3
                        $actividad_id = intval(trim($data[2]));
                        if ($actividad_id === 0) {
                            $errores[] = "Fila $fila: Actividad_id inválido.";
                            continue;
                        }
                        $parametro['actividad_id'] = $actividad_id;
                    }

                    $this->parameterModel->createParameter($parametro);
                    $importados++;

                } catch (Exception $e) {
                    $errores[] = "Fila $fila: " . $e->getMessage();
                }
            }

            fclose($handle);

            $_SESSION['success'] = "Se importaron $importados parámetros correctamente.";
            if (!empty($errores)) {
                $_SESSION['warnings'] = $errores;
            }

            header('Location: index.php?action=parameter-list&type=' . urlencode($tipo));
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header('Location: index.php?action=import-form');
            exit;
        }
    }
}
?>
