<?php

require_once '.\app\models\Arma.php';
class DescargasController
{
    public function DescargarArmas($request, $response, $args)
    {
        $flagGuardado = true;

        try {

            $listaArmas = Arma::ListarTodos();
            $delimitador = ";";
            $archivo = fopen('armas.csv', 'w');

            $header = ["Id", "Precio", "Nombre", "Foto", "Nacionalidad"];
            fputcsv($archivo, $header, $delimitador);
            fputcsv($archivo, []);

            foreach ($listaArmas as $arma) {
                $fila = [$arma->id, $arma->precio, $arma->nombre, $arma->foto, $arma->nacionalidad];
                if (!fputcsv($archivo, $fila, ";")) {
                    $flagGuardado = false;
                }
            }

            if ($flagGuardado) {
                $response->getBody()->write("<br>Se ha guardado el archivo CSV");
            } else {
                $response->getBody()->write("<br>No ha guardado el archivo CSV");
                $response->withStatus(500);
            }

            fclose($archivo);

        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response->withStatus(500);
        }

        return $response;

    }

    public function DescargarLogs($request, $response, $args)
    {
        $flagGuardado = true;

        try {

            $listarLogs = Log::ListarTodos();
            $delimitador = ";";
            $archivo = fopen('logs.csv', 'w');

            $header = ["Id", "Id_usuario", "Id_arma", "Accion", "Fecha"];
            fputcsv($archivo, $header, $delimitador);
            fputcsv($archivo, []);

            foreach ($listarLogs as $log) {
                $fila = [$log->id, $log->id_usuario, $log->id_arma, $log->accion, $log->fecha];
                if (!fputcsv($archivo, $fila, ";")) {
                    $flagGuardado = false;
                }
            }

            if ($flagGuardado) {
                $response->getBody()->write("<br>Se ha guardado el archivo CSV");
            } else {
                $response->getBody()->write("<br>No ha guardado el archivo CSV");
                $response->withStatus(500);
            }

            fclose($archivo);

        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response->withStatus(500);
        }
        return $response;
    }

    public function DescargarPDF($request, $response, $args)
    {
        $params = $request->getQueryParams();

        if (isset($params["orden"])) {
            $orden = $params["orden"];
            if ($orden == "ascendente" || $orden == "descendente") {
                Venta::GuardarPDF($orden, $request, $response);
                $response->getBody()->write("Se ha descargado correctamente el PDF.");
            } else {
                $response->getBody()->write("Ha ingresado un orden incorrecto.");
                $response->withStatus(500);
            }

        } else {
            $response->getBody()->write("No ha ingresado el parámetro 'orden'.");
            $response->withStatus(500);
        }

        return $response;
    }

    public function DescargarPDF_Log($request, $response, $args)
    {
        try {
            if (Log::GuardarPDF($request, $response) != null) {
                $response->getBody()->write("Se ha descargado correctamente el PDF.");
            } else {
                $response->getBody()->write("No ha ingresado el parámetro 'orden'.");
                $response->withStatus(500);
            }
        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response->withStatus(500);
        }
        return $response;
    }


    function DescargarCSV_mes($request, $response, $args)
    {
        $params = $request->getQueryParams();

        $listaFiltrada = [];
        if (isset($params["orden"])) {
            $orden = $params["orden"];
            if ($orden == "ascendente" || $orden == "descendente") {
                $listaFiltrada = Venta::ListarPDF(); //Reutilizo el código de descargar PDF

                if ($listaFiltrada != null) {

                    if (Venta::DescargarCSV($listaFiltrada, $response)) {
                        $response->getBody()->write("Se ha descargado correctamente el PDF.");
                    } else {
                        $response->getBody()->write("No se pudo generar la descarga");
                        $response->withStatus(500);
                    }
                }

            } else {
                $response->getBody()->write("Ha ingresado un orden incorrecto.");
                $response->withStatus(500);
            }

        } else {
            $response->getBody()->write("No ha ingresado el parámetro 'orden'.");
            $response->withStatus(500);
        }
        return $response;
    }
    function DescargarPDF_ID($request, $response, $args)
    {
        if (isset($args['id'])) {
            $id_arma = $args['id'];
            //var_dump($id_arma);
            $arma = Arma::ListarPorID($id_arma);
            //var_dump($arma);

            if ($arma != null) {
                if (Arma::GuardarPDF($request, $response, $arma)) {
                    //$response->getBody()->write("Se ha descargo el PDF.");
                    $response->withStatus(200);
                }
            }

        } else {
            //$response->getBody()->write("Ha ingresado un ID incorrecto.");
            $response->withStatus(500);
        }
        // $listaFiltrada = [];
        // if (isset($params["orden"])) {
        //     $orden = $params["orden"];
        //     if ($orden == "ascendente" || $orden == "descendente") {
        //         $listaFiltrada = Venta::ListarPDF(); //Reutilizo el código de descargar PDF

        //         if ($listaFiltrada != null) {

        //             if (Venta::DescargarCSV($listaFiltrada, $response)) {
        //                 $response->getBody()->write("Se ha descargado correctamente el PDF.");
        //             }else{
        //                 $response->getBody()->write("No se pudo generar la descarga");
        //                 $response->withStatus(500);
        //             }
        //         }

        //     } else {
        //         $response->getBody()->write("Ha ingresado un orden incorrecto.");
        //         $response->withStatus(500);
        //     }

        // } else {
        //     $response->getBody()->write("No ha ingresado el parámetro 'orden'.");
        //     $response->withStatus(500);
        // }
        return $response;
    }

}

?>