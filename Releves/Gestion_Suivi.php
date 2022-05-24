<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
//http://127.0.0.1:8888/Gestion_Suivi.php

require_once '../Commun/config.php';
require_once '../Commun/constantes.php';
require_once('../Commun/Identification.php');

// La page est reservee uniquement aux gens ayant les droits utilitaires
require_once('../Commun/VerificationDroits.php');
//verifie_privilege(DROIT_UTILITAIRES);
require_once '../Commun/ConnexionBD.php';
require_once('../Commun/PaginationTableau.php');
require_once('../Commun/commun.php');

print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=cp1252" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print("<link href='../Commun/Styles.css' type='text/css' rel='stylesheet'/>");
print("<script src='../Commun/jquery-min.js' type='text/javascript'></script>");
print("<script src='../Commun/menu.js' type='text/javascript'></script>");
?>
<script type='text/javascript'>
function VerifieSuppression(Formulaire,IdfElement)
{
  var chaine="";
  for (var i = 0; i < document.forms[Formulaire].elements[IdfElement].length; i++)
  {
      if (document.forms[Formulaire].elements[IdfElement][i].checked)
      {
         chaine+=document.forms[Formulaire].elements[IdfElement][i].id+"\n";
      }
      
  }
  if (chaine == "")
  {
     alert("Pas de suivi sélectionné");
  }
  else
  {
   	 Message="Etes-vous sûr de supprimer ces suivis :\n"+chaine+"?";
   	 if (confirm (Message))                        
   	 {                                                                                                                                    
        document.forms[Formulaire].submit();                                                           
     }
  }
}
function isBisextile(date_a_verifier) {
   
    // On sépare la date en 3 variables pour vérification, parseInt() converti du texte en entier
    j = parseInt(date_a_verifier.split("/")[0], 10); // jour
    m = parseInt(date_a_verifier.split("/")[1], 10); // mois
    a = parseInt(date_a_verifier.split("/")[2], 10); // année
     
    // Définition du dernier jour de février
    // Année bissextile si annnée divisible par 4 et que ce n'est pas un siècle, ou bien si divisible par 400
    if (a%4 == 0 && a%100 !=0 || a%400 == 0) fev = 29;
    else fev = 28;
   
    // Nombre de jours pour chaque mois
    nbJours = new Array(31,fev,31,30,31,30,31,31,30,31,30,31);
   
    // Enfin, retourne vrai si le jour est bien entre 1 et le bon nombre de jours, idem pour les mois, sinon retourn faux
    return ( m >= 1 && m <=12 && j >= 1 && j <= nbJours[m-1] );
}
function Verifie_Fourchette(fourchette, fourchette_envoyee)
{
   debut_fourchette = fourchette.substring(0,4);
	fin_fourchette = fourchette.substring(4,9);
   debut_fourchette_envoyee = fourchette_envoyee.substring(0,4);
	fin_fourchette_envoyee = fourchette_envoyee.substring(4,9);
	if (debut_fourchette_envoyee < debut_fourchette || fin_fourchette_envoyee > fin_fourchette)
	{
	   return false;
	}
	return true;
}
function VerifieChamps(Formulaire)
{
   var date_ptn = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/;
   var fourchette = document.forms[Formulaire].fourchette.value;
   var fourchette_envoyee = document.forms[Formulaire].fourchette_envoi.value;
   var envoi_adherent = document.forms[Formulaire].envoi_adherent.value;
   var ListeErreurs	= "";
   if (fourchette == "")
   {
      ListeErreurs += "La fourchette est obligatoire\n";
   }
   if (fourchette_envoyee == "")
   {
      ListeErreurs += "La fourchette envoyée est obligatoire\n";
   }
	if (!Verifie_Fourchette (fourchette, fourchette_envoyee))
   {
      ListeErreurs += "La fourchette envoyée déborde de la fourchette de base\n";
   }
   if (envoi_adherent == "")
   {
      ListeErreurs += "La date de l'envoi au releveur est obligatoire\n";
   }
	if (!date_ptn.test (envoi_adherent))
   {
      ListeErreurs += "La date de l'envoi au releveur doit être de la forme : JJ/MM/AAAA\n";
   }
	if (!isBisextile (envoi_adherent))
   {
      ListeErreurs += "La date de l'envoi au releveur n'est pas correcte\n";
   }
   if (ListeErreurs != "")
   {
      alert (ListeErreurs);
   }
   else
   {
      document.forms[Formulaire].submit();
   }
   
}
</script>
<?php
print('</head>');
print('<body>');

$gst_mode = empty($_POST['mode']) ? 'LISTE': $_POST['mode'] ;
if (isset($_GET['mod']))
{
   $gst_mode='MENU_MODIFIER';
   $gi_idf_suivi = (int) $_GET['mod'];
}
else
   $gi_idf_suivi = isset ( $_POST['idf_suivi']) ? (int) $_POST['idf_suivi'] : 0;

$gi_num_page_cour = empty($_GET['num_page']) ? 1 : $_GET['num_page'];


/**
 * Affiche la liste des communes
 * @param object $sconnexionBD
 */ 
