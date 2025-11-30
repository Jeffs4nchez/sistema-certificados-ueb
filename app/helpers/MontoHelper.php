<?php
/**
 * Helper para convertir montos a palabras
 */

class MontoHelper {
    
    private static $unidades = [
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'
    ];
    
    private static $especiales = [
        10 => 'DIEZ',
        11 => 'ONCE',
        12 => 'DOCE',
        13 => 'TRECE',
        14 => 'CATORCE',
        15 => 'QUINCE',
        16 => 'DIECISÉIS',
        17 => 'DIECISIETE',
        18 => 'DIECIOCHO',
        19 => 'DIECINUEVE'
    ];
    
    private static $decenas = [
        '', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
    ];
    
    private static $centenas = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
    ];
    
    private static $escalas = [
        1000000 => 'MILLONES',
        1000 => 'MIL',
        1 => ''
    ];
    
    /**
     * Convierte un número a palabras
     */
    public static function convertirALetras($numero) {
        if ($numero == 0) {
            return 'CERO';
        }
        
        // Separar parte entera y decimal
        $partes = explode('.', (string)$numero);
        $entero = (int)$partes[0];
        $decimal = isset($partes[1]) ? str_pad($partes[1], 2, '0', STR_PAD_RIGHT) : '00';
        
        // Convertir parte entera
        $texto_entero = self::convertirEntero($entero);
        
        // Retornar en formato "SON: NÚMERO DÓLARES DECIMAL/100"
        return "SON: " . $texto_entero . " DÓLARES " . $decimal . "/100";
    }
    
    /**
     * Convierte la parte entera a palabras
     */
    private static function convertirEntero($numero) {
        if ($numero == 0) {
            return 'CERO';
        }
        
        $texto = '';
        $escala_actual = 1000000;
        
        foreach (self::$escalas as $escala => $nombre_escala) {
            if ($numero >= $escala) {
                $cantidad = intdiv($numero, $escala);
                $numero = $numero % $escala;
                
                if ($escala == 1000000) {
                    $texto .= self::convertirCientos($cantidad) . ' ' . $nombre_escala . ' ';
                } elseif ($escala == 1000) {
                    if ($cantidad == 1) {
                        $texto .= 'MIL ';
                    } else {
                        $texto .= self::convertirCientos($cantidad) . ' MIL ';
                    }
                } else {
                    $texto .= self::convertirCientos($cantidad);
                }
            }
        }
        
        return trim($texto);
    }
    
    /**
     * Convierte de 0 a 999
     */
    private static function convertirCientos($numero) {
        if ($numero == 0) {
            return '';
        }
        
        $texto = '';
        
        // Centenas
        if ($numero >= 100) {
            $centena = intdiv($numero, 100);
            if ($centena == 1 && $numero == 100) {
                $texto = 'CIEN';
            } else {
                $texto = self::$centenas[$centena];
            }
            $numero = $numero % 100;
            if ($numero > 0) {
                $texto .= ' ';
            }
        }
        
        // Decenas y unidades
        if ($numero >= 10 && $numero <= 19) {
            $texto .= self::$especiales[$numero];
        } else {
            if ($numero >= 20) {
                $decena = intdiv($numero, 10);
                $unidad = $numero % 10;
                if ($unidad == 0) {
                    $texto .= self::$decenas[$decena];
                } else {
                    $texto .= self::$decenas[$decena] . ' Y ' . self::$unidades[$unidad];
                }
            } elseif ($numero > 0) {
                $texto .= self::$unidades[$numero];
            }
        }
        
        return $texto;
    }
}
?>
