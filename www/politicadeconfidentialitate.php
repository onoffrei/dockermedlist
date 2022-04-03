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
				<div class="panel-heading"><h3 class="panel-title"><i class="fa fa-envelope-open" aria-hidden="true"></i> Politica de confidentialitate</h3></div>
				<div class="panel-body">
					<div class="row">
						<div class="col-xs-12 form-horizontal" id="contact_form">
							
INTRODUCERE<br /><br />

Confidentialitatea datelor dumneavoastra este o responsabilitate foarte mare pentru noi. Datele dumneavoastra nu vor fi divulgate unor terte persoane, nu vor fi vandute sau oferite altor institutii, ele vor fi folosite doar pentru a va oferi serviciile noastre.
Scopul acestei Politici de confidentialitate este acela de a va explica ce informatii colectam, pentru ce anume le colectam in cadrul Platformei online MedList.ro si cum le puteti edita (actualiza, modifica, sterge).<br />
Prin utilizarea platformei online MedList, declarati ca sunteti de acord cu colectarea si utilizarea datelor dumneavoastra astfel cum sunt descrise in prezentul document. in cazul in care nu sunteti de acord cu prezenta politica de confidentialitate, va informam ca nu puteti utiliza serviciile oferite in cadrul platformei.<br /><br />

Colectarea, stocarea si utilizarea datelor<br /><br />
Siteul nostru on-line poate fi consultat si fara dezvaluirea identitatii dumneavoastra sau furnizarea de informatii cu referire la datele dumneavoastra personale, dar se pot obtine in mod automat numele furnizorului dumneavoastra de internet precum si siteul web de la care ne vizitati, identificatori unici care sunt asociati cu browserul sau dispozitivul pe care il folositi. In acest fel putem gestiona cat mai correct preferintele dumneavoastra.<br /><br />

Pentru crearea unui cont de utilizator al serviciilor noastre pe siteul web MedList va vor fi solicitate urmatoarele date personale:<br />

Numele si prenumele dumneavoastra;<br />
Numar de telefon, e-mail si o parola personalizata pentru crearea contului in care puteti gestiona datele personale, precum si a programarilor realizate de dumneavoastra, dar si alte date necesare in vederea prestarii Serviciilor.<br />
Aceste date sunt utilizate doar in scopul efectuarii de programari medicale pe platform online MedList.<br /><br />

 In momentul utilizarii Serviciilor noastre, unele date sunt transmise in mod automat de catre browser-ul dumneavoastra web si sunt colectate si stocate pe serverul nostru. Aceste date includ, fara a se limita la:<br />
a.	Adrese IP (internet protocol); <br />
b.	Tipul de aparat utilizat si sistemul de operare;<br />
c.	Tipul browser-ului cat si versiunea sa;<br />
d.	Data si ora accesarii Serviciilor noastre;<br /><br />

Pentru utilizarea Platformei on-line pot fi stocate pe dispozitivele dumneavoastra (de tip desktop sau mobile) sub forma de cookies anumite informatii care sunt folosite pentru utilizarea acesteia. Aceste cookies ne ajuta in a va oferi o caltate deosebita a Serviciilor oferite.<br />
Mai mult, stocarea acestor informatii ne ajuta sa intelegem mai bine interactiunea dumneavoastra cu Serviciile oferite de noi, ajutandu-ne sa va oferim o experienta placuta in utilizare Platformei. Puteti de asemenea sa dezactivati colectarea acestor Date de tip cookie din setarile browserului, dar ca urmare a acestei actiuni unele servicii vor putea fi afectate.<br />
Absolut toate Datele vor fi stocate pe serverele noastre si ale Unitatilor medicale partenere in vederea prestarii Serviciilor noastre si ale acestora din urma.<br />
Vom colecta si stoca de asemenea toata corespondenta pe care dumneavoastra ne-o transmiteti.<br />
Datele referitoare la fisa medicala si/sau rezultatele analizelor medicale ale Utilizatorului nu vor fi stocate pe Platforma noastra, ci vor fi disponibile doar la nivelul Unitatilor medicale furnizoare de servicii medicale.<br /><br />

UTILIZAREA DATELOR<br /><br />

