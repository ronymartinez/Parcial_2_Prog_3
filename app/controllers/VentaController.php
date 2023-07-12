<?php
require_once '.\app\models\Venta.php';
require_once '.\app\interfaces\IArmamentos.php';
require_once __DIR__ . '\JWTController.php';

class VentaController implements IArmamentos
{

    public function ListarTodos($request, $response, $args)
    {
        return $response;
    }
    public function CargarUno($request, $response, $args)
    {
        try {
            $body = $request->getParsedBody();

            if (isset($body['fecha']) && isset($body['cantidad']) && isset($body['id_usuario']) && isset($body['id_arma'])) {
                $fecha = $body['fecha'];
                $cantidad = $body['cantidad'];
                $id_usuario = $body['id_usuario'];
                $id_arma = $body['id_arma'];

                $ventaOk = Venta::CargarUno($fecha, $cantidad, $id_usuario, $id_arma);

                if ($ventaOk) {
                    $response->getBody()->write("Se ha realizado el alta!");
                } else {
                    $response->getBody()->write('No se han encontrado registros con los IDs ingresasdos. Verifique los datos.');
                    $request = $response->withStatus(500);
                }
            } else {
                $response->getBody()->write('No se ha realizado el alta. Verifique los parámetros ingresados.');
                $response = $response->withStatus(500);
            }

        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response->withStatus(500);
        }

        return $response;
    }

    public function ListarVentasEEUU($request, $response, $args)
    {
        try {
            $listado = Venta::ListarVentasEEUU();

            if ($listado != null) {
                $response->getBody()->write("VENTAS DE EE. UU. ENTRE EL 13 Y 16 DE NOVIEMBRE:" . "<br><br>");
                foreach ($listado as $value) {
                    $response->getBody()->write("ID: " . $value->id . "  ||  ");
                    $response->getBody()->write("Fecha: " . $value->fecha . "  ||  ");
                    $response->getBody()->write("Cantidad: " . $value->cantidad . " ||  ");
                    $response->getBody()->write("Ruta Foto: " . $value->foto . "  ||  ");
                    $response->getBody()->write("ID Usuario: " . $value->id_usuario . "  ||  ");
                    $response->getBody()->write("ID Arma: " . $value->id_arma . "<br>");
                }
            } else {
                $response->getBody()->write('No hay armas cargadas de EE. UU. dentro del intervalo indicado.');
            }
        } catch (Exception $e) {
            $request->getBody()->write('Error: ' . $e->getMessage());
            $request->withStatus(500);
        }

        return $response;
    }
    public function ListarExotec($request, $response, $args)
    {
        try {
            $listado = Usuario::ListarExotec();
          
            $response->getBody()->write("LISTADO DE USUARIOS QUE COMPRARON EXOTEC:<br><br>");
            if ($listado != null) {

                foreach ($listado as $value) {
                    $response->getBody()->write("ID: " . $value->id. "  ||  ");
                    $response->getBody()->write("Mail: " . $value->mail. "  ||  ");
                    $response->getBody()->write("Tipo: " . $value->tipo  . "  ||  <br>");
                }
            } else {
                $response->getBody()->write('No hay ventas de Exotec.<br>');
            }

        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response = $response->withStatus(500);
        }

        return $response;
    }

}
?>