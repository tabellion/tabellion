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
verifie_privilege(DROIT_UTILITAIRES);
require_once '../Commun/commun.php';
require_once('../Commun/ConnexionBD.php');
require_once('../Commun/soundex2.cls.php');

/******************************************************************************/
/*                         Corps du programme                                 */
/******************************************************************************/
print('<meta http-equiv="Content-Type" content="text/html; charset=cp1252" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print("<link href='Commun/Styles.css' type='text/css' rel='stylesheet'/>");

print("<body>");
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
require_once("../Commun/menu.php");
$soundex2 = new soundex2;

$ga_patronymes = $connexionBD->sql_select("select distinct p.libelle from `stats_patronyme` sp join patronyme p on (sp.idf_patronyme=p.idf) where p.libelle not in (select patronyme from `variantes_patro`)");

$gh_variantes = array();
foreach($ga_patronymes as $st_patronyme)
{
   if (empty($st_patronyme))
      continue;
   //print("P=$st_patronyme<br>");   
   $soundex2 -> build ($st_patronyme);
   $st_soundex = $soundex2 -> sString;
   if (array_key_exists($st_soundex,$gh_variantes))
   {
      $a_variantes = $gh_variantes{$st_soundex};
      $a_variantes[] = $st_patronyme;
      $gh_variantes{$st_soundex} = $a_variantes;
   }
   else
   {
      $gh_variantes{$st_soundex} = array($st_patronyme);
   }   
}

foreach ($gh_variantes as $st_soundex => $a_variantes)
{
   print(join('|',$a_variantes));
   print("<br>");
}

?>
