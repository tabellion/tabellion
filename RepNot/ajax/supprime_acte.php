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

if (isset($_GET['idf_rep']) && isset ($_GET['idf_acte']))
{

	$i_idf_repertoire = (int) $_GET['idf_rep'];
	$i_idf_acte = (int) $_GET['idf_acte'];
	$st_requete = "delete from rep_not_actes where idf_repertoire=$i_idf_repertoire and idf_acte=$i_idf_acte";
  $connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
	$connexionBD->execute_requete($st_requete);
} 
else
{
	echo "Pas d'identifiant de répertoire et/ou d'actes spécifiés";	
}	
?>