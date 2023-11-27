<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\Models\mesa.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/pedido.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Interfaces/IApiUsable.php';

class MesasController implements IApiUsable {

    public function CargarUno($request, $response, $args) {

        $codigoMesa = Mesa::AsignarCodigoMesa();
        if($codigoMesa){

            $mesa = new Mesa(false, false, $codigoMesa, new DateTime());
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
        
        if (isset($parametros["codigoMesa"]) && isset($parametros["estado"])) {

            $codigoMesa = $parametros["codigoMesa"];
            $estado = $parametros["estado"];

            if($estado === 'cerrada' || $estado === 'con cliente pagando'){

                $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);
                if ($mesa) {

                    $mesa->ActualizarListaPedidos();

                    if($mesa->HayPedidosPendientes()){
                        
                        $mesa -> CambiarEstado($estado);
                        $payload = json_encode(array("ESTADO" => "Se modifico el estado de {$codigoMesa} a '{$estado}'")); 
                        
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
                    $payload = json_encode(array("ERROR" => "No se pudo encontrar una mesa con el código {$codigoMesa}"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "El estado propuesto no esta permitido"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'codigoMesa' es obligatorio para modificar el estado de una mesa"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EliminarUno($request, $response, $args) {

        $resultado = false;

        if (isset($args["codigoMesa"])) {

            $codigoMesa = $args["codigoMesa"];
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

}

?>