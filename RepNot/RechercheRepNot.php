<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
require_once __DIR__ . '/commun_rep_not.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

/*
* Affiche la grille de recherche
* @param object $pconnexionBD connexion à la BD
* @param integer $pi_idf_commune identifiant de la commune du notaire
* @param integer $pi_rayon rayon de recherche
* @param string $pst_type_acte type d'acte
* @param integer $pi_annee_min année minimale de l'acte
* @param integer $pi_annee_max année maximale de l'acte
* @param string $pst_nom1 nom du premier intervenant
* @param string $pst_prenom1 prénom du premier intervenant
* @param string $pst_nom2 nom du second intervenant
* @param string $pst_prenom2 prénom du second intervenant
* @param string $pst_paroisse paroisse objet de l'acte (acte capitulaire)
* @param string $pst_commentaires commentaires de l'acte
* @param string $pb_rech_phonetique recherche phonetique
*/
function affiche_grille_recherche($pconnexionBD, $pi_idf_commune, $pi_rayon, $pst_type_acte, $pi_annee_min, $pi_annee_max, $pst_nom1, $pst_prenom1, $pst_nom2, $pst_prenom2, $pst_paroisse, $pst_commentaires, $pb_rech_phonetique)
{
	$st_requete = "SELECT ca.idf,ca.nom FROM commune_acte ca join rep_not_desc rnd on (ca.idf=rnd.idf_commune) where rnd.publication='O' order by ca.nom";
	$a_communes_notaires = $pconnexionBD->liste_valeur_par_clef($st_requete);
	/*$pst_nom1 = utf8_encode($pst_nom1);
  $pst_prenom1 = utf8_encode($pst_prenom1);
  $pst_nom2 = utf8_encode($pst_nom2);
  $pst_prenom2 = utf8_encode($pst_prenom2);
	*/
	print('<form id="recherche_rep_not" method="post" class="form-inline">');
	print('<input type="hidden" name="mode" value="RECHERCHES">');
	print('<input type="hidden" name="ancienne_page" value="RECHERCHES">');
	print('<div class="panel panel-info">');
	print('<div class="panel-heading">Notaire</div>');
	print('<div class="panel-body">');
	print('<div class="form-group row col-md-12">');
	print('<label for="idf_commune_notaire" class="col-form-label col-md-2">Commune</label>');
	print('<div class="col-md-2">');
	print("<select id=\"idf_commune_notaire\" name=\"idf_commune_notaire\" class=\"js-select-avec-recherche form-control\">");
	$a_communes_notaires[0] = 'Toutes';
	print(chaine_select_options($pi_idf_commune, $a_communes_notaires));
	print("</select>");
	print('</div>');
	print("<div class=\"form-group col-md-8\"><div class=\"input-group\"><span class=\"input-group-addon\">Rayon de recherche:</span><label for=\"rayon\" class=\"sr-only\">Rayon</label><input type=text name=rayon id=\"rayon\" size=2 maxlength=2 value=\"$pi_rayon\" class=\"form-control\"><span class=\"input-group-addon\">Km</span></div></div>");


	print("</div>"); // fin ligne
	print('<div class="form-group row col-md-12">');
	print('<label for="idf_rep" class="col-form-label col-md-2">R&eacute;pertoire:</label>');
	print('<div class="col-md-10">');
	print('<select id="idf_rep" name="idf_rep" class="js-select-avec-recherche form-control">');
	print("</select>");
	print('</div></div>');
	print('</div></div>'); // fin panel

	print('<div class="panel panel-info">');
	print('<div class="panel-heading">Acte</div>');
	print('<div class="panel-body">');
	print('<div class="form-group row">');
	print('<label for="type_acte" class="col-form-label col-md-2">Type d\'acte:</label>');
	print('<div class="col-md-10">');
	print("<input type=\"text\" id=\"type_acte\" name=\"type_acte\" maxlength=40 size=20 value=\"$pst_type_acte\" class=\"form-control\">");
	print('</div></div>');

	print('<div class="form-group row col-md-12">');
	print('<div class="input-group col-md-offset-4 col-md-4">');
	print("<span class=\"input-group-addon\">Ann&eacute;es de</span><input type=text name=annee_min id=\"annee_min_recherches_communes\" size=4 value=\"$pi_annee_min\" class=\"form-control\">");
	print("<span class=\"input-group-addon\">&agrave;</span><input type=text name=annee_max size=4 id=\"annee_max_recherches_communes\" value=\"$pi_annee_max\" class=\"form-control\">");
	print('</div></div>');

	print('<div class="form-group row col-md-12">');

	print("<label for=\"nom1\" class=\"col-form-label col-md-2\">Nom1:</label>");
	print('<div class="col-md-4">');
	print("<input type=\"text\" id=\"nom1\" name=\"nom1\" maxlength=40 size=20 value=\"$pst_nom1\" class=\"form-control\">");
	print('</div>');

	print("<label for=\"prenom1\" class=\"col-form-label col-md-2\">Pr&eacute;nom1:</label>");
	print('<div class="col-md-4">');
	print("<input type=\"text\" id=\"prenom1\" name=\"prenom1\" maxlength=30 size=20 value=\"$pst_prenom1\" class=\"form-control\">");
	print('</div>');

	print("</div>");

	print('<div class="form-group row col-md-12">');
	print("<label for=\"nom2\" class=\"col-form-label col-md-2\">Nom2:</label>");
	print('<div class="col-md-4">');
	print("<input type=\"text\" id=\"nom2\" name=\"nom2\" maxlength=40 size=20 value=\"$pst_nom2\" class=\"form-control\">");
	print('</div>');

	print("<label for=\"prenom2\" class=\"col-form-label col-md-2\">Pr&eacute;nom2:</label>");
	print('<div class="col-md-4">');
	print("<input type=\"text\" id=\"prenom2\" name=\"prenom2\" maxlength=30 size=20 value=\"$pst_prenom2\" class=\"form-control\">");
	print('</div>');
	print('</div>');

	print('<div class="form-group row col-md-12">');
	print('<label for="paroisse" class="col-form-label col-md-2">Paroisse concern&eacute;e par l\'acte:</label>');
	print('<div class="col-md-10">');
	print("<input type=\"text\" id=\"paroisse\" name=\"paroisse\" maxlength=40 size=20 value=\"$pst_paroisse\" class=\"form-control\">");
	print('</div>');
	print('</div>');

	print('<div class="form-group row row col-md-12">');
	print('<label for="commentaires" class="col-form-label col-md-2">Recherche libre dans un commentaire:</label>');
	print('<div class="col-md-10">');
	print("<input type=\"text\" id=\"commentaires\" name=\"commentaires\" maxlength=40 size=20 value=\"$pst_commentaires\" class=\"form-control\">");
	print('</div>');
	print('</div>');

	print('<div class="form-group row col-md-12">');
	$st_checked = $pb_rech_phonetique ? 'checked="checked"' : '';
	print('<label for="rech_phonetique" class="col-form-label col-md-2">Recherche phon&eacute;tique dans les patronymes:</label>');
	print('<div class="col-md-10">');
	print("<input type=\"checkbox\" id=\"rech_phonetique\" name=\"rech_phonetique\" $st_checked value=\"1\">");
	print('</div>');
	print('</div>');

	print('<div class="form-group row col-md-12">');
	print('<div class="text-center">Le caract&egrave;re * peut &ecirc;tre utilis&eacute; pour remplacer une partie de mot dans les champs Nom, Pr&eacute;nom et Paroisse. <br>Exemple: BAR* va chercher tous les mots commen&ccedil;ant par BAR</div>');
	print('</div>');

	print("</div></div>"); // fin panel

	print('<div class="form-row">');
	print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
	print('<button type=submit name=Rechercher class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Chercher</button>');
	print('<button type=button  class="btn btn-warning raz"><span class="glyphicon glyphicon-erase"></span> Effacer tous les Champs</button>');
	print('</div>');
	print('</div>'); //fin ligne
	print("</form>");
}

