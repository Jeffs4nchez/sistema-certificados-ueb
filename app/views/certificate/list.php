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
            <a href="index.php?action=certificate-export" class="btn btn-success me-2">
                <i class="fas fa-download"></i> Exportar Reporte
            </a>
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
        <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
            <h5 class="mb-0" style="color: white !important;"><i class="fas fa-table"></i> Lista de Certificados</h5>
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
                        <thead style="background-color: #0B283F !important; color: white !important;">
                            <tr>
                                <th style="width: 80px;">#</th>
                                <th>Número Certificado</th>
                                <th>Institución</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th>Monto Total</th>
                                <th>Liquidado</th>
                                <th>Pendiente</th>
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
                                    <td class="text-end text-success fw-bold">$ <?php echo number_format($cert['total_liquidado'] ?? 0, 2, ',', '.'); ?></td>
                                    <td class="text-end text-warning fw-bold">$ <?php echo number_format($cert['total_pendiente'] ?? 0, 2, ',', '.'); ?></td>
                                    <td style="white-space: nowrap; vertical-align: middle;">
                                        <a href="index.php?action=certificate-view&id=<?php echo $cert['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver" style="display: inline-block; margin: 2px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success" title="Liquidación"
                                                onclick="openLiquidacionModal(<?php echo $cert['id']; ?>)" style="display: inline-block; margin: 2px;">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </button>
                                        <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" title="Editar"
                                                onclick="openEditModal(<?php echo $cert['id']; ?>)" style="display: inline-block; margin: 2px;">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="index.php?action=certificate-delete&id=<?php echo $cert['id']; ?>" 
                                              style="display: inline-block; margin: 2px;" 
                                              onsubmit="return confirm('¿Estás seguro de eliminar este certificado?');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
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

<!-- Modal de Historial de Liquidaciones -->
<div class="modal fade" id="historicoLiquidacionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" style="color: white !important;">Historial de Liquidaciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="historicoContent">
                    <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #dee2e6;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
                <button type="button" class="btn btn-success" id="btnIrALiquidar">
                    <i class="fas fa-plus"></i> Nueva Liquidación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Liquidación -->
<div class="modal fade" id="liquidacionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" style="color: white !important;">Registrar Nueva Liquidación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="liquidacionContent">
                    <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</p>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #dee2e6;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btnGuardarLiquidaciones">
                    <i class="fas fa-save"></i> Guardar Liquidaciones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variable para almacenar el ID del certificado actual
let currentCertificateId = null;

