<?php

class Helper
{

    public static function verify_email($email)
    {
        return filter_var(filter_var($email, FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL);
    }

    public static function verify_dni($dni)
    {
        $dni = strtoupper($dni);

        $letra = substr($dni, -1, 1);
        $numero = substr($dni, 0, 8);

        // Si es un NIE hay que cambiar la primera letra por 0, 1 ó 2 dependiendo de si es X, Y o Z.
        $numero = str_replace(array('X', 'Y', 'Z'), array(0, 1, 2), $numero);

        $modulo = $numero % 23;
        $letras_validas = "TRWAGMYFPDXBNJZSQVHLCKE";
        $letra_correcta = substr($letras_validas, $modulo, 1);
        if ($letra_correcta != $letra || (strlen($dni) < 9))
            return false;
        else
            return true;

    }
    public static function echoResponse($status_code, $response) {
        $app = \Slim\Slim::getInstance();
        // Http response code
        $app->status($status_code);
        // setting response content type to json
        $app->contentType('application/json');
        echo json_encode($response);
    }

        // FUNCI�N PARA OBTENER FECHA ACTUAL EN FORMATO CODIFICACI�N LARGA
    public static function fechaActual() {

        //$FECHAACTUAL = mktime (date("H"),date("i"),00,date("n"),date("d"),date("Y"),$datoActual);
        $FECHAACTUAL = mktime (date("H"),date("i"),00,date("n"),date("d"),date("Y"));

        return ($FECHAACTUAL);
    }

    public static function  sql_quote($val){

      /*  if(get_magic_quotes_gpc())
            $val = stripslashes($val);

        //chequea si la funci�n existe
        if(function_exists("mysql_real_escape_string"))
            $val = mysql_real_escape_string( $val );

        else //para PHP version <4.3.0 usa addslashes
            $val = addslashes( $val );

    */
        return $val;
    }
}