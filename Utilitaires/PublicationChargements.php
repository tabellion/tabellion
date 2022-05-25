<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_UTILITAIRES);
require_once __DIR__ . '/../Commun/PaginationTableau.php';
require_once __DIR__ . '/../Commun/commun.php';


$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

$gi_num_page_cour = empty($_POST['num_page']) ? 1 : $_POST['num_page'];
$gst_mode = empty($_POST['mode']) ? 'LISTE': $_POST['mode'] ;

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<title>Publication d'un chargement</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');
print('<body>');
print('<div class="container">');

/**
 * Affiche la liste des communes
 * @param object $pconnexionBD
 */ 
function menu_liste($pconnexionBD)
{
   global $gi_num_page_cour,$ga_types_nimegue;
   print('<div class="panel panel-primary">');
   print('<div class="panel-heading">Publication des chargements</div>');
   print('<div class="panel-body">');
   print("<form   method=\"post\" name=\"PubliChargements\">");
   $st_requete = "select c.idf,date_format(c.date_chgt,'%d/%m/%Y %H:%i'),ca.nom,c.type_acte_nim,c.nb_actes,c.publication from `chargement` c join `commune_acte` ca on (c.idf_commune=ca.idf) order by c.date_chgt desc";
   $a_liste_chgt = $pconnexionBD->sql_select_multiple_par_idf($st_requete);
   $a_liste_ids = array();
   if (count($a_liste_chgt)!=0)
   {        
      $pagination = new PaginationTableau(basename(__FILE__),'num_page',count($a_liste_chgt),NB_LIGNES_PAR_PAGE,1,array('Date','Commune','Type','Nbre actes','Publier'));
      $pagination->init_page_cour($gi_num_page_cour);
      $pagination->affiche_entete_liste_select("PubliChargements");
      $a_tableau_affichage=array();
      foreach ($a_liste_chgt as $i_idf_chgt => $a_chgt)
      {
         list($st_date,$st_commune,$i_type_nim,$i_nb_actes,$c_publication) = $a_chgt;
         
         $st_publication = $c_publication==0 ? "<input type=checkbox name=\"publi[]\" id=\"$st_date ".$st_commune." ".$ga_types_nimegue[$i_type_nim]."\" value=$i_idf_chgt class=\"form-check-label col-form-label control-label\">" : "<input type=checkbox name=\"publi[]\" id=\"$st_date ".$st_commune." ".$ga_types_nimegue[$i_type_nim]."\" value=$i_idf_chgt checked class=\"form-check-label col-form-label control-label\">\n";
         $a_tableau_affichage[]=array($st_date,cp1252_vers_utf8($st_commune),$ga_types_nimegue[$i_type_nim],$i_nb_actes,$st_publication);
         $a_liste_ids[]=$i_idf_chgt;
      }
      $pagination->affiche_tableau_simple($a_tableau_affichage,false);
   }
   else
     print("<div class=\"alert alert-danger\">Pas de chargements</div>\n");
   print("<input type=hidden name=mode value=\"PUBLIER\">");
   $a_liste_ids=array_slice($a_liste_ids,($gi_num_page_cour-1)*NB_LIGNES_PAR_PAGE,NB_LIGNES_PAR_PAGE);
   print("<input type=hidden name=chargements value=\"".implode(',',$a_liste_ids)."\">");
   print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary">Publier/Cacher les chargements sélectionn&eacute;s</button></div>'); 
   print("</form>");
   print("</div></div>");  
}

require_once __DIR__ . '/../Commun/menu.php';

switch ($gst_mode) {
  case 'LISTE' : menu_liste($connexionBD); 
  break;
   case 'PUBLIER':
     $a_liste_publications = $_POST['publi'];
     $a_liste_chargements = $_POST['chargements'];
     $st_requete = "update chargement set publication=0 where idf in ($a_liste_chargements)";
     $connexionBD->execute_requete($st_requete);
     if (count($a_liste_publications)!=0)
     {
        $st_liste_publications=implode(',',$a_liste_publications);
        $st_requete = "update chargement set publication=1 where idf in ($st_liste_publications)";
        $connexionBD->execute_requete($st_requete);
     }
     menu_liste($connexionBD);
   break;  
      
}  

print('</div></body></html>');
