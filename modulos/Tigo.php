<?php
function Tigo_Nombre() {
  return "Telemovil:Tigo";
}

function Tigo_Enviar($telefono,$mensaje,$firma) {
    global $MiBD_OK;
    if ( !$MiBD_OK ) {
  $MDB = new iniParser(dirname(__FILE__)."/misc/Tigo.datos.db");
    }

  //**************************************************
  // Snoop
  $snoopy = new Snoopy;
  $snoopy->agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
  //**************************************************
  
  //**************************************************
  //Configuramos el Proxy.
  $Datos_Proxy = explode(":",cProxy());
  $snoopy->proxy_host = $Datos_Proxy[0];
  $snoopy->proxy_port = $Datos_Proxy[1];
  //echo "Servidor usado: $Datos_Proxy[0]:$Datos_Proxy[1]<br>";
  //**************************************************

  //**************************************************
  $firma = urlencode($firma);
  $mensaje = urlencode($mensaje);
  //**************************************************

  //**************************************************
  //Verificamos si hay alguna sesión disponible para este número
  $no_hay_sesion_vigente = true;
  if ( $MiBD_OK ) {
      $ultimo_uso_de_sesion = ObtenerValorSQL("xsms_modulos_tigo","valor","rama='$telefono.ultimo'");
  }else{
  $ultimo_uso_de_sesion = $MDB->getValue($telefono,"ultimo");
  }
  if ($ultimo_uso_de_sesion) {
	//echo "Time: " . (time() -  $ultimo_uso_de_sesion)."<br />";
	if ((time() -  $ultimo_uso_de_sesion) < 120) {
		$no_hay_sesion_vigente = false;
        if ( $MiBD_OK ) {
            $ultimo_uso_de_sesion = ObtenerValorSQL("xsms_modulos_tigo","valor","$telefono.sesion");
        }else{
		$session = $MDB->getValue($telefono,"sesion");
        }
		//echo "Sesion reusada: $session <br />" ;
	}
  }
  //**************************************************
  // ---------------------------------------------------------------------------------------    
  if ($no_hay_sesion_vigente) {
  // Inicio de sesion en el gateway de mensajes
  $comando = "http://interactivo.mensajito.com/interactivo_sv/client.php?orden=1&nick=".$firma."&foo=".rand(10000,90000);
  //echo "C1: ".$comando."<br />";
  $snoopy->fetch($comando);
  //echo "R1: ".$snoopy->results."<br />";
  // Copiando el ID de sesion
  $iPos = stripos($snoopy->results,"session=") + 8;
  $session = substr($snoopy->results, $iPos);
  //echo "Sesion obtenida: ".$session."<br />";
  $comando = "http://interactivo.mensajito.com/interactivo_sv/client.php?orden=21&session=".$session."&nick=".$firma."&dstphone=503".$telefono."&pin=undefined&foo=".rand(10000,90000);
  // Agregando al telefono destino
  //echo "C2: ".$comando."<BR>";
  $snoopy->fetch($comando);
   //Acepto el telefono?
  //echo "R2: ".$snoopy->results."<br>";
  //echo "Sesion creada<br />";  
  if (!eregi('^invitar.*', $snoopy->results, $textoEncontrado)) {
     //echo "ERROR: Tigo | No dio invitacion<br />";
     return false;
  }
  if ( $MiBD_OK ) {
      EstablecerValorSQL("xsms_modulos_tigo","$telefono.ultimo='".time()."'");
      EstablecerValorSQL("xsms_modulos_tigo","$telefono.sesion='". $session."'");
  }else{
  $MDB->setValue($telefono, "ultimo", time());
  $MDB->setValue($telefono, "sesion", $session);
  $MDB->save();
  }
  }
// ---------------------------------------------------------------------------------------
 
  // Se envia el mensaje
  $comando = "http://interactivo.mensajito.com/interactivo_sv/client.php?orden=3&session=$session&nick=".$firma."&mensaje=".$mensaje."&foo=".rand(999,7000);
  //echo $comando."<br />";
  $snoopy->fetch($comando);
  //echo "RESULTADO:<br /><pre>".$snoopy->results."</pre><br />";
  $srandom = stripos($snoopy->results,"error=0");
  // ---------------------------------------------------------------------------------------
  //Cerramos la sesion
  //  $comando = "http://interactivo.mensajito.com/interactivo_sv/client.php?orden=6&session=$session&foo=".rand(10000,90000);
  //echo "Comando:".$comando."<br />";
  // $snoopy->fetch($comando);
  // ---------------------------------------------------------------------------------------

if ($srandom !== FALSE)
  return true;
else
  return false;
}
?>
