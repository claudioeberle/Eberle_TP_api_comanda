<?php

class Encuesta {
    public $id;
    public $codigoMesa;
    public $puntosMesa;
    public $puntosResto;
    public $puntosMozo;
    public $puntosCocinero;
    public $experiencia;

    public function __construct($id, $codigoMesa, $puntosMesa, $puntosResto, $puntosMozo, $puntosCocinero, $experiencia) {
        $this -> id = $id;
        $this -> codigoMesa = $codigoMesa;
        $this -> puntosMesa = $puntosMesa;
        $this -> puntosResto = $puntosResto;
        $this -> puntosMozo = $puntosMozo;
        $this -> puntosCocinero = $puntosCocinero;
        $this -> experiencia = $experiencia;
    }

    public function GuardarEncuesta() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("INSERT INTO encuestas (codigoMesa, puntosMesa, puntosResto, puntosMozo, puntosCocinero, experiencia) VALUES (:codigoMesa, :puntosMesa, :puntosResto, :puntosMozo, :puntosCocinero, :experiencia)");
        $consulta->bindParam(":codigoMesa", $this -> codigoMesa);
        $consulta->bindParam(":puntosMesa", $this -> puntosMesa);
        $consulta->bindParam(":puntosResto", $this -> puntosResto);
        $consulta->bindParam(":puntosMozo", $this -> puntosMozo);
        $consulta->bindParam(":puntosCocinero", $this -> puntosCocinero);
        $consulta->bindParam(":experiencia", $this -> experiencia);
    
        $resultado = $consulta -> execute();
    
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> RetornarUltimoIdInsertado();
        }
        return $retorno;
    }

    public static function ObtenerTodasLasEncuestas() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM encuestas";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> execute();
        $arrayObtenido = array();
        $encuestas = array();
        $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
        foreach($arrayObtenido as $aux){
            $encuesta = new Encuesta($aux->id, $aux->codigoMesa, $aux->puntosMesa, $aux->puntosResto, $aux->puntosMozo, $aux->puntosCocinero, $aux->experiencia);
            $encuestas[] = $encuesta;
        }
        return $encuestas;
    }

    public static function ObtenerPorCodigoMesa($codigoMesa){
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM encuestas WHERE codigoMesa = :codigoMesa";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':codigoMesa', $codigoMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $aux = $consulta->fetchObject();
            if($aux){
                $encuesta = new Encuesta($aux->id, $aux->codigoMesa, $aux->puntosMesa, $aux->puntosResto, $aux->puntosMozo, $aux->puntosCocinero, $aux->experiencia);
                $retorno = $encuesta;
            }
        }
        return $retorno;
    }

    public static function ObtenerListaEncuestasPorPromedio($promedio){

        $retorno = false;
        $listaEncuestas = array();

        if($promedio >= 0 && $promedio <= 10){

            $encuestas = Encuesta::ObtenerTodasLasEncuestas();
            if($encuestas){
                foreach($encuestas as $escuesta){
                    if($escuesta->ObtenerPromedioPuntaje() >= $promedio){
                        array_push($listaEncuestas, $escuesta);    
                    }
                }
            }
            if(count($listaEncuestas) > 0){
                $retorno = $listaEncuestas;
            }
        }
        return $retorno;
    }

    public function ObtenerPromedioPuntaje(){

        $promedio = 0;
        $acum = $this->puntosCocinero + $this->puntosMesa + $this->puntosMozo + $this->puntosResto;
        $promedio = $acum/4;
        return $promedio;
    }
}

?>