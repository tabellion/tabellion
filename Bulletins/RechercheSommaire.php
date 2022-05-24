<?php

session_start();

//http://127.0.0.1:8888/Recherche_Sommaire.php
/*
Programme de recherche des �l�ments du sommaire des bulletins AGC
PL 06/13
*/


require_once('../Commun/config.php');
require_once('../Commun/constantes.php');
require_once('../Commun/commun.php');
require_once('../Commun/ConnexionBD.php');
require_once('../Commun/PaginationTableau.php');

print('<!DOCTYPE html>');
print("<head>");
print('<link rel="shortcut icon" href="images/favicon.ico">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print("<script type='text/javascript'>");
print("</script>");
print('<title>Recherche du sommaire des bulletins</title>');
print('</head>');

print("\n<body>");
print('<div class="container">');

$i_session_num_page = isset($_SESSION['num_page_som']) ? $_SESSION['num_page_som'] : 1;
$gi_num_page_cour = empty($_GET['num_page']) ? $i_session_num_page : $_GET['num_page'];

/*
CREATE TABLE IF NOT EXISTS `sommaire`
(
  `idf` smallint(5) unsigned NOT NULL auto_increment,
  `numero` smallint(3),        num�ro du bulletin
  `moisannee` varchar(30),     mois et ann�e du bulletin
  `rubrique` text,             rubrique du sommaire
  `auteur` varchar(50),        auteur de la rubrique correspondante
  `type` varchar(5),           art pour article, asc pour ascendance, fam pour famille, cou pour cousins, des pour descendance
  `flag` enum ('O', 'N'),      pour utilisation ult�rieure
   PRIMARY KEY (`idf`)
);
*/


/* --- Affiche la liste des rubriques, noms articles, familles, ascendances, descendances et cousinage --- */
function Affiche_noms($type, $sconnexionBD)
{
   global $gi_num_page_cour,$gst_mode;

   switch ($type)
   {
      case 'RUB' :
         $session_numero = isset($_SESSION['rubrique']) ? $_SESSION['rubrique'] : '';
         $numero = isset($_POST['rubrique']) ? $_POST['rubrique'] : $session_numero;
         $_SESSION['rubrique']= $numero;
			$titre = "Sommaire du num&eacute;ro ".$numero;
      break;
      case 'ART' :
         $session_article = isset($_SESSION['article'])? $_SESSION['article'] : '';
         $auteur = isset($_POST['article']) ? $_POST['article'] : $session_article ;
         $_SESSION['article']= $auteur;
			$titre = "Articles de ".$auteur;
      break;
      case 'FAM' :
         $session_famille = isset($_SESSION['famille'])? $_SESSION['famille'] : '';
         $auteur = isset($_POST['famille']) ?  $_POST['famille'] : $session_famille;
         $_SESSION['famille']= $auteur;
			$titre = "Famille &eacute;tudi&eacute;e de ".$auteur;
      break;
      case 'ASC' :
         $session_ascendance = isset($_SESSION['ascendance'])? $_SESSION['ascendance'] : '';
         $auteur = isset($_POST['ascendance']) ? $_POST['ascendance'] : $session_ascendance;
         $_SESSION['ascendance']= $auteur;
			$titre = "Ascendance de ".$auteur;
      break;
      case 'DES' :
         $session_descendance = isset($_SESSION['descendance'])? $_SESSION['descendance'] : '';
         $auteur = isset($_POST['descendance']) ? $_POST['descendance'] : $session_descendance;
         $_SESSION['descendance']= $auteur;
			$titre = "Descendance de ".$auteur;
      break;
      case 'COU' :
         $session_cousinage = isset($_SESSION['cousinage'])? $_SESSION['cousinage'] : '';
         $auteur = isset($_POST['cousinage']) ? $_POST['cousinage'] : $session_cousinage;
         $_SESSION['cousinage']= $auteur;
			$titre = "Cousinage de ".$auteur;
      break;
   }
   if ($type == "RUB")
	   $st_requete = "select numero, moisannee, rubrique from `sommaire` where numero = $numero";
	else
      $st_requete = "select numero, moisannee, rubrique from `sommaire` where auteur like '%$auteur%' and type = '$type'";

     print("<form   method=\"post\">");
   $_SESSION['num_page_som'] = $gi_num_page_cour;
   $a_liste_sommaire = $sconnexionBD->sql_select_multiple($st_requete);
   print('<div class="panel panel-primary">');
   print("<div class=\"panel-heading\">$titre</div>");
   print('<div class="panel-body">');
   $i_nb_sommaires = count($a_liste_sommaire);
   if ($i_nb_sommaires!=0)
   {
      $pagination = new PaginationTableau(basename(__FILE__),'num_page',$i_nb_sommaires,NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,array('Bulletin','Paru en','Sommaire'));
      $pagination->init_param_bd($sconnexionBD,$st_requete);
      $pagination->init_page_cour($gi_num_page_cour);
      $pagination->affiche_entete_liens_navigation();
	  $pagination->affiche_tableau_simple_requete_sql();
      $pagination->affiche_entete_liens_navigation();
   }
   print('</form>');
   print("<form   method=\"post\">");
   print("<button type=\"submit\" class=\"btn btn-primary col-md-4 col-md-offset-4\"><span class=\"glyphicon glyphicon-home\"></span>  Retour &agrave; la recherche</button>");
   print('<input type=hidden name=mode value="DEPART">');
   print('</form>');
   print('</div></div>');
}

/* --- Remplit un select des rubriques --- */
function Select_rubrique($connexionBD)
{
   $chaine_options = "";
   $st_requete = "select distinct numero FROM sommaire order by numero";
   $a_numeros=$connexionBD->sql_select($st_requete);
   foreach ($a_numeros as $i_numero)
   {
	   $chaine_options .= "<option >$i_numero</option>\n";
   }
   return $chaine_options;
}

/* --- Remplit un select des noms --- */
function Select_nom($type,$connexionBD)
{
   $chaine_options = "";
	if ($type == "ART")
	    $a_auteurs=$connexionBD->sql_select("select distinct auteur FROM sommaire where type = '$type' order by upper(trim(auteur))");
   else		// FAM, ASC, DES, COU
	   $a_auteurs=$connexionBD->sql_select("select distinct det_auteur FROM detail_nom where det_type = '$type' order by det_auteur");
  foreach ($a_auteurs as $st_auteur)
   {
	   $chaine_options .= "<option >$st_auteur</option>\n";
   }
   return $chaine_options;
}

/* --- Saisie des crit�res de recherche --- */
/*
+------------------------------------------------------+
|   Les rubriques d'un num�ro    =========    valider  |
+------------------------------------------------------+
|   Chaque article d'un auteur   =========    valider  |
+------------------------------------------------------+
|   Familles �tudi�es            =========    valider  |
+------------------------------------------------------+
|   Ascendance d'un adh�rent     =========    valider  |
+------------------------------------------------------+
|   Descendance d'un adh�rent    =========    valider  |
+------------------------------------------------------+
|   Cousinage des adh�rents      =========    valider  |
+------------------------------------------------------+
*/
function Saisie_recherche($connexionBD)
{
	print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Recherche sur le sommaire des bulletins</div>');
	print('<div class="panel-body">');
    print("<div id='sommaire'>");
	print("<form  method=\"post\">");
	print('<div class="form-group row">');
	print('<div class="col-md-4">');
	print('<button class="btn btn-primary" type=submit id="rub_recherche" name="valide_rub"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
    print('</div>');
	print('<label for="rub" class="col-form-label col-md-4">Les rubriques d\'un num&eacute;ro</label>');
    print('<div class="col-md-4">');
	print('<select id="rub" name="rubrique" class="form-control">'.Select_rubrique($connexionBD).'</select>');
    print("<input type=hidden name=mode value=\"RUBRIQUE\">");
	print('</div>');
	print('</div>');
	print("</form>");
	print("<form  method=\"post\">");
	print('<div class="form-group row">');
	print('<div class="col-md-4">');
	print('<button class="btn btn-primary"  type=submit name="valide_art"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
	print('</div>');
	print('<label for="art" class="col-form-label col-md-4">Chaque article d\'un auteur</label>');
	print('<div class="col-md-4">');
	print('<select id="art" name=article class="form-control">'.Select_nom('ART',$connexionBD).'</select>');
	print("<input type=hidden name=mode value=\"ARTICLE\">");
	print('</div>');
	print('</div>');
	print("</form>");
	print("<form  method=\"post\">");
	print('<div class="form-group row">');
	print('<div class="col-md-4">');
	print('<button class="btn btn-primary" type=submit  name="valide_fam"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
    print('</div>');
    print('<label for="fam" class="col-form-label col-md-4">Familles &eacute;tudi&eacute;e</label>');
	print('<div class="col-md-4">');
	print('<select id="fam" name=famille class="form-control">'.Select_nom('FAM',$connexionBD).'</select>');
	print("<input type=hidden name=mode value=\"FAMILLE\">");
	print('</div>');
	print('</div>');
	print("</form>");
	print("<form  method=\"post\">");
	print('<div class="form-group row">');
	print('<div class="col-md-4">');
	print('<button class="btn btn-primary" type=submit name="valide_asc"><span class="glyphicon glyphicon-search"></span>  Recherche</button>');
    print('</div>');
	print('<label for="asc" class="col-form-label col-md-4">Ascendance d\'un adh&eacute;rent</label>');
	print('<div class="col-md-4">');
	print('<select id="asc" name=ascendance class="form-control">'.Select_nom('ASC',$connexionBD).'</select>');
    print("<input type=hidden name=mode value=\"ASCEND\">");
    print('</div>');
    print('</div>');
	print("</form>");
	print("<form  method=\"post\">");
	print('<div class="form-group row">');
	print('<div class="col-md-4">');
	print('<button class="btn btn-primary" type=submit name="valide_des"><span class="glyphicon glyphicon-search"></span>  Recherche</button>');
    print('</div>');
	print('<label for="des" class="col-form-label col-md-4">Descendance d\'un adh&eacute;rent</label>');
	print('<div class="col-md-4">');
	print('<select id="des" name=descendance class="form-control">'.Select_nom('DES',$connexionBD).'</select>');
	print("<input type=hidden name=mode value=\"DESCEND\">");
	print('</div>');
	print('</div>');
	print("</form>");
	print("<form  method=\"post\">");
	print('<div class="form-group row">');
	print('<div class="col-md-4">');
	print('<button class="btn btn-primary" type=submit name="valide_cou"><span class="glyphicon glyphicon-search"></span> Recherche</button>');
    print('</div>');
	print('<label for="cou" class="col-form-label col-md-4">Cousinage des adh&eacute;rents </label>');
	print('<div class="col-md-4">');
	print('<select id="cou" name=cousinage class="form-control">'.Select_nom('COU',$connexionBD).'</select>');
	print("<input type=hidden name=mode value=\"COUSIN\">");
	print('</div>');
	print('</div>');

	print('</form>');
	print("</div>");
}

/* --- D�but du programme --- */

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");


$st_session_mode = empty($_SESSION['mode']) ? 'DEPART' : $_SESSION['mode'];
$gst_mode = isset($_POST['mode']) ? $_POST['mode'] : $st_session_mode;
$_SESSION['mode']=$gst_mode;

switch ($gst_mode)
{
   case 'DEPART' :
      Saisie_recherche($connexionBD);
   break;
   case 'RUBRIQUE' :
		Affiche_noms('RUB', $connexionBD);
   break;
   case 'ARTICLE' :
      Affiche_noms('ART', $connexionBD);
   break;
   case 'FAMILLE' :
      Affiche_noms('FAM', $connexionBD);
   break;
   case 'ASCEND' :
      Affiche_noms('ASC', $connexionBD);
   break;
   case 'DESCEND' :
      Affiche_noms('DES', $connexionBD);
   break;
   case 'COUSIN' :
      Affiche_noms('COU', $connexionBD);
   break;
}
//unset($_SESSION['mode']);

$connexionBD->ferme();
print ("</form>");
print("</div></body></html>");

?>
