<?php
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/pedido.php';

require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/prodAux.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Interfaces/IApiUsable.php';

class PedidoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {

        $parametros = $request -> getParsedBody();
        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $puestoToken = AutentificadorJWT::ObtenerData($token) -> puesto;

        if (isset($parametros["codigoMesa"]) && isset($parametros["listaProductos"]) && isset($parametros["nombreCliente"])) { 

            $codigoMesa = $parametros["codigoMesa"];
            $cadenaProductos = $parametros["listaProductos"];
            $nombreCliente = $parametros["nombreCliente"];

            $listaProdYCant = $elementos = explode(",", $cadenaProductos);
            $listaProdAux = array();
            
            for($i=0 ; $i < count($listaProdYCant) ; $i+=2){

                array_push($listaProdAux, new ProdAux(intval($listaProdYCant[$i]), intval($listaProdYCant[$i+1])));
            }

            $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);

            if ($mesa) {

                $pedidoAux = new Pedido(0, $mesa->codigoMesa, $nombreCliente, false, false);
                if($pedidoAux->codigoPedido !== false){

                    $resultado = $pedidoAux -> GuardarPedido();
                    $pedido = Pedido::ObtenerPorCodigoPedido($resultado);

                    if($pedido){

                        if($request -> getUploadedFiles()){
                            $fotoMesa = $request -> getUploadedFiles()['foto'];
                            self::GuardarFotoMesa($pedido->codigoPedido, $fotoMesa);
                        }

                        foreach($listaProdAux as $prodAux){

                            $pedidoProductoAux = new PedidoProducto(0, $prodAux->idProducto, $prodAux->cantidad, 0, new DateTime(), null, 'pendiente', $pedido->codigoPedido);
                            $pedidoProductoAux->GuardarPedidoProducto();
                            $pedidoProducto = PedidoProducto::ObtenerPorID($pedidoProductoAux->id);
                            array_push($pedido->pedidosProducto, $pedidoProducto);
                        }
                        $mesa -> CambiarEstado("con cliente esperando pedido", $puestoToken);
                        $mesa->AgregarPedido($pedido->id);

                        if ($resultado) {

                            $payload = json_encode(array("Resultado" => "Codigo de nuevo pedido: {$resultado}"));

                        } else {
                            $payload = json_encode(array("ERROR" => "Hubo un error durante la creacion del pedido"));
                        }
                    } else{
                        $payload = json_encode(array("ERROR" => "Hubo un error el alta del pedido"));
                    }
                } else {
                    $payload = json_encode(array("ERROR" => "Hubo un error en la asignacion de codigo pedido - Reintente la carga"));
                }
                
            } else {
                $payload = json_encode(array("ERROR" => "No se encontraron la mesa o el producto - Revise los datos"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo pedido son: nombreCliente, codigoMesa, listaProductos, foto(opcional)"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Pedido::ObtenerTodosLosPedidos();

        if (is_array($lista)) {

            $payload = json_encode(array("Pedidos" => $lista));

        } else {

            $payload = json_encode(array("ERROR" => "No se pudo obtener los pedidos"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPorCodigo($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($args["codigoPedido"])) {

            $codigo = $args["codigoPedido"];
            $pedido = Pedido::ObtenerPorCodigoPedido($codigo);

            if ($pedido !== false) {

                $payload = json_encode(array("Pedido" => $pedido));

            } else {

                $payload = json_encode(array("ERROR" => "Hubo un error al obtener el pedido"));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parametro 'codigoPedido' es obligatorio."));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerEstadoPedido($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        
        if (isset($parametros["codigoMesa"]) && isset($parametros["codigoPedido"])) {

            $codigoMesa = $parametros["codigoMesa"];
            $codigoPedido = $parametros["codigoPedido"];

            $pedidosProducto = PedidoProducto::ObtenerListaPorCodigoPedido($codigoPedido);
            $resolucion = array();

            foreach($pedidosProducto as $pedProd){

                switch($pedProd->estado){

                    case 'pendiente':
                        array_push($resolucion, [$pedProd->producto->nombre => 'Pendiente de preparacion']);
                        break;
                    case 'en preparacion':
                        array_push($resolucion, [$pedProd->producto->nombre => 'En preparacion', 'Hora Resolucion' => ($pedProd->CalcularFechaResolucionIdeal())->format('H:i:s')]);
                        break;
                    case 'listo para servir':
                        array_push($resolucion, [$pedProd->producto->nombre => 'Listo para servir']);
                        break;
                    case 'entregado':
                        array_push($resolucion, [$pedProd->producto->nombre => 'Entregado']);
                        break; 
                }
            }
            $payload = json_encode($resolucion);

        } else {

            $payload = json_encode(array("ERROR" => "Los parámetros 'codigoMesa' y 'codigoPedido' son obligatorios para traer el tiempo de pedido"));
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidosPendientesPorSector($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $sectorToken = AutentificadorJWT::ObtenerData($token) -> sector;
        $listaFiltrada = array();

        if($sectorToken){

            $listaPedidosProductoDelSector = PedidoProducto::ObtenerListaPorSector($sectorToken);
            if ($listaPedidosProductoDelSector && count($listaPedidosProductoDelSector) > 0) {
                
                foreach($listaPedidosProductoDelSector as $pedProd){

                    if($pedProd->estado !== 'entregado' && $pedProd->estado !== 'anulado'){
                        array_push($listaFiltrada, $pedProd);
                    }
                }

                if (count($listaFiltrada) > 0) {

                    $payload = json_encode(array("Pedidos del sector" => $listaFiltrada));

                } else {
                    $payload = json_encode(array("Lista" => "No hay pedidos pendientes"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No hay pedidos en el sector o no se pudieron obtener"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "No se pudo obtener el sector del usuario"));
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstadoPedidoProducto($request, $response, $args) {
        $parametros = $request -> getParsedBody ();
        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $puestoToken = AutentificadorJWT::ObtenerData($token) -> puesto;

        if (isset($parametros["idPedido"])) {
            $idPedidoProd = $parametros["idPedido"];
            $payload = json_encode(array("ERROR" => "Hubo un error al cambiar el estado"));

            $pedidoProducto = PedidoProducto::ObtenerPorID($idPedidoProd, true);
            if ($pedidoProducto) {

                $pedido = Pedido::ObtenerPorCodigoPedido($pedidoProducto -> codigoPedido);
                if ($pedido) {
                    $mesa = Mesa::ObtenerPorCodigoMesa($pedido->codigoMesa);
                    if($mesa){

                        $nuevoEstado = false;
                        $tiempoPreparacion = "";

                        switch($pedidoProducto->estado) {

                            case 'pendiente':

                                if (isset($parametros["tiempoPreparacion"])) {
                                    if($puestoToken !== 'mozo' || $puestoToken !== 'socio'){
                                        $nuevoEstado = 'en preparacion';
                                        $tiempoPreparacion = $parametros["tiempoPreparacion"];
                                        $mesa -> CambiarEstado("con cliente esperando pedido", $puestoToken);
                                    } else {
                                        $payload = json_encode(array("ERROR" => "No esta autorizado para cambiar el estado del pedido"));
                                    }
                                } else {
                                    $payload = json_encode(array("ERROR" => "indique 'tiempoPreparacion'"));
                                }
                                break;

                            case 'en preparacion':
                                if($puestoToken !== 'mozo' || $puestoToken !== 'socio'){
                                    $nuevoEstado = 'listo para servir';
                                    $tiempoPreparacion = false;
                                    $pedidoProducto->fechaResolucion = new DateTime();
                                    $pedidoProducto->Modificar();
                                } else {
                                    $payload = json_encode(array("ERROR" => "No esta autorizado para cambiar el estado del pedido"));
                                }
                            break;

                            case 'listo para servir':
                                if($puestoToken === 'mozo' || $puestoToken === 'socio'){
                                    $pedidoProducto->fechaResolucion = new DateTime();
                                    $pedidoProducto->Modificar();
                                    $nuevoEstado = 'entregado';
                                    $tiempoPreparacion = false;
                                } else {
                                    $payload = json_encode(array("ERROR" => "No esta autorizado para cambiar el estado del pedido"));
                                }
                            break;

                            default:
                                $nuevoEstado = false;
                            break;
                        }

                        if ($nuevoEstado !== false) {

                            if ($pedidoProducto->CambiarEstado($nuevoEstado, $tiempoPreparacion)) {

                                $payload = json_encode(array("Resultado" => "El estado del pedido fue cambiado a '{$nuevoEstado}'"));
                                if($nuevoEstado === 'entregado'){
                                    $mesa -> CambiarEstado('con cliente comiendo', $puestoToken);
                                }
                            } else {
                                $payload = json_encode(array("ERROR" => "No se pudo modificar el estado del pedido"));
                            }
                        }

                    } else {
                        $payload = json_encode(array("ERROR" => "No se pudo encontrar la mesa vinculada al pedido"));
                    } 
                } else {
                    $payload = json_encode(array("ERROR" => "No se pudo encontrar el pedido buscado"));
                }                      
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar el pedido buscado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'idPedido' es obligatorio"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EliminarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if(isset($parametros["id"])) {

            $idPedido = $parametros["id"];
            $pedido = Pedido::ObtenerPorID($idPedido);

            if ($pedido) {

                $payload = json_encode(array("Pedido" => "pendiente de desarrollo"));

            } else {

                $payload = json_encode(array("ERROR" => "El pedido con el id {$idPedido} no existe o fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'id' es obligatorio"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if(isset($parametros["id"])) {

            $idPedido = $parametros["id"];
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

	public function ModificarUno($request, $response, $args) {
        $modificado = false;
        $parametros = $request -> getParsedBody ();

        if (isset($parametros["id"]) && isset($parametros["nombreCliente"])) {

            $idPedido = $parametros["id"];
            $pedido = Pedido::ObtenerPorID($idPedido);
            if($pedido){

                $nombreCliente = $parametros["nombreCliente"];
                $pedido->nombreCliente = $nombreCliente;
                $pedido->Modificar();
                $modificado = true;

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

    private static function GuardarFotoMesa($codigoPedido, $fotoMesa) {
        $retorno = false;

        if ($fotoMesa -> getError() === UPLOAD_ERR_OK) {
            $path = './Fotos';
    
            if (!file_exists($path)) {
                if (!file_exists('./Fotos')) {
                    mkdir('./Fotos', 0777);
                }
                mkdir($path, 0777);
            }

            $extension = pathinfo($fotoMesa -> getClientFilename(), PATHINFO_EXTENSION);
            $nombreFoto = $codigoPedido . date("Ymd") . '.' . $extension;
            $fotoMesa -> moveTo($path . '/' . $nombreFoto);
    
            $retorno = true;
        } else {
            $retorno = false;
        }
        return $retorno;
    }

    public static function CargarFoto($request, $response, $args){

        $parametros = $request -> getParsedBody ();

        if($request -> getUploadedFiles() && isset($parametros['codigoPedido'])){
            $codigoPedido = $parametros['codigoPedido'];
            $pedido = Pedido::ObtenerPorCodigoPedido($codigoPedido);
            if($pedido){

                $fotoMesa = $request -> getUploadedFiles()['foto'];
                self::GuardarFotoMesa($pedido->codigoPedido, $fotoMesa);
                $payload = json_encode(array("EXITO" => "Foto guardada con exito"));

            } else {
                $payload = json_encode(array("ERROR" => "Son se encontró el pedido indicado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "Los parametros 'foto' y 'codigoPedido son obligatorios"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ObtenerTodosPedidosPendientes($request, $response, $args){

        $payload = json_encode(array("ERROR" => "No se pudieron obtener los pedidos pendientes"));
        $listaFiltrada = array();
        $pedidosProductoPendientes = PedidoProducto::ObtenerTodosLosPedidoProducto();

        if ($pedidosProductoPendientes && count($pedidosProductoPendientes) > 0) {
            
            foreach($pedidosProductoPendientes as $pedProd){

                if($pedProd->estado !== 'entregado' && $pedProd->estado !== 'anulado'){
                    array_push($listaFiltrada, $pedProd);
                }
            }

            if (count($listaFiltrada) > 0) {

                $payload = json_encode(array("Pedidos Pendientes" => $listaFiltrada));

            } else {
                $payload = json_encode(array("Lista" => "No hay pedidos pendientes"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "No hay pedidos o no se pudieron obtener"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerEstadoDeUnPedido($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if(isset($parametros["id"])) {

            $idPedido = $parametros["id"];
            $pedProd = PedidoProducto::ObtenerPorID($idPedido);
            $resolucion = array();

            if ($pedProd) {

                switch($pedProd->estado){

                    case 'pendiente':
                        array_push($resolucion, [$pedProd->producto->nombre => 'Pendiente de preparacion']);
                        break;
                    case 'en preparacion':
                        array_push($resolucion, [$pedProd->producto->nombre => 'En preparacion', 'Hora Resolucion' => ($pedProd->CalcularFechaResolucionIdeal())->format('H:i:s'), 'Tiempo Demora' => $pedProd->ObtenerTiempoDemora() . ' minutos']);
                        break;
                    case 'listo para servir':
                        array_push($resolucion, [$pedProd->producto->nombre => 'Listo para servir']);
                        break;
                    case 'entregado':
                        array_push($resolucion, [$pedProd->producto->nombre => 'Entregado']);
                        break; 
                }
                $payload = json_encode(array("Estado Pedido" => $resolucion));
            } else {
                $payload = json_encode(array("ERROR" => "El pedido con el id {$idPedido} no existe o fue cancelado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'id' es obligatorio"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ObtenerTodosPedidosParaServir($request, $response, $args){
        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $idUsuario = AutentificadorJWT::ObtenerData($token) -> id;
        $payload = json_encode(array("ERROR" => "No se pudieron obtener los pedidos listos"));
        $listaFiltrada = array();
        $pedidosProductoPendientes = PedidoProducto::ObtenerTodosLosPedidoProducto();

        if ($pedidosProductoPendientes && count($pedidosProductoPendientes) > 0) {
            
            foreach($pedidosProductoPendientes as $pedProd){

                $pedido = Pedido::ObtenerPorCodigoPedido($pedProd->codigoPedido);
                if($pedido){

                    $mesa = Mesa::ObtenerPorCodigoMesa($pedido->codigoMesa);
                    if($mesa){
                        if($pedProd->estado === 'listo para servir' && $idUsuario === $mesa->idMozo){
                            array_push($listaFiltrada, $pedProd);
                        }
                    }
                }
            }

            if (count($listaFiltrada) > 0) {

                $payload = json_encode(array("Pedidos listos para servir" => $listaFiltrada));

            } else {
                $payload = json_encode(array("Lista" => "No hay pedidos listos para servir"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "No hay pedidos o no se pudieron obtener"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function PedidosConRetraso($request, $response, $args){
        $payload = json_encode(array("Retrasos" => "No hay pedidos con retrasos"));

        $resolucion = array();
        $pedidosProducto = PedidoProducto::ObtenerTodosLosPedidoProducto();
        if($pedidosProducto){

            foreach($pedidosProducto as $pedProd){
                if($pedProd->estado === 'entregado'){
                    $retraso = $pedProd->ObtenerRetraso();
                    if($retraso){
                        array_push($resolucion, ['Pedido' => $pedProd->codigoPedido, 'Producto' => $pedProd->producto->nombre, 'Hora Alta' => $pedProd->fechaAlta, 'Hora Resolucion' => $pedProd->fechaResolucion, 'Tiempo Retraso' => $retraso . ' minutos']);
                    }
                }
            }
            if(count($resolucion) > 0){
                $payload = json_encode(array("Pedidos con Retraso" => $resolucion));
            }
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>