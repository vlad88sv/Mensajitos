<?php
/**
INTELFON, S.A. DE C.V. (RED)
7-980-0000  7-982-5999
7-983-0000  7-983-9999
*/
function Red_Nombre() {
  return "Red:Intelfon";
}

function Red_Enviar($telefono,$mensaje,$firma) { 

  $snoopy = new Snoopy;

  // Opciones de Snoopy (Mensajitos se muestra como IExplore 6.0)
  // Esto es por las paginas que bloquean a navegadores que no
  // sean Internet Explorer
  $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

  // URL referente:
  $snoopy->referer = "http://red.com.sv/redsms/index.php";
  // URL para mensajes de Red (Intelfon)
  $submit_url = "http://sms.red.com.sv:13013/cgi-bin/sendsms" ;
  $submit_vars["to"] = $telefono;
  $submit_vars["text"] = $mensaje.$firma;
  $submint_vars["Submit"]= $Enviar;
  $snoopy->submit($submit_url,$submit_vars);
  return true;
}
?>