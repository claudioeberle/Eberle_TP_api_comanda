<?php

require_once '..//db/accesoDatos.php';

class Producto {
    public $id;
    public $nombre;
    public $tipo;
    public $sector;
    public $precio;

    public function __construct($id, $nombre, $tipo, $sector, $precio) {
        $this -> id = $id;
        $this -> nombre = $nombre;
        $this -> tipo = $tipo;
        $this -> sector = $sector;
        $this -> precio = $precio;
    }

    public function GuardarProducto() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("INSERT INTO productos (nombre, tipo, sector, precio) VALUES (:nombre, :tipo, :sector, :precio)");
        $consulta->bindParam(':nombre', $this -> nombre);
        $consulta->bindParam(':tipo', $this -> tipo);
        $consulta->bindParam(':sector', $this -> sector);
        $consulta->bindParam(':precio', $this -> precio);

        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $objetoAccesoDatos -> ObtenerUltimoId();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosProductos() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM productos";
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $resultado = $consulta->execute();
        if ($resultado) {
            $retorno = $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
        }
        return $retorno;
    }

    public static function ObtenerPorID($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM productos WHERE id = :id";
        $consulta = $objetoAccesoDatos -> PrepararConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $retorno = $consulta -> fetchObject('Producto');
        }
        return $retorno;
    }

    public static function Eliminar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> PrepararConsulta("DELETE FROM productos WHERE id = :id");
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
        $consulta = $objetoAccesoDatos -> PrepararConsulta("UPDATE productos SET nombre = :nombre, tipo = :tipo, sector = :sector, precio = :precio WHERE id = :id");
        $consulta -> bindParam(':id', $this -> id);
        $consulta -> bindParam(':nombre', $this -> nombre);
        $consulta -> bindParam(':tipo', $this -> tipo);
        $consulta -> bindParam(':sector', $this -> sector);
        $consulta -> bindParam(':precio', $this -> precio);
        $resultado = $consulta -> execute();

        if ($resultado) {
            $retorno = true;
        }
        return $retorno;
    }
}

?>