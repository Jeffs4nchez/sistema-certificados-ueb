<?php
/**
 * Vista: Lista de Certificados
 */
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5">Certificados</h1>
            <p class="text-muted">Gestión de certificados del sistema</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=certificate-create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Crear Certificado
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0"><i class="fas fa-table"></i> Lista de Certificados</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($certificates)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">
                        No hay certificados.<br>
                        <a href="index.php?action=certificate-create">Crea uno ahora</a>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">#</th>
                                <th>Número Certificado</th>
                                <th>Institución</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th>Monto Total</th>
                                <th style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificates as $cert): ?>
                                <tr>
                                    <td class="text-muted small fw-bold"><?php echo htmlspecialchars($cert['id']); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($cert['numero_certificado'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($cert['institucion'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($cert['usuario_creacion'] ?? 'Sistema'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cert['fecha_elaboracion'] ?? '2025-01-01')); ?></td>
                                    <td class="text-end">$ <?php echo number_format($cert['monto_total'] ?? 0, 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="index.php?action=certificate-view&id=<?php echo $cert['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success" title="Liquidación"
                                                onclick="openLiquidacionModal(<?php echo $cert['id']; ?>)">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </button>
                                        <a href="index.php?action=certificate-edit&id=<?php echo $cert['id']; ?>" 
                                           class="btn btn-sm btn-outline-secondary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="index.php?action=certificate-delete&id=<?php echo $cert['id']; ?>" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este certificado?');">
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
</div>

<!-- Modal de Liquidación -->
<div class="modal fade" id="liquidacionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Liquidación de Certificado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="liquidacionContent">
                    <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="saveAllLiquidaciones()">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function openLiquidacionModal(certificateId) {
    const modal = new bootstrap.Modal(document.getElementById('liquidacionModal'));
    
    try {
        // Obtener detalles del certificado
        const response = await fetch(`index.php?action=api-certificate&action-api=get-liquidacion&certificate_id=${certificateId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 8%;">PG</th>
                                <th style="width: 8%;">SP</th>
                                <th style="width: 8%;">PY</th>
                                <th style="width: 8%;">ACT</th>
                                <th style="width: 8%;">ITEM</th>
                                <th style="width: 10%;">Descripción</th>
                                <th style="width: 12%;">Monto</th>
                                <th style="width: 30%;">Liquidación</th>
                            </tr>
                        </thead>
                        <tbody id="liquidacionTableBody">
            `;
            
            result.data.forEach(item => {
                html += `
                    <tr>
                        <td><small>${item.programa_codigo}</small></td>
                        <td><small>${item.subprograma_codigo}</small></td>
                        <td><small>${item.proyecto_codigo}</small></td>
                        <td><small>${item.actividad_codigo}</small></td>
                        <td><small>${item.item_codigo}</small></td>
                        <td><small>${item.descripcion_item}</small></td>
                        <td class="text-end">$ ${parseFloat(item.monto).toFixed(2)}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm liquidacion-input" 
                                   value="${parseFloat(item.cantidad_liquidacion || 0).toFixed(2)}"
                                   data-detalle-id="${item.id}" step="0.01" min="0">
                        </td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            document.getElementById('liquidacionContent').innerHTML = html;
            modal.show();
        } else {
            document.getElementById('liquidacionContent').innerHTML = '<div class="alert alert-danger">Error al cargar los detalles</div>';
        }
    } catch (error) {
        document.getElementById('liquidacionContent').innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
        console.error('Error:', error);
    }
}

async function saveAllLiquidaciones() {
    const inputs = document.querySelectorAll('.liquidacion-input');
    const liquidaciones = [];
    
    // Recolectar solo las liquidaciones con valores ingresados
    inputs.forEach(input => {
        const cantidad = parseFloat(input.value) || 0;
        if (cantidad > 0) {  // Solo guardar si hay un valor
            liquidaciones.push({
                detalle_id: input.dataset.detalleId,
                cantidad_liquidacion: cantidad
            });
        }
    });
    
    // Verificar que al menos una liquidación fue ingresada
    if (liquidaciones.length === 0) {
        alert('⚠️ Por favor ingresa al menos una liquidación');
        return;
    }
    
    try {
        const saveButton = event.target;
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        // Guardar cada liquidación
        for (const liquidacion of liquidaciones) {
            const formData = new FormData();
            formData.append('detalle_id', liquidacion.detalle_id);
            formData.append('cantidad_liquidacion', liquidacion.cantidad_liquidacion);
            
            const response = await fetch('index.php?action=api-certificate&action-api=update-liquidacion', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Error desconocido al guardar liquidación');
            }
        }
        
        // Cerrar modal y mostrar mensaje de éxito
        const modal = bootstrap.Modal.getInstance(document.getElementById('liquidacionModal'));
        modal.hide();
        
        alert('✓ Liquidaciones guardadas correctamente');
        
        // Recargar la página para actualizar datos
        location.reload();
        
    } catch (error) {
        alert('Error: ' + error.message);
        const saveButton = event.target;
        saveButton.disabled = false;
        saveButton.innerHTML = '<i class="fas fa-save"></i> Guardar';
    }
}
</script>

