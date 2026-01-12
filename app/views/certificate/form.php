<?php
/**
 * Vista: Formulario de Certificados con Items Dinámicos
 * Versión: 2.1 - 28/11/2025
 */
$isEdit = isset($certificate) && $certificate;

// Verificar si hay presupuestos cargados para el año actual
$yearActual = $_SESSION['year'] ?? date('Y');
$db = Database::getInstance()->getConnection();
$stmtPresupuesto = $db->prepare("SELECT COUNT(*) as total FROM presupuesto_items WHERE year = ?");
$stmtPresupuesto->execute([$yearActual]);
$resultPresupuesto = $stmtPresupuesto->fetch();
$hayPresupuesto = $resultPresupuesto['total'] > 0;
?>

<div class="container-fluid py-4">
    <!-- VALIDACIÓN: No hay presupuestos -->
    <?php if (!$isEdit && !$hayPresupuesto): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="border-left: 5px solid #FFC107; border-radius: 4px;">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle me-3 mt-1" style="font-size: 1.2rem;"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-2">⚠️ Sin Presupuestos Cargados</h5>
                    <p class="mb-2">No se puede crear certificados porque no hay presupuestos cargados para el año <strong><?php echo $yearActual; ?></strong>.</p>
                    <p class="mb-0">
                        <a href="index.php?action=presupuesto-list" class="alert-link fw-bold">→ Ve a Presupuestos y carga el archivo de presupuestos</a> antes de crear certificados.
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>
    
    <!-- ALERTAS -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5"><?php echo $isEdit ? 'Editar Certificado' : 'Crear Certificado'; ?></h1>
            <p class="text-muted">Completa los datos del certificado y agrega los items</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="index.php?action=certificate-list" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <form method="POST" id="certificateForm" <?php echo !$isEdit && !$hayPresupuesto ? 'disabled' : ''; ?>>
        <?php if (!$isEdit && !$hayPresupuesto): ?>
            <!-- Desabilitar formulario con overlay -->
            <div style="position: relative; opacity: 0.6; pointer-events: none;">
        <?php endif; ?>
        <!-- ENCABEZADO DEL CERTIFICADO -->
        <div class="row mb-2">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
                        <h5 class="mb-0" style="color: white !important;">Datos del Certificado</h5>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label for="numero_certificado" class="form-label small">Número de Certificado</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm bg-light" id="numero_certificado" 
                                           name="numero" readonly style="cursor: not-allowed;" value="Cargando...">
                                    <span class="input-group-text input-group-text-sm">Automático</span>
                                </div>
                                <small class="text-muted d-block mt-1">Se asignará automáticamente al guardar</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="fecha_elaboracion" class="form-label small">Fecha de Elaboración</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control form-control-sm bg-light" id="fecha_elaboracion" name="date" 
                                           readonly style="cursor: not-allowed;">
                                    <span class="input-group-text input-group-text-sm">Automática</span>
                                </div>
                                <small class="text-muted d-block mt-1">Se asigna automáticamente con la fecha de hoy</small>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label for="institucion" class="form-label small">Institución</label>
                                <input type="text" class="form-control form-control-sm" id="institucion" name="name" 
                                       value="<?php echo htmlspecialchars($certificate['institucion'] ?? ''); ?>" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="seccion_memorando" class="form-label small">Sección / Memorando <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="seccion_memorando" name="seccion_memorando" 
                                       value="<?php echo htmlspecialchars($certificate['seccion_memorando'] ?? ''); ?>" 
                                       placeholder="Ej: PRESUPUESTO" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-12 mb-2">
                                <label for="descripcion_general" class="form-label small">Descripción General <span class="text-danger">*</span></label>
                                <textarea class="form-control form-control-sm" id="descripcion_general" name="descripcion_general" rows="2" required><?php 
                                    echo htmlspecialchars($certificate['descripcion'] ?? ''); 
                                ?></textarea>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label for="unid_ejecutora" class="form-label small">Unidad Ejecutora <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="unid_ejecutora" name="unid_ejecutora" 
                                       value="<?php echo htmlspecialchars($certificate['unid_ejecutora'] ?? ''); ?>"
                                       placeholder="Código de unidad ejecutora" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="unid_desc" class="form-label small">Descripción Unidad Ejecutora <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="unid_desc" name="unid_desc" 
                                       value="<?php echo htmlspecialchars($certificate['unid_desc'] ?? ''); ?>"
                                       placeholder="Descripción de la unidad" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label for="clase_registro" class="form-label small">Clase de Registro <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="clase_registro" name="clase_registro" 
                                       value="<?php echo htmlspecialchars($certificate['clase_registro'] ?? ''); ?>"
                                       placeholder="Ej: MODIFICATIVO" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="clase_gasto" class="form-label small">Clase de Gasto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="clase_gasto" name="clase_gasto" 
                                       value="<?php echo htmlspecialchars($certificate['clase_gasto'] ?? ''); ?>"
                                       placeholder="Ej: CORRIENTE" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label for="tipo_doc_respaldo" class="form-label small">Tipo de Documento Respaldo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="tipo_doc_respaldo" name="tipo_doc_respaldo" 
                                       value="<?php echo htmlspecialchars($certificate['tipo_doc_respaldo'] ?? ''); ?>"
                                       placeholder="Ej: FACTURA" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="clase_doc_respaldo" class="form-label small">Clase de Documento Respaldo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="clase_doc_respaldo" name="clase_doc_respaldo" 
                                       value="<?php echo htmlspecialchars($certificate['clase_doc_respaldo'] ?? ''); ?>"
                                       placeholder="Ej: DOCUMENTO" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body p-2">
                        <h6 class="fw-bold mb-2 small">Información</h6>
                        <p class="text-muted small mb-0">
                            Los items se agregan dinámicamente usando los selects en cascada. El monto total se calcula automáticamente.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- SELECCIÓN DE ITEM -->
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
                <h5 class="mb-0" style="color: white !important;">Agregar Items al Certificado</h5>
            </div>
            <div class="card-body p-2">
                <div class="row g-2">
                    <div class="col-md-2 mb-3">
                        <label for="programa_select" class="form-label">Programa</label>
                        <select class="form-select form-select-sm" id="programa_select">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($programas as $prog): ?>
                                <option value="<?php echo $prog['codigo']; ?>" data-codigo="<?php echo $prog['codigo']; ?>">
                                    <?php echo htmlspecialchars($prog['codigo'] . ' - ' . substr($prog['descripcion'], 0, 30)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="subprograma_select" class="form-label">Subprograma</label>
                        <select class="form-select form-select-sm" id="subprograma_select" disabled>
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="proyecto_select" class="form-label">Proyecto</label>
                        <select class="form-select form-select-sm" id="proyecto_select" disabled>
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="actividad_select" class="form-label">Actividad</label>
                        <select class="form-select form-select-sm" id="actividad_select" disabled>
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="fuente_select" class="form-label small">Fuente</label>
                        <select class="form-select form-select-sm" id="fuente_select" disabled>
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="ubicacion_select" class="form-label small">Ubicación</label>
                        <select class="form-select form-select-sm" id="ubicacion_select" disabled>
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-2 mb-3">
                        <label for="item_select" class="form-label">Item</label>
                        <select class="form-select form-select-sm" id="item_select" disabled>
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="organismo_select" class="form-label small">Organismo</label>
                        <select class="form-select form-select-sm" id="organismo_select">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($organismos as $org): ?>
                                <option value="<?php echo $org['id']; ?>">
                                    <?php echo htmlspecialchars($org['codigo'] . ' - ' . $org['descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="naturaleza_select" class="form-label small">Naturaleza</label>
                        <select class="form-select form-select-sm" id="naturaleza_select">
                            <option value="">-- Seleccionar --</option>
                            <?php foreach ($naturalezas as $nat): ?>
                                <option value="<?php echo $nat['id']; ?>">
                                    <?php echo htmlspecialchars($nat['codigo'] . ' - ' . $nat['descripcion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label for="monto_item" class="form-label small">Monto</label>
                        <input type="number" class="form-control form-control-sm" id="monto_item" 
                               placeholder="0.00" step="0.01" min="0">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-success btn-sm w-100" id="addItemBtn">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE ITEMS -->
        <div class="card shadow-sm border-0 mb-2">
            <div class="card-header" style="background-color: #0B283F !important; background: #0B283F !important; color: white !important;">
                <h5 class="mb-0" style="color: white !important;">Items del Certificado</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 table-sm" id="itemsTable">
                    <thead style="background-color: #0B283F !important; color: white !important;">
                        <tr>
                            <th style="width: 8%; font-size: 12px;">#PG</th>
                            <th style="width: 8%; font-size: 12px;">SP</th>
                            <th style="width: 8%; font-size: 12px;">PY</th>
                            <th style="width: 8%; font-size: 12px;">ACT</th>
                            <th style="width: 8%; font-size: 12px;">ITEM</th>
                            <th style="width: 8%; font-size: 12px;">UBG</th>
                            <th style="width: 8%; font-size: 12px;">FTE</th>
                            <th style="width: 8%; font-size: 12px;">ORG</th>
                            <th style="width: 8%; font-size: 12px;">N.Prest</th>
                            <th style="width: 14%; font-size: 12px;">Descripción</th>
                            <th style="width: 10%; font-size: 12px;">Monto</th>
                            <th style="width: 5%; font-size: 12px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        <tr class="text-center text-muted">
                            <td colspan="12">No hay items agregados</td>
                        </tr>
                    </tbody>
                    <tfoot style="background-color: #0B283F !important; color: white !important; font-weight: bold;">
                        <tr>
                            <td colspan="10" class="text-end">TOTAL PRESUPUESTARIO:</td>
                            <td colspan="2">$ <span id="totalMonto">0.00</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- BOTONES DE ACCIÓN -->
        <div class="d-flex gap-2 mb-2">
            <button type="submit" class="btn btn-primary btn-sm" id="submitBtn" <?php echo !$isEdit && !$hayPresupuesto ? 'disabled' : ''; ?>>
                <i class="fas fa-save"></i> <?php echo $isEdit ? 'Actualizar' : 'Guardar'; ?> Certificado
            </button>
            <a href="index.php?action=certificate-list" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>

        <!-- Campo oculto para guardar items como JSON -->
        <input type="hidden" id="itemsData" name="items_data" value="[]">
        <!-- Campo oculto para el año del certificado -->
        <input type="hidden" id="yearField" name="year" value="<?php echo htmlspecialchars($yearActual); ?>">
        <?php if (!$isEdit && !$hayPresupuesto): ?>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- INPUT OCULTO PARA GUARDAR DATOS -->
<script>
// Función para limitar decimales a 2 digitos en campos numéricos
function limitarDecimales(event) {
    const input = event.target;
    const value = input.value;
    
    // Si el valor contiene más de 2 decimales, truncar
    if (value.includes('.')) {
        const parts = value.split('.');
        if (parts[1] && parts[1].length > 2) {
            input.value = parts[0] + '.' + parts[1].substring(0, 2);
        }
    }
}

document.addEventListener('DOMContentLoaded', async function() {
    const API_ENDPOINT = 'index.php?action=api-certificate';
    let items = [];
    
    // Aplicar limitación de decimales al campo de monto
    const montoItemInput = document.getElementById('monto_item');
    if (montoItemInput) {
        montoItemInput.addEventListener('keyup', limitarDecimales);
        montoItemInput.addEventListener('blur', limitarDecimales);
    }

    // Cargar items existentes si estamos en modo edición
    <?php if ($isEdit && !empty($itemsJson)): ?>
    try {
        const existingItems = <?php echo $itemsJson; ?>;
        if (Array.isArray(existingItems) && existingItems.length > 0) {
            items = existingItems;
            console.log('✓ Items cargados desde certificado existente:', items);
        }
    } catch (error) {
        console.error('Error cargando items existentes:', error);
    }
    <?php endif; ?>

    // DESHABILITAR botón al iniciar (sin items)
    document.getElementById('submitBtn').disabled = items.length === 0;

    // Cargar el próximo número de certificado
    async function loadNextCertificateNumber() {
        try {
            let url = 'index.php?action=api-certificate&action-api=get-next-certificate-number';
            console.log('Cargando número de certificado desde:', url);
            const response = await fetch(url);
            const text = await response.text();
            console.log('Respuesta bruta:', text);
            const data = JSON.parse(text);
            console.log('Datos parsedos:', data);
            if (data.success && data.data) {
                console.log('Número de certificado:', data.data.numero_certificado);
                document.getElementById('numero_certificado').value = data.data.numero_certificado;
            } else {
                console.warn('Respuesta sin éxito:', data);
                // Si falla, generar uno por defecto
                const defaultNum = 'CERT-' + String(new Date().getTime()).slice(-3);
                document.getElementById('numero_certificado').value = defaultNum;
            }
        } catch (error) {
            console.error('Error al cargar número de certificado:', error);
            // Generar número por defecto si falla
            const defaultNum = 'CERT-001';
            document.getElementById('numero_certificado').value = defaultNum;
        }
    }

    // Cargar número al iniciar
    await loadNextCertificateNumber();

    // Establecer fecha de hoy automáticamente
    function setTodayDate() {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        const dateString = `${day}/${month}/${year}`;
        document.getElementById('fecha_elaboracion').value = dateString;
    }

    // Establecer fecha al iniciar
    setTodayDate();

    // Cargar códigos del presupuesto importado
    async function loadPresupuestoCodigos() {
        try {
            const response = await fetch('index.php?action=api-certificate&action-api=get-presupuesto-codigos');
            const data = await response.json();
            
            if (data.success && data.data) {
                console.log('Presupuesto cargado:', data.data.length, 'registros');
                // Los datos se cargarán dinámicamente con los selects en cascada
            }
        } catch (error) {
            console.error('Error al cargar códigos del presupuesto:', error);
        }
    }

    // Cargar códigos al iniciar (sin llenar selects aún)
    await loadPresupuestoCodigos();
    
    async function loadData(apiAction, params) {
        try {
            let url = 'index.php?action=api-certificate&action-api=' + apiAction;
            Object.keys(params).forEach(key => {
                url += '&' + key + '=' + encodeURIComponent(params[key]);
            });
            
            console.log('Llamando a:', url); // Debug
            const response = await fetch(url);
            const data = await response.json();
            console.log('Respuesta:', data); // Debug
            return data.success ? data.data : [];
        } catch (error) {
            console.error('Error en AJAX:', apiAction, error);
            return [];
        }
    }

    // EVENTO: Cambio en Programa
    document.getElementById('programa_select').addEventListener('change', async function() {
        const codPrograma = this.value;
        const subprogramaSelect = document.getElementById('subprograma_select');
        subprogramaSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        subprogramaSelect.disabled = true;
        document.getElementById('proyecto_select').innerHTML = '<option value="">-- Seleccionar --</option>';
        document.getElementById('proyecto_select').disabled = true;
        document.getElementById('actividad_select').innerHTML = '<option value="">-- Seleccionar --</option>';
        document.getElementById('actividad_select').disabled = true;
        document.getElementById('item_select').innerHTML = '<option value="">-- Seleccionar --</option>';
        document.getElementById('item_select').disabled = true;
        if (!codPrograma) return;
        const subprogramas = await loadData('get-subprogramas', { cod_programa: codPrograma });
        subprogramas.forEach(sp => {
            const option = document.createElement('option');
            option.value = sp.codigo;
            option.textContent = sp.codigo + ' - ' + sp.descripcion;
            subprogramaSelect.appendChild(option);
        });
        subprogramaSelect.disabled = false;
    });

    // EVENTO: Cambio en Subprograma
    document.getElementById('subprograma_select').addEventListener('change', async function() {
        const codPrograma = document.getElementById('programa_select').value;
        const codSubprograma = this.value;
        const proyectoSelect = document.getElementById('proyecto_select');
        proyectoSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        proyectoSelect.disabled = true;
        document.getElementById('actividad_select').innerHTML = '<option value="">-- Seleccionar --</option>';
        document.getElementById('actividad_select').disabled = true;
        document.getElementById('item_select').innerHTML = '<option value="">-- Seleccionar --</option>';
        document.getElementById('item_select').disabled = true;
        if (!codSubprograma) return;
        const proyectos = await loadData('get-proyectos', { cod_programa: codPrograma, cod_subprograma: codSubprograma });
        proyectos.forEach(py => {
            const option = document.createElement('option');
            option.value = py.codigo;
            option.textContent = py.codigo + ' - ' + py.descripcion;
            proyectoSelect.appendChild(option);
        });
        proyectoSelect.disabled = false;
    });

    // EVENTO: Cambio en Proyecto
    document.getElementById('proyecto_select').addEventListener('change', async function() {
        const codPrograma = document.getElementById('programa_select').value;
        const codSubprograma = document.getElementById('subprograma_select').value;
        const codProyecto = this.value;
        const actividadSelect = document.getElementById('actividad_select');
        actividadSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        actividadSelect.disabled = true;
        document.getElementById('item_select').innerHTML = '<option value="">-- Seleccionar --</option>';
        document.getElementById('item_select').disabled = true;
        if (!codProyecto) return;
        const actividades = await loadData('get-actividades', { cod_programa: codPrograma, cod_subprograma: codSubprograma, cod_proyecto: codProyecto });
        actividades.forEach(act => {
            const option = document.createElement('option');
            option.value = act.codigo;
            option.textContent = act.codigo + ' - ' + act.descripcion;
            actividadSelect.appendChild(option);
        });
        actividadSelect.disabled = false;
    });

    // EVENTO: Cambio en Actividad
    document.getElementById('actividad_select').addEventListener('change', async function() {
        const codPrograma = document.getElementById('programa_select').value;
        const codSubprograma = document.getElementById('subprograma_select').value;
        const codProyecto = document.getElementById('proyecto_select').value;
        const codActividad = this.value;
        const itemSelect = document.getElementById('item_select');
        const fuenteSelect = document.getElementById('fuente_select');
        const ubicacionSelect = document.getElementById('ubicacion_select');
        
        itemSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        itemSelect.disabled = true;
        fuenteSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        fuenteSelect.disabled = true;
        ubicacionSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        ubicacionSelect.disabled = true;
        
        if (!codActividad) return;
        
        // Cargar Fuentes
        const fuentes = await loadData('get-fuentes', { cod_programa: codPrograma, cod_subprograma: codSubprograma, cod_proyecto: codProyecto, cod_actividad: codActividad });
        fuentes.forEach(fuente => {
            const option = document.createElement('option');
            option.value = fuente.codigo;
            option.textContent = fuente.codigo + ' - ' + fuente.descripcion;
            fuenteSelect.appendChild(option);
        });
        fuenteSelect.disabled = false;
    });

    // EVENTO: Cambio en Ubicación
    document.getElementById('ubicacion_select').addEventListener('change', async function() {
        const codPrograma = document.getElementById('programa_select').value;
        const codSubprograma = document.getElementById('subprograma_select').value;
        const codProyecto = document.getElementById('proyecto_select').value;
        const codActividad = document.getElementById('actividad_select').value;
        const codFuente = document.getElementById('fuente_select').value;
        const codUbicacion = this.value;
        const itemSelect = document.getElementById('item_select');
        
        itemSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        itemSelect.disabled = true;
        
        if (!codUbicacion) return;
        
        const items = await loadData('get-items-by-actividad', { cod_programa: codPrograma, cod_subprograma: codSubprograma, cod_proyecto: codProyecto, cod_actividad: codActividad, cod_fuente: codFuente, cod_ubicacion: codUbicacion });
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item.codigo;
            option.textContent = item.codigo + ' - ' + item.descripcion;
            itemSelect.appendChild(option);
        });
        itemSelect.disabled = false;
    });

    // EVENTO: Cambio en Fuente
    document.getElementById('fuente_select').addEventListener('change', async function() {
        const codPrograma = document.getElementById('programa_select').value;
        const codSubprograma = document.getElementById('subprograma_select').value;
        const codProyecto = document.getElementById('proyecto_select').value;
        const codActividad = document.getElementById('actividad_select').value;
        const codFuente = this.value;
        const ubicacionSelect = document.getElementById('ubicacion_select');
        
        ubicacionSelect.innerHTML = '<option value="">-- Seleccionar --</option>';
        ubicacionSelect.disabled = true;
        
        if (!codFuente) return;
        
        const ubicaciones = await loadData('get-ubicaciones', { cod_programa: codPrograma, cod_subprograma: codSubprograma, cod_proyecto: codProyecto, cod_actividad: codActividad, cod_fuente: codFuente });
        ubicaciones.forEach(ubicacion => {
            const option = document.createElement('option');
            option.value = ubicacion.codigo;
            option.textContent = ubicacion.codigo + ' - ' + ubicacion.descripcion;
            ubicacionSelect.appendChild(option);
        });
        ubicacionSelect.disabled = false;
    });

    // EVENTO: Agregar Item
    document.getElementById('addItemBtn').addEventListener('click', async function() {
        const codPrograma = document.getElementById('programa_select').value;
        const codSubprograma = document.getElementById('subprograma_select').value;
        const codProyecto = document.getElementById('proyecto_select').value;
        const codActividad = document.getElementById('actividad_select').value;
        const codFuente = document.getElementById('fuente_select').value;
        const codUbicacion = document.getElementById('ubicacion_select').value;
        const itemCodigo = document.getElementById('item_select').value;
        const itemText = document.getElementById('item_select').selectedOptions[0]?.text;
        const itemDescripcion = itemText ? itemText.split(' - ')[1] : '';
        const monto = parseFloat(document.getElementById('monto_item').value) || 0;
        const organismoId = document.getElementById('organismo_select').value;
        const organismoText = document.getElementById('organismo_select').selectedOptions[0]?.text || '';
        const naturalezaId = document.getElementById('naturaleza_select').value;
        const naturalezaText = document.getElementById('naturaleza_select').selectedOptions[0]?.text || '';

        if (!itemCodigo || monto <= 0) {
            alert('Selecciona un item y especifica un monto válido');
            return;
        }

        // VALIDACIÓN: Obtener saldo disponible y validar
        try {
            let urlMonto = 'index.php?action=api-certificate&action-api=get-monto-codicado';
            urlMonto += '&cod_programa=' + encodeURIComponent(codPrograma);
            urlMonto += '&cod_subprograma=' + encodeURIComponent(codSubprograma);
            urlMonto += '&cod_proyecto=' + encodeURIComponent(codProyecto);
            urlMonto += '&cod_actividad=' + encodeURIComponent(codActividad);
            urlMonto += '&cod_fuente=' + encodeURIComponent(codFuente);
            urlMonto += '&cod_ubicacion=' + encodeURIComponent(codUbicacion);
            urlMonto += '&cod_item=' + encodeURIComponent(itemCodigo);
            urlMonto += '&year=' + encodeURIComponent(document.querySelector('input[name="year"]')?.value || new Date().getFullYear());
            
            const responseMontoData = await fetch(urlMonto);
            const dataMonto = await responseMontoData.json();
            
            if (dataMonto.success && dataMonto.data) {
                const saldoDisponible = dataMonto.data.saldo_disponible;
                console.log('Validación de monto:', { monto, saldoDisponible });
                
                // Si el saldo disponible es 0, el item NO existe en el presupuesto o no hay saldo
                if (saldoDisponible === 0) {
                    const year = document.querySelector('input[name="year"]').value;
                    alert('❌ ITEM SIN SALDO DISPONIBLE\n\nEste item no tiene saldo disponible en el presupuesto del año ' + year + '.\n\nPor favor, verifica:\n' +
                        '✓ Que el código del item sea correcto\n' +
                        '✓ Que el presupuesto esté cargado para ' + year + '\n' +
                        '✓ Que el item tenga saldo disponible');
                    console.error('Item sin saldo disponible:', { codPrograma, codActividad, codFuente, codUbicacion, itemCodigo });
                    return;
                }
                
                // Si el monto ingresado EXCEDE el saldo disponible, mostrar alerta
                if (monto > saldoDisponible) {
                    alert('❌ MONTO EXCEDE SALDO DISPONIBLE\n\nEl monto que ingresaste ($' + monto.toFixed(2) + ')\nexcede el saldo disponible ($' + saldoDisponible.toFixed(2) + ')\n\nDebes ingresar un monto igual o menor al saldo disponible.');
                    console.error('Monto invalido:', monto, 'Saldo Disponible:', saldoDisponible);
                    return;
                }
                
                // Si es igual o menor, está bien
                if (monto === saldoDisponible) {
                    console.log('✓ Monto igual al saldo disponible');
                } else {
                    console.log('✓ Monto menor al saldo disponible');
                }
            } else {
                console.warn('No se pudo obtener saldo disponible:', dataMonto);
                // Continuar sin validación si la API falla
            }
        } catch (error) {
            console.error('Error al validar saldo disponible:', error);
            // Continuar sin validación si hay error en AJAX
        }

        // Extraer códigos de los textos de los selects (formato: "CODIGO - DESCRIPCION")
        const organismoCodego = organismoText.split(' - ')[0] || '';
        const naturalezaCodigo = naturalezaText.split(' - ')[0] || '';

        const itemData = {
            id: Date.now(),
            item_id: itemCodigo,
            programa_id: 0,
            subprograma_id: 0,
            proyecto_id: 0,
            actividad_id: 0,
            programa_codigo: String(codPrograma),
            subprograma_codigo: String(codSubprograma),
            proyecto_codigo: String(codProyecto),
            actividad_codigo: String(codActividad),
            item_codigo: String(itemCodigo),
            item_descripcion: String(itemDescripcion),
            ubicacion_id: 0,
            ubicacion_codigo: String(codUbicacion),
            fuente_id: 0,
            fuente_codigo: String(codFuente),
            organismo_id: organismoId,
            organismo_codigo: String(organismoCodego),
            naturaleza_id: naturalezaId,
            naturaleza_codigo: String(naturalezaCodigo),
            monto: monto
        };

        items.push(itemData);
        renderItems();
        updateTotal();
        clearForm();
        document.getElementById('submitBtn').disabled = false;
    });

    // Renderizar tabla de items
    function renderItems() {
        const tbody = document.getElementById('itemsBody');
        
        if (items.length === 0) {
            tbody.innerHTML = '<tr class="text-center text-muted"><td colspan="12">No hay items agregados</td></tr>';
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
                <td>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Eliminar item
    window.removeItem = function(index) {
        items.splice(index, 1);
        renderItems();
        updateTotal();
        if (items.length === 0) {
            document.getElementById('submitBtn').disabled = true;
        }
    };

    // Actualizar total
    function updateTotal() {
        const total = items.reduce((sum, item) => sum + item.monto, 0);
        document.getElementById('totalMonto').textContent = total.toFixed(2);
    }

    // Limpiar formulario
    function clearForm() {
        // Limpiar cascada de selects
        document.getElementById('programa_select').value = '';
        document.getElementById('subprograma_select').value = '';
        document.getElementById('subprograma_select').disabled = true;
        document.getElementById('proyecto_select').value = '';
        document.getElementById('proyecto_select').disabled = true;
        document.getElementById('actividad_select').value = '';
        document.getElementById('actividad_select').disabled = true;
        document.getElementById('item_select').value = '';
        document.getElementById('item_select').disabled = true;
        
        // Limpiar monto
        document.getElementById('monto_item').value = '';
        
        // Limpiar otros selects
        document.getElementById('ubicacion_select').value = '';
        document.getElementById('fuente_select').value = '';
        document.getElementById('organismo_select').value = '';
        document.getElementById('naturaleza_select').value = '';
    }

    // EVENTO: Enviar formulario
    document.getElementById('certificateForm').addEventListener('submit', function(e) {
        console.log('📋 Items actuales:', items);
        console.log('📊 Count items:', items.length);
        
        // VALIDACIÓN: Verificar que haya al menos 1 item
        if (!items || items.length === 0) {
            e.preventDefault();
            alert('❌ Debes agregar al menos 1 item al certificado antes de guardar');
            return false;
        }
        
        // Preparar los datos de items
        const itemsJson = JSON.stringify(items);
        console.log('📤 JSON a enviar:', itemsJson);
        document.getElementById('itemsData').value = itemsJson;
        
        console.log('✅ Permitiendo envío de formulario...');
        // NO llamamos a e.preventDefault() - dejamos que se envíe normalmente
    });
    
    // Renderizar items si se cargaron desde edición
    if (items.length > 0) {
        renderItems();
        updateTotal();
    }
});
</script>

<style>
    .form-select-sm {
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
    }
    
    .form-control-sm {
        font-size: 0.875rem;
    }

    #itemsTable {
        font-size: 0.85rem;
    }

    #itemsTable th, #itemsTable td {
        padding: 0.5rem;
        vertical-align: middle;
    }
</style>
