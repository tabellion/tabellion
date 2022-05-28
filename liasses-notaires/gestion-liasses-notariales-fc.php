<?php
/*	Affiche la liste des liasses

																@param object $pconnexionBD			 */
function menu_liste($pconnexionBD)
{
	global $gi_num_page_cour;
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
	unset($_SESSION['liasse']);
	$a_numerotation_liasses = array("z", "1", "2", "3", "4", "5", "6", "7", "8", "9");
	print('<div align=center><form id="listeLiasses" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '" method="post">');
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading">Liasses notariales</div>');
	print('<div class="panel-body">');
	print('<table border=0 cellpadding=0 cellspacing=0>');
	print('<tr class="ligne_paire"><td rowspan="4" width="500" align="left">');
	print("<div class=\"row text-left\">");
	print('<label for="serie_liasse" class="col-form-label">Série de liasses&nbsp&nbsp</label>');
	print("<select name='serie_liasse' id=serie_liasse onChange='window.location=\"" . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . "?serie_liasse=\"+this.value;'>" .
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
	$st_requete = "select cote_liasse as idf,cote_liasse,libelle_liasse,libelle_notaires,libelle_annees " .
		"from liasse " .
		"where cote_liasse like '$st_serie_liasse-$numero%' " .
		"order by cote_liasse";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	$i_nb_liasses = count($a_liste_liasses);
	print('<div align=center><input type=hidden name=mode id=mode value="SUPPRIMER">');
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array('Cote', 'Libellé', 'Notaire(s)', 'Periode(s)', 'Modifier', 'Supprimer')
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_tableau_edition_sil(2);
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
		print('<button type=button id=btSupprimerLiasse class="btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span> Supprimer les liasses sélectionnées</button>');
	} else {
		print('<div class="alert alert-danger">Pas de liasses</div>');
		print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	}
	print('<button type=submit id=btAjoutLiasse class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span> Ajouter une liasse</button>');
	print('</div>');

	print('<div class="btn-group col-md-9 col-md-offset-3" role="group">');
	print('<button type=submit id=btAjoutGroupe class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span> Ajouter un groupe de liasses</button>');
	print('<button type=submit id=btCorrigerGroupe class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-new-window"></span> Corriger le notaire d\'un groupe de liasses</button>');
	print('</div>');

	print("</form>");
	print('</div>');
}

/*	Affiche de la table d'édition
	@param string	$pst_cote					Cote de la liasse
	@param string	$pst_libelle				Libellé de la liasse
	@param string	$pst_periodes				Libellé des années couvertes par la liasse
	@param string	$pst_notaires				Libellé du(des) notaire(s)
	@param integer	$pi_depose_ad				Indicateur liasse déposée aux AD
	@param string	$pst_idf_dept_depose_ad		Département de dépose AD
	@param array	$pa_depts_depose_ad			Tableau des départements de dépose AD
	@param integer	$pi_liasse_consult			Indicateur liasse consultable
	@param integer	$pi_idf_forme_liasse		Forme de la liasse 
	@param array	$pa_depts_depose_ad			Tableau des formes de la liasse
	@param integer	$pst_info_compl				Informations complémentaires		
*/
function menu_edition(
	$pst_cote,
	$pst_libelle,
	$pst_periodes,
	$pst_notaires,
	$pi_depose_ad,
	$pst_idf_dept_depose_ad,
	$pa_depts_depose_ad,
	$pi_liasse_consult,
	$pi_idf_forme_liasse,
	$pa_formes_liasses,
	$pst_info_compl,
	$pst_mode
) {
	$st_icone_info = '../images/infos.png';
	print("<input type=hidden name='mode_enr' value='" . $pst_mode . "'>");
	if ($pst_mode == 'M') {
		print('<div class="form-row col-md-12">' .
			'<div class="form-group col-md-4" align="right"><label class="col-form-label">Cote de la liasse&nbsp</label></div>' .
			'<div class="form-group col-md-3" align="left">' . $pst_cote . '</div></div>');
		print('<div class="form-row col-md-12">' .
			'<div class="form-group col-md-4" align="right"><label class="col-form-label">Période(s)&nbsp</label></div>' .
			'<div class="form-group col-md-1" align="left"><a href="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '?smode=LISTE_PERIODE&cote_liasse=' . $pst_cote . '">' .
			'<img src="' . $st_icone_info . '" border=0 alt="détail des périodes"></a></div>' .
			'<div class="form-group col-md-7" align="left">' . $pst_periodes . '</div>' .
			'</div>');
		print('<div class="form-row col-md-12">' .
			'<div class="form-group col-md-4" align="right"><label class="col-form-label">Notaire(s)&nbsp</label></div>' .
			'<div class="form-group col-md-1" align="left"><a href="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '?smode=LISTE_NOTAIRE&cote_liasse=' . $pst_cote . '">' .
			'<img src="' . $st_icone_info . '" border=0 alt="détail des notaires"></a></div>' .
			'<div class="form-group col-md-7" align="left">' . $pst_notaires . '</div>' .
			'</div>');
	} else {
		print("<div class='form-row col-md-12'>" .
			"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Série de la liasse&nbsp</label></div>" .
			"<div class='form-group col-md-1' align=\"left\">" . $_SESSION['serie_liasse'] . "</div></div>");
		print("<div class='form-row col-md-12'>" .
			"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Numéro de la liasse&nbsp</label></div>" .
			"<div class='form-group col-md-1' align=\"left\">");
		print($_SESSION['init_dixm'] == 'z' ? '0' : $_SESSION['init_dixm']);
		print($_SESSION['init_mill'] == 'z' ? '0' : $_SESSION['init_mill']);
		print($_SESSION['init_cent'] == 'z' ? '0' : $_SESSION['init_cent']);
		print($_SESSION['init_dix'] == 'z' ? '0' : $_SESSION['init_dix']);
		print("</div>");
		print("<div class='form-group col-md-1'><input type=text name='numero' id=numero size=1 maxlength='1' width='20' value='' class='form-control'></div></div>");
	}
	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label for="depose_ad" class="col-form-label">Déposée aux AD&nbsp</label></div>' .
		'<div class="form-group col-md-1" align="left"><div class="form-check">' .
		'<input type="checkbox" class="form-check-input" name="depose_ad" id=depose_ad value="1" ');
	if ($pi_depose_ad == 1) {
		print('checked>');
	} else {
		print('unchecked>');
	}
	print("</div></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label for='dept_depose_ad' class='col-form-label'>Département&nbsp</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name='dept_depose_ad' id=dept_depose_ad class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pst_idf_dept_depose_ad, $pa_depts_depose_ad) . "</select></div></div>");
	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label for='liasse_consult' class='col-form-label'>Liasse consultable&nbsp</label></div>" .
		"<div class='form-group col-md-1' align='left'><div class='form-check '>" .
		"<input type='checkbox' class='form-check-input' name='liasse_consult' id=liasse_consult value='1' ");
	if ($pi_liasse_consult == 1) {
		print('checked>');
	} else {
		print('unchecked>');
	}
	print("</div></div></div>");

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label for='forme_liasse' class='col-form-label'>Forme liasse&nbsp</label></div>" .
		"<div class='form-group col-md-3' align='left'><select name='forme_liasse' id=forme_liasse class='js-select-avec-recherche form-control'>" .
		chaine_select_options($pi_idf_forme_liasse, $pa_formes_liasses) . "</select></div></div>");

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Libellé de la liasse</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="libelle" id=libelle maxlength=100 size=80 value="' . $pst_libelle . '" class="form-control"></div>' .
		'</div>');

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label class='col-form-label'>Informations complémentaires&nbsp</label></div>" .
		"<div class='form-group col-md-4' align='left'><textarea class='form-control' rows='4' maxlength=1000 name='info_compl'>" . $pst_info_compl . "</textarea></div></div>");
}

