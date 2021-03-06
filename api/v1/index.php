<?php

require_once '../include/helper.php';
require_once '../include/logInit.php';
require_once '../include/dbHelper.php';
require_once '../include/Entrada.php';
require '.././libs/Slim/Slim.php';
require_once 'compraEntradas.php';


\Slim\Slim::registerAutoloader();
/*
 * $log->logg('page','message','priority','class','mail');
 * Page: This is the page where the log is placed. Set to 1 and it will be automatically detected.
Message: This is the message you want to have the log store. You can have some preset messages which are stored in the logger.php file. Set to 1 to have a preset message stored.
Priority: This is the priority or Importance of the log entry. Values can be: High,Medium,Low.
Class: This is the class you want the log entry to have. Values are: Red, Danger, Yellow, Green, and Blue.
Mail: This setting can be yes or no. If it is not specified it will be set to "no".

 * */
header("Access-Control-Allow-Origin: *");
$app = new \Slim\Slim();

$app->get('/concerts', 'getConcerts');
$app->get('/salas', 'getSalas');
$app->get('/concertsCarrousel', 'getConcertsCarrousel');


function validateEmail($email)
{
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $app->stop();
        return false;
    }
    return true;

}

function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        helper::echoResponse(400, $response);
        $app->stop();
    }
}

$app->post('/verifyTicket', function () use ($app) {
    // check for required params
    global $database;

    verifyRequiredParams(array('nombre', 'apellidos', 'dni', 'email','nentradas', 'id_conciertos','grupos'));

    $response = array();


    //echo "aqui-0-";
    $dni = $database->filter($app->request->post("dni"));
    $apellidos = $database->filter($app->request->post("apellidos"));
    $email = $database->filter($app->request->post('email'));
    $nombre = $database->filter($app->request->post("nombre"));
    $id_conciertos = $database->filter($app->request->post("id_conciertos"));
    $nentradas = $database->filter($app->request->post("nentradas"));
    $grupos = $database->filter($app->request->post("grupos"));



    $entrada = new sEntrada();

    crearEntrada($entrada, $nombre, $apellidos, $dni, $email, $id_conciertos, $nentradas, $grupos);

    validarDatosEntrada($entrada);
    validarConcierto($id_conciertos);
    validarEntradaDisponibles($entrada->show_item("id_conciertos"), $entrada->show_item("nentradas"));
    insertarEntrada($entrada);


});


$app->get('/getConcertDetails/:id_conciertos/', function ($id_conciertos) use ($app) {

    global $database;
    global $log;

    try {

        $query = "SELECT id_conciertos,grupos,nombre_sala,codigo_fecha,precio_ant,precio_taq,imagen FROM conciertos c INNER JOIN salas s ON c.id_sala = s.id_sala WHERE id_conciertos = " .$id_conciertos;

        $response["error"] = false;
        $response["concert"] = array();

        $concert = $database->get_results($query);

        $response["concert"] = $concert;

        helper::echoResponse(200, $response);


    } catch (PDOException $e) {
        $log->logg('1', $e->getMessage(), 'High', 'Danger', 'no');

    }
});

$app->get('/busqueda-concert/:text/', function ($text) use ($app) {

    global $database;
    global $log;

    try {

        $query = "SELECT id_conciertos,grupos,nombre_sala,codigo_fecha,precio_ant,precio_taq,imagen FROM conciertos c INNER JOIN salas s ON c.id_sala = s.id_sala WHERE "
                 ."AND c.codigo_fecha >= " . helper::fechaActual()
                 ." c.grupos LIKE '%" .$text. "%' OR s.nombre_sala LIKE '%" .$text. "%' ORDER BY codigo_fecha";

        $response["error"] = false;
        $response["concerts"] = array();

        $concerts = $database->get_results($query);

        $response["concerts"] = $concerts;

        helper::echoResponse(200, $response);


    } catch (PDOException $e) {
        $log->logg('1', $e->getMessage(), 'High', 'Danger', 'no');

    }
});

$app->get('/detalle-sala/:id/', function ($id) use ($app) {

    global $database;
    global $log;

    try {

        $query = "SELECT id_sala,nombre_sala,ciudad_sala,comunidad_sala,logo_sala FROM salas WHERE id_sala = " .$id;

        $response["error"] = false;
        $response["sala"] = array();

        $sala = $database->get_results($query);

        $response["sala"] = $sala;

        helper::echoResponse(200, $response);


    } catch (PDOException $e) {
        $log->logg('1', $e->getMessage(), 'High', 'Danger', 'no');

    }
});

$app->run();

function getConcertsCarrousel()
{
    global $database;
    global $log;
    try {

        //$query = "SELECT * FROM conciertos c, salas s  WHERE c.id_sala = s.id_sala  AND c.codigo_fecha >= " . helper::fechaActual() . "  AND c.visible='Si'  AND c.destacado = 1   ORDER BY c.codigo_fecha ASC ";
        $query = "SELECT * FROM conciertos c, salas s  WHERE c.id_sala = s.id_sala   AND c.visible='Si' "." AND c.destacado = 1   ORDER BY c.codigo_fecha ASC ";

        echo $query;
        $response["error"] = false;
        $response["concerts"] = array();

        $concerts = $database->get_results($query);

        $response["concerts"] = $concerts;

        //$response["concerts"] = json_encode($concerts);
        helper::echoResponse(200, $response);


    } catch (PDOException $e) {
        $log->logg('1', $e->getMessage(), 'High', 'Danger', 'no');

    }
}

function getConcerts()
{
    global $database;
    global $log;
    try {
        echo "hola";
        $query = "SELECT id_conciertos,grupos,nombre_sala,codigo_fecha,precio_ant,precio_taq,imagen FROM conciertos c INNER JOIN salas s ON c.id_sala = s.id_sala WHERE "
                 ." c.codigo_fecha >= " . helper::fechaActual()
                 ." ORDER BY codigo_fecha";

        $response["error"] = false;
        $response["concerts"] = array();

        $concerts = $database->get_results($query);

        $response["concerts"] = $concerts;

        //$response["concerts"] = json_encode($concerts);
        helper::echoResponse(200, $response);


    } catch (PDOException $e) {
        $log->logg('1', $e->getMessage(), 'High', 'Danger', 'no');

    }
}

function getSalas()
{
    global $database;
    global $log;
    try {

        $query = "SELECT id_sala,nombre_sala,ciudad_sala,comunidad_sala,logo_sala FROM salas ORDER BY nombre_sala";

        $response["error"] = false;
        $response["salas"] = array();

        $salas = $database->get_results($query);

        $response["salas"] = $salas;

        helper::echoResponse(200, $response);


    } catch (PDOException $e) {
        $log->logg('1', $e->getMessage(), 'High', 'Danger', 'no');

    }
}
