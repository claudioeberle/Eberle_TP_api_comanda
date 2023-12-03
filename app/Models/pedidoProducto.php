<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\//db/accesoDatos.php';

class PedidoProducto {
    public $id;
    public $idProducto;
    public $producto;
    public $cantidad;
    public $tiempoEstimado;
    public $fechaAlta;
    public $fechaResolucion;
    public $estado;
    public $sector;
    public $precio;
    public $codigoPedido;

    public function __construct($id, $idProducto, $cantidad, $tiempoEstimado, $fechaAlta, $fechaResolucion, $estado, $codigoPedido) {
        $this -> id = $id;
        $this -> idProducto = $idProducto;
        $this -> producto = Producto::ObtenerPorID($idProducto);
        $this -> cantidad = $cantidad;
        $this -> tiempoEstimado = $tiempoEstimado;
        $this -> fechaAlta = $fechaAlta;
        $this -> fechaResolucion = $fechaResolucion;
        $this -> estado = $estado;
        $this -> sector = $this->producto->sector;
        $this -> precio = $this->producto->precio*$this->cantidad;
        $this -> codigoPedido = $codigoPedido;

    }

    public function GuardarPedidoProducto() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $fechaAlta = $this -> fechaAlta -> format('Y-m-d H:i:s');
        if($this->fechaResolucion == null){
            $fechaResolucion = null;
        } else {
            $fechaResolucion = $this->fechaResolucion-> format('d-m-Y H:i:s');
        }

        $consulta = $objetoAccesoDatos -> RetornarConsulta("INSERT INTO pedidoproducto (idProducto, cantidad, tiempoEstimado, fechaAlta, fechaResolucion, estado, codigoPedido) 
        VALUES (:idProducto, :cantidad, :tiempoEstimado, :fechaAlta, :fechaResolucion, :estado, :codigoPedido)");
        $consulta->bindParam(':idProducto', $this -> idProducto);
        $consulta->bindParam(':cantidad', $this -> cantidad);
        $consulta->bindParam(':tiempoEstimado', $this -> tiempoEstimado);
        $consulta->bindParam(':fechaAlta', $fechaAlta);
        $consulta->bindParam(':fechaResolucion', $fechaResolucion);
        $consulta->bindParam(':estado', $this -> estado);
        $consulta->bindParam(':codigoPedido', $this -> codigoPedido);


        $resultado = $consulta -> execute();
        if ($resultado) {
            
            $retorno = $objetoAccesoDatos -> RetornarUltimoIdInsertado();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosPedidoProducto() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM pedidoproducto";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $resultado = $consulta->execute();
        if ($resultado) {
            $arrayObtenido = array();
            $listaPedidoProducto = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $pedidoProducto){
                $pedProd = new PedidoProducto($pedidoProducto->id, $pedidoProducto->idProducto, $pedidoProducto->cantidad, $pedidoProducto->tiempoEstimado, $pedidoProducto->fechaAlta, $pedidoProducto->fechaResolucion, $pedidoProducto->estado, $pedidoProducto->codigoPedido);
                $listaPedidoProducto[] = $pedProd;
            }
            $retorno = $listaPedidoProducto;
        }
        return $retorno;
    }

