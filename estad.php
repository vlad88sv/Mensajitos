<?php 
ob_start("ob_gzhandler");
echo
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
     <head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
     <meta http-equiv="Content-Style-type" content="text/css" />
     <meta http-equiv="Content-Script-type" content="text/javascript" />
     <meta http-equiv="Content-Language" content="es" />
     <link rel="StyleSheet" href="estilo.css" type="text/css" />
     <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
     <link rel="start" href="/" />
     <title>Estadísticas de xMensajitos.php</title>
     </head>
     <body>
     <div id="centerwrapper">
     <div id="content">';
require_once(dirname(__FILE__)."/libs/graphs.inc.php" );
require_once(dirname(__FILE__)."/libs/iniparser.php" );
require_once(dirname(__FILE__)."/datos/data.php"); //Datos del servidor MySQL
$MDB = new iniParser(dirname(__FILE__)."/datos/cuentas.db");
/*************************************************************************/
// Tratamos de conectarnos a la base de datos, si lo conseguimos entonces
// activamos la variable que indicará que se pueden utilizar las funciones
// dependientes de MiDB.
// Este metodo debería de asegurar que no se pierda funcionalidad principal
// al no tener configurado MiBD.
/*************************************************************************/
$MiBD_link = @mysql_connect($MiBD_IP, $MiBD_usuario, $MiBD_clave, false);
if ( !$MiBD_link ) {
    //No nos pudimos conectar
    $MiBD_OK = false;
 } else {
    //Si nos pudimos conectar, entonces todo depende que podamos escoger sin problemas
    //la base de datos.
    $MiBD_OK = @mysql_select_db($MiBD_BD, $MiBD_link);
 }
 
function ObtenerValorSQL($sTabla, $sColumna, $sWhere) {
    global $MiBD_OK, $MiBD_link;
    if ( $MiBD_OK ) {
	$q = "SELECT $sColumna FROM $sTabla WHERE $sWhere;";
	//echo $q."<br />";
	$resultado = @mysql_query($q, $MiBD_link);
	if(mysql_num_rows($resultado) > 0){
	    return mysql_result($resultado,0,$sColumna);
	} else {
	    return false;
	}
    }
}

function resta_fechas($fecha1,$fecha2) {
    if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha1))           
	list($dia1,$mes1,$anio1)=split("/",$fecha1);
    if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha2))  
	list($dia2,$mes2,$anio2)=split("/",$fecha2);     
    return((mktime(0,0,0,$mes1,$dia1,$anio1) - mktime(0,0,0,$mes2,$dia2,$anio2))/(24*60*60));
}

if ( !isset( $fecha_instalacion ) ){
    $f1 = time();
 } else {
    $f1 = $fecha_instalacion;
 }
 $numdias=resta_fechas(date("d/m/Y"),date("d/m/Y",$f1));
if ( $MiBD_OK ) {
    //Digicel
    $c_Digicel_OK = ObtenerValorSQL("xsms_estadisticas","valor","rama='Digicel-OK'");
    $c_Digicel_NO = ObtenerValorSQL("xsms_estadisticas","valor","rama='Digicel-ERR'");
    //Telecom
    $c_Telecom_OK = ObtenerValorSQL("xsms_estadisticas","valor","rama='Telecom-OK'");
    $c_Telecom_NO = ObtenerValorSQL("xsms_estadisticas","valor","rama='Telecom-ERR'");
    //Telefonica
    $c_Telefonica_OK = ObtenerValorSQL("xsms_estadisticas","valor","rama='Telefonica-OK'");
    $c_Telefonica_NO = ObtenerValorSQL("xsms_estadisticas","valor","rama='Telefonica-ERR'");
    //Tigo
    $c_Tigo_OK = ObtenerValorSQL("xsms_estadisticas","valor","rama='Tigo-OK'");
    $c_Tigo_NO = ObtenerValorSQL("xsms_estadisticas","valor","rama='Tigo-ERR'");
 } else {
    //Digicel
    $c_Digicel_OK = $MDB->get("Companias", "Digicel-OK");
    $c_Digicel_NO = $MDB->get("Companias", "Digicel-ERR");
    //Telecom
    $c_Telecom_OK = $MDB->get("Companias", "Telecom-OK");
    $c_Telecom_NO = $MDB->get("Companias", "Telecom-ERR");
    //Telefonica
    $c_Telefonica_OK = $MDB->get("Companias", "Telefonica-OK");
    $c_Telefonica_NO = $MDB->get("Companias", "Telefonica-ERR");
    //Tigo
    $c_Tigo_OK = $MDB->get("Companias", "Tigo-OK");
    $c_Tigo_NO = $MDB->get("Companias", "Tigo-ERR");
 }

$Exitosos = $c_Digicel_OK+$c_Telecom_OK+$c_Telefonica_OK+$c_Tigo_OK;
$Fallidos = $c_Digicel_NO+$c_Telecom_NO+$c_Telefonica_NO+$c_Tigo_NO;
$Totales = $Exitosos + $Fallidos;

echo "<h1>Este es el centro de estadisticas (1.3 [PRE]).<br />@ " . $_SERVER['SERVER_NAME'] . "</h1><hr />";
echo "<h2>General</h2>";
if ($numdias == 0){
    echo "Aun no se han recolectado estadisticas";
 }else{
    echo "Último reinicio de estadisticas: <b>".date("d/m/y \a\ \l\a\s h:ia",$f1)."</b><br />";
    echo "Han transcurrido <b>".( $numdias )."</b> dias desde el ultimo reinicio de estadisticas<br />";
 }
