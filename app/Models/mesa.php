<?php

require_once './db/AccesoDatos.php';
require_once './Models/mesaPedidos.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Utils/utiles.php';

class Mesa {

    public $id;
    public $estado;
    public $codigoMesa;
    public $pedidos;
    public $fecha;
    public $idPosicion;
    public $idMozo;
    public $facturada;

    public function __construct($id, $estado, $codigoMesa, $fecha, $idPosicion, $idMozo, $facturada) {

        if($id === false){
            $this->id = -1;
        } else {
            $this->id = $id;
        }

        if($estado === false){
            $this -> estado = "pendiente";
        } else {
            $this -> estado = $estado;
        }
        $this->fecha = $fecha;
        $this -> codigoMesa = $codigoMesa;
        $this -> pedidos = array();
        $this -> idPosicion = $idPosicion;
        $this -> idMozo = $idMozo;
        $this -> facturada = $facturada;

    }

    public function GuardarMesa() {
        $retorno = false;
        $fecha = $this -> fecha->format('Y-m-d H:i:s');
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("INSERT INTO mesas (estado, codigoMesa, fecha, idPosicion, idMozo, facturada) VALUES (:estado, :codigoMesa, :fecha, :idPosicion, :idMozo, :facturada)");
        $consulta -> bindParam(":estado", $this -> estado);
        $consulta -> bindParam(":codigoMesa", $this -> codigoMesa);
        $consulta -> bindParam(":fecha", $fecha);
        $consulta -> bindParam(":idPosicion", $this -> idPosicion);
        $consulta -> bindParam(":idMozo", $this -> idMozo);
        $consulta -> bindParam(":facturada", $this -> facturada);

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
            $mesa = new Mesa($i->id, $i->estado, $i->codigoMesa, $i->fecha, $i->idPosicion, $i->idMozo, $i->facturada);
            $mesa->ActualizarListaPedidos();
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
                $mesa = new Mesa($mesaObtenida->id, $mesaObtenida->estado, $mesaObtenida->codigoMesa, $mesaObtenida->fecha, $mesaObtenida->idPosicion, $mesaObtenida->idMozo, $mesaObtenida->facturada);
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
                $mesa = new Mesa($mesaObtenida->id, $mesaObtenida->estado, $mesaObtenida->codigoMesa, $mesaObtenida->fecha, $mesaObtenida->idPosicion, $mesaObtenida->idMozo, $mesaObtenida->facturada);
                $retorno = $mesa;
            }
        }
        return $retorno;
    }

    public function CambiarEstado($nuevoEstado, $puestoUsuario) {
        $retorno = false;
        //*“con cliente esperando pedido” ,”con cliente comiendo”,“con cliente pagando” y “cerrada”.
        if(($this->HayPedidosPendientes() && $nuevoEstado === "con cliente esperando pedido") 
        || (!$this->HayPedidosPendientes() && ($nuevoEstado === "con cliente comiendo" || $nuevoEstado === "con cliente pagando" || $nuevoEstado === "cerrada"))){

            if($nuevoEstado === "cerrada" && $puestoUsuario !== 'socio'){

                $retorno = false;

            } else {
                $this->estado = $nuevoEstado;
                $retorno = $this->Modificar();
            }
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

        $this->pedidos = self::ObtenerPedidosDeUnaMesa($this->id);
    }

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("UPDATE mesas SET estado = :estado, facturada = :facturada WHERE id = :id");
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':estado', $this -> estado);
        $consulta -> bindParam(':facturada', $this -> facturada);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public function AgregarPedido($idPedido){

        $retorno = false;
        $relacionMesaPedido = new MesaPedidos(0, $this->id, $idPedido);
        if($relacionMesaPedido->Guardar()){
            $retorno = true;
            $pedido = Pedido::ObtenerPorID($idPedido);
            array_push($this->pedidos, $pedido);
        }
        return $retorno;
    }

    public static function AsignarCodigoMesa(){

        $reintentos = 0;

        $ruta = 'C:\xampp\htdocs\api-comanda-3\app\/db/codigosMesa.csv';
        do{
            $codigo = Utiles::ObtenerCodigoAlfaNumAleatorio(5);
            $reintentos = $reintentos + 1;

        }while((Utiles::ValidarExistenciaCodigo($codigo, $ruta) || $codigo === "") && $reintentos < 5);

        if($reintentos >= 5){
            $codigo = false;
        } else {
            $retorno = Utiles::GuardarCodigoEnCSV($codigo, $ruta);
        }
        return $codigo;
    }

    public function HayPedidosPendientes(){

        $retorno = false;
        $this->ActualizarListaPedidos();
        foreach($this->pedidos as $pedido){
            if($pedido){
                $pedidosProd = PedidoProducto::ObtenerListaPorCodigoPedido($pedido->codigoPedido);
                if($pedidosProd){
                    foreach($pedidosProd as $pedProd){
                        if($pedProd->estado !== 'entregado' && $pedProd->estado !== 'anulado' ){
                            $retorno = true;
                            break;
                        }
                    }
                }  
            }
        }
        return $retorno;
    }

    public function ObtenerPedidosPendientes(){

        $retorno = false;
        $pedidosPendientes = array();
        $this->ActualizarListaPedidos();
        foreach($this->pedidos as $pedido){
           if($pedido instanceof Pedido){
                $pedidosProd = PedidoProducto::ObtenerListaPorCodigoPedido($pedido->codigoPedido);
                if($pedidosProd){
                    foreach($pedidosProd as $pedProd){
                        if($pedProd->estado === 'pendiente' || $pedProd->estado === 'en preparacion' || $pedProd->estado === 'listo para servir'){
                            array_push($pedidosPendientes, $pedProd);
                        }
                    }
                }
           }
        }

        if(count($pedidosPendientes) > 0){
            var_dump($pedidosPendientes);
            $retorno = $pedidosPendientes;
        }
        return $retorno;
    }

    public static function ObtenerPedidosDeUnaMesa($idMesa){

        $listaDePedidos = array();
        $mesaPedidos = MesaPedidos::ObtenerMesaPedidos($idMesa);
        if($mesaPedidos !== false){
            foreach($mesaPedidos as $mesaPedido){
                $pedido = Pedido::ObtenerPorID($mesaPedido->idPedido);
                if($pedido !== false){
                    $pedidosProd = PedidoProducto::ObtenerListaPorCodigoPedido($pedido->codigoPedido);
                    if($pedidosProd){
                        foreach($pedidosProd as $pedProd){
                            array_push($listaDePedidos, $pedProd);
                        }
                    }
                }
            }
        }
        return $listaDePedidos;
    }

    public function GenerarFacturacion(){
        $retorno = 0;
        $importe = 0;
        $pedidos = Mesa::ObtenerPedidosDeUnaMesa($this->id);
        if($pedidos){

            foreach($pedidos as $pedido){
                $pedidosProductos = PedidoProducto::ObtenerListaPorCodigoPedido($pedido->codigoPedido);
                if($pedidosProductos){
                    foreach($pedidosProductos as $pedProd){
                        $importe += ($pedProd->precio);
                    }
                }
            }
            $retorno = $importe;
        }
        return $retorno;
    }

    public function GenerarDetalleFacturacion(){
        $retorno = false;
        $detalles = array();
        $pedidos = Mesa::ObtenerPedidosDeUnaMesa($this->id);
        if($pedidos){

            foreach($pedidos as $pedido){
                if($pedido instanceof Pedido){
                    $pedidosProductos = PedidoProducto::ObtenerListaPorCodigoPedido($pedido->codigoPedido);
                    if($pedidosProductos){
                        foreach($pedidosProductos as $pedProd){
                            array_push($detalles, [$pedProd->producto->nombre => " {$pedProd->producto->precio}", 'Cantidad: ' => $pedProd->cantidad]);
                        }
                    }
                }
            }
            $retorno = $detalles;
        }
        return $retorno;
    }


}

?>