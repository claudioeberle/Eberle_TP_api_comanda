<?php

require_once './Models/producto.php';
require_once './Interfaces/IApiUsable.php';

class ProductoController implements IApiUsable {

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (!isset($parametros["nombre"]) || !isset($parametros["tipo"]) || !isset($parametros["sector"]) || !isset($parametros["precio"])) { 

            $payload = json_encode(array("ERROR" => "Los parametros obligatorios para agregar un nuevo producto a la carta son: nombre, tipo, sector y precio"));

        } else {

            $producto = new Producto($parametros['nombre'], $parametros['tipo'], $parametros['sector'], $parametros['precio']);
            $resultado = $producto -> GuardarProducto();

            if (is_numeric($resultado)) {

                $payload = json_encode(array("Resultado" => "Se ha creado con éxito el producto"));

            } else {

                $payload = json_encode(array("ERROR" => "Hubo un error en el alta del producto"));
            }
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {

        $lista = Producto::ObtenerTodosLosProductos();

        if (is_array($lista)) {

            $payload = json_encode(array("Productos" => $lista));

        } else {

            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los productos"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {

        if (isset($args["id"])) {

            $producto = Producto::ObtenerPorID($args["id"]);

            if ($producto) {

                $payload = json_encode(array("Producto" => $producto));

            } else {

                $payload = json_encode(array("ERROR" => "No se pudo encontrar el producto con ID {$args["id"]}"));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parametro 'id' es obligatorio"));
        }

        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

	public function EliminarUno($request, $response, $args) {

        if (isset($args["id"])) {

            $resultado = Producto::Eliminar($args["id"]);

            if ($resultado) {

                $payload = json_encode(array("Resultado" => "Se ha dado de baja el producto con ID {$args["id"]}"));
                
            } else {

                $payload = json_encode(array("ERROR" => "No se pudo encontrar el producto."));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio."));
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();
        if (isset($parametros["id"]) && isset($parametros["nombre"]) && isset($parametros["tipo"]) && isset($parametros["sector"]) && isset($parametros["precio"])) { 

            $producto = Producto::ObtenerPorID($parametros["id"]);

            if ($producto) {

                $producto -> tipo = $parametros["tipo"];
                $producto -> sector = $parametros["sector"];
                $producto -> precio = $parametros["precio"];

                if ($producto -> Modificar()) {

                    $payload = json_encode(array("producto modificado:" => $producto));

                } else {

                    $payload = json_encode(array("ERROR" => "No se pudo modificar el producto"));
                }
            } else {

                $payload = json_encode(array("ERROR" => "No se encontro el producto."));
            }
        } else {

            $payload = json_encode(array("ERROR" => "Los parámetros 'id', 'nombre, 'tipo', 'sector' y 'precio' son obligatorios"));
        }
        
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>