<?php

/*
	php modul kde jsou definovany konstanty a tridy pro projekt projektik
	konstanty urcuji pripojeni k serveru a heslo a uzivatele k DB
*/	



	define("servername", "localhost");
	define("username", "martin");
	define("password", "martin");
	define("databasename", "projektikdb");
	define("imageFolder","obrazky");

class uzivatelObjekt{
/*
	trida reprezentujici uzivatele
*/
		
		public $jmeno;
		public $heslo;
		public $uzivatelID;
		
		public function __construct($jmeno, $heslo) {
			$this -> jmeno = $jmeno;
			$this -> heslo = $heslo;
		}
	}
	
class uzivatelDB{
/*
	trida pro praci s tabulkou "tabUzivatele" v DB, nahrana data se prevadi na objekty uzivatelObjekt
	$pripojeni - mysqliconnection, existence pro funkci nutna
*/
	public $pripojeni;
	
	public function __construct($pripojeniNew) {
		$this -> pripojeni = $pripojeniNew;
	}
	
	public function saveUzivatel($uzivatel){
	/*
		metoda pro vlozeni uzivatele, NEOVERUJE zadne podminky
	*/
		$jmeno = $uzivatel -> jmeno;
		$heslo = $uzivatel -> heslo;
		
		
		$dotaz = "INSERT INTO `tabUzivatele` (`uzivatelJmeno`, `uzivatelHeslo`) VALUES ( '".$jmeno."', '".$heslo."');";
		
		//echo " ".$dotaz." ";
		
		$prubehOK = mysqli_query($this -> pripojeni,$dotaz);
		
			if($prubehOK == true){
				//nedelat nic
			}
		else{
			echo("Pri vkladani dat nastala chyba!" . mysqli_error($this->pripojeni) );		
		}	
	
	}
	
	public function nahrajUzivatele(){			
		/*
			vraci pole objektu uzivatelObjekt nahranych z DB z tabUzivatele (bez vyberu)
			vzdy vraci alespon prazdne pole
		*/			
			$dotaz = "SELECT uzivatelID,uzivatelJmeno,uzivatelHeslo FROM `tabUzivatele` WHERE 1 ";
			
						
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			$vratit = array();
			
			if($vysledek == false){
				echo("Pri nahravani dat nastala chyba!" . mysqli_error($this->pripojeni) );
			}
		else{
			//case vysledek je mysqli_result
			$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
			foreach($pole as $radek ){
				
				/*
				foreach($radek as $x => $x_value) {
					echo "Key=" . $x . ", Value=" . $x_value;
					echo "<br>";
				}
				*/

				$jmeno = $radek["uzivatelJmeno"];
				$heslo = $radek["uzivatelHeslo"];
				
				$uzivatel = new uzivatelObjekt($jmeno,$heslo);
				
				$uzivatel->uzivatelID = $radek["uzivatelID"];
				
				array_push($vratit,$uzivatel); 
				
				
			}
		}	
		return	$vratit;		
		
	}

}

class obrazekObjekt{
/*
	objekt pro praci s objekty reprezentujici soubor - obrazek, a zaznam v tabulce tabObrazky
	
	
	public $obrazekID; - ID z tabObrazky
	public $filePath; - nazev souboru (umisteny v adresari obrazky)
	public $uzivatelID; - ID uzivatele co obrázek nahrál, FK do tabUzivatele
	public $jmenoUzivateleCoNahral;	- jmeno uzivatele co obrázek nahrál (z tabUzivatele)
	
*/
	public $obrazekID;
	public $filePath;
	public $uzivatelID;
	public $jmenoUzivateleCoNahral;	
	
	public function __construct($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral) {
		$this -> obrazekID = $obrazekID;
		$this -> filePath = $filePath;
		$this -> uzivatelID = $uzivatelID;
		$this -> jmenoUzivateleCoNahral = $jmenoUzivateleCoNahral;
	}
}

class obrazekObjektDB{
/*
	trida pro praci s tabulkou "tabObrazky" v DB, nahrana data se prevadi na objekty obrazekObjekt
	$pripojeni - mysqliconnection, existence pro funkci nutna
*/

	public $pripojeni;
	
	public function __construct($pripojeniNew) {
		$this -> pripojeni = $pripojeniNew;
	}
	
