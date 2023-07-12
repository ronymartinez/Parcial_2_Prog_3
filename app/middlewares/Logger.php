<?php
use Slim\Psr7\Response as ResponseMW;
require_once __DIR__ . "/../controllers/JWTController.php";

class Logger
{    
    public static function validarJWTUsuario($request, $response, $next)
    {        
        print("<br>");
        $token = $request->getHeaderLine('Authorization');
        $error = null;
        $statusError = 500;

        if (!$token) {
            $error = 'No hay un jwt guardado';
            $statusError = 401;
        }
        
        try {
            $tokenVerificado =  JWTController::VerificarToken($token);
            $request = $request->withAttribute('jwt', $tokenVerificado);
            $response = $next($request, $response);
        } catch (Exception $e) {

            $error = $e->getMessage();
            $statusError = 401;
        }

        if (isset($error)) {
            $response->getBody()->write($error);
            $response->withStatus($statusError);
        }

        return $response;
    }
    public static function validarRoles($rolesPermitidos)
    {
        return function ($request, $response, $next) use ($rolesPermitidos) {
            $dataJwt = $request->getAttribute('jwt');
            $error = null;
            $statusError = 500;
            
            if (!$dataJwt) {
                $error = 'No hay un jwt guardado';
                $statusError = 401;
            }

            if (!in_array($dataJwt->tipo, $rolesPermitidos)) {
                $error = 'No cuenta con permisos para ingresar';
                $statusError = 404;
            }

            if (isset($error)) {
                $response->getBody()->write($error);
                $response->withStatus($statusError);
            } else {
                $response = $next($request, $response);
            }
            return $response;
        };
    }


}