<?php
function menu_liste($pconnexionBD)
{					/*	Affiche la liste des liasses
															@param object $pconnexionBD			*/
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
	print('<div align=center><form  action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	print('<input type=hidden name="mode" id="mode" value="AJOUTER_GROUPE_RELEVE">');
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading">Actions sur les liasses notariales</div>');
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
	} else
		print("<div align=center>Pas de liasses</div>\n");

	print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	print('<button type=submit id="btMenuGroupeReleve" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span>  Relevé d\'un groupe de liasses</button>');
	print('<button type=submit id="btMenuGroupePhoto" class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-new-window"></span>  Photo d\'un groupe de liasses</button>');
	print('<button type=submit id="btMenuGroupeProgram" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span>  Programmation d\'un groupe de liasses</button>');
	print('</div>');

	print("</form>");
	print('</div>');
}

function menu_liste_releve($pconnexionBD)
{			/*	Affiche la liste des relevés d'une liasse
															@param object	$pconnexionBD				*/
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
	print('<input type=hidden name="modeReleve" id="modeReleve" value="SUPPRIMER_RELEVE">');
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Releveur', 'Date fin relevé', 'Publication numérique', 'Infos complémentaires', 'Modifier', 'Supprimer')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_edition_sil(2);
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
		print('<button type=button class="btn btn-sm btn-danger" id="btSupprimerReleve"><span class="glyphicon glyphicon-trash"></span>  Supprimer les relevés sélectionnés</button>');
	} else {
		print("<div class=\"alert alert-danger\">Pas de relevé</div>");
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	}
	print('<button type=submit id="btMenuAjouterReleve" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span>  Ajouter un relevé</button>');
	print('</div></form>');
	print('<div>&nbsp;</div>');
	print('</div>');
}

function menu_liste_publication($pconnexionBD)
{		/*	Affiche la liste des publications papier d'une liasse
															@param object	$pconnexionBD		*/
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
	print('<input type=hidden name="modePubli" id="modePubli" value="SUPPRIMER_LIEN_PUBLI">');
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Titre publication', 'Date publication', 'Infos compl&eacute;mentaires', 'Modifier', 'Supprimer')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_edition_sil(2);
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
		print('<button type=button class="btn btn-sm btn-danger" id="btSupprimerLienPubli"><span class="glyphicon glyphicon-trash"></span>  Supprimer les liens publications sélectionnés</button>');
	} else {
		print('<div class="alert alert-danger">Pas de publication papier</div>');
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	}
	print('<button type=submit id="btMenuAjouterLienPubli" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span> Ajouter un lien publication papier</button>');
	print('</div></form>');
	print('<div>&nbsp;</div>');
	print('</div>');
}

function menu_liste_photo($pconnexionBD)
{			/*	Affiche la liste des photos d'une liasse
															@param object	$pconnexionBD		*/

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
	print('<input type=hidden name="modePhoto" id="modePhoto" value="SUPPRIMER_PHOTO">');
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Photographe', 'Date photos', 'Couverture photos', 'Codif photos', 'Infos compl&eacute;mentaires', 'Modifier', 'Supprimer')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_edition_sil(2);
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
		print('<button type=button class="btn btn-sm btn-danger" id="btSupprimerPhoto"><span class="glyphicon glyphicon-trash"></span>  Supprimer les photos sélectionnées</button>');
	} else {
		print("<div class=\"alert alert-danger\">Pas de photo</div>");
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	}
	print('<button type=submit id="btMenuAjouterPhoto" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span> Ajouter des photos</button>');
	print('</div></form>');
	print('<div>&nbsp;</div></div>');
}

