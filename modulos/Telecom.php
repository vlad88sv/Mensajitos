<?php
function Telecom_Nombre()
{
    return "Telecom:Claro/Personal";
} 
function Telecom_Enviar($telefono, $mensaje, $firma)
{
	$headers  = "To: $telefono<$telefono@sms.claro.com.sv>" . "\r\n";
	$headers .= "From: My noticlaro<noticlaro@sms.claro.com.sv>" . "\r\n";
    $headers .= "Reply-To: sms@todosv.com" . "\r\n";
    $headers .= "Return-Path: sms@todosv.com" . "\r\n";
    $headers .= "X-Mailer: mensajitos.php" . "\r\n";
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";

    $to = $telefono."@sms.claro.com.sv" . "\r\n";
    $subject = $firma;
    $body = $mensaje;
    if (mail($to, $subject, $body, $headers)) {
        return true;
    } else {
        return false;
    }
}
?>
