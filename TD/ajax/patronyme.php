<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('../../Commun/config.php');
require_once('../../Commun/constantes.php');
require_once('../../Commun/ConnexionBD.php');

$a_resultats = array();
if (isset ($_GET['term']))
{  
  $st_rech = substr(trim($_GET['term']),0,30);
  $st_rech=utf8_decode($st_rech);  
  $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
  $st_requete = "select distinct p.libelle from stats_patronyme sp join patronyme p on (sp.idf_patronyme=p.idf) where nb_personnes>5 and p.libelle COLLATE latin1_german1_ci like '$st_rech%' ";
  $a_patronymes = $connexionBD->sql_select($st_requete);
  $a_resultats = array();
  foreach ($a_patronymes as $st_patronyme)
  {
     $a_ligne =array();
     $a_ligne['label'] = utf8_encode($st_patronyme);
     $a_ligne['value'] = utf8_encode($st_patronyme);      
     $a_resultats[] = $a_ligne;     
  }
}
echo json_encode($a_resultats); 

?>
