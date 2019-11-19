<?php

/*
skript pro naplneni existujici DB tabulkami.  
VYTVORENA Z DBCreate zpetne z duvodu neznalosti fungovani webhostingu. Predavat parametr a vytvaret/nevytvaret DB by bylo lepsi reseni.
Predpoklada zahrnuti konstant z modulu "constants.php" pred zavolanim.
Vypisuje udaje o prubehu na obrazovku.
Vytvorena DB obsahuje 2 tabulky
tabUzivatele
tabobrazky (obsahuje FK na tabUzivatele)
schema viz "schema-db.bmp"
*/



$servername = servername;
$username = username;
$password = password;
$databaseName =databasename;



	
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