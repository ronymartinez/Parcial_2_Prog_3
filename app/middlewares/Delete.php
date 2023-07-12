<?php
use Slim\Psr7\Response as ResponseMW;

require_once __DIR__ . "/../Models/Log.php";

class Delete
{
    public function LogDelete($request, $response, $next)
    {
        try {

            $response = $next($request, $response);
            $statusCode = $response->getStatusCode();

            if ($statusCode == 200) {

                $dataJwt = $request->getAttribute('jwt');               

                $params = $request->getQueryParams();

                $id_usuario = $dataJwt->id;
                $id_arma = $params['id'];
                $accion = "borrado";
                $fecha = date('d-m-Y');
                if (Log::GuardarLog($id_usuario, $id_arma, $accion, $fecha)) {
                    $response->getBody()->write("<br>Log creado con éxito");
                } else {
                    $response->getBody()->write("<br>No se pudo crear el log");
                }
            }

        } catch (Exception $e) {
            $response->getBody()->write("<br>No se pudo crear el log");
        }
        return $response;
    }

    public static function GuardarLogMDW($id_usuario, $id_arma, $accion, $fecha)
    {
        if (Log::GuardarLog($id_usuario, $id_arma, $accion, $fecha)) {
            return true;
        }
        return false;
    }
}