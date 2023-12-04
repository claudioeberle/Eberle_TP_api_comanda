<?php

class PosicionMesa{

    public $id;
    public $enUso;

    function __construct($id, $enUso){

        $this->id = $id;
        $this->enUso = $enUso;
    }

    public static function ObtenerTodasLasPosiciones(){
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM ubicaciones";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> execute();
        $arrayObtenido = array();
        $mesas = array();
        $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
        foreach($arrayObtenido as $i){
            $mesa = new PosicionMesa($i->id, $i->enUso);
            $mesas[] = $mesa;
        }
        return $mesas;
    }

    public static function Modificar($id, $enUso){
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("UPDATE ubicaciones SET enUso = :enUso WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $consulta -> bindParam(':enUso', $enUso);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public static function ObtenerPosicionLibre(){
        $retorno = false;
        $posiciones = PosicionMesa::ObtenerTodasLasPosiciones();
        if($posiciones){
            foreach($posiciones as $posicion){
                if(!$posicion->enUso){
                    $retorno = $posicion;
                    break;
                }
            }
        }
        return $retorno;
    }
}
?>