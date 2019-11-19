<?php
/*
	PHP modul pro vyhledani dat v tabulce tabObrazky, podle "textHledani" v $_GET. 
	pouziva 'constants.php' - pro vytvoreni pripojeni, a 'LoadTable.php' - pro nahrani dat
	navraceny DOMDocument z 'LoadTable.php' nakonec vytiskne.
	
*/

	include 'constants.php';	
	
	$textHledani=$_GET["textHledani"];
	
	$pripojeni = new mysqli(servername, username, password, databasename);
	
	$naseptavacText = $textHledani;
		
	$dom = new DOMDocument();
	
	$tabulkaObrazku = include 'LoadTable.php';
	
	$dom->appendChild($tabulkaObrazku);
	
	print_r( $dom->saveHTML() );	

?>