function menu_liste_program($pconnexionBD)
{			/*	Affiche la liste des programmations d'une liasse
															@param object	$pconnexionBD		*/

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
	print('<input type=hidden name="modeProgram" id="modeProgram" value="SUPPRIMER_PROGRAM">');
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
				'Infos compl&eacute;mentaires', 'Modifier', 'Supprimer'
			)
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_edition_sil(2);
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
		print('<button type=button class="btn btn-sm btn-danger" id="btSupprimerProgram"><span class="glyphicon glyphicon-trash"></span> Supprimer les programmations sélectionnées</button>');
	} else {
		print('<div class="alert alert-danger">Pas de programmation</div>');
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	}
	print('<button type=submit id="btMenuAjouterProgram" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span> Ajouter une programmation</button>');
	print('</div></form>');
	print('<div>&nbsp;</div></div>');
}

function menu_gerer($pconnexionBD)
{					/*	Affiche le menu des actions sur une liasse
															@param object	$pconnexionBD			Identifiant de la connexion de base   */

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '</div>');
	print('<div class="panel-body">');
	menu_liste_releve($pconnexionBD);
	menu_liste_publication($pconnexionBD);
	menu_liste_photo($pconnexionBD);
	menu_liste_program($pconnexionBD);
	print('</div></div>');
	print('<form action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	print('<input type=hidden name="modeMenu" id="modeMenu">');
	print('<div class="btn-group col-md-9 col-md-offset-4" role="group">');
	print('<button type=submit id=btMenuAjouterReleveur class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span> Ajouter un releveur ou un photographe</button>');
	print('<button type=submit id=btListe class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Retour</button>');
	print('</div></form>');
}

/** --------------------------------------- relevés ----------------------------------- **/
function menu_edition_releve(
	$pconnexionBD,
	$pa_releveur,
	$pi_idf_releve,
	$pi_idf_releveur,
	$pst_date_fin_releve,
	$pi_publication_numerique,
	$pst_info_compl
) {	/*	Affiche de la table d'édition d'un relevé
															@param object	$pconnexionBD				Identifiant de la connexion de base
															@param array		$pa_releveur				Tableau des releveurs
															@param integer	$pi_idf_releve				Identifiant du relevé à modifier
															@param integer	$pi_idf_releveur			Identifiant du releveur 
															@param string	$pst_date_fin_releve		Date de fin de relevé
															@param integer	$pi_publication_numerique	booleen publication numérique Oui/Non
															@param string	$pst_info_compl				Informations complémentaires sur le relevé		*/

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Releveur&nbsp;</label></div>" .
		"<div class='form-group col-md-6' align='left'><select name=idf_releveur id='idf_releveur' class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_idf_releveur, $pa_releveur) . "</select></div></div>");

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Date de fin de relevé&nbsp;</label></div>" .
		"<div class='form-group col-md-6'>" .
		"<input type=text name=date_fin_releve id=date_fin_releve size=10 maxlength='10' value='" . $pst_date_fin_releve . "' class='form-control'></div></div>");

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Publication numérique&nbsp;</label></div>' .
		'<div class="form-group col-md-1" align="left"><div class="form-check">' .
		'<input type="checkbox" class="form-check-input" name=publi_num id=publi_num value="1" ');
	if ($pi_publication_numerique == 1) {
		print('checked>');
	} else {
		print('unchecked>');
	}
	print("</div></div></div>");

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Informations complémentaires&nbsp;</label></div>" .
		"<div class='form-group col-md-6' align='left'><textarea class='form-control' rows='3' maxlength=255 name='info_compl'>" . $pst_info_compl . "</textarea></div></div>");
}

