<?php

require_once('../Commun/Identification.php');
require_once('../Commun/commun.php');
require_once('../Commun/constantes.php');
require_once('../Commun/config.php');
require_once('../Commun/ConnexionBD.php');
require_once('../RequeteRecherche.php');
require_once('../Commun/VerificationDroits.php');
  
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

$requeteRecherche = new RequeteRecherche($connexionBD);    
$a_liasses=$connexionBD->sql_select_multiple($_SESSION['pdf']['requete']);

$csv  = $_SESSION['pdf']['titre']."\n";
$csv .= $_SESSION['pdf']['sous_titre']."\n";
$csv = utf8_vers_cp1252($csv);
if( $_SESSION['menu_rla'] != 'publication' ) 
	$csv .= $_SESSION['pdf']['nb_liasse']." liasses;".$_SESSION['pdf']['pourc_liste']." % de la liste;".$_SESSION['pdf']['pourc_tot']." % de la série\n";
	
switch($_SESSION['menu_rla']) {
	case 'publication' :
		$csv .= "Titre publication papier;Date;Informations complémentaires\n";
		foreach ($a_liasses as $a_liasse) {
			list($st_titre, $st_date_publication, $st_info_compl) = $a_liasse;
			$st_titre = str_replace(";", "-", $st_titre);
			$st_titre = str_replace(chr(10), " ", $st_titre);
			$st_titre = str_replace(chr(13), " ", $st_titre);
			$st_info_compl = str_replace(";", "-", $st_info_compl);
			$st_info_compl = str_replace(chr(10), " ", $st_info_compl);
			$st_info_compl = str_replace(chr(13), " ", $st_info_compl);
			$csv .= $st_titre.";".$st_date_publication.";".$st_info_compl."\n";
		}
		break;
	case 'publi_pap' :
		$csv .= "Titre publication papier;Date;Cote;Notaire(commune);Période;Forme liasse\n";
		foreach ($a_liasses as $a_liasse) {
			list($st_titre, $st_date_publication, $st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme) = $a_liasse;
			$st_titre = str_replace(";", "-", $st_titre);
			$st_titre = str_replace(chr(10), " ", $st_titre);
			$st_titre = str_replace(chr(13), " ", $st_titre);
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$csv .= $st_titre.";".$st_date_publication.";'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme."\n";
		}
		break;
	case 'program' :
		$csv .= "Cote;Notaire(commune);Période;Forme liasse;Intervenant;Priorité;Echéance;Prog. relevé;Prog. photo\n";
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_intervenant, $st_priorite, 
				$st_date_echeance, $st_program_releve, $st_program_photo) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme.";".$st_intervenant.";".$st_priorite.";".
					$st_date_echeance.";".$st_program_releve.";".$st_program_photo."\n";
		}
		break;
	case 'releve' :
		$csv .= "Cote;Notaire(commune);Période;Forme liasse;Consultable;Releveur;Papier;Numérique;Date relevé;Infos relevé\n";
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_publi_pap, $st_publi_num, 
			     $st_date_fin_releve, $st_info_compl) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$st_info_compl = str_replace(";", "-", $st_info_compl);
			$st_info_compl = str_replace(chr(10), " ", $st_info_compl);
			$st_info_compl = str_replace(chr(13), " ", $st_info_compl);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme.";".$st_consult.";".$st_releveur.";".
			        $st_publi_pap.";".$st_publi_num.";".$st_date_fin_releve.";".$st_info_compl."\n";
		}
		break;
	case 'complete' :
		$csv .= "Cote;Notaire;Commune;Période;Forme liasse;Consultable;Infos liasse;Relevé;Infos relevé\n";
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_notaires, $st_commune_etude, $st_libelle_annees, $st_forme, $st_consult, $st_info_liasse, $st_releve, $st_info_releve) = $a_liasse;
			$st_notaires = str_replace(";", "-", $st_notaires);
			$st_commune_etude = str_replace(";", "-", $st_commune_etude);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$st_info_liasse = str_replace(";", "-", $st_info_liasse);
			$st_info_liasse = str_replace(chr(10), " ", $st_info_liasse);
			$st_info_liasse = str_replace(chr(13), " ", $st_info_liasse);
			$st_info_releve = str_replace(";", "-", $st_info_releve);
			$st_info_releve = str_replace(chr(10), " ", $st_info_releve);
			$st_info_releve = str_replace(chr(13), " ", $st_info_releve);
			$csv .= "'".$st_cote_liasse.";".$st_notaires.";".$st_commune_etude.";".$st_libelle_annees.";".$st_forme.";".$st_consult.";".$st_info_liasse.";".$st_releve.";".$st_info_releve."\n";
		}
		break;
	case 'publi_num' :
		$csv .= "Cote;Notaire(commune);Période;Forme liasse;Consultable;Releveur;Date relevé\n";
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_date_fin_releve) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme.";".$st_consult.";".$st_releveur.";".$st_date_fin_releve."\n";
		}
		break;
	case 'photo' :
		if( $_SESSION['avec_commentaire_rla'] != 'oui' ) {
			$csv .= "Cote;Notaire(commune);Période;Forme liasse;Consultable;Papier;Numérique;Photographe;Date photo;Couverture;Codification\n";
			foreach ($a_liasses as $a_liasse) { 
				list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_publi_pap, $st_publi_num, $st_photographe, $st_date_photo, $st_couverture_photo, $st_codif_photo) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme.";".$st_consult.";".$st_publi_pap.";".$st_publi_num.";".$st_photographe.";".
						$st_date_photo.";".$st_couverture_photo.";".$st_codif_photo."\n";
			}
		}
		else {
			$csv .= "Cote;Notaire(commune);Période;Forme liasse;Couverture;Commentaires\n";
			foreach ($a_liasses as $a_liasse) { 
				list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_couverture_photo, $st_info_compl) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$st_info_compl = str_replace(";", "-", $st_info_compl);
			$st_info_compl = str_replace(chr(10), " ", $st_info_compl);
			$st_info_compl = str_replace(chr(13), " ", $st_info_compl);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme.";".$st_couverture_photo.";".$st_info_compl."\n";
			}
		}
		break;
	case 'pas_releve' :
		$csv .= "Cote;Notaire(commune);Période;Forme liasse\n";
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_publi_num, $st_date_fin_releve) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme."\n";
		}
		break;
	case 'pas_photo':
		$csv .= "Cote;Notaire(commune);Période;Forme liasse\n";
		foreach ($a_liasses as $a_liasse) { 
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_photographe, $st_date_photo, $st_couverture_photo, $st_codif_photo) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme."\n";
		}
		break;
	default :
		$csv .= "Cote;Notaire(commune);Période;Forme liasse\n";
		foreach ($a_liasses as $a_liasse) { 
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme) = $a_liasse;
			$st_libelle_notaires = str_replace(";", "-", $st_libelle_notaires);
			$st_libelle_annees = str_replace(";", "-", $st_libelle_annees);
			$csv .= "'".$st_cote_liasse.";".$st_libelle_notaires.";".$st_libelle_annees.";".$st_forme."\n";
		}
		break;
}
header("Content-type: application/vnd.ms-excel");
header("Content-disposition: attachment; filename=export_".$_SESSION['menu_rla'].".csv");
print($csv);
exit;
?>
