<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association G�n�alogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique G�n�rale GPL GNU publi�e par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_UTILITAIRES);
require_once __DIR__ . '/../Commun/ConnexionBD.php';


$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$gst_mode = empty($_POST['mode']) ? 'FORMULAIRE' : $_POST['mode'];

if ($gst_mode == 'COMPARAISON') {
	$st_fich_dest = __DIR__ . '/../storage/telechargement/adhts_cgcp';
	if (!move_uploaded_file($_FILES['AdhtsCGCP']['tmp_name'], $st_fich_dest)) {
		print("<div class=\"alert alert-danger\">Erreur de t&eacute;l&eacute;chargement :</div>");
		switch ($_FILES['AdhtsCGCP']['error']) {
			case 2:
				print("<div class='alert'Fichier trop gros par rapport &agrave; MAX_FILE_SIZE</div>");
				break;
			default:
				print("<div class='alert'Erreur inconnue</div>");
				print_r($_FILES);
		}
		exit;
	}
	if (($pf = fopen($st_fich_dest, "r")) !== FALSE) {
		$ga_adht_cgcp = array();
		while (($a_tmp = fgetcsv($pf, 1000, ";")) !== FALSE) {
			switch (count($a_tmp)) {
				case 7:
					list($i_idf_adht, $i_annee_inscription, $st_nom_adht, $st_email_adht, $i_annee_cotisation, $st_derniere_cnx, $i_absence) = $a_tmp;
					$i_age = null;
					break;
				case 8:
					list($i_idf_adht, $i_annee_inscription, $st_nom_adht, $i_age, $st_email_adht, $i_annee_cotisation, $st_derniere_cnx, $i_absence) = $a_tmp;
					break;
				default:
					die("<div class='alert'>Le fichier ne comporte pas 7 ou 8 colonnes</div>");
			}
			$ga_adht_cgcp[$i_idf_adht] = array($i_annee_inscription, $st_nom_adht, $i_age, $st_email_adht, $i_annee_cotisation, $st_derniere_cnx, $i_absence);
		}
		fclose($pf);
	}
	header("Content-type: text/csv");
	header("Expires: 0");
	header("Pragma: public");
	header("Content-disposition: attachment; filename=\"adhts_cgcp.csv\"");
	$fh = @fopen('php://output', 'w');
	fputcsv($fh, array('Ann�e inscription CGCP', 'Adht', 'Age', 'Email', 'Ann�e cotisation CGCP', 'Derni�re connexion CGCP', 'Absence CGCP (en J)', 'Statut AGC', 'Derni�re connexion AGC', 'Absence AGC (en J)', 'Ann�e cotisation AGC'), ';');
	foreach ($ga_adht_cgcp as $i_idf_adht => $a_adht) {
		list($i_annee_inscription_cgcp, $st_nom_adht, $i_age, $st_email_adht, $i_annee_cotisation_cgcp, $st_derniere_cnx_cgcp, $i_absence_cgcp) = $a_adht;
		$st_requete = "select statut,date(derniere_connexion),datediff(now(),derniere_connexion),annee_cotisation from adherent where email_forum='$st_email_adht' or email_perso='$st_email_adht'";
		$st_statut_agc =  '';
		$st_derniere_cnx_agc = '';
		list($st_statut_agc, $st_derniere_cnx_agc, $i_absence_agc, $i_annee_cotisation_agc) = $connexionBD->sql_select_liste($st_requete);
		fputcsv($fh, array($i_annee_inscription_cgcp, $st_nom_adht, $i_age, $st_email_adht, $i_annee_cotisation_cgcp, $st_derniere_cnx_cgcp, $i_absence_cgcp, $st_statut_agc, $st_derniere_cnx_agc, $i_absence_agc, $i_annee_cotisation_agc), ';');
	}
	fclose($fh);
	unlink($st_fich_dest);
	exit();
}


/**
 * Affiche le menu de selection
 * @global $gi_max_taille_upload Maximum de la taille  
 */
function affiche_menu()
{
	global $gi_max_taille_upload;
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading">Chargement des adh&eacute;rents du CGCP</div>');
	print('<div class="panel-body">');
	print("<form enctype=\"multipart/form-data\"  method=\"post\" >");
	print("<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"$gi_max_taille_upload\">");
	print('<input type="hidden" name="mode" value="COMPARAISON" >');
	print('<div class="custom-file">');
	print('<label for="AdhtsCGCP" class="custom-file-label">Fichier <span class="alert alert-danger">CSV</span> des adh&eacute;rents du CGCP:</label>');
	print('<input name="AdhtsCGCP" id="AdhtsCGCP" type="file" class="custom-file-input">');
	print('</div>');
	print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Compare les adh&eacute;rents communs</button></div>');
	print('</form>');
	print('</div></div>');
}

/******************************************************************************/
/*                         Corps du programme                                 */
/******************************************************************************/
print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=windows-1252" >');
print('<meta http-equiv="content-language" content="fr">');
print("<title>Comparaison des adherents CGCP</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');
print('<body>');
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

if ($gst_mode = 'FORMULAIRE')
	affiche_menu();
print('</div></body>');
print('</html>');
