<?php


class EstadisticasController{

    public function ObtenerEstadisticasPedidos($request, $response, $args) {

        $cantRetrasos = 0;
        $sumaRetrasos = 0;
        $cantATiempo = 0;
        $sumaPrecios = 0;
        $promedioRetrasos = 0;
        $promedioPrecios = 0;
        $lapso = 43200;
        $fechaHoy = new DateTime();
        $fechaInicio = clone $fechaHoy;
        $fechaInicio = $fechaInicio->modify("-$lapso minutes");
        $retorno = array();
        $cantSabrina = Mesa::CantidadMesasMozo(19);
        $cantRodrigo = Mesa::CantidadMesasMozo(22);
        $cantHugo = Mesa::CantidadMesasMozo(26);
        $MesasMozos = ['Sabrina' => $cantSabrina, 'Rodrigo' => $cantRodrigo, 'Hugo' => $cantHugo];

        $pedidos = PedidoProducto::ObtenerTodosLosPedidoProducto();
        if($pedidos){

            foreach($pedidos as $pedido){

                $fechaAlta = new DateTime($pedido->fechaAlta);

                if($fechaAlta >= $fechaInicio && $fechaAlta <= $fechaHoy){
                    $retraso = $pedido->ObtenerRetraso();
                    if($retraso && $retraso > 0){
                        $cantRetrasos++;
                        $sumaRetrasos += $retraso;
                        $sumaPrecios += $pedido->precio; 
                    } else {
                        $cantATiempo++;
                        $sumaPrecios += $pedido->precio;
                    }
                }
            }
            if($cantRetrasos > 0){
                $promedioRetrasos = $sumaRetrasos/$cantRetrasos;
            }
            $totalPedidos = $cantATiempo + $cantRetrasos;
            if($totalPedidos > 0){
                $promedioPrecios = $sumaPrecios / $totalPedidos;
            }
            array_push($retorno, [
            'Cantidad Total Pedidos' => $totalPedidos, 
            'Cantidad Pedidos Retrasados' => $cantRetrasos, 
            'Promedio Minutos Retraso' => $promedioRetrasos, 
            'Cantidad Pedidos A Tiempo' => $cantATiempo, 
            'Precio Promedio Pedidos' => $promedioPrecios,
            'Cantidad Mesas Por Mozo' => $MesasMozos
            ]);
            $payload = json_encode(array("Estadisticas 30 dias" => $retorno));

        } else {
            $payload = json_encode(array("ERROR" => "No se pudieron obtener los pedidos"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
?>


