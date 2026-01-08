<?php
/**
 * Controlador de Parámetros Presupuestarios
 * Maneja: PG, SP, PY, ACT, ITEM, UBG, FTE, ORG, N.PREST
 */

class ParameterController {
    private $parameterModel;
    
    public function __construct() {
        require_once __DIR__ . '/../Database.php';
        require_once __DIR__ . '/../models/Parameter.php';
        $this->parameterModel = new Parameter();
    }
    
    /**
     * Lista de parámetros presupuestarios
     */
    public function listAction() {
        $type = $_GET['type'] ?? null;
        $parametros = [];
        $tipos = $this->parameterModel->getParameterTypes();
        
        // Filtrar por año de sesión
        $year = $_SESSION['year'] ?? date('Y');
        $totalParametros = $this->parameterModel->countParametersByYear($year);
        
        // Nuevo: contar solo los tipos que tienen al menos un parámetro (por año)
        $tiposConParametros = [];
        foreach ($tipos as $t) {
            if ($this->parameterModel->countParametersByTypeAndYear($t, $year) > 0) {
                $tiposConParametros[] = $t;
            }
        }
        $totalTipos = count($tiposConParametros);
        
        $ordenTipos = ['PG', 'SP', 'PY', 'ACT', 'FTE', 'UBG', 'ITEM', 'ORG', 'N.PREST'];
        if ($type && in_array($type, $tipos)) {
            $parametros = $this->parameterModel->getDistinctParametersByTypeAndYear($type, $year);
        } else {
            $parametros = [];
            foreach ($ordenTipos as $t) {
                if (in_array($t, $tipos)) {
                    $parametros = array_merge($parametros, $this->parameterModel->getDistinctParametersByTypeAndYear($t, $year));
                }
            }
            $type = null;
        }
        
        require_once __DIR__ . '/../views/parameters/index.php';
    }

    /**
     * Obtener código jerárquico completo de un parámetro
     */
    private function buildHierarchicalCode($parametro, $tipo) {
        $codigo = $parametro['codigo'];

        // Construir el código jerárquico según el tipo
        switch ($tipo) {
            case 'SP':
                // Obtener programa padre
                $programa = $this->parameterModel->getParameterById($parametro['programa_id'], 'PG');
                if ($programa) {
                    $codigo = $programa['codigo'] . ' ' . $parametro['codigo'];
                }
                break;

            case 'PY':
                // Obtener subprograma padre
                $subprograma = $this->parameterModel->getParameterById($parametro['subprograma_id'], 'SP');
                if ($subprograma) {
                    $programa = $this->parameterModel->getParameterById($subprograma['programa_id'], 'PG');
                    if ($programa) {
                        $codigo = $programa['codigo'] . ' ' . $subprograma['codigo'] . ' ' . $parametro['codigo'];
                    }
                }
                break;

            case 'ACT':
                // Obtener proyecto padre
                $proyecto = $this->parameterModel->getParameterById($parametro['proyecto_id'], 'PY');
                if ($proyecto) {
                    $subprograma = $this->parameterModel->getParameterById($proyecto['subprograma_id'], 'SP');
                    if ($subprograma) {
                        $programa = $this->parameterModel->getParameterById($subprograma['programa_id'], 'PG');
                        if ($programa) {
                            $codigo = $programa['codigo'] . ' ' . $subprograma['codigo'] . ' ' . $proyecto['codigo'] . ' ' . $parametro['codigo'];
                        }
                    }
                }
                break;

            case 'ITEM':
                // Obtener actividad padre
                $actividad = $this->parameterModel->getParameterById($parametro['actividad_id'], 'ACT');
                if ($actividad) {
                    $proyecto = $this->parameterModel->getParameterById($actividad['proyecto_id'], 'PY');
                    if ($proyecto) {
                        $subprograma = $this->parameterModel->getParameterById($proyecto['subprograma_id'], 'SP');
                        if ($subprograma) {
                            $programa = $this->parameterModel->getParameterById($subprograma['programa_id'], 'PG');
                            if ($programa) {
                                $codigo = $programa['codigo'] . ' ' . $subprograma['codigo'] . ' ' . $proyecto['codigo'] . ' ' . $actividad['codigo'] . ' ' . $parametro['codigo'];
                            }
                        }
                    }
                }
                break;
        }

        return $codigo;
    }

