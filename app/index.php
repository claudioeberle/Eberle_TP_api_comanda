<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;
use Slim\Factory\AppFactory;
use App\Controllers\UsuariosController;
require __DIR__ . '/../vendor/autoload.php';
//Controladores
require_once './Controllers/mesasController.php';
require_once './Controllers/usuariosController.php';
require_once './Controllers/productosController.php';
require_once './Controllers/pedidosController.php';
require_once './Controllers/encuestasController.php';
require_once './Controllers/loginController.php';

require_once './Middlewares/AuthMiddleware.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

//puesto: socio, mozo, bartender, cervecero, cocinero
//sector: local, salon, barra, chopera, cocina, candy
$app = AppFactory::create();
$app -> addBodyParsingMiddleware();

$app -> get('/', function (Request $request, Response $response, $args) {
    $response -> getBody() -> write("Bienvenido a la Rest-api Comanda");
    return $response;
});

$app -> group('/mesas', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \mesasController::class . ':CargarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> post('/cambioEstado', \mesasController::class . ':CambiarEstado') -> add(new AuthMiddleware(["socio","mozo"]));
    $group -> get('[/]', \mesasController::class . ':TraerTodos') -> add(new AuthMiddleware(["socio", "bartender", "cervecero", "mozo", "cocinero"]));
    $group -> get('/{codigoMesa}', \mesasController::class . ':TraerUno') -> add(new AuthMiddleware(["socio", "bartender", "cervecero", "mozo", "cocinero"]));
    $group -> delete('/{codigoMesa}', \mesasController::class . ':EliminarUno') -> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \mesasController::class . ':ModificarUno')-> add(new AuthMiddleware(["socio"]));
});

$app -> group('/usuarios', function (RouteCollectorProxy $group) { 
    $group -> post('[/]', \UsuarioController::class . ':CargarUno')-> add(new AuthMiddleware(["socio"]));
    $group -> post('/login', \UsuarioController::class . ':IniciarSesion');
    $group -> get('[/]', \UsuarioController::class . ':TraerTodos')-> add(new AuthMiddleware(["socio"]));
    $group -> get('/{dni}', \UsuarioController::class . ':TraerUno')-> add(new AuthMiddleware(["socio"]));
    $group -> get('/puesto/{puesto}', \UsuarioController::class . ':TraerPorPuesto')-> add(new AuthMiddleware(["socio"]));
    $group -> delete('/{id}', \UsuarioController::class . ':EliminarUno')-> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \UsuarioController::class . ':ModificarUno')-> add(new AuthMiddleware(["socio"]));
});

$app -> group('/productos', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \ProductoController::class . ':CargarUno')-> add(new AuthMiddleware(["socio"]));
    $group -> get('[/]', \ProductoController::class . ':TraerTodos');
    $group -> get('/csv', \ProductoController::class . ':DescargarCSV') -> add(new AuthMiddleware(["socio"]));
    $group -> post('/csv', \ProductoController::class . ':CargarCSV') -> add(new AuthMiddleware(["socio"]));
    $group -> get('/{id}', \ProductoController::class . ':TraerUno');
    $group -> delete('/{id}', \ProductoController::class . ':EliminarUno')-> add(new AuthMiddleware(["socio"]));
    $group -> put('[/]', \ProductoController::class . ':ModificarUno')-> add(new AuthMiddleware(["socio"]));
});

$app -> group('/pedidos', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \PedidoController::class . ':TraerTodos')-> add(new AuthMiddleware(["socio"]));
    $group -> get('/id/{id}', \PedidoController::class . ':TraerUno')-> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender"]));
    $group -> get('/codigo/{codigoPedido}', \PedidoController::class . ':TraerPorCodigo');
    $group -> get('/tiempoRestante/{codigoMesa}/{codigoPedido}', \PedidoController::class . ':TraerTiempoPedido');
    $group -> get('/sector/{sector}', \PedidoController::class . ':TraerPedidosPendientesPorSector')-> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender","mozo"]));
    $group -> post('[/]', \PedidoController::class . ':CargarUno')-> add(new AuthMiddleware(["mozo"]));
    $group -> post('/cambioEstado', \PedidoController::class . ':CambiarEstadoPedido')-> add(new AuthMiddleware(["cocinero","cervecero","bartender"]));
    $group -> put('[/]', \PedidoController::class . ':ModificarUno')-> add(new AuthMiddleware(["mozo","socio"]));
    $group -> delete('/{id}', \PedidoController::class . ':BorrarUno')-> add(new AuthMiddleware(["mozo","socio"]));
});

$app -> group('/encuestas', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \EncuestasController::class . ':TraerTodos');
    $group -> post('[/]', \EncuestasController::class . ':CargarUno');
});

$app -> group('/login', function (RouteCollectorProxy $group) {
    $group -> post('[/]', \LoginController::class . ':Login');
});

$app->run();