/**
 * Affiche le menu de modification d'une liasse
 * @param object	$pconnexionBD			Identifiant de la connexion de base
 * @param integer	$pst_cote_liasse		Cote de la liasse à modifier 
 * @param array		$pa_depts_depose_ad		Tableau des départements de dépose AD 
 * @param array		$pa_formes_liasses		Tableau des formes de liasses 				
 */
function menu_modifier($pconnexionBD, $pst_cote_liasse, $pa_depts_depose_ad, $pa_formes_liasses)
{	
	list(
		$st_cote, $st_libelle, $st_periodes, $st_notaires, $i_depose_ad, $st_idf_dept_depose_ad, $i_liasse_consult,
		$i_idf_forme_liasse, $st_info_compl
	)
		= $pconnexionBD->sql_select_listeUtf8("select cote_liasse, libelle_liasse, libelle_annees, libelle_notaires, " .
			"       in_liasse_depose_ad, idf_dept_depose_ad, in_liasse_consultable, " .
			"       idf_forme_liasse, info_complementaires " .
			"from liasse " .
			"where cote_liasse='" . $pst_cote_liasse . "'");
	print('<form id=maj_liasses method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print('<input type=hidden name=mode id=mode value="MODIFIER">');
	print("<input type=hidden name=cote_liasse value=$pst_cote_liasse>");

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Liasses notariales</div>');
	print('<div class="panel-body">');
	menu_edition(
		$st_cote,
		$st_libelle,
		$st_periodes,
		$st_notaires,
		$i_depose_ad,
		$st_idf_dept_depose_ad,
		$pa_depts_depose_ad,
		$i_liasse_consult,
		$i_idf_forme_liasse,
		$pa_formes_liasses,
		$st_info_compl,
		'M'
	);
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btModifierLiasse class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Modifier</button>');
	print('<button type=submit formnovalidate id=btRetour class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Retour</button>');
	print('</div>');
	print('</form>');
}


function menu_ajouter($pconnexionBD, $pa_depts_depose_ad, $pa_formes_liasses)
{
	/** Affiche le menu d'ajout d'une liasse
	 * @param array		$pa_depts_depose_ad		Tableau des départements de dépose AD 
	 * @param array		$pa_formes_liasses		Tableau des formes de liasses 			*/
	print('<form id="cre_liasses" method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print("<input type='hidden' name=mode id=mode value='AJOUTER'>");

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Liasses notariales</div>');
	print('<div class="panel-body">');
	menu_edition('', '', '', '', 0, '', $pa_depts_depose_ad, 0, 0, $pa_formes_liasses, '', 'A');
	print("</div></div>");
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterLiasse class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btRetour class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Retour</button>');
	print('</div>');
	print('</form>');
}

function menu_ajouter_groupe($pconnexionBD, $pa_depts_depose_ad, $pa_formes_liasses, $pa_communes)
{
	print('<form id="cre_groupe_liasses" method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print("<input type='hidden' name=mode id=mode value='AJOUTER_GROUPE'>");
	print('<input type="hidden" name=serie id=serie value="' . $_SESSION['serie_liasse'] . '">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Liasses notariales - Ajout d\'un groupe de liasses</div>');
	print('<div class="panel-body">');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Série de la liasse&nbsp</label></div>' .
		'<div class="form-group col-md-1" align="left">' . $_SESSION['serie_liasse'] . '</div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Numéros des liasses&nbsp</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="numeros" id=numeros size=80 maxlength=80 value="" class="form-control"></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Nom du notaire</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="nom" id=nom maxlength=30 size=30 value="" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Prénom du notaire</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="prenom" id=prenom maxlength=50 size=50 value="" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Commentaire</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="commentaire" id=commentaire maxlength=80 size=50 value="" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Nom lieu de l\'étude</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="lieu" id=lieu maxlength=50 size=50 value="" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label for="idf_commune" class="col-form-label">Commune de l\'étude</label></div>' .
		'<div class="form-group col-md-7" align="left"><select name="idf_commune" id=idf_commune class="js-select-avec-recherche form-control">' .
		chaine_select_options(0, $pa_communes) . '</select></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label for="depose_ad" class="col-form-label">Déposée aux AD&nbsp</label></div>' .
		'<div class="form-group col-md-7" align="left"><div class="form-check">' .
		'<input type="checkbox" class="form-check-input" name="depose_ad" id=depose_ad value="1" unchecked>' .
		'</div></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label for="dept_depose_ad" class="col-form-label">Département&nbsp</label></div>' .
		'<div class="form-group col-md-7" align="left"><select name="dept_depose_ad" id=dept_depose_ad class="js-select-avec-recherche form-control">' .
		chaine_select_options(0, $pa_depts_depose_ad) . '</select></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label for="liasse_consult" class="col-form-label">Liasse consultable&nbsp</label></div>' .
		'<div class="form-group col-md-7" align="left"><div class="form-check ">' .
		'<input type="checkbox" class="form-check-input" name="liasse_consult" id=liasse_consult value="1" unchecked>' .
		'</div></div></div>');

	print("<div class='form-row col-md-12'>" .
		"<div class='form-group col-md-4' align='right'><label for='forme_liasse' class='col-form-label'>Forme liasse&nbsp</label></div>" .
		"<div class='form-group col-md-7' align='left'><select name='forme_liasse' id=forme_liasse class='js-select-avec-recherche form-control'>" .
		chaine_select_options(0, $pa_formes_liasses) . '</select></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Informations complémentaires&nbsp</label></div>' .
		'<div class="form-group col-md-7" align="left"><textarea class="form-control" rows="4" maxlength=1000 name="info_compl"></textarea></div></div>');

	print('</div></div>');
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=button id=btAjouterGroupe class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btRetour class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Retour</button>');
	print('</div>');
	print('</form>');
}

function menu_corriger_groupe($pconnexionBD, $pa_communes)
{
	print('<form id="cor_groupe_liasses" method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print("<input type='hidden' name=mode id=mode value='VERIFIER_GROUPE'>");
	print('<input type="hidden" name=serie id=serie value="' . $_SESSION['serie_liasse'] . '">');

	print('<div class="panel panel-primary">' .
		'<div class="panel-heading" align="center">Liasses notariales - Correction d\'un groupe de liasses</div>' .
		'<div class="panel-body">');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Série de la liasse&nbsp</label></div>' .
		'<div class="form-group col-md-1" align="left">' . $_SESSION['serie_liasse'] . '</div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Numéros des liasses&nbsp</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="numeros" id=numeros size=80 maxlength=80 value="" class="form-control"></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Nom du notaire</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="nom" id=nom maxlength=30 size=30 value="" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Prénom du notaire</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="prenom" id=prenom maxlength=50 size=50 value="" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label for="idf_commune" class="col-form-label">Commune de l\'étude</label></div>' .
		'<div class="form-group col-md-7" align="left"><select name="idf_commune" id=idf_commune class="js-select-avec-recherche form-control">' .
		chaine_select_options(0, $pa_communes) . '</select></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Commentaire</label></div>' .
		'<div class="form-group col-md-3"><input type=text name="commentaire" id=commentaire maxlength=80 size=50 value="' . $pst_commentaire . '" class="form-control"></div>' .
		'</div>');

	print('</div></div>');
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btVerifierGroupe class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Vérifier</button>');
	print('<button type=submit formnovalidate id=btRetour class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Retour</button>');
	print('</div>');
	print('</form>');
}

function menu_confirmer_correction_groupe($pconnexionBD, $pa_communes, $pa_cotes, $pst_numeros, $pst_nom, $pst_prenom, $pi_commune, $pst_commentaire)
{
	$gi_get_num_page = empty($_GET['num_page']) ? 1 : (int) $_GET['num_page'];
	$gi_num_page = empty($_POST['num_page']) ? $gi_get_num_page : (int) $_POST['num_page'];
	print('<form id="confirme_cor_groupe_liasses" method="post" class="form-inline" action="' . basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)) . '">');
	print("<input type='hidden' name=mode id=mode value='VERIFIER_GROUPE'>");
	print('<input type="hidden" name=serie id=serie value="' . $_SESSION['serie_liasse'] . '">');

	print('<div class="panel panel-primary">' .
		'<div class="panel-heading" align="center">Liasses notariales - Correction d\'un groupe de liasses</div>' .
		'<div class="panel-body">');
	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Série de la liasse&nbsp</label></div>' .
		'<div class="form-group col-md-1" align="left">' . $_SESSION['serie_liasse'] . '</div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Numéros des liasses&nbsp</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="numeros" id=numeros readonly=true value="' . $pst_numeros . '" class="form-control"></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Nom du notaire</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="nom" id=nom readonly=true value="' . $pst_nom . '" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Prénom du notaire</label></div>' .
		'<div class="form-group col-md-7"><input type=text name="prenom" id=prenom readonly=true value="' . $pst_prenom . '" class="form-control"></div>' .
		'</div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label for="idf_commune" class="col-form-label">Commune de l\'étude</label></div>' .
		'<div class="form-group col-md-7" align="left"><select name="idf_commune" id=idf_commune readonly=true class="form-control">' .
		chaine_select_options($pi_commune, $pa_communes) . '</select></div></div>');

	print('<div class="form-row col-md-12">' .
		'<div class="form-group col-md-4" align="right"><label class="col-form-label">Commentaire</label></div>' .
		'<div class="form-group col-md-3"><input type=text name="commentaire" id=commentaire readonly=true value="' . $pst_commentaire . '" class="form-control"></div>' .
		'</div>');

	print('<br>');
	$st_liste = compose_liste_in($pa_cotes);
	$st_requete = "select liasse.cote_liasse as id, liasse.cote_liasse, liasse_notaire.nom_notaire, liasse_notaire.prenom_notaire, " .
		"commune_acte.nom as nom_commune, liasse_notaire.commentaire, ";
	if ($pst_nom == "") {
		$st_requete .= "liasse_notaire.nom_notaire as nv_nom, ";
	} else {
		$st_requete .= "'" . mb_convert_encoding($pst_nom, 'cp1252', 'UTF8') . "' as nv_nom, ";
	}
	if ($pst_prenom == "") {
		$st_requete .= "liasse_notaire.prenom_notaire as nv_prenom, ";
	} else {
		$st_requete .= "'" . mb_convert_encoding($pst_prenom, 'cp1252', 'UTF8') . "' as nv_prenom, ";
	}
	if ($pi_commune == 0) {
		$st_requete .= "commune_acte.nom as nv_commune, ";
	} else {
		$st_requete .= "'" . mb_convert_encoding($pa_communes[$pi_commune], 'cp1252', 'UTF8') . "' as nv_commune, ";
	}
	if ($pst_commentaire == "") {
		$st_requete .= "liasse_notaire.commentaire as nv_commentaire ";
	} else {
		$st_requete .= "'" . escape_apostrophe(mb_convert_encoding($pst_commentaire, 'cp1252', 'UTF8')) . "' as nv_commentaire ";
	}
	$st_requete .= "from liasse join liasse_notaire on liasse.cote_liasse = liasse_notaire.cote_liasse " .
		"            join commune_acte on commune_acte.idf = liasse_notaire.idf_commune_etude " .
		"where liasse.cote_liasse in (" . $st_liste . ") " .
		"order by cote_liasse";
	$a_liste_liasses = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	$i_nb_liasses = count($a_liste_liasses);
	if ($i_nb_liasses != 0) {
		$pagination = new PaginationTableau(
			basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),
			'num_page',
			$i_nb_liasses,
			10,
			DELTA_NAVIGATION,
			array(
				'Cote', 'Nom notaire', 'Prénom', 'Commune', 'Commentaire',
				'Nouveau nom', 'Nouveau prénom', 'Nouvelle commune', 'Nouveau commentaire'
			)
		);
		$pagination->init_param_bd($pconnexionBD, $st_requete);
		$pagination->init_page_cour($gi_num_page);
		//$pagination->affiche_entete_liens_navlimite();
		$pagination->affiche_tableau_sil(2);
		$pagination->affiche_entete_liens_navlimite();
	} else {
		print('<div class="alert alert-danger">Aucun notaire associé aux liasses sélectionnées ou pas de liasses</div>');
	}

	print('</div></div>');
	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btValiderCorrectionGroupe class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Valider</button>');
	print('<button type=submit formnovalidate id=btRetour class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');
	print('</form>');
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
/*	Extrait un tableau de cotes à partir d'une liste de numéros et d'une série de liasse
															@param string		$pst_chaine			Liste des numéros de liasses
															@param string		$pst_chaine			Série des liasses		*/
function extraction_liste($pst_chaine, $pst_serie)
{
	$a_numeros = explode(",", $pst_chaine);
	$a_cotes = array();
	foreach ($a_numeros as $bloc) {
		$i_pos = strpos($bloc, "-");
		if (!$i_pos) {
			array_push($a_cotes, $pst_serie . "-" . trim(sprintf("%05d", intval($bloc))));
		} else {
			list($st_deb, $st_fin) = explode("-", $bloc);
			for ($i = intval($st_deb); $i <= intval($st_fin); $i++) {
				array_push($a_cotes, $pst_serie . "-" . trim(sprintf("%05d", $i)));
			}
		}
	}
	return ($a_cotes);
}
/*  compose la liste des cotes au format SQL
															@param array		$pa_cotes			tableau des cotes à transformer en liste de valeurs		*/
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
