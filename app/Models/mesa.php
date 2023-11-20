<?php

require_once './db/AccesoDatos.php';
require_once 'C:\xampp\htdocs\zz-api-comanda\app\/Utils/utiles.php';

class Mesa {

    public $id;
    public $estado;
    public $codigoMesa;
    public $pedidos;
    public $fecha;

    public function __construct($id, $estado, $codigoMesa, $fecha) {

        if($id === false){
            $this->id = -1;
        } else {
            $this->id = $id;
        }

        if($estado === false){
            $this -> estado = "cerrada";
        } else {
            $this -> estado = $estado;
        }
        $this->fecha = $fecha;
        $this -> codigoMesa = $codigoMesa;
        $this -> pedidos = array();
    }

    public function GuardarMesa() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("INSERT INTO mesas (estado, codigoMesa, fecha) VALUES (:estado, :codigoMesa, :fecha)");
        $consulta -> bindParam(":estado", $this -> estado);
        $consulta -> bindParam(":codigoMesa", $this -> codigoMesa);
        $consulta -> bindParam(":fecha", $this -> fecha->format('Y-m-d H:i:s'));

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $this -> codigoMesa;
        }
        return $retorno;
    }

    public static function ObtenerTodasLasMesas() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM mesas";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> execute();
        $arrayObtenido = array();
        $mesas = array();
        $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
        foreach($arrayObtenido as $i){
            $mesa = new Mesa($i->id, $i->estado, $i->codigoMesa, $i->fecha);
            $mesas[] = $mesa;
        }
        return $mesas;
    }

    public static function ObtenerPorID($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM mesas WHERE id = :id";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $mesaObtenida = $consulta->fetchObject();
            if($mesaObtenida){
                $mesa = new Mesa($mesaObtenida->id, $mesaObtenida->estado, $mesaObtenida->codigoMesa, $mesaObtenida->fecha);
                $retorno = $mesa;
            }
        }
        return $retorno;
    }

    public static function ObtenerPorCodigoMesa($codigoMesa) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM mesas WHERE codigoMesa = :codigoMesa";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':codigoMesa', $codigoMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $mesaObtenida = $consulta->fetchObject();
            if($mesaObtenida){
                $mesa = new Mesa($mesaObtenida->id, $mesaObtenida->estado, $mesaObtenida->codigoMesa, $mesaObtenida->fecha);
                $retorno = $mesa;
            }
        }
        return $retorno;
    }

    public function CambiarEstado($nuevoEstado) {
        $retorno = false;
        //*“con cliente esperando pedido” ,”con cliente comiendo”,“con cliente pagando” y “cerrada”.
        if($nuevoEstado === "con cliente esperando pedido" || $nuevoEstado === "con cliente comiendo" 
        || $nuevoEstado === "con cliente pagando" || $nuevoEstado === "cerrada"){

            $this->estado = $nuevoEstado;
            $retorno = $this->Modificar();
        }
        return $retorno;
    }

    public static function Eliminar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("DELETE from mesas WHERE id = :id");
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

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("UPDATE mesas SET estado = :estado WHERE id = :id");
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':estado', $this -> estado);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public function AgregarPedido($idPedido){

        $retorno = false;
        $relacionMesaPedido = new MesaPedidos($this->id, $idPedido);
        if($relacionMesaPedido->Guardar()){
            $retorno = true;
        }
        return $retorno;
    }

    public static function AsignarCodigoMesa(){

        $reintentos = 0;

        $ruta = 'C:\xampp\htdocs\zz-api-comanda\app\/db/codigosMesa.csv';
        do{
            $codigo = Utiles::ObtenerCodigoAlfaNumAleatorio(5);
            $reintentos = $reintentos + 1;

        }while((Utiles::ValidarExistenciaCodigo($codigo, $ruta) || $codigo === "") && $reintentos < 5);

        if($reintentos >= 5){
            $codigo = false;
        } else {
            $retorno = Utiles::GuardarCodigoEnCSV($codigo, $ruta);
            var_dump($retorno);
        }
        return $codigo;
    }

    public function HayPedidosPendientes(){

        $retorno = false;
        $this->ActualizarListaPedidos();
        foreach($this->pedidos as $pedido){
            if($pedido->estado === 'pendiente' || $pedido->estado === 'en preparacion' || $pedido->estado === 'listo para servir'){
                $retorno = true;
                break;
            }
        }
        return $retorno;
    }

    public function ObtenerPedidosPendientes(){

        $retorno = false;
        $pedidosPendientes = array();
        $this->ActualizarListaPedidos();
        foreach($this->pedidos as $pedido){
            if($pedido->estado === 'pendiente' || $pedido->estado === 'en preparacion' || $pedido->estado === 'listo para servir'){
                array_push($pedidosPendientes, $pedido);
            }
        }

        if(count($pedidosPendientes) > 0){
            $retorno = $pedidosPendientes;
        }
        return $retorno;
    }

}

?>