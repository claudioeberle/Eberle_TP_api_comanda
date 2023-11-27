<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/mesa.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/encuesta.php';

class EncuestasController{

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (!isset($parametros["codigoMesa"]) || !isset($parametros["puntosMesa"]) || !isset($parametros["puntosResto"]) 
        || !isset($parametros["puntosMozo"]) || !isset($parametros["puntosCocinero"]) || !isset($parametros["experiencia"])) { 

            $payload = json_encode(array("ERROR" => "Los parametros obligatorios para agregar una encuesta son codigoMesa, puntosMesa, puntosResto, puntosMozo, puntosCocinero, experiencia"));

        } else {

            $encuesta = new Encuesta(0, $parametros['codigoMesa'], $parametros['puntosMesa'], $parametros['puntosResto'], $parametros['puntosMozo'], $parametros['puntosCocinero'], $parametros['experiencia']);
            $resultado = $encuesta -> GuardarEncuesta();

            if (is_numeric($resultado)) {

                $payload = json_encode(array("Resultado" => "Se ha creado con la encuesta Nº '{$resultado}'"));

            } else {

                $payload = json_encode(array("ERROR" => "Hubo un error en el alta de la encuesta"));
            }
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {

        $lista = Encuesta::ObtenerTodasLasEncuestas();

        if (is_array($lista)) {

            $payload = json_encode(["Encuestas" => $lista]);

        } else {

            $payload = json_encode(array("ERROR" => "Hubo un error al obtener las encuestas"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {

        if (isset($args["codigoMesa"])) {

            $encuesta = Encuesta::ObtenerPorCodigoMesa($args["codigoMesa"]);

            if ($encuesta) {

                $payload = json_encode(array("Encuesta" => $encuesta));

            } else {

                $payload = json_encode(array("ERROR" => "No se pudo encontrar la encuesta de la mesa con codigo {$args["codigoMesa"]}"));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parametro 'codigoMesa' es obligatorio"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }
    
}

?>