function menu_modifier_releve($pconnexionBD, $pi_idf_releve, $pa_releveur)
{	/*	 Affiche le menu de modification d'un relevé
															@param object	$pconnexionBD		Identifiant de la connexion de base
															@param integer	$pi_idf_releve		Identifiant du revelée à modifier 
															@param array		$pa_releveur		Tableau des releveurs		*/

	list($i_idf_releveur, $st_date_fin_releve, $i_publication_numerique, $st_info_compl)
		= $pconnexionBD->sql_select_listeUtf8("select idf_releveur, " .
			"       case when date_fin_releve = str_to_date('0000/00/00', '%Y/%m/%d') then '' " .
			"            else date_format(date_fin_releve, '%d/%m/%Y') " .
			"            end as date_fin_releve, " .
			"       in_publication_numerique, info_complementaires " .
			"from liasse_releve " .
			"where idf=$pi_idf_releve");

	print('<form id=majReleve method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="MODIFIER_RELEVE">');
	print("<input type=hidden name=idf_releve value=$pi_idf_releve>");

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Relevé</div>');
	print('<div class="panel-body">');
	menu_edition_releve(
		$pconnexionBD,
		$pa_releveur,
		$pi_idf_releve,
		$i_idf_releveur,
		$st_date_fin_releve,
		$i_publication_numerique,
		$st_info_compl
	);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btModifierReleve class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Modifier</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_ajouter_releve($pconnexionBD, $pa_releveur)
{	/*	Affiche le menu d'ajout d'un relevé
															@param object	$pconnexionBD		Identifiant de la connexion de base
															@param array		$pa_releveur		Tableau des releveurs		*/

	print('<form id=majReleve method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_RELEVE">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Relevé</div>');
	print('<div class="panel-body">');
	menu_edition_releve($pconnexionBD, $pa_releveur, '', 0, '', '', '');
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterReleve class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_ajouter_groupe_releve($pconnexionBD, $pa_releveur)
{	/*	Affiche le menu d'ajout d'un relevé sur un groupe de liasses
															@param object	$pconnexionBD		Identifiant de la connexion de base
															@param array		$pa_releveur		Tableau des releveurs		*/
	print('<form id=groupeReleve method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="VERIFIER_GROUPE_RELEVE">');
	print('<input type=hidden name=serie id=serie value="' . $_SESSION['serie_liasse'] . '">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Relevé d\'un groupe de liasses de la série ' . $_SESSION['serie_liasse'] . '</div>');
	print('<div class="panel-body">');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Numéros des liasses&nbsp</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="numeros" id=numeros size=80 maxlength=80 value="" class="form-control"></div></div>');

	menu_edition_releve($pconnexionBD, $pa_releveur, '', 0, '', '', '');

	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterGroupeReleve class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Vérifier</button>');
	print('<button type=submit formnovalidate id=btRetourListe class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_confirmer_groupe_releve(
	$pconnexionBD,
	$pa_cotes,
	$pst_numeros,
	$pa_releveur,
	$pi_idf_releveur,
	$pst_date_fin_releve,
	$pst_publi_num,
	$pst_info_compl
) {

	$gi_get_num_page = empty($_GET['num_page_grp_rel']) ? 1 : (int) $_GET['num_page_grp_rel'];
	$gi_num_page = empty($_POST['num_page_grp_rel']) ? $gi_get_num_page : (int) $_POST['num_page_grp_rel'];
	print('<form id="confirme_groupe_releves" method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print("<input type='hidden' name=mode id=mode value='VERIFIER_GROUPE_RELEVE'>");
	print('<input type="hidden" name=serie id=serie value="' . $_SESSION['serie_liasse'] . '">');

	print('<div class="panel panel-primary">' .
		'<div class="panel-heading" align="center">Vérification du relevé d\'un groupe de liasses de la série ' . $_SESSION['serie_liasse'] . '</div>' .
		'<div class="panel-body">');
	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Série de la liasse&nbsp</label></div>' .
		'<div class="form-group col-md-1" align="left">' . $_SESSION['serie_liasse'] . '</div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Numéros des liasses&nbsp</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="numeros" id=numeros readonly=true value="' . $pst_numeros . '" class="form-control"></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Releveur&nbsp;</label></div>' .
		'<div class="form-group col-md-6" align="left"><select name=idf_releveur id="idf_releveur" readonly=true class="form-control">' .
		chaine_select_options($pi_idf_releveur, $pa_releveur) . '</select></div></div>');

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Date de fin de relevé&nbsp;</label></div>" .
		"<div class='form-group col-md-6'>" .
		"<input type=text name=date_fin_releve id=date_fin_releve size=10 maxlength='10' readonly=true value='" . $pst_date_fin_releve . "' class='form-control'></div></div>");

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Publication numérique&nbsp;</label></div>' .
		'<div class="form-group col-md-1" align="left"><div class="form-check">' .
		'<input type="checkbox" class="form-check-input" name=publi_num id=publi_num readonly=true value="1" ');
	if ($pi_publication_numerique == 1) {
		print('checked>');
	} else {
		print('unchecked>');
	}
	print("</div></div></div>");

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Informations complémentaires&nbsp;</label></div>" .
		"<div class='form-group col-md-6' align='left'><textarea class='form-control' rows='3' readonly=true name='info_compl'>" . $pst_info_compl . "</textarea></div></div>");

	print('<br>');
	$st_liste = compose_liste_in($pa_cotes);
	$st_requete = "select liasse.cote_liasse as id, liasse.cote_liasse, liasse_notaire.nom_notaire, liasse_notaire.prenom_notaire, " .
		"commune_acte.nom as nom_commune, liasse_notaire.commentaire, " .
		"concat(releveur.nom, ' ', releveur.prenom) as releveur_prec, liasse_releve.date_fin_releve ";
	$st_requete .= "from liasse join liasse_notaire on liasse.cote_liasse = liasse_notaire.cote_liasse " .
		"            join commune_acte on commune_acte.idf = liasse_notaire.idf_commune_etude " .
		"            left outer join liasse_releve on liasse_releve.cote_liasse = liasse.cote_liasse " .
		"            left outer join releveur on releveur.idf = liasse_releve.idf_releveur " .
		"where liasse.cote_liasse in (" . $st_liste . ") " .
		"order by cote_liasse";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page_grp_rel',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array(
				'Cote', 'Nom notaire', 'Prénom', 'Commune', 'Commentaire',
				'Releveur antérieur', 'Date relevé antérieur'
			)
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page);
		$pagination->affiche_tableau_sil(2);
		$pagination->affiche_entete_liens_navlimite();
	} else {
		print('<div class="alert alert-danger">Pas de liasses</div>');
	}

	print('</div></div>');
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btValiderGroupeReleve class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Valider</button>');
	print('<button type=submit formnovalidate id=btRetourReleve class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');
	print('</form>');
}

