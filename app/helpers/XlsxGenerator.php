<?php
/**
 * Generador de archivos XLSX
 * Crea archivos Excel .xlsx usando PHP puro sin dependencias externas
 */

class XlsxGenerator {
    private $filename;
    private $sheets = [];
    private $currentSheet = 0;

    public function __construct($filename = 'export.xlsx') {
        $this->filename = $filename;
    }

    /**
     * Escribir archivo XLSX con múltiples hojas
     * @param array $summaryData Datos de la primera hoja (resumen)
     * @param array $detailData Datos de la segunda hoja (detalle)
     */
    public function writeXlsx($summaryData = [], $detailData = []) {
        // Crear hojas
        if (!empty($summaryData)) {
            $this->sheets[] = [
                'name' => 'Resumen',
                'data' => $summaryData
            ];
        }

        if (!empty($detailData)) {
            $this->sheets[] = [
                'name' => 'Detalle',
                'data' => $detailData
            ];
        }

        // Generar contenido ZIP del XLSX
        $this->generateXlsxZip();
    }

    /**
     * Generar el archivo XLSX como ZIP
     */
    private function generateXlsxZip() {
        // Crear directorio temporal
        $tmpDir = sys_get_temp_dir() . '/xlsx_' . uniqid();
        if (!mkdir($tmpDir)) {
            throw new Exception('No se pudo crear directorio temporal');
        }

        try {
            // Crear estructura de directorios
            mkdir($tmpDir . '/xl');
            mkdir($tmpDir . '/xl/worksheets');
            mkdir($tmpDir . '/xl/_rels');
            mkdir($tmpDir . '/xl/theme');
            mkdir($tmpDir . '/_rels');
            mkdir($tmpDir . '/docProps');

            // Crear archivos necesarios
            $this->createContentTypes($tmpDir);
            $this->createRels($tmpDir);
            $this->createWorkbook($tmpDir);
            $this->createWorkbookRels($tmpDir);
            $this->createWorksheets($tmpDir);
            $this->createTheme($tmpDir);
            $this->createDocProps($tmpDir);

            // Crear ZIP
            $this->createZipFile($tmpDir);

        } finally {
            // Limpiar directorio temporal
            $this->removeDir($tmpDir);
        }
    }

    /**
     * Crear archivo [Content_Types].xml
     */
    private function createContentTypes($tmpDir) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' . "\n";
        $xml .= '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' . "\n";
        $xml .= '  <Default Extension="xml" ContentType="application/xml"/>' . "\n";
        $xml .= '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' . "\n";

        for ($i = 1; $i <= count($this->sheets); $i++) {
            $xml .= '  <Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' . "\n";
        }

        $xml .= '  <Override PartName="/xl/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>' . "\n";
        $xml .= '  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' . "\n";
        $xml .= '  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.custom-properties+xml"/>' . "\n";
        $xml .= '</Types>';

