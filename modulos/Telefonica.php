<?php
function Telefonica_Nombre() 
{
    return "Telefonica:Movistar";
}
function Telefonica_Enviar($telefono,$mensaje,$firma) {
    $snoopy = new Snoopy;
    $comando="http://aurox.sytes.net/telefonica/enviar.php?fir=".rawurlencode($firma)."&tel=".$telefono."&men=".rawurlencode($mensaje);
    $snoopy->fetch($comando);
    //Evaluar la salida
    if($snoopy->results == '0'){
        return true;
    }else{
        return false;
    }
}
?>