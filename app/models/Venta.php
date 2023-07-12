<?php

class Venta
{
    public $id;
    public $fecha;
    public $cantidad;
    public $foto;
    public $id_usuario;
    public $id_arma;

    function __construct($id, $fecha, $cantidad, $foto, $id_usuario, $id_arma)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->cantidad = $cantidad;
        $this->foto = $foto;
        $this->id_usuario = $id_usuario;
        $this->id_arma = $id_arma;
    }

    private static function ObtenerConexion()
    {
        try {
            $pdo = new PDO('mysql:host=localhost;dbname=parcial_bd;charset=utf8', 'root', '', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $pdo->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage();
            die();
        }
        return $pdo;
    }

    public static function ListarTodos()
    {
        $pdo = Venta::ObtenerConexion();
        $sentencia = $pdo->prepare('SELECT * FROM ventas');
        $sentencia->execute();

        $args = array("id", "fecha", "cantidad", "foto", "id_usuario", "id_arma");

        $retornoListado = $sentencia->fetchall(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Venta', $args);
        return $retornoListado;
    }
    public static function ListarVentasEEUU()
    {
        $listaVentas = Venta::ListarTodos();
        $nuevaLista = [];
        foreach ($listaVentas as $value) {
            if (Venta::ValidarFecha($value->fecha)) {
                array_push($nuevaLista, $value);
            }
        }
        return $nuevaLista;

    }
    public static function ValidarFecha($fechaString)
    {
        $nuevaFecha = strtotime($fechaString);
        $nuevaFechaDos = getdate($nuevaFecha);
        $mes = $nuevaFechaDos['mon'];
        $dia = $nuevaFechaDos['mday'];

        if ($mes == 11 && 16 > $dia && $dia > 13) {
            return true;
        }
        return false;
    }
    public static function CargarUno($fecha, $cantidad, $id_usuario, $id_arma)
    {

        $nombreUsuario = Usuario::GetNombreByID($id_usuario);
        $nombre_arma = Arma::GetNombreByID($id_arma);
        $rutaFoto = 'C:/xampp/htdocs/Parcial_2/app/' . Arma::GetFotoByID($id_arma);
        $directorioDestino = 'C:/xampp/htdocs/Parcial_2/app/FotosArmas2023/' . $nombreUsuario . '_' . $nombre_arma . '_' . $fecha . '_' . '.jpg';

        $copiaOk = copy($rutaFoto, $directorioDestino);

        $pdo = Venta::ObtenerConexion();

        $sentencia = $pdo->prepare('INSERT into ventas (fecha, cantidad, foto, id_usuario, id_arma) values (:fecha, :cantidad, :foto, :id_usuario, :id_arma)');

        $execOK = $sentencia->execute(array(':fecha' => $fecha, ':cantidad' => $cantidad, ':foto' => $directorioDestino, ':id_usuario' => $id_usuario, ':id_arma' => $id_arma));

        if ($nombreUsuario != false && $nombre_arma != false && $rutaFoto != false && $copiaOk != false && $execOK != false) {

            return true;
        } else {
            return false;
        }
    }

    public static function ListarPDF()
    {
        $listaCompleta = Venta::ListarTodos();
        $listaNueva = [];
        $fechaActual = date('d-m-Y');
        //var_dump($listaCompleta);
        if (count($listaCompleta) > 0) {
            foreach ($listaCompleta as $venta) {
                if (Venta::RestarFechas($fechaActual, $venta->fecha)) {
                    array_push($listaNueva, $venta);
                }
            }
        }
        return $listaNueva;
    }
    public static function RestarFechas($fechaUno, $fechaDos)
    {

        $fechaActual = strtotime($fechaUno);
        $fechaAnterior = strtotime($fechaDos);
        $dias_segundos = 2678400; // Segunsdos en un mes 30 * 86400;

        if (($fechaActual - $fechaAnterior) < $dias_segundos) {
            return true;
        }
        return false;
    }
    public static function OrdenarPDF($response, $orden)
    {
        //$params = $request->getQueryParams();
        $listaPDF = Venta::ListarPDF();

        $listaOrdenada = [];
        if ($orden == "ascendente") {
            Venta::array_sort_by($listaPDF, 'fecha', $order = SORT_ASC);
            $listaOrdenada = $listaPDF;
            $response->withStatus(200);
        } else if ($orden == "descendente") {
            Venta::array_sort_by($listaPDF, 'fecha', $order = SORT_DESC);
            $listaOrdenada = $listaPDF;
            $response->withStatus(200);
        } else {
            $response->getBody()->write("No ha ingresado un parámetro válido");
            $response->withStatus(500);
        }
        return $listaOrdenada;
    }

    static function array_sort_by(&$arrIni, $col, $order = SORT_ASC)
    {
        $arrAux = array();
        foreach ($arrIni as $key => $row) {
            $arrAux[$key] = is_object($row) ? $arrAux[$key] = strtotime($row->$col) : strtotime($row[$col]);
            $arrAux[$key] = strtolower($arrAux[$key]);
        }
        array_multisort($arrAux, $order, $arrIni);
    }

    static function GuardarPDF($orden, $request, $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader; //(__DIR__ . '\app\plantillas');
        $twig = new \Twig\Environment($loader, ['debug' => true]);
        $twig->addExtension(new Twig\Extension\DebugExtension());
        $view = $request->getAttribute('view');
        $listaVentas = Venta::OrdenarPDF($response, $orden);

        $view = $request->getAttribute('view');
        $nuevasVentas = array();
        foreach ($listaVentas as $value) {
            $unaVenta = array($value->id, $value->fecha, $value->cantidad, $value->foto, $value->id_usuario, $value->id_arma);
            array_push($nuevasVentas, $unaVenta);
        }

        $html = $view->render('pdf.twig', ['ventas' => $nuevasVentas]);
        $pdf = new TCPDF();
        $pdf->SetMargins(3, 3, 3);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('ventas.pdf', 'D'); // Descargar el PDF      
        return $response;
    }

    static function DescargarCSV($listaFiltrada, $response)
    {
        $flagGuardado = false;

        try {

            $delimitador = ";";
            $archivo = fopen('ventas.csv', 'w');

            $header = ["Id", "Fecha", "Cantidad", "Foto", "Id_Usuario", "Id_Arma"];
            fputcsv($archivo, $header, $delimitador);
            fputcsv($archivo, []);

            foreach ($listaFiltrada as $venta) {
                $fila = [$venta->id, $venta->fecha, $venta->cantidad, $venta->foto, $venta->id_usuario, $venta->id_arma];
                if (fputcsv($archivo, $fila, ";")) {
                    $flagGuardado = true;
                }
            }

            fclose($archivo);

            if ($flagGuardado) {
                return true;
            }

        } catch (Exception $e) {
            $response->getBody()->write('Error: ' . $e->getMessage());
            $response->withStatus(500);
            return false;
        }
        return false;
    }
}

?>