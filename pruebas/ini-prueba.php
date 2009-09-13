<?php

require_once( "ini.php" );

$iniMANAGER = new ini_manager();

$cuenta = $iniMANAGER->get_entry( "test.ini", $_SERVER['REMOTE_ADDR'], "cuenta" ) ;
$iniMANAGER->add_entry( "test.ini", $_SERVER['REMOTE_ADDR'], "cuenta", $cuenta += 1 ) ;
echo "Nos has visitado ".$iniMANAGER->get_entry( "test.ini", $_SERVER['REMOTE_ADDR'], "cuenta" )." veces!" ;?>
