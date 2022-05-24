<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once '../Commun/config.php';
require_once('../Commun/Identification.php');
require_once('../Commun/constantes.php');
require_once('../Commun/VerificationDroits.php');
verifie_privilege(DROIT_GESTION_ADHERENT);
require_once '../Commun/commun.php';
require_once('../Commun/ConnexionBD.php');
require_once('../Commun/PaginationTableau.php');

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTT-8" >');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print('<title>Base '.SIGLE_ASSO.': Dernières adhésions</title>');
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');

/******************************************************************************/
/*                         Corps du programme                                 */
/******************************************************************************/


print("<body>");
print('<div class="container">');
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");

$gi_num_page_cour = empty($_POST['num_page']) ? 1 : $_POST['num_page'];
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

if (isset($_GET['tri_adhesions']))
{
  if ($_GET['tri_adhesions']=='DateAdhesion')
  {
      $gst_mode_tri = 'DateAdhesion';
      $_SESSION['tri_adhesions'] = 'DateAdhesion';
  }
  if ($_GET['tri_adhesions']=='NomAdh')
  {
      $gst_mode_tri = 'NomAdh';
      $_SESSION['tri_adhesions'] = 'NomAdh';
  } 
}

$gst_mode_tri = isset($_SESSION['tri_adhesions']) ? $_SESSION['tri_adhesions']: 'DateAdhesion';

print('<div class="panel panel-primary">');
print("<div class=\"panel-heading\">Derni&egrave;res Adh&eacute;sions</div>");
print('<div class="panel-body">');

$st_requete = "SELECT count(*) FROM `adherent`";
$i_nbadhts =$connexionBD->sql_select1($st_requete);

switch($gst_mode_tri)
{   
   case 'NomAdh' :
     $gst_tri_requete = "ORDER BY `nom` ASC";
     $pagination = new PaginationTableau(basename(__FILE__),'num_page',$i_nbadhts,NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,array('Nom','Prénom','Numéro','Email','Statut',"<a href=\"".basename(__FILE__)."?tri_adhesions=DateAdhesion\">Date de paiement</a>"));
   break;
   default:
   case 'DateAdhesion':
      $gst_tri_requete = "ORDER BY `date_paiement` DESC";
      $pagination = new PaginationTableau(basename(__FILE__),'num_page',$i_nbadhts,NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,array("<a href=\"".basename(__FILE__)."?tri_adhesions=NomAdh\">Nom</a>",'Prénom','Numéro','Email','Statut','Date de paiement'));
}

$st_requete = "SELECT nom,prenom,idf,email_perso,statut,DATE_FORMAT(date_paiement,'%d/%m/%Y') 
FROM `adherent`
$gst_tri_requete
";

$ga_adhesions = $connexionBD->sql_select_multiple($st_requete);
$ga_tableau = array();
foreach ($ga_adhesions as $a_ligne)
{
   list($st_nom,$st_prenom,$i_idf,$st_email_perso,$st_statut,$st_date_paiement) = $a_ligne;
   $ga_tableau[] = array($st_nom,$st_prenom,"<a href=\"../ListeAdherents.php?mod=$i_idf\" target=\"_blank\">$i_idf</a>",$st_email_perso,$st_statut,$st_date_paiement);
}
print("<form   method=\"post\" name=\"DernieresAdhesions\">");
$pagination->init_page_cour($gi_num_page_cour);
$pagination->affiche_entete_liste_select("DernieresAdhesions");
$pagination->affiche_tableau_simple($ga_tableau);
print("</form></div></div>");
print("</div></body></html>");
?>
