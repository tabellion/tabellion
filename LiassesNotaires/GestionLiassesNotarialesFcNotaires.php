<?php
/**
 * Affiche la liste des liasses
 * @param object $pconnexionBD
 */ 
function menu_liste_notaire($pconnexionBD, $pst_cote_liasse, $pa_communes)
{
	global $gi_num_page_cour;
	print('<form id="listeNotaires" action="'.basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)).'" method="post">');
	print("<input type=hidden name=cote_liasse value=$pst_cote_liasse>");
	print('<input type=hidden name=mode id=mode value="SUPPRIMER_NOTAIRE">');

	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align=center>Notaires de la liasse '.$pst_cote_liasse.'</div>');
	print('<div class="panel-body">');

	$st_requete = "select concat('NOT', liasse_notaire.idf) as idf, liasse_notaire.nom_notaire, ".
	              "       liasse_notaire.prenom_notaire, commentaire, libelle_lieu, commune_acte.nom ".
	              "from liasse_notaire ".
				  "     left outer join commune_acte on liasse_notaire.idf_commune_etude = commune_acte.idf ".
				  "where liasse_notaire.cote_liasse = '" . $pst_cote_liasse . "' ".
				  "order by liasse_notaire.nom_notaire, liasse_notaire.prenom_notaire";
	$a_liste_dates = $pconnexionBD->sql_select_multipleUtf8($st_requete);
	$i_nb_dates=count($a_liste_dates);
	if ($i_nb_dates!=0)
	{        
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',$i_nb_dates,
		                                    10,DELTA_NAVIGATION,array('Nom','Pr&eacute;nom','Commentaire',
											'Libell&eacute; lieu &eacute;tude', 'Commune associ&eacute;e','Modifier','Supprimer'));
		$pagination->init_param_bd($pconnexionBD,$st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		//$pagination->affiche_entete_liens_navigation();
		$pagination->affiche_tableau_edition_sil(2);
		//$pagination->affiche_entete_liens_navigation();      
	}
	else
		print("<div align=center>Pas de notaire</div>\n");
	print('</div></div>');
	
	print('<div class="btn-group col-md-6 col-md-offset-3" role="group">');
	print('<button type=button id=btSupprimerNotaires class="btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span> Supprimer les notaires sélectionnés</button>');
	print('<button type=submit id=btAjoutNotaire class="btn btn-sm btn-success"><span class="glyphicon glyphicon-new-window"></span> Ajouter un notaire</button>');
	print('<button type=submit id=btRetourLiasse class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Revenir à la liasse</button>');
	print("</div>");

	print('</form>');
}

/**
 * Affiche de la table d'édition
 * @param string  $pst_cote					Cote de la liasse
 * @param integer $pi_idf					Identifiant du notaire pour la liasse
 * @param string  $pst_nom_notaire			Nom du notaire
 * @param string  $pst_prenom_notaire		Prénom du notaire
 * @param string  $pst_commentaire			Commentaire ou précision sur le notaire (sénior, le jeune, ...)
 * @param string  $pst_libelle_lieu			Nom du lieu de l'étude
 * @param integer $pi_commune_etude			Identifiant de la commune de rattachement de l'étude
 * @param array   $pa_communes				Tableau des communes 
 */ 
function menu_edition_notaire($pst_cote, $pi_idf, $pst_nom_notaire, $pst_prenom_notaire, $pst_commentaire, 
                              $pst_libelle_lieu, $pi_commune_etude, $pa_communes)
{
	print('<div class="form-row col-md-12">');
	print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Nom du notaire</label></div>');
	print('<div class="form-group col-md-3"><input type=text name="nom" id=nom maxlength=30 size=30 value="'.$pst_nom_notaire.'" class="form-control"></div>');
	print('</div>');
	
	print('<div class="form-row col-md-12">');
	print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Prénom du notaire</label></div>');
	print('<div class="form-group col-md-3"><input type=text name="prenom" id=prenom maxlength=50 size=50 value="'.$pst_prenom_notaire.'" class="form-control"></div>');
	print('</div>');

	print('<div class="form-row col-md-12">');
	print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Commentaire</label></div>');
	print('<div class="form-group col-md-3"><input type=text name="commentaire" id=commentaire maxlength=80 size=50 value="'.$pst_commentaire.'" class="form-control"></div>');
	print('</div>');

	print('<div class="form-row col-md-12">');
	print('<div class="form-group col-md-4" align="right"><label class="col-form-label">Nom lieu de l\'étude</label></div>');
	print('<div class="form-group col-md-3"><input type=text name="lieu" id=lieu maxlength=50 size=50 value="'.$pst_libelle_lieu.'" class="form-control"></div>');
	print('</div>');

	print('<div class="form-row col-md-12">'.
		  '<div class="form-group col-md-4" align="right"><label for="idf_commune" class="col-form-label">Commune de l\'étude</label></div>'.
		  '<div class="form-group col-md-3" align="left"><select name="idf_commune" id=idf_commune class="js-select-avec-recherche form-control">'.
				chaine_select_options($pi_commune_etude,$pa_communes).'</select></div></div>');
}

