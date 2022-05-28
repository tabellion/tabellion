<?php

/**
 * Affiche la liste des liasses
 * @param object $pconnexionBD
 */
function menu_liste($pconnexionBD)
{
	$a_serie_liasse = $pconnexionBD->liste_valeur_par_clef("SELECT serie_liasse, nom FROM serie_liasse order by ordre");
	if (isset($_POST['serie_liasse'])) {
		$_SESSION['serie_liasse'] = $_POST['serie_liasse'];
	} elseif (isset($_GET['serie_liasse'])) {
		$_SESSION['serie_liasse'] = $_GET['serie_liasse'];
	}
	if (!isset($_SESSION['serie_liasse'])) {
		$_SESSION['serie_liasse'] = '2E';
	}
	$st_serie_liasse = $_SESSION['serie_liasse'];
	global $gi_num_page_cour;
	unset($_SESSION['liasse']);
	if (isset($_SESSION['serie_liasse'])) {
		$st_serie_liasse = $_SESSION['serie_liasse'];
	} else {
		$st_serie_liasse = '2E';
	}
	$a_numerotation_liasses = array("z", "1", "2", "3", "4", "5", "6", "7", "8", "9");
	print('<div align=center><form id="consult" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading">Consultation des liasses notariales</div>');
	print('<div class="panel-body">');
	print('<table border=0 cellpadding=0 cellspacing=0>');
	print('<tr class="ligne_paire"><td rowspan="4" width="500" align="left">');
	print("<div class=\"row text-left\">");
	print('<label for="serie_liasse" class="col-form-label">Série de liasses&nbsp&nbsp</label>');
	print("<select name='serie_liasse' id='serie_liasse' onChange='window.location=\"" . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . "?serie_liasse=\"+this.value;'>" .
		chaine_select_options($st_serie_liasse, $a_serie_liasse) . "</select>");
	if ($st_serie_liasse == "L") {
		print("Ces répertoires sont issus de la série L qui regroupe tous les actes de l’administration entre 1789 et l’an VIII. ");
		print("Nous n’avons saisi que les numéros de la série L ayant traits à des répertoires notariés. <br>");
		print("La recherche se fait donc entre des bornes précises, selon 6 groupes : ");
		print(" 2197 à 2263, 2328 à 2393, 2433 à 2492,< 2552 à 2596, 2607 à 2668, 2683 à 2732.");
	}
	print('</div></td><td rowspan="4"><label>Cotes&nbsp&nbsp&nbsp</label></td>');
	// dizaine de milliers
	print('<td>');
	$i_session_init_dixm = isset($_SESSION['init_dixm']) ? $_SESSION['init_dixm'] : $a_numerotation_liasses[0];
	$gc_init_dixm = empty($_GET['init_dixm']) ? $i_session_init_dixm : $_GET['init_dixm'];
	$_SESSION['init_dixm'] = $gc_init_dixm;
	foreach ($a_numerotation_liasses as $c_init_dixm) {
		if ($c_init_dixm == $gc_init_dixm)	print("<span style=\"font-weight: bold;\">$c_init_dixm </span>");
		else								print("<a href=\"" . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . "?init_dixm=$c_init_dixm\">$c_init_dixm</a> ");
	}
	print('</td><td align="left">&nbsp&nbsp&nbsp<I>dizaine de milliers</I></td></tr>');
	// millier
	print('<tr class=ligne_paire><td>');
	$i_session_init_mill = isset($_SESSION['init_mill']) ? $_SESSION['init_mill'] : $a_numerotation_liasses[0];
	$gc_init_mill = empty($_GET['init_mill']) ? $i_session_init_mill : $_GET['init_mill'];
	$_SESSION['init_mill'] = $gc_init_mill;
	foreach ($a_numerotation_liasses as $c_init_mill) {
		if ($c_init_mill == $gc_init_mill)	print("<span style=\"font-weight: bold;\">$c_init_mill </span>");
		else								print("<a href=\"" . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . "?init_mill=$c_init_mill\">$c_init_mill</a> ");
	}
	print('</td><td align="left">&nbsp&nbsp&nbsp<I>millier</I></td></tr>');
	// centaine
	print('<tr class=ligne_paire><td>');
	$i_session_init_cent = isset($_SESSION['init_cent']) ? $_SESSION['init_cent'] : $a_numerotation_liasses[0];
	$gc_init_cent = empty($_GET['init_cent']) ? $i_session_init_cent : $_GET['init_cent'];
	$_SESSION['init_cent'] = $gc_init_cent;
	foreach ($a_numerotation_liasses as $c_init_cent) {
		if ($c_init_cent == $gc_init_cent)	print("<span style=\"font-weight: bold;\">$c_init_cent </span>");
		else								print("<a href=\"" . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . "?init_cent=$c_init_cent\">$c_init_cent</a> ");
	}
	print('</td><td align="left">&nbsp&nbsp&nbsp<I>centaine</I></td></tr>');
	// dizaine
	print('<tr class=ligne_paire><td>');
	$i_session_init_dix = isset($_SESSION['init_dix']) ? $_SESSION['init_dix'] : $a_numerotation_liasses[0];
	$gc_init_dix = empty($_GET['init_dix']) ? $i_session_init_dix : $_GET['init_dix'];
	$_SESSION['init_dix'] = $gc_init_dix;
	foreach ($a_numerotation_liasses as $c_init_dix) {
		if ($c_init_dix == $gc_init_dix)		print("<span style=\"font-weight: bold;\">$c_init_dix </span>");
		else								print("<a href=\"" . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . "?init_dix=$c_init_dix\">$c_init_dix</a> ");
	}
	print('</td><td align="left">&nbsp&nbsp&nbsp<I>dizaine</I></td></tr></table>');
	print('</div></div>');
	$numero  = $gc_init_dixm == 'z' ? '0' : $gc_init_dixm;
	$numero .= $gc_init_mill == 'z' ? '0' : $gc_init_mill;
	$numero .= $gc_init_cent == 'z' ? '0' : $gc_init_cent;
	$numero .= $gc_init_dix == 'z' ? '0' : $gc_init_dix;
	$st_requete = "select liasse.cote_liasse as idf, liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, " .
		"       max(case when liasse_releve.idf is null then 'Non' else 'Oui' end) as releve, " .
		"       max(case when liasse_publication_papier.idf is null then 'Non' else 'Oui' end) as publication_papier, " .
		"       max(case when liasse_releve.in_publication_numerique = 1 then 'Oui' else 'Non' end) as publication_numerique, " .
		"       max(case when liasse_photo.idf is null then 'Non' else 'Oui' end) as photo, " .
		"       max(case when liasse_programmation.idf is null then 'Non' else 'Oui' end) as program " .
		"from liasse " .
		"     left outer join liasse_releve on liasse.cote_liasse = liasse_releve.cote_liasse " .
		"     left outer join liasse_publication_papier on liasse.cote_liasse = liasse_publication_papier.cote_liasse " .
		"     left outer join liasse_photo on liasse.cote_liasse = liasse_photo.cote_liasse " .
		"     left outer join liasse_programmation on liasse.cote_liasse = liasse_programmation.cote_liasse and " .
		"                                             (liasse_programmation.date_reelle_fin is null or " .
		"                                              liasse_programmation.date_reelle_fin=str_to_date('0000/00/00', '%Y/%m/%d'))" .
		"where liasse.cote_liasse like '" . $st_serie_liasse . "-" . $numero . "%' " .
		"group by liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees " .
		"order by liasse.cote_liasse";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Cote', 'Notaire(s)', 'Periode(s)', 'Relev&eacute;', 'Papier', 'Num&eacute;rique', 'Photo', 'Programm&eacute;e', '')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		//$pagination->affiche_entete_liens_navigation();
		$pagination->affiche_tableau_edition_select_sil();
		//$pagination->affiche_entete_liens_navigation();      
	} else {
		print("<div align=center>Pas de liasses</div>\n");
	}
	print("</form>");
	print('</div>');
}
/**
 * Affiche la liste des relevés d'une liasse
 * @param object	$pconnexionBD
 */
