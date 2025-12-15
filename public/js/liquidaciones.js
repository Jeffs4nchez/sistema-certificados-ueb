/**
 * EJEMPLOS DE USO DE LIQUIDACIONES DESDE JAVASCRIPT
 * 
 * Guarda este archivo en: public/js/liquidaciones.js
 */

// ============================================
// 1️⃣ CREAR UNA NUEVA LIQUIDACIÓN
// ============================================

async function crearLiquidacion(detalleId, cantidad, descripcion = '') {
    const response = await fetch('/api/liquidaciones', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            detalle_certificado_id: detalleId,
            cantidad_liquidacion: cantidad,
            descripcion: descripcion,
            usuario: 'usuario_actual'
        })
    });
    
    const resultado = await response.json();
    
    if (resultado.exito) {
        console.log('✅ Liquidación creada:', resultado.id);
        return resultado;
    } else {
        console.error('❌ Error:', resultado.error);
        alert('Error: ' + resultado.error);
    }
}

// Ejemplo de uso:
// crearLiquidacion(5, 1000, 'Primera liquidación de octubre');


// ============================================
// 2️⃣ VER TODAS LAS LIQUIDACIONES DE UN DETALLE
// ============================================

async function obtenerLiquidacionesDetalle(detalleId) {
    const response = await fetch(`/api/detalles/${detalleId}/liquidaciones`);
    const resultado = await response.json();
    
    if (resultado.exito) {
        console.log('Liquidaciones encontradas:', resultado.total);
        console.log('Total liquidado:', resultado.resumen.total_liquidado);
        console.log('Pendiente:', resultado.resumen.cantidad_pendiente);
        console.log('Historial:', resultado.liquidaciones);
        
        // Mostrar en la página
        mostrarLiquidacionesEnTabla(resultado.liquidaciones);
        
        return resultado;
    }
}

// Ejemplo de uso:
// obtenerLiquidacionesDetalle(5);


// ============================================
// 3️⃣ ACTUALIZAR UNA LIQUIDACIÓN
// ============================================

async function actualizarLiquidacion(liquidacionId, nuevaCantidad, nuevaDescripcion = '') {
    const response = await fetch(`/api/liquidaciones/${liquidacionId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cantidad_liquidacion: nuevaCantidad,
            descripcion: nuevaDescripcion
        })
    });
    
    const resultado = await response.json();
    
    if (resultado.exito) {
        console.log('✅ Liquidación actualizada');
        return resultado;
    } else {
        alert('Error: ' + resultado.error);
    }
}

// Ejemplo de uso:
// actualizarLiquidacion(3, 1500, 'Corregida a noviembre');


// ============================================
// 4️⃣ ELIMINAR UNA LIQUIDACIÓN
// ============================================

async function eliminarLiquidacion(liquidacionId) {
    if (!confirm('¿Está seguro de eliminar esta liquidación?')) {
        return;
    }
    
    const response = await fetch(`/api/liquidaciones/${liquidacionId}`, {
        method: 'DELETE'
    });
    
    const resultado = await response.json();
    
    if (resultado.exito) {
        console.log('✅ Liquidación eliminada');
        return resultado;
    } else {
        alert('Error: ' + resultado.error);
    }
}

// Ejemplo de uso:
// eliminarLiquidacion(3);


// ============================================
// 5️⃣ VER EL HISTORIAL DE CAMBIOS (AUDITORÍA)
// ============================================

async function obtenerAuditoria(liquidacionId) {
    const response = await fetch(`/api/liquidaciones/${liquidacionId}/auditoria`);
    const resultado = await response.json();
    
    if (resultado.exito) {
        console.log('Cambios realizados:', resultado.cambios);
        // cambios es un array con {accion, cantidad_anterior, cantidad_nueva, usuario, fecha_cambio}
        
        mostrarAuditoriaEnTabla(resultado.cambios);
    }
}

// Ejemplo de uso:
// obtenerAuditoria(3);


// ============================================
// 6️⃣ FUNCIÓN HELPER PARA MOSTRAR EN TABLA
// ============================================

function mostrarLiquidacionesEnTabla(liquidaciones) {
    let html = `
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cantidad</th>
                <th>Descripción</th>
                <th>Usuario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    liquidaciones.forEach(liq => {
        html += `
            <tr>
                <td>${liq.id}</td>
                <td>${liq.fecha_liquidacion}</td>
                <td>$${parseFloat(liq.cantidad_liquidacion).toLocaleString('es-ES', {minimumFractionDigits: 2})}</td>
                <td>${liq.descripcion || '-'}</td>
                <td>${liq.usuario_creacion}</td>
                <td>
                    <button onclick="editarLiquidacion(${liq.id})" class="btn btn-sm btn-primary">
                        ✏️ Editar
                    </button>
                    <button onclick="eliminarLiquidacion(${liq.id})" class="btn btn-sm btn-danger">
                        🗑️ Eliminar
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
        </tbody>
    </table>
    `;
    
    // Insertarlo en el HTML
    document.getElementById('tabla-liquidaciones').innerHTML = html;
}

function mostrarAuditoriaEnTabla(cambios) {
    let html = `
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Acción</th>
                <th>Monto Anterior</th>
                <th>Monto Nuevo</th>
                <th>Usuario</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
    `;
    
    cambios.forEach(cambio => {
        let monto_ant = cambio.cantidad_anterior ? `$${parseFloat(cambio.cantidad_anterior).toFixed(2)}` : '-';
        let monto_nuevo = cambio.cantidad_nueva ? `$${parseFloat(cambio.cantidad_nueva).toFixed(2)}` : '-';
        
        html += `
            <tr>
                <td><strong>${cambio.accion}</strong></td>
                <td>${monto_ant}</td>
                <td>${monto_nuevo}</td>
                <td>${cambio.usuario}</td>
                <td>${new Date(cambio.fecha_cambio).toLocaleString('es-ES')}</td>
            </tr>
        `;
    });
    
    html += `</tbody></table>`;
    
    document.getElementById('auditoria-liquidaciones').innerHTML = html;
}

// ============================================
// 7️⃣ FORMULARIO PARA CREAR LIQUIDACIÓN RÁPIDO
// ============================================

function mostrarFormularioLiquidacion(detalleId, montoDisponible) {
    const html = `
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>Nueva Liquidación</h5>
        </div>
        <div class="card-body">
            <p>Disponible para liquidar: <strong>$${parseFloat(montoDisponible).toFixed(2)}</strong></p>
            <form onsubmit="submitLiquidacion(event, ${detalleId})">
                <div class="mb-3">
                    <label>Cantidad a liquidar:</label>
                    <input type="number" step="0.01" max="${montoDisponible}" 
                           id="cantidad_liq" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Descripción (opcional):</label>
                    <textarea id="descripcion_liq" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-success">💾 Guardar Liquidación</button>
            </form>
        </div>
    </div>
    `;
    
    document.getElementById('form-liquidacion').innerHTML = html;
}

async function submitLiquidacion(event, detalleId) {
    event.preventDefault();
    
    const cantidad = document.getElementById('cantidad_liq').value;
    const descripcion = document.getElementById('descripcion_liq').value;
    
    const resultado = await crearLiquidacion(detalleId, cantidad, descripcion);
    
    if (resultado.exito) {
        // Limpiar formulario
        document.getElementById('cantidad_liq').value = '';
        document.getElementById('descripcion_liq').value = '';
        
        // Recargar la tabla de liquidaciones
        obtenerLiquidacionesDetalle(detalleId);
    }
}
