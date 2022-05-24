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
print("<title>Gestion des actions sur les liasses notariales</title>");
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
print("<script src='./VerifieChampsGestionActionsLiasse.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
$(document).ready(function() {

$.fn.select2.defaults.set( "theme", "bootstrap" );
	
$(".js-select-avec-recherche").select2();

jQuery.validator.addMethod(			"format_date",						function(value, element) {
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
	
jQuery.validator.addMethod(			"releveur_ou_date",					function(value, element) {
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

jQuery.validator.addMethod(			"lien_publi_select",				function(value, element) {
		var check 	= true;
		if( $(element).val() == 0 ) {
			check=false;
		}
		return this.optional(element) || check;
    },
    "Sélectionner une publication papier"
);

jQuery.validator.addMethod(			"date_couverture_codif",			function(value, element) {
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

jQuery.validator.addMethod(			"intervenant_priorite_program",		function(value, element) {
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

jQuery.validator.addMethod(			"groupe_cotes",						function(value, element) {
		var check = true;
		var groupe = $(element).val();
		var tab = groupe.split(',');
		for(bloc of tab)   {
			var pos = bloc.indexOf('-');
			if( pos == -1 ) {
				if( isNaN(bloc) ){
					check=false;
					break;
				}
			}
			else {
				var sbloc1 = bloc.substr(0, pos);
				var sbloc2 = bloc.substr(pos+1);
				if( sbloc2.indexOf('-') != -1 ){
					check=false;
					break;
				}
				if( isNaN(sbloc1) ){
					check=false;
					break;
				}
				if( isNaN(sbloc2) ){
					check=false;
					break;
				}
			}
		}
		return this.optional(element) || check;
    },
    "La liste de cotes doit être composée de n1-n2 ou n1,n2,n3,... ou une combinaison des deux"
);


// --------------------------------------------------------- Navigation
$("#btListe").click(function() {
    $("#modeMenu").val('LISTE'); 
	});
	
$("#btRetourListe").click(function() {
    $("#mode").val('LISTE'); 
	});
	
$("#btMenuGerer").click(function() {
    $("#mode").val('MENU_GERER'); 
	});

$("#btRetourReleve").click(function() {
    $("#mode").val('MENU_AJOUTER_GROUPE_RELEVE'); 
	});
	
// --------------------------------------------------------- Relevés
$("#btSupprimerReleve").click(function() {
	var chaine="";
	// Un seul élément
	if (document.forms['listeReleve'].elements['supp[]'].checked)	{
		chaine+=document.forms['listeReleve'].elements['supp[]'].id+"\n";
	}
	// Au moins deux éléments 
	for (var i = 0; i < document.forms['listeReleve'].elements['supp[]'].length; i++)  {
		if (document.forms['listeReleve'].elements['supp[]'][i].checked)      {
			chaine+=document.forms['listeReleve'].elements['supp[]'][i].id+"\n";
		}                                                             
	}
	if (chaine=="")  {
		alert("Pas de relevé sélectionné");
	}
	else  {
		Message="Etes-vous sûr de supprimer ces relevés :\n"+chaine+"?";
		if (confirm(Message))        {                                                                                                                                    
			document.forms['listeReleve'].submit();
		}
	}
 	});
	
$("#btMenuAjouterReleve").click(function() {
    $("#modeReleve").val('MENU_AJOUTER_RELEVE'); 
	});

$("#btAjouterReleve").click(function() {
    $("#mode").val('AJOUTER_RELEVE'); 
	});

$("#btModifierReleve").click(function() {
    $("#mode").val('MODIFIER_RELEVE'); 
	});
	
$("#btMenuGroupeReleve").click(function() {
    $("#mode").val('MENU_AJOUTER_GROUPE_RELEVE'); 
	});
	
$("#btAjouterGroupeReleve").click(function() {
    $("#mode").val('VERIFIER_GROUPE_RELEVE'); 
	});
	
	
$("#btValiderGroupeReleve").click(function() {
    $("#mode").val('AJOUTER_GROUPE_RELEVE'); 
	});
	
$("#majReleve").validate({
  rules: {
		idf_releveur:		{ releveur_ou_date:true },
		date_fin_releve:	{ format_date:true }
  },		
  messages: {
		idf_releveur:		{ releveur_ou_date: "Indiquer au moins le releveur ou la date de relevé" },
		date_fin_releve:	{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" }
  }
});

$("#groupeReleve").validate({
  rules: {
		numeros:			{ groupe_cotes:true, required:true	 },
		idf_releveur:		{ releveur_ou_date:true },
		date_fin_releve:	{ format_date:true }
  },		
  messages: {
		numeros:			{ groupe_cotes: "La liste des numéros de liasses doit être composée de n1-n2 ou n1,n2,n3,... ou une combinaison des deux",
							  required: "La liste des numéros de liasses est obligatoire"},
		idf_releveur:		{ releveur_ou_date: "Indiquer au moins le releveur ou la date de relevé" },
		date_fin_releve:	{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" }
  }
});

// --------------------------------------------------------- Liens publications	
$("#btSupprimerLienPubli").click(function() {
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
		alert("Pas de lien de publication sélectionné");
	}
	else  {
		Message="Etes-vous sûr de supprimer ces liens de publications :\n"+chaine+"?";
		if (confirm(Message))        {                                                                                                                                    
			document.forms['listePubli'].submit();
		}
	}
    $("#modePubli").val('SUPPRIMER_LIEN_PUBLI'); 
	});
	
$("#btMenuAjouterLienPubli").click(function() {
    $("#modePubli").val('MENU_AJOUTER_LIEN_PUBLI'); 
	});
	
$("#btAjouterLienPubli").click(function() {
    $("#mode").val('AJOUTER_LIEN_PUBLI'); 
	});

$("#btModifierLienPubli").click(function() {
    $("#mode").val('MODIFIER_LIEN_PUBLI'); 
	});
	
$("#majLienPubli").validate({
  rules: {
		idf_publication:{ lien_publi_select: true }
  },		
  messages: {
		idf_publication:{ lien_publi_select: "Sélectionner une publication papier"	}
  }
});

// --------------------------------------------------------- Photos	
$("#btSupprimerPhoto").click(function() {
	var chaine="";
	// Un seul élément
	if (document.forms['listePhoto'].elements['supp[]'].checked)	{
		chaine+=document.forms['listePhoto'].elements['supp[]'].id+"\n";
	}
	// Au moins deux éléments 
	for (var i = 0; i < document.forms['listePhoto'].elements['supp[]'].length; i++)  {
		if (document.forms['listePhoto'].elements['supp[]'][i].checked)      {
			chaine+=document.forms['listePhoto'].elements['supp[]'][i].id+"\n";
		}                                                             
	}
	if (chaine=="")  {
		alert("Pas de photo sélectionné");
	}
	else  {
		Message="Etes-vous sûr de supprimer ces photos :\n"+chaine+"?";
		if (confirm(Message))        {                                                                                                                                    
			document.forms['listePhoto'].submit();
		}
	}
	});
	
$("#btMenuAjouterPhoto").click(function() {
    $("#modePhoto").val('MENU_AJOUTER_PHOTO'); 
	});
	
$("#btAjouterPhoto").click(function() {
    $("#mode").val('AJOUTER_PHOTO'); 
	});

$("#btMenuGroupePhoto").click(function() {
    $("#mode").val('MENU_AJOUTER_GROUPE_PHOTO'); 
	});
	
$("#btAjouterGroupePhoto").click(function() {
	var check = true;
	if( $("#groupePhoto").valid() ) {
		var pcotes = "";
		var groupe = $('#numeros').val();
		var tab = groupe.split(',');
		for(bloc of tab)   {
			var pos = bloc.indexOf('-');
			if( pos == -1 ) {
				pnum="0000"+bloc;
				pcotes += $('#serie').val()+"-"+pnum.substr(pnum.length-5)+", ";
			}
			else {
				var sbloc1 = bloc.substr(0, pos);
				var sbloc2 = bloc.substr(pos+1);
				for(i=parseInt(sbloc1); i<=parseInt(sbloc2); i++ ) {
					pnum="0000"+i.toString();
					pcotes += $('#serie').val()+"-"+pnum.substr(pnum.length-5)+", ";				
				}
			}
		}
		var l=pcotes.length;
		group = pcotes.substr(0, l-2);
		if( check ) {
			check = confirm("Vous avez déclaré la photo des liasses suivantes : "+group+"\nConfirmez-vous ?");
			if( check ) {
				$("#groupePhoto").submit();
			}
		}
	}
	});
	
$("#btModifierPhoto").click(function() {
    $("#mode").val('MODIFIER_PHOTO'); 
	});
	
$("#majPhoto").validate({
  rules: {
		date_photo:		{ format_date:true  },
		idf_codif_photo:{ date_couverture_codif:true }
  },		
  messages: {
		date_photo:		{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" },
		idf_codif_photo:{ date_couverture_codif: "Indiquer au moins la date de photo, la couverture ou la codification"	}
  }
});

$("#groupePhoto").validate({
  rules: {
		numeros:			{ groupe_cotes:true, required:true	 },
		date_photo:			{ format_date:true  },
		idf_codif_photo:	{ date_couverture_codif:true }
  },		
  messages: {
		numeros:			{ groupe_cotes: "La liste des numéros de liasses doit être composée de n1-n2 ou n1,n2,n3,... ou une combinaison des deux",
							  required: "La liste des numéros de liasses est obligatoire"},
		date_photo:			{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" },
		idf_codif_photo:	{ date_couverture_codif: "Indiquer au moins la date de photo, la couverture ou la codification"	}
  }
});

// --------------------------------------------------------- Programmations	
$("#btSupprimerProgram").click(function() {
	var chaine="";
	// Un seul élément
	if (document.forms['listeProgram'].elements['supp[]'].checked)	{
		chaine+=document.forms['listeProgram'].elements['supp[]'].id+"\n";
	}
	// Au moins deux éléments 
	for (var i = 0; i < document.forms['listeProgram'].elements['supp[]'].length; i++)  {
		if (document.forms['listeProgram'].elements['supp[]'][i].checked)      {
			chaine+=document.forms['listeProgram'].elements['supp[]'][i].id+"\n";
		}                                                             
	}
	if (chaine=="")  {
		alert("Pas de programmation sélectionnée");
	}
	else  {
		Message="Etes-vous sûr de supprimer ces programmations :\n"+chaine+"?";
		if (confirm(Message))        {                                                                                                                                    
			document.forms['listeProgram'].submit();
		}
	}
	});
	
$("#btMenuAjouterProgram").click(function() {
    $("#modeProgram").val('MENU_AJOUTER_PROGRAM'); 
	});

$("#btAjouterProgram").click(function() {
    $("#mode").val('AJOUTER_PROGRAM'); 
	});

$("#btMenuGroupeProgram").click(function() {
    $("#mode").val('MENU_AJOUTER_GROUPE_PROGRAM'); 
	});

$("#btAjouterGroupeProgram").click(function() {
	var check = true;
	if( $("#groupeProgram").valid() ) {
		var pcotes = "";
		var groupe = $('#numeros').val();
		var tab = groupe.split(',');
		for(bloc of tab)   {
			var pos = bloc.indexOf('-');
			if( pos == -1 ) {
				pnum="0000"+bloc;
				pcotes += $('#serie').val()+"-"+pnum.substr(pnum.length-5)+", ";
			}
			else {
				var sbloc1 = bloc.substr(0, pos);
				var sbloc2 = bloc.substr(pos+1);
				for(i=parseInt(sbloc1); i<=parseInt(sbloc2); i++ ) {
					pnum="0000"+i.toString();
					pcotes += $('#serie').val()+"-"+pnum.substr(pnum.length-5)+", ";				
				}
			}
		}
		var l=pcotes.length;
		group = pcotes.substr(0, l-2);
		if( check ) {
			check = confirm("Vous avez déclaré la programmation des liasses suivantes : "+group+"\nConfirmez-vous ?");
			if( check ) {
				$("#groupeProgram").submit();
			}
		}
	}
	});

$("#btModifierProgram").click(function() {
    $("#mode").val('MODIFIER_PROGRAM'); 
	});
	
$("#majProgram").validate({
  rules: {
		idf_intervenant:{ intervenant_priorite_program: true },
		date_creation:	{ format_date:true },
		date_echeance:	{ format_date:true },
		date_reelle_fin:{ format_date:true }
  },		
  messages: {
		idf_intervenant:{ intervenant_priorite_program: "Indiquer au moins l'intervenant, la priorité ou le type de programmation"	},
		date_creation:	{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" },
		date_echeance:	{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" },                                                                                              
		date_reelle_fin:{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" }
  }
});

$("#groupeProgram").validate({
  rules: {
		numeros:			{ groupe_cotes:true, required:true	 },
		idf_intervenant:	{ intervenant_priorite_program: true },
		date_creation:		{ format_date:true },
		date_echeance:		{ format_date:true },
		date_reelle_fin:	{ format_date:true }
  },		
  messages: {
		numeros:			{ groupe_cotes: "La liste des numéros de liasses doit être composée de n1-n2 ou n1,n2,n3,... ou une combinaison des deux",
							  required: "La liste des numéros de liasses est obligatoire"},
		idf_intervenant:	{ intervenant_priorite_program: "Indiquer au moins l'intervenant, la priorité ou le type de programmation"	},
		date_creation:		{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" },
		date_echeance:		{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" },                                                                                              
		date_reelle_fin:	{ format_date: "La date est incorrecte. Attendu : jj/mm/aaaa" }
  }
});

// --------------------------------------------------------- Releveurs ou photographes	
$("#btMenuAjouterReleveur").click(function() {
    $("#modeMenu").val('MENU_AJOUTER_RELEVEUR'); 
	});
	
$("#btAjouterReleveur").click(function() {
    $("#mode").val('AJOUTER_RELEVEUR'); 
	});
	
$("#ajoutReleveur").validate({
  rules: {
		numero:			{ required: true,	integer:true },
		depose_ad:		{ depose_avec_dept:true },
		dept_depose_ad:	{ dept_avec_depose:true },
		forme_liasse:	{ required: true }	
  },		
  messages: {
		numero:			{ required: "Vous devez saisir le dernier chiffre du numéro de liasse", integer: "Vous devez saisir un chiffre"	},
		depose_ad:		{ depose_avec_dept: "Le département doit être renseigné pour une liasse déposée aux AD"	},
		dept_depose_ad:	{ dept_avec_depose: "La case 'Déposée aux AD' doit être cochée quand le département est renseigné"	},                                                                                              
		forme_liasse:	{ required: "La forme de la liasse est obligatoire"	}
  }
});

// --------------------------------------------------------- Publications	
$("#btMenuGerePubli").click(function() {
    $("#mode").val('MENU_GERER_PUBLI'); 
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
print('<body><div class="container">');

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");

/*------- détermination du contexte ------------------*/
if (isset($_GET['initpub'])) {
	$gst_m1 = 'MENU_GERER_PUBLI';
}
elseif (isset($_POST['modeReleve'])) {
	$gst_m1 = $_POST['modeReleve']; 
}
elseif (isset($_POST['modePubli'])) {
	$gst_m1 = $_POST['modePubli'];
}
elseif (isset($_POST['modePhoto'])) {
	$gst_m1 = $_POST['modePhoto'];
}
elseif (isset($_POST['modeProgram'])) {
	$gst_m1 = $_POST['modeProgram'];
}
elseif (isset($_POST['modeMenu'])) {
	$gst_m1 = $_POST['modeMenu'];
}
elseif( isset($_GET['num_page_grp_rel'])) {
	$gst_m1 = "VERIFIER_GROUPE_RELEVE_SUITE";
}
else {
	$gst_m1 = empty($_POST['mode']) ? 'LISTE': $_POST['mode'] ;
}
$gst_mode = isset($_REQUEST['smode']) ? $_REQUEST['smode'] : $gst_m1 ;

if (isset($_GET['mod'])) {
	if(substr($_GET['mod'],0,3) == 'REL') {
		$gst_mode='MENU_MODIFIER_RELEVE';
		$gi_idf_releve = substr($_GET['mod'], 3,10);
		}
	elseif(substr($_GET['mod'],0,3) == 'PUB') {
		$gst_mode='MENU_MODIFIER_LIEN_PUBLI';
		$gi_idf_lien_publication = substr($_GET['mod'], 3,10);
	}
	elseif(substr($_GET['mod'],0,3) == 'PPA') {
		$gst_mode='MENU_MODIFIER_PUBLI';
		$gi_idf_publication = substr($_GET['mod'], 3,10);
	}
	elseif(substr($_GET['mod'],0,3) == 'PHO') {
		$gst_mode='MENU_MODIFIER_PHOTO';
		$gi_idf_photo = substr($_GET['mod'], 3,10);
	}
	elseif(substr($_GET['mod'],0,3) == 'PRO') {
		$gst_mode='MENU_MODIFIER_PROGRAM';
		$gi_idf_program = substr($_GET['mod'], 3,10);
	}
	else {
		$gst_mode='MENU_GERER';
		$_SESSION['cote_liasse_gal'] = $_GET['mod'];
		list($_SESSION['periodes_gal'], $_SESSION['notaires_gal'])
		=$connexionBD->sql_select_listeUtf8("select libelle_annees, libelle_notaires from liasse where cote_liasse='".$_SESSION['cote_liasse_gal']."'");
	}
}

/*-------------- numéro de page courante --------------*/
if( isset($_GET['num_page_grp_rel'])) {
	$gi_num_page_cour = $_GET['num_page_grp_rel'];
}
else {
$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];
}

/*------------------ valorisation des tableaux de valeurs -----------------*/
$a_releveur = $connexionBD->liste_valeur_par_clef("SELECT idf,concat(nom, ' ', prenom) as nom FROM releveur order by nom");
$a_releveur[0] = 'Inconnu';
$a_couverture_photo = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM couverture_photo order by idf");
$a_couverture_photo[0] = '';
$a_codif_photo = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM codif_photo order by idf");
$a_codif_photo[0] = '';
$a_priorite_program = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM programmation_releve order by idf");
$a_priorite_program[0] = '';
$pa_publication = $connexionBD->liste_valeur_par_clef("SELECT idf, concat(nom, ', publi&eacute; le ', ".
                                                      "                   case when date_publication = str_to_date('0000/00/00', '%Y/%m/%d') then '' ".
                                                      "                        else date_format(date_publication, '%d/%m/%Y') ".
                                                      "                        end, ', ', ".
													  "                   substr(info_complementaires,1,80)) as nom ".
													  "FROM publication_papier order by nom");
$pa_publication[0] = '';
require_once('GestionActionsLiassesFc.php');

/*--------------- actions -------------------*/
switch ($gst_mode) {
	case 'LISTE' : {
		if( isset($_SESSION['cote_liasse_gal'])) {
			unset($_SESSION['cote_liasse_gal']);
		}
		if( isset($_SESSION['groupe']) ) {
			unset($_SESSION['groupe']);
		}
		menu_liste($connexionBD); 
		break; }
	case 'MENU_GERER' : {
		menu_gerer($connexionBD);
		break; }
	/** -------------------- releve --------------------- **/
	case 'MENU_MODIFIER_RELEVE' : {
		menu_modifier_releve($connexionBD, $gi_idf_releve, $a_releveur);
		break; }
	case 'MODIFIER_RELEVE' :  { 
		$i_idf_releve		= $_POST['idf_releve'];
		$i_idf_releveur		= $_POST['idf_releveur'];
		$st_date_fin_releve	= $_POST['date_fin_releve'];
		$i_publi_num		= empty($_POST['publi_num']) ? 0 : trim($_POST['publi_num']);
		$st_info_compl		= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "update liasse_releve set ".
		              "    idf_releveur=".$i_idf_releveur.", date_fin_releve=str_to_date('".$st_date_fin_releve."', '%d/%m/%Y'), ".
					  "    in_publication_numerique=".$i_publi_num.", info_complementaires='".$st_info_compl."' ".
					  "where idf=".$i_idf_releve."";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'MENU_AJOUTER_RELEVE' : {
		menu_ajouter_releve($connexionBD, $a_releveur);
		break; }
	case 'AJOUTER_RELEVE': {
		$i_idf_releveur		= $_POST['idf_releveur'];
		$st_date_fin_releve	= $_POST['date_fin_releve'];
		$i_publi_num		= empty($_POST['publi_num']) ? 0 : trim($_POST['publi_num']);
		$st_info_compl		= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "INSERT INTO `liasse_releve`(`cote_liasse`, `idf_releveur`, `date_fin_releve`, ".
		              "            `in_publication_numerique`, `info_complementaires`) ".
					  "VALUES ('".$_SESSION['cote_liasse_gal']."', ".$i_idf_releveur.", str_to_date('".$st_date_fin_releve."', '%d/%m/%Y'), ".
					  "        ".$i_publi_num.", '".$st_info_compl."')";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'SUPPRIMER_RELEVE': {
		$a_liste_releves = $_POST['supp'];
		foreach ($a_liste_releves as $st_idf) {
			$i_idf=substr($st_idf, 3, 6);
			$connexionBD->execute_requete("delete from liasse_releve where idf=".$i_idf);
		}
		menu_gerer($connexionBD);
		break; }
	case 'MENU_AJOUTER_GROUPE_RELEVE' : {
		menu_ajouter_groupe_releve($connexionBD, $a_releveur);
		break; }
	case 'VERIFIER_GROUPE_RELEVE' : {
		$_SESSION['groupe']['idf_releveur'] 	= $_POST['idf_releveur'];
		$_SESSION['groupe']['date_fin_releve'] 	= $_POST['date_fin_releve'];
		$_SESSION['groupe']['publi_num']		= empty($_POST['publi_num']) ? 0 : trim($_POST['publi_num']);
		$_SESSION['groupe']['info_compl']		= escape_apostrophe(trim($_POST['info_compl']));
		$_SESSION['groupe']['numeros']			= $_POST['numeros'];
		$_SESSION['groupe']['cotes']	= extraction_liste($_POST['numeros'], $_SESSION['serie_liasse']);
		menu_confirmer_groupe_releve($connexionBD, $_SESSION['groupe']['cotes'], $_SESSION['groupe']['numeros'], 
							         $a_releveur, $_SESSION['groupe']['idf_releveur'], $_SESSION['groupe']['date_fin_releve'], 
									 $_SESSION['groupe']['publi_num'], $_SESSION['groupe']['info_compl']);
		break; }
	case 'VERIFIER_GROUPE_RELEVE_SUITE' : {
		menu_confirmer_groupe_releve($connexionBD, $_SESSION['groupe']['cotes'], $_SESSION['groupe']['numeros'], 
							         $a_releveur, $_SESSION['groupe']['idf_releveur'], $_SESSION['groupe']['date_fin_releve'], 
									 $_SESSION['groupe']['publi_num'], $_SESSION['groupe']['info_compl']);
		break; }
	case 'AJOUTER_GROUPE_RELEVE': {
		$i_idf_releveur		= $_SESSION['groupe']['idf_releveur'];
		$st_date_fin_releve	= $_SESSION['groupe']['date_fin_releve'];
		$i_publi_num		= empty($_SESSION['groupe']['publi_num']) ? 0 : trim($_SESSION['groupe']['publi_num']);
		$st_info_compl		= escape_apostrophe(trim($_SESSION['groupe']['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$a_cotes = $_SESSION['groupe']['cotes'];
		$check=true;
		foreach($a_cotes as $st_cote)     {
			$a_liasse = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse where cote_liasse='".$st_cote."'");
			if( count($a_liasse)==0 ) {
				print('<div align=center class="alert alert-danger">La liasse '.$st_cote.' n\'existe pas. Relevé impossible.</div><br>');
				$check=false;
			}
			else {
				$st_requete = "INSERT INTO `liasse_releve`(`cote_liasse`, `idf_releveur`, `date_fin_releve`, ".
		              "            `in_publication_numerique`, `info_complementaires`) ".
					  "VALUES ('".$st_cote."', ".$i_idf_releveur.", str_to_date('".$st_date_fin_releve."', '%d/%m/%Y'), ".
					  "        ".$i_publi_num.", '".$st_info_compl."')";
				$connexionBD->execute_requete($st_requete);
			}             
		}
		if( $check ) {
			menu_liste($connexionBD);			
		}
		break; }
	case 'MENU_AJOUTER_RELEVEUR' : {
		menu_ajouter_releveur($connexionBD);
		break; }
	case 'AJOUTER_RELEVEUR': {
		$i_idf_adherent		= $_POST['idf_adherent'];
		list($st_nom, $st_prenom) = $connexionBD->sql_select_listeUtf8("select nom, prenom from adherent where idf=".$i_idf_adherent);
		$st_nom = escape_apostrophe(trim($st_nom));
		$st_prenom = escape_apostrophe(trim($st_prenom));
		//---- modif UTF8
		$st_nom = mb_convert_encoding($st_nom, 'cp1252', 'UTF8');
		$st_prenom = mb_convert_encoding($st_prenom, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "INSERT INTO `releveur`(`idf_adherent`, `nom`, `prenom`) ".
					  "VALUES (".$i_idf_adherent.", '".$st_nom."', '".$st_prenom."')";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	/** -------------------- publication papier --------------------- **/
	case 'MENU_MODIFIER_LIEN_PUBLI' : {
		menu_modifier_lien_publication($connexionBD, $gi_idf_lien_publication, $pa_publication);
		break; }
	case 'MODIFIER_LIEN_PUBLI' : {
		$i_idf_lien_publication		= $_POST['idf_lien_publi'];
		$i_idf_publication_papier	= $_POST['idf_publication'];
		$st_requete = "update liasse_publication_papier set ".
		              "    idf_publication_papier=".$i_idf_publication_papier." ".
					  "where idf=".$i_idf_lien_publication."";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'MENU_AJOUTER_LIEN_PUBLI' : {
		menu_ajouter_lien_publication($connexionBD, $pa_publication);
		break; }
	case 'AJOUTER_LIEN_PUBLI': {
		$i_idf_publication_papier	= $_POST['idf_publication'];
		$st_requete = "INSERT INTO `liasse_publication_papier`(`cote_liasse`, `idf_publication_papier`) ".
					  "VALUES ('".$_SESSION['cote_liasse_gal']."', ".$i_idf_publication_papier.")";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'SUPPRIMER_LIEN_PUBLI': {
		$a_liste_publis = $_POST['supp'];
		foreach ($a_liste_publis as $st_idf) {
			$i_idf=substr($st_idf, 3, 6);
			$connexionBD->execute_requete("delete from liasse_publication_papier where idf=".$i_idf);
		}
		menu_gerer($connexionBD);
		break; }
	case 'MENU_GERER_PUBLI' : {
		menu_gerer_publication($connexionBD);
		break; }
	case 'MENU_MODIFIER_PUBLI' :    {
		menu_modifier_publication($connexionBD, $gi_idf_publication);
		break; }
	case 'MODIFIER_PUBLI' : {
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
		break; }
	case 'MENU_AJOUTER_PUBLI' : {
		menu_ajouter_publication($connexionBD);
		break; }
	case 'AJOUTER_PUBLI': {
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
		break; }
	case 'SUPPRIMER_PUBLI': {
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
		break; }
	/** -------------------- photo --------------------- **/
	case 'MENU_MODIFIER_PHOTO' : {
		menu_modifier_photo($connexionBD, $gi_idf_photo, $a_releveur, $a_couverture_photo, $a_codif_photo);
		break; }
	case 'MODIFIER_PHOTO' : {
		$i_idf_photo				= $_POST['idf_photo'];
		$i_idf_photographe			= $_POST['idf_photographe'];
		$st_date_photo				= $_POST['date_photo'];
		$i_idf_couverture_photo		= empty($_POST['idf_couverture_photo']) ? 0 : trim($_POST['idf_couverture_photo']);
		$i_idf_codif_photo			= empty($_POST['idf_codif_photo']) ? 0 : trim($_POST['idf_codif_photo']);
		$st_info_compl				= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "update liasse_photo set ".
		              "    idf_photographe=".$i_idf_photographe.", date_photo=str_to_date('".$st_date_photo."', '%d/%m/%Y'), ".
					  "    idf_couverture_photo=".$i_idf_couverture_photo.", idf_codif_photo=".$i_idf_codif_photo.", info_complementaires='".$st_info_compl."' ".
					  "where idf=".$i_idf_photo."";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'MENU_AJOUTER_PHOTO' : {
		menu_ajouter_photo($connexionBD, $a_releveur, $a_couverture_photo, $a_codif_photo);
		break; }
	case 'AJOUTER_PHOTO': {
		$i_idf_photographe			= $_POST['idf_photographe'];
		$st_date_photo				= $_POST['date_photo'];
		$i_idf_couverture_photo		= empty($_POST['idf_couverture_photo']) ? 0 : trim($_POST['idf_couverture_photo']);
		$i_idf_codif_photo			= empty($_POST['idf_codif_photo']) ? 0 : trim($_POST['idf_codif_photo']);
		$st_info_compl				= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "INSERT INTO `liasse_photo`(`cote_liasse`, `idf_photographe`, `date_photo`, ".
		              "            `idf_couverture_photo`, `idf_codif_photo`, `info_complementaires`) ".
					  "VALUES ('".$_SESSION['cote_liasse_gal']."', ".$i_idf_photographe.", str_to_date('".$st_date_photo."', '%d/%m/%Y'), ".
					  "        ".$i_idf_couverture_photo.", ".$i_idf_codif_photo.", '".$st_info_compl."')";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'MENU_AJOUTER_GROUPE_PHOTO' : {
		menu_ajouter_groupe_photo($connexionBD, $a_releveur, $a_couverture_photo, $a_codif_photo);
		break; }
	case 'AJOUTER_GROUPE_PHOTO': {
		$i_idf_photographe			= $_POST['idf_photographe'];
		$st_date_photo				= $_POST['date_photo'];
		$i_idf_couverture_photo		= empty($_POST['idf_couverture_photo']) ? 0 : trim($_POST['idf_couverture_photo']);
		$i_idf_codif_photo			= empty($_POST['idf_codif_photo']) ? 0 : trim($_POST['idf_codif_photo']);
		$st_info_compl				= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$a_cotes = extraction_liste($_POST['numeros'], $_SESSION['serie_liasse']);
		$check=true;
		foreach($a_cotes as $st_cote)     {
			$a_liasse = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse where cote_liasse='".$st_cote."'");
			if( count($a_liasse)==0 ) {
				print('<div align=center class="alert alert-danger">La liasse '.$st_cote.' n\'existe pas. Relevé impossible.</div><br>');
				$check=false;
			}
			else {
				$st_requete = "INSERT INTO `liasse_photo`(`cote_liasse`, `idf_photographe`, `date_photo`, ".
							  "            `idf_couverture_photo`, `idf_codif_photo`, `info_complementaires`) ".
							  "VALUES ('".$st_cote."', ".$i_idf_photographe.", str_to_date('".$st_date_photo."', '%d/%m/%Y'), ".
							  "        ".$i_idf_couverture_photo.", ".$i_idf_codif_photo.", '".$st_info_compl."')";
				$connexionBD->execute_requete($st_requete);
			}             
		}
		if( $check ) {
			menu_liste($connexionBD);			
		}
		break; }
	case 'SUPPRIMER_PHOTO': {
		$a_liste_releves = $_POST['supp'];
		foreach ($a_liste_releves as $st_idf) {
			$i_idf=substr($st_idf, 3, 6);
			$connexionBD->execute_requete("delete from liasse_photo where idf=".$i_idf);
		}
		menu_gerer($connexionBD);
		break; }
	/** -------------------- programmation --------------------- **/
	case 'MENU_MODIFIER_PROGRAM' : {
		menu_modifier_program($connexionBD, $gi_idf_program, $a_releveur, $a_priorite_program);
		break; }
	case 'MODIFIER_PROGRAM' : {    
		$i_idf_program			= $_POST['idf_program'];
		$i_idf_intervenant		= $_POST['idf_intervenant'];
		$i_idf_priorite			= $_POST['idf_priorite'];
		$st_date_creation		= $_POST['date_creation'];
		$st_date_echeance		= $_POST['date_echeance'];
		$st_date_reelle_fin		= $_POST['date_reelle_fin'];
		$i_program_releve		= empty($_POST['program_releve']) ? 0 : trim($_POST['program_releve']);
		$i_program_photo		= empty($_POST['program_photo']) ? 0 : trim($_POST['program_photo']);
		$st_info_compl			= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "update liasse_programmation set ".
		              "    idf_intervenant=".$i_idf_intervenant.", idf_priorite=".$i_idf_priorite.", ".
					  "    date_creation=str_to_date('".$st_date_creation."', '%d/%m/%Y'), ".
					  "    date_echeance=str_to_date('".$st_date_echeance."', '%d/%m/%Y'), ".
					  "    date_reelle_fin=str_to_date('".$st_date_reelle_fin."', '%d/%m/%Y'), ".
					  "    in_program_releve=".$i_program_releve.", in_program_photo=".$i_program_photo.", info_complementaires='".$st_info_compl."' ".
					  "where idf=".$i_idf_program."";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'MENU_AJOUTER_PROGRAM' : {
		menu_ajouter_program($connexionBD, $a_releveur, $a_priorite_program);
		break; }
	case 'AJOUTER_PROGRAM': {
		$i_idf_intervenant		= $_POST['idf_intervenant'];
		$i_idf_priorite			= $_POST['idf_priorite'];
		$st_date_creation		= $_POST['date_creation'];
		$st_date_echeance		= $_POST['date_echeance'];
		$st_date_reelle_fin		= $_POST['date_reelle_fin'];
		$i_program_releve		= empty($_POST['program_releve']) ? 0 : trim($_POST['program_releve']);
		$i_program_photo		= empty($_POST['program_photo']) ? 0 : trim($_POST['program_photo']);
		$st_info_compl			= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$st_requete = "INSERT INTO `liasse_programmation`(`cote_liasse`, `idf_intervenant`, `idf_priorite`, `date_creation`, `date_echeance`, `date_reelle_fin`, ".
		              "            `in_program_releve`, `in_program_photo`, `info_complementaires`) ".
					  "VALUES ('".$_SESSION['cote_liasse_gal']."', ".$i_idf_intervenant.", ".$i_idf_priorite.", ".
					  "        str_to_date('".$st_date_creation."', '%d/%m/%Y'), ".
					  "        str_to_date('".$st_date_echeance."', '%d/%m/%Y'), ".
					  "        str_to_date('".$st_date_reelle_fin."', '%d/%m/%Y'), ".
					  "        ".$i_program_releve.", ".$i_program_photo.", '".$st_info_compl."')";
		$connexionBD->execute_requete($st_requete);
		menu_gerer($connexionBD);
		break; }
	case 'MENU_AJOUTER_GROUPE_PROGRAM' : {
		menu_ajouter_groupe_program($connexionBD, $a_releveur, $a_priorite_program);
		break; }
	case 'AJOUTER_GROUPE_PROGRAM': {
		$i_idf_intervenant		= $_POST['idf_intervenant'];
		$i_idf_priorite			= $_POST['idf_priorite'];
		$st_date_creation		= $_POST['date_creation'];
		$st_date_echeance		= $_POST['date_echeance'];
		$st_date_reelle_fin		= $_POST['date_reelle_fin'];
		$i_program_releve		= empty($_POST['program_releve']) ? 0 : trim($_POST['program_releve']);
		$i_program_photo		= empty($_POST['program_photo']) ? 0 : trim($_POST['program_photo']);
		$st_info_compl			= escape_apostrophe(trim($_POST['info_compl']));
		//---- modif UTF8
		$st_info_compl = mb_convert_encoding($st_info_compl, 'cp1252', 'UTF8');
		//---- fin modif UTF8
		$a_cotes = extraction_liste($_POST['numeros'], $_SESSION['serie_liasse']);
		$check=true;
		foreach($a_cotes as $st_cote)     {
			$a_liasse = $connexionBD->sql_select_multipleUtf8("select cote_liasse from liasse where cote_liasse='".$st_cote."'");
			if( count($a_liasse)==0 ) {
				print('<div align=center class="alert alert-danger">La liasse '.$st_cote.' n\'existe pas. Programmation impossible.</div><br>');
				$check=false;
			}
			else {
				$st_requete = "INSERT INTO `liasse_programmation`(`cote_liasse`, `idf_intervenant`, `idf_priorite`, `date_creation`, `date_echeance`, `date_reelle_fin`, ".
							  "            `in_program_releve`, `in_program_photo`, `info_complementaires`) ".
							  "VALUES ('".$st_cote."', ".$i_idf_intervenant.", ".$i_idf_priorite.", ".
							  "        str_to_date('".$st_date_creation."', '%d/%m/%Y'), ".
							  "        str_to_date('".$st_date_echeance."', '%d/%m/%Y'), ".
							  "        str_to_date('".$st_date_reelle_fin."', '%d/%m/%Y'), ".
							  "        ".$i_program_releve.", ".$i_program_photo.", '".$st_info_compl."')";
				$connexionBD->execute_requete($st_requete);
			}             
		}
		if( $check ) {
			menu_liste($connexionBD);			
		}
		break; }
	case 'SUPPRIMER_PROGRAM': {
		$a_liste_releves = $_POST['supp'];
		foreach ($a_liste_releves as $st_idf) {
			$i_idf=substr($st_idf, 3, 6);
			$connexionBD->execute_requete("delete from liasse_programmation where idf=".$i_idf);
		}
		menu_gerer($connexionBD);
		break; }

}  
print('</div></body></html>');
//$connexionBD->ferme(); 
?>