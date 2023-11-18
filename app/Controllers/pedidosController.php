<?php

require_once '../Models/pedido.php';
require_once '../Interfaces/IApiUsable.php';

class PedidoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {

        $parametros = $request -> getParsedBody();

        //$codigoMesa, $idProducto, $nombreCliente, $estado, $fecha

         if (isset($parametros["codigoMesa"]) && isset($parametros["idProducto"]) && isset($parametros["nombreCliente"]) && isset($parametros["estado"])) { 

            $codigoMesa = $parametros["codigoMesa"];
            $idProducto = $parametros["idProducto"];
            $nombreCliente = $parametros["nombreCliente"];

            $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);
            $producto = Producto::ObtenerPorID($idProducto);

            if ($mesa && $producto) {

                //RESOLVER TEMA FOTO
                //$fotoMesa = $request -> getUploadedFiles()['foto'];
                //self::SubirFotoMesa($codigo, $fotoMesa);
                
                $pedido = new Pedido($codigoMesa, $idProducto, $nombreCliente);
                if($pedido->codigoPedido !== false){
                    $resultado = $pedido -> GuardarPedido();
                    $mesa -> CambiarEstado("con cliente esperando pedido");
                    $mesa->AgregarPedido($pedido->id);

                    if (is_string($resultado)) {

                        $payload = json_encode(array("Resultado" => "Codigo de nuevo pedido: {$resultado}"));

                    } else {

                        $payload = json_encode(array("ERROR" => "Hubo un error durante la creacion del pedido"));
                    }
                } else {
                    $payload = json_encode(array("ERROR" => "Hubo un error en la asignacion de codigo pedido - Reintente"));
                }
                
            } else {
                $payload = json_encode(array("ERROR" => "No se encontraron la mesa o el producto - Revise los datos"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo pedido son: nombre, codigoMesa, idProducto y idEmpleado"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Pedido::ObtenerTodosLosPedidos();

        if (is_array($lista)) {

            $payload = json_encode(array("Lista" => $lista));

        } else {

            $payload = json_encode(array("ERROR" => "No se pudo obtener los pedidos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPorCodigo($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($args["codigoPedido"])) {

            $codigo = $args["codigoIdentificacion"];
            $pedido = Pedido::ObtenerPorCodigoPedido($codigo);

            if ($pedido !== false) {

                $payload = json_encode(array("Pedido" => $pedido));

            } else {

                $payload = json_encode(array("ERROR" => "Hubo un error al obtener el pedido"));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parámetro 'codigoPedido' es obligatorio."));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTiempoPedido($request, $response, $args) {

        if (isset($args["codigoMesa"]) && isset($args["codigoPedido"])) {
            $codigoMesa = $args["codigoMesa"];
            $codigoPedido = $args["codigoPedido"];

            $resolucion = Pedido::ObtenerTiempoRestante($codigoMesa, $codigoPedido);
            $payload = json_encode($resolucion);

        } else {

            $payload = json_encode(array("ERROR" => "Los parámetros 'codigoMesa' y 'codigoPedido' son obligatorios para traer el tiempo de pedido"));
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidosPendientesPorSector($request, $response, $args) {

        if (isset($args["sector"])) {

            $listaPedidos = Pedido::ObtenerPedidosPorSector($args["sector"], true); // Trae solo los pedidos pendientes de ese sector

            if (is_array($listaPedidos)) {

                $payload = json_encode(array("Lista" => json_encode($listaPedidos)));

            } else if(count($listaPedidos) === 0){

                $payload = json_encode(array("Lista" => "No hay pedidos pendientes"));

            } else {

                $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los productos"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'sector' es obligatorio"));
        }
        
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstadoPedido($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (isset($parametros["id"]) || isset($parametros["codigoPedido"])) {

            $payload = json_encode(array("ERROR" => "Hubo un error al cambiar el estado"));
            
            if(isset($parametros["id"])){

                $pedido = Pedido::ObtenerPorID($parametros["id"], true);

            }else {

                $pedido = Pedido::ObtenerPorCodigoPedido($parametros["codigoPedido"], true);
            }

            if ($pedido) {

                $mesa = Mesa::ObtenerPorCodigoMesa($pedido->codigoMesa);
                if($mesa){

                    $nuevoEstado = false;
                    $tiempoPreparacion = "";

                    switch($pedido -> estado) {

                        case 'pendiente':

                            if (isset($parametros["tiempoPreparacion"])) {
                                $nuevoEstado = 'en preparacion';
                                $tiempoPreparacion = $parametros["tiempoPreparacion"];
                                $mesa -> CambiarEstado("con cliente esperando pedido");
                            } else {
                                $payload = json_encode(array("ERROR" => "Indique 'tiempoPreparacion'"));
                            }
                            break;

                        case 'en preparacion':
                            $nuevoEstado = 'listo para servir';
                            $tiempoPreparacion = false;
                        break;

                        case 'listo para servir':
                            $mesa -> CambiarEstado('con cliente comiendo');
                            $nuevoEstado = 'entregado';
                            $tiempoPreparacion = false;
                        break;

                        default:
                            $nuevoEstado = false;
                        break;
                    }

                    if ($nuevoEstado) {

                        if ($pedido->CambiarEstado($nuevoEstado, $tiempoPreparacion)) {

                            $payload = json_encode(array("Resultado" => "El estado del pedido fue cambiado a '{$nuevoEstado}'"));
                        }
                    }
                } else {
                    $payload = json_encode(array("ERROR" => "No se pudo encontrar la mesa vinculada al pedido"));
                }               
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar el pedido buscado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'id' o 'codigoPedido' es obligatorio"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {

        if(isset($args["id"])) {

            $idPedido = $args["id"];
            $pedido = Pedido::ObtenerPorID($idPedido);

            if ($pedido) {

                $payload = json_encode(array("Pedido" => $pedido));
            } else {

                $payload = json_encode(array("ERROR" => "El pedido con el id {$idPedido} no existe o fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'id' es obligatorio"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function EliminarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if(isset($parametros["id"])) {

            $idPedido = $parametros["id"];
            $payload = json_encode(array("ERROR" => "Hubo un error al cambiar el estado"));
            $pedido = Pedido::ObtenerPorID($idPedido);
            if ($pedido) {

                $nuevoEstado = "cancelado";
                if ($pedido->CambiarEstado($nuevoEstado, false)) {

                    $payload = json_encode(array("Resultado" => "El pedido con el id {$idPedido} fue cancelado"));
                }       
            } else {

            $payload = json_encode(array("ERROR" => "No se encontro el pedido con el id {$idPedido}"));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parametro 'id' es obligatorio"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $modificado = false;
        $parametros = $request -> getParsedBody ();

        if (isset($parametros["id"]) && (isset($parametros["idProducto"]) || isset($parametros["nombreCliente"]))) {

            $idPedido = $parametros["id"];
            $pedido = Pedido::ObtenerPorID($idPedido);
            if($pedido){

                if(isset($parametros["idProducto"])) {

                    $idProducto = $parametros["idProducto"];
                    $producto = Producto::ObtenerPorID($idProducto);
                    if($producto) {

                        $pedido->idProducto = $producto->id;
                        $pedido->Modificar();
                        $modificado = true;

                    } else {
                        $payload = json_encode(array("ERROR" => "No se pudo encontrar el producto indicado"));
                    }
                } else {

                    $nombreCliente = $parametros["nombreCliente"];
                    $pedido->nombreCliente = $nombreCliente;
                    $pedido->Modificar();
                    $modificado = true;
                }
                
                if ($modificado) {

                        $payload = json_encode(array("MODIFICADO" => $pedido));
                }else {

                    $payload = json_encode(array("ERROR" => "No se pudo hacer la modificacion"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se encontró el pedido con {$idPedido}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'id' y el parametro 'idProducto' o 'nombreCliente' es obligatorio para modificar un pedido"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static function SubirFotoMesa($codigoIdentificacion, $fotoMesa) {
        $retorno = false;

        if ($fotoMesa -> getError() === UPLOAD_ERR_OK) {
            $path = './fotos/pedidosDeMesas';
    
            if (!file_exists($path)) {
                if (!file_exists('./fotos')) {
                    mkdir('./fotos', 0777);
                }
                mkdir($path, 0777);
            }

            $extension = pathinfo($fotoMesa -> getClientFilename(), PATHINFO_EXTENSION);
            $nombreFoto = $codigoIdentificacion . date("Ymd") . '.' . $extension;
            $fotoMesa -> moveTo($path . '/' . $nombreFoto);
    
            $retorno = true;
        } else {
            $retorno = false;
        }

        return $retorno;
    }
}

?>