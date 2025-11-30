<?php
/**
 * Vista: Formulario de Parámetro
 */
$isEdit = isset($parametro) && $parametro;
$title = $isEdit ? 'Editar Parámetro' : 'Nuevo Parámetro';
$tipo = $isEdit ? $parametro['tipo'] : ($tipo_seleccionado ?? '');

// Mapeo de descripciones de tipos
$tipo_descriptions = [
    'PG' => 'Programas',
    'SP' => 'Subprogramas',
    'PY' => 'Proyectos',
    'ACT' => 'Actividades',
    'ITEM' => 'Items Presupuestarios',
    'UBG' => 'Ubicaciones Geográficas',
    'FTE' => 'Fuentes de Financiamiento',
    'ORG' => 'Organismos',
    'N.PREST' => 'Naturaleza de Prestación'
];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5"><?php echo $title; ?></h1>
            <p class="text-muted">Gestión de parámetros presupuestarios</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=parameter-list<?php echo $tipo ? '&type=' . urlencode($tipo) : ''; ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <!-- Tipo de Parámetro -->
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Parámetro <span class="text-danger">*</span></label>
                            <?php if ($isEdit): ?>
                                <input type="text" class="form-control" id="tipo" name="tipo" 
                                       value="<?php echo htmlspecialchars($tipo); ?> - <?php echo $tipo_descriptions[$tipo] ?? ''; ?>" readonly>
                            <?php else: ?>
                                <select class="form-select" id="tipo" name="tipo" required onchange="location.href='index.php?action=parameter-create&tipo=' + this.value">
                                    <option value="">-- Selecciona un tipo --</option>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?php echo $t; ?>" <?php echo ($tipo === $t) ? 'selected' : ''; ?>>
                                            <?php echo $t . ' - ' . $tipo_descriptions[$t]; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <!-- Código -->
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="codigo" name="codigo" 
                                   value="<?php echo $isEdit ? htmlspecialchars($parametro['codigo']) : ''; ?>" 
                                   placeholder="Ingresa el código" required>
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                      placeholder="Ingresa la descripción" required><?php echo $isEdit ? htmlspecialchars($parametro['descripcion']) : ''; ?></textarea>
                        </div>

                        <!-- Para SP: Seleccionar Programa -->
                        <?php if ($tipo === 'SP'): ?>
                            <div class="mb-3">
                                <label for="programa_id" class="form-label">Programa <span class="text-danger">*</span></label>
                                <select class="form-select" id="programa_id" name="programa_id" required>
                                    <option value="">-- Selecciona un Programa --</option>
                                    <?php foreach ($programas as $prog): ?>
                                        <option value="<?php echo $prog['id']; ?>" 
                                            <?php echo ($isEdit && $parametro['programa_id'] == $prog['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prog['codigo_jerarquico'] . ' - ' . $prog['descripcion']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Para PY: Seleccionar Subprograma -->
                        <?php if ($tipo === 'PY'): ?>
                            <div class="mb-3">
                                <label for="subprograma_id" class="form-label">Subprograma <span class="text-danger">*</span></label>
                                <select class="form-select" id="subprograma_id" name="subprograma_id" required>
                                    <option value="">-- Selecciona un Subprograma --</option>
                                    <?php foreach ($subprogramas as $subprog): ?>
                                        <option value="<?php echo $subprog['id']; ?>" 
                                            <?php echo ($isEdit && $parametro['subprograma_id'] == $subprog['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subprog['codigo_jerarquico'] . ' - ' . $subprog['descripcion']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Para ACT: Seleccionar Proyecto -->
                        <?php if ($tipo === 'ACT'): ?>
                            <div class="mb-3">
                                <label for="proyecto_id" class="form-label">Proyecto <span class="text-danger">*</span></label>
                                <select class="form-select" id="proyecto_id" name="proyecto_id" required>
                                    <option value="">-- Selecciona un Proyecto --</option>
                                    <?php foreach ($proyectos as $proy): ?>
                                        <option value="<?php echo $proy['id']; ?>" 
                                            <?php echo ($isEdit && $parametro['proyecto_id'] == $proy['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($proy['codigo_jerarquico'] . ' - ' . $proy['descripcion']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Para ITEM: Seleccionar Actividad -->
                        <?php if ($tipo === 'ITEM'): ?>
                            <div class="mb-3">
                                <label for="actividad_id" class="form-label">Actividad <span class="text-danger">*</span></label>
                                <select class="form-select" id="actividad_id" name="actividad_id" required>
                                    <option value="">-- Selecciona una Actividad --</option>
                                    <?php foreach ($actividades as $act): ?>
                                        <option value="<?php echo $act['id']; ?>" 
                                            <?php echo ($isEdit && $parametro['actividad_id'] == $act['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($act['codigo_jerarquico'] . ' - ' . $act['descripcion']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php?action=parameter-list<?php echo $tipo ? '&type=' . urlencode($tipo) : ''; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $isEdit ? 'Actualizar' : 'Crear'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-3">
                    <h6 class="card-title"><i class="fas fa-info-circle"></i> Tipos de Parámetros</h6>
                    <hr>
                    <ul class="list-unstyled small">
                        <li><strong>PG</strong> - Programas</li>
                        <li><strong>SP</strong> - Subprogramas</li>
                        <li><strong>PY</strong> - Proyectos</li>
                        <li><strong>ACT</strong> - Actividades</li>
                        <li><strong>ITEM</strong> - Items Presupuestarios</li>
                        <li><strong>UBG</strong> - Ubicaciones Geográficas</li>
                        <li><strong>FTE</strong> - Fuentes de Financiamiento</li>
                        <li><strong>ORG</strong> - Organismos</li>
                        <li><strong>N.PREST</strong> - Naturaleza de Prestación</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
?>
