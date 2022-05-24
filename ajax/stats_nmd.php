<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('../Commun/config.php');
require_once('../Commun/constantes.php');
require_once('../Commun/ConnexionBD.php');
require_once('../RequeteRecherche.php');

$gi_idf_commune_acte = isset($_GET['idf_commune_acte']) ? (int) $_GET['idf_commune_acte'] : '';
$gc_idf_type_acte = isset($_GET['idf_type_acte']) ? (int) $_GET['idf_type_acte'] : '';
$gi_annee_min = isset($_GET['annee_min']) ? (int) $_GET['annee_min'] : '';
$gi_annee_max = isset($_GET['annee_max']) ? (int) $_GET['annee_max'] : '';
$gi_rayon = isset($_GET['rayon']) ? (int) $_GET['rayon'] : '';

$ga_couleurs =array("#FF7F50","#00008B","#BDB76B","#8FBC8F","#00BFFF","#FFD700","#4B0082","#D3D3D3","#FFA07A","#7B68EE","#6B8E23","#EEE8AA","#FFC0CB","#BC8F8F","#A0522D","#708090","#00FF7F","#FF6347","#EE82EE","#F0F8FF");
  
$a_resultats = array();
if (!empty ($gi_idf_commune_acte) && !empty ($gc_idf_type_acte) && !empty ($gi_annee_min) && !empty ($gi_annee_max))
{
	$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
	$requeteRecherche = new RequeteRecherche($connexionBD);  
	$st_clause_communes = $requeteRecherche->clause_droite_commune($gi_idf_commune_acte,$gi_rayon,'');
	$st_requete= "select ca.nom,a.annee,count(*) from acte a join commune_acte ca on (a.idf_commune=ca.idf) where a.idf_commune $st_clause_communes and a.idf_type_acte=$gc_idf_type_acte and a.annee>=$gi_annee_min and a.annee<=$gi_annee_max group by ca.nom,a.annee";
	//print("Req=$st_requete<br>");
	$ga_donnees = $connexionBD->liste_valeur_par_doubles_clefs($st_requete);
	$a_annees = range($gi_annee_min,$gi_annee_max,1);
	// empile les étiquettes
	$a_resultats['labels'] = $a_annees;
	$i=0;
	$a_resultats['donnees'] = array();	
	foreach ($ga_donnees as $st_commune => $a_stat_com)
	{
		// empile les catégories de graphes
		$a_serie_courante = array();
    $st_commune = mb_convert_encoding($st_commune, "UTF-8","Windows-1252");
		$a_serie_courante['label'] = "$st_commune";
		$a_serie_courante['borderColor'] = $ga_couleurs[$i];
    $a_serie_courante['backgroundColor'] = $ga_couleurs[$i];
    $a_serie_courante['fill'] = false;
		//complète les séries
		$a_donnees =array();
		foreach ($a_annees as $i_annee)
		{			
			$a_donnees[] = array_key_exists($i_annee,$a_stat_com) ? intval($a_stat_com[$i_annee][0]): null;
		}
		$a_serie_courante['data']=$a_donnees;
		$a_resultats['donnees'][]=$a_serie_courante;
		$i=$i<count($ga_couleurs)-1?$i+1:0;
	}
}
echo json_encode($a_resultats);
?>