	public function saveObrazek($obrazek){
	/*
		metoda pro vlozeni objektu $obrazek do tabulky tabObrazky, NEPROVADI zadnou kontrolu
	*/
		$filePath = $obrazek -> filePath;
		$uzivatelID = $obrazek -> uzivatelID;
		
		
		$dotaz = "INSERT INTO `tabObrazky` (`filepath`, `uzivatelID`) VALUES ( '".$filePath."', '".$uzivatelID."');";
		
		//echo " ".$dotaz." ";
		
		$prubehOK = mysqli_query($this -> pripojeni,$dotaz);
		
			if($prubehOK == true){
				//nedelat nic
			}
		else{
			echo("Pri vkladani dat nastala chyba!" . mysqli_error($this->pripojeni) );		
		}	
	
	}


	
	public function nahrajObrazky(){			
		/*
		vraci pole objektu obrazekObjekt nahranych z DB z tabObrazky (bez vyberu)
		vzdy vraci alespon prazdne pole
		*/			
			$dotaz = "SELECT obrazekID,filepath,tabObrazky.uzivatelID,uzivatelJmeno FROM tabObrazky 
					JOIN tabUzivatele ON tabUzivatele.uzivatelID = tabObrazky.uzivatelID WHERE 1 ";
			
				//echo "X ".$dotaz ." X";
			
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			$vratit = array();
			
			if($vysledek == false){
				echo("Pri nahravani dat nastala chyba!" . mysqli_error($this->pripojeni) );
			}
		else{
			//case vysledek je mysqli_result
			$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
			foreach($pole as $radek ){
				
				/*
				foreach($radek as $x => $x_value) {
					echo "Key=" . $x . ", Value=" . $x_value;
					echo "<br>";
				}
				*/

				$obrazekID = $radek["obrazekID"];
				$filePath = $radek["filepath"];
				$uzivatelID = $radek["uzivatelID"];
				$jmenoUzivateleCoNahral = $radek["uzivatelJmeno"];	
				
				/*
				echo $obrazekID;
				echo $filePath;
				echo $uzivatelID;
				*/
				//echo '*'.$jmenoUzivateleCoNahral.'*';
				
				
				$obrazek = new obrazekObjekt($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral);
				
				//echo $obrazek->filePath;
				//echo $obrazek->jmenoUzivateleCoNahral;
				
				array_push($vratit,$obrazek); 
				
				
			}
		}	
		return	$vratit;		
		
	}
	
	



	public function nahrajObrazkyVyhledavac($textHledani){			
		/*
		vraci pole objektu obrazekObjekt nahranych z DB z tabObrazky - podle retezce $textHledani, ktery musi byt ve sloupci filepath (ILIKE vyber)
		vzdy vraci alespon prazdne pole
		*/			
			$dotaz = "SELECT obrazekID,filepath,tabObrazky.uzivatelID,uzivatelJmeno FROM tabObrazky 
					JOIN tabUzivatele ON tabUzivatele.uzivatelID = tabObrazky.uzivatelID WHERE filepath LIKE ".'"%' . $textHledani.'%"';
			
				//echo "X ".$dotaz ." X";
			
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			$vratit = array();
			
			if($vysledek == false){
				echo("Pri nahravani dat nastala chyba!" . mysqli_error($this->pripojeni) );
			}
		else{
			//case vysledek je mysqli_result
			$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
			foreach($pole as $radek ){
				
				/*
				foreach($radek as $x => $x_value) {
					echo "Key=" . $x . ", Value=" . $x_value;
					echo "<br>";
				}
				*/

				$obrazekID = $radek["obrazekID"];
				$filePath = $radek["filepath"];
				$uzivatelID = $radek["uzivatelID"];
				$jmenoUzivateleCoNahral = $radek["uzivatelJmeno"];	
				
				/*
				echo $obrazekID;
				echo $filePath;
				echo $uzivatelID;
				*/
				//echo '*'.$jmenoUzivateleCoNahral.'*';
				
				
				$obrazek = new obrazekObjekt($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral);
				
				//echo $obrazek->filePath;
				//echo $obrazek->jmenoUzivateleCoNahral;
				
				array_push($vratit,$obrazek); 
				
				
			}
		}	
		return	$vratit;		
		
	}
	
	public function nahrajObrazekByID($obrazekID){
	/*
		funkce co nahraje jeden obrazek z tabObrazky podle $obrazekID, pokud se nahrani nezdari, vraci 
		null
	*/
		$dotaz = "SELECT obrazekID,filepath,tabObrazky.uzivatelID,uzivatelJmeno FROM tabObrazky 
					JOIN tabUzivatele ON tabUzivatele.uzivatelID = tabObrazky.uzivatelID WHERE obrazekID = ". $obrazekID;
			
				//echo "X ".$dotaz ." X";
			$vratit = null;
			
			$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			if($vysledek == false){
				echo("Pri nahravani dat nastala chyba!" . mysqli_error($this->pripojeni) );
			}
			else{
				//case vysledek je mysqli_result
				$pole = $vysledek->fetch_all(MYSQLI_ASSOC);
				foreach($pole as $radek ){
			
					$obrazekID = $radek["obrazekID"];
					$filePath = $radek["filepath"];
					$uzivatelID = $radek["uzivatelID"];
					$jmenoUzivateleCoNahral = $radek["uzivatelJmeno"];	
					
										
					$obrazek = new obrazekObjekt($obrazekID, $filePath,$uzivatelID,$jmenoUzivateleCoNahral);
			
					$vratit = $obrazek;
					
					
				}
			}	
		
		return $vratit;
	}	
	
	public function smazObrazekByID($obrazekID){
	/*
		pokusi se smazat zaznam z tabObrazky podle obrazekID, vraci TRUE pokud se smazani provede, FALSE pokud ne
	*/
		$vratit = false;
		$dotaz = "DELETE FROM tabObrazky WHERE obrazekID = ". $obrazekID;
		$vysledek = mysqli_query($this -> pripojeni,$dotaz);
			if($vysledek == false){
				echo("Pri nahravani dat nastala chyba!" . mysqli_error($this->pripojeni) );
			}
			else{
				$vratit = true;
			}
		return $vratit;	
	}
}





	
?>