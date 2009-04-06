<?php
function Telecom_Nombre()
{
    return "Telecom:Claro/Personal";
} 
function Telecom_Enviar($telefono, $mensaje, $firma)
{
    $headers = 'From: noticlaro@sms.claro.com.sv' ."\r\n" .
        'Reply-To: noticlaro@sms.claro.com.sv' . "\r\n";
    $to = $telefono."@sms.claro.com.sv";
    $subject = $firma;
    $body = $mensaje;
    if (mail($to, $subject, $body, $headers)) {
        return true;
    } else {
        return false;
    }
}
?>
