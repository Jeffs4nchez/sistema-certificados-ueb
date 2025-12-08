<style>
    .bg-purple {
        background-color: #0B283F !important;
        color: #fff !important;
    }
</style>
<?php
/**
 * Vista: Parámetros Presupuestarios - Índice
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">Parámetros Presupuestarios</h1>
            <p class="text-muted">Gestión de valores para la estructura presupuestaria</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=bulk-import" class="btn btn-warning me-2" title="Importar estructura completa">
                <i class="fas fa-database"></i> Importación Masiva
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tipoModal">
                <i class="fas fa-plus"></i> Nuevo Parámetro
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-layer-group fa-xs"></i> Tipos de Parámetros</p>
                    <h5 class="mb-0"><?php echo $totalTipos; ?></h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-list fa-xs"></i> Total Parámetros</p>
                    <h5 class="mb-0"><?php echo $totalParametros; ?></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-filter"></i></span>
                        <select class="form-select form-select-sm" id="filterTipo" onchange="location.href='index.php?action=parameter-list&type=' + this.value">
                            <option value="">-- Todos los tipos --</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo ($type === $t) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="location.href='index.php?action=parameter-list'">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Parámetros -->
    <div class="card shadow-sm border-0">
        <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important; border-bottom: 1px solid #333; padding: 0.5rem 0.75rem;">
            <small style="color: white !important;"><i class="fas fa-list"></i> Lista de Parámetros (<?php echo count($parametros); ?>)</small>
        </div>
        <div class="card-body p-0">
            <?php if (empty($parametros)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">
                        No hay parámetros registrados.<br>
                        <a href="index.php?action=parameter-create">Crear uno ahora</a>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background-color: #0B283F !important; color: white !important;">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input" onchange="toggleAllCheckboxes(this)">
                                </th>
                                <th style="width: 60px;">#</th>
                                <th style="width: 100px;">Tipo</th>
                                <th style="width: 100px;">Código</th>
                                <th>Descripción</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Definir colores para cada tipo (Bootstrap colors)
                            $colores = [
                                'PG' => 'primary',        // Azul
                                'SP' => 'success',        // Verde
                                'PY' => 'info',           // Celeste
                                'ACT' => 'warning',       // Naranja
                                'ITEM' => 'danger',       // Rojo
                                'UBG' => 'secondary',     // Gris
                                'FTE' => 'dark',          // Negro
                                'ORG' => 'info',          // Celeste claro (diferente de PY)
                                'N.PREST' => 'purple'     // Color personalizado para N.PREST
                            ];
                            ?>
                            <?php 
                            // Mapeo de tipo a campos
                            foreach ($parametros as $param): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input param-checkbox" data-id="<?php echo $param['id']; ?>" data-tipo="<?php echo htmlspecialchars($param['tipo']); ?>">
                                    </td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($param['id']); ?></td>
                                    <td><span class="badge bg-<?php echo $colores[$param['tipo']] ?? 'secondary'; ?>"><?php echo htmlspecialchars($param['tipo']); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($param['codigo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($param['descripcion']); ?></td>
                                    <td style="white-space: nowrap; vertical-align: middle;">
                                        <a href="index.php?action=parameter-edit&id=<?php echo $param['id']; ?>&tipo=<?php echo urlencode($param['tipo']); ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Editar" style="display: inline-block; margin: 2px;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="index.php?action=parameter-delete&id=<?php echo $param['id']; ?>&tipo=<?php echo urlencode($param['tipo']); ?>" 
                                              style="display: inline-block; margin: 2px;" 
                                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar este parámetro?');">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panel de acciones para registros seleccionados -->
    <div id="selectedActionsPanel" class="mt-3 p-3 bg-light border rounded d-none">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="badge bg-info me-2" id="selectedCount">0 seleccionados</span>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSelectedParameters()" title="Eliminar seleccionados">
                    <i class="fas fa-trash"></i> Eliminar seleccionados
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para borrado múltiple -->
<div class="modal fade" id="deleteMultipleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar eliminación múltiple</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar los <strong id="confirmDeleteCount">0</strong> parámetros seleccionados?</p>
                <p class="text-danger small"><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteSelected()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para seleccionar tipo de parámetro -->
<div class="modal fade" id="tipoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-layer-group"></i> Seleccionar Tipo de Parámetro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <a href="index.php?action=parameter-create&tipo=PG" class="list-group-item list-group-item-action">
                        <strong>PG</strong> - Programas
                    </a>
                    <a href="index.php?action=parameter-create&tipo=SP" class="list-group-item list-group-item-action">
                        <strong>SP</strong> - Subprogramas
                    </a>
                    <a href="index.php?action=parameter-create&tipo=PY" class="list-group-item list-group-item-action">
                        <strong>PY</strong> - Proyectos
                    </a>
                    <a href="index.php?action=parameter-create&tipo=ACT" class="list-group-item list-group-item-action">
                        <strong>ACT</strong> - Actividades
                    </a>
                    <a href="index.php?action=parameter-create&tipo=ITEM" class="list-group-item list-group-item-action">
                        <strong>ITEM</strong> - Items Presupuestarios
                    </a>
                    <a href="index.php?action=parameter-create&tipo=UBG" class="list-group-item list-group-item-action">
                        <strong>UBG</strong> - Ubicaciones Geográficas
                    </a>
                    <a href="index.php?action=parameter-create&tipo=FTE" class="list-group-item list-group-item-action">
                        <strong>FTE</strong> - Fuentes de Financiamiento
                    </a>
                    <a href="index.php?action=parameter-create&tipo=ORG" class="list-group-item list-group-item-action">
                        <strong>ORG</strong> - Organismos
                    </a>
                    <a href="index.php?action=parameter-create&tipo=N.PREST" class="list-group-item list-group-item-action">
                        <strong>N.PREST</strong> - Naturaleza de Prestación
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para alternar todos los checkboxes
function toggleAllCheckboxes(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.param-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateSelectedPanel();
}

// Función para actualizar el panel de seleccionados
function updateSelectedPanel() {
    const checkboxes = document.querySelectorAll('.param-checkbox:checked');
    const panel = document.getElementById('selectedActionsPanel');
    const count = document.getElementById('selectedCount');
    
    if (checkboxes.length > 0) {
        count.textContent = checkboxes.length + ' seleccionado' + (checkboxes.length !== 1 ? 's' : '');
        panel.classList.remove('d-none');
    } else {
        panel.classList.add('d-none');
        document.getElementById('selectAll').checked = false;
    }
}

// Agregar event listeners a los checkboxes individuales
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.param-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedPanel);
    });
});

// Función para mostrar confirmación de borrado múltiple
function deleteSelectedParameters() {
    const checkboxes = document.querySelectorAll('.param-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Por favor selecciona al menos un parámetro');
        return;
    }
    
    document.getElementById('confirmDeleteCount').textContent = checkboxes.length;
    const modal = new bootstrap.Modal(document.getElementById('deleteMultipleModal'));
    modal.show();
}

// Función para confirmar y ejecutar el borrado múltiple
function confirmDeleteSelected() {
    const checkboxes = document.querySelectorAll('.param-checkbox:checked');
    
    // Contar por tipo para mensajes
    const toDelete = Array.from(checkboxes).map(cb => ({
        id: cb.dataset.id,
        tipo: cb.dataset.tipo
    }));
    
    // Realizar borrados individuales de manera secuencial
    let deletedCount = 0;
    let errorCount = 0;
    let currentIndex = 0;
    
    const deleteNextItem = () => {
        if (currentIndex >= toDelete.length) {
            // Todos completados
            if (deletedCount + errorCount === toDelete.length) {
                setTimeout(() => {
                    location.reload();
                }, 500);
            }
            return;
        }
        
        const item = toDelete[currentIndex];
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?action=parameter-delete&id=' + item.id + '&tipo=' + encodeURIComponent(item.tipo);
        form.style.display = 'none';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        
        // Usar fetch en lugar de submit directo
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(() => {
            deletedCount++;
        })
        .catch(error => {
            console.error('Error deleting parameter:', error);
            errorCount++;
        })
        .finally(() => {
            form.remove();
            currentIndex++;
            deleteNextItem(); // Llamar recursivamente para el siguiente
        });
    };
    
    // Iniciar el proceso
    deleteNextItem();
    
    // Cerrar modal
    bootstrap.Modal.getInstance(document.getElementById('deleteMultipleModal')).hide();
}
</script>