function menu_liste($sconnexionBD)
{
   global $gi_num_page_cour;

//   $st_requete = "select p.idf,ca.nom,p.fourchette,ca2.libelle from `photos` p join `commune_acte` ca  on (p.id_commune=ca.idf ) join `collection_acte` ca2 on (p.id_collection=ca2.idf) order by ca.nom,p.fourchette,ca2.libelle";
   $st_requete = "select s.idf, ad.nom, ca.nom, s.fourchette, co.libelle from `suivi_releve` s join `adherent` ad  on (s.id_adherent = ad.idf ) join `commune_acte` ca  on (s.id_commune = ca.idf) join `collection_acte` co on (s.id_collection = co.idf) order by ad.nom, ca.nom, s.fourchette, co.libelle";
   print("<form   method=\"post\" onSubmit=\"return VerifieChamps(0)\">");
   $a_liste_suivis = $sconnexionBD->liste_valeur_par_clef($st_requete);
   if (count($a_liste_suivis)!=0)
   {        
      $pagination = new PaginationTableau(basename(__FILE__),'num_page',$sconnexionBD->nb_lignes(),NB_LIGNES_PAR_PAGE,DELTA_NAVIGATION,array('Releveur','Commune','Fourchette','Collection','Modifier','Supprimer'));
      $pagination->init_param_bd($sconnexionBD,$st_requete);
      $pagination->init_page_cour($gi_num_page_cour);
      $pagination->affiche_entete_liens_navigation();
      print("<br>");
      $pagination->affiche_tableau_edition(basename(__FILE__));
      print("<br>");
      $pagination->affiche_entete_liens_navigation();      
   }
   else
      print("<div align=center>Pas de suivis</div>\n");
   print("<input type=hidden name=mode value=SUPPRIMER>");
   print("<br><div align=center><input type=button value=\"Supprimer les suivis sélectionnés\" ONCLICK=VerifieSuppression(0,\"supp[]\")></div>");   
   print("</form>");  
   print("<form   method=\"post\">");  
   print("<input type=hidden name=mode value=MENU_AJOUTER>");  
   print("<div align=center><input type=submit value=\"Ajouter un suivi\"></div>");  
   print('</form>');  

}

/**
 * Affiche de la table d'édition
 * @param integer $pi_id_adherent identifiant de l'adherent
 * @param integer $pi_id_commune identifiant de la commune
 * @param string $pst_fourchette Fourchette de la photo
 * @param integer $pi_id_collection identifiant de la collection
 * @param string $pst_fourchette_envoyee Fourchette des photos envoyée
 * @param string $pst_envoi_adherent date de l'envoi au releveur
 * @param string $pst_retour_adherent date du retour du releveur
 * @param array $pa_communes liste des communes
 * @param array $pa_collections liste des collections    
 * @param array $pa_adherents liste des releveurs (adhérents) 
 */ 
function menu_edition($pi_id_adherent,$pi_id_commune,$pst_fourchette,$pi_id_collection,$pst_fourchette_envoyee,$pst_envoi_adherent,$pst_retour_adherent,$pa_communes,$pa_collections,$pa_adherents)
{
   print("<table border=1>");
   print("<tr><th>Releveur</th><td><select name=id_adherent>".chaine_select_options($pi_id_adherent,$pa_adherents)."</select></td></tr>");
   print("<tr><th>Commune</th><td><select name=id_commune>".chaine_select_options($pi_id_commune,$pa_communes)."</select></td></tr>");
   print("<tr><th>Fourchette (aaaa-aaaa)</th><td><input type=\"text\" maxsize=9 size=9 name=fourchette value=\"$pst_fourchette\"></td></tr>");
   print("<tr><th>Collection</th><td><select name=id_collection>".chaine_select_options($pi_id_collection,$pa_collections)."</select></td></tr>");
   print("<tr><th>Fourchette envoyée (aaaa-aaaa)</th><td><input type=\"text\" maxsize=9 size=9 name=fourchette_envoi value=\"$pst_fourchette_envoyee\"></td></tr>");
   print("<tr><th>Date envoi au releveur (jj/mm/aaaa)</th><td><input type=\"text\" name=envoi_adherent value=\"$pst_envoi_adherent\"></td></tr>");
   print("<tr><th>Date retour du releveur (jj/mm/aaaa)</th><td><input type=\"text\" name=retour_adherent value=\"$pst_retour_adherent\"></td></tr>");
   print("</table>");
}

/** Affiche le menu de modification ddes suivis
 * @param object $sconnexionBD Identifiant de la connexion de base
 * @param integer $pi_idf_suivi Identifiant du relevé
 * @param array $pa_communes Liste des commmunes
 * @param array $pa_collections Liste des collections
 * @param array $pa_adherents Liste des adhérents
 */ 
