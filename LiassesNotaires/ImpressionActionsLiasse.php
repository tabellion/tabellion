<?php

require_once('../Commun/Identification.php');
require_once('../Commun/commun.php');
require_once('../Commun/constantes.php');
require_once('../Commun/config.php');
require_once('../Commun/ConnexionBD.php');
require_once('../RequeteRecherche.php');
require_once('../Commun/PaginationTableau.php');
require_once('../Commun/Benchmark.inc');


// La page est reservee uniquement aux gens ayant les droits d'import/export
require_once('../Commun/VerificationDroits.php');
verifie_privilege(DROIT_NOTAIRES);

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

$requeteRecherche = new RequeteRecherche($connexionBD);    
$a_liasses=$connexionBD->sql_select_multiple($_SESSION['pdf']['requete']);

require('../Publication/fpdf/fpdf.php');

class PDF extends FPDF
{
// En-tête
function Header() {
    // Logo
    $this->Image('../images/LogoAGC.jpg',10,10,20);
    // Police Arial gras 15
    $this->SetFont('Arial','B',12);
    // Décalage à droite
    $this->Cell(80);
    // Titre
    $this->Cell(30,8,utf8_vers_cp1252($_SESSION['pdf']['titre']),0,1,'C');
    $this->SetFont('Arial','B',10);
    $this->Cell(80);
    $this->Cell(30,6,utf8_vers_cp1252($_SESSION['pdf']['sous_titre']),0,0,'C');
   // Saut de ligne
    $this->Ln(20);
    $this->SetFont('Times','B',7);
	$this->SetFillColor(200,200,200);
	switch($_SESSION['menu_rla']) {
		case 'publication' :
			$this->Cell(120,8,'Titre publication papier',1,0,'L',true);
			$this->Cell(15,8,'Date',1,0,'C',true);
			$this->Cell(137,8,utf8_vers_cp1252('Informations complémentaires'),1,1,'L',true);
			break;
		case 'publi_pap' :
			$this->Cell(100,6,$_SESSION['pdf']['nb_liasse'].' liasses',0, 0,'L',true);
			$this->Cell(72,6,$_SESSION['pdf']['pourc_liste'].' % de la liste',0,0,'C',true);
			$this->Cell(100,6,$_SESSION['pdf']['pourc_tot'].utf8_vers_cp1252(' % de la série'),0,1,'R',true);			
			$this->Cell(120,8,'Titre publication papier',1,0,'L',true);
			$this->Cell(15,8,'Date',1,0,'C',true);
			$this->Cell(17,8,'Cote',1,0,'C',true);
			$this->Cell(60,8,'Notaire(commune)',1,0,'L',true);
			$this->Cell(40,8,utf8_vers_cp1252('Période'),1,0,'L',true);
			$this->Cell(20,8,'Forme liasse',1,1,'L',true);
			break;
		case 'program' :
			$this->Cell(100,6,$_SESSION['pdf']['nb_liasse'].' liasses',0, 0,'L',true);
			$this->Cell(77,6,$_SESSION['pdf']['pourc_liste'].' % de la liste',0,0,'C',true);
			$this->Cell(100,6,$_SESSION['pdf']['pourc_tot'].utf8_vers_cp1252(' % de la série'),0,1,'R',true);			
			$this->Cell(15,8,'Cote',1,0,'C',true);
			$this->Cell(80,8,'Notaire(commune)',1,0,'L',true);
			$this->Cell(60,8,utf8_vers_cp1252('Période'),1,0,'L',true);
			$this->Cell(30,8,'Forme liasse',1,0,'L',true);
			$this->Cell(30,8,'Intervenant',1,0,'L',true);
			$this->Cell(17,8,utf8_vers_cp1252('Priorité'),1,0,'L',true);
			$this->Cell(15,8,utf8_vers_cp1252('Echéance'),1,0,'C',true);
			$this->Cell(15,8,utf8_vers_cp1252('Prog. relevé'),1,0,'C',true);
			$this->Cell(15,8,'Prog. photo',1,1,'C',true);
			break;
		case 'releve' :
			$this->Cell(100,6,$_SESSION['pdf']['nb_liasse'].' liasses',0, 0,'L',true);
			$this->Cell(80,6,$_SESSION['pdf']['pourc_liste'].' % de la liste',0,0,'C',true);
			$this->Cell(100,6,$_SESSION['pdf']['pourc_tot'].utf8_vers_cp1252(' % de la série'),0,1,'R',true);			
			$this->Cell(17,8,'Cote',1,0,'C',true);
			$this->Cell(80,8,'Notaire(commune)',1,0,'L',true);
			$this->Cell(50,8,utf8_vers_cp1252('Période'),1,0,'L',true);
			$this->Cell(23,8,'Forme liasse',1,0,'L',true);
			$this->Cell(15,8,'Consultable',1,0,'C',true);
			$this->Cell(50,8,'Releveur',1,0,'C',true);
			$this->Cell(15,8,'Papier',1,0,'C',true);
			$this->Cell(15,8,utf8_vers_cp1252('Numérique'),1,0,'C',true);
			$this->Cell(15,8,utf8_vers_cp1252('Date relevé'),1,1,'C',true);
			break;
		case 'publi_num' :
			$this->Cell(100,6,$_SESSION['pdf']['nb_liasse'].' liasses',0, 0,'L',true);
			$this->Cell(50,6,$_SESSION['pdf']['pourc_liste'].' % de la liste',0,0,'C',true);
			$this->Cell(100,6,$_SESSION['pdf']['pourc_tot'].utf8_vers_cp1252(' % de la série'),0,1,'R',true);			
			$this->Cell(17,8,'Cote',1,0,'C',true);
			$this->Cell(80,8,'Notaire(commune)',1,0,'L',true);
			$this->Cell(50,8,utf8_vers_cp1252('Période'),1,0,'L',true);
			$this->Cell(23,8,'Forme liasse',1,0,'L',true);
			$this->Cell(15,8,'Consultable',1,0,'C',true);
			$this->Cell(50,8,'Releveur',1,0,'C',true);
			$this->Cell(15,8,utf8_vers_cp1252('Date relevé'),1,1,'C',true);
			break;
		case 'photo' :
			if( $_SESSION['avec_commentaire_rla'] != 'oui' ) {
				$this->Cell(100,6,$_SESSION['pdf']['nb_liasse'].' liasses',0, 0,'L',true);
				$this->Cell(80,6,$_SESSION['pdf']['pourc_liste'].' % de la liste',0,0,'C',true);
				$this->Cell(100,6,$_SESSION['pdf']['pourc_tot'].utf8_vers_cp1252(' % de la série'),0,1,'R',true);			
				$this->Cell(15,8,'Cote',1,0,'C',true);
				$this->Cell(70,8,'Notaire(commune)',1,0,'L',true);
				$this->Cell(46,8,utf8_vers_cp1252('Période'),1,0,'L',true);
				$this->Cell(17,8,'Forme liasse',1,0,'L',true);
				$this->Cell(14,8,'Consultable',1,0,'C',true);
				$this->Cell(9,8,'Papier',1,0,'C',true);
				$this->Cell(13,8,utf8_vers_cp1252('Numérique'),1,0,'C',true);
				$this->Cell(35,8,'Photographe',1,0,'L',true);
				$this->Cell(15,8,'Date photo',1,0,'C',true);
				$this->Cell(26,8,'Couverture',1,0,'L',true);
				$this->Cell(20,8,'Codification',1,1,'L',true);
			}
			else {
				$this->Cell(100,6,$_SESSION['pdf']['nb_liasse'].' liasses',0, 0,'L',true);
				$this->Cell(80,6,$_SESSION['pdf']['pourc_liste'].' % de la liste',0,0,'C',true);
				$this->Cell(100,6,$_SESSION['pdf']['pourc_tot'].utf8_vers_cp1252(' % de la série'),0,1,'R',true);			
				$this->Cell(15,8,'Cote',1,0,'C',true);
				$this->Cell(70,8,'Notaire(commune)',1,0,'L',true);
				$this->Cell(46,8,utf8_vers_cp1252('Période'),1,0,'L',true);
				$this->Cell(17,8,'Forme liasse',1,0,'L',true);
				$this->Cell(26,8,'Couverture',1,0,'L',true);
				$this->Cell(106,8,'Commentaires',1,1,'L',true);
			}
			break;
		case 'pas_releve' :
		case 'pas_photo':
		default :
			$this->Cell(61,6,$_SESSION['pdf']['nb_liasse'].' liasses',0, 0,'L',true);
			$this->Cell(65,6,$_SESSION['pdf']['pourc_liste'].' % de la liste',0,0,'C',true);
			$this->Cell(61,6,$_SESSION['pdf']['pourc_tot'].utf8_vers_cp1252(' % de la série'),0,1,'R',true);			
			$this->Cell(17,8,'Cote',1,0,'C',true);
			$this->Cell(80,8,'Notaire(commune)',1,0,'L',true);
			$this->Cell(60,8,utf8_vers_cp1252('Période'),1,0,'L',true);
			$this->Cell(30,8,'Forme liasse',1,1,'L',true);
			break;
	}
}

function Ligne_releve($pa_liasse, $fond) {
    $this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_publi_pap, $st_publi_num, $st_date_fin_releve, $st_info_compl) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 55 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 52)."...";
	if( strlen($st_libelle_annees) > 30 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 27)."...";
	if( strlen($st_forme) > 18 ) 
		$st_forme = substr($st_forme, 0, 15)."...";
	$this->Cell(17,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(80,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(50,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(23,5,$st_forme,1,0,'L',$fond);
	$this->Cell(15,5,$st_consult,1,0,'C',$fond);
	$this->Cell(50,5,$st_releveur,1,0,'L',$fond);
	$this->Cell(15,5,$st_publi_pap,1,0,'C',$fond);
	$this->Cell(15,5,$st_publi_num,1,0,'C',$fond);
	$this->Cell(15,5,$st_date_fin_releve,1,1,'C',$fond);
}

function Ligne_publi_num($pa_liasse, $fond) {
    $this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_date_fin_releve) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 55 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 52)."...";
	if( strlen($st_libelle_annees) > 30 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 27)."...";
	if( strlen($st_forme) > 18 ) 
		$st_forme = substr($st_forme, 0, 15)."...";
	$this->Cell(17,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(80,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(50,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(23,5,$st_forme,1,0,'L',$fond);
	$this->Cell(15,5,$st_consult,1,0,'C',$fond);
	$this->Cell(50,5,$st_releveur,1,0,'L',$fond);
	$this->Cell(15,5,$st_date_fin_releve,1,1,'C',$fond);
}

function Ligne_publication($pa_liasse, $fond) {
   $this->SetFont('Times','',8);
	$this->SetFillColor(150,150,150);
	list($st_titre, $st_date_publication, $st_info_compl) = $pa_liasse;
	if( strlen($st_titre) > 95 ) 
		$st_titre = substr($st_titre, 0, 94)."...";
	if( strlen($st_info_compl) > 130 ) 
		$st_info_compl = substr($st_info_compl, 0, 127)."...";
	$this->Cell(120,5,$st_titre,1,0,'L',$fond);
	$this->Cell(15,5,$st_date_publication,1,0,'C',$fond);
	$this->Cell(137,5,$st_info_compl,1,1,'L',$fond);
}

function Ligne_publi_pap($pa_liasse, $fond) {
   $this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_titre, $st_date_publication, $st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme) = $pa_liasse;
	if( strlen($st_titre) > 95 ) 
		$st_titre = substr($st_titre, 0, 94)."...";
	if( strlen($st_libelle_notaires) > 40 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 37)."...";
	if( strlen($st_libelle_annees) > 30 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 27)."...";
	if( strlen($st_forme) > 17 ) 
		$st_forme = substr($st_forme, 0, 14)."...";
	$this->Cell(120,5,$st_titre,1,0,'L',$fond);
	$this->Cell(15,5,$st_date_publication,1,0,'C',$fond);
	$this->Cell(17,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(60,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(40,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(20,5,$st_forme,1,1,'L',$fond);
}

function Ligne_program($pa_liasse, $fond) {
	$this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_intervenant, $st_priorite, 
	     $st_date_echeance, $st_program_releve, $st_program_photo) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 50 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 47)."...";
	if( strlen($st_libelle_annees) > 45 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 42)."...";
	$this->Cell(15,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(80,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(60,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(30,5,$st_forme,1,0,'L',$fond);
	$this->Cell(30,5,$st_intervenant,1,0,'L',$fond);
	$this->Cell(17,5,$st_priorite,1,0,'L',$fond);
	$this->Cell(15,5,$st_date_echeance,1,0,'C',$fond);
	$this->Cell(15,5,$st_program_releve,1,0,'C',$fond);
	$this->Cell(15,5,$st_program_photo,1,1,'C',$fond);
}

function Ligne_pas_releve($pa_liasse, $fond) {
	$this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_publi_num, $st_date_fin_releve) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 50 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 47)."...";
	if( strlen($st_libelle_annees) > 45 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 42)."...";
	if( strlen($st_forme) > 20 ) 
		$st_forme = substr($st_forme, 0, 17)."...";
	$this->Cell(17,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(80,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(60,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(30,5,$st_forme,1,1,'L',$fond);
}

function Ligne_photo_sans($pa_liasse, $fond) {
	$this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_publi_pap, $st_publi_num, $st_photographe, $st_date_photo, $st_couverture_photo, $st_codif_photo) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 40 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 37)."...";
	if( strlen($st_libelle_annees) > 30 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 27)."...";
	if( strlen($st_photographe) > 25 ) 
		$st_photographe = substr($st_photographe, 0, 22)."...";
	$this->Cell(15,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(70,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(46,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(17,5,$st_forme,1,0,'L',$fond);
	$this->Cell(14,5,$st_consult,1,0,'C',$fond);
	$this->Cell(9,5,$st_publi_pap,1,0,'C',$fond);
	$this->Cell(13,5,$st_publi_num,1,0,'C',$fond);
	$this->Cell(35,5,$st_photographe,1,0,'L',$fond);
	$this->Cell(15,5,$st_date_photo,1,0,'C',$fond);
	$this->Cell(26,5,$st_couverture_photo,1,0,'L',$fond);
	$this->Cell(20,5,$st_codif_photo,1,1,'L',$fond);
}

function Ligne_photo_avec($pa_liasse, $fond) {
	$this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_couverture_photo, $st_info_compl) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 40 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 37)."...";
	if( strlen($st_libelle_annees) > 30 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 27)."...";
	if( strlen($st_info_compl) > 130 ) 
		$st_info_compl = substr($st_info_compl, 0, 128)."...";
	$this->Cell(15,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(70,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(46,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(17,5,$st_forme,1,0,'L',$fond);
	$this->Cell(26,5,$st_couverture_photo,1,0,'L',$fond);
	$this->Cell(106,5,$st_info_compl,1,1,'L',$fond);
}

function Ligne_pas_photo($pa_liasse, $fond) {
	$this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_photographe, $st_date_photo, $st_couverture_photo, $st_codif_photo) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 50 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 47)."...";
	$this->Cell(17,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(80,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(60,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(30,5,$st_forme,1,1,'L',$fond);
}

function Ligne_defaut($pa_liasse, $fond) {
    $this->SetFont('Times','',8);
	$this->SetFillColor(240,240,240);
	list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme) = $pa_liasse;
	if( strlen($st_libelle_notaires) > 50 ) 
		$st_libelle_notaires = substr($st_libelle_notaires, 0, 47)."...";
	if( strlen($st_libelle_annees) > 45 ) 
		$st_libelle_annees = substr($st_libelle_annees, 0, 42)."...";
	$this->Cell(17,5,$st_cote_liasse,1,0,'C',$fond);
	$this->Cell(80,5,$st_libelle_notaires,1,0,'L',$fond);
	$this->Cell(60,5,$st_libelle_annees,1,0,'L',$fond);
	$this->Cell(30,5,$st_forme,1,1,'L',$fond);
}

// Pied de page
function Footer() {
    // Positionnement à 1,5 cm du bas
    $this->SetY(-15);
    // Police Arial italique 8
    $this->SetFont('Arial','I',8);
    // Numéro de page
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}

// Instanciation de la classe dérivée
$pdf = new PDF();
$pdf->AliasNbPages();
if( $_SESSION['menu_rla'] == 'photo' || $_SESSION['menu_rla'] == 'publi_pap' || $_SESSION['menu_rla'] == 'releve' || 
    $_SESSION['menu_rla'] == 'publi_num' || $_SESSION['menu_rla'] == 'program' || $_SESSION['menu_rla'] == 'publication' )
	$pdf->AddPage('L');
else
	$pdf->AddPage();

$pdf->SetFont('Times','',12);

$fond = false;

switch($_SESSION['menu_rla']) {
	case 'publication' :
		foreach ($a_liasses as $a_liasse) {
			$pdf->Ligne_publication($a_liasse, $fond);			
			$fond = ( $fond ) ? false : true;
		}
		break;
	case 'publi_pap' :
		foreach ($a_liasses as $a_liasse) {
			$pdf->Ligne_publi_pap($a_liasse, $fond);			
			$fond = ( $fond ) ? false : true;
		}
		break;
	case 'program' :
		foreach ($a_liasses as $a_liasse) {
			$pdf->Ligne_program($a_liasse, $fond);
			$fond = ( $fond ) ? false : true;
		}
		break;
	case 'releve' :
		foreach ($a_liasses as $a_liasse) {
			$pdf->Ligne_releve($a_liasse, $fond);
			$fond = ( $fond ) ? false : true;
		}
		break;
	case 'publi_num' :
		foreach ($a_liasses as $a_liasse) {
			$pdf->Ligne_publi_num($a_liasse, $fond);
			$fond = ( $fond ) ? false : true;
		}
		break;		
	case 'pas_releve' :
		foreach ($a_liasses as $a_liasse) {
			$pdf->Ligne_pas_releve($a_liasse, $fond);
			$fond = ( $fond ) ? false : true;
		}
		break;
	case 'photo' :
		foreach ($a_liasses as $a_liasse) { 
			if( $_SESSION['avec_commentaire_rla'] != 'oui' ) 
				$pdf->Ligne_photo_sans($a_liasse, $fond);
			else
				$pdf->Ligne_photo_avec($a_liasse, $fond);
			$fond = ( $fond ) ? false : true;
		}
		break;
	case 'pas_photo' :
		foreach ($a_liasses as $a_liasse) { 
			$pdf->Ligne_pas_photo($a_liasse, $fond);
			$fond = ( $fond ) ? false : true;
		}
		break;
	default :
		foreach ($a_liasses as $a_liasse) { 
			$pdf->Ligne_defaut($a_liasse, $fond);
			$fond = ( $fond ) ? false : true;
		}
		break;
}

$pdf->Output();
?>