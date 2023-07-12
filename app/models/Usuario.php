<?php
//include_once __DIR__ . "/../bd/AccesoDatos.php";
//include_once __DIR__ . "AccesoDatos.php";
include_once "Venta.php";

class Usuario
{
    public $id;
    public $mail;
    public $tipo;
    public $clave;

    function __construct($id, $mail, $tipo, $clave)
    {
        $this->id = $id;
        $this->mail = $mail;
        $this->tipo = $tipo;
        $this->clave = $clave;
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
        $pdo = Usuario::ObtenerConexion();

        $sentencia = $pdo->prepare('SELECT * FROM usuarios');
        $sentencia->execute();

        $args = array("id", "mail", "tipo", "clave");

        $retornoListado = $sentencia->fetchall(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Usuario', $args);

        return $retornoListado;
    }

    public static function GetNombreByID($id_usuario)
    {
        $pdo = Usuario::ObtenerConexion();
        $sentencia = $pdo->prepare('select mail from usuarios where id = :id');
        $sentencia->execute(array(':id' => $id_usuario));

        $result = $sentencia->fetch(PDO::FETCH_BOTH);

        if ($result != false) {
            $mail = explode("@", $result[0]);
            return $mail[0];
        } else {
            print("Error al obtener la ruta de la foto. Verifique el id del arma");
        }
        return false;
    }

    public static function ListarExotec()
    {
        $id_arma = 204;
        $pdo = Usuario::ObtenerConexion();
        $sentencia = $pdo->prepare('SELECT id_usuario FROM ventas where id_arma = :id_arma');
        $sentencia->execute(array(':id_arma' => $id_arma));

        $listaIDUsuariosExotec = $sentencia->fetchall(PDO::FETCH_BOTH);

        $listaUsuariosExotec = [];
        foreach ($listaIDUsuariosExotec as $value) {


            $pdo_Usuario = Usuario::ObtenerConexion();
            $sentencia = $pdo_Usuario->prepare('SELECT * FROM usuarios where id= :id');
            $sentencia->execute(array(':id' => $value[0]));

            $args = array("id", "mail", "tipo", "clave");
            $nuevoUsuario = $sentencia->fetchall(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'Usuario', $args);
            if ($nuevoUsuario != null) {

                array_push($listaUsuariosExotec, $nuevoUsuario[0]);
            }
        }
        return $listaUsuariosExotec;
    }

    public static function VerificarUsuario($mail, $clave)
    {
        $listaUsuarios = Usuario::ListarTodos();

        foreach ($listaUsuarios as $value) {
            if ($value->mail == $mail && $value->clave == $clave) {
                return $value;
            }
        }
        return null;
    }
}
?>