<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
session_start();

$gst_chemin = "../";
//$gst_chemin = "";
require_once("$gst_chemin/Commun/config.php");
require_once("$gst_chemin/Commun/constantes.php");
require_once("$gst_chemin/Commun/ConnexionBD.php");
require_once("$gst_chemin/Commun/commun.php"); 

/*
print("<pre>");
print_r($_POST);
print("</pre>");
*/

$gst_session_statut = isset($_SESSION['statut']) ? $_SESSION['statut'] : '';
$gst_session_type = isset($_SESSION['type']) ? $_SESSION['type'] : '';
$ga_session_aides = isset($_SESSION['aides']) ? $_SESSION['aides']: array();
$gi_session_origine = isset($_SESSION['type_origine']) ? $_SESSION['type_origine'] : 0;
$gst_session_origine = isset($_SESSION['description_origine']) ? $_SESSION['description_origine'] : '';  

$gst_statut = isset($_POST['statut']) ? $_POST['statut']: $gst_session_statut;
$gst_type = isset($_POST['type']) ? $_POST['type']: $gst_session_type;
$ga_aides = isset($_POST['aide']) ? $_POST['aide']: $ga_session_aides;
$gi_origine = isset($_POST['type_origine']) ?  (int) $_POST['type_origine'] : $gi_session_origine; 
$gst_origine = isset($_POST['description_origine']) ? trim($_POST['description_origine']) : $gst_session_origine; 

$gst_aides = array_sum($ga_aides);

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
switch ($gst_type)
{
  case TYPE_INSCRIPTION:
      $gi_idf_prov = (int) $_POST['idf_prov'];    
      $st_requete = "select ins_nom, ins_prenom,ins_email_perso,ins_cp,ins_pays,ins_token from inscription_prov where idf = :idf_prov";
	  $connexionBD->initialise_params(array(':idf_prov'=>$gi_idf_prov)); 
      $a_adh = $connexionBD->sql_select_liste($st_requete);
      if (count($a_adh)==0)
        die("Identifiant provisoire inconnu: $gi_idf_prov<br>"); 
      list($st_nom_adh,$st_prenom_adh,$st_email_adh,$st_cp_adh,$st_pays_adh,$st_jeton_ins) = $a_adh;
      setlocale(LC_CTYPE, 'fr_FR.UTF8');
      $st_nom_adh = trim(strip_tags(iconv("UTF-8", "ASCII//TRANSLIT", $_POST['nom'])));
      $st_prenom_adh = trim(strip_tags(iconv("UTF-8", "ASCII//TRANSLIT", $_POST['prenom'])));
      // suppression des espaces
      $st_nom_adh = preg_replace('/\s/','',$st_nom_adh);
      $st_prenom_adh = preg_replace('/\s/','',$st_prenom_adh);
      // suppresion des apostrophes
      $st_nom_adh = preg_replace("/'/",'',$st_nom_adh);
      $st_prenom_adh = preg_replace("/'/",'',$st_prenom_adh);
      // suppression des &
      $st_nom_adh = preg_replace("/\&/",'',$st_nom_adh); 
      $i_idf_agc = $gi_idf_prov;
      $gst_ref = implode('_',array('INSCRIPTION',$st_nom_adh,$st_prenom_adh,$gi_idf_prov));
      
  break;    
  case TYPE_READHESION:
      if(!isset($_SESSION['ident']))
      {         
         $_SESSION['statut']=$gst_statut;
         $_SESSION['type']=$gst_type;
         $_SESSION['aides']=$ga_aides;
         $_SESSION['type_origine']=$gi_origine;
         $_SESSION['description_origine']=$gst_origine;
         require_once('../Commun/Identification.php');
      }
      if(!isset($_SESSION['ident']))
         die("<div class=\"alert alert-danger\"> Identifiant non reconnu</div>");
      $gst_ident = $_SESSION['ident'];
	  $connexionBD->initialise_params(array(':ident'=>$gst_ident)); 
      $a_adh_agc= $connexionBD->sql_select_liste("select idf,nom,prenom,email_perso,cp,pays,annee_cotisation,jeton_paiement from adherent where ident=:ident");
      if (empty($a_adh_agc))
         die("<div class=\"alert alert-danger\"> Identifiant ".SIGLE_ASSO." non retrouv&eacute;</div>");
      list($i_idf_agc,$st_nom_adh,$st_prenom_adh,$st_email_adh,$st_cp_adh,$st_pays_adh,$i_annee_cotisation_adh)= $a_adh_agc;
      setlocale(LC_CTYPE, 'fr_FR.UTF8');
      $st_nom_adh = trim(strip_tags(iconv("UTF-8", "ASCII//TRANSLIT", $st_nom_adh)));
      $st_prenom_adh = trim(strip_tags(iconv("UTF-8", "ASCII//TRANSLIT", $st_prenom_adh)));
      // suppression des espaces
      $st_nom_adh = preg_replace('/\s/','',$st_nom_adh);
      $st_prenom_adh = preg_replace('/\s/','',$st_prenom_adh);
      // suppresion des apostrophes
      $st_nom_adh = preg_replace("/'/",'',$st_nom_adh);
      $st_prenom_adh = preg_replace("/'/",'',$st_prenom_adh);
      $localtime = localtime();
      $st_temps = sprintf("%02d%02d%04d%02d%02d",$localtime[3],$localtime[4]+1,$localtime[5]+1900,$localtime[2],$localtime[1]);
      $gst_ref = implode('_',array('READHESION',$st_nom_adh,$st_prenom_adh,$i_idf_agc,$st_temps));
  break;
  default:
    die("<div class=\"alert alert-danger\">Type d'inscription ($gst_type) inconnu</div>");      
}