/**
 * Renvoie la partie droite de l'egalite dans la clause de recherche par prénom (Gre le joker* ) 
 * @param string $pst_prenom : prénom à chercher  
 */
function clause_droite_prenom($pst_prenom, $pi_num_param)
{
	$st_clause = '';
	if ($pst_prenom != '') {
		if (preg_match('/\%/', $pst_prenom))
			$st_clause = " like :prenom$pi_num_param collate latin1_german1_ci";
		else
			$st_clause = "= :prenom$pi_num_param collate latin1_german1_ci";
	}
	return $st_clause;
}

/**
 * Renvoie la partie droite de l'égalite dans la clause de recherche par patronyme or prénom (Gère le joker* ) 
 * @param string $pst_nom : patronyme ou prénom à chercher  
 */
function clause_droite_nom($pconnexionBD, $pst_nom, $pb_variantes, $pi_num_param)
{
	$st_clause = '';
	if (!$pb_variantes || preg_match('/\%/', $pst_nom)) {
		if (preg_match('/\%/', $pst_nom))
			$st_clause = " like :nom$pi_num_param";
		else
			$st_clause = "=:nom$pi_num_param";
	} else {
		$a_params_precedents = $pconnexionBD->params();
		$pconnexionBD->initialise_params(array(":nom" => utf8_vers_cp1252($pst_nom)));
		$st_requete = "select rnv1.nom from rep_not_variantes rnv1, rep_not_variantes rnv2 where rnv2.nom = :nom COLLATE latin1_german1_ci and rnv1.idf_groupe=rnv2.idf_groupe";
		$a_variantes = $pconnexionBD->sql_select($st_requete);

		$pconnexionBD->initialise_params($a_params_precedents);
		if (count($a_variantes) == 0)
			$st_clause = "=:nom$pi_num_param";
		else {
			$i = 0;
			$a_params_variantes = array();
			foreach ($a_variantes as $st_variante) {
				$pconnexionBD->ajoute_params(array(":variante$i" => $st_variante));
				$a_params_variantes[] = ":variante$i";
				$i++;
			}
			$st_variantes = join(',', $a_params_variantes);
			$st_clause = " in ($st_variantes) ";
		}
	}
	return $st_clause;
}

