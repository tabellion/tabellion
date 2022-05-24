<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

/* config.php, Identification.php  et ConnexionBD doivent avoir ete charges auparavant*/

/**
 * Indique si l'utilisateur identifié par $pst_ident bénéficie du droit $pst_droit  
 * @param string $pst_ident identifiant
 * @param string $st_droit droit à vérifier
 * @return boolean : vrai si l'utilisateur possède le droit indiqué 
 * @global string $gst_utilisateur_bd utilisateur de la connexion BD
 * @global string $gst_mdp_utilisateur_bd mot de passe pour se connecter à la BD
 * @global string $gst_nom_bd nom de la base de données
 * @global string $gst_serveur_bd serveur de bd     
*/
function a_droits($pst_ident,$pst_droit) 
{
   global $gst_utilisateur_bd, $gst_mdp_utilisateur_bd,$gst_nom_bd,$gst_serveur_bd;
   $connexionBD            = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);	
   $connexionBD->ajoute_params(array(':ident'=>$pst_ident,':droit'=>$pst_droit));
   $st_requete="select count(droit) from privilege join adherent on (adherent.idf=privilege.idf_adherent) where droit=:droit and ident=:ident";
   $i_a_droits=$connexionBD->sql_select1($st_requete);   
   return ($i_a_droits!=0);
} 

/**
 *  Verifie que l'utilisateur connecte a le droit $pst_droit pour visualiser la page, sinon arrête le script
 *  @param string $pst_droit
 **/ 
function verifie_privilege($pst_droit)
{
  if (isset($_SESSION['ident']))
  {
     if (!a_droits($_SESSION['ident'],$pst_droit))
     {
        print("<h3 align=center><font color=red>Vous n'&ecirc;tes pas autoris&eacute; &agrave; consulter cette page</font></h3>");
        exit(-2);
     }
  }
  else
  {
     print("<h3 align=center><font color=red>Vous n'&ecirc;tes pas autoris&eacute; &agrave; consulter cette page</font></h3>");
     exit(-1);
  }
}  
?>