function menu_liste_releve($pconnexionBD)
{
	global $gi_num_page_cour;
	$st_requete = "select concat('REL', liasse_releve.idf) as idf, " .
		"       case when liasse_releve.idf_releveur=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as releveur, " .
		"       case when date_fin_releve = str_to_date('0000/00/00', '%Y/%m/%d') then '' else date_format(date_fin_releve, '%d/%m/%Y') end as date_fin_releve, " .
		"       case when in_publication_numerique=1 then 'Oui' else 'Non' end as publi_num, " .
		"       info_complementaires " .
		"from liasse_releve " .
		"     left outer join releveur on liasse_releve.idf_releveur = releveur.idf " .
		"where liasse_releve.cote_liasse = '" . $_SESSION['cote_liasse_gal'] . "' " .
		"order by liasse_releve.date_fin_releve";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	print('<div align=center><form id="listeReleve" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Releveur', 'Date fin relevé', 'Publication numérique', 'Infos complémentaires')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_sil(2);
	} else {
		print("<div class=\"alert alert-danger\">Pas de relevé</div>");
	}
	print('</form>');
	print('<div>&nbsp;</div>');
	print('</div>');
}

/**
 * Affiche la liste des publications papier d'une liasse
 * @param object	$pconnexionBD
 */
function menu_liste_publication($pconnexionBD)
{
	global $gi_num_page_cour;
	$st_requete = "select concat('PUB', liasse_publication_papier.idf) as idf, publication_papier.nom, " .
		"       case when date_publication = str_to_date('0000/00/00', '%Y/%m/%d') then '' else date_format(date_publication, '%d/%m/%Y') end as date_publication, " .
		"       substr(info_complementaires,1,50) as info_complementaires " .
		"from liasse_publication_papier " .
		"     left outer join publication_papier on liasse_publication_papier.idf_publication_papier = publication_papier.idf " .
		"where liasse_publication_papier.cote_liasse = '" . $_SESSION['cote_liasse_gal'] . "' " .
		"order by publication_papier.date_publication";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	print('<div align=center><form id="listePubli" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Titre publication', 'Date publication', 'Infos compl&eacute;mentaires')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_sil(2);
	} else {
		print('<div class="alert alert-danger">Pas de publication papier</div>');
	}
	print('</form>');
	print('<div>&nbsp;</div>');
	print('</div>');
}

