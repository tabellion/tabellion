<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
$gst_chemin = "../";

$gb_test = false;
//$gst_chemin = "";
require_once("$gst_chemin/Commun/config.php");
require_once("$gst_chemin/Commun/constantes.php");
require_once("$gst_chemin/Commun/ConnexionBD.php");
require_once("$gst_chemin/Commun/commun.php");
require_once("$gst_chemin/Commun/GestionAdherents.php");
require_once("$gst_chemin/Commun/Adherent.php"); 

// INITIALIZE
//FBO
require_once("include.php");

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

$array = array();

//FBO
$payline = new paylineSDK();

$st_token = isset($_GET['token']) ? $_GET['token'] : '';
$a_res = array();
//VERSION
if(isset($_POST['version'])){
	$a_res['version'] = $_POST['version'];
}else{
    $a_res['version'] = '';
}

$pf=@fopen("$gst_rep_logs/inscriptions.log",'a');
date_default_timezone_set($gst_time_zone);
list($i_sec,$i_min,$i_heure,$i_jmois,$i_mois,$i_annee,$i_j_sem,$i_j_an,$b_hiver)=localtime();
$i_mois++;
$i_annee+=1900;
$st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d",$i_jmois,$i_mois,$i_annee,$i_heure,$i_min,$i_sec);

//FBO
$response = $payline->get_webPaymentDetails($st_token,$a_res);
//FBO
if ($response['result']['code'] == '00000' )
//FBO
//if (true)
{
  $connexionBD->initialise_params(array(':token'=>$st_token));
  $st_requete = "select i_p.idf,i_p.ins_idf_agc,i_p.ins_type,adht.statut as ancien_statut from `inscription_prov` i_p left join `adherent` adht on (i_p.ins_idf_agc=adht.idf) where i_p.ins_token = :token";
  list($i_idf_ins_prov,$i_idf_agc,$st_type_adhesion,$st_ancien_statut) = $connexionBD->sql_select_liste($st_requete);
  if (empty($st_ancien_statut))
  {
	   // l'adhérent doit forcément être créé
	   $adherent = new Adherent($connexionBD,null);
	   $adherent->initialise_inscription_en_ligne($st_token);
	   $adherent->cree();
  }
  else
  {
	   // le compte généabank doit être recréé
	   $adherent = new Adherent($connexionBD,$i_idf_agc);
     if ($st_ancien_statut==ADHESION_SUSPENDU)
		   $adherent->reactive();
	   $adherent->initialise_readhesion_en_ligne($st_token);
	   $adherent->modifie();
	   $adherent->modifie_adhesion();
	   $adherent->envoie_message_readhesion();
  }
  $st_chaine_log = sprintf("%s;%s;%s;%d;%s",$st_date_log,$_SERVER['REQUEST_URI'],$st_token,$i_idf_agc,$st_type_adhesion);
  $connexionBD->initialise_params(array(':token'=>$st_token));
  $st_requete = "delete from `inscription_prov` where ins_token=:token";
  $connexionBD->execute_requete($st_requete);
}
else
{
   $st_erreur = "Payement non valid&eacute";
   $st_chaine_log = sprintf("%s;%s;%s;%s",$st_date_log,$_SERVER['REQUEST_URI'],$st_token,$st_erreur);
   print("$st_erreur<br>");   
}
@fwrite($pf,"$st_chaine_log\n");
@fclose($pf);

?>