// calcul du tarif
// même code que ce soit une première inscription ou une réadhésion
switch ($gst_statut)
{
  case ADHESION_INTERNET:
    $i_tarif = $ga_tarifs['internet'];
  break;
  case ADHESION_BULLETIN:
    if ($st_pays_adh=="France" &&  preg_match('/^\d+$/',$st_cp_adh) && substr($st_cp_adh,0,2)<96 )
       $i_tarif = $ga_tarifs['bulletin_metro'];
    else
       $i_tarif = $ga_tarifs['bulletin_etranger'];   
  break;
  default:
    die("<div class=\"alert alert-danger\"> Statut $st_statut invalide</div>");
}

// INITIALISATION
require_once("include.php");

$array = array();
$payline = new paylineSDK();

// PAYEMENT
$array['payment']['amount'] = $i_tarif*100;
$array['payment']['currency'] = 978;

// ORDRE
$array['order']['ref'] =  substr($gst_ref,0,50);
$array['order']['amount'] = $i_tarif;;
$array['order']['currency'] = 978;

// ACHETEUR
$array['buyer']['lastName'] = $st_nom_adh;
$array['buyer']['firstName'] = $st_prenom_adh;
$array['buyer']['customerId'] = $i_idf_agc;
$array['buyer']['email'] = $st_email_adh;
                                              
// EXECUTION
$result = $payline->do_webpayment($array);

if(isset($result) && $result['result']['code'] == '00000' )
//if (true)
{
    $st_token = $result['token'];
    // mise à jour des statuts, prix et aides possibles
    switch ($gst_type)
    {
      case TYPE_INSCRIPTION:
        if (empty($st_jeton_ins))
		{
		   $connexionBD->initialise_params(array(':statut'=>$gst_statut,':tarif'=>$i_tarif,':aides'=>$gst_aides,':i_origine'=>$gi_origine,':s_origine'=>utf8_vers_cp1252($gst_origine),':token'=>$st_token,':idf_prov'=>$gi_idf_prov));
           $st_requete = "update `inscription_prov` set ins_date_paiement=now(),ins_statut=:statut, ins_prix=:tarif, ins_aide = :aides, ins_type_origine=:i_origine,ins_description_origine=:s_origine,ins_type='".TYPE_INSCRIPTION."',ins_token=:token where idf = :idf_prov";  
        }
		else
		{
		   $connexionBD->initialise_params(array(':statut'=>$gst_statut,':tarif'=>$i_tarif,':aides'=>$gst_aides,':i_origine'=>$gi_origine,':s_origine'=>utf8_vers_cp1252($gst_origine),':token'=>$st_token,':idf_prov'=>$gi_idf_prov));
           $st_requete = "
           insert into `inscription_prov`(ins_date_paiement,ins_date, ins_nom, ins_prenom, ins_adr1, ins_adr2, ins_cp, ins_commune, ins_pays, ins_email_perso, ins_site_web, ins_telephone, ins_cache, ins_idf_agc, ins_alea, ins_valid,ins_mdp,ins_statut,ins_prix,ins_aide,ins_type_origine,ins_description_origine,ins_type,ins_token) 
           select now(),ins_date, ins_nom, ins_prenom, ins_adr1, ins_adr2, ins_cp, ins_commune, ins_pays, ins_email_perso, ins_site_web, ins_telephone, ins_cache, ins_idf_agc, ins_alea, ins_valid,ins_mdp,:statut,$:tarif,:aides,:i_origine,:s_origine,'".TYPE_INSCRIPTION."',:token from `inscription_prov` where idf=:idf_prov";
		}
        $connexionBD->execute_requete($st_requete);
      break;    
      case TYPE_READHESION:
	    $connexionBD->initialise_params(array(':idf_agc'=>$i_idf_agc,':nom_adh'=>$st_nom_adh,':prenom_adh'=>$st_prenom_adh,':email_adh'=>$st_email_adh,':statut'=>$gst_statut,':tarif'=>$i_tarif,':aides'=>$gst_aides,':i_origine'=>$gi_origine,':s_origine'=>utf8_vers_cp1252($gst_origine),':token'=>$st_token,':idf_prov'=>$gi_idf_prov));
        $st_requete = "insert `inscription_prov`(ins_date_paiement,ins_idf_agc,ins_nom,ins_prenom,ins_email_perso,ins_mdp,ins_statut,ins_prix,ins_aide,ins_type_origine,ins_description_origine,ins_type,ins_token) select now(),:idf_agc,:nom_adh,:prenom_adh,:email_adh,mdp,:statut,:tarif,:aides,:i_origine,:s_origine,'".TYPE_READHESION."',:token from adherent where idf=:idf_agc";  
        $connexionBD->execute_requete($st_requete);
      break;
      default:
        die("<div class=\"alert alert-danger\"> Type d'inscription inconnu</div>");      
    }
		header("location:".$result['redirectURL']);
		exit();
}
elseif(isset($result))
{
	echo '<div class="alert alert-danger"> ERROR : '.$result['result']['code']. ' '.$result['result']['longMessage'].' </div>';
}
	
?>