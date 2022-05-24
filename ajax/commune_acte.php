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
  $i_idf_source = isset($_GET['idf_source']) ? (int) $_GET['idf_source']: null;   
  $st_rech=utf8_decode($st_rech);  
  $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
  $st_rech="$st_rech%";
  $connexionBD->initialise_params(array(':recherche'=>$st_rech)); 
  if (empty($i_idf_source))
    $st_requete = "select nom from commune_acte where nom COLLATE latin1_german1_ci like :recherche order by nom";
  else
    $st_requete = "select distinct ca.nom from commune_acte ca join stats_commune sc on (ca.idf=sc.idf_commune) where sc.idf_source=$i_idf_source and ca.nom COLLATE latin1_german1_ci like :recherche order by nom";  
  $a_patros = $connexionBD->sql_select($st_requete);
  $a_resultats = array();
  foreach ($a_patros as $st_patro)
  {
     $a_val =array();
     $a_val['label'] = sprintf("%s",utf8_encode($st_patro));
     $a_val['value'] = sprintf("%s",utf8_encode($st_patro));      
     $a_resultats[] = $a_val;     
  }
}
echo json_encode($a_resultats); 
?>