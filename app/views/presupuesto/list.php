<?php
/**
 * Vista: Lista de Presupuestos
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h4 class="mb-0">Presupuestos</h4>
            <small class="text-muted">Gestión de presupuesto importado</small>
        </div>
        <div class="col-md-4 text-end">
            <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
            <a href="index.php?action=presupuesto-upload" class="btn btn-primary btn-sm">
                <i class="fas fa-upload"></i> Importar CSV
            </a>
            <a href="index.php?action=presupuesto-export-excel" class="btn btn-success btn-sm" title="Exportar a CSV">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
            <a href="index.php?action=presupuesto-export-pdf" class="btn btn-danger btn-sm" title="Exportar a PDF">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <?php endif; ?>
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
    <div class="row row-cols-1 row-cols-md-4 g-2 mb-3">
        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-list fa-xs"></i> Total Items</p>
                    <h5 class="mb-0"><?php echo number_format($totalItems); ?></h5>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-coins fa-xs text-info"></i> Total Codificado</p>
                    <h5 class="mb-0">
                        $<?php echo number_format($resumen['total_codificado'] ?? 0, 0, '.', ','); ?>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-check-circle fa-xs text-success"></i> Total Certificado</p>
                    <h5 class="mb-0">
                        $<?php echo number_format($resumen['total_certificado'] ?? 0, 0, '.', ','); ?>
                    </h5>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <p class="text-muted mb-1 small"><i class="fas fa-balance-scale fa-xs text-warning"></i> Saldo Disponible</p>
                    <h5 class="mb-0">
                        $<?php echo number_format($resumen['total_saldo_disponible'] ?? 0, 0, '.', ','); ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-2 px-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-code"></i></span>
                        <select class="form-select form-select-sm" id="filterPrograma">
                            <option value="">Código Programa</option>
                            <?php 
                            $programas = array_unique(array_column($items, 'codigog1'));
                            sort($programas);
                            foreach ($programas as $prog): ?>
                                <option value="<?php echo htmlspecialchars($prog); ?>">
                                    <?php echo htmlspecialchars($prog); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-tasks"></i></span>
                        <select class="form-select form-select-sm" id="filterActividad">
                            <option value="">Código Actividad</option>
                            <?php 
                            $actividades = array_unique(array_column($items, 'codigog2'));
                            sort($actividades);
                            foreach ($actividades as $act): ?>
                                <option value="<?php echo htmlspecialchars($act); ?>">
                                    <?php echo htmlspecialchars($act); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-piggy-bank"></i></span>
                        <select class="form-select form-select-sm" id="filterFuente">
                            <option value="">Código Fuente</option>
                            <?php 
                            $fuentes = array_unique(array_column($items, 'codigog3'));
                            sort($fuentes);
                            foreach ($fuentes as $fuente): ?>
                                <option value="<?php echo htmlspecialchars($fuente); ?>">
                                    <?php echo htmlspecialchars($fuente); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-1">
                    <button class="btn btn-sm btn-outline-secondary w-100" id="btnLimpiar" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Presupuestos -->
    <div class="card shadow-sm border-0">
        <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important; border-bottom: 1px solid #333; padding: 0.5rem 0.75rem;">
            <div class="row align-items-center g-2">
                <div class="col">
                    <small style="color: white !important;"><i class="fas fa-list"></i> Lista de Presupuestos</small>
                </div>
                <div class="col-auto">
                    <input type="text" class="form-control form-control-sm" id="searchInput" 
                           placeholder="Buscar..." style="width: 200px;">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">
                        No hay presupuestos importados.<br>
                        <a href="index.php?action=presupuesto-upload">Haz clic aquí para importar un CSV</a>
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
                                <th style="width: 50px;">#</th>
                                <th>Código Programa</th>
                                <th>Código Actividad</th>
                                <th>Código Fuente</th>
                                <th>Código Item</th>
                                <th>Descripción Item</th>
                                <th class="text-end">Codificado</th>
                                <th class="text-end">Certificado</th>
                                <th class="text-end">Saldo Disponible</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr class="searchable-row" 
                                    data-search="<?php 
                                        echo htmlspecialchars(strtolower(
                                            $item['codigog1'] . ' ' . 
                                            $item['codigog2'] . ' ' . 
                                            $item['codigog3'] . ' ' .
                                            $item['descripciong3'] . ' ' . 
                                            $item['codigog5'] . ' ' .
                                            $item['descripciong5']
                                        )); 
                                    ?>"
                                    data-programa="<?php echo htmlspecialchars($item['codigog1']); ?>"
                                    data-actividad="<?php echo htmlspecialchars($item['codigog2']); ?>"
                                    data-fuente="<?php echo htmlspecialchars($item['codigog3']); ?>">
                                    <td>
                                        <input type="checkbox" class="form-check-input presupuesto-checkbox" data-id="<?php echo $item['id']; ?>">
                                    </td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($item['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['codigog1']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item['codigog2']); ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['codigog3']); ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['codigog5']); ?></strong>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($item['descripciong5'], 0, 40)); ?></small>
                                    </td>
                                    <td class="text-end fw-bold">
                                        $<?php echo number_format($item['col3'] ?? 0, 2, '.', ','); ?>
                                    </td>
                                    <td class="text-end fw-bold">
                                        $<?php echo number_format($item['col4'] ?? 0, 2, '.', ','); ?>
                                    </td>
                                    <td class="text-end fw-bold">
                                        <?php 
                                        $saldo = ($item['saldo_disponible'] ?? 0);
                                        $class = $saldo >= 0 ? 'text-success' : 'text-danger';
                                        ?>
                                        <span class="<?php echo $class; ?>">
                                            $<?php echo number_format($saldo, 2, '.', ','); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?action=presupuesto-view&id=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
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
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSelectedPresupuestos()" title="Eliminar seleccionados">
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
                <p>¿Estás seguro de que deseas eliminar los <strong id="confirmDeleteCount">0</strong> presupuestos seleccionados?</p>
                <p class="text-danger small"><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteSelected()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Función para alternar todos los checkboxes
function toggleAllCheckboxes(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.presupuesto-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    updateSelectedPanel();
}

// Función para actualizar el panel de seleccionados
function updateSelectedPanel() {
    const checkboxes = document.querySelectorAll('.presupuesto-checkbox:checked');
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
    const checkboxes = document.querySelectorAll('.presupuesto-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedPanel);
    });
});

// Función para mostrar confirmación de borrado múltiple
function deleteSelectedPresupuestos() {
    const checkboxes = document.querySelectorAll('.presupuesto-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Por favor selecciona al menos un presupuesto');
        return;
    }
    
    document.getElementById('confirmDeleteCount').textContent = checkboxes.length;
    const modal = new bootstrap.Modal(document.getElementById('deleteMultipleModal'));
    modal.show();
}

// Función para confirmar y ejecutar el borrado múltiple
function confirmDeleteSelected() {
    const checkboxes = document.querySelectorAll('.presupuesto-checkbox:checked');
    
    const toDelete = Array.from(checkboxes).map(cb => ({
        id: cb.dataset.id
    }));
    
    let deletedCount = 0;
    let errorCount = 0;
    let currentIndex = 0;
    
    const deleteNextItem = () => {
        if (currentIndex >= toDelete.length) {
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
        form.action = 'index.php?action=presupuesto-delete&id=' + item.id;
        form.style.display = 'none';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(() => {
            deletedCount++;
        })
        .catch(error => {
            console.error('Error deleting presupuesto:', error);
            errorCount++;
        })
        .finally(() => {
            form.remove();
            currentIndex++;
            deleteNextItem();
        });
    };
    
    deleteNextItem();
    
    bootstrap.Modal.getInstance(document.getElementById('deleteMultipleModal')).hide();
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterPrograma = document.getElementById('filterPrograma').value;
    const filterActividad = document.getElementById('filterActividad').value;
    const filterFuente = document.getElementById('filterFuente').value;
    
    const rows = document.querySelectorAll('.searchable-row');
    
    rows.forEach(row => {
        const searchData = row.getAttribute('data-search').toLowerCase();
        const programa = row.getAttribute('data-programa');
        const actividad = row.getAttribute('data-actividad');
        const fuente = row.getAttribute('data-fuente');
        
        const matchSearch = searchData.includes(searchTerm);
        const matchPrograma = !filterPrograma || programa === filterPrograma;
        const matchActividad = !filterActividad || actividad === filterActividad;
        const matchFuente = !filterFuente || fuente === filterFuente;
        
        row.style.display = (matchSearch && matchPrograma && matchActividad && matchFuente) ? '' : 'none';
    });
}

// Event listeners para filtros
document.getElementById('searchInput').addEventListener('keyup', applyFilters);
document.getElementById('filterPrograma').addEventListener('change', applyFilters);
document.getElementById('filterActividad').addEventListener('change', applyFilters);
document.getElementById('filterFuente').addEventListener('change', applyFilters);

// Limpiar filtros
document.getElementById('btnLimpiar').addEventListener('click', function() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterPrograma').value = '';
    document.getElementById('filterActividad').value = '';
    document.getElementById('filterFuente').value = '';
    applyFilters();
});
</script>