/** Affiche le menu de modification d'une commune
 * @param object	$pconnexionBD		Identifiant de la connexion de base
 * @param string	$pst_cote_liasse	Identifiant de la liasse porteuse du notaire
 * @param integer	$pi_idf_notaire		Identifiant du notaire à modifier
 * @param array		$pa_communes		Tableau des communes 
 */ 
function menu_modifier_notaire($pconnexionBD, $pst_cote_liasse, $pi_idf_notaire, $pa_communes)
{
	list($st_nom_notaire, $st_prenom_notaire, $st_commentaire, $st_libelle_lieu, $i_idf_commune_etude)
	=$pconnexionBD->sql_select_listeUtf8("select nom_notaire, prenom_notaire, commentaire, libelle_lieu, idf_commune_etude  ".
	                                 "from liasse_notaire ".
				                     "where idf = " . $pi_idf_notaire);

	print('<form id=maj_notaire method="post" class="form-inline" action="'.basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)).'">');
	print('<input type=hidden name=mode id=mode value="MODIFIER_NOTAIRE">');
	print("<input type=hidden name=idf_notaire value=$pi_idf_notaire>");
	print("<input type=hidden name=cote_liasse value=$pst_cote_liasse>");
	
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Notaire de la liasse '.$pst_cote_liasse.'</div>');
	print('<div class="panel-body">');
	menu_edition_notaire($pst_cote_liasse, $pi_idf_notaire, $st_nom_notaire, $st_prenom_notaire, $st_commentaire, 
	                     $st_libelle_lieu, $i_idf_commune_etude, $pa_communes);
	print("</div></div>");

	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btModifierNotaire class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Modifier</button>');
	print('<button type=submit formnovalidate id=btRetourNotaires class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');
	
	print('</form>');
}

/** Affiche le menu d'ajout d'une liasse
 * @param string	$pst_cote_liasse	Identifiant de la liasse porteuse du notaire
 * @param array		$pa_communes		Tableau des communes 
 */ 
function menu_ajouter_notaire($pst_cote_liasse, $pa_communes)
{
	print('<form id=maj_notaire method="post" class="form-inline" action="'.basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)).'">');
	print('<input type=hidden name=mode id=mode value="AJOUTER_NOTAIRE">');
	print("<input type=hidden name=cote_liasse value=$pst_cote_liasse>");
	
	print('<div class="panel panel-primary">');
	print('<div class="panel-heading" align="center">Notaire de la liasse '.$pst_cote_liasse.'</div>');
	print('<div class="panel-body">');
	menu_edition_notaire($pst_cote_liasse, 0,'','','','',0,$pa_communes);
	print("</div></div>");

	print('<div class="btn-group col-md-6 col-md-offset-5" role="group">');
	print('<button type=submit id=btAjouterNotaire class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-floppy-save"></span> Ajouter</button>');
	print('<button type=submit formnovalidate id=btRetourNotaires class="btn btn-sm btn-primary"><span class="glyphicon glyphicon-arrow-left"></span> Annuler</button>');
	print('</div>');
	
	print('</form>');
}

/** Met à jour le libellé notaires d'une liasse
 * @param object	$pconnexionBD		Identifiant de la connexion de base
 * @param string	$pst_cote_liasse	Identifiant de la liasse porteuse du notaire
 */ 
function maj_libelle_notaire($pconnexionBD, $pst_cote_liasse)
{
	$st_requete = "select distinct concat(liasse_notaire.nom_notaire, ".
	                      "'(', case when commune_acte.nom is null then '' else commune_acte.nom end, ')' )".
	              "from liasse_notaire ".
				  "     left outer join commune_acte on liasse_notaire.idf_commune_etude = commune_acte.idf ".
				  "where cote_liasse='" . $pst_cote_liasse . "'";
	$a_liste_notaires = $pconnexionBD->sql_select($st_requete);
	if (count($a_liste_notaires)!=0)
		$st_libelle = implode("/",$a_liste_notaires);
	else
		$st_libelle='';
  $pconnexionBD->initialise_params(array(':libelle'=>$st_libelle));
	$st_requete = "update liasse set libelle_notaires = :libelle where cote_liasse='" . $pst_cote_liasse . "'";
	$pconnexionBD->execute_requete($st_requete);
}
?>