<?php

namespace App\Helpers;

class CalculosHelper
{
    /**
     * Redondea al entero más cercano
     * 6999.52 → 7000
     * 6280.14 → 6280
     */
    public static function redondearEntero($monto)
    {
        return round($monto);
    }
    
    /**
     * Redondea una colección de montos manteniendo la suma total
     */
    public static function redondearDistribucion($montos)
    {
        $redondeados = [];
        $diferencia = 0;
        
        foreach ($montos as $i => $monto) {
            $redondeado = self::redondearEntero($monto);
            $redondeados[$i] = $redondeado;
            $diferencia += ($monto - $redondeado);
        }
        
        // Ajustar la diferencia en el último monto
        if (abs($diferencia) >= 1) {
            $lastIndex = count($montos) - 1;
            $redondeados[$lastIndex] += $diferencia;
            $redondeados[$lastIndex] = self::redondearEntero($redondeados[$lastIndex]);
        }
        
        return $redondeados;
    }
}