/** --------------------------------------- publication papier ----------------------------------- **/
function menu_edition_lien_publication($pconnexionBD, $pa_publication, $pi_idf_publication)
{	/*	Affiche de la table d'édition d'un lien publication papier
														@param object	$pconnexionBD				Identifiant de la connexion de base
														@param array		$pa_publication				Tableau des publications papier
														@param integer	$pi_idf_publication				Identifiant d'une publication papier		*/

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-1' align='right'><label class='col-form-label'>Publication&nbsp;</label></div>" .
		"<div class='form-group col-md-6' align='left'><select name=idf_publication id='idf_publication' class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_idf_publication, $pa_publication) . "</select></div></div>");
}

function menu_modifier_lien_publication($pconnexionBD, $pi_idf_lien_publication, $pa_publication)
{	/*	Affiche le menu de modification d'un lien publication papier
															@param object	$pconnexionBD		Identifiant de la connexion de base
															@param integer	$pi_idf_publication	Identifiant du lien publication papier 
															@param array		$pa_publication		Tableau des publications papier		*/

	list($i_idf_publication)
		= $pconnexionBD->sql_select_listeUtf8("select idf_publication_papier from liasse_publication_papier where idf=" . $pi_idf_lien_publication);
	print('<form id=majLienPubli method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="MODIFIER_LIEN_PUBLI">');
	print("<input type=hidden name=idf_lien_publi value=$pi_idf_lien_publication>");

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Lien publication papier</div>');
	print('<div class="panel-body">');
	menu_edition_lien_publication($pconnexionBD, $pa_publication, $i_idf_publication);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btModifierLienPubli class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Modifier</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_ajouter_lien_publication($pconnexionBD, $pa_publication)
{			/*	Affiche le menu d'ajout d'un lien publication papier
															@param object		$pconnexionBD		Identifiant de la connexion de base
															@param array		$pa_publication		Tableau des publications papier		*/

	print('<form id=majLienPubli method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_LIEN_PUBLI">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Lien publication papier</div>');
	print('<div class="panel-body">');
	menu_edition_lien_publication($pconnexionBD, $pa_publication, 0);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterLienPubli class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

/** --------------------------------------- photos ----------------------------------- **/

function menu_edition_photo(
	$pconnexionBD,
	$pa_reveleur,
	$pi_idf_photo,
	$pi_idf_photographe,
	$pst_date_photo,
	$pi_couverture_photo,
	$pi_codif_photo,
	$pst_info_compl,
	$pa_couverture_photo,
	$pa_codif_photo
) { /*		Affiche de la table d'édition d'une photo
															@param object	$pconnexionBD				Identifiant de la connexion de base
															@param array	$pa_reveleur				Tableau des releveurs
															@param integer	$pi_idf_photo				Identifiant de la prise de photo à modifier
															@param integer	$pi_idf_photographe			Identifiant du photographe 
															@param string	$pst_date_photo				Date de photo
															@param integer	$pi_couverture_photo	code couverture photo
															@param integer	$pi_codif_photo			codif photo
															@param string	$pst_info_compl				Informations complémentaires sur la prise de photo		*/
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Photographe&nbsp;</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name=idf_photographe id='idf_photographe' class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_idf_photographe, $pa_reveleur) . "</select></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Date de photo&nbsp;</label></div>" .
		"<div class='form-group col-md-1'>" .
		"<input type=text name=date_photo id=date_photo size=10 maxlength='10' value='" . $pst_date_photo . "' class='form-control'></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Couverture photo&nbsp;</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name=idf_couverture_photo id='idf_couverture_photo' class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_couverture_photo, $pa_couverture_photo) . "</select></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Codif photo&nbsp;</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name=idf_codif_photo id='idf_codif_photo' class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_codif_photo, $pa_codif_photo) . "</select></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Informations complémentaires&nbsp;</label></div>" .
		"<div class='form-group col-md-4' align='left'><textarea class='form-control' rows='3' maxlength=255 name='info_compl'>" . $pst_info_compl . "</textarea></div></div>");
}