        file_put_contents($tmpDir . '/[Content_Types].xml', $xml);
    }

    /**
     * Crear archivo .rels
     */
    private function createRels($tmpDir) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        $xml .= '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' . "\n";
        $xml .= '  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' . "\n";
        $xml .= '  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/custom-properties" Target="docProps/app.xml"/>' . "\n";
        $xml .= '</Relationships>';

        file_put_contents($tmpDir . '/_rels/.rels', $xml);
    }

    /**
     * Crear archivo workbook.xml
     */
    private function createWorkbook($tmpDir) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '  <fileVersion appName="xl" lastEdited="4" lowestEdited="4" rupBuild="4505"/>' . "\n";
        $xml .= '  <workbookPr defaultTheme="1"/>' . "\n";
        $xml .= '  <bookViews><workbookView xWindow="240" yWindow="120" windowWidth="16095" windowHeight="9660" activeTab="0"/></bookViews>' . "\n";
        $xml .= '  <sheets>' . "\n";

        for ($i = 1; $i <= count($this->sheets); $i++) {
            $xml .= '    <sheet name="' . htmlspecialchars($this->sheets[$i-1]['name']) . '" sheetId="' . $i . '" r:id="rId' . (2 + $i) . '"/>' . "\n";
        }

        $xml .= '  </sheets>' . "\n";
        $xml .= '  <calcPr calcId="140080"/>' . "\n";
        $xml .= '</workbook>';

        file_put_contents($tmpDir . '/xl/workbook.xml', $xml);
    }

    /**
     * Crear archivo workbook.xml.rels
     */
    private function createWorkbookRels($tmpDir) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . "\n";
        
        for ($i = 1; $i <= count($this->sheets); $i++) {
            $xml .= '  <Relationship Id="rId' . (2 + $i) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>' . "\n";
        }

        $xml .= '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>' . "\n";
        $xml .= '</Relationships>';

        file_put_contents($tmpDir . '/xl/_rels/workbook.xml.rels', $xml);
    }

    /**
     * Crear hojas de trabajo
     */
    private function createWorksheets($tmpDir) {
        foreach ($this->sheets as $index => $sheet) {
            $sheetNum = $index + 1;
            $this->createSheet($tmpDir, $sheetNum, $sheet['data']);
        }
    }

    /**
     * Crear una hoja individual
     */
    private function createSheet($tmpDir, $sheetNum, $data) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '  <sheetPr filterOn="false"><outlinePr summaryRight="true" summaryBelow="true"/></sheetPr>' . "\n";
        
        // Dimensiones
        $lastRow = count($data);
        $lastCol = 0;
        foreach ($data as $row) {
            $lastCol = max($lastCol, count($row));
        }
        
        $lastColLetter = $this->getColumnLetter($lastCol);
        $xml .= '  <dimension ref="A1:' . $lastColLetter . $lastRow . '"/>' . "\n";
        
        $xml .= '  <sheetViews><sheetView workbookViewId="0"/></sheetViews>' . "\n";
        $xml .= '  <sheetFormatPr defaultRowHeight="15"/>' . "\n";
        $xml .= '  <sheetData>' . "\n";

        // Agregar datos
        foreach ($data as $rowNum => $row) {
            $xml .= '    <row r="' . ($rowNum + 1) . '">' . "\n";
            foreach ($row as $colNum => $value) {
                $colLetter = $this->getColumnLetter($colNum + 1);
                $cellRef = $colLetter . ($rowNum + 1);
                
                // Detectar tipo de dato
                $cellValue = is_numeric($value) ? $value : $value;
                $cellType = 'str';
                
                if (is_numeric($value) && !is_float($value)) {
                    $cellType = 'n';
                } elseif (is_numeric($value) && is_float($value)) {
                    $cellType = 'n';
                    $cellValue = round($value, 2);
                }
                
                $xml .= '      <c r="' . $cellRef . '" t="' . $cellType . '"><v>' . htmlspecialchars($cellValue) . '</v></c>' . "\n";
            }
            $xml .= '    </row>' . "\n";
        }

        $xml .= '  </sheetData>' . "\n";
        $xml .= '  <pageMargins left="0.75" top="1" right="0.75" bottom="1" header="0.5" footer="0.5"/>' . "\n";
        $xml .= '  <pageSetup paperSize="1" orientation="landscape"/>' . "\n";
        $xml .= '</worksheet>';

        file_put_contents($tmpDir . '/xl/worksheets/sheet' . $sheetNum . '.xml', $xml);
    }

    /**
     * Convertir número de columna a letra
     */
    private function getColumnLetter($col) {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(ord('A') + ($col % 26)) . $letter;
            $col = intdiv($col, 26);
        }
        return $letter;
    }

    /**
     * Crear archivo theme1.xml
     */
    private function createTheme($tmpDir) {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="Office Theme">' . "\n";
        $xml .= '  <a:themeElements>' . "\n";
        $xml .= '    <a:clrScheme name="Office">' . "\n";
        $xml .= '      <a:dk1><a:srgbClr val="000000"/></a:dk1>' . "\n";
        $xml .= '      <a:lt1><a:srgbClr val="FFFFFF"/></a:lt1>' . "\n";
        $xml .= '    </a:clrScheme>' . "\n";
        $xml .= '  </a:themeElements>' . "\n";
        $xml .= '</a:theme>';

        file_put_contents($tmpDir . '/xl/theme/theme1.xml', $xml);
    }

    /**
     * Crear propiedades del documento
     */
    private function createDocProps($tmpDir) {
        // core.xml
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/officeDocument/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . "\n";
        $xml .= '  <dc:creator>Sistema de Gestión de Certificados</dc:creator>' . "\n";
        $xml .= '  <cp:lastModifiedBy>Admin</cp:lastModifiedBy>' . "\n";
        $xml .= '  <dcterms:created xsi:type="dcterms:W3CDTF">' . date('Y-m-d\TH:i:s\Z') . '</dcterms:created>' . "\n";
        $xml .= '  <dcterms:modified xsi:type="dcterms:W3CDTF">' . date('Y-m-d\TH:i:s\Z') . '</dcterms:modified>' . "\n";
        $xml .= '</cp:coreProperties>';

        file_put_contents($tmpDir . '/docProps/core.xml', $xml);

        // app.xml
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/custom-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' . "\n";
        $xml .= '  <property fmtid="{D5CDD505-2E9C-101B-9397-08002B2CF9AE}" pid="2" name="Category"><vt:lpwstr>Presupuestos</vt:lpwstr></property>' . "\n";
        $xml .= '</Properties>';

        file_put_contents($tmpDir . '/docProps/app.xml', $xml);
    }

    /**
     * Crear archivo ZIP
     */
    private function createZipFile($tmpDir) {
        // Crear archivo temporal para el ZIP
        $tempZipPath = sys_get_temp_dir() . '/' . uniqid() . '.zip';
        
        $zip = new ZipArchive();
        
        if ($zip->open($tempZipPath, ZipArchive::CREATE) !== true) {
            throw new Exception('No se pudo crear archivo ZIP');
        }

        // Agregar archivos al ZIP
        $this->addDirToZip($tmpDir, $zip);
        
        $zip->close();

        // Enviar archivo al navegador
        $this->sendFile($tempZipPath);
        
        // Limpiar archivo temporal
        if (file_exists($tempZipPath)) {
            unlink($tempZipPath);
        }
    }

    /**
     * Enviar archivo al navegador
     */
    private function sendFile($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception('No se puede enviar el archivo: no existe');
        }

        $fileSize = filesize($filePath);
        $filename = basename($this->filename);

        // Enviar headers
        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . $fileSize);
            header('Content-Transfer-Encoding: binary');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        
        // Enviar archivo
        readfile($filePath);
    }

    /**
     * Agregar directorio al ZIP
     */
    private function addDirToZip($dir, $zip, $zipPath = '') {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $dir . '/' . $file;
            $addPath = $zipPath ? $zipPath . '/' . $file : $file;
            
            if (is_dir($filePath)) {
                $zip->addEmptyDir($addPath);
                $this->addDirToZip($filePath, $zip, $addPath);
            } else {
                $zip->addFile($filePath, $addPath);
            }
        }
    }

    /**
     * Eliminar directorio recursivamente
     */
    private function removeDir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $dir . '/' . $file;
                    if (is_dir($filePath)) {
                        $this->removeDir($filePath);
                    } else {
                        unlink($filePath);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
?>