/**
 * Renvoie la partie droite de l'egalite dans la clause de recherche par patronyme or prénom (Gère le joker* ) 
 * @param string $pst_nom : patronyme ou prénom à chercher  
 */
function clause_commune($pconnexionBD, $pst_nom)
{
	$st_clause = '';
	$st_clause = '';
	if (preg_match('/\%/', $pst_nom))
		$st_clause = " like :commune";
	else
		$st_clause = "= :commune COLLATE latin1_german1_ci";
	return $st_clause;
}

/*
* Renvoie la requête de recherche en fonction des critères sélectionnés
* @param object $pconnexionBD connexion à la BD
* @param integer $pi_idf_commune identifiant de la commune du notaire
* @param integer $pi_rayon rayon de recherche
* @param integer $pi_idf_repertoire identifiant du répertoire
* @param string $pst_type_acte type d'acte
* @param integer $pi_annee_min année minimale de l'acte
* @param integer $pi_annee_max année maximale de l'acte
* @param string $pst_nom1 nom du premier intervenant
* @param string $pst_prenom1 prénom du premier intervenant
* @param string $pst_nom2 nom du second intervenant
* @param string $pst_prenom2 prénom du second intervenant
* @param string $pst_paroisse paroisse objet de l'acte (acte capitulaire)
* @param string $pst_commentaires Recherche libre dans le commentaire
* @param boolean  $pb_rech_phonetique Recherche patronymique phonétique (0|1))
* @return array(Requête SQL NB actes, Requête SQL résultats)
*/
function requete_recherche($pconnexionBD, $pi_idf_commune, $pi_rayon, $pi_idf_repertoire, $pst_type_acte, $pi_annee_min, $pi_annee_max, $pst_nom1, $pst_prenom1, $pst_nom2, $pst_prenom2, $pst_paroisse, $pst_commentaires, $pb_rech_phonetique)
{
	$a_clauses = array();
	$pconnexionBD->initialise_params(array());
	if ($pi_rayon != '' && $pi_idf_commune != 0) {
		$a_communes_voisines = $pconnexionBD->liste_valeur_par_clef("select tk.idf_commune2,ca.nom from `tableau_kilometrique` tk join `commune_acte` ca on (tk.idf_commune2=ca.idf) where tk.distance <=$pi_rayon and tk.idf_commune1=$pi_idf_commune and tk.idf_commune2 in (select distinct idf_commune from `rep_not_desc`) order by nom");
		$a_champs = array_keys($a_communes_voisines);
		$a_champs[] = $pi_idf_commune;
		$a_clauses[] = "idf_commune in (" . join(',', $a_champs) . ")";;
	} elseif ($pi_idf_commune != 0)
		$a_clauses[] = "idf_commune=$pi_idf_commune";
	if (!empty($pi_idf_repertoire))
		$a_clauses[] = "rnd.idf_repertoire=$pi_idf_repertoire";
	if (!empty($pst_type_acte)) {
		$pconnexionBD->ajoute_params(array(':type_acte' => utf8_vers_cp1252($pst_type_acte)));
		if (preg_match('/\:/', $pst_type_acte))
			$a_clauses[] = "`type` like :type_acte collate latin1_german1_ci";
		else
			$a_clauses[] = "`type`= :type_acte COLLATE latin1_german1_ci";
	}
	if (!empty($pi_annee_min))
		$a_clauses[] = "annee>=$pi_annee_min";
	if ($pi_annee_max != '')
		$a_clauses[] = "annee<=$pi_annee_max";

	$st_requete_nb_actes = "select count(*) from `rep_not_desc` rnd join `rep_not_actes` rna on (rnd.idf_repertoire=rna.idf_repertoire) join commune_acte ca on (rnd.idf_commune=ca.idf) where rnd.publication='O'";
	$st_requete = "select rna.annee, rna.mois,rna.jour,rna.date_rep,rna.`type`,rna.nom1,rna.prenom1,rna.nom2,rna.prenom2,rna.paroisse,rna.commentaires,rna.page,rnd.nom_notaire,ca.nom,rnd.cote from `rep_not_desc` rnd join `rep_not_actes` rna on (rnd.idf_repertoire=rna.idf_repertoire) join commune_acte ca on (rnd.idf_commune=ca.idf) where rnd.publication='O'";
	if (!empty($pst_nom1) && !empty($pst_prenom1)) {
		$pst_nom1 = str_replace('*', '%', $pst_nom1);
		$pst_prenom1 = str_replace('*', '%', $pst_prenom1);
		$a_clauses[] = '((nom1' . clause_droite_nom($pconnexionBD, $pst_nom1, $pb_rech_phonetique, 1) . ' and prenom1' . clause_droite_prenom($pst_prenom1, 1) . ') or (nom2 ' . clause_droite_nom($pconnexionBD, $pst_nom1, $pb_rech_phonetique, 1) . ' and prenom2' . clause_droite_prenom($pst_prenom1, 1) . '))';
		$pconnexionBD->ajoute_params(array(':nom1' => utf8_vers_cp1252($pst_nom1), ':prenom1' => utf8_vers_cp1252($pst_prenom1)));
	} elseif (!empty($pst_nom1)) {
		$pst_nom1 = str_replace('*', '%', $pst_nom1);
		$a_clauses[] = '(nom1' . clause_droite_nom($pconnexionBD, $pst_nom1, $pb_rech_phonetique, 1) . ' or nom2' . clause_droite_nom($pconnexionBD, $pst_nom1, $pb_rech_phonetique, 1) . ')';
		$pconnexionBD->ajoute_params(array(':nom1' => utf8_vers_cp1252($pst_nom1)));
	} elseif (!empty($pst_prenom1)) {
		$pst_prenom1 = str_replace('*', '%', $pst_prenom1);
		$pconnexionBD->ajoute_params(array(':prenom1' => utf8_vers_cp1252($pst_prenom1), ':prenom2' => utf8_vers_cp1252($pst_prenom1)));
		$a_clauses[] = '(prenom1' . clause_droite_prenom($pst_prenom1, 1) . ' or prenom2' . clause_droite_prenom($pst_prenom1, 2) . ')';
	}
	if (!empty($pst_nom2) && !empty($pst_prenom2)) {
		$pst_nom2 = str_replace('*', '%', $pst_nom2);
		$pst_prenom2 = str_replace('*', '%', $pst_prenom2);
		$a_clauses[] = '((nom1' . clause_droite_nom($pconnexionBD, $pst_nom2, $pb_rech_phonetique, 2) . ' and prenom1' . clause_droite_prenom($pst_prenom2, 2) . ') or (nom2' . clause_droite_nom($pconnexionBD, $pst_nom2, $pb_rech_phonetique, 2) . ' and prenom2' . clause_droite_prenom($pst_prenom2, 2) . '))';
		$pconnexionBD->ajoute_params(array(':nom2' => utf8_vers_cp1252($pst_nom2), ':prenom2' => utf8_vers_cp1252($pst_prenom2)));
	} elseif (!empty($pst_nom2)) {
		$pst_nom2 = str_replace('*', '%', $pst_nom2);
		$a_clauses[] = '(nom1' . clause_droite_nom($pconnexionBD, $pst_nom2, $pb_rech_phonetique, 2) . ' or nom2' . clause_droite_nom($pconnexionBD, $pst_nom2, $pb_rech_phonetique, 2) . ')';
		$pconnexionBD->ajoute_params(array(':nom2' => utf8_vers_cp1252($pst_nom2)));
	} elseif (!empty($pst_prenom2)) {
		$pst_prenom2 = str_replace('*', '%', $pst_prenom2);
		$pconnexionBD->ajoute_params(array(':prenom1' => utf8_vers_cp1252($pst_prenom2), ':prenom2' => utf8_vers_cp1252($pst_prenom2)));
		$a_clauses[] = '(prenom1' . clause_droite_prenom($pst_prenom2, 1) . ' or prenom2' . clause_droite_prenom($pst_prenom2, 2) . ')';
	}
	if (!empty($pst_paroisse)) {
		$a_clauses[] = 'paroisse' . clause_commune($pconnexionBD, $pst_paroisse);
		$pconnexionBD->ajoute_params(array(':commune' => utf8_vers_cp1252($pst_paroisse)));
	}
	if (!empty($pst_commentaires)) {
		$st_commentaires = '%' . utf8_vers_cp1252($pst_commentaires) . '%';
		$a_clauses[] = "commentaires like :commentaires";
		$pconnexionBD->ajoute_params(array(':commentaires' => $st_commentaires));
	}
	if (count($a_clauses) > 0) {
		$st_clauses = join(' and ', $a_clauses);
		$st_requete = "$st_requete and  $st_clauses";
		$st_requete_nb_actes =  "$st_requete_nb_actes and $st_clauses";
	}
	$st_requete .= " order by annee, mois, jour";
	//print_r($pconnexionBD->params());
	//print("R=$st_requete<br>");  
	return array($st_requete_nb_actes, $st_requete);
}

