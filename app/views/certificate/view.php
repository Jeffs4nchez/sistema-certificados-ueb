<?php
/**
 * Vista: Ver Certificado - Formato Oficial
 */
require_once __DIR__ . '/../../helpers/MontoHelper.php';
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .certificate-container {
            background-color: white;
            padding: 40px;
            margin: 20px auto;
            max-width: 900px;
            border: 1px solid #333;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 13px;
        }
        .header-info-item {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px;
        }
        .header-info-label {
            font-weight: bold;
        }
        .header-info-value {
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
        }
        .header-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .header-section-item {
            font-size: 13px;
        }
        .header-section-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header-section-value {
            border-bottom: 1px solid #333;
            padding-bottom: 2px;
            min-height: 20px;
        }
        .table-section {
            margin-top: 30px;
        }
        .table-section-title {
            font-weight: bold;
            border-bottom: 2px solid #333;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }
        table th {
            border: 1px solid #333;
            padding: 0.5rem;
            text-align: center;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        table td {
            border: 1px solid #333;
            padding: 0.5rem;
            text-align: center;
        }
        table td.description {
            text-align: left;
        }
        table td.amount {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .sono-section {
            margin-top: 30px;
            font-weight: bold;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .description-section {
            margin-top: 20px;
            font-size: 13px;
        }
        .description-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .description-text {
            text-align: justify;
            line-height: 1.6;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            padding: 10px;
            border: 1px solid #e0e0e0;
        }
        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            text-align: center;
            font-size: 12px;
            justify-items: center;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-item {
            border-top: 1px solid #333;
            padding-top: 20px;
            margin-top: 40px;
        }
        .buttons {
            margin-bottom: 20px;
            text-align: right;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            padding: 8px 16px;
            margin-left: 0;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        @media print {
            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }
            .buttons {
                display: none !important;
            }
            .certificate-container {
                max-width: 100%;
                margin: 0;
                padding: 40px;
                box-shadow: none;
                border: none;
                page-break-after: avoid;
            }
            * {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="buttons">
        <a href="javascript:window.print()" class="btn" title="Imprimir">
            <i class="fas fa-print"></i> Imprimir
        </a>
        <a href="index.php?action=certificate-list" class="btn btn-secondary" title="Volver">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="certificate-container">
        <!-- TITULO -->
        <div class="header-title">CERTIFICACION DE RECURSOS</div>

        <!-- ENCABEZADO -->
        <div class="header-info">
            <div class="header-info-item">
                <span class="header-info-label">Institución:</span>
                <span class="header-info-value"><?php echo htmlspecialchars($certificate['institucion'] ?? ''); ?></span>
            </div>
            <div class="header-info-item">
                <span class="header-info-label">No. CERTIFICACION:</span>
                <span class="header-info-value"><?php echo htmlspecialchars($certificate['numero_certificado'] ?? ''); ?></span>
            </div>
            <div class="header-info-item">
                <span class="header-info-label">Sección:</span>
                <span class="header-info-value"><?php echo htmlspecialchars($certificate['seccion_memorando'] ?? ''); ?></span>
            </div>
            <div class="header-info-item">
                <span class="header-info-label">FECHA DE ELABORACION:</span>
                <span class="header-info-value"><?php echo date('d/m/Y', strtotime($certificate['fecha_elaboracion'] ?? '')); ?></span>
            </div>
        </div>

        <!-- TABLA DE ITEMS -->
        <div class="table-section">
            <div class="table-section-title">CERTIFICACION DE RECURSOS</div>
            <table>
                <thead>
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
                        <th>DESCRIPCION</th>
                        <th>MONTO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Obtener detalles del certificado
                    $details = $this->certificateModel->getCertificateDetails($certificate['id']);
                    $totalMonto = 0;
                    if (!empty($details)): 
                        foreach ($details as $detail):
                            $totalMonto += $detail['monto'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['programa_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['subprograma_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['proyecto_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['actividad_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['item_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['ubicacion_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['fuente_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['organismo_codigo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($detail['naturaleza_codigo'] ?? ''); ?></td>
                        <td class="description"><?php echo htmlspecialchars($detail['descripcion_item'] ?? ''); ?></td>
                        <td class="amount">$ <?php echo number_format($detail['monto'] ?? 0, 2, ',', '.'); ?></td>
                    </tr>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 20px;">No hay items registrados</td>
                    </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan="10" style="text-align: right;">TOTAL PRESUPUESTARIO:</td>
                        <td class="amount">$ <?php echo number_format($totalMonto, 2, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- SON -->
        <div class="sono-section">
            <?php echo MontoHelper::convertirALetras($totalMonto); ?>
        </div>

        <!-- DESCRIPCION -->
        <div class="description-section">
            <div class="description-label">DESCRIPCION:</div>
            <div class="description-text">
                <?php 
                $desc = $certificate['descripcion'] ?? '';
                if (empty($desc)) {
                    echo "CERTIFICO QUE LAS RESOLUCIONES PRESUPUESTARIAS EN LA PARTIDA " . htmlspecialchars($certificate['numero_certificado'] ?? '') . ", 
                    SEGÚN CERTIFICACION POA-0617-DPAC-UEB-2025, SEGÚN CERTIFICACION DE " . date('d/m/Y', strtotime($certificate['fecha_elaboracion'] ?? '')) . ", 
                    INFORME NRO-" . htmlspecialchars($certificate['numero_certificado'] ?? '') . ", 
                    SEGÚN MEMORANDO NRO-UEB-RECT-2025-3897 Y MEMORANDO N-UEB - DTI-2025-2802-M.";
                } else {
                    echo htmlspecialchars($desc);
                }
                ?>
            </div>
        </div>

        <!-- FIRMAS -->
        <div class="signature-section">
            <div class="signature-item">
                <div>REGISTRADO:</div>
                <div style="margin-top: 40px;">_________________</div>
            </div>
            <div class="signature-item">
                <div>APROBADO:</div>
                <div style="margin-top: 40px;">_________________</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 12px; color: #666;">
            FECHA: <?php echo date('d/m/Y', strtotime($certificate['fecha_elaboracion'] ?? '')); ?>
        </div>
    </div>
</body>
</html>
</body>
</html>