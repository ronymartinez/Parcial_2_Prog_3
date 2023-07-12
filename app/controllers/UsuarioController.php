<?php

require_once '.\app\models\Usuario.php';
require_once '.\app\interfaces\IArmamentos.php';
require_once __DIR__ . '\JWTController.php';

class UsuarioController implements IArmamentos
{
    public function ListarTodos($request, $response, $args)
    {
        $listado = Usuario::ListarTodos();
        foreach ($listado as $value) {
            $response->getBody()->write("ID: " . $value->id . "  |  ");
            $response->getBody()->write("Mail: " . $value->mail . "  |  ");
            $response->getBody()->write("Tipo: " . $value->tipo . "  |  ");
            $response->getBody()->write("Clave: " . $value->clave . "<br>");
        }

        return $response;
    }
    public function CargarUno($request, $response, $args)
    {
        return $response;
    }

    public function Login($request, $response, $args)
    {
        try {

            $body = $request->getParsedBody();
            if (isset($body["mail"]) && isset($body["clave"])) {

                $usuario = Usuario::VerificarUsuario($body["mail"], $body["clave"]);
                if ($usuario != null) {

                    $token = [
                        'id' => $usuario->id,
                        'tipo' => $usuario->tipo,
                        'email' => $usuario->mail
                    ];
                    $jwt = JWTController::CrearToken($token);

                    // Agrega la cookie a la respuesta
                    $response = $response->withHeader('Set-Cookie', 'jwt=' . $jwt . '; path=/; HttpOnly; Secure; SameSite=Strict');
                    $response->getBody()->write('Usuario logueado!'. "<br>");
                    $response->getBody()->write('ID: ' . $usuario->id . "<br>");
                    $response->getBody()->write('Emai: '. $usuario->mail . "<br>");
                    $response->getBody()->write('Tipo: '. $usuario->tipo . "<br>");

                    $response = $response->withStatus(200);
                } else {
                    $response->getBody()->write('Contraseña y/o mail incorrecto');
                    $response = $response->withStatus(401);
                }

            } else {
                $response->getBody()->write('No ha ingresado los datos necesarios');
                $response = $response->withStatus(401);
            }
        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response = $response->withStatus(500);
        }
        return $response;
    }
}
?>