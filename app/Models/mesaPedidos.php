<?php

class MesaPedidos{

    public $idMesa;
    public $idPedido;

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