/**
 * Affiche la liste des photos d'une liasse
 * @param object	$pconnexionBD
 */
function menu_liste_photo($pconnexionBD)
{
	global $gi_num_page_cour;
	$st_requete = "select concat('PHO', liasse_photo.idf) as idf, " .
		"       case when liasse_photo.idf_photographe=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as photographe, " .
		"       case when date_photo = str_to_date('0000/00/00', '%Y/%m/%d') then '' else date_format(date_photo, '%d/%m/%Y') end as date_photo, " .
		"       couverture_photo.nom as couverture, codif_photo.nom as codif, info_complementaires " .
		"from liasse_photo " .
		"     left outer join releveur on liasse_photo.idf_photographe = releveur.idf " .
		"     left outer join couverture_photo on liasse_photo.idf_couverture_photo = couverture_photo.idf " .
		"     left outer join codif_photo on liasse_photo.idf_codif_photo = codif_photo.idf " .
		"where liasse_photo.cote_liasse = '" . $_SESSION['cote_liasse_gal'] . "' " .
		"order by liasse_photo.date_photo";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	print('<div align=center><form id="listePhoto" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Photographe', 'Date photos', 'Couverture photos', 'Codif photos', 'Infos compl&eacute;mentaires')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_sil(2);
	} else {
		print("<div class=\"alert alert-danger\">Pas de photo</div>");
	}
	print('</form>');
	print('<div>&nbsp;</div></div>');
}

/**
 * Affiche la liste des programmations d'une liasse
 * @param object	$pconnexionBD
 */
function menu_liste_program($pconnexionBD)
{
	global $gi_num_page_cour;
	$st_requete = "select concat('PRO', liasse_programmation.idf) as idf, " .
		"       case when liasse_programmation.idf_intervenant=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as intervenant, " .
		"       case when date_creation = str_to_date('0000/00/00', '%Y/%m/%d') then '' else date_format(date_creation, '%d/%m/%Y') end as date_creation, " .
		"       case when date_echeance = str_to_date('0000/00/00', '%Y/%m/%d') then '' else date_format(date_echeance, '%d/%m/%Y') end as date_echeance, " .
		"       programmation_releve.nom as etat, " .
		"       case when in_program_releve=1 then 'Oui' else 'Non' end as releve, " .
		"       case when in_program_photo=1 then 'Oui' else 'Non' end as photo, info_complementaires  " .
		"from liasse_programmation " .
		"     left outer join releveur on liasse_programmation.idf_intervenant = releveur.idf " .
		"     left outer join programmation_releve on liasse_programmation.idf_priorite = programmation_releve.idf " .
		"where liasse_programmation.cote_liasse = '" . $_SESSION['cote_liasse_gal'] . "' and" .
		"      (liasse_programmation.date_reelle_fin is null or liasse_programmation.date_reelle_fin=str_to_date('0000/00/00', '%Y/%m/%d')) " .
		"order by liasse_programmation.date_creation";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	print('<div align=center><form id="listeProgram" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array(
				'Intervenant', 'Date cr&eacute;ation', 'Date &eacute;ch&eacute;ance', 'Etat programmation', 'Programmation relev&eacute;', 'Programmation photo',
				'Infos compl&eacute;mentaires'
			)
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_sil(2);
	} else {
		print('<div class="alert alert-danger">Pas de programmation</div>');
	}
	print('</form>');
	print('<div>&nbsp;</div></div>');
}

/** Affiche le menu des actions sur une liasse
 * @param object	$pconnexionBD			Identifiant de la connexion de base
 */
function menu_gerer($pconnexionBD)
{
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '</div>');
	print('<div class="panel-body">');
	menu_liste_releve($pconnexionBD);
	menu_liste_publication($pconnexionBD);
	menu_liste_photo($pconnexionBD);
	menu_liste_program($pconnexionBD);
	print('</div></div>');
	print('<form method="post">');
	print('<input type=hidden name="mode" id="mode" value="LISTE">');
	print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	print('<button type=submit id=btListe class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Retour</button>');
	print('</div></form>');
}


/** Elimine les éventuels \ du texte et remplace les ' par \'
 * @param string	$pst_texte			Le texte à traiter
 */
function escape_apostrophe($pst_texte)
{
	$st_texte = str_replace("\\", "", $pst_texte);
	$st_texte = str_replace("'", "\'", $pst_texte);
	return ($st_texte);
}
