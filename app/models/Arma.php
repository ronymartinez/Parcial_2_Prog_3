<?php
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Slim\Factory\AppFactory;
use Slim\Http\Message;

class Arma
{
    public $id;
    public $precio;
    public $nombre;
    public $foto;
    public $nacionalidad;

    function __construct($id, $precio, $nombre, $foto, $nacionalidad)
    {
        $this->id = $id;
        $this->precio = $precio;
        $this->nombre = $nombre;
        $this->foto = $foto;
        $this->nacionalidad = $nacionalidad;
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
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('SELECT * FROM armas');
        $sentencia->execute();

        $args = array("id", "precio", "nombre", "ruta", "nacionalidad");

        $retornoListado = $sentencia->fetchall(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Arma', $args);
        return $retornoListado;
    }

    public static function ListarPorNacionalidad($nacionalidad)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('SELECT * FROM armas where nacionalidad = :nacionalidad');
        $sentencia->execute(array(':nacionalidad' => $nacionalidad));

        $args = array("id", "precio", "nombre", "ruta", "nacionalidad");

        $retornoListado = $sentencia->fetchall(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Arma', $args);
        return $retornoListado;
    }

    public static function ListarPorID($id)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('SELECT * FROM armas where id = :id');
        $sentencia->execute(array(':id' => $id));
        $args = array("id", "precio", "nombre", "ruta", "nacionalidad");

        $retornoListado = $sentencia->fetchall(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Arma', $args);
        return $retornoListado;
    }
    public static function ObtenerUltimoID()
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('select max(id) from armas');
        $sentencia->execute();

        $result = $sentencia->fetch(PDO::FETCH_BOTH);
        $id = $result[0];

        return $id;
    }
    public static function CargarUno($precio, $nombre, $foto, $nacionalidad)
    {
        $id_arma = Arma::ObtenerUltimoID() + 1;
        $directorioDestino = 'fotos_armas/' . 'arma_N_' . $id_arma . '.jpg';
        $foto->moveTo(__DIR__ . '/../' . $directorioDestino);

        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('INSERT into armas (precio, nombre, foto, nacionalidad) values (:precio, :nombre, :foto, :nacionalidad)');

        if ($sentencia->execute(array(':precio' => $precio, ':nombre' => $nombre, ':foto' => $directorioDestino, ':nacionalidad' => $nacionalidad))) {
            return true;
        } else {
            return false;
        }
    }

    public static function GetFotoByID($id_arma)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('select foto from armas where id = :id');
        $sentencia->execute(array(':id' => $id_arma));
        $result = $sentencia->fetch(PDO::FETCH_BOTH);

        if ($result != false) {
            return $result[0];
        } else {
            print("Error al obtener la ruta de la foto. Verifique el id del arma");
        }
        return false;
    }

    public static function GetNombreByID($id_arma)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('select nombre from armas where id = :id');
        $sentencia->execute(array(':id' => $id_arma));

        $result = $sentencia->fetch(PDO::FETCH_BOTH);

        if ($result != false) {
            return $result[0];
        } else {
            print("Error al obtener el nombre del arma. Verifique el id del arma");
        }
        return false;
    }
    public static function Borrar($id_arma)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('delete from armas where id = :id');
        $sentencia->execute(array(':id' => $id_arma));

        if ($sentencia != false) {
            return true;
        }
        return false;
    }
    public static function ValidarIdActivo($id_arma)
    {
        if (Arma::ListarPorID($id_arma) != null) {
            return true;
        }
        return false;
    }
    public static function ModificarPrecio($id_arma, $precio)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('update armas set precio = :precio where id = :id_arma');
        $sentencia->execute(array(':id_arma' => $id_arma, ':precio' => $precio));

        if ($sentencia != false) {
            return true;
        }
        return false;
    }
    public static function ModificarNombre($id_arma, $nombre)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('update armas set nombre = :nombre where id = :id_arma');
        $sentencia->execute(array(':id_arma' => $id_arma, ':nombre' => $nombre));

        if ($sentencia != false) {
            return true;
        }
        return false;
    }
    public static function ModificarNacionalidad($id_arma, $nacionalidad)
    {
        $pdo = Arma::ObtenerConexion();
        $sentencia = $pdo->prepare('update armas set nacionalidad = :nacionalidad where id = :id_arma');
        $sentencia->execute(array(':id_arma' => $id_arma, ':nacionalidad' => $nacionalidad));

        if ($sentencia != false) {
            return true;
        }
        return false;
    }
    public static function ValidarImagen($id_arma)
    {
        $arma = Arma::ListarPorID($id_arma);

        if ($arma[0]->foto != null) {
            return true;
        }
        return false;
    }
    public static function GuardarImagen($id_arma, $archivo)
    {
        if (Arma::ValidarImagen($id_arma)) {
            $directorioDestino = 'Backup_2023/' . 'arma_N_' . $id_arma . '.jpg';
            $archivo->moveTo(__DIR__ . '/../' . $directorioDestino);
            return 1;
        } else {
            $directorioDestino = 'fotos_armas/' . 'arma_N_' . $id_arma . '.jpg';
            $archivo->moveTo(__DIR__ . '/../' . $directorioDestino);

            $pdo = Arma::ObtenerConexion();
            $sentencia = $pdo->prepare('update armas set foto = :foto where id = :id_arma');
            $sentencia->execute(array(':id_arma' => $id_arma, ':foto' => $directorioDestino));

            if ($sentencia != false) {
                return 2;
            }
        }

        return 0;
    }
    static function GuardarPDF($request, $response, $arma)
    {
        try {
            $loader = new \Twig\Loader\FilesystemLoader; //(__DIR__ . '\app\plantillas');
            $twig = new \Twig\Environment($loader, ['debug' => true]);
            $twig->addExtension(new Twig\Extension\DebugExtension());
            $view = $request->getAttribute('view');

            $view = $request->getAttribute('view');
            //print($arma[0]->id . "<br>");
            $nuevoArrays = [];
            $armaArray = [$arma[0]->id, $arma[0]->precio, $arma[0]->nombre, $arma[0]->foto, $arma[0]->nacionalidad];
            array_push($nuevoArrays, $armaArray);

            $html = $view->render('pdf_arma.twig', ['ventas' => $nuevoArrays]);
            $pdf = new TCPDF();
            $pdf->SetMargins(3, 3, 3);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('arma.pdf', 'D'); // Descargar el PDF  
            return true; 
        } 
        catch (Exception $e) {
            return false;
        }
    }

}
?>