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
            <a href="index.php?action=presupuesto-upload" class="btn btn-primary btn-sm">
                <i class="fas fa-upload"></i> Importar CSV
            </a>
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
                            $programas = array();
                            foreach ($items as $item) {
                                $programas[$item['codigog1']] = $item['descripciong1'];
                            }
                            ksort($programas);
                            foreach ($programas as $code => $desc): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>">
                                    <?php echo htmlspecialchars($code) . ' - ' . htmlspecialchars($desc); ?>
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
                            $actividades = array();
                            foreach ($items as $item) {
                                $actividades[$item['codigog2']] = $item['descripciong2'];
                            }
                            ksort($actividades);
                            foreach ($actividades as $code => $desc): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>">
                                    <?php echo htmlspecialchars($code) . ' - ' . htmlspecialchars($desc); ?>
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
                            $fuentes = array();
                            foreach ($items as $item) {
                                $fuentes[$item['codigog3']] = $item['descripciong3'];
                            }
                            ksort($fuentes);
                            foreach ($fuentes as $code => $desc): ?>
                                <option value="<?php echo htmlspecialchars($code); ?>">
                                    <?php echo htmlspecialchars($code) . ' - ' . htmlspecialchars($desc); ?>
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
        <div class="card-header bg-light border-bottom py-2 px-3">
            <div class="row align-items-center g-2">
                <div class="col">
                    <small><i class="fas fa-list"></i> Lista de Presupuestos</small>
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
                        <thead class="table-light">
                            <tr>
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
</div>

<script>
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
