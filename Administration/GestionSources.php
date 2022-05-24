<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once '../Commun/config.php';
require_once '../Commun/constantes.php';
require_once('../Commun/Identification.php');

// La page est reservee uniquement aux gens ayant les droits d'import/export
require_once('../Commun/VerificationDroits.php');
verifie_privilege(DROIT_CHARGEMENT);
require_once '../Commun/ConnexionBD.php';
require_once('../Commun/PaginationTableau.php');
require_once('../Commun/commun.php');

print('<!DOCTYPE html>');
print("<head>");
print("<title>Gestion des sources</title>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/jquery.validate.min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>"); 
?>
<script type='text/javascript'>

$(document).ready(function() {
  $("#edition_source").validate({
  rules: {
		nom_source: "required"
    },
    messages: {
		nom_source: {
			required: "Le nom de la source est obligatoire"
      }
		},
    errorElement: "em",
    errorPlacement: function ( error, element ) {
	    // Add the `help-block` class to the error element
	    error.addClass( "help-block" );

	    // Add `has-feedback` class to the parent div.form-group
	    // in order to add icons to inputs
	    element.parents( ".col-md-10" ).addClass( "has-feedback" );

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
		  $( element ).parents( ".col-md-10" ).addClass( "has-error" ).removeClass( "has-success" );
		  $( element ).next( "span" ).addClass( "glyphicon-remove" ).removeClass( "glyphicon-ok" );
	  },
	  unhighlight: function ( element, errorClass, validClass ) {
		  $( element ).parents( ".col-md-10" ).addClass( "has-success" ).removeClass( "has-error" );
		  $( element ).next( "span" ).addClass( "glyphicon-ok" ).removeClass( "glyphicon-remove" );
	   }
  });
    
  $("#suppression_sources").validate({
  rules: {
    "supp[]": { 
                    required: true, 
                    minlength: 1 
            } 
  },
  messages: {
     "supp[]": "Merci de choisir au moins une source à supprimer"
  },
  errorElement: "em",
  errorPlacement: function ( error, element ) {
	// Add the `help-block` class to the error element
	error.addClass( "help-block" );

	if ( element.prop( "type" ) === "checkbox" ) {
		error.insertAfter( element.parent( "label" ) );
	} else {
		error.insertAfter( element );
	}
	},
   highlight: function ( element, errorClass, validClass ) {
	$( element ).parents( ".lib_erreur" ).addClass( "has-error" ).removeClass( "has-success" );
  },
  unhighlight: function (element, errorClass, validClass) {
		$( element ).parents( ".lib_erreur" ).addClass( "has-success" ).removeClass( "has-error" );
  },
  submitHandler: function(form) {
    var sources='';
    $("input:checkbox").each(function(){
      var $this = $(this);
      if($this.is(":checked")){
        sources=sources+' '+$this.attr("id");
      }   
    });
    if (confirm('Etes-vous sûr de supprimer les sources '+sources+' ?')) {
            form.submit();
        }
    }
  });
  
  $("#modifier" ).click(function() {
    $('#mode').val("MODIFIER");
    $("form").submit();
 });

 $("#ajouter" ).click(function() {
    $('#mode').val("AJOUTER");
    $("form").submit();
 });


 $("#annuler" ).click(function() {
    window.location.href = 'GestionSources.php';
 });
    
  
});
</script>
<?php
print('</head>');
print('<body>');
print('<div class="container">');

$gst_post_mode = isset($_POST['mode']) ? $_POST['mode'] : null;
$gst_mode = empty($_POST['mode']) && empty($_GET['mod'])? 'LISTE': $gst_post_mode;

if (isset($_GET['mod']))
{
	if (empty($gst_mode))
		$gst_mode='MENU_MODIFIER';
	$gi_idf_source = (int) $_GET['mod'];
}
else
  $gi_idf_source = isset($_POST['idf_source']) ? (int) $_POST['idf_source']:null;

$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];


/**
 * Affiche la liste des communes
 * @param object $pconnexionBD
 */ 
function menu_liste($pconnexionBD)
{
   global $gi_num_page_cour;
   print('<div class="panel panel-primary">');
   print('<div class="panel-heading">Gestion des sources</div>');
   print('<div class="panel-body">');
   print("<form   method=\"post\" id=\"suppression_sources\" >");
   $st_requete = "select idf,nom from source order by nom";
   $a_liste_sources = $pconnexionBD->liste_valeur_par_clef($st_requete);
   $i_nb_sources = count($a_liste_sources);
   if ($i_nb_sources!=0)
   {        
      $pagination = new PaginationTableau(basename(__FILE__),'num_page',$i_nb_sources,NB_LIGNES_PAR_PAGE,1,array('Source','Modifier','Supprimer'));
      $pagination->init_param_bd($pconnexionBD,$st_requete);
      $pagination->init_page_cour($gi_num_page_cour);
      $pagination->affiche_entete_liens_navigation();
      $pagination->affiche_tableau_edition(basename(__FILE__));
   }
   else
     print('<div class="alert alert-danger">Pas de sources</div>');
   print("<input type=hidden name=mode value=\"SUPPRIMER\">");
   print('<button type=submit class="btn btn-danger col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-trash"></span> Supprimer les sources s&eacute;lectionn&eacute;es</button>'); 
   print("</form>");  
   print("<form   method=\"post\">");  
   print("<input type=hidden name=mode value=\"MENU_AJOUTER\">");
   print('<button type=submit class="btn btn-primary col-md-4 col-md-offset-4"><span class="glyphicon glyphicon-plus"></span> Ajouter une source</button>');   
   print('</form></div></div>');  

}

