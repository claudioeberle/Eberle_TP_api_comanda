<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/usuario.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/autenticador.php';

class LoginController {

    public static function Login($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($parametros["email"]) && isset($parametros["password"])) {

            $email = $parametros["email"];
            $password = $parametros["password"];

            $resultado = Usuario::Login($email, $password);

            if ($resultado instanceof Usuario) {

                $usuario = $resultado;
                $token = AutentificadorJWT::CrearToken([ "email" => $usuario -> email, "puesto" => $usuario -> puesto]);
                $payload = json_encode(array("Token" => $token));
                
            } else {

                $payload = json_encode(array("ERROR" => $resultado));
            }
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>