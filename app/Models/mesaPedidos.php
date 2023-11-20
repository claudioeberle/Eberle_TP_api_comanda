<?php

require_once 'C:\xampp\htdocs\zz-api-comanda\app\/db/accesoDatos.php';

class MesaPedidos{

    public $idMesa;
    public $idPedido;

    public function __construct($idMesa, $idPedido){

        $mesa = Mesa::ObtenerPorID($idMesa);
        $pedido = Pedido::ObtenerPorID($idPedido);

        if($pedido && $mesa){
            $this->idMesa = $idMesa;
            $this->idPedido = $idPedido;
        }
    }

    public function Guardar(){

        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("INSERT INTO mesa_pedidos (idMesa, idPedido) VALUES (:idMesa, :idPedido)");
        $consulta -> bindParam(":idMesa", $this-> idMesa);
        $consulta -> bindParam(":idPedido", $this-> idPedido);

        $resultado = $consulta -> execute();
        if($resultado){
            $retorno = $objetoAccesoDatos->RetornarUltimoIdInsertado();
        }
        return $retorno;
    }

    private static function ObtenerMesaPedidos($idMesa){
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM mesa_pedidos WHERE id_mesa = :id_mesa";
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id_mesa', $idMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('MesaPedidos');
        }
        return $retorno;
    }

    public static function ObtenerPedidosDeUnaMesa($idMesa){

        $listaDePedidos = array();
        $mesaPedidos = self::ObtenerMesaPedidos($idMesa);
        if($mesaPedidos !== false){
            foreach($mesaPedidos as $mesaPedido){
                $pedido = Pedido::ObtenerPorID($mesaPedido->idPedido);
                if($pedido !== null){
                    array_push($listaDePedidos, $pedido);
                }
            }
        }
        return $listaDePedidos;
    }

}
?>