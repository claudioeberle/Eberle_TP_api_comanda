<?php

require_once 'C:\xampp\htdocs\zz-api-comanda\app\//db/accesoDatos.php';

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
        $consulta = $objetoAccesoDatos -> RetornarConsulta("INSERT INTO productos (nombre, tipo, sector, precio) VALUES (:nombre, :tipo, :sector, :precio)");
        $consulta->bindParam(':nombre', $this -> nombre);
        $consulta->bindParam(':tipo', $this -> tipo);
        $consulta->bindParam(':sector', $this -> sector);
        $consulta->bindParam(':precio', $this -> precio);

        $resultado = $consulta -> execute();
        if ($resultado) {
            
            $retorno = $objetoAccesoDatos -> RetornarUltimoIdInsertado();
        }
        return $retorno;
    }

    public static function ObtenerTodosLosProductos() {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM productos";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $resultado = $consulta->execute();
        if ($resultado) {
            $arrayObtenido = array();
            $productos = array();
            $arrayObtenido = $consulta->fetchAll(PDO::FETCH_OBJ);
            foreach($arrayObtenido as $producto){
                $productoAux = new Producto($producto->id, $producto->nombre, $producto->tipo, $producto->sector, $producto->precio);
                $productos[] = $productoAux;
            }
            $retorno = $productos;
        }
        return $retorno;
    }

    public static function ObtenerPorID($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $query = "SELECT * FROM productos WHERE id = :id";
        $consulta = $objetoAccesoDatos -> RetornarConsulta($query);
        $consulta -> bindParam(':id', $id);
        $resultado = $consulta -> execute();
        if ($resultado) {
            $prod = $consulta->fetchObject();
            if($prod){
                $producto = new Producto($prod->id, $prod->nombre, $prod->tipo, $prod->sector, $prod->precio);
                $retorno = $producto;
            }
        }
        return $retorno;
    }

    public static function Eliminar($id) {
        $retorno = false;
        $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDatos -> RetornarConsulta("DELETE FROM productos WHERE id = :id");
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
        $consulta = $objetoAccesoDatos -> RetornarConsulta("UPDATE productos SET nombre = :nombre, tipo = :tipo, sector = :sector, precio = :precio WHERE id = :id");
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