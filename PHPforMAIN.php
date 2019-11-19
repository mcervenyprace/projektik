<?php
	/*
		Hlavni php modul. Pouziva 'constants.php' (udaje pripojeni serveru) a cte veskere HTML soubory projektu. 
		Vzdy vola runMain();. Predpoklada vyplnenou promennou "switch". Pri jeji absenci pouzije hodnotu 1 = default.		
		Vyznamy hodnoty "switch":
		"switch" = 1 = neznamy stav DB, podle jejiho stavu se bud vypise hlaska, vytvori DB, zobrazi vytvoreni/prihlaseni uzivatele.  Viz metoda urciStavDB().
		"switch" = 2 = stav po pokusu o vytvoreni uzivatele, overuje zadane udaje. Viz pokusSeVytvoritUzivatele().	
		"switch" = 3 = stav po pokusu o vytvoreni uzivatele, overuje zadane udaje. Viz pokusSePrihlasitUzivatele().
		"switch" = 4 = stav po uspesnem vytvoreni DB. prejde na stranku kde se vytvori prvni uzivatel. Viz prejdiNaVytvoreniUzvivatele().
		"switch" = 5 = stav po odhlaseni uzivatele. Ukonci session a prejde na prihlaseni.
		"switch" = 6 = stav po pokusu o smazani obrazku, overuje data (aktivni uzivatel), viz zkusSmazatObrazek().
		"switch" = 7 = stav po pokusu o nahrani obrazku viz zkusNahratObrazek();
	*/

	
	include 'constants.php';	

		
	runMain();	

	function runMain(){
		/*
			Hlavni funkce, viz popis modulu. Vola se vzdy pri behu tohoto modulu.  
		*/
	 
		$spinac = 1;
		
		if(isset($_POST["switch"])){	
			$spinac = $_POST["switch"];
		}
		
		$stavDB = urciStavDB();

		//echo($stavDB);

		//$pripojeni = = PripojSeDoProjektDB();
		//echo $spinac;
			

		if($spinac == 1){
			//case defaultni stranka, stav neznamy
			if($stavDB == 0){
				
				echo ("Nastala chyba pripojeni na server.");
			}
			if($stavDB == 1){
				echo ("Neexistuje DB. Vytvarim DB.");
				echo("<br>");
				include 'DBCreate.php';
				echo("<br>");
				echo("Pokud je DB uspesne vytvorena, provedte obnoveni (F5).");
			}
			
			if($stavDB == 2){
				readfile("templatePRVNIuzivatel.html");	
			}
			
			if($stavDB == 3){
				readfile("templateDALSIuzivatel.html");	
				
			}
			
			if($stavDB == 4){
				echo ("DB je bez spravne struktury. Vytvarim pozadovanou strukturu DB.");
				echo("<br>");
				include 'DBFill.php';
				echo("<br>");
				echo("Pokud je struktura DB uspesne vytvorena, provedte obnoveni (F5).");	
				
			}
			
			//pozn.: z defaultni stranky se nelze rovnou pripojit na tabulku
		}


		if($spinac == 2){
			//case po vytvoreni uzivatele
			pokusSeVytvoritUzivatele();
		}

		if($spinac == 3){

			pokusSePrihlasitUzivatele();
		}

		if($spinac == 4){
			//case po vytvoreni prvniho uzivatele
			prejdiNaVytvoreniUzvivatele();
		}
		
		if($spinac == 5){
			//case odhlaseni
			
			session_unset(); 
			session_destroy(); 
			
			
			//readfile("templateDALSIuzivatel.html");
			//chybna konstrukce viz problemChromeEdge.bmp
			readfile("index.html");	
		}
		
		if($spinac == 6){
			//case smazat obrazek
			zkusSmazatObrazek();	
		}

		if($spinac == 7){
			//case nahrat obrazek
			zkusNahratObrazek();	
		}
	
	
	}

	function urciStavDB(){
	/*
	Pokus se zjistit stav DB na serveru. 
	vraci
	0 pokud neexistuje pripojeni nebo nastala fatalni chyba
	1 pokud se pripoji na server
	2 pokud se pripoji do DB ale ta je prazdna (tabulky jsou bez zaznamu)
	3 pokud se pripoji a DB je neprazdna (aspon 1 uzivatel)
	4 pokud se pripoji a DB je bez tabulek
	*/	

	$vratit = 0;

	try{
		error_reporting(0);
		$pripojeniBezDB = new mysqli(servername, username, password);
		error_reporting(1);
		if ($pripojeniBezDB->connect_error) {
		throw new Exception("Pripojeni na server selhalo"); }
		
	$vratit = 1;
	} 	
	catch(Exception $e) {
		$vratit = 0;		
	} 

	
	try{
		$pripojeni = new mysqli(servername, username, password, databasename);
		if ($pripojeniBezDB->connect_error) {
		throw new Exception("Pripojeni do DB selhalo"); }
		
	$vratit = 2;
	} 	
	catch(Exception $e) {
		$vratit = 1;		
	} 
	
	$tabulkyExistuji = urciExistenciTabulekVDB();
	
	if($tabulkyExistuji == false){
		$vratit =4;	
		
	}
	
	
	
	
	
	$uzivatelExistuje = False;
	
	if($vratit == 2){
		$uzivatelExistuje = urciExistenciUzivateleVDB();
		if($uzivatelExistuje == true){
			$vratit = 3;
		} else {
			
		}
			
	}

	return $vratit;
		
	}
	
	function urciExistenciUzivateleVDB(){
	/*
	vraci true pokud je v tabulce uzivatele aspon 1 zaznam, jinak false
	*/
		$pripojeni = new mysqli(servername, username, password, databasename);
		
		$dotaz = "SELECT COUNT(*) FROM tabUzivatele;";	

		$vysledek = mysqli_query($pripojeni,$dotaz);
		
		$pocetUzivatelu = 0;
		
		if($vysledek == false){
			echo "Dotaz pro pocet uzivatlu nad databazi selhal = v DB neni tabulka.";
		}
		else{
			try{			
				while($radek =$vysledek->fetch_array()){		
							$pocetUzivatelu = $radek["COUNT(*)"]; 
						}
			}
			
			catch(Exception $e){
				$message = $e->getMessage();
				echo "ERROR: $message";
								}
		    }
		$vratit = false;
		
		if($pocetUzivatelu > 0){
			$vratit = true;
		}
		
		return $vratit;
			

	}

	
	function urciExistenciTabulekVDB(){
	/*
		Vraci true pokud v DB existuji tabulky tabObrazky a tabUzivatele, jinak false.
	*/
		$tabulkyOK = false;
		
		$pripojeni = new mysqli(servername, username, password, databasename);
		
		$dotaz = "SELECT * FROM tabUzivatele LIMIT 1;";	
		
		//$dotaz =  "SHOW TABLES LIKE 'tabUzivatele'"; 

		$tabUzivateleOK = mysqli_query($pripojeni,$dotaz);
		
		if($tabUzivateleOK == false){
			echo "ERROR: V DB neni tabulka tabUzivatele.";
			echo "<br>";
		}	
		
		$dotaz = "SELECT 1 FROM tabObrazky LIMIT 1;";
		//$dotaz =  "SHOW TABLES LIKE 'tabObrazky'"; 
		
		$tabObrazkyOK = mysqli_query($pripojeni,$dotaz);
		
		if($tabObrazkyOK == false){
			echo "ERROR: V DB neni tabulka tabObrazky.";
			echo "<br>";
		}
		
		if(($tabObrazkyOK != false) and ($tabUzivateleOK != false)){
			$tabulkyOK = true;	
		}
		return $tabulkyOK;
		
		
	}
	
	
	function pokusSeVytvoritUzivatele(){
	/*
		Pokus o vytvoreni uzivatele. Data jsou prevzata z $_POST promennych:
		"jmenoUzivatele", "heslo", "hesloPotvrzeni"
		Metoda overi vyplneni dat, shodnost retezcu "heslo" a "hesloPotvrzeni".
		Nasledne metoda nahraje vsechny stavajici uzivatele (pomoci objektu uzivatelDB) a  pomoci metody jmenoJeVPoliUzivatelu
		overi existenci uzivatele s danym jmenem. Pokud existuje, zakaze vytvoreni a vypise hlasku.
		Pokud je vse v poradku, ulozi noveho uzivatele do DB (pomoci objektu uzivatelDB). 
		
	*/
		$jmenoNovehoUzivatele = $_POST["jmenoUzivatele"];
		$hesloNovehoUzivatele = $_POST["heslo"];
		$hesloPotvrzeni = $_POST["hesloPotvrzeni"];
		
		$problemNastal = false;
		$zprava = "";
		
		if($jmenoNovehoUzivatele == ''){
				$zprava="Vyplňte prosím uživatele";
				$problemNastal = true;
			
		}
		
		if($hesloNovehoUzivatele == ''){
				$zprava="Vyplňte prosím heslo";
				$problemNastal = true;
			
		}

		if($hesloNovehoUzivatele != $hesloPotvrzeni){
			$zprava="Vyplněná hesla nejsou shodná";
			$problemNastal = true;
		}
		
		$pripojeni = new mysqli(servername, username, password, databasename);
		$DBObjekt = new uzivatelDB($pripojeni);
		$uzivatele = $DBObjekt->nahrajUzivatele();
		$jmenoJizZabrano = jmenoJeVPoliUzivatelu($jmenoNovehoUzivatele,$uzivatele);
		
		if($jmenoJizZabrano == true){
			$zprava="Zadané jméno je již zabráno jiným uživatelem. Vyplňte prosím jiné.";
			$problemNastal = true;
		}
		
		
		
		if($problemNastal == true){
			echo "<script type='text/javascript'>alert('$zprava');</script>";
			readfile("templatePRVNIuzivatel.html");	
		}
		else{
			//case vytvorit uzivatele a prihlasit se za nej
			$uzivatelNovy = new uzivatelObjekt($jmenoNovehoUzivatele,$hesloNovehoUzivatele);
			
			
			$DBObjekt->saveUzivatel($uzivatelNovy);
			mysqli_close($pripojeni);
			
			pokusSePrihlasitUzivatele();

		}
	
		
		

	
		
	}
	
	function jmenoAHesloJeVPoliUzivatelu($jmenoHledane,$hesloHledane,$poleUzivatelu){
	/*
		Vraci true, pokud v $poleUzivatelu existuje objekt uzivatelObjekt   s vlastnostmi 
		jmeno a heslo shodnymi se zadanymi parametry.
		$jmenoHledane = retezec
		$hesloHledane = retezec
		$poleUzivatelu  = pole objektu uzivatelObjekt  
	*/
		$jmenoAHesloNalezeno = false;

		foreach($poleUzivatelu as $uzivatel ){
			
			//echo $uzivatel->jmeno . " " . $uzivatel->heslo . " VS " . $jmenoHledane ." ". $hesloHledane;
			
			
			if(($uzivatel->jmeno == $jmenoHledane) AND ($uzivatel->heslo == $hesloHledane)){
				//echo"UZIVATEL NALEZEN";
				$jmenoAHesloNalezeno = true;
				break;
			}
			
		}
		
		return $jmenoAHesloNalezeno;
		
		
	}
	
		function jmenoJeVPoliUzivatelu($jmenoHledane,$poleUzivatelu){
		/*
			Vraci true, pokud v $poleUzivatelu existuje objekt uzivatelObjekt   s vlastnosti 
			jmeno shodnou se zadanym parametrem.
			$jmenoHledane = retezec
			$poleUzivatelu  = pole objektu uzivatelObjekt  
		*/
		$jmenoNalezeno = false;

		foreach($poleUzivatelu as $uzivatel ){
			
			//echo $uzivatel->jmeno . " " . $uzivatel->heslo . " VS " . $jmenoHledane ." ". $hesloHledane;
			
			
			if($uzivatel->jmeno == $jmenoHledane){
				//echo"UZIVATEL NALEZEN";
				$jmenoNalezeno = true;
				break;
			}
			
		}
		
		return $jmenoNalezeno;
		
		
	}
	
	
	
	function najdiIDUzivateleVPoliUzivatelu($jmenoHledane,$poleUzivatelu){
		/*
			Pokusi se nalest uzivatelObjekt v poli $poleUzivatelu a vratit jeho vlastnost 
			uzivatelID. Pokud jej nenajde, vraci 0.
			$jmenoHledane = retezec
			$poleUzivatelu  = pole objektu uzivatelObjekt  
			
		*/	
		$nalezeneID = 0;

		foreach($poleUzivatelu as $uzivatel ){
			
						
			
			if($uzivatel->jmeno == $jmenoHledane){
				//echo"UZIVATEL NALEZEN";
				$nalezeneID = $uzivatel->uzivatelID;
				break;
			}
			
		}
		
		return $nalezeneID;
		
		
	}
	
	
	
	function pokusSePrihlasitUzivatele(){
	/*
		Prevezme promenne z $_POST
		"jmenoUzivatele", "heslo" 
		nahraje uzivatele pomoci uzivatelDB->nahrajUzivatele() a overuje platnost uzivatele pomoci metody jmenoAHesloJeVPoliUzivatelu.
		Pokud je uzivatel nalezen v poli, vola prihlasUzivatele, pokud neni vyhodi hlasku a vrati se na prihlaseni.
	*/
				
		$jmenoNovehoUzivatele = $_POST["jmenoUzivatele"];
		$hesloNovehoUzivatele = $_POST["heslo"];

		$pripojeni = new mysqli(servername, username, password, databasename);
		$DBObjekt = new uzivatelDB($pripojeni);
		$poleUzivatelu = $DBObjekt->nahrajUzivatele();
		
		$uzivatelPlatny = jmenoAHesloJeVPoliUzivatelu($jmenoNovehoUzivatele,$hesloNovehoUzivatele,$poleUzivatelu);
		
		
		if($uzivatelPlatny){
				//readfile("templateTABULKA.html");	
				
				$idUzvatele = najdiIDUzivateleVPoliUzivatelu($jmenoNovehoUzivatele,$poleUzivatelu);
				
				
				prihlasUzivatele($jmenoNovehoUzivatele,$idUzvatele,$pripojeni);
		}
		else{
			
			//readfile("templateDALSIuzivatel.html");	
			//chybna konstrukce viz problemChromeEdge.bmp
			readfile("index.html");	
			
			
			$zprava = "Neplatný uživatel";
			echo "<script type='text/javascript'>alert('$zprava');</script>";
		}
		
	}
	
	function setInnerHTML($element, $html){
		/*
		Metoda pro snadne nastaveni textu do elementu - pouziva se pouze pro nastaveni 
		jmena prihlaseneho uzivatele. 
		prevzato z:
		https://stackoverflow.com/questions/2778110/change-innerhtml-of-a-php-domelement

		narozdil od javascriptu nelze v php nastavit innerhtml - je treba
		pouzit potomky (zde DocumentFragment)
		
		*/
	
    $fragment = $element->ownerDocument->createDocumentFragment();
    $fragment->appendXML($html);
    while ($element->hasChildNodes())
        $element->removeChild($element->firstChild);
    $element->appendChild($fragment);
	}
	
	function getInnerHTML($element){
		/*
		Metoda pro ziskani textu z elementu, vytvorena reverznim postupem z setInnerHTML.
		
		*/
		$html = '';
		$doc = $element->ownerDocument;
		
		foreach ($element->childNodes as $node) {
			$html .= $doc->saveHTML($node);
		}
		
		return $html;
	}
	
	
	function prihlasUzivatele($jmenoNovehoUzivatele, $idUzivatele,$pripojeni){
		/*
			Metoda pro prihlaseni uzivatele, PO KONTROLE UDAJU. 
			Zapocne session a vyplni "userID" a "userName". 
			Pote nahraje stranku  pro zobrazovani obsahu ("templateTABULKA.html")
			a nastavi do daneho elementu jmeno prihlaseneho uzivatele.			
			Nasledne nahraje obsah pomocu modulu 'LoadTable.php' (vraci DOM element) a ten 
			nastavi do prislusneho elementu. 
			Nakonec pomoci echo vytiskne vysledek.
		
		*/
		
		session_start();
		$_SESSION["userID"] = $idUzivatele;
		$_SESSION["userName"] = $jmenoNovehoUzivatele;
		
	
			
		$dom = new DOMDocument();
		$dom -> loadHTMLFile("templateTABULKA.html");

		
		$uzivatelPole = $dom->getElementById('uzviatelZobraz');
		setInnerHTML($uzivatelPole,$jmenoNovehoUzivatele);
		//pozor - vhodne jen k nastaveni jednoducheho textu, lepsi je pouzit DOM objekt (jako v loadTable.php)
		
		$naseptavac = $dom->getElementById('zadavac');		
		$naseptavacText = getInnerHTML($naseptavac);
		
		//echo "<script type='text/javascript'>alert('$naseptavacText');</script>";
		
		
		
		
		$tabulkaObrazkuPole = $dom->getElementById('tabulkaZde');

		$tabulkaObrazku = include 'LoadTable.php';
		//$tabulkaObrazku je DOM ELEMENT
		
		$tabulkaObrazkuPole->appendChild($tabulkaObrazku);
		
		echo $dom->saveHTML();	
	}
	
	function prejdiNaVytvoreniUzvivatele(){
	/*
		metoda pro prejiti na stranku "templatePRVNIuzivatel.html" a 
		vyprazdneni jednoho jejiho elementu.
	*/
	
		$dom = new DOMDocument();
		$dom -> loadHTMLFile("templatePRVNIuzivatel.html");
		
		$vyprazdnit = $dom->getElementById('prvniUzivatelZobrazovac');
		$prazdno = "";
		
		setInnerHTML($vyprazdnit,$prazdno);
		
		
		echo $dom->saveHTML();
		//readfile("templatePRVNIuzivatel.html");	
	}
	
	function zkusSmazatObrazek(){
	/*
		Prevezme 
		$_POST["IDMazanehoObrazku"]
		a 
		$_SESSION["userID"] $_SESSION["userName"]
		Nahraje vsechny obrazky z DB a prochazi je. Pokud najde obrazekObjekt s obrazekID = IDMazanehoObrazku
		tak jej urci jako mazany. Pokud je null, vypise hlasku. 
		Jinak overi, ze jeho vlastnost uzivatelID = userID ($_SESSION["userID"] é aktivni uzivatel), pokud ano, 
		smaze zanam v DB a soubor v adresari "obrazky". Pokud ne, vypise hlasku.
		
		
	*/

		$idMazanehoObrazku = $_POST["IDMazanehoObrazku"];
		session_start();	
		$idAktivnihoUzivatele = $_SESSION["userID"];
		$jmenoAktivnihoUzivatele = $_SESSION["userName"];
		
		$pripojeni = new mysqli(servername, username, password, databasename);
		$DBObjekt = new obrazekObjektDB($pripojeni);
		$obrazekVDB = $DBObjekt->nahrajObrazekByID($idMazanehoObrazku);
		
		if(obrazekVDB == null){
			$zprava = "Chyba: obrázek s ID: ".$idMazanehoObrazku. " se nenahrál z DB";
			echo $zprava;	
		}
		else {
			if(($obrazekVDB -> uzivatelID) == $idAktivnihoUzivatele){
				$DBObjekt->smazObrazekByID($idMazanehoObrazku);
				
				$filepath = imageFolder."/".$obrazekVDB->filePath;

				unlink($filepath);
			}
			else{
				//case obrazek nenahral aktivni uzivatel
				$zprava = "Obrázek nenahrál aktivní uživatel. Obrázek smí smazat pouze uživatel, co jej nahrál.";
				echo $zprava;	
			}
			
		}
		
		
		//MARKER SMAZAT
		
		//echo $jmenoAktivnihoUzivatele . " " . $idAktivnihoUzivatele . " " . $idMazanehoObrazku;
		//print_r($_SESSION);
			
	}
	
	function zkusNahratObrazek(){
	/*
		Metoda pro uploadovani ubrazku na server. 
		Prevezme data o aktivnim uzivateli 
		$_SESSION["userName"] $_SESSION["userID"]
		pak overi existenci 
		$_FILES["obrazekVstup"]["tmp_name"]
		pokud neexistuje, vypise skript pro hlasku. Pokud existuje, vytvori novy ObrazekObjekt. 
		Tento novy obrazek porovnava se vsemi jiz nahranymi obrazky a pokud najde obrazek se shodnym nazvem,
		vypise hlasku. Pokud ne, ulozi zaznam do DB a soubor do adresare "obrazky". 
		Pokud vse probehne OK, prihlasi stavajiciho uzivatele do stranky s daty a prepne jeji tab na obsah
		pomoci echo javaScriptu.
	
	*/
		
		$zprava = "Není vybrán žádný soubor!";
		
		session_start();

		$pripojeni = new mysqli(servername, username, password, databasename);
		  $DBObjekt = new obrazekObjektDB($pripojeni);
		  
		  $jmenoAktivnihoUzivatele= $_SESSION["userName"];
		  $IDAktivnihoUzivatele=$_SESSION["userID"];
			
			
			//overeni existence vybraneho souboru			
			if(file_exists($_FILES["obrazekVstup"]["tmp_name"]) == false){				
				//echo "KONEC";
				//return;	
				$zprava = "Není vybrán žádný soubor!";
				prihlasUzivatele($jmenoAktivnihoUzivatele, $IDAktivnihoUzivatele,$pripojeni);
								
				echo "<script type='text/javascript'>
				prepniTab(event, 'tabObsah2');		  
				</script>";
				$retezec =  "<script type='text/javascript'> alert('" . $zprava . "'); </script>";
				echo $retezec;	
				return;		
				
			}

		  
		  
		  $jmenoObrazku = $_FILES["obrazekVstup"]["name"];
		  $obrazek = new obrazekObjekt(0, $jmenoObrazku,$IDAktivnihoUzivatele,$jmenoAktivnihoUzivatele); //ID je auto-increment
		  
		  //overeni existence obrazku s identickym nazvem -> je nutne jej predtim smazat 
		  $obrazky = $DBObjekt->nahrajObrazky();		  
		  foreach($obrazky as $prochazeny ){
			if( $prochazeny->filePath == $obrazek ->filePath) {
				//echo "KONEC";
				//return;	
				$zprava = "Obrázek se stejným názvem nahrál jiný uživatel. Je nutné jej nejdříve smazat.";
				prihlasUzivatele($jmenoAktivnihoUzivatele, $IDAktivnihoUzivatele,$pripojeni);
								
				echo "<script type='text/javascript'>
				prepniTab(event, 'tabObsah2');		  
				</script>";
				$retezec =  "<script type='text/javascript'> alert('" . $zprava . "'); </script>";
				echo $retezec;	
				return;		
			}
			  
		  }
		  
		  
		  $DBObjekt -> saveObrazek($obrazek);	
		
		
		
		
		
		
		//echo "START <br>";

		if ($_FILES["obrazekVstup"]["error"] > 0)
		  {
		  //echo "Error: " . $_FILES["obrazekVstup"]["error"] . "<br />";
		  $zprava = "Error: " . $_FILES["obrazekVstup"]["error"] . "<br />";
		  }
		else
		  {
			
			/*
			echo "Upload: " . $_FILES["obrazekVstup"]["name"] . "<br />";
			echo "Type: " . $_FILES["obrazekVstup"]["type"] . "<br />";
			echo "Size: " . ($_FILES["obrazekVstup"]["size"] / 1024) . " Kb<br />";
			echo "Stored in: " . $_FILES["obrazekVstup"]["tmp_name"]. "<br />";		  
			echo "Uploaded by: " . $_SESSION["userName"]. "<br />";
			echo "Uploaded by user ID: " . $_SESSION["userID"]. "<br />";
			*/
		
		  
		  $target_dir = "obrazky/";
		  $target_file = $target_dir . basename($_FILES["obrazekVstup"]["name"]);
		  $prubehOK= move_uploaded_file($_FILES["obrazekVstup"]["tmp_name"], $target_file);
		  
		  if($prubehOK == true){
			//echo "Nahráno do /obrázky";
			$zprava = "Obrázek nahrán";
			
			
			
		  }
		  
		  }
		  
		  			
		  
		  prihlasUzivatele($jmenoAktivnihoUzivatele, $IDAktivnihoUzivatele,$pripojeni);
		  
		  echo "<script type='text/javascript'>
		  prepniTab(event, 'tabObsah1');		  
		  </script>";
		  
		  
		  $retezec =  "<script type='text/javascript'> alert('" . $zprava . "'); </script>";
		  
		   //echo "<script type='text/javascript'> alert('HAHA'); </script>";
		  echo $retezec;
		  
	}
?>