<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require_once '.\vendor\autoload.php';
require_once '.\app\controllers\UsuarioController.php';
require_once '.\app\controllers\ArmaController.php';
require_once '.\app\controllers\VentaController.php';
require_once '.\app\controllers\JWTController.php';
require_once '.\app\controllers\UsuarioController.php';
require_once '.\app\controllers\DescargasController.php';
require_once '.\app\middlewares\Logger.php';
require_once '.\app\middlewares\Delete.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);


//------------------- RUTAS USUARIOS ------------------
// 1 - localhost:666/usuarios/login
$app->group('/usuarios', function ($group) {
  $group->post('/login', \UsuarioController::class . ':Login');
});

//------------------- RUTAS ARMAS ------------------
$app->group('/armas', function ($group) {

  // 2: localhost:666/armas
  $group->post('[/]', \ArmaController::class . ':CargarUno')
    ->add(\Logger::validarRoles(['admin']))
    ->add(\Logger::class . ':validarJWTUsuario');

  // 3: localhost:666/armas
  $group->get('[/]', \ArmaController::class . ':ListarTodos'); //Sin autenticación

  // 4: localhost:666/armas/nacionalidad?nacionalidad=XXX
  $group->get('/nacionalidad', \ArmaController::class . ':ListarPorNacionalidad'); //Sin autenticación

  // 5: localhost:666/armas/id?id=XXXX
  $group->get('/id', \ArmaController::class . ':ListarPorID')
    ->add(\Logger::class . ':validarJWTUsuario'); //Cualquier usuario registrado

  // 9: localhost:666/armas/borrado?id=212
  $group->delete('/borrado', \ArmaController::class . ':BorrarUna')
    ->add(\Delete::class . ':LogDelete')
    ->add(\Logger::validarRoles(['admin']))
    ->add(\Logger::class . ':validarJWTUsuario');

  // 10: localhost:666/armas/modificar
  $group->post('/modificar', \ArmaController::class . ':ModificarArma')
    ->add(\Logger::validarRoles(['admin']))
    ->add(\Logger::class . ':validarJWTUsuario');
});

//------------------- RUTAS VENTAS ------------------
$app->group('/ventas', function ($group) {

  // 6: localhost:666/ventas
  $group->post('[/]', \VentaController::class . ':CargarUno')
    ->add(\Logger::class . ':validarJWTUsuario');

  // 7: localhost:666/ventas/eeuu_intervalo
  $group->get('/eeuu_intervalo', \VentaController::class . ':ListarVentasEEUU')
    ->add(\Logger::validarRoles(['admin']))
    ->add(\Logger::class . ':validarJWTUsuario');

  // 8: localhost:666/ventas/exocet
  $group->get('/exotec', \VentaController::class . ':ListarExotec')
    ->add(\Logger::validarRoles(['admin']))
    ->add(\Logger::class . ':validarJWTUsuario');
});

//------------------- RUTAS DESCARGAS ------------------

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/app/plantillas');
$twig = new \Twig\Environment($loader, ['debug' => true]);
$twig->addExtension(new Twig\Extension\DebugExtension());
$app->add(function ($request, $response, $next) use ($twig) {
  $request = $request->withAttribute('view', $twig);
  $response = $next($request, $response);
  return $response;
});

$app->group('/descargas', function ($group) {
  // 11: localhost:666/descargas/armas
  $group->get('/armas', \DescargasController::class . ':DescargarArmas');

  // 12: localhost:666/descargas/logs
  $group->get('/logs', \DescargasController::class . ':DescargarLogs');

  // 13: localhost:666/descargas/pdf?orden=XXX
  $group->get('/pdf', \DescargasController::class . ':DescargarPDF');

  // 14: localhost:666/descargas/pdf_log
  $group->get('/pdf_log', \DescargasController::class . ':DescargarPDF_Log');

  //15: localhost:666/descargas/csv_mes?orden=ascendente
  $group->get('/csv_mes', \DescargasController::class . ':DescargarCSV_mes');
  
  //15: localhost:666/descargas/pdf_id/2
  $group->get('/pdf_id/{id}', \DescargasController::class . ':DescargarPDF_ID');

});


$app->run();