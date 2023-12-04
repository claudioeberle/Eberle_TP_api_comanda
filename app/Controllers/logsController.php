<?php

require_once 'C:\xampp\htdocs\zz-api-homebanking\app\/Models/cliente.php';
require_once 'C:\xampp\htdocs\zz-api-homebanking\app\/Models/autenticador.php';


class LogsController {

    public static function ObtenerTodosLogs($request, $response, $args) {
        
        $logs = Log::ObtenerTodosLogs();
        if($logs){
        
            $payload = json_encode(array("Logs" => $logs));
            
        } else {

            $payload = json_encode(array("ERROR" => 'No se obtuvieron los logs'));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>