/*
* Affiche les résultats de recherche
* @param object $pconnexionBD connexion à la BD
* @param integer $pi_idf_commune identifiant de la commune du notaire
* @param integer $pi_rayon rayon de recherche
* @param integer $pi_idf_repertoire identifiant du répertoire
* @param string $pst_type_acte type d'acte
* @param integer $pi_annee_min année minimale de l'acte
* @param integer $pi_annee_max année maximale de l'acte
* @param string $pst_nom1 nom du premier intervenant
* @param string $pst_prenom1 prénom du premier intervenant
* @param string $pst_nom2 nom du second intervenant
* @param string $pst_prenom2 prénom du second intervenant
* @param string $pst_paroisse paroisse objet de l'acte (acte capitulaire)
* @param string $pst_commentaires recherche libre dans commentaires 
* @param boolean  $pb_rech_phonetique Recherche patronymique phonétique (0|1))
* @param integer $pi_num_page numéro de la page
*/
function affiche_resultats_recherche($pconnexionBD, $pi_idf_commune, $pi_rayon, $pi_idf_repertoire, $pst_type_acte, $pi_annee_min, $pi_annee_max, $pst_nom1, $pst_prenom1, $pst_nom2, $pst_prenom2, $pst_paroisse, $pst_commentaires, $pb_rech_phonetique, $pi_num_page)
{
	global $ga_mois, $gi_max_actes;
	$a_clauses = array();
	$a_communes_voisines = array();
	$a_tableau_affichage = array();
	list($st_requete_nb_actes, $st_requete) = requete_recherche($pconnexionBD, $pi_idf_commune, $pi_rayon, $pi_idf_repertoire, $pst_type_acte, $pi_annee_min, $pi_annee_max, $pst_nom1, $pst_prenom1, $pst_nom2, $pst_prenom2, $pst_paroisse, $pst_commentaires, $pb_rech_phonetique);

	$a_params_precedents = $pconnexionBD->params();
	$i_nb_total_lignes = $pconnexionBD->sql_select1($st_requete_nb_actes);
	$pconnexionBD->initialise_params($a_params_precedents);
	$st_requete = "$st_requete limit $gi_max_actes";
	$a_liste_actes = $pconnexionBD->sql_select_multiple($st_requete);
	$i_nb_pages = ceil($i_nb_total_lignes / NB_LIGNES_PAR_PAGE);
	if ($i_nb_total_lignes > 0) {
		if ($i_nb_total_lignes > $gi_max_actes) {
			$i_nb_pages = ceil($gi_max_actes / NB_LIGNES_PAR_PAGE);
			print("<div class=\"alert alert-info\">$i_nb_total_lignes actes trouv&eacute;s. Recherche limit&eacute;e aux $gi_max_actes premiers</div>");
		} else
			print("<div class=\"alert alert-info\">$i_nb_total_lignes actes trouv&eacute;s</div>");
		print('<form id="recherche_rep_not" method="post">');
		print('<input type="hidden" name="mode" value="RECHERCHES">');
		if ($i_nb_pages > 1) {
			print("<div class=\"form-group row\">");
			print('<label for="num_page_rep_not" class="col-md-2 col-md-offset-4">Page du r&eacute;pertoire:</label>');
			print('<div class="col-md-2">');
			print("<select id=\"num_page_rep_not\" name=\"num_page_rep_not\" class=\"form-control\">");
			$a_pages = range(1, $i_nb_pages);
			print(chaine_select_options_simple($pi_num_page, $a_pages));
			print("</select>");
			print('</div>');
			print('</div>');
		}
		print("<table class=\"table table-bordered table-striped\"");
		print("<tr>");
		$a_entetes = array('Date', 'Type', 'Intervenant1', 'Intervenant2', 'Paroisse', 'Commentaires', 'Notaire - Commune (Cote)', 'Page', 'DateRep');
		foreach ($a_entetes as $st_cell_entete) {
			print("<th>$st_cell_entete</th>");
		}
		print("</tr>\n");
		$i_limite_inf = ($pi_num_page - 1) * NB_LIGNES_PAR_PAGE;
		$a_liste_actes = array_slice($a_liste_actes, $i_limite_inf, NB_LIGNES_PAR_PAGE);
		$i = 0;
		foreach ($a_liste_actes as $a_acte) {

			print("<tr>");
			list($i_annee, $i_mois, $i_jour, $st_date_rep, $st_type, $st_nom1, $st_prenom1, $st_nom2, $st_prenom2, $st_paroisse, $st_commentaires, $i_page, $st_notaire, $st_com_notaire, $st_cote) = $a_acte;
			if ($i_annee == 9999)
				$st_date = "Sans date";
			else
        if (empty($i_jour) && empty($i_mois))
				$st_date =  sprintf("Jour et mois inconnus %4d", $i_annee);
			else if (empty($i_jour))
				$st_date =  sprintf("Jour inconnu %s %4d", $ga_mois[$i_mois], $i_annee);
			else if (empty($i_mois))
				$st_date =  sprintf("%d mois inconnu %4d", $i_jour, $i_annee);
			else
				$st_date =  sprintf("%d %s %4d", $i_jour, $ga_mois[$i_mois], $i_annee);
			$a_ligne = array($st_date, cp1252_vers_utf8($st_type), cp1252_vers_utf8("$st_prenom1 $st_nom1"), cp1252_vers_utf8("$st_prenom2 $st_nom2"), cp1252_vers_utf8($st_paroisse), cp1252_vers_utf8($st_commentaires), sprintf("%s - %s (%s)", cp1252_vers_utf8($st_notaire), cp1252_vers_utf8($st_com_notaire), $st_cote), $i_page, $st_date_rep);
			foreach ($a_ligne as $st_champ) {
				if ($st_champ != "")
					print("<td>$st_champ</td>");
				else
					print("<td>&nbsp;</td>");
			}
			print("</tr>\n");
			$i++;
		}
		print("</table></form>");
	} else {
		print("<div class=\"text-center alert alert-danger\">Pas de r&eacute;sultats avec les contraintes d&eacute;finies</div>");
	}
	print('<div class="text-align">La cote fait r&eacute;f&eacute;rence &agrave; celle du r&eacute;pertoire et non celle de la liasse o&ugrave; se trouve l\'acte</div>');
	print('<div class="row">');
	print('<div class="btn-group col-md-offset-3 col-md-6" role="group">');
	print('<a class="btn btn-primary" href="RechercheRepNot.php" role="button"><span class="glyphicon glyphicon-search"></span>  Rechercher</a>');
	print('<a class="btn btn-warning" href="RechercheRepNot.php?recherche=nouvelle" role="button"><span class="glyphicon glyphicon-erase"></span> Commencer une nouvelle recherche</a>');
	print("</div>");
	print("</div>");
}

