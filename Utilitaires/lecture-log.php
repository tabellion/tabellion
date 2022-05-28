<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_UTILITAIRES);
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Origin/PaginationTableau.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');

/**
 * Affiche le menu formulaire
 * @param array Liste des adhérents
 * @param integer Identifiant d'un adhérent 
 */
function affiche_formulaire($pa_adherents, $pi_idf_adherent)
{
	print("<form method=post>");
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading">Affichage des recherches d\'un adh&eacute;rent </div>');
	print('<div class="panel-body">');
	print('<label for="idf_adherent" class="col-form-label col-md-2">Adh&eacute;rent:</label>');
	print('<div class="col-md-10">');
	print('<select name="idf_adherent" id="idf_adherent" class="form-control">');
	print(chaine_select_options($pi_idf_adherent, $pa_adherents));
	print('</select>');
	print('</div>');

	print('<label for="idf_adherent" class="col-form-label col-md-2">S&eacute;lection du fichier pour la recherche</label>');
	print('<div class="col-md-10">');
	print('<select name="choix_log" id="choix_log" class="form-control">');
	print('<option value=1>Requ&ecirc;te recherche sur une personne </option>');
	print('<option value=2>Requ&ecirc;te recherche sur couple </option>');
	print('<option value=3>Requ&ecirc;te recherche sur les d&eacute;c&egrave;s </option>');
	print('<option value=4>Requ&ecirc;te recherche sur les naissances </option>');
	print('<option value=5>Requ&ecirc;te recherche sur les dépouillements </option>');
	print('<option value=6>Requ&ecirc;te recherche sur les liasses</option>');
	print('<option value=7>Requ&ecirc;te recherche sur les r&eacute;pertoires</option>');
	print("</select>");
	print('</div>');

	print("<div>");
	print('<label for="libre" class="col-form-label col-md-2">Recherche libre dans un des fichiers :</label>');
	print('<div class="col-md-10">');
	print('<input type="text" name="libre" id="libre" class="form-control">');
	print('</div>');

	print("</div>");
	print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Valider</button></div>');
	print("</form>");
	print("</div></div>");
}

$ga_fichiers_logs = array(
	1 => array('requetes_personne.log', 5, array('Date', 'Ident', 'IP', 'Nom', 'Prenom', 'Commune', 'Rayon', 'Année Min', 'Année Max', 'Commentaires')),
	2 => array('requetes_couple.log', 7, array('Date', 'Ident', 'IP', 'Nom Epx', 'Prenom Epx', 'Nom Epse', 'Prenom Epse', 'Commune', 'Rayon', 'Année Min', 'Année Max')),
	3 => array('requetes_deces.log', 3, array('Date', 'Ident', 'IP', 'Commune')),
	4 => array('requetes_naissances.log', 3, array('Date', 'Ident', 'IP', 'Commune')),
	5 => array('requetes_depouillements.log', null, array('Date', 'Ident', 'IP', 'Commune', 'Type d\'acte')),
	6 => array('requetes_liasse.log', 8, array('Date', 'Ident', 'IP', 'Nom notaire', 'Prenom Notaire', 'Serie', 'Cote Debut', 'Cote Fin', 'Commune', 'Rayon', 'Année Min', 'Année Max')),
	7 => array('requetes_rep_not.log', 3, array('Date', 'Ident', 'IP', 'Commune', 'Rayon', 'Idf Rep', 'Type acte', 'Année Min', 'Année Max', 'Nom 1', 'Prenom 1', 'Nom 2', 'Prenom 2', 'Paroisse'))
);


/******************************************************************************/
/*                         Corps du programme                                 */
/******************************************************************************/
print("<body>");
print('<div class="container">');
$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);
require_once __DIR__ . '/../Commun/menu.php';

$ga_adherents = $connexionBD->liste_valeur_par_clef("select idf,concat(nom,'  ',prenom,' (',idf,')') from adherent order by nom,prenom");

