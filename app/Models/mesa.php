<?php

require_once './db/AccesoDatos.php';

class Mesa {

    public $id;
    public $estado;
    public $codigoMesa;
    public $pedidos;
    public $fecha;

    public function __construct($id, $estado, $codigoMesa, $fecha) {

        $this -> id = $id;
        $this -> estado = "cerrada";
        $this -> codigoMesa = $codigoMesa;
        $this -> fecha = $fecha;
        $this -> pedidos = array();
    }


    public function GuardarMesa() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO MESAS (estado, codigoMesa, fecha) VALUES (:estado, :codigoMesa, :fecha)");
        $consulta -> bindParam(":estado", $this -> estado);
        $consulta -> bindParam(":codigoMesa", $this -> codigoMesa);
        $consulta -> bindParam(":fecha", $this -> fecha);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodasLasMesas() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM MESAS";
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Mesa');
        }
        return $retorno;
    }

    public static function ObtenerPorID($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM mesas WHERE id = :id";
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Mesa');
        }
        return $retorno;
    }

    public static function ObtenerPorCodigoMesa($codigoMesa) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM mesas WHERE codigoMesa = :codigoMesa";
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':codigoMesa', $codigoMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Mesa');
        }
        return $retorno;
    }

    public function CambiarEstado($nuevoEstado) {
        $retorno = false;
        if ($this -> estado != $nuevoEstado) {
            $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
            $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE mesas SET estado = :nuevoEstado WHERE id = :id");
            $consulta -> bindParam(':nuevoEstado', $nuevoEstado);
            $consulta -> bindParam(':id', $this -> id);
            $retorno = $consulta -> execute();
        }
        return $retorno;
    }

    public static function Eliminar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("DELETE mesas WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }

        return $retorno;
    }

    public function ActualizarListaPedidos(){

        $this->pedidos = MesaPedidos::ObtenerPedidosDeUnaMesa($this->id);
    }
}

?>