<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
if ($_REQUEST['sid']) session_id($_REQUEST['sid']); 

require_once('../../Commun/config.php');
require_once('../../Commun/Identification.php');
require_once('../../Commun/constantes.php');
require_once('../../Commun/ConnexionBD.php');
require_once('../commun_rep_not.php');

$i_idf_rep = (int) $_POST['idf_rep'];
$i_idf_acte= isset($_POST['idf_acte_cour']) ? (int) $_POST['idf_acte_cour'] : null;
if (isset($_POST['sans_date']))
{
   $i_jour = 0;
   $i_mois = 0;
   $i_annee = 9999;
   $st_date_rep = '';
}
else
{
  $i_jour = (int) $_POST['jour'];
  $i_mois = (int) $_POST['mois'];
  $i_annee = (int) $_POST['annee'];
  $i_jour_rep = (int) $_POST['jour_rep'];
  $i_mois_rep = (int) $_POST['mois_rep'];
  $i_annee_rep = (int) $_POST['annee_rep'];
  $st_mois_rep = array_key_exists($i_mois_rep,$ga_mois_revolutionnaires_nimegue) ? $ga_mois_revolutionnaires_nimegue[$i_mois_rep] : '';
  if (!empty($i_jour_rep) && !empty($st_mois_rep) && !empty($i_annee_rep))
    $st_date_rep = sprintf("%02d/%s/%02d",$i_jour_rep,$st_mois_rep,$i_annee_rep);
  else
    $st_date_rep = '';
}
$i_page = (int) $_POST['page'];
$st_type_acte = mb_convert_case(substr(trim($_POST['type_acte']),0,40),MB_CASE_TITLE,"UTF-8");
$st_nom1 = mb_convert_case(substr(trim($_POST['nom1']),0,40),MB_CASE_UPPER,"UTF-8");
$st_prenom1 = isset($_POST['prenom1']) ? mb_convert_case(substr(trim($_POST['prenom1']),0,30),MB_CASE_TITLE,"UTF-8"):'';
$st_nom2 = mb_convert_case(substr(trim($_POST['nom2']),0,40),MB_CASE_UPPER,"UTF-8");
$st_prenom2 = isset($_POST['prenom2']) ? mb_convert_case(substr(trim($_POST['prenom2']),0,30),MB_CASE_TITLE,"UTF-8"):'';
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
$st_paroisse = isset($_POST['paroisse']) ? mb_convert_case(substr(trim($_POST['paroisse']),0,40),MB_CASE_TITLE,"UTF-8") : '';
$st_commentaires = isset($_POST['commentaires']) ? substr(trim($_POST['commentaires']),0,255) : '';

$st_type_acte=utf8_decode($st_type_acte);
$st_nom1=utf8_decode($st_nom1);
$st_prenom1=utf8_decode($st_prenom1);
$st_nom2=utf8_decode($st_nom2);
$st_prenom2=utf8_decode($st_prenom2);
$st_paroisse=utf8_decode($st_paroisse);
$st_commentaires=utf8_decode($st_commentaires);
if (empty($i_idf_acte))
{
   $connexionBD->initialise_params(array(':idf_rep'=>$i_idf_rep,':annee'=>$i_annee,':mois'=>$i_mois,':jour'=>$i_jour,':date_rep'=>$st_date_rep,':page'=>$i_page,':type_acte'=>$st_type_acte,':nom1'=>$st_nom1,':prenom1'=>$st_prenom1,':nom2'=>$st_nom2,':prenom2'=>$st_prenom2,':paroisse'=>$st_paroisse,':commentaires'=>$st_commentaires));
   $st_requete = "insert into rep_not_actes(idf_repertoire,annee,mois,jour,date_rep,page,`type`,nom1,prenom1,nom2,prenom2,paroisse,commentaires) values(:idf_rep,:annee,:mois,:jour,:date_rep,:page,:type_acte,:nom1,:prenom1,:nom2,:prenom2,:paroisse,:commentaires)";
}
else
{
    $connexionBD->initialise_params(array(':idf_rep'=>$i_idf_rep,':idf_acte'=>$i_idf_acte,':annee'=>$i_annee,':mois'=>$i_mois,':jour'=>$i_jour,':date_rep'=>$st_date_rep,':page'=>$i_page,':type_acte'=>$st_type_acte,':nom1'=>$st_nom1,':prenom1'=>$st_prenom1,':nom2'=>$st_nom2,':prenom2'=>$st_prenom2,':paroisse'=>$st_paroisse,':commentaires'=>$st_commentaires));
   $st_requete = "update rep_not_actes set annee=:annee,mois=:mois,jour=:jour,date_rep=:date_rep,page=:page,`type`=:type_acte,nom1=:nom1,prenom1=:prenom1,nom2=:nom2,prenom2=:prenom2,paroisse=:paroisse,commentaires=:commentaires where idf_repertoire=:idf_rep and idf_acte=:idf_acte";
}

$connexionBD->execute_requete($st_requete);
$connexionBD->initialise_params(array(':page'=>$i_page,':annee'=>$i_annee,':mois'=>$i_mois,':idf_rep'=>$i_idf_rep));
$st_requete = "update rep_not_desc set page_courante=:page,annee_courante=:annee,mois_courant=:mois where idf_repertoire=:idf_rep";
$connexionBD->execute_requete($st_requete);

?>