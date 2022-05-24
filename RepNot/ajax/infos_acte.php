<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('../../Commun/config.php');
require_once('../../Commun/constantes.php');
require_once('../../Commun/ConnexionBD.php');
require_once('../commun_rep_not.php');

$a_resultats = array();
if (isset ($_GET['idf_rep']) && isset ($_GET['idf_acte']))
{
	$i_idf_repertoire = (int) $_GET['idf_rep'];
	$i_idf_acte = (int) $_GET['idf_acte'];;
	$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
	$st_requete = "select idf_acte,annee,mois,jour,date_rep,`type`,nom1,prenom1,nom2,prenom2,paroisse,commentaires from rep_not_actes where idf_repertoire=$i_idf_repertoire and idf_acte=$i_idf_acte";
	$a_infos = $connexionBD->sql_select_liste($st_requete);
	$a_resultats = array();

	list($i_idf_acte,$i_annee,$i_mois,$i_jour,$st_date_rep,$st_type,$st_nom1,$st_prenom1,$st_nom2,$st_prenom2,$st_paroisse,$st_commentaires)=$a_infos;
   $a_resultats['idf_acte'] = $i_idf_acte;
   if ($i_annee==9999)
   {
      $a_resultats['jour']="";
      $a_resultats['mois']="";
      $a_resultats['annee']="";
      $a_resultats['sans_date']=true;
      $a_resultats['date_rep'] = "";
   }
   else
   {
      $a_resultats['jour']=$i_jour;
      $a_resultats['mois']=$i_mois;
      $a_resultats['annee']=$i_annee;
      $a_resultats['sans_date']=false;
      $a_resultats['date_rep'] = utf8_encode($st_date_rep);
   }      
	$a_resultats['type'] = utf8_encode($st_type);
	$a_resultats['nom1'] = utf8_encode($st_nom1);
	$a_resultats['prenom1'] = utf8_encode($st_prenom1);
	$a_resultats['nom2'] = utf8_encode($st_nom2);
	$a_resultats['prenom2'] = utf8_encode($st_prenom2);
	$a_resultats['paroisse'] = utf8_encode($st_paroisse);
   $a_resultats['commentaires'] = utf8_encode($st_commentaires);		      
}
echo json_encode($a_resultats); 
?>