if (!isset($_POST['idf_adherent'])) {
	$i_session_idf_adherent = isset($_SESSION['idf_adherent']) ? $_SESSION['idf_adherent'] : null;
	affiche_formulaire($ga_adherents, $i_session_idf_adherent);
} else {
	$gi_idf_adherent = isset($_POST['idf_adherent']) ? (int) $_POST['idf_adherent'] : 0;
	$_SESSION['idf_adherent'] = $gi_idf_adherent;
	$a_communes_acte = $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM commune_acte");
	if (isset($_POST['choix_log'])) {
		$i_idf_choix = (int) $_POST['choix_log'];
		$st_libre = empty($_POST['libre']) ? '' : $_POST['libre'];
		switch ($i_idf_choix) {
			case 4:
				$st_requete = "select date_demande,concat(a.prenom,' ',a.nom) ,adresse_ip,ca.nom from `demandes_adherent` da join adherent a  on (da.idf_adherent=a.idf) join commune_acte ca on (da.idf_commune=ca.idf) where da.idf_adherent=$gi_idf_adherent and da.idf_type_acte=" . IDF_NAISSANCE;
				if (!empty($st_libre))
					$st_requete .=  " and (adresse_ip like '%$st_libre%' or ca.nom like '%$st_libre%')";
				$st_requete .= " order by date_demande";
				$a_ddes = $connexionBD->sql_select_multiple($st_requete);
				$pagination = new PaginationTableau(basename(__FILE__), 'num_page', count($a_ddes), NB_LIGNES_PAR_PAGE, 1, array('Date', 'Adherent', 'IP', 'Commune'));
				$pagination->affiche_tableau_simple($a_ddes);
				break;
			case 3:
				$st_requete = "select date_demande,concat(a.prenom,' ',a.nom) ,adresse_ip,ca.nom from `demandes_adherent` da join adherent a  on (da.idf_adherent=a.idf) join commune_acte ca on (da.idf_commune=ca.idf) where da.idf_adherent=$gi_idf_adherent and da.idf_type_acte=" . IDF_DECES;
				if (!empty($st_libre))
					$st_requete .=  " and (adresse_ip like '%$st_libre%' or ca.nom like '%$st_libre%')";
				$st_requete .= " order by date_demande";
				$a_ddes = $connexionBD->sql_select_multiple($st_requete);
				$pagination = new PaginationTableau(basename(__FILE__), 'num_page', count($a_ddes), NB_LIGNES_PAR_PAGE, 1, array('Date', 'Adherent', 'IP', 'Commune'));
				$pagination->affiche_tableau_simple($a_ddes);
				break;
			default:
				$st_ident =  $connexionBD->sql_select1("select ident from adherent where idf=$gi_idf_adherent");
				if (array_key_exists($i_idf_choix, $ga_fichiers_logs)) {
					$fichier_log = sprintf("%s/%s", $gst_rep_logs, $ga_fichiers_logs[$i_idf_choix][0]);
					$i_col_paroisse = $ga_fichiers_logs[$i_idf_choix][1];
					$a_entete = $ga_fichiers_logs[$i_idf_choix][2];
					$resultats = array();
					$fp = @fopen($fichier_log, 'r') or die("Ouverture en lecture de \"$fichier_log\" impossible !");
					$nom = empty($_POST['idf_adherent']) ? '' : (int) $_POST['idf_adherent'];
					while (!feof($fp)) {
						$ligne = fgets($fp, 1024);
						if (empty($st_libre)) {
							if (preg_match('|\b' . ';' . $st_ident . ';' . '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}' . '\b|i', $ligne)) {
								$resultats[] = $ligne;
							}
						} else {
							if (preg_match('|\b' . ';' . $st_libre . '\b|i', $ligne)) {
								$resultats[] = $ligne;
							}
						}
					}
					fclose($fp);
					$nb = count($resultats);
					if ($nb > 0) {
						if (empty($st_libre))
							print("<div class=\"alert alert-info\">L'adh&eacute;rent $gi_idf_adherent a fait $nb recherche(s):</div>");
						else
							print("<div class=\"alert alert-info\">Recherche sur: $st_libre</div>");
						print('<table class="table table-bordered table-striped">');
						print('<tr>');
						foreach ($a_entete as $st_entete) {
							print("<th>$st_entete</th>");
						}
						print('</tr>');
						foreach ($resultats as $v) {
							$a_champs = explode(';', trim($v));
							print('<tr>');
							$i = 0;
							foreach ($a_champs as $st_champ) {
								if ($i == 1) {
									$st_adherent = array_key_exists($st_champ, $ga_adherents) ? $ga_adherents[$st_champ] : '';
									print("<td>$st_adherent</td>");
								} else {
									if (!is_null($i_col_paroisse) && $i == $i_col_paroisse) {
										$st_paroisse = array_key_exists($st_champ, $a_communes_acte) ? $a_communes_acte[$st_champ] : '';
										print("<td>$st_paroisse</td>");
									} else
										print("<td>$st_champ</td>");
								}
								$i++;
							}
							print("</tr>\n");
						}
						print('</table></div>');
					} else {
						if (empty($st_libre))
							print('<div class="alert alert-danger">L\'adh&eacute;rent n\'a pas fait de recherche !</div>');
						else
							print('<div class="alert alert-danger">Pas de r&eacute;sultat</div>');
					}
				} else {
					print("<div class=\"alert alert-danger\">Choix inconnu: $i_idf_choix</div>");
				}
		}
	} else {
		print("<div class=\"alert alert-danger\">Choix vide</div>");
	}
	print("<form method=post>");
	print("<input type=hidden name=mode value=FORMULAIRE>");
	print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Retour</button></div>');
	print("</form>");
}
print("</div></body></html>");
