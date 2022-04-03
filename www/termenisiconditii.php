<?php
require_once("_cs_config.php");
function header_ob(){ 
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<title>MedList - Programari medicale online</title>
	<meta name="robots" content="index, follow" />	
	<style>
	</style>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
} 
$GLOBALS['header_ob'] = header_ob();
require_once(cs_path . DIRECTORY_SEPARATOR . 'header.php' );
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12">
			<div class="panel panel-default" style="padding:0; margin-top:5px">
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-envelope-open" aria-hidden="true"></i> Termeni si Conditii</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 form-horizontal" id="contact_form">
							
 &nbsp; &nbsp; Platforma de programari online MedList („Platforma”) este un software (program) care se poate accesa de pe orice dispozitiv fix (desktop, computer) sau mobil (tablete  sau telefoane mobile) prin intermediul unui browser (navigator), si care pune la dispozitia dvs. („Utilizatorul”) o baza de date cu doctori, fizioterapeuti care lucreaza in, cabinete medicale individuale (CMI), cabinete de fizioterapie, clinici, policlinici si spitale, si va ajuta sa gasiti personalul medical pe care il cautati si la care puteti face o programare.<br /><br />
 
 &nbsp; &nbsp; Prezentele Termeni si Conditii reprezinta prevederile in baza carora Utilizatorul poate solicita prin intermediul Platformei prestarea Serviciului (definit mai jos) de catre Furnizor (definit mai jos).<br /><br />
DEFINITII<br /><br />
"Platforma" - Aplicatie software care este accesibila prin intermediul unui browser (navigator) de pe orice dispozitiv fix (desktop) sau mobil (tableta, telefon mobil) care este conectat la internet, si va ofera posibilitatea de a efectua programari medicala la orice unitate medicala care este inscrisa pe platforma MedList in functie de disponibilitatea de la acel moment, precum si pentru a realiza programari la laboratoarele de imagistica si analize medicale.<br /><br />

"Unitati medicale" - Orice societate privata de drept roman sau cabinet medical individual care a obtinut autorizatiile necesare in vederea prestarii serviciilor medicale pe teritoriul Romaniei si care este partener al Furnizorului, ca urmare a incheierii unui contract in vederea listarii pe platforma.<br /><br />
„Contract” - Contractul incheiat intre Utilizator si Furnizor, conform prezentelor Termene si Conditii, in vederea efectuarii unei sau mai multor programari medicale.<br /><br />
„Date cu caracter personal” - Orice informatie in legatura cu Utilizatorul care permite identificarea sa in mod direct sau indirect, cum ar fi nume, prenume, numar de telefon, e-mail care sunt furnizate de Utilizator prin Platforma in scopul efectuarii de programarii medicale.<br /><br />


„Drepturile de Proprietate Intelectuala privind Platforma on-line si Aplicatia” - Totalitatea drepturilor de proprietate intelectuala detinute de catre Furnizor cu privire la Platforma.<br /><br />
„Platforma on-line” - totalitatea functionalitatilor disponibile pentru Platforma online.<br /><br />
„Furnizor” :<br /><br />
"Serviciu" : <br />
a.	oferirea posibilitatii Utilizatorilor de a efectua programari medicale prin intermediul unei aplicatii web („Platforma”), la doctori, fizioterapeuti din cadrul unitatilor medicale partenere;<br />
b.	oferirea posibilitatii unitatilor medicale de a deveni partenere ale Furnizorului in vederea listarii lor in baza de date a Platformei MedList.<br /><br />
INCHEIEREA CONTRACTULUI<br /><br />
1. Inregistrarea si folosirea Platformei constituie consimtamantul expres si neechivoc al Utilizatorului cu privire la incheierea Contractului care include prezentele Termene si Conditii si Politica de confidentialitate.<br />
2. Contractul dintre Prestator si Utilizator pentru prestarea serviciilor se incheie numai daca sunt indeplinite conditiile enumerate mai jos:<br />
a.	Varsta Utilizatorului este de minim 18 ani;<br />
b.	Utilizatorul acceseaza site-ul web www.medlist.ro;<br />
c.	Utilizatorul citeste cu atentie prezentele Termeni si Conditii si Politica de confidentialitate este de acord cu privire la acestea;<br />
d.	Utilizatorul isi creeaza un cont completand si transmitand informatiile reale despre sine, mentionate in formularul de programare de pe Platforma;<br /><br />
 In cazul in care numele de utilizator si parola au fost compromise, va rugam sa ne notificati imediat prin transmiterea unui e-mail la adresa office@medlist.ro.<br /><br />
3. Contractul va fi considerat incheiat (semnat) in momentul in care Utilizatorul apasa butonul Finalizeaza programare pentru prima data.<br /><br />

