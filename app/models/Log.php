<?php
include_once "Venta.php";

class Log
{
    public $id;
    public $id_usuario;
    public $id_arma;
    public $accion;
    public $fecha;

    function __construct($id, $id_usuario, $id_arma, $accion, $fecha)
    {
        $this->id = $id;
        $this->id_usuario = $id_usuario;
        $this->id_arma = $id_arma;
        $this->accion = $accion;
        $this->fecha = $fecha;
    }

    public static function ObtenerConexion()
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
    public static function GuardarLog($id_usuario, $id_arma, $accion, $fecha)
    {
        try {
            $pdo = Log::ObtenerConexion();
            $sentencia = $pdo->prepare("Insert into logs (id_usuario, id_arma, accion, fecha) values (:id_usuario, :id_arma, :accion, :fecha)");
            $sentencia->execute(array(":id_usuario" => $id_usuario, ":id_arma" => $id_arma, ":accion" => $accion, ":fecha" => $fecha));
            if ($sentencia != false) {
                return true;
            }

        } catch (Exception $e) {
            print("Error: No se pudo guardar el log");
            print "Error!: " . $e->getMessage();
        }
        return false;
    }
    public static function ListarTodos()
    {
        $pdo = Log::ObtenerConexion();
        $sentencia = $pdo->prepare('SELECT * FROM logs');
        $sentencia->execute();

        $args = array("id", "id_usuario", "id_arma", "accion", "fecha");

        $retornoListado = $sentencia->fetchall(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Log', $args);
        return $retornoListado;
    }
    public static function ListarPDF()
    {
        $listaCompleta = Log::ListarTodos();
        $listaNueva = [];
        $fechaActual = date('d-m-Y');
        //var_dump($listaCompleta);
        if (count($listaCompleta) > 0) {
            foreach ($listaCompleta as $log) {
                if (Log::RestarFechas($fechaActual, $log->fecha)) {
                    array_push($listaNueva, $log);
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
        $listaPDF = Log::ListarPDF();

        $listaOrdenada = [];
        if ($orden == "ascendente") {
            Log::array_sort_by($listaPDF, 'fecha', $order = SORT_ASC);
            $listaOrdenada = $listaPDF;
            $response->withStatus(200);
        } else if ($orden == "descendente") {
            Log::array_sort_by($listaPDF, 'fecha', $order = SORT_DESC);
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

    static function GuardarPDF($request, $response)
    {
        $loader = new \Twig\Loader\FilesystemLoader; //(__DIR__ . '\app\plantillas');
        $twig = new \Twig\Environment($loader, ['debug' => true]);
        $twig->addExtension(new Twig\Extension\DebugExtension());
        $view = $request->getAttribute('view');
        $listaLogs = Log::ListarTodos();

        $view = $request->getAttribute('view');
        $nuevosLogs = array();
        foreach ($listaLogs as  $value) {
            $unLog = array($value->id, $value->id_usuario, $value->id_arma, $value->accion, $value->fecha);
            array_push($nuevosLogs, $unLog);
        }
  
        $html = $view->render('pdf_log.twig', ['ventas' => $nuevosLogs]);
        $pdf = new TCPDF();
        $pdf->SetMargins(3, 3, 3);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('logs.pdf', 'D'); // Descargar el PDF      
        return $response;
    }



}
?>