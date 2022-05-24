<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once '../Commun/config.php';
require_once '../Commun/constantes.php';
require_once('../Commun/Identification.php');
require_once('../Commun/VerificationDroits.php');
require_once 'chargement/CompteurActe.php';	
require_once 'chargement/Acte.php';
require_once 'chargement/CompteurPersonne.php';		
require_once 'chargement/Personne.php';
require_once 'chargement/Prenom.php';
require_once 'chargement/Profession.php';
require_once 'chargement/CommunePersonne.php';
require_once 'chargement/TypeActe.php';
require_once 'chargement/Union.php';

verifie_privilege(DROIT_CHARGEMENT);

require_once '../Commun/ConnexionBD.php';
require_once '../Commun/commun.php';

if (isset($_REQUEST['idf_acte']))
{
   $i_idf_acte = (int) $_REQUEST['idf_acte'];
   header("Content-type: text/csv");
   header("Expires: 0");
   header("Pragma: public");
   header("Content-disposition: attachment; filename=\"ExportNimV3-$i_idf_acte.csv\"");   
   $pf = @fopen('php://output', 'w');
   $gi_idf_acte= (int) $_REQUEST['idf_acte'];
	 $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
   $go_acte = new Acte($connexionBD,null,null,null,null,null,null);
   $go_acte->charge($gi_idf_acte);
   $a_col_personnes= $go_acte->colonnes_entete_nimv3();
   $a_col_personnes=array_merge($a_col_personnes,$go_acte->liste_personnes_nimv3());
   $a_col_personnes[]=str_replace("\r\n",'§',$go_acte->getCommentaires());
   $a_col_personnes[]='';
   $a_col_personnes[]=$go_acte->getUrl();
   fwrite($pf,(join(';',$a_col_personnes)));
   fwrite($pf,"\r\n");
   fclose($pf);
   exit(); 
}
else
  die("idf_acte n'est pas défini");

?>