PRETUL<br /><br />
1. Folosirea site-ului si serviciile aferente catre Utilizatorii care doresc sa efectueze programari medicale sunt gratuite, in afara costurilor produse de utilizarea serviciilor de comunicatii si date mobile, in functie de taxele operatorului de internet sau telefonie mobila.<br />

2. Unitatilor medicale care sunt listate in cadrul aplicatiei MedList vor fi taxate conform contractelor incheite cu acesta si sunt confidentiale.<br /><br />

 PRESTAREA SERVICIILOR<br /><br />
1. Furnizorul se obliga sa presteze Serviciul Utilizatorului in timp real, prin intermediul Platformei online.<br />
2. Serviciul este furnizat in format electronic, prin afisarea confirmarii programarii pe ecranul dispozitivului pe care ruleaza Platforma.<br />
3. Utilizatorul intelege si este de acord cu faptul ca toate Drepturile de Proprietate Intelectuala privind Platforma online apartin Furnizorului, Utilizatorul neavand niciun drept de proprietate intelectuala prin prezentul Contract, ci doar la un drept de utilizare neexclusiv, pe durata Contractului, asupra Platformei, in scopul exclusiv al efectuarii de programari medicale in sistem online.<br /><br />

LIMITAREA RASPUNDERII<br /><br />
1. Furnizorul nu poate garanta expres sau implicit calificarile profesionale si experienta personalului medical  inscris in baza noastra de date de catre Unitatile medicale partenere, calitatea serviciilor medicale, pretul acestora. Evaluarea serviciilor oferite de acestea nu fac parte din serviciile oferite de Platforma noastra. <br />
2. Utilizatorul este singurul responsabil de alegerea facuta in ceea ce priveste personalul medical la care a efectuat programarea.<br />
3. Furnizorul nu sustine si nici nu recomanda vreun doctor inscris de catre Unitatile medicale partenere in baza de date a Platformei in mod special.<br />
4. Furnizorul nu isi asuma raspunderea pentru utilizarea Platformei pentru efectuarii unei programari medicale de urgenta. In situatia in care aveti nevoie de ingrijiri medicale de urgenta, va recomandam sa apelati serviciul unic de urgenta apeland numarul 112. Daca veti utiliza Serviciul de programari in acest caza o veti face doar pe riscul Dumneavoastra (Utilizatorului).<br />
5.Furnizorul nu poate garanta disponibilitatea unei doctor, fizioterapeut apartinand unei anumite unitati medicale la o zi sau ora anume si nu putem fi raspunzatori pentru programarile anulate, efectuate necorespunzator sau orice alte repercursiuni care reies din acestea.<br />
6. Furnizorul nu isi asuma raspunderea pentru situatiile in care operatorii Unitatile medicale nu si-au actualizat datele si intervalele orare corespunzatore pentru programarile medicale si astfel se creeaza suprapuneri de programari.<br />
7. Nu garantam expres sau implicit serviciile. <br />
8. Utilizatorul intelege faptul ca furnizarea Serviciilor poate fi modificata/incetata si intelege ca Furnizorul nu este raspunzator pentru aceasta.<br />
9. Furnizorul nu isi asuma raspunderea pentru intreruperi ale functionarii Platformei in vederea realizarii imbunatatirilor, mentinerii la o stare optima de functionare, lipsa functionarii internetului sau a altor situatii in care Platforma nu poate fi accesata din cauza erorilor tehnice ce nu sunt sub controlul Furnizorul.<br />
10. Furnizorul nu isi asuma raspunderea pentru nicio dauna care rezulta din utilizarea, sau incapacitatea de a utiliza Platforma.<br /><br />

 DURATA SI INCETAREA CONTRACTULUI<br /><br />
1. Contractul se considera incheiat pentru o perioada nedeterminata.<br />
2. Utilizatorul poate renunta la folosirea Platformei oricand isi doreste.<br />
3. Furnizorul isi rezerva dreptul de a suspenda sau inceta imediat Serviciile catre un Utilizator in orice moment si fara notificare.<br />
4 Dupa incetarea Contractului Utilizatorul nu mai poate folosi Platforma.<br /><br />

DECLARATIE PRIVIND DATELE CU CARACTER PERSONAL<br /><br />

In conformitate cu prevederile Legii nr. 677/2001 pentru protectia persoanelor cu privire la prelucrarea datelor cu caracter personal si libera circulatie a acestor date, modificata si completata („Legea nr. 677/2001”), Furnizorul va colecta datele cu caracter personal ale Utilizatorului, numai in scopul efectuarii programarii medicale la Unitatile medicale partenere.<br /><br />
In baza acestui Contract, Furnizorul va prelucra datele cu caracter personal ale Utilizatorului, in calitate de persoana imputernicita conform art. 3 lit. f) din Legea nr. 677/2001, actionand in numele si pe seama Unitatilor medicale partenere, acesta din urma avand calitatea de operator conform art. 3 lit. e) din aceeasi Lege. <br /><br />

