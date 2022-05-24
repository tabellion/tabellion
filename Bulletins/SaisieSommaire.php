<?php
//http://127.0.0.1:8888/Saisie_Sommaire.php
/*
Programme de saisie des éléments du sommaire des bulletins AGC
PL 06/17
*/

$gst_chemin = "../";
//$gst_chemin = ".";

require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_UTILITAIRES);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/PaginationTableau.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
$gst_mode = empty($_POST['mode']) ? 'DEPART': $_POST['mode'] ;
if (isset($_GET['mod']))
{
   $gst_mode='LIGNE';
}
$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

/*
CREATE TABLE IF NOT EXISTS `sommaire`
( 
  `idf` smallint(5) unsigned NOT NULL auto_increment,
  `numero` smallint(3),        numéro du bulletin
  `moisannee` varchar(30),     mois et année du bulletin
  `rubrique` text,             rubrique du sommaire
  `auteur` varchar(50),        auteur de la rubrique correspondante
  `type` varchar(5),           art pour article, asc pour ascendance, fam pour famille, cou pour cousins, des pour descendance
  `flag` enum ('O', 'N'),      pour utilisation ultèrieure
   PRIMARY KEY (`idf`)
);
*/

//print_r($_POST); 


$gst_mode = isset($_POST['mode']) ?   $_POST['mode'] : "DEPART";
if (empty($_POST)) 
{
   $gst_mode = "DEPART";   
}
else
{
	if (isset($_POST['valide_rub'])) {                  
	$gst_mode = "NUMERO";
	} 
	elseif (isset($_POST['valid'])) {                  
		$gst_mode = "ENREGISTRE";
   } 
	elseif (isset($_POST['valid_sup'])) {                  
		$gst_mode = "SUPPRESSION";
   } 
	elseif (isset($_POST['retour'])) {                  
		$gst_mode = "DEPART";
   } 
}
if (isset($_GET['idrub']))
{
   $gst_mode='LIGNE';
   $idrub = (int) $_GET['idrub'];
	if (isset($_GET['sup']))
	{
//echo "sup ? ".	$_GET['idrub']." - ".$_GET['sup'];
//      $gst_mode = 'SUPPRESSION';
      $gst_mode = 'CONFIRM';
	}
}

/* --- Remplit un select des rubriques --- */
function Select_rubrique()
{
   global $connexionBD;
	$chaine_options = "";
  $a_numeros=$connexionBD->sql_select("select distinct numero FROM sommaire order by numero");
	foreach ($a_numeros as $i_numero)
  {
	   $chaine_options .= "<option >$i_numero</option>\n";
  }
  return $chaine_options;
}

/* --- Affiche les boutons du départ --- */
function Affiche_depart()
{   
    print("<form  method=\"post\">");
    print("<button type=\"submit\" class=\"btn btn-primary col-md-4 col-md-offset-4\"><span class=\"glyphicon glyphicon-plus\"></span>  Cr&eacute;ation du bulletin</button>");
	print("<input type=\"hidden\" name=\"mode\" value=\"CREATION\"> ");
	print('</form>');
	print("<form  method=\"post\">");
	print("<button type=\"submit\" class=\"btn btn-primary col-md-4 col-md-offset-4\"><span class=\"glyphicon glyphicon-edit\"></span>  Mise &agrave; jour du bulletin</button>");
	print("<input type=\"hidden\" name=\"mode\" value=\"RUBRIQUE\"> "); 
	print('</form>'); 
}

/* --- Affiche le bulletin à modifier --- */
function Affiche_bulletin()
{
	print("<form  method=\"post\">");
    print('<div class="row form-group">');  	
	print('<label for="rub" class="col-form-label col-md-4">Choisir un numéro de bulletin à mettre à jour</label>');
	print('<div class="col-md-4">');
	print("<select id='rub' name=rubrique>".Select_rubrique()."</select>");
	print('</div>');
	print("<input type=\"hidden\" name=\"mode\" value=\"NUMERO\"> ");
	print("<button type=\"submit\" class=\"btn btn-primary col-md-4 col-md-offset-4\"><span class=\"glyphicon glyphicon-ok\"></span>  Validation num&eacute;ro</button>");
    print('</div>');	
	print('</form>');
}

