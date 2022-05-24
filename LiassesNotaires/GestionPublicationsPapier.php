<?php
require_once '../Commun/config.php';
require_once '../Commun/constantes.php';
require_once('../Commun/Identification.php');

// La page est reservee uniquement aux gens ayant les droits d'import/export
require_once('../Commun/VerificationDroits.php');
verifie_privilege(DROIT_NOTAIRES);
require_once '../Commun/ConnexionBD.php';
require_once('../Commun/PaginationTableau.php');
require_once('../Commun/commun.php');

print('<!DOCTYPE html>');
print("<head>");
print("<title>Gestion des publications papier de liasses notiariales</title>");
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
?>
<script type='text/javascript'>
$(document).ready(function() {

jQuery.validator.addMethod(
    "format_date",
    function(value, element) {
		var check 			= true;
		var ListeErreurs	= "";
		var jj				= value.substring(0,2);
		var mm				= value.substring(3,5);
		var aa				= value.substring(6);
		var sep1			= value.substring(2,3);
		var sep2			= value.substring(5,6);
		if ( value != "" ) {
			if ( isNaN(jj) || jj<1 || jj>31 ) {
				check = false;
			}	
			else if ( isNaN(mm) || mm<1 || mm>12 ) {
				check = false;
			}	
			else if ( isNaN(aa) || aa<1980 || aa>2100 ) {
				check = false;
			}	
			else if ( ( mm == 4 || mm == 6 || mm == 9 || mm == 11 ) && jj > 30 ) {
				check = false;
			}	
			else if ( mm == 2 && (aa % 4) == 0 && jj > 29 ){
				check = false;
			}	
			else if ( mm == 2 && (aa % 4) != 0 && jj > 28 ){
				check = false;
			}		
			else if ( sep1 != "/" || sep2 != "/" ) {
				check = false;
			}
		}
		return this.optional(element) || check;
    },
    "La date est incorrecte. Attendu : jj/mm/aaaa"
);
	
jQuery.validator.addMethod(
    "releveur_ou_date",
    function(value, element) {
		var check 		= true;
		var releveur	= $(element).val();
		var dateReleve	= $('#date_fin_releve').val();
		if( releveur == 0 && dateReleve == '' ) {
			check=false;
		}
		return this.optional(element) || check;
    },
    "Indiquer au moins le releveur ou la date de relevé"
);

jQuery.validator.addMethod(
    "lien_publi_select",
    function(value, element) {
		var check 	= true;
		if( $(element).val() == 0 ) {
			check=false;
		}
		return this.optional(element) || check;
    },
    "Sélectionner une publication papier"
);

jQuery.validator.addMethod(
    "date_couverture_codif",
    function(value, element) {
		var check 		= true;
		var datePhoto	= $('#date_photo').val();
		var couverture	= $('#idf_couverture_photo').val();
		var codif		= $('#idf_codif_photo').val();
		if( datePhoto == '' && couverture == 0 && codif == 0 ) {
			check=false;
		}
		return this.optional(element) || check;
    },
    "Indiquer au moins la date de photo, la couverture ou la codification"
);

jQuery.validator.addMethod(
    "intervenant_priorite_program",
    function(value, element) {
		var check 		= true;
		var intervenant	= $('#idf_intervenant').val();
		var priorite	= $('#idf_priorite').val();
		var releve		= $('#program_releve').is(':checked');
		var photo		= $('#program_photo').is(':checked');
		if ( intervenant == 0 && priorite == 0 && !releve && !photo )   {
			check=false;
		}
		return this.optional(element) || check;
    },
    "Indiquer au moins l'intervenant, la priorité ou le type de programmation"
);

$("#majPubli").validate({
  rules: {
		titre:				{ required: true },
		date_publication:	{ required:true, format_date:true }
  },		
  messages: {
		titre:				{ required: "Indiquer le titre de la publication"	},
		date_publication:	{ required: "Indiquer la date de publication",
							  format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" }
  }
});


// --------------------------------------------------------- Publications	
$("#btMenuGerePubli").click(function() {
    $("#mode").val('MENU_GERER_PUBLI'); 
	});
	
$("#btSupprimerPubli").click(function() {
	var chaine="";
	// Un seul élément
	if (document.forms['listePubli'].elements['supp[]'].checked)	{
		chaine+=document.forms['listePubli'].elements['supp[]'].id+"\n";
	}
	// Au moins deux éléments 
	for (var i = 0; i < document.forms['listePubli'].elements['supp[]'].length; i++)  {
		if (document.forms['listePubli'].elements['supp[]'][i].checked)      {
			chaine+=document.forms['listePubli'].elements['supp[]'][i].id+"\n";
		}                                                             
	}
	if (chaine=="")  {
		alert("Pas de publication sélectionnée");
	}
	else  {
		Message="Etes-vous sûr de supprimer ces publications :\n"+chaine+"?";
		if (confirm(Message))        {                                                                                                                                    
			document.forms['listePubli'].submit();
		}
	}
	});
	
$("#btMenuAjouterPubli").click(function() {
    $("#mode").val('MENU_AJOUTER_PUBLI'); 
	});
	
$("#btAjouterPubli").click(function() {
    $("#mode").val('AJOUTER_PUBLI'); 
	});
	
$("#btModifierPubli").click(function() {
    $("#mode").val('MODIFIER_PUBLI'); 
	});
	
});
</script>
<?php

print('</head>');
print("<body>");
print('<div class="container">');

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");

if (isset($_GET['initpub'])) {
	$gst_m1 = 'MENU_GERER_PUBLI';
}
else {
	$gst_m1 = empty($_POST['mode']) ? 'MENU_GERER_PUBLI': $_POST['mode'] ;
}
$gst_mode = isset($_REQUEST['smode']) ? $_REQUEST['smode'] : $gst_m1 ;

if (isset($_GET['mod'])) {
	if(substr($_GET['mod'],0,3) == 'PPA') {
		$gst_mode='MENU_MODIFIER_PUBLI';
		$gi_idf_publication = substr($_GET['mod'], 3,10);
	}
}
$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

$pa_publication = $connexionBD->liste_valeur_par_clef("SELECT idf, concat(nom, ', publi&eacute; le ', ".
                                                      "                   case when date_publication = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
                                                      "                        else date_format(date_publication, '%d/%m/%Y') ".
                                                      "                        end, ', ', ".
													  "                   info_complementaires) as nom ".
													  "FROM publication_papier order by nom");
$pa_publication[0] = '';

require_once('GestionPublicationsPapierFc.php');
switch ($gst_mode) {
	/** -------------------- publication papier --------------------- **/
	case 'MENU_GERER_PUBLI' :
		menu_gerer_publication($connexionBD);
		break;
	case 'MENU_MODIFIER_PUBLI' :
		menu_modifier_publication($connexionBD, $gi_idf_publication);
		break;
	case 'MODIFIER_PUBLI' :     
		$i_idf_publication		= $_POST['idf_publication'];
		$st_nom					= escape_apostrophe(trim($_POST['titre']));;
		$st_date_publication	= $_POST['date_publication'];
		$st_info_compl			= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_nom = mb_convert_encoding($st_nom, 'cp1252', 'UTF8');
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "update publication_papier set ".
		              "    nom='".$st_nom."',  ".
		              "    date_publication=str_to_date('".$st_date_publication."', '%d/%m/%Y'), ".
					  "    info_complementaires='".$st_info_compl."' ".
					  "where idf=".$i_idf_publication."";
		$connexionBD->execute_requete($st_requete);
		menu_gerer_publication($connexionBD);
		break;
	case 'MENU_AJOUTER_PUBLI' : 
		menu_ajouter_publication($connexionBD);
		break;
	case 'AJOUTER_PUBLI':
		$st_nom					= escape_apostrophe(trim($_POST['titre']));;
		$st_date_publication	= $_POST['date_publication'];
		$st_info_compl			= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_nom = mb_convert_encoding($st_nom, 'cp1252', 'UTF8');
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "INSERT INTO `publication_papier`(`nom`, `date_publication`, `info_complementaires`) ".
					  "VALUES ('".$st_nom."', str_to_date('".$st_date_publication."', '%d/%m/%Y'), '".$st_info_compl."')";
		$connexionBD->execute_requete($st_requete);
		menu_gerer_publication($connexionBD);
		break;
	case 'SUPPRIMER_PUBLI':
		$a_liste_publis = $_POST['supp'];
		foreach ($a_liste_publis as $st_idf) {
			$i_idf=substr($st_idf, 3, 6);
			$a_liasses = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse_publication_papier where idf_publication_papier=".$i_idf);
			if (count($a_liasses)==0) {
				$connexionBD->execute_requete("delete from publication_papier where idf=".$i_idf);
			}
			else {
				print("<div align=center>Des liasses sont li&eacute;es &agrave; une des publications s&eacute;lectionn&eacute;es</div><br>");
			} 
		}
		menu_gerer_publication($connexionBD);
		break;
}  
print('</div></body></html>');
$connexionBD->ferme(); 
?>