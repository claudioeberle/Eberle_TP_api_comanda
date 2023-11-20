<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware {

    private $puestosValidos;
    public function __construct($puestosValidos) {
        $this -> puestosValidos = $puestosValidos;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        try {

            $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
            AutentificadorJWT::VerificarToken($token);
            $puestoToken = AutentificadorJWT::ObtenerData($token) -> puesto;
    
            if (in_array($puestoToken, $this -> puestosValidos)) {

                return $handler -> handle($request);
                
            } else {
                throw new Exception("El usuario no esta autorizado");
            }
        } catch (Exception $e) {

            $response = new Response();
            $payload = json_encode(['ERROR EN LA AUTENTICACION' => $e -> getMessage()]);
            $response -> getBody() -> write($payload);
            return $response -> withHeader('Content-Type', 'application/json');
        }
    }
}