/* --- Récupération de l'enregistrement choisi --- */
function Recupere_sommaire()
{
	global $connexionBD,$gi_num_page_cour;
	$numero = (int) $_POST['rubrique'];
	//echo "Numéro choisi : ".$numero;
	print("<form  method=\"post\">");  
    
	$st_requete = "select idf,numero,moisannee,rubrique,auteur,type from sommaire where numero = $numero";
	$a_liste_sommaires = $connexionBD->liste_valeur_par_clef($st_requete);
	$i_nb_sommaires = count($a_liste_sommaires);
	if ($i_nb_sommaires!=0)
	{     
		$pagination = new PaginationTableau(basename(__FILE__),'num_page',$i_nb_sommaires,NB_LIGNES_PAR_PAGE,1,array('Bulletin','Mois Ann&eacute;e','D&eacute;signation de la rubrique','Auteur du texte','Type','Modifier','Supprimer'));
		$pagination->init_param_bd($connexionBD,$st_requete);
		$pagination->init_page_cour($gi_num_page_cour);
		$pagination->affiche_entete_liens_navigation();
		$pagination->affiche_tableau_edition(basename(__FILE__));
		print("<input type=hidden name=mode value=\"SUPPRIMER\">");
	}
	else
		print('<div class="alert alert-danger">Pas de sommaire</div>');	
	print('</form>');
}

/* --- Recherche l'enregistrement choisi et appelle la saisie --- */
function Recherche_enreg($idrub)
{
	global $connexionBD;
    $st_requete = "select numero,moisannee,rubrique,auteur,type from sommaire where idf = ".utf8_vers_cp1252($idrub);
	list($num,$moisaa,$rubrique,$auteur,$typrub)=$connexionBD->sql_select_liste($st_requete);
 	Affiche_saisie($num, $moisaa, $rubrique, $auteur, $typrub, $idrub);
}