function menu_modifier_photo($pconnexionBD, $pi_idf_photo, $pa_reveleur, $pa_couverture_photo, $pa_codif_photo)
{	/*	Affiche le menu de modification d'une photo
															@param object	$pconnexionBD		Identifiant de la connexion de base
															@param integer	$pi_idf_photo		Identifiant de la prise de photo à modifier 
															@param array	$pa_reveleur		Tableau des releveurs		*/

	list($i_idf_photographe, $st_date_photo, $pi_couverture_photo, $pi_codif_photo, $st_info_compl)
		= $pconnexionBD->sql_select_listeUtf8("select idf_photographe, " .
			"       case when date_photo = str_to_date('0000/00/00', '%Y/%m/%d') then '' " .
			"            else date_format(date_photo, '%d/%m/%Y') " .
			"            end as date_photo, " .
			"       idf_couverture_photo, idf_codif_photo, info_complementaires " .
			"from liasse_photo " .
			"where idf=$pi_idf_photo");
	print('<form id=majPhoto method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="MODIFIER_PHOTO">');
	print("<input type=hidden name=idf_photo value=$pi_idf_photo>");

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Prise de photo</div>');
	print('<div class="panel-body">');
	menu_edition_photo(
		$pconnexionBD,
		$pa_reveleur,
		$pi_idf_photo,
		$i_idf_photographe,
		$st_date_photo,
		$pi_couverture_photo,
		$pi_codif_photo,
		$st_info_compl,
		$pa_couverture_photo,
		$pa_codif_photo
	);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btModifierPhoto class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Modifier</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_ajouter_photo($pconnexionBD, $pa_reveleur, $pa_couverture_photo, $pa_codif_photo)
{	/*		Affiche le menu d'ajout d'une photo
															@param object	$pconnexionBD		Identifiant de la connexion de base
															@param array	$pa_reveleur		Tableau des releveurs		*/

	print('<form id=majPhoto method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_PHOTO">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Prise de photo</div>');
	print('<div class="panel-body">');
	menu_edition_photo($pconnexionBD, $pa_reveleur, 0, 0, '', 0, 0, '', $pa_couverture_photo, $pa_codif_photo);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterPhoto class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_ajouter_groupe_photo($pconnexionBD, $pa_reveleur, $pa_couverture_photo, $pa_codif_photo)
{
	print('<form id=groupePhoto method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_GROUPE_PHOTO">');
	print('<input type=hidden name=serie id=serie value="' . $_SESSION['serie_liasse'] . '">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Photo d\'un groupe de liasses de la série ' . $_SESSION['serie_liasse'] . '</div>');
	print('<div class="panel-body">');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Numéros des liasses&nbsp</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="numeros" id=numeros size=80 maxlength=80 value="" class="form-control"></div></div>');
	menu_edition_photo($pconnexionBD, $pa_reveleur, 0, 0, '', 0, 0, '', $pa_couverture_photo, $pa_codif_photo);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=button id=btAjouterGroupePhoto class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btListe class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

/** --------------------------------------- programmation ----------------------------------- **/

function menu_edition_program(
	$pconnexionBD,
	$pa_reveleur,
	$pi_idf_program,
	$pi_idf_intervenant,
	$pi_priorite_program,
	$pst_date_creation,
	$pst_date_echeance,
	$pst_date_reelle_fin,
	$pi_program_releve,
	$pi_program_photo,
	$pst_info_compl,
	$pa_priorite_program
) {	/*	Affiche de la table d'édition d'une programmation
															@param object	$pconnexionBD				Identifiant de la connexion de base
															@param array		$pa_reveleur				Tableau des releveurs
															@param integer	$pi_idf_program				Identifiant de la programmation à modifier
															@param integer	$pi_idf_intervenant			Identifiant de l'intervenant (la personne programmée) 
															@param string	$pst_date_creation			Date de création de la programmation
															@param string	$pst_date_reelle_fin		Date réelle de fin de l'action programmée
															@param integer	$pi_program_releve			booleen programmation d'un relevé Oui/Non
															@param integer	$pi_program_photo			booleen programmation de photos Oui/Non
															@param string	$pst_info_compl				Informations complémentaires sur le relevé	*/
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Intervenant programmé&nbsp;</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name=idf_intervenant id='idf_intervenant' class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_idf_intervenant, $pa_reveleur) . "</select></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Date de création de la programmation&nbsp;</label></div>" .
		"<div class='form-group col-md-1'>" .
		"<input type=text name=date_creation id=date_creation size=10 maxlength='10' value='" . $pst_date_creation . "' class='form-control'></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Date d'échéance de la programmation&nbsp;</label></div>" .
		"<div class='form-group col-md-1'>" .
		"<input type=text name=date_echeance id=date_echeance size=10 maxlength='10' value='" . $pst_date_echeance . "' class='form-control'></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Priorité de la programmation&nbsp;</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name=idf_priorite id='idf_priorite' class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_priorite_program, $pa_priorite_program) . "</select></div></div>");
	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Programmation relevé&nbsp;</label></div>' .
		'<div class="form-group col-md-1" align="left"><div class="form-check">' .
		'<input type="checkbox" class="form-check-input" name=program_releve id=program_releve value="1" ');
	if ($pi_program_releve == 1) {
		print('checked>');
	} else {
		print('unchecked>');
	}
	print("</div></div></div>");
	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Programmation photo&nbsp;</label></div>' .
		'<div class="form-group col-md-1" align="left"><div class="form-check">' .
		'<input type="checkbox" class="form-check-input" name=program_photo id=program_photo value="1" ');
	if ($pi_program_photo == 1) {
		print('checked>');
	} else {
		print('unchecked>');
	}
	print("</div></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Informations complémentaires&nbsp;</label></div>" .
		"<div class='form-group col-md-7' align='left'><textarea class='form-control' rows='3' maxlength=255 name='info_compl'>" . $pst_info_compl . "</textarea></div></div>");

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Date réelle de fin l'action programmée&nbsp;</label></div>" .
		"<div class='form-group col-md-2'>" .
		"<input type=text name=date_reelle_fin id=date_reelle_fin size=10 maxlength='10' value='" . $pst_date_reelle_fin . "' class='form-control'></div>" .
		"<div class='form-group col-md-4'>Si vous renseignez cette date, la programmation disparaîtra de la liste, sans être supprimée</div></div>");
}

