<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AutentificadorJWT {
    private static $claveSecreta = 'dfsd**A$-0=12mlfgo94';
    private static $tipoEncriptacion = 'HS256';

    public static function CrearToken($datos) {
        $ahora = time();
        $payload = array(
            'iat' => $ahora,
            'exp' => $ahora + (60*60*24),
            'data' => $datos,
            'app' => "api rest comanda"
        );
        return JWT::encode($payload, self::$claveSecreta, self::$tipoEncriptacion);
    }

    public static function VerificarToken($token) {
        if (empty($token)) {
            throw new Exception("El token esta vacio.");
        }
        try {
            $decodificado = JWT::decode(
                $token,
                new Key(self::$claveSecreta, self::$tipoEncriptacion)
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function ObtenerPayLoad($token) {
        if (empty($token)) {
            throw new Exception("El token esta vacio.");
        }
        return JWT::decode(
            $token,
            new Key(self::$claveSecreta, self::$tipoEncriptacion)
        );
    }

    public static function ObtenerData($token) {
        return JWT::decode(
            $token,
            new Key(self::$claveSecreta, self::$tipoEncriptacion)
        ) -> data;
    }

}

?>