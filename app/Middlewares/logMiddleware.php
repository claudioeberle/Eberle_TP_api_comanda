<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/log.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/db/accesoDatos.php';

class LogMiddleware
{
    private $accion;

    public function __construct($accion) {
        $this -> accion = $accion;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $parametros = $request->getParsedBody();

        $token = trim(explode("Bearer", $request -> getHeaderLine('Authorization'))[1]);
        $idUsuario = AutentificadorJWT::ObtenerData($token) -> id;
        $fechaHoraEvento = new DateTime();

        $nuevoLog = new Log(0, $idUsuario, $this->accion, $fechaHoraEvento);
        $nuevoLog->GuardarLog();
        $response = $handler->handle($request);

        return $response;
    }
}