    /**
     * Agregar información completa de la jerarquía a un parámetro
     */
    private function addHierarchyInfo($parametro, $tipo) {
        $info = [
            'codigo_jerarquico' => $parametro['codigo'],
            'programa' => null,
            'subprograma' => null,
            'proyecto' => null,
            'actividad' => null
        ];

        // Construir jerarquía según el tipo
        switch ($tipo) {
            case 'SP':
                // Obtener programa padre
                $programa = $this->parameterModel->getParameterById($parametro['programa_id'], 'PG');
                if ($programa) {
                    $info['programa'] = $programa;
                    $info['codigo_jerarquico'] = $programa['codigo'] . ' ' . $parametro['codigo'];
                }
                break;

            case 'PY':
                // Obtener subprograma padre
                $subprograma = $this->parameterModel->getParameterById($parametro['subprograma_id'], 'SP');
                if ($subprograma) {
                    $info['subprograma'] = $subprograma;
                    $programa = $this->parameterModel->getParameterById($subprograma['programa_id'], 'PG');
                    if ($programa) {
                        $info['programa'] = $programa;
                        $info['codigo_jerarquico'] = $programa['codigo'] . ' ' . $subprograma['codigo'] . ' ' . $parametro['codigo'];
                    }
                }
                break;

            case 'ACT':
                // Obtener proyecto padre
                $proyecto = $this->parameterModel->getParameterById($parametro['proyecto_id'], 'PY');
                if ($proyecto) {
                    $info['proyecto'] = $proyecto;
                    $subprograma = $this->parameterModel->getParameterById($proyecto['subprograma_id'], 'SP');
                    if ($subprograma) {
                        $info['subprograma'] = $subprograma;
                        $programa = $this->parameterModel->getParameterById($subprograma['programa_id'], 'PG');
                        if ($programa) {
                            $info['programa'] = $programa;
                            $info['codigo_jerarquico'] = $programa['codigo'] . ' ' . $subprograma['codigo'] . ' ' . $proyecto['codigo'] . ' ' . $parametro['codigo'];
                        }
                    }
                }
                break;

            case 'ITEM':
                // Obtener actividad padre
                $actividad = $this->parameterModel->getParameterById($parametro['actividad_id'], 'ACT');
                if ($actividad) {
                    $info['actividad'] = $actividad;
                    $proyecto = $this->parameterModel->getParameterById($actividad['proyecto_id'], 'PY');
                    if ($proyecto) {
                        $info['proyecto'] = $proyecto;
                        $subprograma = $this->parameterModel->getParameterById($proyecto['subprograma_id'], 'SP');
                        if ($subprograma) {
                            $info['subprograma'] = $subprograma;
                            $programa = $this->parameterModel->getParameterById($subprograma['programa_id'], 'PG');
                            if ($programa) {
                                $info['programa'] = $programa;
                                $info['codigo_jerarquico'] = $programa['codigo'] . ' ' . $subprograma['codigo'] . ' ' . $proyecto['codigo'] . ' ' . $actividad['codigo'] . ' ' . $parametro['codigo'];
                            }
                        }
                    }
                }
                break;
        }

        return $info;
    }

    /**
     * Añadir códigos jerárquicos a un array de parámetros
     */
    private function addHierarchicalCodes($parametros, $tipo) {
        foreach ($parametros as &$param) {
            $info = $this->addHierarchyInfo($param, $tipo);
            $param['codigo_jerarquico'] = $info['codigo_jerarquico'];
        }
        return $parametros;
    }
    
    /**
     * Crear nuevo parámetro
     */
    public function createAction() {
        // Si se envió el formulario del modal
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Obtener todos los datos del formulario
                $data = [
                    'cod_programa' => $_POST['cod_programa'] ?? null,
                    'desc_programa' => $_POST['desc_programa'] ?? null,
                    'cod_subprograma' => $_POST['cod_subprograma'] ?? null,
                    'desc_subprograma' => $_POST['desc_subprograma'] ?? null,
                    'cod_proyecto' => $_POST['cod_proyecto'] ?? null,
                    'desc_proyecto' => $_POST['desc_proyecto'] ?? null,
                    'cod_actividad' => $_POST['cod_actividad'] ?? null,
                    'desc_actividad' => $_POST['desc_actividad'] ?? null,
                    'cod_fuente' => $_POST['cod_fuente'] ?? null,
                    'desc_fuente' => $_POST['desc_fuente'] ?? null,
                    'cod_ubicacion' => $_POST['cod_ubicacion'] ?? null,
                    'desc_ubicacion' => $_POST['desc_ubicacion'] ?? null,
                    'cod_item' => $_POST['cod_item'] ?? null,
                    'desc_item' => $_POST['desc_item'] ?? null,
                    'cod_organismo' => $_POST['cod_organismo'] ?? null,
                    'desc_organismo' => $_POST['desc_organismo'] ?? null,
                    'cod_nprest' => $_POST['cod_nprest'] ?? null,
                    'desc_nprest' => $_POST['desc_nprest'] ?? null,
                    'codigo_completo' => $_POST['codigo_completo'] ?? null
                ];

                // Limpiar valores vacíos a NULL
                foreach ($data as $key => $value) {
                    if (empty($value) || $value === '') {
                        $data[$key] = null;
                    }
                }

                // Validar que al menos un campo esté completo
                $hasData = false;
                foreach ($data as $value) {
                    if ($value !== null) {
                        $hasData = true;
                        break;
                    }
                }

                if (!$hasData) {
                    throw new Exception('Debe completar al menos un campo.');
                }

                $result = $this->parameterModel->createParameter($data);
                
                if ($result) {
                    $_SESSION['success'] = 'Parámetro creado correctamente.';
                    header('Location: index.php?action=parameter-list');
                    exit;
                } else {
                    throw new Exception('Error al crear el parámetro.');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
                header('Location: index.php?action=parameter-list');
                exit;
            }
        }
        
