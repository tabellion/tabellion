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
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
$st_requete = "select rnd.idf_repertoire,rnd.`nom_notaire`,ca.nom,rnd.`cote` from rep_not_desc rnd join commune_acte ca on (rnd.idf_commune=ca.idf) where rnd.publication='O'";
if (isset($_GET['idf_commune_notaire']) &&! empty($_GET['idf_commune_notaire']))
{
	$st_requete.= " and rnd.idf_commune=".(int) $_GET['idf_commune_notaire'];
}

$st_requete .= " order by rnd.`nom_notaire`";
$a_rep_not = $connexionBD->sql_select_multiple($st_requete);
$a_resultats = array();

foreach ($a_rep_not as $a_notaire)
{
	list($i_idf_rep,$st_notaire,$st_commune,$st_cote) = $a_notaire;
  $a_resultats[$i_idf_rep] = sprintf("%s - %s (%s)",utf8_encode($st_notaire),utf8_encode($st_commune),utf8_encode($st_cote));     
}

echo json_encode($a_resultats);  
?>