<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\/db/accesoDatos.php';

class Log {

    public $id;
    public $idUsuario;
    public $accion;
    public $fechaHoraAccion;

    public function __construct($id, $idUsuario, $accion, $fechaHoraAccion) {

        $fecha = $fechaHoraAccion;
        $this -> id = $id;
        $this -> idUsuario = $idUsuario;
        $this -> accion = $accion;
        $this -> fechaHoraAccion = $fecha;
    }

    public function GuardarLog() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "INSERT INTO logs (idUsuario, accion, fechaHoraAccion) VALUES (:idUsuario, :accion, :fechaHoraAccion)";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $fecha = $this -> fechaHoraAccion -> format('Y-m-d H:i:s');
        $consulta -> bindParam(':idUsuario', $this -> idUsuario);
        $consulta -> bindParam(':accion', $this -> accion);
        $consulta -> bindParam(':fechaHoraAccion', $fecha);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> RetornarUltimoIdInsertado();
        }
        return $retorno;
    }

    public static function ObtenerTodosLogs() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();

        $query = "SELECT * FROM logs";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $arrayObtenido = array();
            $logs = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $log){
                $logAux = new Log($log->id, $log->idUsuario, $log->accion, $log->fechaHoraAccion);
                $logs[] = $logAux;
            }
            $retorno = $logs;
        }
        return $retorno;
    }

}
?>