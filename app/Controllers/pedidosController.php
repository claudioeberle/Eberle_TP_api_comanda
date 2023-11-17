<?php

require_once './middlewares/Validadores.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {

        $parametros = $request -> getParsedBody();

        //$codigoMesa, $idProducto, $nombreCliente, $estado, $fecha

         if (isset($parametros["codigoMesa"]) && isset($parametros["idProducto"]) && isset($parametros["nombreCliente"]) && isset($parametros["estado"])) { 

            $mesa = Mesa::ObtenerPorCodigoIdentificacion($parametros["codigoMesa"]);

            $producto = Producto::ObtenerPorID($parametros["idProducto"]);

            if ($mesa && $producto) {

                //RESOLVER TEMA FOTO
                //$fotoMesa = $request -> getUploadedFiles()['foto'];
                //self::SubirFotoMesa($codigo, $fotoMesa);
                
                $pedido = new Pedido($parametros['codigoMesa'], $parametros['idProducto'], $parametros["nombreCliente"]);
                $resultado = $pedido -> GuardarPedido();
                $mesa -> CambiarEstado("con cliente esperando pedido");

                if (is_string($resultado)) {

                    $payload = json_encode(array("Resultado" => "Codigo de nuevo pedido: {$resultado}"));

                } else {

                    $payload = json_encode(array("ERROR" => "Hubo un error durante la creacion del pedido"));
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

            $listaPedidos = Pedido::ObtenerPedidosPorSector($args["sector"]); // Trae solo los pedidos pendientes de ese sector

            if (is_array($lista)) {
                $payload = json_encode(array("Lista" => $lista));
            } else {
                $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los productos"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'sector' es obligatorio para traer los pedidos por sector"));
        }
        
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstado($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (Validadores::ValidarParametros($parametros, ["id"])) {
            $payload = json_encode(array("ERROR" => "Hubo un error al cambiar el estado"));
            $pedido = Pedido::ObtenerPorID($parametros["id"], true);
            if ($pedido) {
                $nuevoEstado = false;
                $tiempoPreparacion = "";
                switch($pedido -> estado) {
                    case 'pendiente':
                        if (Validadores::ValidarParametros($parametros, ["tiempoPreparacion"])) {
                            $nuevoEstado = 'en preparacion';
                            $tiempoPreparacion = $parametros["tiempoPreparacion"];
                        }
                        break;
                    case 'en preparacion':
                        $nuevoEstado = 'listo para servir';
                    break;
                    case 'listo para servir':
                        $mesa = Mesa::ObtenerPorCodigoIdentificacion($pedido -> codigoMesa);
                        $mesa -> CambiarEstado('con cliente comiendo');
                        $nuevoEstado = 'entregado';
                    break;
                    default:
                        $nuevoEstado = false;
                    break;
                }

                if ($nuevoEstado) {
                    if (Pedido::CambiarEstado($parametros["id"], $nuevoEstado, $tiempoPreparacion)) {
                        $payload = json_encode(array("Resultado" => "El estado del pedido fue cambiado a '{$nuevoEstado}'"));
                    }
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar el pedido buscado o fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para cambiar el estado de un pedido. Si el pedido se pasa a 'en preparacion', también debe pasarse 'tiempoPreparacion'"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        if (Validadores::ValidarParametros($args, ["id"])) {
            $pedido = Pedido::ObtenerPorID($args["id"], true);

            if ($pedido) {
                $payload = json_encode(array("Pedido" => $pedido));
            } else {
                $payload = json_encode(array("ERROR" => "El pedido con el id {$args["id"]} no existe o fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para traer un pedido"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function EliminarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (Validadores::ValidarParametros($args, ["id"])) {
            $payload = json_encode(array("ERROR" => "Hubo un error al cambiar el estado"));
            $pedido = Pedido::ObtenerPorID($args["id"], true);
            if ($pedido) {
                $nuevoEstado = "cancelado";
                if (Pedido::CambiarEstado($args["id"], $nuevoEstado)) {
                    $payload = json_encode(array("Resultado" => "El pedido con el id {$args["id"]} fue cancelado"));
                }       
            } else {
            $payload = json_encode(array("ERROR" => "El pedido con el id {$args["id"]} no existe o ya fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'sector' es obligatorio para traer los pedidos por sector. Si el pedido se pasa a 'en preparacion', también debe pasarse 'tiempoPreparacion'"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (Validadores::ValidarParametros($parametros, [ "id", "idProducto", "nombreCliente" ])) {
            $pedido = Pedido::ObtenerPorID($parametros["id"], true);
            $producto = Producto::ObtenerPorID($parametros["idProducto"], true);
            if ($pedido && $producto) {
                $pedido -> idProducto = $parametros["idProducto"];
                $pedido -> nombreCliente = $parametros["nombreCliente"];
                if ($pedido -> Modificar()) {
                    $payload = json_encode(array("Pedido modificado:" => $pedido));
                } else {
                    $payload = json_encode(array("ERROR" => "No se pudo modificar el pedido"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "Tanto el pedido como el producto deben existir y estar activos para realizar la modificación"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio para modificar un pedido"));
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