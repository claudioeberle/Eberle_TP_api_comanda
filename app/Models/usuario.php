<?php

require_once 'C:\xampp\htdocs\zz-api-comanda\app\//db/accesoDatos.php';

class Usuario {
    public $id;
    public $nombre;
    public $apellido;
    public $dni;
    public $email;
    public $password;
    public $puesto;
    public $sector;
    public $activo;

    public function __construct($id, $nombre, $apellido, $dni, $email, $password, $puesto, $sector, $activo) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> apellido = $apellido;
        $this -> dni = $dni;
        $this -> email = $email;
        $this -> password = $password;
        $this -> puesto = $puesto;
        $this -> sector = $sector;
        $this-> activo = $activo;
    }

    public function GuardarUsuario() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "INSERT INTO usuarios (nombre, apellido, dni, email, password, puesto, sector, activo) VALUES (:nombre, :apellido, :dni, :email, :password, :puesto, :sector, :activo)";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':apellido', $this -> apellido);
        $consulta -> bindParam(':dni', $this -> dni);
        $consulta -> bindParam(':email', $this -> email);
        $consulta -> bindParam(':password', $this -> password);
        $consulta -> bindParam(':puesto', $this -> puesto);
        $consulta -> bindParam(':sector', $this -> sector);
        $consulta -> bindParam(':activo', $this -> activo);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> RetornarUltimoIdInsertado();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosUsuarios($activo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        if ($activo) {
            $query = "SELECT * FROM usuarios WHERE activo = TRUE";
        } else {
            $query = "SELECT * FROM usuarios";
        }
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $arrayObtenido = array();
            $usuarios = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $usuario){
                $usuarioAux = new Usuario($usuario->id, $usuario->nombre, $usuario->apellido, $usuario->dni, $usuario->email , $usuario->password , $usuario->puesto , $usuario->sector , $usuario->activo);
                $usuarios[] = $usuarioAux;
            }
            $retorno = $usuarios;
        }
        return $retorno;
    }

    public static function ObtenerPorDNI($dni, $activo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        if ($activo) {
            $query = "SELECT * FROM usuarios WHERE dni = :dni AND activo = TRUE";
        } else {
            $query = "SELECT * FROM usuarios WHERE dni = :dni";
        }
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':dni', $dni);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $usuario = $consulta->fetchObject();
            if($usuario){
                $usuario = new Usuario($usuario->id, $usuario->nombre, $usuario->apellido, $usuario->dni, $usuario->email , $usuario->password , $usuario->puesto , $usuario->sector , $usuario->activo);
                $retorno = $usuario;
            }
        }
        return $retorno;
    }

    public static function ObtenerPorID($id, $activo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        if ($activo) {

            $query = "SELECT * FROM usuarios WHERE id = :id AND activo = TRUE";
        } else {
            
            $query = "SELECT * FROM usuarios WHERE id = :id";
        }
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $usuarioObtenido = $consulta->fetchObject();
            if($usuarioObtenido){
                $usuario = new Usuario($usuarioObtenido->id, $usuarioObtenido->nombre, $usuarioObtenido->apellido, $usuarioObtenido->dni, $usuarioObtenido->email , $usuarioObtenido->password , $usuarioObtenido->puesto , $usuarioObtenido->sector , $usuarioObtenido->activo);
                $retorno = $usuario;
            }
        }
        return $retorno;
    }

    public static function ObtenerUsuariosPorPuesto($puesto, $activo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        if ($activo) {
            $query = "SELECT * FROM usuarios WHERE activo = 1 AND puesto = :puesto";
        } else {
            $query = "SELECT * FROM usuarios WHERE puesto = :puesto";
        }
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':puesto', $puesto);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $arrayObtenido = array();
            $usuarios = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $usuario){
                $usuarioAux = new Usuario($usuario->id, $usuario->nombre, $usuario->apellido, $usuario->dni, $usuario->email , $usuario->password , $usuario->puesto , $usuario->sector , $usuario->activo);
                $usuarios[] = $usuarioAux;
            }
            $retorno = $usuarios;
        }
        return $retorno;
    }
    
    public static function Login($email, $password) {
        $retorno = "Revise los datos ingresados";
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("SELECT * FROM usuarios WHERE email = :email AND activo = true");
        $consulta -> bindParam(':email', $email);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $usuarioObtenido = $consulta->fetchObject();
            if($usuarioObtenido){
                $usuario = new Usuario($usuarioObtenido->id, $usuarioObtenido->nombre, $usuarioObtenido->apellido, $usuarioObtenido->dni, $usuarioObtenido->email , $usuarioObtenido->password , $usuarioObtenido->puesto , $usuarioObtenido->sector , $usuarioObtenido->activo);
                if ($usuario && $password === $usuario -> password) {
                    $retorno = $usuario;
                }
            }
        } else {
            $retorno = "El email no se encuentra registrado o el usuario fue dado de baja";
        }
        return $retorno;
    }

    public static function Eliminar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("UPDATE usuarios SET activo = FALSE WHERE id = :id");
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }

    public function Modificar() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        //nombre, apellido, dni, email, password, puesto, sector, activo
        $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, dni = :dni, email = :email, password = :password, puesto = :puesto, sector = :sector, activo = :activo WHERE id = :id";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':apellido', $this -> apellido);
        $consulta -> bindParam(':dni', $this -> dni);
        $consulta -> bindParam(':email', $this -> email);
        $consulta -> bindParam(':password', $this -> password);
        $consulta -> bindParam(':puesto', $this -> puesto);
        $consulta -> bindParam(':sector', $this -> sector);
        $consulta -> bindParam(':activo', $this -> activo);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }
}

?>