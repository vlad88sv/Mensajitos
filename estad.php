<?php 
ob_start("ob_gzhandler");
header("Content-Type:text/html; charset=UTF-8");
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

echo "<b>Este es el centro de estadisticas (1.2 PRE) para " . $_SERVER['SERVER_NAME'] . "</b><br /><br />";
echo "<b>~Conteo de mensajes~</b><br />";
echo "<b>* Totales *</b><br />";
echo "Se ha enviado un total de <b>$Totales</b> mensajes.<br />De los cuales el <b>".@round(($Exitosos/$Totales)*100,2)."%</b> ( <b>$Exitosos</b> mensajes) ha sido exitoso y el <b>".@round(($Fallidos/$Totales)*100,2)."%</b> ( <b>$Fallidos</b> mensajes ) ha fallado.<br />";
echo "Eficiencia de envio actual: <b>".@round(($Exitosos/$Totales)*100,2).'%</b> ( Aprox. '.@ceil(($Exitosos/$Totales)*100)." de cada 100 mensajes se envian bien ).<br />";
echo "<br /><b>* Totales por dia *</b><br />";
$numdias=resta_fechas(date("d/m/Y"),date("d/m/Y",$f1)) + 1;
if ($numdias == 0){
    echo "Aun no se han recolectado estadisticas";
 }else{
    //
    echo "<b>~Estadisticas de tiempo~</b><br />";
    echo "Último reinicio de estadisticas: <b>".date("d/m/y ~ h:ia",$f1)."</b><br />";
    echo "Han transcurrido ".($numdias - 1)." dias desde el ultimo reinicio de estadisticas<br />";
    echo "<br /><b>~Estadisticas de mensaje y tiempo~</b><br />";
    echo "Mensajes por dia: <b>".ceil($Totales/$numdias)."</b><br />";
    echo "Mensajes por hora: <b>".ceil($Totales/($numdias * 24))."</b><br />";
    echo "Mensajes por minuto: <b>".ceil($Totales/($numdias * 24 * 60))."</b><br />";
    echo "Mensajes exitosos por dia: <b>".ceil($Exitosos/$numdias)."</b><br />";
    echo "Mensajes fallidos por dia: <b>".ceil($Fallidos/$numdias)."</b><br />";
    //
 }
echo "<br /><b>* Totales por compañia *</b><br />";
echo "<b>Digicel:</b><br />";
echo "Exitosos: ".$c_Digicel_OK." (".@round(($buenos/($c_Digicel_OK+$c_Digicel_NO))*100,2)."%)<br />";
echo "Erroneos: ".$c_Digicel_NO." (".@round(($malos/($c_Digicel_OK+$c_Digicel_NO))*100,2)."%)<br />";
echo "Total: ".($c_Digicel_OK + $c_Digicel_NO)." (".@round((($c_Digicel_OK + $c_Digicel_NO)/$Totales)*100,2) ."% de todos lo mensajes)<br />";
//
echo "<b>Telefonica/Movistar:</b><br />";
echo "Exitosos: ".$c_Telefonica_OK." (".@round(($c_Telefonica_OK/($c_Telefonica_OK+$c_Telefonica_NO))*100,2)."%)<br />";
echo "Erroneos: ".$c_Telefonica_NO." (".@round(($c_Telefonica_NO/($c_Telefonica_OK+$c_Telefonica_NO))*100,2)."%)<br />";
echo "Total: ".($c_Telefonica_OK + $c_Telefonica_NO)." (".@round((($c_Telefonica_OK + $c_Telefonica_NO)/$Totales)*100,2) ."% de todos lo mensajes)<br />";
//
echo "<b>Telecom/Claro:</b><br />";
echo "Exitosos: ".$c_Telecom_OK." (".@round(($c_Telecom_OK/($c_Telecom_OK+$c_Telecom_NO))*100,2)."%)<br />";
echo "Erroneos: ".$c_Telecom_NO." (".@round(($c_Telecom_NO/($c_Telecom_OK+$c_Telecom_NO))*100,2)."%)<br />";
echo "Total: ".($c_Telecom_OK + $malos)." (".@round((($c_Telecom_OK + $c_Telecom_NO)/$Totales)*100,2) ."% de todos lo mensajes)<br />";
//
echo "<b>Telemovil/Tigo:</b><br />";
echo "Exitosos: ".$c_Tigo_OK." (".@round(($c_Tigo_OK/($c_Tigo_OK+$c_Tigo_NO))*100,2)."%)<br />";
echo "Erroneos: ".$c_Tigo_NO." (".@round(($c_Tigo_NO/($c_Tigo_OK+$c_Tigo_NO))*100,2)."%)<br />";
echo "Total: ".($c_Tigo_OK + $c_Tigo_NO)." (".@round((($c_Tigo_OK + $c_Tigo_NO)/$Totales)*100,2) ."% de todos lo mensajes)<br />";
if ( $MiBD_OK ) {
    echo "<br /><b>~Estadisticas de visitas~</b><br />";
    echo "Visitas normales (HTML): ". ObtenerValorSQL("xsms_estadisticas","valor","rama='text/html'"). "<br />";
    echo "Visitas mobiles (WAP/WML): " . ObtenerValorSQL("xsms_estadisticas","valor","rama='text/vnd.wap.wml'") . "<br />";
 }
echo "<br /><b>~Copyright~</b><br />Mensajitos.php es un proyecto creado por <b>mxgxw</b> -> www.nohayrazon.com<br />Este es Mensajitos.php TSV, una version modificada por <b>Vlad</b> del software Mensajitos.php<br />";
?> 