/******************************************************************************/
/*                     CORPS DU PROGRAMME                                     */
/******************************************************************************/
$gst_mode = empty($_REQUEST['mode']) ? 'MENU' : $_REQUEST['mode'];

print('<!DOCTYPE html>');
print("<head>\n");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print('<meta http-equiv="content-language" content="fr">');
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/select2.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");

?>
<script type='text/javascript'>
	$(document).ready(function() {

		function MajNotaires(json, textStatus, jqXHR) {
			$("#idf_rep").empty();
			$("#idf_rep").append('<option value="0">Tous</option>');
			var rep_crt = <?php if (isset($_SESSION['idf_repertoire'])) print($_SESSION['idf_repertoire']);
							else print("null"); ?>;
			$.each(json, function(key, rep) {
				console.log(rep + "\n");
				if (rep_crt == key) {
					$("#idf_rep").append('<option value="' + key + '" selected="selected">' + rep + '</option>');
				} else {
					$("#idf_rep").append('<option value="' + key + '">' + rep + '</option>');
				}
			});
		}

		$.ajax({
			url: 'ajax/notaire.php',
			data: 'idf_commune_notaire=' + $('#idf_commune_notaire').val(),
			dataType: 'json',
			success: MajNotaires
		});

		$("#idf_commune_notaire").change(function() {
			$.ajax({
				url: 'ajax/notaire.php',
				data: 'idf_commune_notaire=' + $('#idf_commune_notaire').val(),
				dataType: 'json',
				success: MajNotaires
			});
		});

		$('#type_acte').autocomplete({
			source: './ajax/type_acte.php',
			minLength: 2
		});
		$('#nom1').autocomplete({
			source: './ajax/patronyme.php',
			minLength: 2
		});
		$('#nom2').autocomplete({
			source: './ajax/patronyme.php',
			minLength: 2
		});
		$('#prenom1').autocomplete({
			source: './ajax/prenom.php',
			minLength: 2
		});
		$('#prenom2').autocomplete({
			source: './ajax/prenom.php',
			minLength: 2
		});
		$('#paroisse').autocomplete({
			source: './ajax/paroisse.php',
			minLength: 3
		});
		$("#num_page_rep_not").change(function() {
			$('#recherche_rep_not').submit();
		});

		$.fn.select2.defaults.set("theme", "bootstrap");

		$(".js-select-avec-recherche").select2({
			width: '100%'
		});

		$('.raz').click(function() {
			$("#idf_commune_notaire").val('');
			$("#rayon").val('');
			$("#idf_rep").val('');
			$("#type_acte").val('');
			$("#annee_min").val('');
			$("#annee_max").val('');
			$("#nom1").val('');
			$("#prenom1").val('');
			$("#nom2").val('');
			$("#prenom2").val('');
			$("#paroisse").val('');
			$("#commentaires").val('');
			$('#rech_phonetique').prop('checked', true);
		});
	});
