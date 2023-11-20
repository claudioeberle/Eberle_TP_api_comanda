<?php

class Utiles{


    public static function ObtenerCodigoAlfaNumAleatorio($tamaño){

        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = '';
        if(is_int($tamaño)){

            for ($i = 0; $i < $tamaño; $i++) {
                $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }
        }
        return $codigo;
    }
    
    public static function GuardarCodigoEnCSV($codigo, $ruta){

        $exito = false;
        $data = "";
        $codigos = self::GetCodigosArchivo($ruta);
        array_push($codigos, $codigo);
        if(count($codigos) > 0)
        {
            $archivo = fopen($ruta, "w");

            if($archivo !== false)
            {
                foreach($codigos as $codigo)
                {
                    $data .= $codigo . ",";
                }
                if(strlen($data) > 0)
                {
                    $caracteres = fwrite($archivo, $data);

                    if($caracteres >  0)
                    {
                        $exito = true;
                    }
                }
            }
            fclose($archivo);
        }
        return $exito;
    }

    static function GetCodigosArchivo($ruta){
        $exito = false;
        $codigos = array();
            
            $archivo = fopen($ruta, "r");
            if($archivo !== false)
            {
                while (($linea = fgets($archivo)) !== false) 
                {
                    $codigosString = explode(',', $linea);
                    foreach($codigosString as $codigo)
                    {
                        if($codigo !== ""){
                            array_push($codigos, $codigo);
                        }
                    }
                }
            }
            fclose($archivo);
        return $codigos;
    }

    public static function ValidarExistenciaCodigo($codigo, $rutaArchivo){

        $existe = false;
        $codigos = self::GetCodigosArchivo($rutaArchivo);

        if(count($codigos) > 0){

            foreach($codigos as $aux){
                if($aux === $codigo){
                    $existe = true;
                    break;
                }
            }
        }
        return $existe;
    }
}
?>