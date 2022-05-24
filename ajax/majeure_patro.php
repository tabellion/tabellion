<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('../Commun/config.php');
require_once('../Commun/constantes.php');
require_once('../Commun/ConnexionBD.php');

$a_resultats = array();
if (isset ($_GET['term']))
{
  $st_rech = substr(trim($_GET['term']),0,30);
  $st_rech=utf8_decode($st_rech);  
  $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
  $st_requete = "select idf_groupe,patronyme from variantes_patro where patronyme COLLATE latin1_german1_ci like  '$st_rech%' and majeure=1 order by patronyme";
  $a_majeures = $connexionBD->liste_valeur_par_clef($st_requete);
  $a_resultats = array();
  foreach ($a_majeures as $i_idf_groupe => $st_patronyme)
  {
     $a_ligne =array();
     $a_ligne['text'] = utf8_encode($st_patronyme);
     $a_ligne['id'] = utf8_encode($i_idf_groupe);      
     $a_resultats[] = $a_ligne;     
  }
}
echo json_encode($a_resultats); 

?>