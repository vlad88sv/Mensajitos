<?php
function Telecom_Nombre()
{
    return "Telecom:Claro/Personal";
} 
function Telecom_Enviar($telefono, $mensaje, $firma)
{
    $headers = 'From: noticlaro@sms.claro.com.sv' . "\n" .
        'Reply-To: noticlaro@sms.claro.com.sv' . "\n";
    $to = $telefono."@sms.claro.com.sv\n";
    $subject = $firma;
    $body = $mensaje;
    if (mail($to, $subject, $body, $headers)) {
        return true;
    } else {
        return false;
    }
}
?>