function menu_modifier($sconnexionBD,$pi_idf_suivi,$pa_communes,$pa_collections,$pa_adherents)
{
   $st_requete = "select `id_adherent`,`id_commune`,`fourchette`,`id_collection`,`annee_envoi`,`envoi_adherent`,`retour_adherent` from `suivi_releve` where idf = $pi_idf_suivi";
   list($i_id_adherent,$i_id_commune,$st_fourchette,$i_id_collection,$st_fourchette_envoyee,$st_envoi_adherent,$st_retour_adherent)	= $sconnexionBD->sql_select_liste($st_requete);
   print("<form   method=\"post\" onSubmit=\"return VerifieChamps(0)\">");
   print("<input type=hidden name=mode value=MODIFIER>");
   print("<input type=hidden name=idf_suivi value=$pi_idf_suivi>");
   print("<div align=center>");
   menu_edition($i_id_adherent,$i_id_commune,$st_fourchette,$i_id_collection,$st_fourchette_envoyee,$st_envoi_adherent,$st_retour_adherent,$pa_communes,$pa_collections,$pa_adherents);   
   print("</div><br>");
   print("<div align=center><input type=button value=\"Modifier\" ONCLICK=VerifieChamps(0)></div>");
   print('</form>');
   print("<form   method=\"post\">");
   print("<input type=hidden name=mode value=LISTE>");
   print("<div align=center>");
   print("<div align=center><input type=submit value=\"Annuler\")></div>");
   print('</form>');
}

/** Affiche le menu d'ajout d'un suivi
 * @param array $pa_communes Liste des commmunes
 * @param array $pa_collections Liste des collections
 * @param array $pa_adherents Liste des adhérents
 */ 
function menu_ajouter($pa_communes,$pa_collections,$pa_adherents)
{
   print("<form   method=\"post\" onSubmit=\"return VerifieChamps(0)\">");
   print("<input type=hidden name=mode value=AJOUTER>");
   print("<div align=center>");
   menu_edition(0,0,'',0,'','','',$pa_communes,$pa_collections,$pa_adherents);
   print("</div><br>");
   print("<div align=center><input type=button value=\"Ajouter\" ONCLICK=VerifieChamps(0)></div>");
   print('</form>');
   print("<form   method=\"post\">");
   print("<input type=hidden name=mode value=LISTE>");
   print("<div align=center>");
   print("<div align=center><input type=submit value=\"Annuler\")></div>");
   print('</form>');
}

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");

$ga_communes    =    $connexionBD->liste_valeur_par_clef("select idf,nom from `commune_acte` order by nom");
$ga_collections =    $connexionBD->liste_valeur_par_clef("select idf,libelle from `collection_acte` order by libelle");
$ga_adherent     =   $connexionBD->liste_valeur_par_clef("select idf,concat(nom,'  ',prenom,' (',idf,')') from adherent order by nom,prenom");

switch ($gst_mode) {
  case 'LISTE' : menu_liste($connexionBD); 
  break;
  case 'MENU_MODIFIER' :
  menu_modifier($connexionBD,$gi_idf_suivi,$ga_communes,$ga_collections,$ga_adherent);
  break;
  
  case 'MODIFIER' :
     $i_id_adherent = (int) $_POST['id_adherent'];
     $i_id_commune = (int) $_POST['id_commune'];
     $st_fourchette = trim($_POST['fourchette']);
     $i_id_collection = (int) $_POST['id_collection'];
     $st_fourchette_envoyee = trim($_POST['fourchette_envoi']);
     $st_envoi_adherent = trim($_POST['envoi_adherent']);
     $st_retour_adherent = trim($_POST['retour_adherent']);
     $connexionBD->execute_requete("update `suivi_releve` set id_adherent=$i_id_adherent, id_commune=$i_id_commune, fourchette='$st_fourchette', id_collection=$i_id_collection, annee_envoi='$st_fourchette_envoyee', envoi_adherent='$st_envoi_adherent' , retour_adherent='$st_retour_adherent' where idf=$gi_idf_suivi");
     
     menu_liste($connexionBD);  
  break;
  case 'MENU_AJOUTER' : 
     menu_ajouter($ga_communes,$ga_collections,$ga_adherent);
  break;
  case 'AJOUTER':
     $i_id_adherent = (int) $_POST['id_adherent'];
     $i_id_commune = (int) $_POST['id_commune'];
     $st_fourchette = trim($_POST['fourchette']);
     $i_id_collection = (int) $_POST['id_collection'];
     $st_fourchette_envoyee = trim($_POST['fourchette_envoi']);
     $st_envoi_adherent = trim($_POST['envoi_adherent']);
     $st_retour_adherent = trim($_POST['retour_adherent']);
     $connexionBD->execute_requete("insert into suivi_releve (id_adherent, id_commune, fourchette, id_collection, annee_envoi, envoi_adherent, retour_adherent ) values($i_id_adherent, $i_id_commune, '$st_fourchette', $i_id_collection, '$st_fourchette_envoyee', '$st_envoi_adherent', '$st_retour_adherent')");
     menu_liste($connexionBD);
   break;
   case 'SUPPRIMER':
     $a_liste_suivis = $_POST['supp'];
     foreach ($a_liste_suivis as $i_idf_suivi)
     {
       $connexionBD->execute_requete("delete from `suivi_releve` where idf=$i_idf_suivi");
     }
     menu_liste($connexionBD);
   break;  
      
}  
print('</body>');

?>