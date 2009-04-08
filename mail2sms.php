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
    do_post_request('http://sms.todosv.com/index.php',  http_build_query($datos));
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
        if (preg_match("/^From: .*<(.*)>.*/", $lines[$i], $matches)) {
            $from = $matches[1];
        }
        if (preg_match("/^To: [<]{0,1}(.*)@/", $lines[$i], $matches)) {
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
//echo $from . "\n";
//echo $to . "\n";
//echo $subject . "\n";
//echo $message . "\n";
tsv_sms_enviar($to, $subject, $from);
exit(0);
?>
