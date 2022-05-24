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
verifie_privilege(DROIT_GESTION_ADHERENT);
require_once '../Commun/ConnexionBD.php';
require_once('../Commun/PaginationTableau.php');
require_once('../Commun/commun.php');
require_once('../Commun/GestionAdherents.php');
require_once("../Commun/Adherent.php"); 
// INITIALISATION
require_once("include.php");

print('<!DOCTYPE html>');
print("<head>");
print("<title>Adhesions en cours</title>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>

$(document).ready(function() {
$('a.lien_edition').click(function(){
	window.open(this.href, 'Edition');
    return false;
  });

$("#bouton_creer_adherent").click(function() {
	if (confirm("Voulez-vous réellement créer cet adhérent ?"))   
    {
      $("#creer_adherent").submit();
    }
  }); 
});  
</script>
<?php
print('</head>');
print('<body>');
print('<div class="container">');

$gst_mode = empty($_POST['mode']) ? 'LISTE': $_POST['mode'] ;
$gst_jeton = isset($_POST['jeton']) ? $_POST['jeton'] : null;
if (isset($_GET['jeton']))
{
    $gst_mode = 'STATUT';
    $gst_jeton = $_GET['jeton'];
}
$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];

/**
 * Affiche la liste des communes
 * @param object $pconnexionBD
 */ 
function menu_liste($pconnexionBD)
{
   global $gi_num_page_cour;
   $st_requete = "SELECT DISTINCT (left( nom, 1 )) AS init FROM `commune_acte` ORDER BY init";
   $a_initiales_communes = $pconnexionBD->sql_select($st_requete);  
   print('<div class="panel panel-primary>');
   print('<div class="panel-heading">Adh&eacute;sions en ligne en cours</div>'); 
   print('<div class="panel-body">');
   print("<form   method=\"post\">");
   
   $st_requete = "select idf,ins_nom,ins_prenom,ins_email_perso,ins_token,DATE_FORMAT(ins_date_paiement, \"%d/%m/%Y %H:%m\")  from inscription_prov order by idf desc";
   $a_liste_adhesions = $pconnexionBD->sql_select_multiple_par_idf($st_requete);   
   if (count($a_liste_adhesions)!=0)
   {        
      $pagination = new PaginationTableau(basename(__FILE__),'num_page',count($a_liste_adhesions),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,array('Idf Adhesion','Nom','Pr&eacute;nom','Email','Transaction','Date de paiement'));
      $pagination->init_param_bd($pconnexionBD,$st_requete);
      $pagination->init_page_cour($gi_num_page_cour);
      $pagination->affiche_entete_liens_navigation();
      $a_tableau_visualisation = array();
     
      foreach ($a_liste_adhesions as $i_idf => $a_tab)
      {
         list($st_nom,$st_prenom,$st_email,$st_jeton,$st_date_paiement) = $a_tab;
         $st_cmd = $st_jeton!=''? "<a class=\"btn btn-primary lien_edition\" href=\"".basename(__FILE__)."?jeton=$st_jeton\">Afficher</a>" : "Attente paiement";
         $a_tableau_visualisation[]=array($i_idf,$st_nom,$st_prenom,"<a href=mailto:$st_email>$st_email</a>",$st_cmd,$st_date_paiement);
      }
      $pagination->affiche_tableau_simple($a_tableau_visualisation);
     
   }
   else
     print("<div class=\"alert alert-danger\">Pas d'adh&eacute;sions</div>\n"); 
  print("</form></div></div>");

}

/**
 * Affiche de le satut d'une transaction
 * @param string $pst_jeton jeton identifiant la transaction 
 */ 
function affiche_statut($pst_jeton)
{  
   print('<div class="panel panel-primary>');
   print('<div class="panel-heading">Statut de la transaction identifi&eacute;e par le jeton '.$pst_jeton.'</div>'); 
   print('<div class="panel-body">');
   print("<div align=center>");
   $payline = new paylineSDK(); 
   $array = array();
   $array['version'] = '';      
   $a_reponse = $payline->get_webPaymentDetails($pst_jeton,$array);
   
   $st_msg_court=$a_reponse['result']['shortMessage'];
   $st_msg_long=$a_reponse['result']['longMessage'];
   $st_code=$a_reponse['result']['code'];
   print("<table class=\"table table-bordered table-striped\">");
   print("<tr><th>Message court</th><td>$st_msg_court</td></tr>");
   print("<tr><th>Message long</th><td>$st_msg_long</td></tr>");
   print("</table>");
   if ($st_code=='00000')
   {
      print("<form   method=\"post\" id=\"creer_adherent\">");
      print("<input type=\"hidden\" name=\"mode\" value=\"CREATION\">");
      print("<input type=\"hidden\" name=\"jeton\" value=\"$pst_jeton\">");
      print("<button type=\"button\" id=\"bouton_creer_adherent\" class=\"btn btn-primary col-md-offset-4 col-md-4\">Cr&eacute;er cet adh&eacute;rent</button>");
      print("</form>");
   }
   print("<form method=\"post\" action=\"AdhesionsEnCours.php\">");
   print('<div class="form-row">'); 
   print('<button type=submit class="btn btn-primary col-md-offset-4 col-md-4">Retour &agrave; la liste des adh&eacute;sions en cours</button>');
   print('</div>');
   print("</form>");
   print("</div></div>");
}

/**
 * Crée le statut identifié par le jeton
 * @param string $pst_jeton jeton identifiant la transaction 
 * @global object $connexionBD connexion à la BD
 */ 
function cree_adherent($pst_jeton)
{
  global $connexionBD; 
  $st_requete = "select i_p.idf,i_p.ins_idf_agc,i_p.ins_type,adht.statut as ancien_statut from `inscription_prov` i_p left join `adherent` adht on (i_p.ins_idf_agc=adht.idf) where i_p.ins_token = '$pst_jeton'";
  list($i_idf_ins_prov,$i_idf_agc,$st_type_adhesion,$st_ancien_statut) = $connexionBD->sql_select_liste($st_requete);
  if (empty($st_ancien_statut))
  {
	   // l'adhérent doit forcément être créé
	   $adherent = new Adherent($connexionBD,null);
	   $adherent->initialise_inscription_en_ligne($pst_jeton);
	   $adherent->cree();
  }
  else
  {
	   // le compte généabank doit être recréé
	   $adherent = new Adherent($connexionBD,$i_idf_agc);
	   if ($st_ancien_statut==ADHESION_SUSPENDU)
	   {
		    $adherent->cree_utilisateur_gbk();
	   }
	   // c'est forcément une réadhésion
	   $adherent->initialise_readhesion_en_ligne($pst_jeton);
	   $adherent->modifie();
     $adherent->modifie_adhesion();
     $adherent->envoie_message_readhesion(); 
  }
  $st_requete = "delete from `inscription_prov` where ins_token='$pst_jeton'";
  $connexionBD->execute_requete($st_requete);
  print("<div class=\"alert alert-success\">Adh&eacuterent cr&eacute;&eacute</div>");
   
}

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");

switch ($gst_mode) {
  case 'LISTE' : menu_liste($connexionBD); 
  break;
  case 'STATUT': affiche_statut($gst_jeton);
  break;
  case 'CREATION' : cree_adherent($gst_jeton);
                    menu_liste($connexionBD); 
  break;
  default:  
      
}  
print('</div></body></html>');
?>