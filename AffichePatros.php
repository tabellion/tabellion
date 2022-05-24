<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
session_start();

require_once('Commun/config.php');
require_once('Commun/constantes.php');
require_once('Commun/ConnexionBD.php');
require_once('Commun/commun.php');
require_once('RequeteRecherche.php');
require_once('Commun/PaginationTableau.php');

print('<!DOCTYPE html>');
print("<head>\n");
print('<link rel="shortcut icon" href="images/favicon.ico">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print("<link href='css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='css/select2.min.css' type='text/css' rel='stylesheet'> ");
print("<link href='css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='js/jquery-min.js' type='text/javascript'></script>");
print("<script src='js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='js/select2.min.js' type='text/javascript'></script>");
print("<script src='js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='js/additional-methods.min.js' type='text/javascript'></script>");
print("<script src='js/bootstrap.min.js' type='text/javascript'></script>");  
print("<script type='text/javascript'>");
?>
$(document).ready(function() {

  $.fn.select2.defaults.set( "theme", "bootstrap" );
  
  $(".js-select-avec-recherche").select2();
  
  $('#patronyme').autocomplete({
    source : function(request, response) {
    $.getJSON("./ajax/patronyme_commune.php", { term: request.term}, 
              response);
    },
   minLength: 3
});

$("#patros").validate({
  rules: {
		patronyme: {
			required:true,
			minlength: 2
		},
		rayon_patro: {
			integer: true
		}
  },
  messages: {
	  patronyme: {
		  required: "Le patronyme est obligatoire",
		  minlength: "Saisir au moins deux caract&egrave;res"
      },
	  rayon_patro: {
		  integer: "Le rayon doit &ecirc;tre un entier"
	  }
  },
  errorElement: "em",
  errorPlacement: function ( error, element ) {
	// Add the `help-block` class to the error element
	error.addClass( "help-block" );

	// Add `has-feedback` class to the parent div.form-group
	// in order to add icons to inputs
	element.parents( ".lib_erreur" ).addClass( "has-feedback" );

	if ( element.prop( "type" ) === "checkbox" ) {
		error.insertAfter( element.parent( "label" ) );
	} else {
		error.insertAfter( element );
	}

	// Add the span element, if doesn't exists, and apply the icon classes to it.
		if ( !element.next( "span" )[ 0 ] ) {
			$( "<span class='glyphicon glyphicon-remove form-control-feedback'></span>" ).insertAfter( element );
		}
	},
	success: function ( label, element ) {
		// Add the span element, if doesn't exists, and apply the icon classes to it.
		if ( !$( element ).next( "span" )[ 0 ] ) {
			$( "<span class='glyphicon glyphicon-ok form-control-feedback'></span>" ).insertAfter( $( element ) );
		}
	},
	highlight: function ( element, errorClass, validClass ) {
		$( element ).parents( ".lib_erreur" ).addClass( "has-error" ).removeClass( "has-success" );
		$( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
	},
	unhighlight: function ( element, errorClass, validClass ) {
		$( element ).parents( ".lib_erreur" ).addClass( "has-success" ).removeClass( "has-error" );
		$( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
	}
});  
});

<?php
print("</script>");
print("<title>Base ".SIGLE_ASSO.": Recherche d'un patronyme</title>");
print('</head>');
/**
 *  Affiche le menu de demande
 */ 
function affiche_menu($gi_idf_commune,$gi_rayon,$gi_idf_source,$pst_msg)
{
  global $connexionBD;
  print("<form id=\"patros\"  method=\"post\">");
  $a_communes_acte = $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM commune_acte order by nom");
  $a_sources = $connexionBD->liste_valeur_par_clef("SELECT idf,nom FROM source order by nom collate latin1_german1_ci");
  print("<input type=hidden name=mode value=\"LISTE\">");
  print("<input type=hidden name=idf_source value=0>");
  if (!empty($pst_msg)) print("<div class=\"alert alert-danger\">$pst_msg</div>\n");
  
  print('<div class="form-group row col-md-12">');
  print('<label for="patronyme" class="col-form-label col-md-2">Patronyme</label>');
  print('<div class="col-md-3 lib_erreur">');
  print('<input type=text name=patronyme id=patronyme size=15 maxlength=30 class="form-control">');
  print('</div>');
 
  print('<div class="form-check col-md-4">');
  print('<input type=checkbox name=variantes_pat id=variantes_pat value=oui checked class="form-check-input">');
  print('<label for="variantes_pat" class="form-check-label">Recherche par variantes connues</label>');
  print('</div>');
  print('<button type="submit" class="btn btn-primary col-md-3"><span class="glyphicon glyphicon-search"></span> Rechercher le patronyme</button>');
  print('</div>'); // fin ligne 
  
  print('<div class="form-group row col-md-12">');
  print('<label for="idf_source" class="col-form-label col-md-2">Source</label>');
  print('<div class="col-md-2">');
  print('<select name=idf_source id=idf_source class="form-control">');
  $a_sources[0] = 'Toutes';
  print(chaine_select_options($gi_idf_source,$a_sources));
  print('</select></div>');
  
  print('<label for="idf_commune_patro" class="col-form-label col-md-2">Commune/Paroisse</label>');
  print('<div class="col-md-2">');
  print('<select name="idf_commune_patro" id="idf_commune_patro" class="js-select-avec-recherche form-control">');
  $a_toutes_communes = array(''=>'Toutes')+$a_communes_acte;
  print(chaine_select_options($gi_idf_commune,$a_toutes_communes));
  print('</select></div>');
  print("<div class=\"form-group col-md-4\"><div class=\"input-group\"><span class=\"input-group-addon\">Rayon de recherche:</span><label for=\"rayon_patro\" class=\"sr-only\">Rayon</label><div class=\"lib_erreur\"><input type=text name=rayon_patro id='rayon_patro' size=2 maxlength=2 value=\"$gi_rayon\" class=\"form-control\"></div><span class=\"input-group-addon\">Km</span></div>");
  print("</div>"); // fin ligne 
 
  
  
  
  
  print ("</form>");
  unset($_SESSION['variantes_pat']);
  unset($_SESSION['patronyme']);
  unset($_SESSION['mode']);
  unset($_SESSION['tri_pat']);
  unset($_SESSION['num_page_pat']);
  unset($_SESSION['idf_commune_patro']);
  unset($_SESSION['rayon_patro']); 
}

print("<body>");
print('<div class="container">');

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("Commun/menu.php");

$i_session_idf_source = empty($_SESSION['idf_source']) ? 0 : $_SESSION['idf_source'];
$gi_idf_source=isset($_POST['idf_source']) ? (int) $_POST['idf_source']:$i_session_idf_source;
$i_session_num_page = isset($_SESSION['num_page_pat']) ? $_SESSION['num_page_pat'] : 1;
$gi_num_page = empty($_POST['num_page_pat']) ? $i_session_num_page : (int) $_POST['num_page_pat'];
$st_session_tri = empty($_SESSION['tri_pat']) ? 'patronyme' : $_SESSION['tri_pat'];
$gst_tri = empty($_GET['tri_pat']) ? $st_session_tri : $_GET['tri_pat'];

$i_session_idf_commune = empty($_SESSION['idf_commune_patro']) ? 0 : $_SESSION['idf_commune_patro'];
$gi_idf_commune=isset($_POST['idf_commune_patro']) ? (int) $_POST['idf_commune_patro']:$i_session_idf_commune;
$i_session_rayon = empty($_SESSION['rayon_patro']) ? 0 : $_SESSION['rayon_patro'];
$gi_rayon=isset($_POST['rayon_patro']) ? (int) $_POST['rayon_patro']:$i_session_rayon;

//if (isset ($_GET['tri_pat']))
//   $gi_num_page=1;

$st_session_mode = empty($_SESSION['mode']) ? 'DEMANDE' : $_SESSION['mode'];   
$gst_mode = empty($_POST['mode']) ? $st_session_mode: $_POST['mode'] ;

$st_session_patronyme = empty($_SESSION['patronyme']) ?  '' :$_SESSION['patronyme'];
$st_session_variantes = empty($_SESSION['variantes_pat']) ?  '' :$_SESSION['variantes_pat'];


$gst_patronyme        = empty($_POST['patronyme'])? $st_session_patronyme :substr(trim($_POST['patronyme']),0,30);

$st_variantes = empty($_POST['variantes_pat']) ?  $st_session_variantes :$_POST['variantes_pat'] ;

$_SESSION['patronyme'] = $gst_patronyme;
$_SESSION['variantes_pat'] = $st_variantes;
$_SESSION['idf_source'] = $gi_idf_source;
$_SESSION['num_page_pat'] = $gi_num_page;
$_SESSION['idf_commune_patro'] = $gi_idf_commune;
$_SESSION['rayon_patro'] = $gi_rayon;
   
switch ($gst_mode)
{
	case 'DEMANDE':	  
      affiche_menu($gi_idf_commune,$gi_rayon,$gi_idf_source,'');
   break;
	case 'LISTE':
		$_SESSION['mode']=$gst_mode; 
		$_SESSION['tri_pat']=$gst_tri;
		$gst_patronyme  = preg_replace('/\*+/','%', $gst_patronyme);
		if (($gst_patronyme== '*') || (empty($gst_patronyme)) || (strlen($gst_patronyme)<2))
			affiche_menu($gi_idf_commune,$gi_rayon,$gi_idf_source,"Le patronyme doit comporter au moins deux caract&egrave;res");
		else
		{
			print("<div class=alignCenter><input type=hidden name=mode value=LISTE>");   
			$requeteRecherche = new RequeteRecherche($connexionBD);
			switch ($gst_tri) {
				case 'patronyme': $st_tri_sql = ' order by p.libelle,ca.nom,ta.nom';break;
				case 'commune': $st_tri_sql = ' order by ca.nom,p.libelle,ta.nom';break;
				case 'type_acte': $st_tri_sql = ' order by ta.nom,p.libelle,ca.nom';break;
				case 'nb_actes': $st_tri_sql = ' order by sp.nb_personnes desc,ca.nom,p.libelle';
			break;
		} 
		if (!empty($gi_idf_source))
			$st_requete = "select p.libelle,sp.idf_commune,ca.nom,sp.idf_type_acte,ta.nom,sp.annee_min,sp.annee_max,sp.nb_personnes from stats_patronyme sp join patronyme p on (sp.idf_patronyme
		=p.idf) join commune_acte ca on (sp.idf_commune=ca.idf) join type_acte ta on (sp.idf_type_acte=ta.idf) where idf_source=$gi_idf_source and sp.idf_type_acte in (".IDF_MARIAGE.",".IDF_CM.",".IDF_NAISSANCE.",".IDF_DECES.") and p.libelle ".$requeteRecherche->clause_droite_patronyme($gst_patronyme,$st_variantes,1);
		else
			$st_requete = "select p.libelle,sp.idf_commune,ca.nom,sp.idf_type_acte,ta.nom,sp.annee_min,sp.annee_max,sp.nb_personnes from stats_patronyme sp join patronyme p on (sp.idf_patronyme=p.idf) join commune_acte ca on (sp.idf_commune=ca.idf) join type_acte ta on (sp.idf_type_acte=ta.idf) where sp.idf_type_acte in (".IDF_MARIAGE.",".IDF_CM.",".IDF_NAISSANCE.",".IDF_DECES.") and p.libelle ".$requeteRecherche->clause_droite_patronyme($gst_patronyme,$st_variantes,1);
		if (!empty($gi_idf_commune)) 
			$st_requete .=  " and sp.idf_commune ".$requeteRecherche->clause_droite_commune($gi_idf_commune,$gi_rayon,'oui');
		$st_requete.=$st_tri_sql; 
		//print("Req=$st_requete<br>");
		$a_liste_stats = $connexionBD->sql_select_multiple($st_requete);
		$i_nb_stats =count($a_liste_stats); 
		if ($i_nb_stats!=0)
		{ 
            print("<form name=\"Patros\"  method=\"post\">");
			$pagination = new PaginationTableau(basename(__FILE__),'num_page_pat',$i_nb_stats,NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,array("<a href=\"".basename(__FILE__)."?tri_pat=patronyme\">Patronyme</a>","<a href=\"".basename(__FILE__)."?tri_pat=commune\">Commune</a>","<a href=\"".basename(__FILE__)."?tri_pat=type_acte\">Type d'acte</a>",'Ann&eacute;e minimale','Ann&eacute;e maximale',"<a href=\"".basename(__FILE__)."?tri_pat=nb_actes\">Nombre de personnes</a>"));
			$a_tableau_affichage = array();
			foreach ($a_liste_stats as $a_stat_patro)
			{
				list($st_patronyme,$i_idf_commune,$st_commune,$i_idf_type_acte,$st_type_acte,$i_annee_min,$i_annee_max,$i_nb_pers) = $a_stat_patro;
				if ($gi_idf_source!=0)
					$st_lien_patronyme = "<a href=\"".PAGE_RECHERCHE."?recherche=nouvelle&amp;idf_src=$gi_idf_source&amp;idf_ca=$i_idf_commune&amp;idf_ta=$i_idf_type_acte&amp;var=N&amp;nom=$st_patronyme\">$st_patronyme</a>";
				else
					$st_lien_patronyme ="<a href=\"".PAGE_RECHERCHE."?recherche=nouvelle&amp;idf_ca=$i_idf_commune&amp;idf_ta=$i_idf_type_acte&amp;a_min=$i_annee_min&amp;a_max=$i_annee_max&amp;var=N&amp;nom=$st_patronyme\">$st_patronyme</a>" ;    
				$a_tableau_affichage[] = array($st_lien_patronyme,$st_commune,$st_type_acte,$i_annee_min,$i_annee_max,$i_nb_pers);
			}
			$pagination->init_page_cour($gi_num_page);
			$pagination->affiche_entete_liste_select('Patros');
			$pagination->affiche_tableau_simple($a_tableau_affichage);
			$pagination->affiche_entete_liste_select('Patros');
            print ("</form>");        
		}
		else
			print("<div class=\"text-center alert alert-danger\">Pas de donn&eacute;es</div>\n");
		
		print("<form  method=\"post\">");
		print("<input type=hidden name=mode value=\"DEMANDE\">");
        print('<div class="form-group row"><button type="submit" class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-erase"></span> Rechercher un autre patronyme</button></div>');		
		print ("</form>");
	}
	break;
   default:
     affiche_menu($gi_idf_commune,$gi_rayon,'');  
}
print("</div></body></html>");


?>