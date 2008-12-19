<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">
     <head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
     <meta http-equiv="Content-Style-type" content="text/css" />
     <meta http-equiv="Content-Script-type" content="text/javascript" />
     <meta http-equiv="Content-Language" content="es" />
     <link rel="StyleSheet" href="estilo.css" type="text/css" />
     <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
     <link rel="start" href="/" />
     <title>Instalador de Control de Laboratorio</title>
     </head>
     <body>
     <?php
     function CREAR_TBL($TBL,$QUERY) {
     global $link;
     $x = @mysql_query("DROP TABLE IF EXISTS $TBL;", $link) or die('!->No se pudo eliminar la tabla "'.$TBL.'".<br /><pre>' . mysql_error() . '</pre>');
     $x = @mysql_query($QUERY, $link) or die('!->No se pudo crear la tabla "'. $TBL .'".<br /><pre>' . mysql_error() . '</pre>');
     if ($x) {echo "- Creada: '$TBL'<br />";}
 }

     if (!isset($_POST['instalar'])) {
         echo '
<h1>xMensajitos.php - Instalador</h1>
<h2>Sobre el instalador de xMensajitos.php.</h2>
<p>A partir de xMensajitos.php 3.0 todos los datos son almacenados en bases de datos, por lo que es <b>necesario</b> realizar ciertas configuraciones preliminares para que el programa pueda acceder a dichas facilidades de almacenamiento.<br /><br />
Si Ud. no configura el acceso a la base de datos, entonces las siguientes caracteristicas serán automáticamente deshabilitadas en xMensajitos.php:<br />
<ol>
<li>Reporte automático de números fuera de rango</li>
<li>Protección de Flood</li>
<li>Reutilización de sesiones de envío [Tigo]</li>
<li>Estadísticas</li>
</ol>
</p>
<h2>Configuración MySQL</h2>
<form action="'. $_SERVER['PHP_SELF'] .'" method="post">
<table border=0>
<tr>
<td>Dirección del servidor MySQL:</td>
<td><input type="text" name="motor"  maxlength="50" size="20" value="localhost" /></td>
</tr>
<tr>
<td>Base de datos a utilizar:</td>
<td><input type="text" name="base"  maxlength="50" size="20" value="" /></td>
</tr>
<tr>
<td>Usuario:</td>
<td><input type="text" name="usuario"  maxlength="50" size="20" value="" /></td>
</tr>
<tr>
<td>Clave:</td>
<td><input type="password" name="clave"  maxlength="30" size="20" value="" /></td>
</tr>
</table>
<br />
<input type="submit" name="instalar" value="Instalar" />
</form>
';
     } else {
         echo '<b>cLab - Instalador : Instalando</b><br />';
         if ($_POST['admin_clave'] != $_POST['admin_clave2']) {
             echo '<h3>+Las contraseñas no coinciden.</h3><br />
<a href="javascript:history.back();">Regresar al instalador</a>';
         }
         echo '<h3>+Creando conexión a la base de datos...</h3><br />';
         $link = @mysql_connect($_POST['motor'], $_POST['usuario'], $_POST['clave']) or die('Por favor revise sus datos, puesto que se produjo el siguiente error:<br /><pre>' . mysql_error() . '</pre>');
         mysql_select_db($_POST['base'], $link) or die('!->La base de datos seleccionada "'.$_POST['base'].'" no existe');
         echo '- Base de datos conectada...<br />';
         echo '<h3>+Creando Archivo con datos de conexión...</h3><br />';
         @touch("datos/data.php");
         @chmod("datos/data.php", 0777);
         $fh = @fopen("datos/data.php", 'w') or die("No se pudo escribir 'data.php'.<br />");
         if ($fh) {
             $Datos = "<?php\n";
             fwrite($fh, $Datos);
             $Datos = '$MiBD_IP = '. $_POST['motor'] .";\n" . '$MiBD_usuario = '. $_POST['usuario'] .";\n". '$MiBD_clave = '. $_POST['clave'] .";\n" . '$MiBD_BD = '. $_POST['base'] .";\n";
             fwrite($fh, $Datos);
             $Datos = "?>\n";
             fwrite($fh, $Datos);
             fclose($fh);
         }
         echo '- Creado<br />';
         echo '<h3>+Creando Tablas...</h3><br />';
         //Números fuera de rango.
         $q="CREATE TABLE xsms_fuera_de_rango ( numero varchar(10) primary key;";
         CREAR_TBL("xsms_fuera_de_rango", $q);
         //Protección de Flood.
         $q="CREATE TABLE xsms_flood (identificador varchar(30) primary key, fecha_ultimo_acceso int(11) unsigned, numero_accesos int(5) unsigned);";
         CREAR_TBL("xsms_flood", $q);
         //Estadisticas
         $q="CREATE TABLE xsms_estadisticas (rama varchar(30) primary key, sub_rama varchar(30), valor varchar(30);";
         CREAR_TBL("xsms_estadisticas", $q);
         mysql_close($link);
         echo '<br /><b>Instalación completa</b><br />';
     }
?>
</body>
</html>
