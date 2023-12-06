<?php

require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/mesa.php';
require_once 'C:\xampp\htdocs\api-comanda-3\app\/Models/encuesta.php';
use Dompdf\Dompdf;

class EncuestasController{

    public function CargarUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (!isset($parametros["codigoMesa"]) || !isset($parametros["puntosMesa"]) || !isset($parametros["puntosResto"]) 
        || !isset($parametros["puntosMozo"]) || !isset($parametros["puntosCocinero"]) || !isset($parametros["experiencia"])) { 

            $payload = json_encode(array("ERROR" => "Los parametros obligatorios para agregar una encuesta son codigoMesa, puntosMesa, puntosResto, puntosMozo, puntosCocinero, experiencia"));
        } else {

            $codigoMesa = $parametros['codigoMesa'];
            $puntosMesa = $parametros['puntosMesa'];
            $puntosResto = $parametros['puntosResto'];
            $puntosMozo = $parametros['puntosMozo'];
            $puntosCocinero = $parametros['puntosCocinero'];
            $experiencia = $parametros['experiencia'];

            $encuestaPrevia = Encuesta::ObtenerPorCodigoMesa($codigoMesa);
            if(!$encuestaPrevia){
                if(($puntosMesa >= 0 && $puntosMesa <= 10) && ($puntosResto >= 0 && $puntosResto <= 10) 
                && ($puntosMozo >= 0 && $puntosMozo <= 10) && ($puntosCocinero >= 0 && $puntosCocinero <= 10)
                && (strlen($experiencia) >= 0 && strlen($experiencia) <= 100)){

                    $mesa = Mesa::ObtenerPorCodigoMesa($codigoMesa);
                    if($mesa){
                        if($mesa->estado === 'cerrada'){
                            $encuesta = new Encuesta(0, $codigoMesa, $puntosMesa, $puntosResto, $puntosMozo, $puntosCocinero, $experiencia);
                            $resultado = $encuesta -> GuardarEncuesta();

                            if ($resultado) {
                                $payload = json_encode(array("Resultado" => "Se ha creado con la encuesta Nº '{$resultado}'"));

                            } else {
                                $payload = json_encode(array("ERROR" => "Hubo un error en el alta de la encuesta"));
                            }
                        } else {
                            $payload = json_encode(array("ERROR" => "Para cargar la encuenta la mesa debe estar cerrada"));
                        }
                    } else {
                        $payload = json_encode(array("ERROR" => "No se encontro una mesa para el codigo {$codigoMesa}"));
                    }
                } else {
                    $payload = json_encode(array("ERROR" => "Revise los datos ingresados. Puntajes 0-10. Exp 0-100"));
                }   
            } else {
                $payload = json_encode(array("ERROR" => "Ya existe una encuesta para esa mesa"));
            }
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args) {

        $lista = Encuesta::ObtenerTodasLasEncuestas();

        if ($lista) {
            $payload = json_encode(["Encuestas" => $lista]);

        } else {
            $payload = json_encode(array("ERROR" => "Hubo un error al obtener las encuestas"));
        }
        $response -> getBody() -> write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        if (isset($parametros["codigoMesa"])) {

            $codigoMesa = $parametros["codigoMesa"];
            $encuesta = Encuesta::ObtenerPorCodigoMesa($codigoMesa);

            if ($encuesta) {
                  $payload = json_encode(array("Encuesta" => $encuesta));

            } else {
                $payload = json_encode(array("ERROR" => "No se pudo encontrar la encuesta de la mesa con codigo {$codigoMesa}"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'codigoMesa' es obligatorio"));
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function ObtenerMejoresComentarios($request, $response, $args) {
        $parametros = $request -> getParsedBody();

        $comentarios = array();
        if (isset($parametros["promedioPuntaje"])) {

            $promedio = $parametros["promedioPuntaje"];
            $encuestas = Encuesta::ObtenerListaEncuestasPorPromedio($promedio);
            if ($encuestas) {

                foreach($encuestas as $encuesta){
                    
                    array_push($comentarios, ['Mesa' => $encuesta->codigoMesa, 'Comentario' => $encuesta->experiencia]);
                }
                $payload = json_encode(array("Mejores encuestas" => $comentarios));

            } else {
                $payload = json_encode(array("ERROR" => "Ninguna encuesta iguala o supera el promedio otorgado"));
            }
        } else {
            $payload = json_encode(array("ERROR" => "El parametro 'promedioPuntaje' es obligatorio"));
        }
        $response -> getBody() -> write($payload);
        return $response -> withHeader('Content-Type', 'application/json');
    }

    public function GuardarLogoPdf($request, $response, $args){

        $dompdf = new Dompdf();
        $html = '<html><body><img src="https://www.cronista.com/files/image/306/306621/5ffe2e44324f6.jpg" alt="LOGO-EMPRESA"></body></html>';
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        
        $tempFolderPath = sys_get_temp_dir();
        $pdfFileName = 'logo-API-COMANDA.pdf';
        $pdfFilePath = $tempFolderPath . '/' . $pdfFileName;
        file_put_contents($pdfFilePath, $pdfContent);

        $response = $response->withHeader('Content-Type', 'application/pdf');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="' . $pdfFileName . '"');
        $response = $response->withHeader('Content-Length', filesize($pdfFilePath));
        readfile($pdfFilePath);
        return $response;
    }
}

?>