function menu_modifier_program($pconnexionBD, $pi_idf_program, $pa_reveleur, $pa_priorite_program)
{		/*	Affiche le menu de modification d'une programmation
															@param object	$pconnexionBD			Identifiant de la connexion de base
															@param integer	$pi_idf_program			Identifiant de la programmation à modifier 
															@param array	$pa_reveleur			Tableau des releveurs
															@param array	$pa_priorite_program	Tableau des prioriés de programmation		*/
	list($i_idf_intervenant, $i_priorite_program, $st_date_creation, $pst_date_echeance, $st_date_reelle_fin, $i_program_releve, $i_program_photo, $st_info_compl)
		= $pconnexionBD->sql_select_listeUtf8("select idf_intervenant, idf_priorite, " .
			"       case when date_creation = str_to_date('0000/00/00', '%Y/%m/%d') then '' " .
			"            else date_format(date_creation, '%d/%m/%Y') " .
			"            end as date_creation, " .
			"       case when date_echeance = str_to_date('0000/00/00', '%Y/%m/%d') then '' " .
			"            else date_format(date_echeance, '%d/%m/%Y') " .
			"            end as date_echeance, " .
			"       case when date_reelle_fin = str_to_date('0000/00/00', '%Y/%m/%d') then '' " .
			"            else date_format(date_reelle_fin, '%d/%m/%Y') " .
			"            end as date_reelle_fin, " .
			"       in_program_releve, in_program_photo, info_complementaires " .
			"from liasse_programmation " .
			"where idf=$pi_idf_program");
	print('<form id=majProgram method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="MODIFIER_PROGRAM">');
	print("<input type=hidden name=idf_program value=$pi_idf_program>");

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Programmation</div>');
	print('<div class="panel-body">');
	menu_edition_program(
		$pconnexionBD,
		$pa_reveleur,
		$pi_idf_program,
		$i_idf_intervenant,
		$i_priorite_program,
		$st_date_creation,
		$pst_date_echeance,
		$st_date_reelle_fin,
		$i_program_releve,
		$i_program_photo,
		$st_info_compl,
		$pa_priorite_program
	);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btModifierProgram class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Modifier</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_ajouter_program($pconnexionBD, $pa_reveleur, $pa_priorite_program)
{		/*	Affiche le menu d'ajout d'une programmation
															@param object		$pconnexionBD			Identifiant de la connexion de base
															@param array		$pa_reveleur			Tableau des releveurs
															@param array		$pa_priorite_program	Tableau des priorités de programmation		*/

	print('<form id=majProgram method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_PROGRAM">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Actions sur la liasse ' . $_SESSION['cote_liasse_gal'] .
		'   -   Notaire(s) ' . $_SESSION['notaires_gal'] . "   -   Période " . $_SESSION['periodes_gal'] . '<br>Programmation</div>');
	print('<div class="panel-body">');
	menu_edition_program($pconnexionBD, $pa_reveleur, 0, 0, 0, date('d/m/Y'), '', '', 0, 0, '', $pa_priorite_program);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterProgram class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

function menu_ajouter_groupe_program($pconnexionBD, $pa_reveleur, $pa_priorite_program)
{
	print('<form id=groupeProgram method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_GROUPE_PROGRAM">');
	print('<input type=hidden name=serie id=serie value="' . $_SESSION['serie_liasse'] . '">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Programmation d\'un groupe de liasses de la série ' . $_SESSION['serie_liasse'] . '</div>');
	print('<div class="panel-body">');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Numéros des liasses&nbsp</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="numeros" id=numeros size=80 maxlength=80 value="" class="form-control"></div></div>');
	menu_edition_program($pconnexionBD, $pa_reveleur, 0, 0, 0, date('d/m/Y'), '', '', 0, 0, '', $pa_priorite_program);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=button id=btAjouterGroupeProgram class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btListe class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

/** --------------------------------------- releveurs ----------------------------------- **/

function menu_ajouter_releveur($pconnexionBD)
{		/*	Affiche le menu d'ajout d'un releveur ou d'un photographe
															@param object	$pconnexionBD		Identifiant de la connexion de base		*/

	$st_requete = "SELECT idf,concat(nom, ' ', prenom) as nom FROM adherent " .
		"where idf not in (select idf_adherent from releveur) " .
		"order by nom";
	$a_adherent = array(0 => '') + $pconnexionBD->liste_valeur_par_clef($st_requete);
	print('<form id="ajoutReleveur" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Ajout d\'un releveur</div>');
	print('<div class="panel-body">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_RELEVEUR">');
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Adhérent&nbsp;</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name=idf_adherent id='idf_adherent' class='js-select-avec-recherche form-control'>" .
		chaine_select_options(0, $a_adherent) . "</select></div></div>");
	print("</div></div>");
	print("<div>&nbsp;</div>");

	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterReleveur class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btMenuGerer class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');

	print('</form>');
}

/** --------------------------------------- outils -------------------------------------- **/

function escape_apostrophe($pst_texte)
{				/*	Elimine les éventuels \ du texte et remplace les ' par \'
															@param string	$pst_texte			Le texte à traiter		*/
	$st_texte = str_replace("\\", "", $pst_texte);
	$st_texte = str_replace("'", "\'", $pst_texte);
	return ($st_texte);
}

function extraction_liste($st_chaine, $st_serie)
{		/*	Extrait un tableau de cotes à partir d'une liste de numéros et d'une série de liasse
															@param string		$st_chaine			Liste des numéros de liasses
															@param string		$st_chaine			Série des liasses		*/
	$a_numeros = explode(",", $st_chaine);
	$a_cotes = array();
	foreach ($a_numeros as $bloc) {
		$i_pos = strpos($bloc, "-");
		if (!$i_pos) {
			array_push($a_cotes, $st_serie . "-" . trim(sprintf("%05d", intval($bloc))));
		} else {
			list($st_deb, $st_fin) = explode("-", $bloc);
			for ($i = intval($st_deb); $i <= intval($st_fin); $i++) {
				array_push($a_cotes, $_SESSION['serie_liasse'] . "-" . trim(sprintf("%05d", $i)));
			}
		}
	}
	return ($a_cotes);
}

/**  
 * compose la liste des cotes au format SQL
 * @param array	$pa_cotes tableau des cotes à transformer en liste de valeurs
 */
function compose_liste_in($pa_cotes)
{
	$st_liste = "";
	foreach ($pa_cotes as $st_cote) {
		if ($st_liste != "") {
			$st_liste .= ", ";
		}
		$st_liste .= "'" . $st_cote . "'";
	}
	return ($st_liste);
}