Furnizorul se obliga sa respecte urmatoarele obligatii: <br /><br />
a) sa actioneze intocmai pentru atingerea scopului legat de desfasurarea Serviciului si numai in conformitate cu instructiunile primite din partea Unitatilor medicale partenere;<br />
b) sa aplice masurile tehnice si organizatorice adecvate pentru protejarea datelor cu caracter personal impotriva distrugerii accidentale sau ilegale, pierderii, modificarii, dezvaluirii sau accesului neautorizat, precum si impotriva oricarei alte forme de prelucrare ilegala.<br /><br />
Prin accesarea Platformei, completarea formularului disponibil in cadrul Platformei, Utilizatorul confirma si este de acord, in mod expres si neechivoc, ca Furnizorul sa prelucreze datele sale cu caracter personal pentru realizarea scopului enuntat mai sus.<br /><br />
Furnizorul va prelucra datele cu caracter personal ale Utilizatorului pe intreaga perioada de timp necesara atingerii scopului de mai sus si chiar ulterior, in conformitate cu legislatia aplicabila.<br /><br />
Daca unele dintre datele Utilizatorului sunt incorecte, este necesar ca acesta sa contacteze Furnizorul cat mai curand posibil pentru remedierea acestei probleme la adresa  de email office@medlist.ro.<br /><br />

PREVEDERI JURIDICE<br /><br />
Utilizarea Platformei in vederea programarilor medicale si incheierea Contractului sunt guvernate si interpretate in conformitate cu dreptul roman.
Se va incerca rezolvarea oricarei dispute in legatura cu utilizarea Platformei si/sau Contractul mai intai pe cale amiabila. In cazul in care partile nu ajung la un acord cu privire la disputa, aceasta va fi definitiv solutionata de instanta romana competenta.<br /><br />

PREVEDERI DIVERSE<br /><br />
1. Furnizorul acestor servicii isi rezerva dreptul de a modifica sau completa prezentele Termeni si Conditii in orice moment, care vor fi publicate pe Platforma.<br />
2. Prin continuarea folosirii Platformei ulterior intrari in vigoare a unor modificari sau completari, Utilizatorul este de acord sa le respecte.
Utilizatorul ii este recomandat sa verifice periodic prezentele Termene si Conditii.<br />
3. In cazul in care utilizatorul nu doreste sa accepte prezentele termene si conditii, inclusiv modificarile sau completarile acestora, trebuie sa inceteze sa mai foloseasca platforma  si serviciile oferite.<br />
4. Utilizatorul intelege faptul ca tarifele cu privire la consultatiile medicale vor fi in intregime suportate de acesta si ca orice disputa intre acesta si furnizorii de servicii medicale va fi solutionata direct cu furnizorii de servicii medicale, fara ca Furnizorul sa fie in vreun fel implicat.<br />
5. Furnizorul informeaza prin prezenta Utilizatorul ca va desfasura activitati de marketing si va transmite informatii de marketing Utilizatorului, ca urmare a exprimarii consimtamantului prin bifarea casutei de mai jos.<br />
6.Utilizatorul este expres de acord cu prezentele Termeni si Conditii si Politica de confidentialitate.<br /><br />


						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php 
function footer_ob(){
	global $postid;
	$ret = array('success'=>false, 'resp'=>array(),'error'=>'');
	ob_start();
	?>
	<script>
		contact_trimite_click = function(){
			cs('mesaje/send',{
				from:1,
				text:'contact form - ' + $('#contact_form input[name=email]').val() + " - " + $('#contact_form textarea[name=mesaj]').val()
			}).then(function(mesaje_send){
				if ((typeof(mesaje_send.success) != 'undefined') && (mesaje_send.success == true)){
					alert('mesaj trimis cu succes')
				}else{
					if ((typeof(mesaje_send.error) != 'undefined') && (mesaje_send.error != '')){
						alert(error)
					}else{
						alert('ceva nu a functionat')
					}
				}
				window.location.href = '/'
			})
		}
	</script>
	<?php
	$ret['resp']['html'] = ob_get_contents(); ob_end_clean();
	$ret['success'] = true;
	return $ret;
}
$GLOBALS['footer_ob'] = footer_ob();
cscheck($GLOBALS['footer_ob']);
require_once(cs_path . DIRECTORY_SEPARATOR . 'footer.php' ); 
?>
