<?php

/*
skript pro vytvoreni DB. Predpoklada zahrnuti konstant z modulu "constants.php" pred zavolanim.
Vypisuje udaje o prubehu na obrazovku.
Vytvorena DB obsahuje 2 tabulky
tabUzivatele
tabObrazky (obsahuje FK na tabUzivatele)
schema viz "schema-db.bmp"
*/



$servername = servername;
$username = username;
$password = password;
	
$pripojeniBezDB = new mysqli($servername, $username, $password);



if ($pripojeniBezDB->connect_error) {
    die("Pripojeni selhalo: " . $conn->connect_error);
	echo("<br>");
} 

$databaseName =databasename;

$queryDBCReate = "CREATE DATABASE " . $databaseName;

if ($pripojeniBezDB->query($queryDBCReate) === TRUE) {
    echo("Databaze uspesne vytvorena");
	echo("<br>");
} else {
    echo("Chyba pri vytvareni databaze: " . $pripojeniBezDB->error);
	echo("<br>");
}



$pripojeniBezDB->close();


	
$pripojeni = mysqli_connect($servername, $username, $password,$databaseName);

$sqlDotaz = "CREATE TABLE 
tabUzivatele(
uzivatelID SMALLINT AUTO_INCREMENT,
uzivatelJmeno VARCHAR(20) NOT NULL,
uzivatelHeslo VARCHAR(20) NOT NULL,
PRIMARY KEY(uzivatelID)

);
";

	$prubehOK = $pripojeni->query($sqlDotaz);
	
	if($prubehOK === true){
		echo("Tabulka tabUzivatele vytvorena!");
		echo("<br>");			
	}
	else{
		echo("Pri vytvareni tabulky tabUzivatele nastala chyba!" . mysqli_error($pripojeni) );
		echo("<br>");		
	}	

	
$sqlDotaz = "CREATE TABLE 
tabObrazky(
obrazekID SMALLINT AUTO_INCREMENT,
filepath VARCHAR(60) NOT NULL,
uzivatelID SMALLINT,
PRIMARY KEY(obrazekID),
FOREIGN KEY (uzivatelID)
    REFERENCES tabUzivatele(uzivatelID)

);
";

	$prubehOK = $pripojeni->query($sqlDotaz);
	
	if($prubehOK === true){
		echo("Tabulka tabObrazky vytvorena!");
		echo("<br>");			
	}
	else{
		echo("Pri vytvareni tabulky tabObrazky nastala chyba!" . mysqli_error($pripojeni) );
		echo("<br>");		
	}		
	
	
	
	
	
	
	
	
	

?>