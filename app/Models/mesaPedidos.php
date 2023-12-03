<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\/db/accesoDatos.php';

class MesaPedidos{

    public $id;
    public $idMesa;
    public $idPedido;

    public function __construct($id, $idMesa, $idPedido){

        $this->id = $id;
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

    public static function ObtenerMesaPedidos($idMesa){
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM mesa_pedidos WHERE idMesa = :idMesa";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':idMesa', $idMesa);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $listaMesaPedido = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $mesaPed){
                $mesaAux = new MesaPedidos($mesaPed->id, $mesaPed->idMesa, $mesaPed->idPedido);
                $listaMesaPedido[] = $mesaAux;
            }
            $retorno = $listaMesaPedido;
        }
        return $retorno;
    }

    
}
?>