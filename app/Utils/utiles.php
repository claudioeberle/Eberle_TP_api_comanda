<?php

class Utiles{


    public static function ObtenerCodigoAlfaNumAleatorio($tamaño){

        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $codigo = '';
        if(is_int($tamaño)){

            for ($i = 0; $i < $tamaño; $i++) {
                $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }
        }
        return $codigo;
    }
    
    public static function GuardarCodigoEnCSV($codigo, $ruta){

        $archivo = $ruta;
        $existeArchivo = file_exists($archivo);

        $csvFile = fopen($archivo, 'a');
            if (!$existeArchivo) {
            fputcsv($csvFile, array('Codigo'));
        }
        fputcsv($csvFile, array($codigo));
        fclose($csvFile);
    }

    public static function ValidarExistenciaCodigo($codigo, $rutaArchivo){

        $archivo = fopen($rutaArchivo, 'r');
        if ($archivo) {

            fgetcsv($archivo);

            while (($fila = fgetcsv($archivo)) !== false) {

                if ($fila[0] === $codigo) {
                    fclose($archivo);
                    return true;
                }
            }
            fclose($archivo);
        }
        return false;
    }
}
?>