/**
 * Affiche de la table d'édition
 * @param string $pst_nom_source Nom de la source
 * @param integer $pi_publication_gbk La source doit-elle être publiées sous Généabank (0|1) 
 * @param string $pst_script_demande Script qui fait la demande de l'acte
 * @param boolean $pi_utilise_details Doit-on utiliser le champ "details_supplementaires" 
 * @param string $pst_icone_info icone à afficher si l'acte a des informations 
 * @param string $pst_icone_ninfo icone à afficher si l'acte n'a pas d'information supplémentaires
 * @param string $pst_icone_index icone à afficher si l'acte correspond à une indexation 
 */ 
function menu_edition($pst_nom_source,$pi_publication_gbk,$pst_script_demande,$pi_utilise_details,$pst_icone_info,$pst_icone_ninfo,$pst_icone_index)
{
   global $ga_scripts_demande,$ga_booleen_oui_non,$ga_icones_source;
   
   print('<div class="form-group row">');   
   print('<label for="nom_source" class="col-form-label col-md-2">Nom</label>');
   print('<div class="col-md-10">');
   print("<input type=\"text\" maxlength=50 size=30 name=nom_source id=nom_source value=\"".cp1252_vers_utf8($pst_nom_source)."\" class=\"form-control\">");
   print('</div>');
   print('</div>');
   
   $st_checked = $pi_publication_gbk==1? 'checked': '';
   print('<div class="form-group row">'); 
   print('<label for="publication_geneabank" class="col-form-label form-check-label col-md-2">Publication G&eacute;n&eacute;abank</label>');
   print('<div class="col-md-10">');
   print("<input type=\"checkbox\" class=\"form-check-input\" name=publication_geneabank id=publication_geneabank value=1 $st_checked>");
   print('</div>');
   print('</div>');
   
   print('<div class="form-group row">'); 
   print('<label for="script_demande" class="col-form-label col-md-2">Script de demande</label>');
   print('<div class="col-md-10">');
   print("<select name=script_demande id=script_demande class=\"form-control\">".chaine_select_options_simple($pst_script_demande,$ga_scripts_demande)."</select>");
   print('</div>');
   print('</div>');
   
   $st_checked = $pi_utilise_details==1? 'checked': '';
   print('<div class="form-group row">'); 
   print('<label for="utilise_details" class="col-form-label form-check-label col-md-2">Utilisation des d&eacute;tails suppl&eacute;mentaires</label>');
   print('<div class="col-md-10">');
   print("<input type=\"checkbox\" class=\"form-check-input\" name=utilise_details id=utilise_details value=1 $st_checked>");
   print('</div>');
   print('</div>');
   
   print('<div class="form-group row">'); 
   print('<label for="icone_info" class="col-form-label col-md-2">Ic&ocirc;ne si information</label>');
   print('<div class="col-md-10">');
   print("<select name=icone_info id=icone_info class=\"form-control\">".chaine_select_options_simple($pst_icone_info,$ga_icones_source)."</select>");
   print('</div>');
   print('</div>');
   
   print('<div class="form-group row">'); 
   print('<label for="icone_ninfo" class="col-form-label col-md-2">Ic&ocirc;ne si pas d\'information</label>');
   print('<div class="col-md-10">');
   print("<select name=icone_ninfo id=icone_ninfo class=\"form-control\">".chaine_select_options_simple($pst_icone_ninfo,$ga_icones_source)."</select>");
   print('</div>');
   print('</div>');
   
   print('<div class="form-group row">'); 
   print('<label for="icone_index" class="col-form-label col-md-2">Ic&ocirc;ne si indexation</label>');
   print('<div class="col-md-10">');
   print("<select name=icone_index id=icone_index class=\"form-control\">".chaine_select_options_simple($pst_icone_index,$ga_icones_source)."</select>");
   print('</div>');
   print('</div>');
}

/** Affiche le menu de modification d'une source
 * @param object $pconnexionBD Identifiant de la connexion de base
 * @param integer $pi_idf_source Identifiant de la source à modifier 
 * @param array $pa_cantons liste des cantons 
 */ 