echo "<hr /><h2>Mensajes</h2>";
echo "Se ha enviado un total de <b>$Totales</b> mensajes.<br />De los cuales el <b>".@round(($Exitosos/$Totales)*100,2)."%</b> ( <b>$Exitosos</b> mensajes) ha sido exitoso y el <b>".@round(($Fallidos/$Totales)*100,2)."%</b> ( <b>$Fallidos</b> mensajes ) ha fallado.<br />";
echo "Eficiencia de envio actual: <b>".@round(($Exitosos/$Totales)*100,2).'%</b> ( Aprox. '.@ceil(($Exitosos/$Totales)*100)." de cada 100 mensajes se envian bien ).<br />";
if ($numdias > 0){
echo "<h3>Totales</h3>";
echo "Mensajes por dia: <b>".ceil($Totales/$numdias)."</b><br />";
echo "Mensajes por hora: <b>".ceil($Totales/($numdias * 24))."</b><br />";
echo "Mensajes por minuto: <b>".ceil($Totales/($numdias * 24 * 60))."</b><br />";
echo "Mensajes exitosos por dia: <b>".ceil($Exitosos/$numdias)."</b><br />";
echo "Mensajes fallidos por dia: <b>".ceil($Fallidos/$numdias)."</b><br />";
}
    //
// Configurador del graficador.
$graph = new BAR_GRAPH("hBar");
$graph->showValues = 1;
$graph->barColors = "#E0E0E0,#0E0E0E";
$graph->barBGColor = "";
$graph->barBorder = "1px solid #808080";
$graph->labelColor = "#A0A0A0";
$graph->labelBGColor = "";
$graph->labelBorder = "1px dashed #A0A0A0";
$graph->labelFont = "Arial Black, Arial, Helvetica";
$graph->labelSize = 16;
$graph->absValuesColor = "#A0A0A0";
$graph->absValuesBGColor = "";
$graph->absValuesBorder = "1px solid silver";
$graph->absValuesFont = "Verdana, Arial, Helvetica";
$graph->absValuesSize = 14;
$graph->percValuesColor = "#A0A0A0";
$graph->percValuesFont = "Comic Sans MS, Times New Roman";
$graph->percValuesSize = 16;
// Fin de configuración del graficador.

echo "<h3>Comparativa de compañías.</h3>Número de envios exitosos y erroneos respecto a todas las compañías.<br />";
$graph->legend = "Exitosos,Erroneos";
$graph->labels = "Digicel,Telefonica/Movistar,Telecom/Claro,Telemovil/Tigo";
$graph->values = "$c_Digicel_OK;$c_Digicel_NO, $c_Telefonica_OK;$c_Telefonica_NO, $c_Telecom_OK;$c_Telecom_NO, $c_Tigo_OK;$c_Tigo_NO";
echo $graph->create();
echo "<h3>Comparativa de compañías.</h3>Demanda de envio por compañías.<br />";
$graph->labels = "Digicel,Telefonica/Movistar,Telecom/Claro,Telemovil/Tigo";
$graph->legend = "";
$graph->values = ($c_Digicel_OK + $c_Digicel_NO).", ".($c_Telefonica_OK + $c_Telefonica_NO).", ".($c_Telecom_OK + $c_Telecom_NO).", ".($c_Tigo_OK +$c_Tigo_NO);
echo $graph->create();
echo "<h3>Eficiencia de envio por compañías.</h3>Porcentaje de envios exitosos respecto a total de mensajes por compañia.<br />";
$graph->type = "pBar";
$graph->legend = "";
$graph->showValues = 0;
$graph->barColors = "#E0E0E0,#E0E0E0,#E0E0E0,#E0E0E0";
$graph->labels = "Digicel,Telefonica/Movistar,Telecom/Claro,Telemovil/Tigo";
$graph->values = $c_Digicel_OK.";".($c_Digicel_OK + $c_Digicel_NO).", ".$c_Telefonica_OK.";".($c_Telefonica_OK + $c_Telefonica_NO).", ".$c_Telecom_OK.";".($c_Telecom_OK + $c_Telecom_NO).", ".$c_Tigo_OK.";".($c_Tigo_OK +$c_Tigo_NO);
echo $graph->create();
if ( $MiBD_OK ) {
    echo "<hr /><h2>Estadisticas de visitas</h2>";
    $graph->showValues = 1;
    $graph->type = "hBar";
    $c_Visitas_HTML = floatval(ObtenerValorSQL("xsms_estadisticas","valor","rama='text/html'"));
    $c_Visitas_WAP = floatval(ObtenerValorSQL("xsms_estadisticas","valor","rama='text/vnd.wap.wml'"));
    $graph->labels = "HTML,WAP/WML";
    $graph->values = $c_Visitas_HTML.", ".$c_Visitas_WAP;
    echo $graph->create();
    echo "<br />¡<b>".ceil(($c_Visitas_HTML+$c_Visitas_WAP)/$numdias)."</b> visitas por dia!<br />";
 }
echo "<hr /><h2>Copyright</h2>Mensajitos.php es un proyecto creado por <b>mxgxw</b> -> www.nohayrazon.com<br />Este es Mensajitos.php TSV, una version modificada por <b>Vlad</b> del software Mensajitos.php<br />";
echo
'</div>
</div>
</body>
</html>';
?> 
