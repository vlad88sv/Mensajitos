<?php
ob_start("ob_gzhandler");
header("Content-Type:text/html; charset=UTF-8");
/*
 *****************************************************************
 Licencia original:
 *
 * Mensajitos V2.3.1
 * Actualizado el 14/10/2006_**
 * Este programa es propiedad intelectual de
 * Mario Enrique Gomez Argueta. Su uso y distribucion
 * esta permitida bajo los terminos de la licencia
 * GNU/GPL 2.0 o posterior, que puede ser obtenida en
 * http://www.fsf.org/
 *
 * Este programa hace uso de la biblioteca Snoopy
 * distribuida tambien bajo la licencia GNU/GPL.
 * Para mayor informacion visitar:
 * http://snoopy.sourceforge.net/
 *
 *****************************************************************
 *
 *****************************************************************
 Esta es la rama experimental NO oficial: "xMensajitos.php".
 Esta rama experimental es  mantenida por Carlos Vladimir Hidalgo Durán.
 Contacto: vladimiroski@gmail.com
 Más información: http://xmensajitos.todosv.com
*/
// Definimos las constantes de directorios para poder accesarlas desde todo el codigo
$MiVersion = ' 2.20.0';
$home = dirname(__FILE__);
$plantilla = $home."/plantilla/mensajitos.htm";
$modulos = array('Digicel','Telecom','Red','Telefonica','Tigo');
require_once($home."/datos/data.php"); //Datos del servidor MySQL
$nMDB = $home."/datos/numeros.db";
$cMDB = $home."/datos/cuentas.db";
$r_fuera_de_rango = $home."/datos/fuera_rango.db";
require_once($home."/libs/iniparser.php" );
require_once($home."/libs/snoopy.php");
require_once($home."/libs/proxymity.php");
foreach($modulos as $item => $elemento)
require_once($home."/modulos/".$elemento.".php");
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

//Ok, si no tenemos MiBD entonces regresamos al viejo y confiable sistema INI.
if ( !$MiBD_OK ) {
    $I_nMDB = new iniParser($nMDB);
    $I_cMDB = new iniParser($cMDB);
 } else {
    //echo 'conectado<br />';
 }

/*************************************************************************/
// Mensajes
/*************************************************************************/
//Envio exitoso
$mensajeOK = "<b>Mensaje enviado a {uNumero}.<br />De la red de {operador}.</b>";
//Envio fallido
$mensajeERROR = "<b>Error al enviar el mensaje.<br />Se uso el operador: {operador}.</b>";
//No habia Operador válido
$mensajeOPEP =  "<b>Error al enviar el mensaje.<br />Revise el numero ({uNumero},{operador}).</b>";
/*************************************************************************/

/*************************************************************************/
// Opciones
/*************************************************************************/
$limite_flood_num = 50; //Numero maximo de mensajes por $intervalo_flood a un numero.
$limite_flood_ip = 100; //Numero maximo de mensajes por $intervalo_flood desde 1 ip
$intervalo_flood = 3600; //Intervalo de flood (en segundos)
$filtro = array(".*(hsbc).*", ".*(citibank).*", ".*(banco agr?cola).*", ".*(banco cu?catl?n).*", ".*(mora|moroso) .*", ".*(deud.{1,2}) .*");
/*************************************************************************/
 
if(stristr($_SERVER['HTTP_ACCEPT'],"text/vnd.wap.wml")){
	// Es un dispositivo movil, soporta WML
	$plantilla = $home."/plantilla/mensajitos.wml";
	$mime = "text/vnd.wap.wml";
	header("Content-type: $mime");
 }
 else{
     // No soporta wml (o no quiere xD)
     $plantilla = $home."/plantilla/mensajitos.htm";
     $mime = "text/html";
 }
 
if ( $MiBD_OK ) {
    InsertarValorSQL("xsms_estadisticas", "'$mime','1'", "valor=valor+1");
 }

function ObtenerValorSQL($sTabla, $sColumna, $sWhere) {
    global $MiBD_OK, $MiBD_link;
    if ( $MiBD_OK ) {
        $q = "SELECT $sColumna FROM $sTabla WHERE $sWhere;";
        //echo $q."<br>";
        $resultado = @mysql_query($q, $MiBD_link);
        if(mysql_num_rows($resultado) > 0){
            return mysql_result($resultado,0,$sColumna);
        } else {
            return false;
        }
    }
}

function EstablecerValorSQL($sTabla,  $sValores) {
    global $MiBD_OK, $MiBD_link;
    if ( $MiBD_OK ) {
        $q = "REPLACE INTO $sTabla VALUES ($sValores);";
        //echo $q."<br>";
        $resultado = @mysql_query($q, $MiBD_link);
        if( $resultado ){
            return true;
        } else {
            return false;
        }
    }
}

