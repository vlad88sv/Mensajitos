<?php
function probarS($server, $puerto) {
 $port = $puerto;
 preg_match("/^(http:\/\/)?([^\/]+)/i", "$server", $match);
 $host = $match[2];
 preg_match_all("/\.([^\.\/]+)/",$host, $match);

 $matches[0][0] = $matches[1][0];
 $host = trim($host);
 $socket = "";
 @$socket = fsockopen("$host", $port, $errno, $errstr, 2);
 if(!$socket) {
  return FALSE;
 } else {
  fclose($socket);
  return TRUE;
 }
}

function cProxy(){
$Proxys = array(0 => 'auro1.zapto.org:31280', 'aurox.sytes.net:31280');
$ProxyAleatorio = rand(0, count($Proxys)-1);
$Datos_Proxy = explode(":",$Proxys[$ProxyAleatorio]);
//echo "DP:".$Datos_Proxy[0]."<BR>";
if (probarS($Datos_Proxy[0],$Datos_Proxy[1]))
{
//echo "Proxy ok, devolviendo: $Proxys[$ProxyAleatorio].<br>";
return $Proxys[$ProxyAleatorio];
}
else
{
//echo "Proxy no servia, usando localhost.<br>";
return '';
}
}
?>
