<?php
/**
 * Vista: Reporte de Certificado Presupuestario (Formato Oficial A4)
 */
require_once __DIR__ . '/../../helpers/MontoHelper.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Certificado Presupuestario - <?php echo htmlspecialchars($certificate['numero_certificado']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            padding: 0;
            margin: 0;
            overflow-x: hidden;
        }
        
        .page {
            background: white;
            width: 210mm;
            height: 297mm;
            margin: 10px auto;
            padding: 8mm;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
            font-size: 10px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }
        }
        
        .print-btn {
            padding: 12px 24px;
            background: linear-gradient(135deg, #0B283F 0%, #0B3F3C 100%);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(11, 40, 63, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .print-btn:hover {
            background: linear-gradient(135deg, #0B3F3C 0%, #0B283F 100%);
            box-shadow: 0 8px 25px rgba(11, 40, 63, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .print-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(11, 40, 63, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .print-btn i {
            font-size: 16px;
        }
            box-shadow: 0 4px 12px rgba(11, 40, 63, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .print-btn i {
            font-size: 16px;
        }
        
        /* ===== HEADER ===== */
        .header-main {
            border: 2px solid #000;
            text-align: center;
            padding: 3px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 0;
            letter-spacing: 0.5px;
        }
        
        /* ===== SECCION 1: INFO BASICA (3 FILAS) ===== */
        .info-section {
            border: 1px solid #000;
            border-top: none;
            display: flex;
            flex-direction: column;
            margin-bottom: 0;
        }
        
        /* FILA 1: Institución | No. Certificación | Fecha */
        .info-row-1 {
            display: grid;
            grid-template-columns: 2.5fr 1fr 1fr;
            border-bottom: 1px solid #000;
            gap: 0;
        }
        
        .info-row-1-col-large {
            border-right: 1px solid #000;
            padding: 3px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 35px;
        }
        
        .info-row-1-col-small {
            border-right: 1px solid #000;
            padding: 3px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 35px;
        }
        
        .info-row-1-col-small:last-child {
            border-right: none;
        }
        
        .info-row-1-label {
            font-weight: bold;
            font-size: 8px;
        }
        
        .info-row-1-value {
            border-bottom: 1px solid #000;
            flex: 1;
            padding: 1px 0;
            font-size: 9px;
            display: flex;
            align-items: center;
        }
        
        /* FILA 2: Unidad Ejecutora + Unidades */
        .info-row-2 {
            display: grid;
            grid-template-columns: 1fr 1.5fr 1fr;
            border-bottom: 1px solid #000;
            gap: 0;
        }
        
        .info-row-2-col {
            border-right: 1px solid #000;
            padding: 3px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 28px;
        }
        
        .info-row-2-col:last-child {
            border-right: none;
        }
        
        .info-row-2-col-fill {
            border-right: 1px solid #000;
            padding: 3px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 28px;
        }
        
        .info-row-2-label {
            font-weight: bold;
            font-size: 9px;
        }
        
        .info-row-2-value {
            border-bottom: 1px solid #000;
            flex: 1;
            padding: 2px 0;
            font-size: 10px;
        }
        
        .info-row-2-value-only {
            padding: 2px 0;
            font-size: 10px;
            display: flex;
            align-items: center;
        }
        
        /* FILA 3: Unidad Desc + empty cells */
        .info-row-3 {
            display: grid;
            grid-template-columns: 2.5fr 1fr 1fr;
            gap: 0;
        }
        
        .info-row-3-col-large {
            border-right: 1px solid #000;
            padding: 3px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 28px;
        }
        
        .info-row-3-col-small {
            border-right: 1px solid #000;
            padding: 3px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 28px;
        }
        
        .info-row-3-col-small:last-child {
            border-right: none;
        }
        
        .info-row-3-label {
            font-weight: bold;
            font-size: 9px;
        }
        
        .info-row-3-value {
            border-bottom: 1px solid #000;
            flex: 1;
            padding: 2px 0;
            font-size: 10px;
        }
        
        /* ===== SECCION 2: DOCUMENTOS ===== */
        .doc-section {
            border: 1px solid #000;
            border-bottom: none;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            margin-bottom: 0;
        }
        
        .doc-col {
            border-right: 1px solid #000;
            padding: 4px;
            display: flex;
            flex-direction: column;
            min-height: 38px;
        }
        
        .doc-col:last-child {
            border-right: none;
        }
        
        .doc-label {
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 2px;
        }
        
        .doc-value {
            padding: 2px 0;
            font-size: 8px;
            min-height: 18px;
            display: flex;
            align-items: center;
        }
        
        /* ===== SECCION 3: CLASIFICACION ===== */
        .classification-section {
            border: 1px solid #000;
            border-top: none;
            padding: 6px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 3px;
            min-height: 50px;
            align-items: center;
        }
        
        .class-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .class-label {
            font-weight: bold;
            font-size: 9px;
            min-width: 110px;
        }
        
        .class-box {
            border: 1px solid #000;
            padding: 3px 8px;
            font-size: 10px;
            min-width: 80px;
            min-height: 24px;
            display: flex;
            align-items: center;
        }
        
        /* ===== SEPARADOR TABLA ===== */
        .table-separator {
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
            padding: 2px;
            font-weight: bold;
            font-size: 10px;
            margin: 0;
            letter-spacing: 0px;
        }
        
        /* ===== TABLA CON DIVS ===== */
        .table-container {
            width: 100%;
            margin-bottom: 2px;
            font-size: 8px;
            display: flex;
            flex-direction: column;
            border: 1px solid #000;
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 3.5% 3.5% 4% 3.5% 5% 3.5% 3.5% 3.5% 4% 45% 12%;
            width: 100%;
            grid-auto-flow: row;
        }
        
        .table-header {
            background: white;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        
        .table-cell {
            border-right: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            vertical-align: top;
            min-height: 18px;
            display: flex;
            align-items: center;
            word-wrap: break-word;
            overflow: hidden;
            font-size: 8px;
        }
        
        .table-header .table-cell {
            font-weight: bold;
            font-size: 7px;
            min-height: 18px;
            border-bottom: 1px solid #000;
        }
        
        .table-cell:last-child {
            border-right: none;
        }
        
        .table-row .table-cell {
            border-bottom: 1px solid #000;
        }
        
        .table-row.total-row .table-cell {
            font-weight: bold;
            padding: 3px 1px;
        }
        
        .table-cell.desc-cell {
            text-align: left;
            padding-left: 3px;
        }
        
        .table-cell.monto-cell {
            text-align: right;
            padding-right: 3px;
            font-weight: bold;
        }
        
        /* ===== SON ===== */
        .son-section {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 2px 6px;
            margin: 2px 0;
            font-size: 9px;
            font-weight: bold;
        }
        
        /* ===== DESCRIPCION ===== */
        .description-box {
            border: 1px solid #000;
            padding: 4px;
            margin-bottom: 4px;
            min-height: 65px;
            flex-grow: 1;
        }
        
        .description-label {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 3px;
        }
        
        .description-text {
            font-size: 8px;
            line-height: 1.3;
            overflow: hidden;
        }
        
        /* ===== DATOS APROBACION ===== */
        .approval-box {
            border: 1px solid #000;
            border-top: 2px solid #000;
        }
        
        .approval-header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-bottom: 1px solid #000;
        }
        
        .approval-header-cell {
            border-right: 1px solid #000;
            padding: 6px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
        }
        
        .approval-header-cell:last-child {
            border-right: none;
        }
        
        .approval-body {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border-bottom: 1px solid #000;
            min-height: 50px;
        }
        
        .approval-state {
            border-right: 1px solid #000;
            padding: 6px 3px;
            display: flex;
            flex-direction: column;
            font-size: 9px;
            font-weight: bold;
        }
        
        .approval-signature {
            border-right: 1px solid #000;
            padding: 6px 3px;
        }
        
        .approval-signature:last-child {
            border-right: none;
        }
        
        .approval-footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
        }
        
        .approval-footer-cell {
            border-right: 1px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 7px;
            font-weight: bold;
        }
        
        .approval-footer-cell:last-child {
            border-right: none;
        }
        
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                width: 210mm !important;
                height: 297mm !important;
            }
            
            body {
                font-size: 10px !important;
            }
            
            .page {
                width: 210mm !important;
                height: 297mm !important;
                margin: 0 !important;
                padding: 8mm !important;
                box-shadow: none !important;
                display: flex !important;
                flex-direction: column !important;
                page-break-after: always !important;
                background: white !important;
                box-sizing: border-box !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
            }
            
            .print-btn {
                display: none !important;
            }
            
            @page {
                size: A4;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="imprimirConNombre()" title="Guardar/Imprimir certificado">
        <i class="fas fa-download"></i> Descargar PDF
    </button>
    
    <script>
        function imprimirConNombre() {
            // Extraer número de certificado del contenido HTML
            let numeroCertificado = 'CERTIFICADO';
            
            // Buscar el elemento que contiene el número de certificado
            const elementos = document.querySelectorAll('div');
            for (let el of elementos) {
                if (el.textContent.includes('CERT-')) {
                    const match = el.textContent.match(/CERT-\d+/);
                    if (match) {
                        numeroCertificado = match[0];
                        break;
                    }
                }
            }
            
            // Establecer el título de la página para el nombre del PDF
            const tituloOriginal = document.title;
            document.title = `Certificacion_Presupuestaria_${numeroCertificado}`;
            
            // Imprimir
            window.print();
            
            // Restaurar el título original después de imprimir
            setTimeout(() => {
                document.title = tituloOriginal;
            }, 500);
        }
    </script>
    
    <div class="page">
        <!-- HEADER PRINCIPAL -->
        <div class="header-main">CERTIFICACION PRESUPUESTARIA</div>
        
        <!-- SECCION 1: INFO BASICA + DOCUMENTOS COMBINADOS -->
        <div style="border: 1px solid #000; margin-bottom: 20px; margin-top: 15px;">
            <!-- Fila 1: Datos Básicos -->
            <div style="display: flex; border-bottom: 1px solid #000;">
                <!-- Columna izquierda: Etiquetas -->
                <div style="border-right: 1px solid #000; padding: 5px; width: 15%; font-size: 8px;">
                    <div style="margin-bottom: 8px;"><strong>Institucion:</strong></div>
                    <div style="margin-bottom: 8px;"><strong>Unid. Ejecutora:</strong></div>
                    <div><strong>Unid. Desc:</strong></div>
                </div>
                
                <!-- Columna 2: Valores Institución -->
                <div style="border-right: 1px solid #000; padding: 5px; width: 35%; font-size: 9px;">
                    <div style="border-bottom: 1px solid #000; margin-bottom: 3px; padding-bottom: 3px; min-height: 14px;"><?php echo htmlspecialchars($certificate['institucion'] ?? ''); ?></div>
                    <div style="border-bottom: 1px solid #000; margin-bottom: 3px; padding-bottom: 3px; min-height: 14px;"><?php echo htmlspecialchars($certificate['unid_ejecutora'] ?? ''); ?></div>
                    <div style="min-height: 14px;"><?php echo htmlspecialchars($certificate['unid_desc'] ?? ''); ?></div>
                </div>
                
                <!-- Columna 3: No. Certificación -->
                <div style="border-right: 1px solid #000; padding: 5px; width: 18%; text-align: center; font-size: 8px;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 3px;"><strong>NO. CERTIFICACION</strong></div>
                    <div style="font-weight: bold; font-size: 10px; min-height: 14px; display: flex; align-items: center; justify-content: center;"><?php echo htmlspecialchars($certificate['numero_certificado'] ?? ''); ?></div>
                </div>
                
                <!-- Columna 4: Fecha -->
                <div style="padding: 5px; width: 32%; text-align: center; font-size: 8px;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 3px;"><strong>FECHA DE ELABORACION</strong></div>
                    <div style="display: flex; gap: 4px; justify-content: center;">
                        <div style="border: 1px solid #000; padding: 1px 3px; width: 20px; text-align: center; font-size: 8px;"><?php echo date('d', strtotime($certificate['fecha_elaboracion'] ?? date('Y-m-d'))); ?></div>
                        <div style="border: 1px solid #000; padding: 1px 3px; width: 20px; text-align: center; font-size: 8px;"><?php echo date('m', strtotime($certificate['fecha_elaboracion'] ?? date('Y-m-d'))); ?></div>
                        <div style="border: 1px solid #000; padding: 1px 3px; width: 25px; text-align: center; font-size: 8px;"><?php echo date('y', strtotime($certificate['fecha_elaboracion'] ?? date('Y-m-d'))); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Fila 2: Documentos -->
            <div style="display: flex;">
                <!-- Tipo de Documento -->
                <div style="border-right: 1px solid #000; padding: 3px; width: 50%; border-bottom: 1px solid #000;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 2px; font-size: 8px;"><strong>TIPO DE DOCUMENTO RESPALDO</strong></div>
                    <div style="font-size: 9px; min-height: 20px;"><?php echo htmlspecialchars($certificate['tipo_doc_respaldo'] ?? ''); ?></div>
                </div>
                
                <!-- Clase de Documento -->
                <div style="padding: 5px; width: 50%;">
                    <div style="border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 2px; font-size: 8px;"><strong>CLASE DE DOCUMENTO RESPALDO</strong></div>
                    <div style="font-size: 9px; min-height: 20px;"><?php echo htmlspecialchars($certificate['clase_doc_respaldo'] ?? ''); ?></div>
                </div>
            </div>
        </div>
        
        <!-- SECCION 3: CLASIFICACION - CON DIVS -->
        <div style="border: 1px solid #000; padding: 12px; display: flex; gap: 50px; min-height: 50px; align-items: center; justify-content: center; margin-bottom: 20px;">
            <!-- Clase de Registro -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 8px;"><strong>CLASE DE REGISTRO</strong></span>
                <div style="border: 1px solid #000; padding: 2px 6px; font-size: 9px; background: white;"><?php echo htmlspecialchars($certificate['clase_registro'] ?? ''); ?></div>
            </div>
            
            <!-- Clase de Gasto -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 8px;"><strong>CLASE DE GASTO</strong></span>
                <div style="border: 1px solid #000; padding: 2px 6px; font-size: 9px; background: white;"><?php echo htmlspecialchars($certificate['clase_gasto'] ?? ''); ?></div>
            </div>
        </div>
        
        <!-- SEPARADOR TABLA -->
        <div style="border-top: 2px solid #000; border-bottom: 1px solid #000; text-align: center; padding: 2px; font-weight: bold; font-size: 9px; margin: 25px 0 20px 0;">
            CERTIFICACION PRESUPUESTARIA
        </div>
        
        <!-- TABLA CON DATOS PRESUPUESTARIOS - CON DIVS -->
        <div style="border: 1px solid #000; margin-bottom: 20px;">
            <!-- HEADER -->
            <div style="display: grid; grid-template-columns: 3.5% 3.5% 4% 3.5% 5% 3.5% 3.5% 3.5% 4% 45% 12%; font-weight: bold; border-bottom: 1px solid #000; background: white;">
                <div style="border-right: 1px solid #000; padding: 4px; text-align: center; font-size: 7px;">PG</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">SP</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">PY</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">ACT</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">ITEM</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">UBG</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">FTE</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">ORG</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">N.Prest</div>
                <div style="border-right: 1px solid #000; padding: 2px; text-align: left; font-size: 7px;">DESCRIPCION</div>
                <div style="padding: 2px; text-align: right; font-size: 7px;">MONTO</div>
            </div>
            
            <!-- ROWS -->
            <?php 
            $totalMonto = 0;
            if (!empty($details)):
                foreach ($details as $item): 
                    $totalMonto += floatval($item['monto'] ?? 0);
            ?>
                <div style="display: grid; grid-template-columns: 3.5% 3.5% 4% 3.5% 5% 3.5% 3.5% 3.5% 4% 45% 12%; border-bottom: 1px solid #000;">
                    <div style="border-right: 1px solid #000; padding: 2px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['programa_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['subprograma_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['proyecto_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['actividad_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['item_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['ubicacion_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['fuente_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['organismo_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px; text-align: center; font-size: 8px;"><?php echo htmlspecialchars($item['naturaleza_codigo'] ?? ''); ?></div>
                    <div style="border-right: 1px solid #000; padding: 1px 2px; text-align: left; font-size: 8px;"><?php echo htmlspecialchars($item['descripcion_item'] ?? ''); ?></div>
                    <div style="padding: 1px 2px; text-align: right; font-size: 8px; font-weight: bold;">$ <?php echo number_format(abs($item['monto'] ?? 0), 2, ',', '.'); ?></div>
                </div>
            <?php 
                endforeach;
            else:
            ?>
                <div style="display: grid; grid-template-columns: 1fr; border-bottom: 1px solid #000; padding: 2px; text-align: center; font-size: 7px;">
                    No hay items registrados
                </div>
            <?php endif; ?>
            
            <!-- TOTAL PRESUPUESTARIO -->
            <div style="display: grid; grid-template-columns: 3.5% 3.5% 4% 3.5% 5% 3.5% 3.5% 3.5% 4% 45% 12%; font-weight: bold; border-bottom: 1px solid #000;">
                <div style="border-right: 1px solid #000; padding: 2px; text-align: right; font-size: 8px; grid-column: 1 / 11;">TOTAL PRESUPUESTARIO:</div>
                <div style="padding: 2px 2px; text-align: right; font-size: 8px; font-weight: bold;">$ <?php echo number_format(abs($totalMonto), 2, ',', '.'); ?></div>
            </div>
        </div>
            </tr>
            

        </table>
        
        <!-- SON -->
        <div style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 4px 6px; margin: 20px 0; font-size: 8px; font-weight: bold;">
            <?php echo MontoHelper::convertirALetras($totalMonto); ?>
        </div>
        
        <!-- DESCRIPCION -->
        <div style="border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 5px; margin-bottom: 25px;">
            <div style="font-weight: bold; font-size: 8px; margin-bottom: 2px;">DESCRIPCION:</div>
            <div style="font-size: 7px; line-height: 1.3; overflow: hidden; max-height: 60px;">
                <?php echo nl2br(htmlspecialchars($certificate['descripcion'] ?? '')); ?>
            </div>
        </div>
        
        <!-- DATOS APROBACION -->
        <div style="border: 1px solid #000; border-top: 2px solid #000; margin-bottom: 0; margin-top: auto;">
            <!-- Header -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; border-bottom: 1px solid #000;">
                <div style="border-right: 1px solid #000; padding: 8px 5px; text-align: center; font-weight: bold; font-size: 9px;">ESTADO</div>
                <div style="border-right: 1px solid #000; padding: 8px 5px; text-align: center; font-weight: bold; font-size: 9px;">REGISTRADO:</div>
                <div style="padding: 8px 5px; text-align: center; font-weight: bold; font-size: 9px;">APROBADO:</div>
            </div>
            
            <!-- Body -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; border-bottom: 1px solid #000; min-height: 60px;">
                <div style="border-right: 1px solid #000; padding: 6px 5px; text-align: center; vertical-align: top; font-size: 9px;">
                    <div style="font-weight: bold;">APROBADO</div>
                    <div style="font-size: 8px; margin-top: 3px;">FECHA:<br><?php echo date('d/m/Y', strtotime($certificate['fecha_actualizacion'] ?? date('Y-m-d'))); ?></div>
                </div>
                <div style="border-right: 1px solid #000; padding: 6px 5px;"></div>
                <div style="padding: 6px 5px;"></div>
            </div>
            
            <!-- Footer -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr;">
                <div style="border-right: 1px solid #000; padding: 6px; text-align: center; font-size: 8px;"></div>
                <div style="border-right: 1px solid #000; padding: 6px; text-align: center; font-size: 8px; font-weight: bold;">Funcionario Responsable</div>
                <div style="padding: 6px; text-align: center; font-size: 8px; font-weight: bold;">Director Financiero</div>
            </div>
        </div>
    </div>
</body>
</html>