function menu_modifier($pconnexionBD,$pi_idf_source)
{
   list($st_nom_source,$i_publication_gbk,$st_script_demande,$i_utilise_details,$st_icone_info,$st_icone_ninfo,$st_icone_index)=$pconnexionBD->sql_select_liste("select nom,publication_geneabank,script_demande,utilise_ds,icone_info,icone_ninfo,icone_index from source where idf=$pi_idf_source");
   print("<form   method=\"post\" id=\"edition_source\">");
   print("<input type=hidden name=mode value=MODIFIER>");
   print("<input type=hidden name=idf_source value=$pi_idf_source>");
   menu_edition($st_nom_source,$i_publication_gbk,$st_script_demande,$i_utilise_details,$st_icone_info,$st_icone_ninfo,$st_icone_index);
   print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
   print('<button type=button class="btn btn-primary" id="modifier"><span class="glyphicon glyphicon-ok"></span> Modifier</button>');
   print('<button type=button class="btn btn-primary" id="annuler"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
   print('</div>');
   print('</form>');
}

/** Affiche le menu d'ajout d'une source 
 */ 
function menu_ajouter()
{
   print("<form   method=\"post\" id=\"edition_source\">");
   print("<input type=hidden name=mode value=\"AJOUTER\">");
   menu_edition('',0,'',0,'','','');
   print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
   print('<button type=button class="btn btn-primary" id="ajouter"><span class="glyphicon glyphicon-ok"></span> Ajouter</button>');
   print('<button type=button class="btn btn-primary" id="annuler"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
   print('</div>');
   print('</form>');
}


$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");

switch ($gst_mode) {
  case 'LISTE' : menu_liste($connexionBD); 
  break;
  case 'MENU_MODIFIER' :
  menu_modifier($connexionBD,$gi_idf_source);
  break;
  case 'MODIFIER' :
     $st_nom_source = utf8_vers_cp1252(trim($_POST['nom_source']));
     $i_publication_geneabank = isset($_POST['publication_geneabank']) ? $_POST['publication_geneabank']: 0;
     $st_script_demande = trim($_POST['script_demande']);
     $i_utilise_details = isset($_POST['utilise_details']) ? $_POST['utilise_details']: 0;
     $st_icone_info = trim($_POST['icone_info']);
     $st_icone_ninfo = trim($_POST['icone_ninfo']);
     $st_icone_index = trim($_POST['icone_index']);
     $connexionBD->execute_requete("update source set nom='$st_nom_source', publication_geneabank=$i_publication_geneabank,script_demande='$st_script_demande',utilise_ds=$i_utilise_details,icone_info='$st_icone_info',icone_ninfo='$st_icone_ninfo',icone_index='$st_icone_index' where idf=$gi_idf_source");
     menu_liste($connexionBD);  
  break;
  case 'MENU_AJOUTER' : 
  menu_ajouter();
  break;
  case 'AJOUTER':
     $st_nom_source = utf8_vers_cp1252(trim($_POST['nom_source']));
     $i_publication_geneabank = isset($_POST['publication_geneabank']) ? $_POST['publication_geneabank']: 0;
     $st_script_demande = trim($_POST['script_demande']);
     $i_utilise_details = isset($_POST['utilise_details']) ? $_POST['utilise_details']: 0;
     $st_icone_info = trim($_POST['icone_info']);
     $st_icone_ninfo = trim($_POST['icone_ninfo']);
     $st_icone_index = trim($_POST['icone_index']);
     $connexionBD->execute_requete("insert into source(nom,publication_geneabank,script_demande,utilise_ds,icone_info,icone_ninfo,icone_index) values('$st_nom_source',$i_publication_geneabank,'$st_script_demande',$i_utilise_details,'$st_icone_info','$st_icone_ninfo','$st_icone_index')");
     menu_liste($connexionBD);
   break;
   case 'SUPPRIMER':
     $a_liste_sources = $_POST['supp'];
     foreach ($a_liste_sources as $i_idf_source)
     {
        $a_actes = $connexionBD->sql_select_multiple("select ca.nom,type_acte.nom from stats_commune join type_acte on (type_acte.idf=stats_commune.idf_type_acte) join commune_acte ca on (idf_commune=ca.idf) where idf_source=$i_idf_source order by ca.nom,type_acte.nom");
        if (count($a_actes)==0)
        {
          $connexionBD->execute_requete("delete from source where idf=$i_idf_source");
        }
        else
        {
          print('<div class="alert alert-danger">Les actes suivants doivent &ecirc;tre supprim&egrave;s auparavant:</div>');
          $st_nom_source = $connexionBD->sql_select1("select nom from source where idf=$i_idf_source");
          print("<div class=\"align-center\">Source: ".cp1252_vers_utf8($st_nom_source)."</div>");
          print("<table class=\"table table-bordered table-striped\">");
          print("<tr><th>Commune</th><th>Type d'acte</th></tr>\n");
          foreach ($a_actes as $a_acte)
          {
             list($st_commune,$st_type) = $a_acte;
             print("<tr><td>".cp1252_vers_utf8($st_commune)."</td><td>".cp1252_vers_utf8($st_type)."</td></tr>\n");
          }
          print("</table>");          
        } 
     }
     menu_liste($connexionBD);
   break;  
      
}  
print('</div></body></html>');
?>