Furnizorul va folosi Datele dumneavoastra pentru prestarea de in vederea prestarii de Servicii pentru a efectua programari pentru consultatii medicale si pentru a le transmite catre Unitatea medicala dorita, pentru a furniza, administra, mentine, ori extinde Serviciile oferite de noi,  pentru a furniza informatii cerute de dumneavoastra, pentru va raspunde la corespondenta primita, si pentru a va contacta atunci cand va fi necesar.<br />
In cazul in care va hotarati sa stergeti contul dumneavoastra, veti putea efectua acest din contul dumneavoastra, accesand butonul CONT si accesarea linkului Sterge cont. In acest fel toate datele dumneavoastra vor fi sterse din aplicatia si implicit de pe serverele noastre.<br /><br />
Sunt situatii cand stergerea contului nu implica si stergerea Datelor dumneavoastra, rezervandu-ne dreptul de a retine aceste Date in cazul in care acestea sunt necesare in indeplinirea obligatiilor noastre legale, solutionarea litigiilor etc.<br /><br />
Datele personale vor fi stocate de Furnizor pe toata durata Contractului, iar in momentul incetarii Contractului toate Datele Utilizatorului vor fi sterse in totalitate.<br /><br />

ACCESAREA DATELOR<br /><br />
Datele dumneavoastra vor fi accesate numai de catre salariatii si Unitatile medicale contractante ale Furnizorului si numai in scopul furnizarii Serviciilor. <br /><br />

OBLIGATIILE UTILIZATORILOR SERVICIILOR<br /><br />
In calitate de Utilizator al Serviciilor noastre, aveti urmatoarele obligatii:<br />
a.	sa ne furnizati Date adevarate, exacte si complete despre dumneavoastra, asa cum cere formularul de inregistrare al Platformei. In cazul in care Datele dumneavoastra nu sunt reale, exacte si complete, aveti obligatia de a le modifica in cel mai scurt timp accesand butonul CONT in vederea remedierii acestei situatii;<br />
b.	sa mentineti si sa innoiti Datele dumneavoastra pentru a fi comforme cu realitatea, atunci cand este necesar;<br />
c.	sa nu publicati recenzii si evaluari ale medicilor, serviciilor medicale sau CMI/Clinicii/Policlinicaii/Spitalului, ori informatii prohibite de prevederile legale in vigoare;<br />
In caz de nerespectare a acestor conditii, Furnizorul se va disocia de autorul acestora, va sterge informatiile respective si isi rezerva dreptul de a actiona pe cale legala.<br /><br />

DURATA STOCARII DATELOR<br /><br />
Datele Personale completate de Utilizator vor fi stocate de Furnizor pe toata durata Contractului, iar in momentul incetarii prezentului Contract toate Datele referitoare la Utilizatorul respective vor fi sterse.<br /><br />

MODIFICAREA/COMPLETAREA PREZENTEI POLITICI DE CONFIDENTIALITATE<br /><br />
Ne rezervam dreptul de a modifica/completa prezenta Politica de confidentialitate din cand in cand, ca urmare a schimbarilor legislative sau a tipului de Servicii oferite.<br /><br />
Actualizarea Politicii de confidentialitate se va publica pe Platforma si va intra in vigoare in momentul publicarii acesteia.<br /><br />
Folorirea Platformei online ulterior actualizarii Politicii de confidentialitate obliga Utilizatorul sa respecte aceste Conditii.<br /><br />
Recomandam Utilizatorului sa verifica periodic Politica de confidentialitate.<br /><br />
In cazul in care utilizatorul nu doreste sa accepte prezenta politica de confidentialitate, inclusiv modificarile sau completarile ulterioare, nu va mai putea utiliza platforma online si serviciile oferite de Furnizor.<br /><br />
 
SCHIMBARI JURIDICE IN CADRUL FURNIZORULUI<br /><br />
In situatia in care vom fi implicati intr-un proces de reorganizare, fuziune, achizitie sau orice alta schimbare a controlului asupra noastra sau transfer de active, Datele dumneavoastra pot fi transferate ca parte a respectivei tranzactii, moment in care va vom notifica pentru a va comunica drepturile dumneavoastra si a va solicita consimtamantul cu privire la un asemenea transfer.<br /><br />
 
 




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
