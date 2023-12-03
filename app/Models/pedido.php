<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/pedidoProducto.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/mesaPedidos.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/db/accesoDatos.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Utils/utiles.php';



class Pedido {

    public $id;
    public $codigoMesa;
    public $nombreCliente;
    public $codigoPedido;
    public $fecha;
    public $pedidosProducto;

    public function __construct($id, $codigoMesa, $nombreCliente, $codigoPedido = false, $fecha = false) {
        $this -> id = $id;
        $this -> codigoMesa = $codigoMesa;
        $this -> nombreCliente = $nombreCliente;
        
        if($codigoPedido){
            $this -> codigoPedido = $codigoPedido;
        } else{
            $this -> codigoPedido = self::AsignarCodigoPedido();
        }

        if($fecha && is_string($fecha)){
            $this -> fecha = new DateTime($fecha);
        } else if($fecha){
            $this -> fecha = $fecha;
        } else {
            $this -> fecha = new DateTime();
        }

        $this -> pedidosProducto = array();
    }
 
    public function GuardarPedido() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("INSERT INTO pedidos (codigoMesa, nombreCliente, codigoPedido, fecha) VALUES (:codigoMesa, :nombreCliente, :codigoPedido, :fecha)");
        $fecha = $this -> fecha->format('Y-m-d H:i:s');
        $consulta -> bindParam(':codigoMesa', $this -> codigoMesa);
        $consulta -> bindParam(':nombreCliente', $this -> nombreCliente);
        $consulta -> bindParam(':codigoPedido', $this -> codigoPedido);
        $consulta -> bindParam(':fecha', $fecha);
        $resultado = $consulta -> execute();

        if ($resultado) {

            $retorno = $this -> codigoPedido;
        }
        return $retorno;
    }

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("UPDATE pedidos SET nombreCliente = :nombreCliente WHERE id = :id");
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':nombreCliente', $this -> nombreCliente);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public static function ObtenerTodosLosPedidos() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM pedidos";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $arrayObtenido = array();
            $pedidos = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $aux){
                $pedido = new Pedido($aux->id, $aux->codigoMesa, $aux->nombreCliente, $aux->codigoPedido, $aux->fecha);
                $pedido->ActualizarPedidosProducto();
                $pedidos[] = $pedido;
            }
            $retorno = $pedidos;
        }
        return $retorno;
    }

    public static function ObtenerPorID($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM pedidos WHERE id = :id";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $aux = $consulta->fetchObject();
            if($aux){
                $nuevoPedido = new Pedido($aux->id, $aux->codigoMesa, $aux->nombreCliente, $aux->codigoPedido, $aux->fecha);
                $nuevoPedido->ActualizarPedidosProducto();
                $retorno = $nuevoPedido;
            }
        }
        return $retorno;
    }

    public static function ObtenerUltimoPedidoPorMesa($codigoMesa) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("SELECT * FROM pedidos WHERE codigoMesa = :codigoMesa ORDER BY fecha DESC LIMIT 1");
        $consulta -> bindParam(':codigoMesa', $codigoMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $aux = $consulta->fetchObject();
            if($aux){
                $nuevoPedido = new Pedido($aux->id, $aux->codigoMesa, $aux->nombreCliente, $aux->codigoPedido, $aux->fecha);
                $nuevoPedido->ActualizarPedidosProducto();
                $retorno = $nuevoPedido;
            }
        }
        return $retorno;
    }

    public static function ObtenerPorCodigoPedido($codigoPedido) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM pedidos WHERE codigoPedido = :codigoPedido";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':codigoPedido', $codigoPedido);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $aux = $consulta->fetchObject();
            if($aux){
                $nuevoPedido = new Pedido($aux->id, $aux->codigoMesa, $aux->nombreCliente, $aux->codigoPedido, $aux->fecha);
                $nuevoPedido->ActualizarPedidosProducto();
                $retorno = $nuevoPedido;
            }
        }
        return $retorno;
    }

    public static function ObtenerTiempoRestante($codigoMesa, $codigoPedido) {

        /* $retorno = [ "Producto" => "", "mensaje" => "" ];

        $pedido = self::ObtenerPorCodigoPedido($codigoPedido);
        if($pedido !== false){

            $producto = Producto::ObtenerPorID($pedido->idProducto);
            if($producto !== false){

                $retorno['Producto'] = $producto->nombre;
                if($pedido->codigoMesa === $codigoMesa){

                    if($pedido->estado === 'pendiente'){

                        $retorno['mensaje'] = "Se pedido aún no ha sido asignado - Intente en unos minutos";

                    } else if($pedido->estado === 'listo para servir'){

                        $retorno['mensaje'] = "Su pedido ya está listo. Se lo servirán en un momento";

                    } else if($pedido->estado === 'entregado'){

                        $retorno['mensaje'] = "Su pedido ya fue entregado";

                    } else {

                        $retorno['mensaje'] = "Se pedido aún no ha sido asignado - Intente en unos minutos";
                            
                        $tiempoFaltante = $pedido->minutosFaltantesParaResolucion();

                        if($tiempoFaltante === 0){

                            $retorno['mensaje'] = "En breve estara terminado";

                        } else {

                            $retorno['mensaje'] = "Faltan " . $tiempoFaltante . " minutos para que este listo";
                        }
                    }
                } else {
                    $retorno['mensaje'] = "El pedido no corresponde a la mesa";
                }
            } else {
                $retorno['mensaje'] = "No se encontró el producto";
            }
        } else {
            $retorno['mensaje'] = "No se encontró el pedido";
        }
        return $retorno; */
    }

    public static function ObtenerPedidosPorSector($sector, $pendientes = true) {

        /* $pedidosSector = array();
        $pedidos = Pedido::ObtenerTodosLosPedidos();
        if($pedidos !== false){

            foreach($pedidos as $pedido){
                if($pendientes){
                    if($pedido->estado === 'pendiente'){
                        $sectorPedido = $pedido->ObtenerSector();
                        if($sectorPedido === $sector){
                            array_push($pedidosSector, $pedido);
                        }
                    }
                } else{
                    $sectorPedido = $pedido->ObtenerSector();
                    if($sectorPedido === $sector){
                        array_push($pedidosSector, $pedido);
                    }
                }
            }
        }
        return $pedidosSector; */
    }

    public function ObtenerSector(){
       /*  $sectorPedido = false;
        $producto = Producto::ObtenerPorID($this->idProducto);
        if($producto !== false){
            $sectorPedido = $producto->sector;
        }
        return $sectorPedido; */
    }

    private static function AsignarCodigoPedido(){

        $reintentos = 0;

        $ruta = './db/codigosPedido.csv';
        do{
            $codigo = Utiles::ObtenerCodigoAlfaNumAleatorio(5);
            $reintentos = $reintentos + 1;

        }while((Utiles::ValidarExistenciaCodigo($codigo, $ruta) || $codigo === "") && $reintentos < 5);

        if($reintentos >= 5){
            $codigo = false;
        } else {
            Utiles::GuardarCodigoEnCSV($codigo, $ruta);
        }
        return $codigo;
    }

    public function ActualizarPedidosProducto(){
        $listaPedidosProducto = PedidoProducto::ObtenerListaPorCodigoPedido($this->codigoPedido);
        $this->pedidosProducto = $listaPedidosProducto;
    }
}

?>