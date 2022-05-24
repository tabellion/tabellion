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
  $st_requete = "select idf,nom from profession where nom COLLATE latin1_german1_ci like  '$st_rech%'";
  $a_professions = $connexionBD->liste_valeur_par_clef($st_requete);
  $a_resultats = array();
  foreach ($a_professions as $i_idf => $st_profession)
  {
     $a_ligne =array();
     $a_ligne['label'] = utf8_encode($st_profession);
     $a_ligne['value'] = utf8_encode($st_profession);      
     $a_resultats[] = $a_ligne;     
  }
}
echo json_encode($a_resultats); 

?>