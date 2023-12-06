<?php
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/posiciones.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\Models\mesa.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/pedido.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Interfaces/IApiUsable.php';

class MesasController implements IApiUsable {

    public function CargarUno($request, $response, $args) {

        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $idUsuario = AutentificadorJWT::ObtenerData($token) -> id;
        $codigoMesa = Mesa::AsignarCodigoMesa();
        $posicion = PosicionMesa::ObtenerPosicionLibre();
        if($posicion){
            if($codigoMesa){

                $mesa = new Mesa(false, false, $codigoMesa, new DateTime(), $posicion->id, $idUsuario, false);
                if($mesa){
                    $resultado = $mesa -> GuardarMesa();
                    if($resultado !== false){
                        $payload = json_encode(array("Resultado" => "Se ha creado con éxito una mesa con el codigo: {$resultado}"));
                    } else {
                        $payload = json_encode(array("ERROR" => "Hubo un error durante la asignacion de codigo de mesa"));
                    }
                } else {
                    $payload = json_encode(array("ERROR" => "Hubo un error durante la creacion de la mesa"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "Error al asignar codigo de Mesa - reintente"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "No hay ninguna posicion libre"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Mesa::ObtenerTodasLasMesas();

        if (is_array($lista)) {

            $payload = json_encode(array("Mesas" => $lista));
        } else {

            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todas las mesas"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {

        $parametros = $request -> getParsedBody();

        if (isset($args["codigoMesa"])) {

            $codigoMesa = $args["codigoMesa"];
            $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);

            if ($mesa) {
                $payload = json_encode(array("Mesa" => $mesa));
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el codigo {$codigoMesa}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'codigoMesa' es obligatorio."));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstado($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $puestoToken = AutentificadorJWT::ObtenerData($token) -> puesto;
        
        if (isset($parametros["codigoMesa"]) && isset($parametros["estado"])) {

            $codigoMesa = $parametros["codigoMesa"];
            $estado = $parametros["estado"];
            $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);
            if ($mesa) {
            
                if($mesa->estado !== 'cerrada'){
                    
                    if($estado === 'cerrada' && $mesa->facturada == true){

                        if($estado === 'cerrada' || $estado === 'con cliente pagando'){

                            $mesa->ActualizarListaPedidos();
    
                            if(!$mesa->HayPedidosPendientes()){
                                
                                if($mesa -> CambiarEstado($estado, $puestoToken)){
                                    $payload = json_encode(array("ESTADO" => "Se modifico el estado de la mesa {$codigoMesa} a '{$estado}'"));
                                    if($estado === 'cerrada'){
                                        PosicionMesa::Modificar($mesa->idPosicion, false);
                                    }
                                } else {
                                    $payload = json_encode(array("ERROR" => "Recuerde que solo un 'socio' puede cerrar una mesa"));
                                }
                            } else{
                                $pedidosPendientes = $mesa->ObtenerPedidosPendientes();
                                if($pedidosPendientes !== false){
                                    
                                    foreach($pedidosPendientes as $pedido){
                                        $mensaje = 'La mesa tiene pendientes los pedidos: \n';
                                        $mensaje .= $pedido->codigoPedido . "\n";
                                    }                                
                                    $payload = json_encode(array("ERROR" => $mensaje));
    
                                } else {
                                    $payload = json_encode(array("ERROR" => "Todavia quedan pedidos pendientes"));
                                }
                            }
                        } else {
                            $payload = json_encode(array("ERROR" => "El estado propuesto no esta permitido"));
                        }
                    } else {
                        $payload = json_encode(array("ERROR" => "No puede cerrar una mesa que aun no ha sido facturada"));
                    } 
                } else {
                        $payload = json_encode(array("ERROR" => "La mesa informada ya se encuentra cerrada."));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el código {$codigoMesa}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'codigoMesa' y 'estado' son obligatorios para modificar el estado de una mesa"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EliminarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        $resultado = false;

        if (isset($parametros["codigoMesa"])) {

            $codigoMesa = $parametros["codigoMesa"];
            $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);
            if($mesa){

                $resultado = Mesa::Eliminar($mesa->id);
            }

            if ($resultado) {

                $payload = json_encode(array("Resultado" => "Se ha dado de baja la mesa con el codigo de identificacion {$codigoMesa}"));
            } else {

                $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el codigo de identificacion {$codigoMesa}"));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parametro 'codigoMesa' es obligatorio para dar de baja una mesa"));
        }
        $response->getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {

        $payload = json_encode(array("ERROR" => "Para modificar el estado de una mesa utilice la funcion CambiarEstado"));
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function Facturacion($request, $response, $args) {
        $parametros = $request -> getParsedBody();
        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $puestoToken = AutentificadorJWT::ObtenerData($token) -> puesto;
        $retorno = array();

        if(isset($parametros['codigoMesa'])){

            $codigoMesa = $parametros['codigoMesa'];
            $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);
            if($mesa){
                if(!$mesa->HayPedidosPendientes()){
                    if(!$mesa->facturada){
                        $facturacion = $mesa->GenerarFacturacion();
                        if($facturacion > 0){
                            $mesa->CambiarEstado('con cliente pagando', $puestoToken);
                            array_push($retorno, ['Detalles', $mesa->GenerarDetalleFacturacion()]);
                            array_push($retorno, ['Importe Total' => "$ {$facturacion}"]);
                            $payload = json_encode(array("Factura" => $retorno));
                            $mesa->facturada = true;
                            $mesa->Modificar();
                        } else {
                            $payload = json_encode(array("ERROR" => "Hubo un error en el calculo de la facturacion"));
                        }
                    } else {
                        $payload = json_encode(array("ERROR" => "La factura de esta mesa ya fue emitida"));
                    }
                } else {
                    $payload = json_encode(array("ERROR" => "Aun hay pedidos pendientes para esta mesa - Para facturar debe entregarlos o anularlos"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se encontro una mesa para el codigo {$codigoMesa}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'codigoMesa' es obligatorio para emitir la factura"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function MesaMasUtilizada($request, $response, $args){

        $lista = array();
        $valorMax = 0;
        $numeroMesa = 0;
        $posiciones = PosicionMesa::ObtenerTodasLasPosiciones();
        $mesas = Mesa::ObtenerTodasLasMesas();
        if($mesas && $posiciones){

            for($i = 1; $i <= count($posiciones); $i++){

                $lista[$i] = 0;
            }

            for($i = 0; $i < count($mesas); $i++){

                $posicion = $mesas[$i]->idPosicion;
                $cant = $lista[$posicion];
                $cant += 1;
                $lista[$posicion] = $cant;
            }
            for ($i=1; $i <= count($lista); $i++) { 
                
                if($lista[$i] > $valorMax){
                    $valorMax = $lista[$i];
                    $numeroMesa = $i;
                }
            }
            $payload = json_encode(array("Mesa mas utilizada" => "La mesa más utilizada en la Nº {$numeroMesa}"));
        } else {
            $payload = json_encode(array("ERROR" => "error al obtener las mesas"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}

?>