/* --- Affiche les éléments à saisir --- */
function Affiche_saisie($num, $moisaa, $rubrique, $auteur, $typrub, $idrub)
{
	print("<form  id=saisie_rubrique method=\"post\">");  
    
	print('<div class="row form-group">');
    print('<label for="num" class="col-form-label col-md-4">Numéro du bulletin</label>');
	print('<div class="col-md-4">');	
	print("<input type=text value=$num name=num id=num class=\"form-control\">");
	print('</div></div>');
	
	print('<div class="row form-group">');
    print('<label for="moisaa" class="col-form-label col-md-4">Mois et Année</label>');
	print('<div class="col-md-4">');	
	print("<input type=text value='$moisaa' name=moisaa id=moisaa class=\"form-control\">");
	print('</div></div>');
	
	print('<div class="row form-group">');
    print('<label for="typrub" class="col-form-label col-md-4">Type rubrique (Article, Famille, Ascendance, Descendance)</label>');
	print('<div class="col-md-4">');	
	print("<select name=typrub id=typrub class=\"form-control\">");
	if (($idrub != 0) and ($typrub == "ART"))
	   print("<option value=ART selected>Article</option>");
	else
	   print("<option value=ART>Article</option>");
	if (($idrub != 0) and ($typrub == "FAM"))
		print("<option value=FAM selected>Famille</option>");
	else		
		print("<option value=FAM>Famille</option>");
	if (($idrub != 0) and ($typrub == "COU"))
		print("<option value=COU selected>Cousinage</option>");
	else		
		print("<option value=COU>Cousinage</option>");
	if (($idrub != 0) and ($typrub == "ASC"))
		print("<option value=ASC selected>Ascendance</option>");
	else		
	   print("<option value=ASC>Ascendance</option>");
	if (($idrub != 0) and ($typrub == "DES"))
	   print("<option value=DES selected>Descendance</option>");
	else
		print("<option value=DES>Descendance</option>");
	print("</select>");
	print('</div></div>');	
	
	print('<div class="row form-group">');
    print('<label for="nompre" class="col-form-label col-md-4">Pr&eacute;nom et nom de l\'auteur</label>');
	print('<div class="col-md-4">');
	$nompre = "";
	if ($idrub != 0) // Mise à jour
	{
	   if (strstr($rubrique, "-"))   // si on trouve prénom nom - texte, on mets le prénom et le nom
		{
         $part = explode("-", $rubrique);
		   $nompre = $part[0];
		   $nompre = substr($nompre, 0, -1);
		}
		else                          // si pas de nom et prénom  et AGC on mets AGC dans le prénom et nom
		{
		   if ($auteur == "AGC")
			{
		      $nompre = $auteur;
			}
		}	
	}
	print("<input type=text value='$nompre' name=nompre id =nompre class=\"form-control\">");
	print('</div></div>');
	
	print('<div class="row form-group">');
    print('<label for="auteur" class="col-form-label col-md-4">Code Auteur</label>');
	print('<div class="col-md-4">');
	print("<input type=text value='".cp1252_vers_utf8($auteur)."' name=auteur id=auteur>");
	print('</div></div>');
	
	print('<div class="row form-group">');
    print('<label for="txtrub" class="col-form-label col-md-4">Texte de la rubrique</label>');
	print('<div class="col-md-4">');
	$txtrub = "";
	if ($idrub != 0) // Mise à jour
	{
	   if (strstr($rubrique, "-"))   // si on trouve prénom nom - texte, on mets le texte de la rubrique
		{
         $part = explode("-", $rubrique);
		   $txtrub = $part[1];
		   $txtrub = substr($txtrub, 1);
		}
		else                          // si pas de nom et prénom, on mets le texte de la rubrique
		{
		   $txtrub = $rubrique;
		}
	}
	print("<input type=text size=70 value='".cp1252_vers_utf8($txtrub)."' name=txtrub id=txtrub>");
	print('</div></div>');

	print("<input type=hidden name=idrub value=$idrub>");
	print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
	print('<button type=submit class="btn btn-primary" id="modifier"><span class="glyphicon glyphicon-ok"></span> Validation de l\'enregistrement</button>');
	print('<button type=button class="btn btn-primary" id="annuler"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
	print('</div>');
		
	print("<input type=\"hidden\" name=\"mode\" value=\"ENREGISTRE\"> ");
	print('</form>');
}

/* --- Enregistrement --- */
function Enregistrement()
{
	global $connexionBD;
	
	$idrub = $_POST['idrub'];
	$numero = $_POST['num'];
	$moisannee = $_POST['moisaa'];
	$type = $_POST['typrub'];
	if (($type == "ASC") or ($type == "DES"))
		$rubrique = $_POST['nompre'];
	else		
		$rubrique = $_POST['nompre']." - ".$_POST['txtrub'];
	$rubrique = strtr ($rubrique, "'", " ");
	$auteur = $_POST['auteur'];
	$flag = "N";
	if ($idrub == 0)               // création d'un enregistrement
	{
		$connexionBD->initialise_params(array(':numero'=>$numero,':moisannee'=>$moisannee,":rubrique"=>utf8_vers_cp1252($rubrique),":auteur"=>utf8_vers_cp1252($auteur),":type"=>utf8_vers_cp1252($type),":flag"=>$flag));
		$sqlins = "insert into sommaire (numero, moisannee, rubrique, auteur, type, flag)
                 values (:numero, :moisannee, :rubrique, :auteur, :type, :flag)";
		$connexionBD->execute_requete($sqlins);	
		if (($type == "ASC") or ($type == "DES"))   // Ascendance ou descendance, création d'un enregistrement
		{
			$connexionBD->initialise_params(array(':numero'=>$numero,":auteur"=>$auteur,":type"=>$type));
			$sqlins = "insert into detail_nom (det_numero, det_type, det_auteur, id_bulletin)
                                    values (:numero, :type, :auteur, 0)";
			$connexionBD->execute_requete($sqlins);	
		}
		print("<div class=\"alert alert-success\">Création rubrique enregistrée</div>"); 
	}
	else                           // modification d'un enregistrement
	{
      $connexionBD->initialise_params(array(':numero'=>$numero,':moisannee'=>$moisannee,":rubrique"=>utf8_vers_cp1252($rubrique),":auteur"=>utf8_vers_cp1252($auteur)));
      $sqlmaj = "update sommaire set numero = :numero, moisannee = :moisannee, rubrique = :rubrique, 
		                               auteur = :auteur where idf = $idrub";  
	    $connexionBD->execute_requete($sqlmaj);
	    print("<div class=\"alert alert-success\">Modification rubrique effectuée</div>"); 
	}
}

/* --- Confirmation suppression --- */
function Confirmation()
{
	global $connexionBD;		
	print("<form  method=\"post\">"); 
	$idrub = $_GET['idrub']; 
	$st_requete = "select * from sommaire where idf = $idrub";
	$st_requete = "select numero,moisannee,rubrique from sommaire where idf = $idrub";
	list($num,$moisaa,$rubrique)=$connexionBD->sql_select_liste($st_requete);
	print('<div class="panel panel-danger">');
	print("<div class=\"panel-heading\">Confirmation suppression de la rubrique $rubrique, du bulletin $num</div>");
	print('<div class="panel-body">');
	print("<input type=hidden name=idrub value=$idrub>");
	print('<div class="btn-group col-md-4 col-md-offset-4" role="group">');
	print('<button type=button class="btn btn-primary" id="modifier"><span class="glyphicon glyphicon-ok"></span> Validation</button>');
	print('<button type=button class="btn btn-primary" id="annuler"><span class="glyphicon glyphicon-remove"></span> Annuler</button>');
	print('</div>'); 
	print("<input type=hidden name=idrub value=$idrub>");
	print('</div></div>');
	print('</form>');
}

?>

<!DOCTYPE html> 
<html lang="fr">
<head>
  <title>Saisie du sommaire des bulletins</title>
  <meta charset="UTF-8" />       <!-- ou charset="utf-8" -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="SaisieSommaire.css" />
  <link href='../css/styles.css' type='text/css' rel='stylesheet'>
  <link href='../css/bootstrap.min.css' rel='stylesheet'> 
  <link href='../css/jquery-ui.css' type='text/css' rel='stylesheet'>  
  <link href='../css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>
  <link href='../css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>
  <script src='../js/jquery-min.js' type='text/javascript'></script>
  <script src='../js/jquery.validate.min.js' type='text/javascript'></script>
  <script src='../js/additional-methods.min.js' type='text/javascript'></script>
  <script src='../js/jquery-ui.min.js' type='text/javascript'></script>
  <script src='../js/bootstrap.min.js' type='text/javascript'></script>
  <script type='text/javascript'>
$(document).ready(function() {
$("#annuler" ).click(function() {
    window.location.href = 'SaisieSommaire.php';
});

$("#saisie_rubrique").validate({
		rules:{
				num: {
					required: true,
					number: true
				},
				moisaa: {
					required: true,
				},
				nompre: {
					required: true,
				}
				
			},	
		messages:{
				num: {
					required: "Le numéro est obligatoire",
					number: "Le numéro doit être un entier"
				},
				moisaa: {
					required: "Le mois et l'année sont obligatoires",
				},
				nompre: {
					required: "Le nom et le prénom sont obligatoires",
				}
			}
	});
           
});

</script>
</head>

<body>

<?php

require_once __DIR__ . '/../Commun/menu.php';

print('<div class="panel panel-primary">');
print('<div class="panel-heading">Mise &agrave; jour du sommaire des bulletins</div>');
print('<div class="panel-body">'); 
	
switch ($gst_mode) 
{
   case 'DEPART' : 
      Affiche_depart(); 
   break;
   case 'RUBRIQUE' : 
		Affiche_bulletin(); 
   break;
  case 'NUMERO' : 
		Recupere_sommaire(); 
   break;
  case 'LIGNE' : 
//		echo "idf de la ligne : ".$_GET['idrub']; 
      //$idrub = $_GET['idrub']; 
      $idrub = $_GET['mod'];
      Recherche_enreg($idrub);
   break;
  case 'CONFIRM' : 
     Confirmation();
   break;
  case 'SUPPRESSION' : 
//		echo "idf de la ligne à supprimer : ".$_GET['idrub']; 
      $idrub = $_POST['idrub']; 
      $sqlmaj = "delete from sommaire where idf = $idrub";
      $connexionBD->execute_requete($sqlmaj); 
	   print('<div class="alert alert-success">Suppression rubrique effectuée</div>'); 
	   Affiche_depart(); 
   break;
  case 'ENREGISTRE' : 
		Enregistrement(); 
   	Affiche_depart(); 
   break;
  case 'CREATION' : 
		Affiche_saisie(0, "", "", "", "", 0); 
   break;
   default: print("<div class=\"alert alert-danger\">Mode inconnu $gst_mode</div>");
}
print('</div></div>');
?>	

</body>
</html>
