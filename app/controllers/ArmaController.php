<?php
require_once '.\app\models\Arma.php';
require_once '.\app\interfaces\IArmamentos.php';
require_once __DIR__ . '\JWTController.php';

class ArmaController implements IArmamentos
{

    public function ListarTodos($request, $response, $args)
    {
        try {
            $listado = Arma::ListarTodos();
            $response->getBody()->write("LISTADO DE TODAS LAS ARMAS: <br><br>");

            if ($listado != null) {

                foreach ($listado as $value) {

                    $response->getBody()->write("ID: " . $value->id . "  ||  ");
                    $response->getBody()->write("Precio: " . $value->precio . "  ||  ");
                    $response->getBody()->write("Nombre: " . $value->nombre . "  ||  ");
                    $response->getBody()->write("Foto: " . $value->foto . "  ||  ");
                    $response->getBody()->write("Nacionalidad: " . $value->nacionalidad . "<br>");
                }
            } else {
                $response->getBody()->write('No hay armas cargadas');
            }
        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response = $response->withStatus(500);
        }

        return $response;
    }

    public function ListarPorNacionalidad($request, $response, $args)
    {
        try {
            $params = $request->getQueryParams();
            if (isset($params['nacionalidad'])) {

                $nacionalidad = strtolower($params['nacionalidad']);

                $listado = Arma::ListarPorNacionalidad($nacionalidad);
                $response->getBody()->write("LISTADO DE ARMAS POR NACIONALIDAD: " . $nacionalidad . "<br><br>");
                if ($listado != null) {

                    foreach ($listado as $value) {
                        $response->getBody()->write("ID: " . $value->id . "  ||  ");
                        $response->getBody()->write("Precio: " . $value->precio . "  ||  ");
                        $response->getBody()->write("Nombre: " . $value->nombre . "  ||  ");
                        $response->getBody()->write("Foto: " . $value->foto . "  ||  ");
                        $response->getBody()->write("Nacionalidad: " . $value->nacionalidad . "<br>");
                    }
                } else {
                    $response->getBody()->write('No hay armas cargadas con esa nacionalidad');
                }
            }

        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response = $response->withStatus(500);
        }

        return $response;
    }
    public function ListarPorID($request, $response, $args)
    {
        try {
            $params = $request->getQueryParams();
            if (isset($params['id'])) {

                $id = $params['id'];

                $listado = Arma::ListarPorID($id);
                $response->getBody()->write("LISTADO DE ARMAS POR ID: " . $id . "<br><br>");
                if ($listado != null) {

                    foreach ($listado as $value) {
                        $response->getBody()->write("ID: " . $value->id . "  ||  ");
                        $response->getBody()->write("Precio: " . $value->precio . "  ||  ");
                        $response->getBody()->write("Nombre: " . $value->nombre . "  || ");
                        $response->getBody()->write("Foto: " . $value->foto . "  ||  ");
                        $response->getBody()->write("Nacionalidad: " . $value->nacionalidad . "<br>");
                    }
                } else {
                    $response->getBody()->write('No hay armas cargadas con ese ID');
                }
            }

        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response = $response->withStatus(500);
        }

        return $response;
    }

    public function CargarUno($request, $response, $args)
    {
        try {
            $body = $request->getParsedBody();

            if (isset($body['precio']) && isset($body['nombre']) && ($request->getUploadedFiles() != null) && isset($body['nacionalidad'])) {

                $precio = $body['precio'];
                $nombre = $body['nombre'];
                $foto = $request->getUploadedFiles()['foto'];
                $nacionalidad = $body['nacionalidad'];

                if (Arma::CargarUno($precio, $nombre, $foto, $nacionalidad)) {
                    $response->getBody()->write("Se ha realizado el alta!");
                } else {
                    $response->getBody()->write("No se pudo realizazr el alta");
                }
            }
        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response = $response->withStatus(500);
        }
        return $response;
    }
    public function BorrarUna($request, $response, $args)
    {
        try {

            $params = $request->getQueryParams();
            if (isset($params['id'])) {
                $id_arma = $params['id'];
                $validarIdActivo = Arma::ValidarIdActivo($id_arma);

                if ($validarIdActivo) {
                    if (Arma::Borrar($id_arma)) {
                        $response->getBody()->write("Se ha realizado el borrado del arma!<br>");
                        $response->withStatus(200);
                    } else {
                        $response->getBody()->write("No se ha realizado el borrado del arma. <br>");
                        $response = $response->withStatus(500);
                    }
                } else {
                    $response->getBody()->write("No se ha realizado el borrado.<br>El ID " . $id_arma . " del arma no existe o ya fue anteriormente eliminado.<br>");
                    $response = $response->withStatus(500);
                }
            }
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

    public function ModificarArma($request, $response, $args)
    {
        try {
            $body = $request->getParsedBody();
            $flagID = false;

            //----VERIFICO ID
            if (isset($body["id"])) {

                $id_arma = $body["id"];

                if (Arma::ValidarIdActivo($id_arma)) {
                    $flagID = true;
                } else {
                    $response->getBody()->write("<br>No se encontraron registros con el ID indicado.");
                }
            } else {
                $response->getBody()->write("<br>No se ha modificado el arma. Verifique el ID indicado");
            }

            //----ACTUALIZADO PRECIO
            if ($flagID && isset($body["precio"])) {

                $precio = $body["precio"];

                if (Arma::ModificarPrecio($id_arma, $precio)) {
                    $response->getBody()->write("<br>Se ha actualizado el precio.");
                } else {
                    $response->getBody()->write("<br>No se pudo realizar la modificación del precio.");
                }
            }
            //----ACTUALIZADO NOMBRE
            if ($flagID && isset($body["nombre"])) {

                $nombre = $body["nombre"];

                if (Arma::ModificarNombre($id_arma, $nombre)) {
                    $response->getBody()->write("<br>Se ha actualizado el nombre.");
                    $flagPrecio = true;
                } else {
                    $response->getBody()->write("<br>No se pudo realizar la modificación del nombre.");
                }
            }
            //----ACTUALIZADO NACIONALIDAD
            if ($flagID && isset($body["nacionalidad"])) {

                $nacionalidad = $body["nacionalidad"];

                if (Arma::ModificarNacionalidad($id_arma, $nacionalidad)) {
                    $response->getBody()->write("<br>Se ha actualizado el nacionalidad.");
                    $flagPrecio = true;
                } else {
                    $response->getBody()->write("<br>No se pudo realizar la modificación de la nacionalidad.");
                }
            }

            //----ACTUALIZADO IMAGEN

            if ($flagID && $request->getUploadedFiles() != null) {
                $archivo = $request->getUploadedFiles()['foto'];
                $EstadoCargaImagen = Arma::GuardarImagen($id_arma, $archivo);
                if($EstadoCargaImagen == 1){
                    $response->getBody()->write("<br>Se ha guardado la imagen en la carpeta 'Backup_2023'.");
                }
                else if($EstadoCargaImagen == 2){
                    $response->getBody()->write("<br>Se ha guardado la imagen en la carpeta 'fotos_armas'.");
                }else{
                    $response->getBody()->write("<br>El error al guardar la imagen.");
                }
            }

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
}

?>