</script>
<?php
print("<title>Recherche dans les r&eacute;pertoires de notaire</title>");
print('</head>');
print('<body>');
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

print('<div class="panel panel-primary">');
print('<div class="panel-heading">Recherche dans les r&eacute;pertoires de notaire</div>');

print('<div class="panel-body">');
$gst_type_recherche         = isset($_REQUEST['recherche']) ? $_REQUEST['recherche'] : '';
$gi_max_actes = 200;

switch ($gst_mode) {
	case 'MENU':
		unset($_SESSION['num_page_rep_not']);
		if ($gst_type_recherche == 'nouvelle') {
			$i_idf_commune = 0;
			$i_idf_repertoire = 0;
			$i_rayon = '';
			$st_type_acte = '';
			$i_annee_min = '';
			$i_annee_max = '';
			$st_nom1 = '';
			$st_prenom1 = '';
			$st_nom2 = '';
			$st_prenom2 = '';
			$st_paroisse = '';
			$st_commentaires = '';
			$b_rech_phonetique = true;
			unset($_SESSION['rech_phonetique']);
		} else {
			$i_idf_commune       = isset($_SESSION['idf_commune_notaire']) ? $_SESSION['idf_commune_notaire'] : 0;
			$i_rayon             = isset($_SESSION['rayon_rep_not']) && !empty($_SESSION['rayon_rep_not']) ? $_SESSION['rayon_rep_not'] : '';
			$i_idf_repertoire 	 = isset($_SESSION['idf_repertoire']) ? (int) $_SESSION['idf_repertoire'] : '';
			$st_type_acte  		 = isset($_SESSION['type_acte_rep_not']) ? $_SESSION['type_acte_rep_not'] : '';
			$i_annee_min         = isset($_SESSION['annee_min_rep_not']) && !empty($_SESSION['annee_min_rep_not']) ? $_SESSION['annee_min_rep_not'] : '';
			$i_annee_max         = isset($_SESSION['annee_max_rep_not']) && !empty($_SESSION['annee_max_rep_not']) ? $_SESSION['annee_max_rep_not'] : '';
			$st_nom1         	 = isset($_SESSION['nom1']) ? $_SESSION['nom1'] : '';
			$st_prenom1          = isset($_SESSION['prenom1']) ? $_SESSION['prenom1'] : '';
			$st_nom2         	 = isset($_SESSION['nom2']) ? $_SESSION['nom2'] : '';
			$st_prenom2          = isset($_SESSION['prenom2']) ? $_SESSION['prenom2'] : '';
			$st_paroisse         = isset($_SESSION['paroisse']) ? $_SESSION['paroisse'] : '';
			$st_commentaires         = isset($_SESSION['commentaires']) ? $_SESSION['commentaires'] : '';
			if (empty($_REQUEST['ancienne_page']))
				$b_rech_phonetique = true;
			else
				$b_rech_phonetique = isset($_SESSION['rech_phonetique']) ? $_SESSION['rech_phonetique'] : false;
		}
		$i_num_page = 1;
		$_SESSION['num_page_rep_not'] = $i_num_page;
		affiche_grille_recherche($connexionBD, $i_idf_commune, $i_rayon, $st_type_acte, $i_annee_min, $i_annee_max, $st_nom1, $st_prenom1, $st_nom2, $st_prenom2, $st_paroisse, $st_commentaires, $b_rech_phonetique);
		break;
	case 'RECHERCHES':
		$i_session_num_page = isset($_SESSION['num_page_rep_not']) ? $_SESSION['num_page_rep_not'] : 1;
		$i_num_page = empty($_POST['num_page_rep_not']) ? $i_session_num_page : (int) $_POST['num_page_rep_not'];
		$i_session_idf_commune = isset($_SESSION['idf_commune_notaire']) ? $_SESSION['idf_commune_notaire'] : null;
		$i_idf_commune_notaire = isset($_POST['idf_commune_notaire']) ? (int) $_POST['idf_commune_notaire'] : $i_session_idf_commune;
		$i_session_rayon             = isset($_SESSION['rayon_rep_not']) && !empty($_SESSION['rayon_rep_not']) ? $_SESSION['rayon_rep_not'] : '';
		$i_rayon = isset($_POST['rayon']) ? (int) $_POST['rayon'] : $i_session_rayon;
		$i_session_annee_min         = isset($_SESSION['annee_min_rep_not']) && !empty($_SESSION['annee_min_rep_not']) ? $_SESSION['annee_min_rep_not'] : '';
		$i_annee_min = isset($_POST['annee_min']) ? (int) $_POST['annee_min'] : $i_session_annee_min;
		$i_session_annee_max         = isset($_SESSION['annee_max_rep_not']) && !empty($_SESSION['annee_max_rep_not']) ? $_SESSION['annee_max_rep_not'] : '';
		$i_annee_max = isset($_POST['annee_max']) ? (int) $_POST['annee_max'] : $i_session_annee_max;
		$st_session_type_acte  		 = isset($_SESSION['type_acte_rep_not']) ? $_SESSION['type_acte_rep_not'] : '';
		$st_type_acte = isset($_POST['type_acte']) ? substr($_POST['type_acte'], 0, 40) : $st_session_type_acte;
		$i_session_idf_repertoire 	 = isset($_SESSION['idf_repertoire']) ? (int) $_SESSION['idf_repertoire'] : '';
		$i_idf_repertoire = isset($_POST['idf_rep']) ? (int) $_POST['idf_rep'] : $i_session_idf_repertoire;
		$st_session_nom1         	 = isset($_SESSION['nom1']) ? $_SESSION['nom1'] : '';
		$st_nom1 = isset($_POST['nom1']) ? substr($_POST['nom1'], 0, 40) : $st_session_nom1;
		$st_session_prenom1         	 = isset($_SESSION['prenom1']) ? $_SESSION['prenom1'] : '';
		$st_prenom1 = isset($_POST['prenom1']) ? substr($_POST['prenom1'], 0, 30) : $st_session_prenom1;
		$st_session_nom2        	 = isset($_SESSION['nom2']) ? $_SESSION['nom2'] : '';
		$st_nom2 = isset($_POST['nom2']) ? substr($_POST['nom2'], 0, 40) : $st_session_nom2;
		$st_session_prenom2         	 = isset($_SESSION['prenom2']) ? $_SESSION['prenom2'] : '';
		$st_prenom2 = isset($_POST['prenom2']) ? substr($_POST['prenom2'], 0, 30) : '';
		$st_session_paroisse         = isset($_SESSION['paroisse']) ? $_SESSION['paroisse'] : '';

		$st_paroisse = isset($_POST['paroisse']) ? substr($_POST['paroisse'], 0, 40) : $st_session_paroisse;
		$st_session_commentaires         = isset($_SESSION['commentaires']) ? $_SESSION['commentaires'] : '';
		$st_commentaires = isset($_POST['commentaires']) ? substr($_POST['commentaires'], 0, 40) : $st_session_commentaires;
		$b_session_rech_phonetique         = isset($_SESSION['rech_phonetique']) ? $_SESSION['rech_phonetique'] : false;
		if (empty($_REQUEST['ancienne_page']))
			$b_rech_phonetique = isset($_POST['rech_phonetique']) ? true : $b_session_rech_phonetique;
		else
			$b_rech_phonetique = isset($_POST['rech_phonetique']) ? true : false;
		$_SESSION['idf_commune_notaire']    = $i_idf_commune_notaire;
		$_SESSION['idf_repertoire']    		= $i_idf_repertoire;
		$_SESSION['rayon_rep_not']                  = $i_rayon;
		$_SESSION['annee_min_rep_not']				= $i_annee_min;
		$_SESSION['annee_max_rep_not']				= $i_annee_max;
		$_SESSION['type_acte_rep_not'] 				= $st_type_acte;
		$_SESSION['nom1'] 					= $st_nom1;
		$_SESSION['prenom1'] 				= $st_prenom1;
		$_SESSION['nom2'] 					= $st_nom2;
		$_SESSION['prenom2'] 				= $st_prenom2;
		$_SESSION['paroisse'] 				= $st_paroisse;
		$_SESSION['commentaires'] 		= $st_commentaires;
		$_SESSION['rech_phonetique'] 	= $b_rech_phonetique;
		$_SESSION['num_page_rep_not'] 		= $i_num_page;
		$gst_adresse_ip = $_SERVER['REMOTE_ADDR'];
		$pf = @fopen("$gst_rep_logs/requetes_rep_not.log", 'a');
		date_default_timezone_set($gst_time_zone);
		list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
		$i_mois++;
		$i_annee += 1900;
		$st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
		$st_chaine_log = join(';', array($st_date_log, $_SESSION['ident'], $gst_adresse_ip, $i_idf_commune_notaire, $i_rayon, $i_idf_repertoire, $st_type_acte, $i_annee_min, $i_annee_max, $st_nom1, $st_prenom1, $st_nom2, $st_prenom2, $st_paroisse));
		@fwrite($pf, "$st_chaine_log\n");
		@fclose($pf);
		affiche_resultats_recherche($connexionBD, $i_idf_commune_notaire, $i_rayon, $i_idf_repertoire, $st_type_acte, $i_annee_min, $i_annee_max, $st_nom1, $st_prenom1, $st_nom2, $st_prenom2, $st_paroisse, $st_commentaires, $b_rech_phonetique, $i_num_page);
		break;
	default:
}

print("</div></div>"); // fin panel body
print("</div>"); // fin container
print("</body>");
print("</html>");