    public static function ObtenerPorID($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM pedidoproducto WHERE id = :id";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $pedidoProducto = $consulta->fetchObject();
            if($pedidoProducto){
                $pedProd = new PedidoProducto($pedidoProducto->id, $pedidoProducto->idProducto, $pedidoProducto->cantidad, $pedidoProducto->tiempoEstimado, $pedidoProducto->fechaAlta, $pedidoProducto->fechaResolucion, $pedidoProducto->estado, $pedidoProducto->codigoPedido);
                $retorno = $pedProd;
            }
        }
        return $retorno;
    }

    public static function ObtenerListaPorCodigoPedido($codigoPedido) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM pedidoproducto WHERE codigoPedido = :codigoPedido";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':codigoPedido', $codigoPedido);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $arrayObtenido = array();
            $listaPedidoProducto = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $pedidoProducto){
                $pedProd = new PedidoProducto($pedidoProducto->id, $pedidoProducto->idProducto, $pedidoProducto->cantidad, $pedidoProducto->tiempoEstimado, $pedidoProducto->fechaAlta, $pedidoProducto->fechaResolucion, $pedidoProducto->estado, $pedidoProducto->codigoPedido);
                $listaPedidoProducto[] = $pedProd;
            }
            $retorno = $listaPedidoProducto;
        }
        return $retorno;
    }

    public static function ObtenerListaPorSector($sector) {
        $retorno = false;
        $listaFiltrada = array();
        $listaPedidoProducto = PedidoProducto::ObtenerTodosLosPedidoProducto();
        foreach($listaPedidoProducto as $pedidoProd){
            if($pedidoProd->sector === $sector){
                array_push($listaFiltrada, $pedidoProd);
            }
        }
        
        if(count($listaFiltrada) > 0){
            $retorno = $listaFiltrada;
        }
        return $retorno;
    }

    public static function Eliminar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("DELETE FROM pedidoproducto WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public function Modificar() {
        $retorno = false;
        $fechaResolucion = null;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("UPDATE pedidoproducto SET tiempoEstimado = :tiempoEstimado, fechaResolucion = :fechaResolucion, estado = :estado WHERE id = :id");
        if($this -> fechaResolucion !== null && !is_string($this -> fechaResolucion)){
            $fechaResolucion = $this -> fechaResolucion -> format('Y-m-d H:i:s');
        }
        $consulta->bindParam(':id', $this -> id);
        $consulta->bindParam(':tiempoEstimado', $this -> tiempoEstimado);
        $consulta->bindParam(':fechaResolucion', $fechaResolucion);
        $consulta->bindParam(':estado', $this -> estado);

        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public function CambiarEstado($nuevoEstado, $tiempoPreparacion = false) {

        $retorno = false;

        if($nuevoEstado === 'pendiente' || $nuevoEstado === 'en preparacion' || $nuevoEstado === 'listo para servir' 
        || $nuevoEstado === 'entregado' || $nuevoEstado === 'cancelado'){

            if($nuevoEstado === 'en preparacion' && $tiempoPreparacion !== false){
                
                $this->estado = $nuevoEstado;
                $this->tiempoEstimado = $tiempoPreparacion;
                $this->Modificar();
                $retorno = true;
            }

            if($nuevoEstado !== 'pendiente'){
                $this->estado = $nuevoEstado;
                $this->Modificar();
                $retorno = true;
            }
        }
        return $retorno;
    }

    public function CalcularFechaResolucionIdeal(){
        if(is_string($this -> fechaAlta)){
            $fechaAlta = new DateTime($this -> fechaAlta);
        } else {
            $fechaAlta = $this -> fechaAlta;
        }
        $tiempoEstimado = $this -> tiempoEstimado;
        $fechaResolucion = $fechaAlta;
        if($fechaResolucion instanceof DateTime){
            $fechaResolucion->modify("+$tiempoEstimado minutes");
        }
       return $fechaResolucion;
    }

    public function ObtenerTiempoDemora(){
        $horaActual = new DateTime();
        $tiempoEstimado = $this->tiempoEstimado;
    
        if(is_string($this->fechaAlta)){
            $fechaAlta = new DateTime($this->fechaAlta);
        } else {
            $fechaAlta = $this->fechaAlta;
        }
        $horaResolucion = clone $fechaAlta;
    
        $horaResolucion->modify("+$tiempoEstimado minutes");
    
        if ($horaActual > $horaResolucion) {
            $diferencia = $horaActual->diff($horaResolucion);
            $minutosDiferencia = $diferencia->days * 24 * 60 + $diferencia->h * 60 + $diferencia->i;
    
            return $minutosDiferencia;
        }
    
        return 0;
    }

}

?>