        // Si no es POST, redirigir a la lista
        header('Location: index.php?action=parameter-list');
        exit;
    }
    
    /**
     * Editar parámetro
     */
    public function editAction($id) {
        $tipos = $this->parameterModel->getParameterTypes();
        $tipo = $_GET['tipo'] ?? null;
        
        if (!$tipo || !in_array($tipo, $tipos)) {
            $_SESSION['error'] = 'Tipo de parámetro inválido.';
            header('Location: index.php?action=parameter-list');
            exit;
        }

        $parametro = $this->parameterModel->getParameterById($id, $tipo);
        
        if (!$parametro) {
            $_SESSION['error'] = 'Parámetro no encontrado.';
            header('Location: index.php?action=parameter-list');
            exit;
        }

        $programas = [];
        $subprogramas = [];
        $proyectos = [];
        $actividades = [];
        
        // Si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'tipo' => $tipo,
                    'codigo' => $_POST['codigo'] ?? '',
                    'descripcion' => $_POST['descripcion'] ?? ''
                ];

                if (empty($data['codigo']) || empty($data['descripcion'])) {
                    throw new Exception('Código y descripción son requeridos.');
                }

                // Para tipos jerárquicos, agregar el parent_id
                if ($tipo === 'SP') {
                    $data['programa_id'] = intval($_POST['programa_id'] ?? 0);
                    if ($data['programa_id'] === 0) {
                        throw new Exception('Debe seleccionar un Programa.');
                    }
                } elseif ($tipo === 'PY') {
                    $data['subprograma_id'] = intval($_POST['subprograma_id'] ?? 0);
                    if ($data['subprograma_id'] === 0) {
                        throw new Exception('Debe seleccionar un Subprograma.');
                    }
                } elseif ($tipo === 'ACT') {
                    $data['proyecto_id'] = intval($_POST['proyecto_id'] ?? 0);
                    if ($data['proyecto_id'] === 0) {
                        throw new Exception('Debe seleccionar un Proyecto.');
                    }
                } elseif ($tipo === 'ITEM') {
                    $data['actividad_id'] = intval($_POST['actividad_id'] ?? 0);
                    if ($data['actividad_id'] === 0) {
                        throw new Exception('Debe seleccionar una Actividad.');
                    }
                }

                $this->parameterModel->updateParameter($id, $data);
                $_SESSION['success'] = 'Parámetro actualizado correctamente.';
                header('Location: index.php?action=parameter-list&type=' . urlencode($tipo));
                exit;
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
        }

        // Cargar datos relacionados
        if ($tipo === 'SP') {
            $programas = $this->parameterModel->getPrograms();
            $programas = $this->addHierarchicalCodes($programas, 'PG');
        } elseif ($tipo === 'PY') {
            $subprogramas = $this->parameterModel->getParametersByType('SP');
            $subprogramas = $this->addHierarchicalCodes($subprogramas, 'SP');
        } elseif ($tipo === 'ACT') {
            $proyectos = $this->parameterModel->getParametersByType('PY');
            $proyectos = $this->addHierarchicalCodes($proyectos, 'PY');
        } elseif ($tipo === 'ITEM') {
            $actividades = $this->parameterModel->getParametersByType('ACT');
            $actividades = $this->addHierarchicalCodes($actividades, 'ACT');
        }
        
        require_once __DIR__ . '/../views/parameters/form.php';
    }
    
    /**
     * Eliminar parámetro
     */
    public function deleteAction($id) {
        $tipos = $this->parameterModel->getParameterTypes();
        $tipo = $_GET['tipo'] ?? null;
        
        if (!$tipo || !in_array($tipo, $tipos)) {
            $_SESSION['error'] = 'Tipo de parámetro inválido.';
            header('Location: index.php?action=parameter-list');
            exit;
        }

        $parametro = $this->parameterModel->getParameterById($id, $tipo);
        
        if (!$parametro) {
            $_SESSION['error'] = 'Parámetro no encontrado.';
            header('Location: index.php?action=parameter-list');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Guardar info antes de eliminar
                $codigo = $parametro['codigo'];
                $descripcion = $parametro['descripcion'];
                
                // Ejecutar delete (borrado físico)
                $this->parameterModel->deleteParameter($id, $tipo);
                
                $_SESSION['success'] = "Parámetro '$codigo' eliminado correctamente.";
            } catch (Exception $e) {
                $_SESSION['error'] = 'Error al eliminar: ' . $e->getMessage();
                error_log('Error DELETE parámetro ' . $id . ' (' . $tipo . '): ' . $e->getMessage());
            }
        }
        
        header('Location: index.php?action=parameter-list&type=' . urlencode($tipo));
        exit;
    }
}
?>

