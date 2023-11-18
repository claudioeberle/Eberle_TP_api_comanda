<?php

require_once '..//db/accesoDatos.php';

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
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
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
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
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
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Usuario');
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
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':dni', $dni);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchObject('Usuario');
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
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchObject('Usuario');
        }
        return $retorno;
    }

    public static function ObtenerUsuariosPorPuesto($puesto, $activo = false) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        if ($activo) {
            $query = "SELECT * FROM usuarios WHERE activo = TRUE AND puesto = :puesto";
        } else {
            $query = "SELECT * FROM usuarios WHERE puesto = :puesto";
        }
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':puesto', $puesto);
        $resultado = $consulta -> execute();
        if ($resultado && $consulta -> rowCount() > 0) {
            $retorno = $consulta -> fetchAll(PDO::FETCH_CLASS, 'Usuario');
        }
        return $retorno;
    }
    
    public static function Login($email, $clave) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("SELECT * FROM usuarios WHERE email = :email AND activo = TRUE");
        $consulta -> bindParam(':email', $email);
        $resultado = $consulta -> execute();

        if ($resultado && $consulta -> rowCount() > 0) {
            $usuario = $consulta -> fetchObject('Usuario');
            if ($clave === $usuario -> clave) {
                $retorno = "Login Exitoso";
            } else {
                $retorno = "Login fallido - Revise los datos ingresados";
            }
        } else {
            $retorno = "El email no se encuentra registrado o el usuario fue dado de baja";
        }
        return $retorno;
    }

    public static function Eliminar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE usuarios SET activo = FALSE WHERE id = :id");
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
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
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