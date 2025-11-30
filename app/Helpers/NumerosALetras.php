<?php

namespace App\Helpers;

class NumerosALetras
{
    private static $UNIDADES = [
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'
    ];
    
    private static $DECENAS = [
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE',
        'VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'
    ];
    
    private static $CENTENAS = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
    ];

    public static function convertir($number, $moneda = 'SOLES', $centimos = 'CENTÍMOS')
    {
        $converted = '';
        $decimales = '';
        
        // Separar parte entera y decimal
        $number_arr = explode('.', number_format($number, 2, '.', ''));
        $entero = $number_arr[0];
        $decimals = isset($number_arr[1]) ? $number_arr[1] : '00';
        
        // Convertir parte entera
        if ($entero == '0') {
            $converted = 'CERO';
        } else if ($entero < 1000) {
            $converted = self::convertGroup($entero);
        } else {
            $converted = self::convertirGrandes($entero);
        }
        
        // Convertir decimales
        if ($decimals > 0) {
            $decimales = ' CON ' . self::convertGroup($decimals) . ' ' . $centimos;
        }
        
        return trim($converted . ' ' . $moneda . $decimales);
    }
    
    private static function convertirGrandes($number)
    {
        $output = '';
        
        if ($number >= 1000 && $number < 1000000) {
            $miles = floor($number / 1000);
            $resto = $number % 1000;
            
            if ($miles == 1) {
                $output = 'MIL';
            } else {
                $output = self::convertGroup($miles) . ' MIL';
            }
            
            if ($resto > 0) {
                $output .= ' ' . self::convertGroup($resto);
            }
        } else {
            $output = 'NÚMERO DEMASIADO GRANDE';
        }
        
        return $output;
    }
    
    private static function convertGroup($n)
    {
        $n = intval($n);
        $output = '';
        
        if ($n == 0) {
            return '';
        } elseif ($n == 100) {
            return 'CIEN';
        } elseif ($n > 100) {
            $centena = floor($n / 100);
            $output = self::$CENTENAS[$centena];
            $resto = $n % 100;
            
            if ($resto > 0) {
                $output .= ' ' . self::convertGroup($resto);
            }
        } elseif ($n >= 10) {
            if ($n < 30) {
                $output = self::$DECENAS[$n - 10];
            } else {
                $decena = floor($n / 10);
                $unidad = $n % 10;
                
                switch ($decena) {
                    case 3: $output = 'TREINTA'; break;
                    case 4: $output = 'CUARENTA'; break;
                    case 5: $output = 'CINCUENTA'; break;
                    case 6: $output = 'SESENTA'; break;
                    case 7: $output = 'SETENTA'; break;
                    case 8: $output = 'OCHENTA'; break;
                    case 9: $output = 'NOVENTA'; break;
                }
                
                if ($unidad > 0) {
                    $output .= ' Y ' . self::$UNIDADES[$unidad];
                }
            }
        } else {
            $output = self::$UNIDADES[$n];
        }
        
        return $output;
    }
}