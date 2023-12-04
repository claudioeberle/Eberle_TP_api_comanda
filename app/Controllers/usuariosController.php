<?php

require_once './Models/usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController implements IApiUsable {

    public function CargarUno($request, $response, $args) {

        $parametros = $request -> getParsedBody();

        if (!isset($parametros["nombre"]) || !isset($parametros["apellido"]) || !isset($parametros["dni"]) || !isset($parametros["email"]) || !isset($parametros["password"]) || !isset($parametros["puesto"]) || !isset($parametros["sector"])) {

            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo usuario son: nombre, apellido, dni, email, password, puesto, sector"));

        } else {

            $resultado = false;
            $usuario = new Usuario(0, $parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['email'], $parametros['password'], $parametros['puesto'], $parametros['sector'], true);
            $resultado = $usuario -> GuardarUsuario();
            
            if (is_numeric($resultado)) {

                $payload = json_encode(array("Resultado" => "Se ha creado con éxito un usuario con el ID {$resultado}"));

            } else{

                $payload = json_encode(array("ERROR" => "Hubo un error durante el alta del nuevo usuario"));
            }
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {
        $lista = Usuario::ObtenerTodosLosUsuarios(true);

        if (is_array($lista)) {

            $payload = json_encode(array("Lista" => $lista));

        } else {

            $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los usuarios"));
        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPorPuesto($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($parametros[ "puesto" ])) {

            $lista = Usuario::ObtenerUsuariosPorPuesto($parametros["puesto"], true);

            if ($lista) {

                $payload = json_encode(array("Lista" => $lista));

            } else {

                $payload = json_encode(array("ERROR" => "Hubo un error al obtener todos los usuarios"));
            }
        } else {

            $payload = json_encode(array("ERROR" => "El parámetro 'puesto' es obligatorio para traer a los empleados por puesto"));

        }

        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($parametros["dni"])) {

            $usuario = Usuario::ObtenerPorDNI($parametros["dni"], true);

            if ($usuario) {
                $payload = json_encode(array("Usuario" => $usuario));
            } else {
                $payload = json_encode(array("ERROR" => "No se encontró al usuario con el DNI {$parametros["dni"]}"));
            }
        } 
        else if (isset($parametros[ "id" ])) {

            $usuario = Usuario::ObtenerPorID($parametros["id"], true);

            if ($usuario) {
                $payload = json_encode(array("Usuario" => $usuario));
            } else {
                $payload = json_encode(array("ERROR" => "No se encontró al usuario con el ID {$parametros["id"]}"));
            }
        }
        else {

            $payload = json_encode(array("ERROR" => "El parámetro 'dni' o 'id' son obligatorios"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function EliminarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($parametros["id"])) {
        
            $resultado = Usuario::Eliminar($parametros["id"]);

            if ($resultado) {

                $payload = json_encode(array("Resultado" => "Se suspendio el usuario con el id {$parametros["id"]}"));

            } else {

                $payload = json_encode(array("ERROR" => "No se encontró el usuario con el id {$parametros["id"]}"));
            }
        } 
        else {

            $payload = json_encode(array("ERROR" => "El parametro 'id' es obligatorio."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($parametros["id"]) || isset($parametros["nombre"]) || isset($parametros["apellido"]) 
        || isset($parametros["dni"]) || isset($parametros["email"]) || isset($parametros["password"]) 
        || isset($parametros["puesto"]) || isset($parametros["sector"])) {

            $usuario = Usuario::ObtenerPorID($parametros["id"], false);

            if ($usuario) {

                $usuario -> nombre = $parametros["nombre"];                
                $usuario -> apellido = $parametros["apellido"];                
                $usuario -> dni = $parametros["dni"];                
                $usuario -> email = $parametros["email"];                
                $usuario -> puesto = $parametros["puesto"];
                $usuario -> sector = $parametros["sector"];

                if ($usuario -> Modificar()) {

                    $payload = json_encode(array("Usuario modificado:" => $usuario));
                } else {

                    $payload = json_encode(array("ERROR" => "No se pudo modificar el usuario"));
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar al usuario para realizar la modificacion"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'id', 'nombre', 'apellido', 'dni', 'email', 'passwpord', 'puesto' y sector son obligatorios para modificar un usuario"));
        }
        
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function Login($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($parametros["email"]) && isset($parametros["clave" ])) {

            $resultado = Usuario::Login($parametros["email"], $parametros["clave"]);

            if (is_string($resultado)) {

                $payload = json_encode(array("Resultado" => $resultado));

            } else {

                $payload = json_encode(array("ERROR" => "Hubo un error al intentar iniciar sesion"));
            }
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

?>