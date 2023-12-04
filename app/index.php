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
require_once './Controllers/estadisticasController.php';

require_once './Middlewares/AuthMiddleware.php';
require_once './Middlewares/logMiddleware.php';


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

    $group -> post('[/]', \mesasController::class . ':CargarUno')
    -> add(new LogMiddleware('ALTA_MESA'))
    -> add(new AuthMiddleware(["socio", "mozo"]));

    $group -> post('/cambioEstado', \mesasController::class . ':CambiarEstado')
    -> add(new LogMiddleware('CAMBIO_ESTADO_MESA'))
    -> add(new AuthMiddleware(["socio","mozo"]));

    $group -> post('/facturacion', \mesasController::class . ':Facturacion')
    -> add(new LogMiddleware('FACTURACION_MESA'))
    -> add(new AuthMiddleware(["socio","mozo"]));

    $group -> get('[/]', \mesasController::class . ':TraerTodos')
    -> add(new LogMiddleware('CONSULTA_MESA'))
    -> add(new AuthMiddleware(["socio", "bartender", "cervecero", "mozo", "cocinero"]));

    $group -> delete('/{codigoMesa}', \mesasController::class . ':EliminarUno')
    -> add(new LogMiddleware('BAJA_MESA'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> put('[/]', \mesasController::class . ':ModificarUno')
    -> add(new LogMiddleware('MODIF_MESA'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('/masUsada', \mesasController::class . ':MesaMasUtilizada')
    -> add(new LogMiddleware('CONSULTA_MESA'))
    -> add(new AuthMiddleware(["socio"]));
});

$app -> group('/usuarios', function (RouteCollectorProxy $group) { 

    $group -> post('[/]', \UsuarioController::class . ':CargarUno')
    -> add(new LogMiddleware('ALTA_USUARIO'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('[/]', \UsuarioController::class . ':TraerTodos')
    -> add(new LogMiddleware('CONSULTA_USUARIOS'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('/{dni}', \UsuarioController::class . ':TraerUno')
    -> add(new LogMiddleware('CONSULTA_USUARIOS')) -> add(new AuthMiddleware(["socio"]));

    $group -> get('/puesto/{puesto}', \UsuarioController::class . ':TraerPorPuesto')
    -> add(new LogMiddleware('CONSULTA_USUARIOS')) -> add(new AuthMiddleware(["socio"]));

    $group -> delete('[/]', \UsuarioController::class . ':EliminarUno')
    -> add(new LogMiddleware('BAJA_USUARIO'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> put('[/]', \UsuarioController::class . ':ModificarUno')
    -> add(new LogMiddleware('MODIF_USUARIO'))
    -> add(new AuthMiddleware(["socio"]));
});

$app -> group('/productos', function (RouteCollectorProxy $group) {

    $group -> post('[/]', \ProductoController::class . ':CargarUno')
    -> add(new LogMiddleware('ALTA_PRODUCTO'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('[/]', \ProductoController::class . ':TraerTodos');

    $group -> get('/csv', \ProductoController::class . ':DescargarCSV')
    -> add(new LogMiddleware('PRODUCTO_CSV'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> post('/csv', \ProductoController::class . ':CargarCSV')
    -> add(new LogMiddleware('CARGA_CSV'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('/{id}', \ProductoController::class . ':TraerUno');

    $group -> delete('/{id}', \ProductoController::class . ':EliminarUno')
    -> add(new LogMiddleware('BAJA_PRODUCTO'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> put('[/]', \ProductoController::class . ':ModificarUno')
    -> add(new LogMiddleware('MODIF_PRODUCTO'))
    -> add(new AuthMiddleware(["socio"]));
});

$app -> group('/pedidos', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \PedidoController::class . ':TraerTodos')
    -> add(new LogMiddleware('CONSULTA_PEDIDOS'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('/pendientes', \PedidoController::class . ':ObtenerTodosPedidosPendientes')
    -> add(new LogMiddleware('CONSULTA_PEDIDOS'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('/id/{id}', \PedidoController::class . ':TraerUno')
    -> add(new LogMiddleware('CONSULTA_PEDIDOS'))
    -> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender"]));

    $group -> get('/codigo/{codigoPedido}', \PedidoController::class . ':TraerPorCodigo');

    $group -> post('/estadoPedido', \PedidoController::class . ':TraerEstadoPedido');

    $group -> post('/estadoUnPedido', \PedidoController::class . ':ObtenerEstadoDeUnPedido')
    -> add(new LogMiddleware('ESTADO_PEDIDO'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> post('/sector', \PedidoController::class . ':TraerPedidosPendientesPorSector')
    -> add(new LogMiddleware('PEDIDOS_SECTOR'))
    -> add(new AuthMiddleware(["socio","cocinero","cervecero","bartender","mozo"]));

    $group -> post('[/]', \PedidoController::class . ':CargarUno')
    -> add(new LogMiddleware('ALTA_PEDIDO'))
    -> add(new AuthMiddleware(["mozo"]));

    $group -> get('/listosParaServir', \PedidoController::class . ':ObtenerTodosPedidosParaServir')
    -> add(new LogMiddleware('PEDIDOS_LISTOS_SERVIR'))
    -> add(new AuthMiddleware(["mozo"]));

    $group -> post('/cambioEstado', \PedidoController::class . ':CambiarEstadoPedidoProducto')
    -> add(new LogMiddleware('CBIO_ESTADO_PEDIDOS'))
    -> add(new AuthMiddleware(["cocinero","cervecero","bartender", "mozo"]));

    $group -> put('[/]', \PedidoController::class . ':ModificarUno')
    -> add(new LogMiddleware('MODIF_PEDIDO'))
    -> add(new AuthMiddleware(["mozo","socio"]));

    $group -> delete('/{id}', \PedidoController::class . ':BorrarUno')
    -> add(new LogMiddleware('BAJA_PEDIDO'))
    -> add(new AuthMiddleware(["mozo","socio"]));

    $group -> post('/foto', \PedidoController::class . ':CargarFoto')
    -> add(new LogMiddleware('ALTA_FOTO'))
    -> add(new AuthMiddleware(["mozo"]));

    $group -> get('/retrasos', \PedidoController::class . ':PedidosConRetraso')
    -> add(new LogMiddleware('CONSULTA_RETRASOS_PEDIDOS'))
    -> add(new AuthMiddleware(["socio"]));


});

$app -> group('/encuestas', function (RouteCollectorProxy $group) {
    $group -> get('[/]', \EncuestasController::class . ':TraerTodos')
    -> add(new LogMiddleware('CONSULTA_ENCUESTAS'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> post('[/]', \EncuestasController::class . ':CargarUno');

    $group -> post('/mejores', \EncuestasController::class . ':ObtenerMejoresComentarios')
    -> add(new LogMiddleware('CONSULTA_MEJORES_ENCUESTAS'))
    -> add(new AuthMiddleware(["socio"]));

    $group -> get('/logo', \EncuestasController::class . ':GuardarLogoPdf')
    -> add(new LogMiddleware('DESCARGA_LOGO'))
    -> add(new AuthMiddleware(["socio"]));
});

$app -> group('/login', function (RouteCollectorProxy $group) {
    
    $group -> post('[/]', \LoginController::class . ':Login');
});

$app -> group('/estadisticas', function (RouteCollectorProxy $group) {
    
    $group -> get('[/]', \EstadisticasController::class . ':ObtenerEstadisticasPedidos');
});

$app->run();