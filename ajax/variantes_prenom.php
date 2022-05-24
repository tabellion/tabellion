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
  $st_rech = substr(trim($_GET['term']),0,35);  
  $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
  $st_rech=utf8_decode($st_rech); 
  //$st_rech="$st_rech%";
   
  $connexionBD->initialise_params(array(':recherche'=>$st_rech));
  $st_requete = "select distinct vp1.idf_groupe,vp1.libelle from variantes_prenom vp1 join variantes_prenom vp2 on (vp1.idf_groupe=vp2.idf_groupe) where vp2.`libelle` like :recherche  collate latin1_general_ci order by libelle";
  $a_prenoms = $connexionBD->sql_select_multiple($st_requete);
  $a_variantes_prenom = array();
  foreach ($a_prenoms as  $a_ligne)
  {
     list($i_idf_groupe,$st_prenom)  =  $a_ligne;
	   $a_variantes_prenom[]=utf8_encode($st_prenom);
     if (array_key_exists($i_idf_groupe,$a_resultats))
         $a_resultats[$i_idf_groupe][]=utf8_encode($st_prenom);         
     else
         $a_resultats[$i_idf_groupe]=array(utf8_encode($st_prenom));       
  }
  
}
$a_retour = array();
$i_nb_resultats = count($a_resultats);
$a_retour['nb_reponses']= $i_nb_resultats;
if ($i_nb_resultats==1)
{
  $a_clefs = array_keys($a_resultats);
  $a_valeurs = array_values($a_resultats);
  $a_retour['idf_groupe']=$a_clefs[0];
  $a_retour['variantes']=array_unique($a_valeurs[0]);
}
else
  $a_retour['variantes']=$a_variantes_prenom;	
echo json_encode($a_retour); 
?>