async function openLiquidacionModal(certificateId) {
    currentCertificateId = certificateId;
    
    try {
        // Obtener historial de liquidaciones
        const response = await fetch(`index.php?action=api-certificate&action-api=get-liquidacion-historial&certificate_id=${certificateId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            let html = '';
            
            if (result.data.liquidaciones && result.data.liquidaciones.length > 0) {
                // Mostrar tabla de historial agrupado por item
                html += `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Liquidaciones registradas de este certificado
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead style="background-color: #0B283F !important; color: white !important;">
                                <tr>
                                    <th>Descripción del Item</th>
                                    <th>Fecha</th>
                                    <th>Cantidad</th>
                                    <th>Usuario</th>
                                    <th>Memorando</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                result.data.liquidaciones.forEach(item => {
                    // Mostrar el encabezado del item
                    html += `
                        <tr style="background-color: #f0f0f0;">
                            <td colspan="5" class="fw-bold">
                                <i class="fas fa-box"></i> ${item.descripcion_item}
                            </td>
                        </tr>
                    `;
                    
                    // Mostrar cada liquidación del item
                    item.liquidaciones.forEach(liq => {
                        html += `
                            <tr>
                                <td></td>
                                <td><small>${liq.fecha}</small></td>
                                <td class="text-end fw-bold">$ ${parseFloat(liq.cantidad).toLocaleString('es-ES', {minimumFractionDigits: 2})}</td>
                                <td><small>${liq.usuario || 'SISTEMA'}</small></td>
                                <td><small>${liq.memorando || '-'}</small></td>
                            </tr>
                        `;
                    });
                    
                    // Mostrar subtotal del item
                    html += `
                        <tr style="background-color: #e8f5e9;">
                            <td colspan="2" class="text-end fw-bold">Subtotal:</td>
                            <td class="text-end fw-bold text-success">$ ${parseFloat(item.subtotal).toLocaleString('es-ES', {minimumFractionDigits: 2})}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-success mt-3">
                        <strong><i class="fas fa-calculator"></i> Total General Liquidado:</strong> $ ${parseFloat(result.data.total_general).toLocaleString('es-ES', {minimumFractionDigits: 2})}
                    </div>
                `;
            } else {
                // No hay liquidaciones anteriores
                html += `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i> <strong>No hay liquidaciones anteriores</strong>
                        <p class="mt-2">Este certificado aún no ha sido liquidado. ¡Crea la primera liquidación!</p>
                    </div>
                `;
            }
            
            document.getElementById('historicoContent').innerHTML = html;
            const modal = new bootstrap.Modal(document.getElementById('historicoLiquidacionModal'));
            modal.show();
        } else {
            document.getElementById('historicoContent').innerHTML = '<div class="alert alert-danger">Error al cargar el historial</div>';
        }
    } catch (error) {
        document.getElementById('historicoContent').innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
        console.error('Error:', error);
    }
}

// Botón para ir a la liquidación
document.addEventListener('DOMContentLoaded', function() {
    const btnIrALiquidar = document.getElementById('btnIrALiquidar');
    if (btnIrALiquidar) {
        btnIrALiquidar.addEventListener('click', function() {
            // Cerrar modal de historial
            bootstrap.Modal.getInstance(document.getElementById('historicoLiquidacionModal')).hide();
            // Abrir modal de liquidación
            abrirModalRegistroLiquidacion(currentCertificateId);
        });
    }
});

async function abrirModalRegistroLiquidacion(certificateId) {
    const modal = new bootstrap.Modal(document.getElementById('liquidacionModal'));
    
    try {
        // Obtener detalles del certificado
        const response = await fetch(`index.php?action=api-certificate&action-api=get-liquidacion&certificate_id=${certificateId}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead style="background-color: #0B283F !important; color: white !important;">
                            <tr>
                                <th style="width: 6%;">PG</th>
                                <th style="width: 6%;">SP</th>
                                <th style="width: 6%;">PY</th>
                                <th style="width: 6%;">ACT</th>
                                <th style="width: 6%;">ITEM</th>
                                <th style="width: 14%;">Descripción</th>
                                <th style="width: 10%;">Monto</th>
                                <th style="width: 14%;">Liquidación</th>
                                <th style="width: 24%;">Memorando</th>
                            </tr>
                        </thead>
                        <tbody>
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
                        <td class="text-end"><strong>$ ${parseFloat(item.monto).toFixed(2)}</strong></td>
                        <td>
                            <input type="number" class="form-control form-control-sm liquidacion-input" 
                                   value=""
                                   data-detalle-id="${item.id}" 
                                   data-cantidad-pendiente="${parseFloat(item.cantidad_pendiente || 0).toFixed(2)}"
                                   step="0.01" min="0" 
                                   onchange="validarLiquidacion(this)" 
                                   oninput="mostrarAlerta(this)">
                            <small class="text-danger d-none validacion-error" data-detalle-id="${item.id}"></small>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm memorando-input" 
                                   value=""
                                   placeholder="Ej: Comprobante #123"
                                   data-detalle-id="${item.id}" maxlength="255"
                                   oninput="validarMemorandoObligatorio(this)">
                            <small class="text-danger d-none validacion-error-memorando" data-detalle-id="${item.id}">Obligatorio si hay liquidación</small>
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

async function saveLiquidacion(detalleId, button) {
    const input = button.previousElementSibling;
    const cantidadLiquidacion = parseFloat(input.value) || 0;
    const cantidadPendiente = parseFloat(input.dataset.cantidadPendiente) || 0;
    
    // Validar que no exceda cantidad pendiente
    if (cantidadLiquidacion > cantidadPendiente) {
        alert(`Error: La cantidad a liquidar ($ ${cantidadLiquidacion.toFixed(2)}) no puede ser mayor al saldo pendiente ($ ${cantidadPendiente.toFixed(2)})`);
        return;
    }
    
    if (cantidadLiquidacion <= 0) {
        alert('Error: Debes ingresar una cantidad mayor a 0');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('detalle_id', detalleId);
        formData.append('cantidad_liquidacion', cantidadLiquidacion);
        
        const response = await fetch('index.php?action=api-certificate&action-api=update-liquidacion', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            button.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-save"></i>';
            }, 2000);
            alert('✓ Liquidación actualizada correctamente');
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function clearLiquidacion(detalleId, button) {
    if (confirm('¿Limpiar la liquidación de este item?')) {
        const row = button.closest('tr');
        const input = row.querySelector('.liquidacion-input');
        input.value = '0';
        const saveButton = row.querySelector('button');
        await saveLiquidacion(detalleId, saveButton);
    }
}

// Guardar todas las liquidaciones
document.getElementById('btnGuardarLiquidaciones').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando...';
    
    try {
        const liquidacionInputs = document.querySelectorAll('.liquidacion-input');
        const memorandoInputs = document.querySelectorAll('.memorando-input');
        const liquidaciones = [];
        let hayErrores = false;
        let erroresValidacion = [];
        
        liquidacionInputs.forEach((input, index) => {
            const memorandoInput = memorandoInputs[index];
            const cantidad = parseFloat(input.value) || 0;
            const cantidadPendiente = parseFloat(input.dataset.cantidadPendiente) || 0;
            
            // Si hay liquidación, validar que tenga memorando
            if (cantidad > 0) {
                const memorando = memorandoInput.value.trim();
                if (!memorando) {
                    memorandoInput.classList.add('is-invalid');
                    const detalleId = input.dataset.detalleId;
                    erroresValidacion.push(`Item ${detalleId}: El Memorando/Comprobante es obligatorio cuando hay liquidación`);
                    hayErrores = true;
                    return;
                } else {
                    memorandoInput.classList.remove('is-invalid');
                }
            }
            
            // Validar que no exceda cantidad pendiente
            if (cantidad > cantidadPendiente) {
                input.classList.add('is-invalid');
                hayErrores = true;
                return;
            }
            
            // Omitir items sin liquidación
            if (cantidad <= 0) {
                return;
            }
            
            const item = {
                detalle_id: input.dataset.detalleId,
                cantidad_liquidacion: cantidad,
                memorando: memorandoInput.value.trim()
            };
            liquidaciones.push(item);
        });
        
        // Si hay errores, detener
        if (hayErrores) {
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar Liquidaciones';
            btn.disabled = false;
            if (erroresValidacion.length > 0) {
                alert('❌ Por favor completa los siguientes campos:\n\n' + erroresValidacion.join('\n'));
            } else {
                alert('⚠️ Error: Hay liquidaciones que exceden el saldo pendiente. Revisa los montos en rojo.');
            }
            return;
        }
        
        // Si no hay items para guardar
        if (liquidaciones.length === 0) {
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar Liquidaciones';
            btn.disabled = false;
            alert('⚠️ No hay liquidaciones para guardar. Ingresa al menos una cantidad mayor a 0.');
            return;
        }
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        const formData = new FormData();
        formData.append('liquidaciones', JSON.stringify(liquidaciones));
        
        const response = await fetch('index.php?action=api-certificate&action-api=save-liquidaciones', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            btn.innerHTML = '<i class="fas fa-check"></i> Guardado';
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-save"></i> Guardar Liquidaciones';
                btn.disabled = false;
                bootstrap.Modal.getInstance(document.getElementById('liquidacionModal')).hide();
                location.reload();
            }, 1500);
        } else {
            alert('Error: ' + result.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar Liquidaciones';
        }
    } catch (error) {
        console.error('Error en JavaScript:', error);
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar Liquidaciones';
    }
});

// Validar liquidación al cambiar valor
function validarLiquidacion(input) {
    const cantidad = parseFloat(input.value) || 0;
    const cantidadPendiente = parseFloat(input.dataset.cantidadPendiente) || 0;
    const errorElement = document.querySelector(`.validacion-error[data-detalle-id="${input.dataset.detalleId}"]`);
    const row = input.closest('tr');
    const memorandoInput = row.querySelector('.memorando-input');
    
    if (cantidad > cantidadPendiente) {
        input.classList.add('is-invalid');
        if (errorElement) {
            errorElement.classList.remove('d-none');
            errorElement.textContent = `Máximo: $ ${cantidadPendiente.toFixed(2)}`;
        }
    } else if (cantidad > 0) {
        // Si hay liquidación, validar que haya memorando
        input.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.classList.add('d-none');
        }
        validarMemorandoObligatorio(memorandoInput);
    } else {
        input.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.classList.add('d-none');
        }
        memorandoInput.classList.remove('is-invalid');
    }
}

// Validar que el memorando sea obligatorio cuando hay liquidación
function validarMemorandoObligatorio(memorandoInput) {
    const row = memorandoInput.closest('tr');
    const liquidacionInput = row.querySelector('.liquidacion-input');
    const cantidad = parseFloat(liquidacionInput.value) || 0;
    
    if (cantidad > 0 && !memorandoInput.value.trim()) {
        memorandoInput.classList.add('is-invalid');
    } else {
        memorandoInput.classList.remove('is-invalid');
    }
}

// Mostrar alerta mientras el usuario escribe
function mostrarAlerta(input) {
    const cantidad = parseFloat(input.value) || 0;
    const cantidadPendiente = parseFloat(input.dataset.cantidadPendiente) || 0;
    const errorElement = document.querySelector(`.validacion-error[data-detalle-id="${input.dataset.detalleId}"]`);
    const row = input.closest('tr');
    const memorandoInput = row.querySelector('.memorando-input');
    
    if (cantidad > cantidadPendiente) {
        input.classList.add('is-invalid');
        if (errorElement) {
            errorElement.classList.remove('d-none');
            errorElement.textContent = `⚠️ Máximo: $ ${cantidadPendiente.toFixed(2)}`;
        }
    } else if (cantidad > 0) {
        input.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.classList.add('d-none');
        }
        validarMemorandoObligatorio(memorandoInput);
    } else {
        input.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.classList.add('d-none');
        }
        memorandoInput.classList.remove('is-invalid');
    }
}

// Función para actualizar el saldo pendiente en tiempo real
function updatePendiente(inputElement) {
    const row = inputElement.closest('tr');
    const montoCell = row.cells[6]; // Celda de Monto
    const monto = parseFloat(montoCell.textContent.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0;
    const liquidacion = parseFloat(inputElement.value) || 0;
    const pendiente = Math.max(0, monto - liquidacion);
    
    // Actualizar la celda de saldo pendiente
    const pendienteCell = row.querySelector('.saldo-pendiente');
    pendienteCell.textContent = '$ ' + pendiente.toFixed(2).replace('.', ',');
    
    // Cambiar color según el estado
    if (pendiente === 0) {
        pendienteCell.classList.remove('text-warning');
        pendienteCell.classList.add('text-success');
    } else {
        pendienteCell.classList.remove('text-success');
        pendienteCell.classList.add('text-warning');
    }
}

// Modal para editar certificado
function openEditModal(certificateId) {
    console.log('🔄 Abriendo modal para certificado ID:', certificateId);
    
    const url = 'index.php?action=api-certificate&action-api=get-certificate-for-edit&id=' + certificateId;
    console.log('📡 Llamando a:', url);
    
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers.get('content-type'));
            return response.text();
        })
        .then(text => {
            console.log('📨 Raw response:', text);
            
            try {
                const data = JSON.parse(text);
                console.log('✓ JSON parseado:', data);
                
                if (data.success && data.data) {
                    const cert = data.data.certificate;
                    const items = data.data.items;
                    
                    console.log('📋 Certificado:', cert);
                    console.log('📦 Items:', items);
                    
                    // Llenar el formulario con los datos del certificado
                    document.getElementById('editCertId').value = cert.id;
                    document.getElementById('editNumeroCertificado').value = cert.numero_certificado;
                    document.getElementById('editFechaElaboracion').value = cert.fecha_elaboracion;
                    document.getElementById('editInstitucion').value = cert.institucion || '';
                    document.getElementById('editSeccionMemorandum').value = cert.seccion_memorando || '';
                    document.getElementById('editDescripcionGeneral').value = cert.descripcion || '';
                    document.getElementById('editUnidEjecutora').value = cert.unid_ejecutora || '';
                    document.getElementById('editUnidDesc').value = cert.unid_desc || '';
                    document.getElementById('editClaseRegistro').value = cert.clase_registro || '';
                    document.getElementById('editClaseGasto').value = cert.clase_gasto || '';
                    document.getElementById('editTipoDocRespaldo').value = cert.tipo_doc_respaldo || '';
                    document.getElementById('editClaseDocRespaldo').value = cert.clase_doc_respaldo || '';
                    
                    // Cargar los items en la tabla
                    loadEditModalItems(items || []);
                    
                    // Mostrar el modal
                    const editModal = new bootstrap.Modal(document.getElementById('editCertificateModal'));
                    editModal.show();
                    
                    console.log('✓ Modal abierto correctamente');
                } else {
                    alert('❌ Error: ' + (data.message || 'Error desconocido'));
                    console.error('API error:', data);
                }
            } catch (e) {
                console.error('❌ Error parsing JSON:', e);
                console.error('Response was:', text.substring(0, 500));
                alert('❌ Error al procesar la respuesta:\n' + text.substring(0, 200));
            }
        })
        .catch(error => {
            console.error('❌ Fetch error:', error);
            alert('❌ Error al cargar los datos del certificado: ' + error.message);
        });
}

function loadEditModalItems(items) {
    const tbody = document.getElementById('editItemsBody');
    
    if (items.length === 0) {
        tbody.innerHTML = '<tr class="text-center text-muted"><td colspan="11">No hay items agregados</td></tr>';
        return;
    }
    
    tbody.innerHTML = items.map((item, index) => `
        <tr>
            <td><small>${item.programa_codigo}</small></td>
            <td><small>${item.subprograma_codigo}</small></td>
            <td><small>${item.proyecto_codigo}</small></td>
            <td><small>${item.actividad_codigo}</small></td>
            <td><small>${item.item_codigo}</small></td>
            <td><small>${item.ubicacion_codigo}</small></td>
            <td><small>${item.fuente_codigo}</small></td>
            <td><small>${item.organismo_codigo}</small></td>
            <td><small>${item.naturaleza_codigo}</small></td>
            <td><small>${item.item_descripcion}</small></td>
            <td class="text-end">$ ${item.monto.toFixed(2)}</td>
        </tr>
    `).join('');
    
    // Actualizar total
    updateEditTotal(items);
}

function removeEditItem(index) {
    // Este es un placeholder - la lógica será más completa si es necesario
    alert('Eliminar item desde el modal');
}

function updateEditTotal(items) {
    const total = items.reduce((sum, item) => sum + item.monto, 0);
    document.getElementById('editTotalMonto').textContent = total.toFixed(2);
}

function saveEditCertificate() {
    const certId = document.getElementById('editCertId').value;
    
    if (!certId) {
        alert('Error: ID del certificado no válido');
        return;
    }
    
    const formData = new FormData(document.getElementById('editCertificateForm'));
    formData.append('id', certId);
    
    // Debug: mostrar datos que se envían
    console.log('=== SALVANDO CERTIFICADO ===');
    console.log('ID:', certId);
    console.log('Datos a enviar:', Object.fromEntries(formData));
    
    fetch('index.php?action=certificate-update', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', {
            'content-type': response.headers.get('content-type')
        });
        
        // Obtener el texto primero
        return response.text().then(text => {
            console.log('Raw response:', text);
            return { status: response.status, text: text };
        });
    })
    .then(({ status, text }) => {
        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            
            if (data.success) {
                alert('✓ Certificado actualizado correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + (data.message || 'Error desconocido'));
            }
        } catch (e) {
            console.error('Error parsing JSON:', e);
            console.error('Response was:', text);
            console.error('Status code:', status);
            
            // Mostrar los primeros 200 caracteres de la respuesta
            const preview = text.substring(0, 200);
            alert('❌ Error al procesar la respuesta:\n\n' + preview + '\n\nRevisa la consola para más detalles');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('❌ Error de red: ' + error.message);
    });
}
</script>

<!-- MODAL DE EDICIÓN -->
<div class="modal fade" id="editCertificateModal" tabindex="-1" aria-labelledby="editCertificateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #0B283F !important; color: white !important;">
                <h5 class="modal-title" id="editCertificateModalLabel" style="color: white !important;">
                    <i class="fas fa-edit"></i> Editar Certificado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCertificateForm">
                    <input type="hidden" id="editCertId">
                    
                    <!-- Datos Básicos -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="editNumeroCertificado" class="form-label small">Número de Certificado</label>
                            <input type="text" class="form-control form-control-sm" id="editNumeroCertificado" name="numero_certificado" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="editFechaElaboracion" class="form-label small">Fecha de Elaboración</label>
                            <input type="text" class="form-control form-control-sm" id="editFechaElaboracion" name="fecha_elaboracion" readonly>
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="editInstitucion" class="form-label small">Institución</label>
                            <input type="text" class="form-control form-control-sm" id="editInstitucion" name="institucion">
                        </div>
                        <div class="col-md-6">
                            <label for="editSeccionMemorandum" class="form-label small">Sección / Memorando</label>
                            <input type="text" class="form-control form-control-sm" id="editSeccionMemorandum" name="seccion_memorando">
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-12">
                            <label for="editDescripcionGeneral" class="form-label small">Descripción General</label>
                            <textarea class="form-control form-control-sm" id="editDescripcionGeneral" name="descripcion_general" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="editUnidEjecutora" class="form-label small">Unidad Ejecutora</label>
                            <input type="text" class="form-control form-control-sm" id="editUnidEjecutora" name="unid_ejecutora">
                        </div>
                        <div class="col-md-6">
                            <label for="editUnidDesc" class="form-label small">Descripción Unidad Ejecutora</label>
                            <input type="text" class="form-control form-control-sm" id="editUnidDesc" name="unid_desc">
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="editClaseRegistro" class="form-label small">Clase de Registro</label>
                            <input type="text" class="form-control form-control-sm" id="editClaseRegistro" name="clase_registro">
                        </div>
                        <div class="col-md-6">
                            <label for="editClaseGasto" class="form-label small">Clase de Gasto</label>
                            <input type="text" class="form-control form-control-sm" id="editClaseGasto" name="clase_gasto">
                        </div>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="editTipoDocRespaldo" class="form-label small">Tipo de Documento Respaldo</label>
                            <input type="text" class="form-control form-control-sm" id="editTipoDocRespaldo" name="tipo_doc_respaldo">
                        </div>
                        <div class="col-md-6">
                            <label for="editClaseDocRespaldo" class="form-label small">Clase de Documento Respaldo</label>
                            <input type="text" class="form-control form-control-sm" id="editClaseDocRespaldo" name="clase_doc_respaldo">
                        </div>
                    </div>
                    
                    <!-- Items -->
                    <div class="card mt-3">
                        <div class="card-header" style="background-color: #0B283F !important; color: white !important;">
                            <h6 class="mb-0" style="color: white !important;">Items del Certificado</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead style="background-color: #f8f9fa;">
                                    <tr>
                                        <th>PG</th>
                                        <th>SP</th>
                                        <th>PY</th>
                                        <th>ACT</th>
                                        <th>ITEM</th>
                                        <th>UBG</th>
                                        <th>FTE</th>
                                        <th>ORG</th>
                                        <th>N.Prest</th>
                                        <th>Descripción</th>
                                        <th>Monto</th>
                                    </tr>
                                </thead>
                                <tbody id="editItemsBody">
                                    <tr class="text-center text-muted">
                                        <td colspan="11">Cargando...</td>
                                    </tr>
                                </tbody>
                                <tfoot style="background-color: #f8f9fa; font-weight: bold;">
                                    <tr>
                                        <td colspan="10" class="text-end">TOTAL:</td>
                                        <td>$ <span id="editTotalMonto">0.00</span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="saveEditCertificate()">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>