function InsertarValorSQL($sTabla,  $sValores, $OnUpdate) {
    global $MiBD_OK, $MiBD_link;
    if ( $MiBD_OK ) {
        $q = "INSERT INTO $sTabla VALUES ($sValores) ON DUPLICATE KEY UPDATE $OnUpdate;";
        //echo $q."<br>";
        $resultado = @mysql_query($q, $MiBD_link);
        if( $resultado ){
            return true;
        } else {
            return false;
        }
    }
}

function agregarNumFueraDeRango($Numero){
    global $MiBD_OK;
    if ( $MiBD_OK ) {
        global $MiBD_link;
        $q = "INSERT IGNORE INTO xsms_fuera_de_rango VALUES ('$Numero');";
        @mysql_query($q, $MiBD_link);
    }else {
        $I_FR_MDB = new iniParser($r_fuera_de_rango);
        $I_FR_MDB->setValue($Numero, "Hit","SI");
        $I_FR_MDB->save();
    }
}

function procesarPlantilla($archivo,$valores) {
    $buffer = file_get_contents($archivo);
    foreach($valores as $var=>$val) { 
        $buffer = str_replace($var,$val,$buffer);
    }
    return $buffer;
}

function DenegarFiltro($mensaje) {
    global $filtro;
    $denegar = false;
    foreach($filtro as $var=>$val) {
        //echo "$var";
        //echo "$val<br>";
        if (eregi($val, $mensaje, $textoEncontrado)) {
            echo "Palabra '$textoEncontrado[1]' (detectada como '$val' !)<br>";
            $denegar = true;
        }
    }
    return $denegar;
}

// Detecta el modulo a utilizar en base al numero de telefono
function ModuloOperador($pre) {
    global $modulos;
    if((($pre>=73000000)&&($pre<=73349999))||
       (($pre>=73350000)&&($pre<=73799999))||
       (($pre>=73800000)&&($pre<=73999999))||
       (($pre>=77600000)&&($pre<=77799999))||
       (($pre>=79700000)&&($pre<=79799999))) {
        return $modulos[0]; //Digicel
    }

    if((($pre>=21000000)&&($pre<=21029999))||
       (($pre>=70000000)&&($pre<=70699999))||
       (($pre>=70800000)&&($pre<=70999999))||
       (($pre>=76000000)&&($pre<=76699999))||
       (($pre>=77400000)&&($pre<=77599999))||
       (($pre>=78050000)&&($pre<=78099999))||
       (($pre>=78400000)&&($pre<=78699999))||
       (($pre>=79500000)&&($pre<=79699999))||
       (($pre>=79850000)&&($pre<=79899999))) {
        return $modulos[1]; //Telecom
    }
    
    if(($pre>=79800000)&&($pre<=79839999)) {
        return $modulos[2]; //Red
    }
    
    if((($pre>=71000000)&&($pre<=71899999))||
       (($pre>=77000000)&&($pre<=77199999))||
       (($pre>=77800000)&&($pre<=77849999))||
       (($pre>=77900000)&&($pre<=77949999))||
       (($pre>=78100000)&&($pre<=78399999))||
       (($pre>=78450000)&&($pre<=78499999))||
       (($pre>=79900000)&&($pre<=79989999))||
       (($pre>=79990000)&&($pre<=79999999))) {
        return $modulos[3]; //Telefonica
    }

    if((($pre>=72000000)&&($pre<=72999999))||
       (($pre>=75000000)&&($pre<=75999999))||
       (($pre>=76000000)&&($pre<=76099999))||
       (($pre>=77200000)&&($pre<=77399999))||
       (($pre>=77850000)&&($pre<=77899999))||
       (($pre>=77950000)&&($pre<=77999999))||
       (($pre>=78700000)&&($pre<=78749999))||
       (($pre>=78800000)&&($pre<=78999999))||
       (($pre>=78750000)&&($pre<=78799999))||
       (($pre>=79000000)&&($pre<=79499999))) {
        return $modulos[4]; //Tigo
    }
    agregarNumFueraDeRango(substr($pre,0,4));
    return NULL;
}

//Sera que quieren hacer un GET?
if(isset($_GET['t'])&&isset($_GET['m'])&&isset($_GET['f'])) {
	$_POST['telefono'] = $_GET['t'];
	$_POST['mensaje'] = $_GET['m'];
	$_POST['firma'] = $_GET['f'];
 } else if(isset($_GET['o'])) {
	$modulB = ($modulB = ModuloOperador($_GET['o'])) ? $modulB : '?';
	exit ($modulB);
 }

