<?php
/**
 * Generador de PDF Simple
 * Basado en FPDF para generar PDF sin dependencias externas
 * Crea PDF en una sola página A4 en orientación horizontal
 */

class SimplePdfGenerator {
    private $items = [];
    private $resumen = [];
    private $filename = '';

    public function __construct($filename = 'reporte.pdf') {
        $this->filename = $filename;
    }

    /**
     * Generar PDF con datos
     */
    public function generate($items, $resumen) {
        $this->items = $items;
        $this->resumen = $resumen;

        // Generar HTML que se puede convertir a PDF
        $html = $this->generateHtml();
        
        // Usar navegador para imprimir como PDF
        $this->downloadAsHtml($html);
    }

    /**
     * Generar HTML imprimible
     */
    private function generateHtml() {
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Presupuestos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8pt;
            color: #333;
            line-height: 1.4;
        }
        
        @page {
            size: A4 landscape;
            margin: 5mm;
            margin-bottom: 10mm;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 2px solid #0B283F;
            padding-bottom: 5px;
        }
        
        .header h1 {
            font-size: 14pt;
            color: #0B283F;
            margin: 0;
        }
        
        .header p {
            font-size: 7pt;
            color: #666;
            margin: 2px 0;
        }
        
        .summary {
            margin-bottom: 8px;
            padding: 5px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        
        .summary h3 {
            font-size: 9pt;
            color: #0B283F;
            margin: 0 0 3px 0;
            font-weight: bold;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 7pt;
            padding: 2px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row label {
            font-weight: bold;
            color: #0B283F;
        }
        
        .summary-row value {
            text-align: right;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            margin-top: 5px;
        }
        
        thead {
            background-color: #0B283F;
            color: white;
            position: relative;
        }
        
        th {
            padding: 3px 2px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #0B283F;
            white-space: nowrap;
            font-size: 7pt;
        }
        
        td {
            padding: 2px 2px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        tr:hover {
            background-color: #f0f0f0;
        }
        
        .number {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 6pt;
            color: #999;
        }
        
        .instructions {
            background-color: #fffbcc;
            border: 1px solid #ffeb3b;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 3px;
            font-size: 8pt;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE PRESUPUESTOS</h1>
        <p>Sistema de Gestión de Certificados y Presupuesto</p>
        <p>Generado: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <div class="summary">
        <h3>RESUMEN GENERAL</h3>
        <div class="summary-row">
            <label>Total Items:</label>
            <value><?php echo number_format($this->resumen['total_items'] ?? 0); ?></value>
        </div>
        <div class="summary-row">
            <label>Total Codificado:</label>
            <value>$<?php echo number_format($this->resumen['total_codificado'] ?? 0, 2, '.', ','); ?></value>
        </div>
        <div class="summary-row">
            <label>Total Certificado:</label>
            <value>$<?php echo number_format($this->resumen['total_certificado'] ?? 0, 2, '.', ','); ?></value>
        </div>
        <div class="summary-row">
            <label>Saldo Disponible:</label>
            <value>$<?php echo number_format($this->resumen['total_saldo_disponible'] ?? 0, 2, '.', ','); ?></value>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Código<br>Programa</th>
                <th>Código<br>Actividad</th>
                <th>Código<br>Fuente</th>
                <th>Código<br>Item</th>
                <th>Descripción Item</th>
                <th class="number">Codificado</th>
                <th class="number">Certificado</th>
                <th class="number">Saldo<br>Disponible</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->items as $index => $item): ?>
            <tr>
                <td class="text-center"><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars_decode($item['codigog1']); ?></td>
                <td><?php echo htmlspecialchars_decode($item['codigog2']); ?></td>
                <td><?php echo htmlspecialchars_decode($item['codigog3']); ?></td>
                <td><?php echo htmlspecialchars_decode($item['codigog5']); ?></td>
                <td><?php echo htmlspecialchars_decode(substr($item['descripciong5'], 0, 50)); ?></td>
                <td class="number">$<?php echo number_format($item['col3'] ?? 0, 2, '.', ','); ?></td>
                <td class="number">$<?php echo number_format($item['col4'] ?? 0, 2, '.', ','); ?></td>
                <td class="number">$<?php echo number_format($item['saldo_disponible'] ?? 0, 2, '.', ','); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>© 2025 Sistema de Gestión de Certificados - UEB</p>
        <p><strong>Para guardar como PDF:</strong> Presiona Ctrl+P (o Cmd+P en Mac), selecciona "Guardar como PDF" y presiona "Guardar"</p>
    </div>
    
    <div class="instructions no-print" style="margin-top: 20px; padding: 10px; background: #e3f2fd; border: 1px solid #2196F3; border-radius: 4px; font-size: 11pt;">
        <strong>📋 Instrucciones:</strong><br>
        1. Presiona <strong>Ctrl+P</strong> (Windows/Linux) o <strong>Cmd+P</strong> (Mac)<br>
        2. Selecciona <strong>"Guardar como PDF"</strong> en la lista de impresoras<br>
        3. En las opciones de impresión:<br>
        &nbsp;&nbsp;- Márgenes: Mínimo<br>
        &nbsp;&nbsp;- Tamaño: A4<br>
        &nbsp;&nbsp;- Orientación: Horizontal (Landscape)<br>
        4. Haz clic en <strong>"Guardar"</strong>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Descargar como HTML
     */
    private function downloadAsHtml($html) {
        // Limpiar buffer si hay algo pendiente
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="' . str_replace('.pdf', '.html', $this->filename) . '"');
        
        echo $html;
        exit;
    }
}
?>
