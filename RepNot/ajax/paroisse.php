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
  $st_rech = "$st_rech%";
  $st_rech=utf8_decode($st_rech);
  $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
  $connexionBD->initialise_params(array(':recherche'=>$st_rech));
  $st_requete = "select distinct `paroisse` from rep_not_actes where `paroisse` collate latin1_german1_ci like :recherche order by `paroisse`";
  $a_paroisses = $connexionBD->sql_select($st_requete);
  $a_resultats = array();
  foreach ($a_paroisses as $st_paroisse)
  {
     $a_val =array();
     $a_val['label'] = sprintf("%s",utf8_encode($st_paroisse));
     $a_val['value'] = sprintf("%s",utf8_encode($st_paroisse));      
     $a_resultats[] = $a_val;     
  }
}
echo json_encode($a_resultats); 
?>