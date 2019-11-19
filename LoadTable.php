<?php

/*
 pouzito v PHPforMAIN.php
 ve funkci
 prihlasUzivatele($jmenoNovehoUzivatele, $idUzivatele,$pripojeni)
 vsechny parametry funkce se zde pouzivaji a musi byt vyplnene
 
 $pripojeni = mysqli
 $dom = DOMdocument
 $naseptavacText = string
*/


/*
VZOROVA TABULKA

<table class= "tabulkaObrazky">
							
 <tr class = "tabulkaObrazkyTr">
	<th class = "tabulkaObrazkyTh">Název obrázku</th>
	<th class = "tabulkaObrazkyTh">Obrázek</th>
	<th class = "tabulkaObrazkyTh">Uživatel</th>
	<th class = "tabulkaObrazkyTh">smazat</th>
  </tr>


<tr class = "tabulkaObrazkyTr">

	<td class = "tabulkaObrazkyTd">
		<div class= "tabulkaObrazkyTdInside">	
			smiley.bmp
		</div>
	
	</td>
	
	<td class = "tabulkaObrazkyTd">
			<div class= "tabulkaObrazkyTdInside">
				<div  class = "tabulkaObrazekContainer">
					<img class ="tabulkaObrazek" src="obrazky/smiley.bmp" alt="smiley.bmp" >
				</div>
			</div>
	</td>
	
	<td class = "tabulkaObrazkyTd">
		<div class= "tabulkaObrazkyTdInside">	
			A
		</div>
	
	</td>
	
	<td class = "tabulkaObrazkyTd">
		<div class= "tabulkaObrazkyTdInside">	
			
				<form action="PHPforMAIN.php" method="post">
					<button type="submit" name="smazatPost" value="IDOBRAZKU" class="tlacitko">smazat</button>
					<input type="hidden" name="switch" value="6" />
				</form>
			
			
		</div>									
	</td>	
</tr>



</table>
							
							
*/



$predpona = 'obrazky/';
$dbObjekt = new obrazekObjektDB($pripojeni);
//$poleObrazku = $dbObjekt->nahrajObrazky();

$poleObrazku = $dbObjekt->nahrajObrazkyVyhledavac($naseptavacText);


$vracenaTabulka = $dom->createElement('table','');
$vracenaTabulka->setAttribute('class','tabulkaObrazky');

$titulek = $dom->createElement('tr','');
$titulek->setAttribute('class','tabulkaObrazkyTr');

$nadpis= $dom->createElement('th','Název obrázku');
$nadpis->setAttribute('class','tabulkaObrazkyTh');
$titulek->appendChild($nadpis);

$nadpis= $dom->createElement('th','Obrázek');
$nadpis->setAttribute('class','tabulkaObrazkyTh');
$titulek->appendChild($nadpis);

$nadpis= $dom->createElement('th','Uživatel');
$nadpis->setAttribute('class','tabulkaObrazkyTh');
$titulek->appendChild($nadpis);

$nadpis= $dom->createElement('th','smazat');
$nadpis->setAttribute('class','tabulkaObrazkyTh');
$titulek->appendChild($nadpis);

$vracenaTabulka->appendChild($titulek);

//nadpisy jsou v tabulce, nyni data

foreach($poleObrazku as $obrazek){
	
$tr = $dom->createElement('tr','');
$tr->setAttribute('class','tabulkaObrazkyTr');	

//nazev obrazku
$td= $dom->createElement('td',$obrazek->filePath);
$td->setAttribute('class','tabulkaObrazkyTd');
$tr->appendChild($td);

//"nahled" obrazku
$td= $dom->createElement('td','');
$td->setAttribute('class','tabulkaObrazkyTd');

$divInside = $dom->createElement('div','');
$divInside->setAttribute('class','tabulkaObrazkyTdInside');
$td->appendChild($divInside);

$divContainer = $dom->createElement('div','');
$divContainer->setAttribute('class','tabulkaObrazekContainer');
$divContainer->setAttribute('onClick','obrazekSkript(this)');
$divInside->appendChild($divContainer);


$imgTag = $dom->createElement('img','');
$imgTag->setAttribute('class','tabulkaObrazek');

$srcPath = $predpona . ($obrazek->filePath);

$imgTag->setAttribute('src',$srcPath);
$imgTag->setAttribute('alt',$obrazek->filePath);
$divContainer->appendChild($imgTag);

$tr->appendChild($td);
	
//jmeno uzivatele
$td= $dom->createElement('td',$obrazek->jmenoUzivateleCoNahral);
$td->setAttribute('class','tabulkaObrazkyTd');
$tr->appendChild($td);
	
//smazat tag
$td= $dom->createElement('td','');
$td->setAttribute('class','tabulkaObrazkyTd');

$divInside = $dom->createElement('div','');
$divInside->setAttribute('class','tabulkaObrazkyTdInside');
$td->appendChild($divInside);

$divContainer = $dom->createElement('div','');
$divContainer->setAttribute('class','tabulkasmazatContainer');
$divInside->appendChild($divContainer);


$form = $dom -> createElement('form','');
//$form->setAttribute('action','PHPforMAIN.php');
$form->setAttribute('method','POST');
$form->setAttribute('class','tabulkaSmazatForm');
$divContainer->appendChild($form);

$button = $dom -> createElement('button','smazat');
$button->setAttribute('class','tlacitko');
$button->setAttribute('type','button');
$button->setAttribute('name','smazatPost');
$button->setAttribute('onClick','smazatSkript(this)');
$button->setAttribute('value',$obrazek->obrazekID);
$form->appendChild($button);

$hidden = $dom -> createElement('input','');
$hidden->setAttribute('type','hidden');
$hidden->setAttribute('name','switch');
$hidden->setAttribute('value','6');

$form->appendChild($hidden);

$tr->appendChild($td);




/*

		<form action="PHPforMAIN.php" method="post">
					<button type="submit" name="smazatPost" value="IDOBRAZKU" class="tlacitko">smazat</button>
					<input type="hidden" name="switch" value="6" />
				</form>
*/
	
$vracenaTabulka->appendChild($tr);

}

	
return $vracenaTabulka;
?>