<?php

require_once('../Commun/Identification.php');
require_once('../Commun/commun.php');
require_once('../Commun/constantes.php');
require_once('../Commun/config.php');
require_once('../Commun/ConnexionBD.php');
require_once('../RequeteRecherche.php');
require_once('../Commun/PaginationTableau.php');
require_once('../Commun/Benchmark.inc');
require_once("../Commun/VerificationDroits.php");
verifie_privilege(DROIT_NOTAIRES);

print('<!DOCTYPE html>');
print('<Head>');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/select2.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='../js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../js/select2.min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print('<script type="text/javascript">');
print('</script>');
?>
<script type='text/javascript'>
$(document).ready(function() {

$("#btImprimer").click(function() {	
	window.open('ImpressionActionsLiasse.php');
	});

$("#btExporter").click(function() {	
	window.open('ExportActionsLiasses.php');
	});

});
</script>
<?php

//print("<script src='../Commun/menu.js' type='text/javascript'></script>");
print("<title>Base AGC: Reponse à; votre recherches d'actions sur les liasses notariales</title>");
print('</Head>');

/* ------------------------------------------------------
   récupération des critères de recherche 
*/
if( isset($_SESSION['pdf']) ) 
	unset($_SESSION['pdf']);
if( isset($_POST['menu']) ) {
	unset($_SESSION['serie_liasse']);
	unset($_SESSION['forme_liasse']);
	unset($_SESSION['repertoire_rla']);
	unset($_SESSION['non_comm_rla']);
	unset($_SESSION['cote_debut_rla']);
	unset($_SESSION['cote_fin_rla']);
	unset($_SESSION['av_1793_rla']);
	unset($_SESSION['sans_notaire_rla']);
	unset($_SESSION['sans_periode_rla']);
	unset($_SESSION['sans_lieu_rla']);
	unset($_SESSION['releve_rla']);
	unset($_SESSION['photo_rla']);
	unset($_SESSION['pas_photo_rla']);
	unset($_SESSION['pas_publi_num_rla']);
	unset($_SESSION['publi_pap_rla']);
	unset($_SESSION['pas_publi_pap_rla']);
	unset($_SESSION['sans_photographe_rla']);
	unset($_SESSION['sans_date_photo_rla']);
	unset($_SESSION['commune_rla']);
	unset($_SESSION['publication_rla']);
	unset($_SESSION['avec_commentaire_rla']);
	$_SESSION['serie_liasse'] = isset($_POST['serie_liasse']) ? $_POST['serie_liasse'] : '2E';
	$_SESSION['forme_liasse_rla'] = isset($_POST['forme_liasse']) ? $_POST['forme_liasse'] : 0;
	$_SESSION['repertoire_rla'] = isset($_POST['repertoire']) ? isset($_POST['repertoire']) : '';
	$_SESSION['non_comm_rla'] = isset($_POST['non_comm']) ? isset($_POST['non_comm']) : '';
	$_SESSION['cote_debut_rla'] = isset($_POST['cote_debut']) && $_POST['cote_debut'] != '' ? sprintf("%05d", $_POST['cote_debut']) : '';
	$_SESSION['cote_fin_rla'] = isset($_POST['cote_fin']) && $_POST['cote_fin'] != '' ? sprintf("%05d", $_POST['cote_fin']) : '';
	$_SESSION['av_1793_rla'] = isset($_POST['av_1793']) ? $_POST['av_1793'] : '';
	$_SESSION['sans_notaire_rla'] = isset($_POST['sans_notaire']) ? $_POST['sans_notaire'] : '';
	$_SESSION['sans_periode_rla'] = isset($_POST['sans_periode']) ? $_POST['sans_periode'] : '';
	$_SESSION['sans_lieu_rla'] = isset($_POST['sans_lieu']) ? $_POST['sans_lieu'] : '';
	$_SESSION['releve_rla'] = isset($_POST['releve']) ? $_POST['releve'] : '';
	$_SESSION['photo_rla'] = isset($_POST['photo']) ? $_POST['photo'] : '';
	$_SESSION['pas_photo_rla'] = isset($_POST['pas_photo']) ? $_POST['pas_photo'] : '';
	$_SESSION['pas_publi_num_rla'] = isset($_POST['pas_publi_num']) ? $_POST['pas_publi_num'] : '';
	$_SESSION['publi_pap_rla'] = isset($_POST['publi_pap']) ? $_POST['publi_pap'] : '';
	$_SESSION['pas_publi_pap_rla'] = isset($_POST['pas_publi_pap']) ? $_POST['pas_publi_pap'] : '';
	$_SESSION['sans_photographe_rla'] = isset($_POST['sans_photographe']) ? $_POST['sans_photographe'] : '';
	$_SESSION['sans_date_photo_rla'] = isset($_POST['sans_date_photo']) ? $_POST['sans_date_photo'] : '';
	$_SESSION['commune_rla'] = isset($_POST['commune']) ? $_POST['commune'] : '';
	$_SESSION['publication_rla'] = isset($_POST['publication']) ? $_POST['publication'] : '';
	$_SESSION['avec_commentaire_rla'] = isset($_POST['avec_commentaire']) ? $_POST['avec_commentaire'] : '';
}
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

$requeteRecherche = new RequeteRecherche($connexionBD);    

$a_communes = $connexionBD->liste_valeur_par_clefUtf8("SELECT idf,nom FROM commune_acte order by nom");
$a_forme_liasse = $connexionBD->liste_valeur_par_clefUtf8("SELECT idf, nom FROM forme_liasse order by nom");