// Evaluamos el formulario basico
if(isset($_POST['telefono'])&&isset($_POST['mensaje'])&&isset($_POST['firma'])) {
	// Verificamos que no se haya establecido nada en vars
	if(isset($vars))
        unset($vars);
	// Guardamos las variables:
	$telefono = $_POST['telefono'];
	$mensaje = $_POST['mensaje'];
	$firma = $_POST['firma'];
	//************************************************
	// Revisión de respuesta (Si se envío o no)
	//$url_ok = $_POST['urlok'];
	//$url_bad = $_POST['urlbad'];
	//************************************************
	//Comprobamos que no sea publicidad, cobro, etc.
	if (DenegarFiltro($mensaje))
		exit ("Lo sentimos, publicidad y cobros no son aceptados. <br>Aprenda mas sobre esto aqui:<br>" .' <A href="http://foro.todosv.com/index.php/topic,95">Filtros... en pro de los salvadoreños y en contra de las compañías tacañas.<A />');
	//Validamos el numero telefonico
	if ($telefono==""||!ereg("^((2|7)[0-9]{7})$", $telefono)) {
		$estado = "Escriba el numero correctamente";
		$ret = "Revise su numero";
	} else {
        if ( $MiBD_OK ) {
            $cuentaNum = ObtenerValorSQL("xsms_flood","valor","clave='$telefono.cuenta'");
            $ultimoNum = ObtenerValorSQL("xsms_flood","valor","clave='$telefono.ultimo'");
            $cuentaIP = ObtenerValorSQL("xsms_flood","valor","clave='".$_SERVER['REMOTE_ADDR'].".cuenta'");
            $ultimoIP = ObtenerValorSQL("xsms_flood","valor","clave='".$_SERVER['REMOTE_ADDR'].".ultimo'");
        } else {
            //Comprobamos que no tenga ban.
            //Cuenta de mensajes a ese numero
            $cuentaNum = $I_nMDB->getValue($telefono, "cuenta");
            //Cuando se envio por ultima vez un mensaje a ese numero
            $ultimoNum = $I_nMDB->getValue($telefono, "ultimo");
            //Cuenta de mensajes desde esa IP
            $cuentaIP = $I_nMDB->getValue($_SERVER['REMOTE_ADDR'], "cuenta");
            //Cuando esa IP nos envio por ultima vez un mensaje
            $ultimoIP = $I_nMDB->getValue($_SERVER['REMOTE_ADDR'], "ultimo");
        }
        //-------------------------------------------------
        $flooder = 0;
        if (((time() - $ultimoIP) < $intervalo_flood)&&($cuentaIP>$limite_flood_ip)) {
            //Si no ha pasado una hora desde su ultimo mensaje y ha enviado mas mensajes de la cuenta (IP)
            $estado = "Demasiados mensajes por hora desde tu maquina.";
            $ret = "Por favor espere 1 hora. [FLOOD]";
            if ( $MiBD_OK ) {
                EstablecerValorSQL("xsms_flood","'".$_SERVER['REMOTE_ADDR'].".ultimo', '". time() . "'");
                EstablecerValorSQL("xsms_flood","'".$_SERVER['REMOTE_ADDR'].".flood', '1'");
            } else {
                $I_nMDB->setValue( $_SERVER['REMOTE_ADDR'], "ultimo", time());
                $I_nMDB->setValue( $_SERVER['REMOTE_ADDR'], "flood", 1);
            }
            $flooder = 1;
        } else if (((time() - $ultimoNum) < $intervalo_flood)&&($cuentaNum>$limite_flood_num)) {
            //Si no ha pasado una hora desde su ultimo mensaje y ha enviado mas mensajes de la cuenta (Numero)
            $estado = "Demasiados mensajes por hora a este numero.";
            $ret = "Por favor espere 1 hora para enviar a este numero. [FLOOD]";
            if ( $MiBD_OK ) {
                EstablecerValorSQL("xsms_flood","'$telefono.flood', '1'");
            } else {
                $I_nMDB->setValue( $telefono, "flood", 1);
            }
            $flooder = 1;
        }
        if ((time() - $ultimoIP) > $intervalo_flood) {
            //Si ha pasado una hora desde su ultimo mensaje (IP) le reseteamos su conteo (IP)
            $cuentaIP = 0;
            if ( $MiBD_OK ) {
                EstablecerValorSQL("xsms_flood","'".$_SERVER['REMOTE_ADDR'].".flood', '0'");
                EstablecerValorSQL("xsms_flood","'".$_SERVER['REMOTE_ADDR'].".cuenta, '0'");
            } else {
                $I_nMDB->setValue($_SERVER['REMOTE_ADDR'], "flood", 0);
                $I_nMDB->setValue($_SERVER['REMOTE_ADDR'], "cuenta", 0);
            }
        }
        if ((time() - $ultimoNum) > $intervalo_flood) {
            //Si ha pasado una hora desde su ultimo mensaje (Num) le reseteamos su conteo (Num)
            $cuentaNum = 0;
            if ( $MiBD_OK ) {
                EstablecerValorSQL("xsms_flood", "'$telefono.flood', '0'");
                EstablecerValorSQL("xsms_flood","'$telefono.cuenta', '0'");
            } else {
                $I_nMDB->setValue($telefono, "flood", 0);
                $I_nMDB->setValue($telefono, "cuenta", 0);
            }
        }
        if ($flooder == 0) {
            //Ok, no tiene banneo por flood.
            //Ok, el numero es valido, pero ha escrito un mensaje a enviar?.
            if ($mensaje){
                // Si, ha escrito un mensaje ahora buscar un operador para el numero.
                $modulo = ModuloOperador($telefono);
                if($modulo) {
                    $nombreMod = $modulo."_Nombre"; 
                    $ret = $nombreMod();
                    $FEnvio = $modulo."_Enviar";
                    if($FEnvio($telefono,$mensaje,$firma)) {
                        $estado = $mensajeOK;
                        //Control de Flood
                        if ( $MiBD_OK ) {
                            EstablecerValorSQL("xsms_flood","'".$_SERVER['REMOTE_ADDR'].".cuenta', '" . ($cuentaIP+=1) ."'");
                            EstablecerValorSQL("xsms_flood","'".$_SERVER['REMOTE_ADDR'].".ultimo', '". time() ."'");
                            EstablecerValorSQL("xsms_flood","'$telefono.cuenta', '". ($cuentaNum+=1) ."'");
                            EstablecerValorSQL("xsms_flood","'$telefono.ultimo', '". time() ."'");
                        } else {
                            $I_nMDB->setValue($_SERVER['REMOTE_ADDR'], "cuenta", $cuentaIP += 1);
                            $I_nMDB->setValue($_SERVER['REMOTE_ADDR'], "ultimo", time());
                            $I_nMDB->setValue($telefono, "cuenta", $cuentaNum += 1) ;
                            $I_nMDB->setValue($telefono, "ultimo", time());
                        }
                        //Control de Flood
                        $mensaje = '';
                        //+1 al modulo OK
                        if ( $MiBD_OK ) {
                            InsertarValorSQL("xsms_estadisticas", "'".$modulo."-OK". "','1'", "valor=valor+1");
                        }else {
                            $cuenta = $I_cMDB->getValue("Companias", $modulo."-OK");
                            $I_cMDB->setValue( "Companias", $modulo."-OK",$cuenta += 1);
                        }
                    } else {
                        $estado = $mensajeERROR;
                        //+1 al modulo ERROR
                        if ( $MiBD_OK ) {
                            InsertarValorSQL("xsms_estadisticas", "'".$modulo."-ERR". "','1'", "valor=valor+1");
                        }else {
                            $cuenta = $I_cMDB->getValue("Companias", $modulo."-ERR");
                            $I_cMDB->setValue( "Companias", $modulo."-ERR",$cuenta += 1);
                        }
                    }			
                } else {
                    $ret = "Sin Operador";
                    $estado = $mensajeOPEP;
                }
            } else {
                $estado = "Olvido escribir su mensaje";
                $ret = "Revise su mensaje";
            }
        }
        if ( $MiBD_OK ) {
        }else {    
            $I_cMDB->save();
            $I_nMDB->save();
        }
    }
 }
//Accion del POST
if(isset($_SERVER['REQUEST_URI']))
    $vars["{script}"] = $_SERVER['REQUEST_URI'];
 else
     $vars["{script}"] = $_SERVER['PHP_SELF'];
//Accion del POST

//Informacion del formulario
$vars["{version}"] = '<a href="http://www.todosv.com">Version ' . $MiVersion . '</a><br /><a href="estad.php" target="_blank">Estadísticas</a>';
$vars["{estado}"] = $estado;
$vars["{operador}"] = $ret;
$vars["{uNumero}"] = $telefono;
$vars["{uMensaje}"] = $mensaje;
$vars["{uFirma}"] = $firma;

// Sustituimos los valores en la plantilla:
echo procesarPlantilla($plantilla,$vars);
return 0
?>
