#!/usr/bin/php -q
<?php
//sms.todosv.com wrapper
error_reporting(0);

function do_post_request($url, $data, $optional_headers = null)
{
$params = array('http' => array('method' => 'POST','content' => $data));
if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
}
$ctx = stream_context_create($params);
$fp = @fopen($url, 'rb', false, $ctx);
if (!$fp) {
    echo "Problem with $url, $php_errormsg". "\n";
}
$response = @stream_get_contents($fp);
if ($response === false) {
    echo "Problem reading data from $url, $php_errormsg". "\n";
}
return $response;
}

function tsv_sms_enviar($telefono, $mensaje, $firma){
    $datos = array('telefono' => $telefono, 'mensaje' => $mensaje, 'firma' => $firma, 'enviar' => '¡Enviar Mensaje!');
    return do_post_request('http://sms.todosv.com/index.php?visual=estado',  http_build_query($datos));
}

// read from stdin
$email = file_get_contents("php://stdin");

// handle email
$lines = explode("\n", $email);

// empty vars
$from = "";
$to = "";
$subject = "";
$headers = "";
$message = "";
$splittingheaders = true;

for ($i=0; $i < count($lines); $i++) {
    if ($splittingheaders) {
        // this is a header
        $headers .= $lines[$i]."\n";

        // look out for special headers
        if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
            $subject = $matches[1];
        }
        if (preg_match("/^From: .*<(.*)>.*/", $lines[$i], $matches) && !$from) {
            $from = $matches[1];
        }
        if (preg_match("/^To: [<]{0,1}(.*)@/", $lines[$i], $matches) && !$to) {
            $to = $matches[1];
        }
        if (preg_match("/^X-Forwarded-For: (.*) .*/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
        if (preg_match("/^X-Forwarded-To: (.*)@/", $lines[$i], $matches)) {
            $to = $matches[1];
        }

    } else {
        // not a header, but message
        $message .= $lines[$i]."\n";
    }

    if (trim($lines[$i])=="") {
        // empty line, header section has ended
        $splittingheaders = false;
    }
}

$message = substr(str_replace("\n"," ",$message),0,110);

//@file_put_contents("d_".time(),$email);
//echo "FROM:" . $from . "\n";
//echo "TO:" . $to . "\n";
//echo "SUBJECT:" . $subject . "\n";
//echo $message . "\n";
if ( stristr($to,"_r") ) {
	$flag_mail = true;
	$to = str_replace("_r","",$to);
} else {
	$flag_mail = false;
}

$body = tsv_sms_enviar($to, $subject, $from);
$headers =  'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=iso-8859-1' . "\r\n" . 'From: robot@sms.todosv.com' ."\r\n" . 'Reply-To: no_responder_aqui@todosv.com' . "\r\n";
if ($flag_mail) @mail($from,"Estado del mensaje a $to","<html><head><title>Correo de respuesta solicitado por su mensaje enviado a $to</title></head><body>".$body."</body></html>", $headers);
exit(0);
?>