/* ------------------------------------------------------
   constitution des requêtes
*/   
//-----------------------------clauses SELECT et FROM
if( $_SESSION['menu_rla'] == 'publication' ) {
	$st_select = "SELECT publication_papier.nom as titre, ".
	             "       case when publication_papier.date_publication = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
				 "            else date_format(publication_papier.date_publication, '%d/%m/%Y') end as date_publication, ".
	             "       publication_papier.info_complementaires ";
	$st_from = "FROM publication_papier	";
}
elseif( $_SESSION['menu_rla'] == 'publi_pap' ) {
	$st_select = "SELECT publication_papier.nom as titre, ".
	             "       case when publication_papier.date_publication = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
				 "            else date_format(publication_papier.date_publication, '%d/%m/%Y') end as date_publication, ".
	             "       liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
	           "            join liasse_publication_papier	on liasse_publication_papier.cote_liasse	= liasse.cote_liasse ".
			   "            join publication_papier			on publication_papier.idf					= liasse_publication_papier.idf_publication_papier ".
		       " left outer join liasse_releve				on liasse_releve.cote_liasse				= liasse.cote_liasse ";
}
elseif( $_SESSION['menu_rla'] == 'program' ) {
	$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme , ".
	             "       case when liasse_programmation.idf_intervenant=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as intervenant, ".
				 "       programmation_releve.nom as priorite, ".
	             "       case when liasse_programmation.date_echeance = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
				 "            else date_format(liasse_programmation.date_echeance, '%d/%m/%Y') end as date_echeance, ".
	             "       case liasse_programmation.in_program_releve when 1 then 'oui' else '' end as in_program_releve, ".
	             "       case liasse_programmation.in_program_photo when 1 then 'oui' else '' end as in_program_photo ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
	           "            join liasse_programmation		on liasse_programmation.cote_liasse			= liasse.cote_liasse ".
	           " left outer join releveur					on releveur.idf								= liasse_programmation.idf_intervenant ".
		       " left outer join programmation_releve		on programmation_releve.idf					= liasse_programmation.idf_priorite ";
}
elseif( $_SESSION['menu_rla'] == 'releve' ) {
	$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme, ".
	             "       case when liasse.in_liasse_consultable = 1 then 'oui' else '' end as liasse_consultable, ".
	             "       case when liasse_releve.idf_releveur=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as releveur, ".
	             "       case when liasse_publication_papier.idf is null then '' else 'oui' end as publication_papier, ".
	             "       case when liasse_releve.in_publication_numerique = 1 then 'oui' else '' end as publication_numerique, ".
	             "       case when liasse_releve.date_fin_releve = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
				 "            else date_format(liasse_releve.date_fin_releve, '%d/%m/%Y') end as date_fin_releve, liasse_releve.info_complementaires ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
		       " left outer join liasse_releve				on liasse_releve.cote_liasse				= liasse.cote_liasse ".
	           " left outer join liasse_publication_papier	on liasse_publication_papier.cote_liasse	= liasse.cote_liasse ".
		       " left outer join liasse_photo				on liasse_photo.cote_liasse					= liasse.cote_liasse ".
		       " left outer join releveur					on releveur.idf								= liasse_releve.idf_releveur ";
}
elseif( $_SESSION['menu_rla'] == 'complete' ) {
	$st_select = "SELECT distinct liasse.cote_liasse, concat(liasse_notaire.nom_notaire, ' ', liasse_notaire.prenom_notaire, '(', liasse_notaire.commentaire, ')'), commune_acte.nom, ".
	             "       liasse.libelle_annees, forme_liasse.nom as forme, ".
	             "       case when liasse.in_liasse_consultable = 1 then 'oui' else '' end as liasse_consultable, liasse.info_complementaires as info_liasse, ".
	             "       case when liasse_releve.idf is null then '' else 'oui' end as releve, liasse_releve.info_complementaires as info_releve ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
		       " left outer join liasse_notaire				on liasse_notaire.cote_liasse				= liasse.cote_liasse ".
		       " left outer join commune_acte				on commune_acte.idf							= liasse_notaire.idf_commune_etude ".
		       " left outer join liasse_releve				on liasse_releve.cote_liasse				= liasse.cote_liasse ";
}
elseif( $_SESSION['menu_rla'] == 'publi_num' ) {
	$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme, ".
	             "       case when liasse.in_liasse_consultable = 1 then 'oui' else '' end as liasse_consultable, ".
	             "       case when liasse_releve.idf_releveur=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as releveur, ".
	             "       case when liasse_releve.date_fin_releve = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
				 "            else date_format(liasse_releve.date_fin_releve, '%d/%m/%Y') end as date_fin_releve ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
		       " left outer join liasse_releve				on liasse_releve.cote_liasse				= liasse.cote_liasse ".
	           " left outer join liasse_publication_papier	on liasse_publication_papier.cote_liasse	= liasse.cote_liasse ".
		       " left outer join liasse_photo				on liasse_photo.cote_liasse					= liasse.cote_liasse ".
		       " left outer join releveur					on releveur.idf								= liasse_releve.idf_releveur ";
}
elseif( $_SESSION['menu_rla'] == 'pas_releve' ) {
	$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme, ".
	             "       case when liasse.in_liasse_consultable = 1 then 'oui' else '' end as liasse_consultable, ".
	             "       case when liasse_releve.idf_releveur=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as releveur, ".
	             "       case when liasse_releve.in_publication_numerique = 1 then 'oui' else '' end as publication_numerique, ".
	             "       case when liasse_releve.date_fin_releve = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
				 "            else date_format(liasse_releve.date_fin_releve, '%d/%m/%Y') end as date_fin_releve ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
		       " left outer join liasse_releve				on liasse_releve.cote_liasse				= liasse.cote_liasse ".
		       " left outer join liasse_photo				on liasse_photo.cote_liasse					= liasse.cote_liasse ".
		       " left outer join releveur					on releveur.idf								= liasse_releve.idf_releveur ";
}
elseif( $_SESSION['menu_rla'] == 'photo' ) {
	if( $_SESSION['avec_commentaire_rla'] != 'oui' )
		$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme, ".
					"       case liasse.in_liasse_consultable when 1 then 'oui' else '' end as liasse_consultable, ".
					"       case when liasse_publication_papier.idf is null then '' else 'oui' end as publication_papier, ".
					"       case when liasse_releve.in_publication_numerique = 1 then 'oui' else '' end as publication_numerique, ".
					"       case when liasse_photo.idf_photographe=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as photographe, ".
					"       case when liasse_photo.date_photo = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
					"            else date_format(liasse_photo.date_photo, '%d/%m/%Y') end as date_photo, ".
					"       couverture_photo.nom as lib_couverturephoto, codif_photo.nom as lib_codif_photo ";
	else
		$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme, ".
					"       couverture_photo.nom as lib_couverturephoto, liasse_photo.info_complementaires ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
		       " left outer join liasse_photo				on liasse_photo.cote_liasse					= liasse.cote_liasse ".
	           " left outer join releveur 					on releveur.idf								= liasse_photo.idf_photographe ".
		       " left outer join liasse_releve				on liasse_releve.cote_liasse				= liasse.cote_liasse ".
	           " left outer join liasse_publication_papier	on liasse_publication_papier.cote_liasse	= liasse.cote_liasse ".
	           " left outer join couverture_photo			on couverture_photo.idf						= liasse_photo.idf_couverture_photo ".
	           " left outer join codif_photo 				on codif_photo.idf							= liasse_photo.idf_codif_photo ";
}
elseif( $_SESSION['menu_rla'] == 'pas_photo' ) {
	$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme, ".
	             "       case liasse.in_liasse_consultable when 1 then 'oui' else '' end as liasse_consultable, ".
	             "       case when liasse_photo.idf_photographe=0 then 'Inconnu' else concat(releveur.nom, ' ', releveur.prenom) end as photographe, ".
	             "       case when liasse_photo.date_photo = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
				 "            else date_format(liasse_photo.date_photo, '%d/%m/%Y') end as date_photo, ".
				 "       couverture_photo.nom as lib_couverturephoto, codif_photo.nom as lib_codif_photo ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ".
		       " left outer join liasse_photo				on liasse_photo.cote_liasse					= liasse.cote_liasse ".
	           " left outer join releveur 					on releveur.idf								= liasse_photo.idf_photographe ".
	           " left outer join couverture_photo			on couverture_photo.idf						= liasse_photo.idf_couverture_photo ".
	           " left outer join codif_photo 				on codif_photo.idf							= liasse_photo.idf_codif_photo ";
}
else {
	$st_select = "SELECT distinct liasse.cote_liasse, liasse.libelle_notaires, liasse.libelle_annees, forme_liasse.nom as forme ";
	$st_from = "FROM liasse join forme_liasse				on liasse.idf_forme_liasse					= forme_liasse.idf ";
}
if( $_SESSION['commune_rla'] != '' ){
	$st_from .= " left outer join liasse_notaire				on liasse_notaire.cote_liasse				= liasse.cote_liasse ";
}
//-----------------------------clause WHERE, compteur, titre, critères et log
if( $_SESSION['menu_rla'] != 'publication' ){
	$st_where = " where liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
	$st_criteres = "liasses de la série ".$_SESSION['serie_liasse']."\n";
}
else {
	$st_titre = "Publications papier";
	$st_where = "";
	$st_criteres = "lpublications papier\n";
	$st_log = "";
	$st_count = "SELECT count(*) as nb_liasse, count(*) as nb_ligne FROM publication_papier";
}
$st_sous_titre = "";
switch( $_SESSION['menu_rla'] ) {
	case 'releve':
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse']." ayant été relevées";
		if( $_SESSION['cote_debut_rla'] != '' || $_SESSION['cote_fin_rla'] != '' ) {
			if( $_SESSION['cote_debut_rla'] != '' && $_SESSION['cote_fin_rla'] != '' ) {
				$st_sous_titre .= " - cotes entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est compris entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla']."\n";
			}
			elseif( $_SESSION['cote_debut_rla'] != '' ) {
				$st_sous_titre .= " - cotes >= ".$_SESSION['cote_debut_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est supérieur ou égal à; ".$_SESSION['cote_debut_rla']."\n";
			}
			else {
				$st_sous_titre .= " - cotes <= ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est inférieur ou égal à; ".$_SESSION['cote_fin_rla']."\n";
			}
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		if( $_SESSION['cote_debut_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse >= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_debut_rla']."'";
		}
		if( $_SESSION['cote_fin_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse <= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_fin_rla']."'";
		}
		if( $_SESSION['forme_liasse_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_forme_liasse[$_SESSION['forme_liasse_rla']];
			$st_where .= " and liasse.idf_forme_liasse = ".$_SESSION['forme_liasse_rla']." ";
			$st_criteres .= "Uniquement les liasses ".$a_forme_liasse[$_SESSION['forme_liasse_rla']]."\n";
		}
		if( $_SESSION['non_comm_rla'] == '1' ) {
			$st_sous_titre .= " - non communicables";
			$st_where .= " and liasse.in_liasse_consultable = 0 ";
			$st_criteres .= "Uniquement les liasses non communicables\n";
		}		
		if( $_SESSION['photo_rla'] == '1' ) {
			$st_sous_titre .= " - photographiées";
			$st_where .= " and liasse_photo.idf is not null ";
			$st_criteres .= "Uniquement les liasses photographiées\n";
		}
		if( $_SESSION['pas_photo_rla'] == '1' ) {
			$st_sous_titre .= " - pas photographiées";
			$st_where .= " and liasse_photo.idf is null ";
			$st_criteres .= "Uniquement les liasses pas photographiées\n";
		}
		if( $_SESSION['av_1793_rla'] == '1' ) {
			$st_sous_titre .= " - avant 1793";
			$st_where .= " and liasse.cote_liasse in (select distinct cote_liasse from liasse_dates where date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d'))";
			$st_criteres .= "Uniquement les liasses antérieures à; 1793\n";
		}
		$st_where .= " and liasse_releve.cote_liasse is not null ";
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse join liasse_releve	on liasse_releve.cote_liasse = liasse.cote_liasse ".
					"WHERE liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "repertoire=".$_SESSION['repertoire_rla'].", non_comm=".$_SESSION['non_comm_rla'].", photo=".$_SESSION['photo_rla'].", pas photo=".$_SESSION['pas_photo_rla'];
		break;
	case 'complete':
		$st_titre = "Toutes les liasses de la série ".$_SESSION['serie_liasse'];
		$st_sous_titre .= " ";
		$st_criteres .= "Toutes les liasses\n";
		$st_log = "liste complete";
		break;
	case 'pas_releve':
		$st_where .= " and liasse_releve.cote_liasse is null ";
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse']." n'ayant pas été relevées";
		if( $_SESSION['cote_debut_rla'] != '' || $_SESSION['cote_fin_rla'] != '' ) {
			if( $_SESSION['cote_debut_rla'] != '' && $_SESSION['cote_fin_rla'] != '' ) {
				$st_sous_titre .= " - cotes entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est compris entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla']."\n";
			}
			elseif( $_SESSION['cote_debut_rla'] != '' ) {
				$st_sous_titre .= " - cotes >= ".$_SESSION['cote_debut_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est supérieur ou égal à; ".$_SESSION['cote_debut_rla']."\n";
			}
			else {
				$st_sous_titre .= " - cotes <= ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est inférieur ou égal à; ".$_SESSION['cote_fin_rla']."\n";
			}
		}
		if( $_SESSION['cote_debut_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse >= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_debut_rla']."'";
		}
		if( $_SESSION['cote_fin_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse <= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_fin_rla']."'";
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		if( $_SESSION['forme_liasse_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_forme_liasse[$_SESSION['forme_liasse_rla']];
			$st_where .= " and liasse.idf_forme_liasse = ".$_SESSION['forme_liasse_rla']." ";
			$st_criteres .= "Uniquement les liasses ".$a_forme_liasse[$_SESSION['forme_liasse_rla']]."\n";
		}
		if( $_SESSION['non_comm_rla'] == '1' ) {
			$st_sous_titre .= " - non communicables";
			$st_where .= " and liasse.in_liasse_consultable = 0 ";
			$st_criteres .= "Uniquement les liasses non communicables\n";
		}		
		if( $_SESSION['av_1793_rla'] == '1' ) {
			$st_sous_titre .= " - avant 1793";
			$st_where .= " and liasse.cote_liasse in (select distinct cote_liasse from liasse_dates where date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d'))";
			$st_criteres .= "Uniquement les liasses antérieures à; 1793\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse left outer join liasse_releve	on liasse_releve.cote_liasse = liasse.cote_liasse ".
					"WHERE liasse_releve.cote_liasse is null and liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "repertoire=".$_SESSION['repertoire_rla'].", non_comm=".$_SESSION['non_comm_rla'].", av_1793=".$_SESSION['av_1793_rla'].", cote debut=".$_SESSION['cote_debut_rla'].", cote fin=".$_SESSION['cote_fin_rla'];
		break;
	case 'publi_pap':
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse']." ayant fait l'objet d'une publication papier";
		if( $_SESSION['cote_debut_rla'] != '' || $_SESSION['cote_fin_rla'] != '' ) {
			if( $_SESSION['cote_debut_rla'] != '' && $_SESSION['cote_fin_rla'] != '' ) {
				$st_sous_titre .= " - cotes entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est compris entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla']."\n";
			}
			elseif( $_SESSION['cote_debut_rla'] != '' ) {
				$st_sous_titre .= " - cotes >= ".$_SESSION['cote_debut_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est supérieur ou égal à; ".$_SESSION['cote_debut_rla']."\n";
			}
			else {
				$st_sous_titre .= " - cotes <= ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est inférieur ou égal à; ".$_SESSION['cote_fin_rla']."\n";
			}
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		if( $_SESSION['cote_debut_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse >= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_debut_rla']."'";
		}
		if( $_SESSION['cote_fin_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse <= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_fin_rla']."'";
		}
		if( $_SESSION['pas_publi_num_rla'] == '1' ) {
			$st_sous_titre .= " - pas publiées numérique";
			$st_where .= " and (liasse_releve.idf is null or liasse_releve.in_publication_numerique = 0) ";
			$st_criteres .= "Uniquement les liasses pas publiées numérique\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse join liasse_publication_papier	on liasse_publication_papier.cote_liasse = liasse.cote_liasse ".
					"WHERE liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = '';
		break;
	case 'publi_num':
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse']." ayant fait l'objet d'une publication numérique";
		$st_where .= " and liasse_releve.in_publication_numerique = 1 ";
		if( $_SESSION['cote_debut_rla'] != '' || $_SESSION['cote_fin_rla'] != '' ) {
			if( $_SESSION['cote_debut_rla'] != '' && $_SESSION['cote_fin_rla'] != '' ) {
				$st_sous_titre .= " - cotes entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est compris entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla']."\n";
			}
			elseif( $_SESSION['cote_debut_rla'] != '' ) {
				$st_sous_titre .= " - cotes >= ".$_SESSION['cote_debut_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est supérieur ou égal à; ".$_SESSION['cote_debut_rla']."\n";
			}
			else {
				$st_sous_titre .= " - cotes <= ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est inférieur ou égal à; ".$_SESSION['cote_fin_rla']."\n";
			}
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		if( $_SESSION['cote_debut_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse >= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_debut_rla']."'";
		}
		if( $_SESSION['cote_fin_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse <= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_fin_rla']."'";
		}
		if( $_SESSION['publi_pap_rla'] == '1' ) {
			$st_sous_titre .= " - publiées papier";
			$st_where .= " and liasse_publication_papier.idf is not null ";
			$st_criteres .= "Uniquement les liasses publiées papier\n";
		}
		if( $_SESSION['pas_publi_pap_rla'] == '1' ) {
			$st_sous_titre .= " - pas publiées papier";
			$st_where .= " and liasse_publication_papier.idf is null ";
			$st_criteres .= "Uniquement les liasses pas publiées papier\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse join liasse_releve	on liasse_releve.cote_liasse = liasse.cote_liasse ".
					"WHERE liasse_releve.in_publication_numerique = 1 and liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = '';
		break;
	case 'photo':
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse']." ayant été photographiées";
		$st_where .= " and liasse_photo.cote_liasse is not null ";
		if( $_SESSION['cote_debut_rla'] != '' || $_SESSION['cote_fin_rla'] != '' ) {
			if( $_SESSION['cote_debut_rla'] != '' && $_SESSION['cote_fin_rla'] != '' ) {
				$st_sous_titre .= " - cotes entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est compris entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla']."\n";
			}
			elseif( $_SESSION['cote_debut_rla'] != '' ) {
				$st_sous_titre .= " - cotes >= ".$_SESSION['cote_debut_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est supérieur ou égal à; ".$_SESSION['cote_debut_rla']."\n";
			}
			else {
				$st_sous_titre .= " - cotes <= ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est inférieur ou égal à; ".$_SESSION['cote_fin_rla']."\n";
			}
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		if( $_SESSION['cote_debut_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse >= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_debut_rla']."'";
		}
		if( $_SESSION['cote_fin_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse <= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_fin_rla']."'";
		}
		if( $_SESSION['forme_liasse_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_forme_liasse[$_SESSION['forme_liasse_rla']];
			$st_where .= " and liasse.idf_forme_liasse = ".$_SESSION['forme_liasse_rla']." ";
			$st_criteres .= "Uniquement les liasses ".$a_forme_liasse[$_SESSION['forme_liasse_rla']]."\n";
		}
		if( $_SESSION['non_comm_rla'] == '1' ) {
			$st_sous_titre .= " - non communicables";
			$st_where .= " and liasse.in_liasse_consultable = 0 ";
			$st_criteres .= "Uniquement les liasses non communicables\n";
		}		
		if( $_SESSION['pas_publi_pap_rla'] == '1' ) {
			$st_sous_titre .= " - pas publiées papier";
			$st_where .= " and liasse_publication_papier.idf is null ";
			$st_criteres .= "Uniquement les liasses pas publiées papier\n";
		}
		if( $_SESSION['pas_publi_num_rla'] == '1' ) {
			$st_sous_titre .= " - pas publiées numérique";
			$st_where .= " and (liasse_releve.idf is null or liasse_releve.in_publication_numerique = 0) ";
			$st_criteres .= "Uniquement les liasses pas publiées numérique\n";
		}
		if( $_SESSION['sans_photographe_rla'] == '1' ) {
			$st_sous_titre .= " - pas de photographe";
			$st_where .= " and liasse_photo.idf_photographe = 0 ";
			$st_criteres .= "Uniquement les liasses sans photographe\n";
		}
		if( $_SESSION['sans_date_photo_rla'] == '1' ) {
			$st_sous_titre .= " - pas de date de photo";
			$st_where .= " and liasse_photo.date_photo = str_to_date('0000/00/00', '%Y/%m/%d') ";
			$st_criteres .= "Uniquement les liasses sans date de photo\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse join liasse_photo	on liasse_photo.cote_liasse = liasse.cote_liasse ".
					"WHERE liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "repertoire=".$_SESSION['repertoire_rla'].", non_comm=".$_SESSION['non_comm_rla'];
		break;
	case 'pas_photo':
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse']." n'ayant pas été photographiées";
		$st_where .= " and liasse_photo.cote_liasse is null ";
		if( $_SESSION['cote_debut_rla'] != '' || $_SESSION['cote_fin_rla'] != '' ) {
			if( $_SESSION['cote_debut_rla'] != '' && $_SESSION['cote_fin_rla'] != '' ) {
				$st_sous_titre .= " - cotes entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est compris entre ".$_SESSION['cote_debut_rla']." et ".$_SESSION['cote_fin_rla']."\n";
			}
			elseif( $_SESSION['cote_debut_rla'] != '' ) {
				$st_sous_titre .= " - cotes >= ".$_SESSION['cote_debut_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est supérieur ou égal à; ".$_SESSION['cote_debut_rla']."\n";
			}
			else {
				$st_sous_titre .= " - cotes <= ".$_SESSION['cote_fin_rla'];
				$st_criteres .= "Uniquement les liasses dont le numéro est inférieur ou égal à; ".$_SESSION['cote_fin_rla']."\n";
			}
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		if( $_SESSION['cote_debut_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse >= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_debut_rla']."'";
		}
		if( $_SESSION['cote_fin_rla'] != '' ) {
			$st_where .= " and liasse.cote_liasse <= '".$_SESSION['serie_liasse']."-".$_SESSION['cote_fin_rla']."'";
		}
		if( $_SESSION['forme_liasse_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_forme_liasse[$_SESSION['forme_liasse_rla']];
			$st_where .= " and liasse.idf_forme_liasse = ".$_SESSION['forme_liasse_rla']." ";
			$st_criteres .= "Uniquement les liasses ".$a_forme_liasse[$_SESSION['forme_liasse_rla']]."\n";
		}
		if( $_SESSION['non_comm_rla'] == '1' ) {
			$st_sous_titre .= " - non communicables";
			$st_where .= " and liasse.in_liasse_consultable = 0 ";
			$st_criteres .= "Uniquement les liasses non communicables\n";
		}		
		if( $_SESSION['av_1793_rla'] == '1' ) {
			$st_sous_titre .= " - avant 1793";
			$st_where .= " and liasse.cote_liasse in (select distinct cote_liasse from liasse_dates where date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d'))";
			$st_criteres .= "Uniquement les liasses antérieures à; 1793\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse left outer join liasse_photo	on liasse_photo.cote_liasse = liasse.cote_liasse ".
					"WHERE liasse_photo.cote_liasse is null and liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "repertoire=".$_SESSION['repertoire_rla'].", non_comm=".$_SESSION['non_comm_rla'].", av_1793=".$_SESSION['av_1793_rla'].", cote debut=".$_SESSION['cote_debut_rla'].", cote fin=".$_SESSION['cote_fin_rla'];
		break;
	case 'repert':
		$st_where .= " and liasse.idf_forme_liasse = 9 ";
		$st_titre = "Répertoires de la série ".$_SESSION['serie_liasse'];
		if( $_SESSION['av_1793_rla'] == '1' ) {
			$st_sous_titre .= " - avant 1793";
			$st_where .= " and liasse.cote_liasse in (select distinct cote_liasse from liasse_dates where date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d'))";
			$st_criteres .= "Uniquement les liasses antérieures à; 1793\n";
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse WHERE liasse.idf_forme_liasse = 9 and liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "av_1793=".$_SESSION['av_1793_rla'];
		break;
	case 'sans':
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse'];
		if( $_SESSION['sans_notaire_rla'] == '1' ) {
			$st_sous_titre .= " - sans notaire";
			$st_where .= " and liasse.libelle_notaires = '' ";
			$st_criteres .= "Liasses sans notaire\n";
		}
		if( $_SESSION['sans_periode_rla'] == '1' ) {
			$st_sous_titre .= " - sans dates";
			$st_where .= " and liasse.libelle_annees = '' ";
			$st_criteres .= "Liasses sans dates\n";
		}
		if( $_SESSION['sans_lieu_rla'] == '1' ) {
			$st_sous_titre .= " - sans lieu";
			$st_where .= " and liasse.cote_liasse in (select distinct cote_liasse from liasse_notaire where idf_commune_etude = 0) ";
			$st_criteres .= "Liasses sans lieu\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse ".
					"WHERE (libelle_notaires = '' or libelle_annees = '' or cote_liasse in (select distinct cote_liasse from liasse_notaire where idf_commune_etude = 0)) ".
					"and liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "sans_notaire=".$_SESSION['sans_notaire_rla'].", non_comm=".$_SESSION['sans_periode_rla'].", sans_lieu=".$_SESSION['sans_lieu_rla'];
		break;
	case 'non_comm':
		$st_titre = "Liasses de la série ".$_SESSION['serie_liasse']." non communicables";
		$st_where .= " and liasse.in_liasse_consultable = 0 ";
		if( $_SESSION['av_1793_rla'] == '1' ) {
			$st_sous_titre .= " - avant 1793";
			$st_where .= " and liasse.cote_liasse in (select distinct cote_liasse from liasse_dates where date_debut_periode < str_to_date('1793/01/01', '%Y/%m/%d')) ";
			$st_criteres .= "Uniquement les liasses antérieures à; 1793\n";
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse WHERE in_liasse_consultable = 0 and liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "av_1793=".$_SESSION['av_1793_rla'];
		break;
	case 'program':
		$st_titre = "Programmation sur la série ".$_SESSION['serie_liasse'];
		$st_where .= " and (liasse_programmation.date_reelle_fin is null or liasse_programmation.date_reelle_fin=str_to_date('0000/00/00', '%Y/%m/%d')) ";
		if( $_SESSION['releve_rla'] == '1' ) {
			$st_sous_titre .= " - relevé";
			$st_where .= " and liasse_programmation.in_program_releve = 1 ";
			$st_criteres .= "Uniquement les liasses avec programmation de relevé\n";
		}
		if( $_SESSION['photo_rla'] == '1' ) {
			$st_sous_titre .= " - photo";
			$st_where .= " and liasse_programmation.in_program_photo = 1 ";
			$st_criteres .= "Uniquement les liasses avec programmation de photo\n";
		}
		if( $_SESSION['commune_rla'] != 0 ) {
			$st_sous_titre .= " - ".$a_communes[$_SESSION['commune_rla']];
			$st_where .= " and liasse_notaire.idf_commune_etude = ".$_SESSION['commune_rla']." ";
			$st_criteres .= "Uniquement les liasses de ".$a_communes[$_SESSION['commune_rla']]."\n";
		}
		$st_count = "SELECT count(distinct liasse.cote_liasse) as nb_liasse, count(*) as nb_ligne ".
					"FROM liasse join liasse_programmation	on liasse_programmation.cote_liasse = liasse.cote_liasse ".
					"WHERE liasse.cote_liasse like '".$_SESSION['serie_liasse']."%'";
		$st_log = "releve=".$_SESSION['releve_rla'].", photo=".$_SESSION['photo_rla'];
		break;
}
//-----------------------------clause ORDER
switch( $_SESSION['menu_rla'] ) {
	case 'publication':
		$st_order = " order by titre, date_publication ";
		break;
	case 'publi_pap':
		$st_order = " order by titre, date_publication, cote_liasse ";
		break;
	case 'program':
		$st_order = " order by intervenant, cote_liasse ";
		break;
	default:
		$st_order = " order by cote_liasse ";
		break;
}

/* ------------------------------------------------------
   constitution de la log 
*/   
$gst_adresse_ip = $_SERVER['REMOTE_ADDR'];
$pf=@fopen("$gst_rep_logs/requetes_action_liasse.log",'a');
list($i_sec,$i_min,$i_heure,$i_jmois,$i_mois,$i_annee,$i_j_sem,$i_j_an,$b_hiver)=localtime();
$i_mois++;
$i_annee+=1900;
$st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d",$i_jmois,$i_mois,$i_annee,$i_heure,$i_min,$i_sec);
$st_chaine_log = join(';',array($st_date_log,$_SESSION['ident'],$_SESSION['menu_rla'], $st_log));
@fwrite($pf,"$st_chaine_log\n"); 
@fclose($pf);

/* -------------------------------------------------------------------------------------------------------------------------
   Constitution des requêtes et des compteurs
   ------------------------------------------
*/

if( $_SESSION['menu_rla'] != 'publication' &&  $_SESSION['menu_rla'] != 'complete' ) {
	list($i_nb_liasse_liste, $i_nb_ligne_liste) = $connexionBD->sql_select_liste($st_count);	
	list($i_nb_liasse_tot) = $connexionBD->sql_select_liste("select count(distinct cote_liasse) from liasse where cote_liasse like '".$_SESSION['serie_liasse']."%'");	
	list($i_nb_liasse_extr) = $connexionBD->sql_select_liste("select count(distinct liasse.cote_liasse) $st_from $st_where");	
}

$gst_requete_liasses = "$st_select $st_from $st_where $st_order";
$_SESSION['pdf']['requete'] = $gst_requete_liasses;
$a_liasses=$connexionBD->sql_select_multiple($gst_requete_liasses);
$i_nb_ligne_extr = count($a_liasses);
if( $i_nb_ligne_extr != 0 &&  $_SESSION['menu_rla'] != 'publication' &&  $_SESSION['menu_rla'] != 'complete' ) {
	$i_pourc_liste = round($i_nb_liasse_extr / $i_nb_liasse_liste * 100,2);
	$i_pourc_tot = round($i_nb_liasse_extr / $i_nb_liasse_tot * 100,2);
}
else {
	$i_pourc_liste = 0;
	$i_pourc_tot = 0;
	$i_nb_liasse_extr = $i_nb_ligne_extr;
}
$st_sous_titre = substr($st_sous_titre, 2);
$_SESSION['pdf']['titre'] = $st_titre;
$_SESSION['pdf']['sous_titre'] = $st_sous_titre;
$_SESSION['pdf']['nb_liasse'] = $i_nb_liasse_extr;
$_SESSION['pdf']['pourc_liste'] = $i_pourc_liste;
$_SESSION['pdf']['pourc_tot'] = $i_pourc_tot;
//$_SESSION['pdf']['liasses'] = $a_liasses;

/* ------------------------------------------------------
   affichage de l'entête 
   ---------------------
*/   
print('<body>');
print('<div class="container" align="center">');
require_once('../Commun/menu.php');

print('<form action="RecherchesActionsLiasses.php" method="post">');     
print('<div class="panel panel-primary">');
print('<div class="panel-heading">'.$st_titre.'</div>');
print('<div class="panel-body">');

print("<label class='col-form-label'>".$st_sous_titre."</label><br><br>");
if( $_SESSION['menu_rla'] != 'publication' &&  $_SESSION['menu_rla'] != 'complete' ) {
	print("<div class='form-row col-md-12'>");
	print("<div class='form-group col-md-5' align='left'>".$i_nb_liasse_extr." liasses</div>");
	print("<div class='form-group col-md-2' align='center'>".$i_pourc_liste."  % de la liste</div>");
	print("<div class='form-group col-md-5' align='right'>".$i_pourc_tot." % de la série</div>");
	print("</div>");
}
print('</div></div>');
$gi_get_num_page = empty($_GET['num_page']) ? 1 : (int) $_GET['num_page'];
$gi_num_page = empty($_POST['num_page']) ? $gi_get_num_page : (int) $_POST['num_page'];
$etape_prec = getmicrotime();
if ($i_nb_ligne_extr>0)
{
	$etape_prec = getmicrotime();
	$a_tableau = array();
	if( $_SESSION['menu_rla'] == 'publication' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_titre, $st_date_publication, $st_info_compl) = $a_liasse;
			$a_tableau[] = array($st_titre, $st_date_publication, $st_info_compl); 
		}
		// remplacement de NB_LIGNES_PAR_PAGE par 10
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
											array('Titre publication papier','Date','Informations complémentaires'));
	}
	elseif( $_SESSION['menu_rla'] == 'publi_pap' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_titre, $st_date_publication, $st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme) = $a_liasse;
			$a_tableau[] = array($st_titre, $st_date_publication, $st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
											array('Titre publication papier','Date','Cote','Notaire(commune)','Période','Forme de liasse'));
	}
	elseif( $_SESSION['menu_rla'] == 'program' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_intervenant, $st_priorite, 
			     $st_date_echeance, $st_program_releve, $st_program_photo) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_intervenant, $st_priorite, 
			                     $st_date_echeance, $st_program_releve, $st_program_photo); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                            array('Cote','Notaire(commune)','Période','Forme de liasse','Intervenant','Priorité',
											      'Echéance','Programmation relevé','Programmation photo'));
	}
	elseif( $_SESSION['menu_rla'] == 'releve' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_publi_pap, 
			     $st_publi_num, $st_date_fin_releve, $st_info_compl) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_publi_pap, 
			                     $st_publi_num, $st_date_fin_releve, $st_info_compl); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                        array('Cote','Notaire(commune)','Période','Forme de liasse','Consultable', 'Releveur', 'Papier', 
										      'Numérique','Date relevé', 'Informations relevé'));
	}
	elseif( $_SESSION['menu_rla'] == 'complete' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_notaires, $st_commune_etude, $st_libelle_annees, $st_forme, $st_consult, $st_info_liasse, $st_releve,  $st_info_releve) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_notaires, $st_commune_etude, $st_libelle_annees, $st_forme, $st_consult, $st_info_liasse, $st_releve,  $st_info_releve); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                        array('Cote','Notaire','Commune','Période','Forme de liasse','Consultable', 'Informations liasse', 'Relevée', 'Informations relevé'));
	}
	elseif( $_SESSION['menu_rla'] == 'publi_num' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_date_fin_releve) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_releveur, $st_date_fin_releve); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                        array('Cote','Notaire(commune)','Période','Forme de liasse','Consultable', 'Releveur', 'Date relevé'));
	}
	elseif( $_SESSION['menu_rla'] == 'pas_releve' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_publi_num, $st_date_fin_releve) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                        array('Cote','Notaire(commune)','Période','Forme de liasse','Consultable'));
	}
	elseif( $_SESSION['menu_rla'] == 'photo' && $_SESSION['avec_commentaire_rla'] != '1') {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_publi_pap, $st_publi_num,
			     $st_photographe, $st_date_photo, $st_couverture_photo, $st_codif_photo) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, $st_publi_pap, $st_publi_num,
			                     $st_photographe, $st_date_photo, $st_couverture_photo, $st_codif_photo); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                        array('Cote','Notaire(commune)','Période','Forme de liasse','Consultable','Papier','Numérique','Photographe','Date photo','Couverture','Codification'));
	}
	elseif( $_SESSION['menu_rla'] == 'photo' && $_SESSION['avec_commentaire_rla'] == '1') {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_couverture_photo, $st_info_compl) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_couverture_photo, $st_info_compl); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                        array('Cote','Notaire(commune)','Période','Forme de liasse','Couverture','Commentaires'));
	}
	elseif( $_SESSION['menu_rla'] == 'pas_photo' ) {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult, 
			     $st_photographe, $st_date_photo, $st_couverture_photo, $st_codif_photo) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme, $st_consult); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
                                        array('Cote','Notaire(commune)','Période','Forme de liasse','Consultable'));
	}
	else {
		foreach ($a_liasses as $a_liasse) {
			list($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme) = $a_liasse;
			$a_tableau[] = array($st_cote_liasse, $st_libelle_notaires, $st_libelle_annees, $st_forme); 
		}
		$pagination = new PaginationTableau(basename(filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING)),'num_page',count($a_tableau),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,
											array('Cote','Notaire(commune)','Période','Forme de liasse'));
	}
	$pagination->init_page_cour($gi_num_page);
	$pagination->affiche_entete_liens_navlimite();
	$pagination->affiche_tableau_simple($a_liasses);  
	$pagination->affiche_entete_liens_navlimite();

	if( $_SESSION['menu_rla'] != 'complete' ) {
		print('<button type=submit class="btn btn-sm btn-success" id="btImprimer"><span class="glyphicon glyphicon-print"></span> Imprimer</button>');
	}
	print('<button type=submit class="btn btn-sm btn-warning" id="btExporter"><span class="glyphicon glyphicon-download-alt"></span> Exporter</button>');
}
else {
	print('<div class="alert alert-danger" align="center">');
	print("Aucun résultat<br>");
	print("Vérifiez que vous n'avez pas mis trop de contraintes<br><br>");
	print("Rappel de vos critères: <br><br>");
	print(nl2br($st_criteres));
	print("</div>");
	print('<div class="btn-group col-md-6 col-md-offset-4" role="group">');
}
print('<button type=submit class="btn btn-sm btn-primary" id="btRetour"><span class="glyphicon glyphicon-arrow-left"></span> Retour</button>');
print("</div>");
print ("</form>");
print("</div></body></html>");
//$connexionBD->ferme(); 
?>