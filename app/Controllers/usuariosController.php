<?php

require_once './Models/usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController implements IApiUsable {

    public function CargarUno($request, $response, $args) {

        $parametros = $request -> getParsedBody();

        if (!isset($parametros["nombre"]) || !isset($parametros["apellido"]) || !isset($parametros["dni"]) || !isset($parametros["email"]) || !isset($parametros["password"]) || !isset($parametros["puesto"])) {

            $payload = json_encode(array("ERROR" => "Los parámetros obligatorios para cargar un nuevo usuario son: nombre, apellido, dni, email, password, puesto, sector"));

        } else {

            $resultado = false;
    
            if ($parametros["puesto"] === 'mozo' && $parametros["puesto"] === 'socio') {
                
                $usuario = new Usuario($parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['email'], $parametros['clave'], $parametros['puesto'], "", true);
                $resultado = $usuario -> GuardarUsuario();
                
            } else if(isset($parametros["puesto"])){

                $usuario = new Usuario($parametros['nombre'], $parametros['apellido'], $parametros['dni'], $parametros['email'], $parametros['clave'], $parametros['puesto'], $parametros['sector']);
                $resultado = $usuario -> GuardarUsuario();
            }
            else{

                $payload = json_encode(array("ERROR" => "El parametro sector es obligatorio"));
            }
    
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

        if (isset($args[ "puesto" ])) {

            $lista = Usuario::ObtenerUsuariosPorPuesto($args["puesto"], true);

            if (is_array($lista)) {

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
        if (isset($args[ "dni" ])) {

            $usuario = Usuario::ObtenerPorDNI($args["dni"], true);

            if ($usuario) {
                $payload = json_encode(array("Usuario" => $usuario));
            } else {
                $payload = json_encode(array("ERROR" => "No se encontró al usuario con el DNI {$args["dni"]}"));
            }
        } 
        else if (isset($args[ "id" ])) {

            $usuario = Usuario::ObtenerPorID($args["id"], true);

            if ($usuario) {
                $payload = json_encode(array("Usuario" => $usuario));
            } else {
                $payload = json_encode(array("ERROR" => "No se encontró al usuario con el ID {$args["id"]}"));
            }
        }
        else {

            $payload = json_encode(array("ERROR" => "El parámetro 'dni' o 'id' son obligatorios"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args) {
        if (isset($args[ "id" ])) {
        
            $resultado = Usuario::Eliminar($args["id"]);

            if ($resultado) {

                $payload = json_encode(array("Resultado" => `Se eliminó el usuario con el id {$args["id"]}`));

            } else {

                $payload = json_encode(array("ERROR" => `No seencontró el usuario con el id {$args["id"]}`));
            }
        } 
        else {

            $payload = json_encode(array("ERROR" => "El parámetro 'id' es obligatorio."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

	public function ModificarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody ();

        if (!isset($parametros["id"]) || !isset($parametros["nombre"]) || !isset($parametros["apellido"]) || !isset($parametros["dni"]) || !isset($parametros["email"]) || !isset($parametros["password"]) || !isset($parametros["puesto"])) {

            $usuario = Usuario::ObtenerPorID($parametros["id"], true);

            if ($usuario) {

                $nuevoPuestoUsuario = $parametros["puesto"];

                if ($nuevoPuestoUsuario === 'mozo' || $nuevoPuestoUsuario === 'socio') {

                    $usuario -> sector = " ";

                } else if ($nuevoPuestoUsuario != 'mozo' && $nuevoPuestoUsuario != 'socio') {

                    if (!isset($parametros["sector"])) {

                        $usuario -> sector = $parametros["sector"];

                    } else {

                        $payload = json_encode(array("ERROR" => "Se debe especificar un sector si el empleado no es un mozo o un socio"));
                    }
                }

                if (!isset($payload)) {

                    $usuario -> nombre = $parametros["nombre"];                
                    $usuario -> apellido = $parametros["apellido"];                
                    $usuario -> dni = $parametros["dni"];                
                    $usuario -> email = $parametros["email"];                
                    $usuario -> puesto = $parametros["puesto"];
                    if ($usuario -> Modificar()) {
                        $payload = json_encode(array("Usuario modificado:" => $usuario));
                    } else {
                        $payload = json_encode(array("ERROR" => "No se pudo modificar el usuario"));
                    }
                }
            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar al usuario para realizar la modificación"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parámetro 'id', 'nombre', 'apellido', 'dni', 'email' y 'puesto' son obligatorios para modificar un usuario"));
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