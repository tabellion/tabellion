<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('../Commun/config.php');
require_once('../Commun/constantes.php');
require_once('../Commun/ConnexionBD.php');

$gi_annee = isset($_GET['annee']) ? (int) $_GET['annee'] : '';
$gi_idf_canton = isset($_GET['idf_canton']) ? (int) $_GET['idf_canton'] : '';

$ga_couleurs =array("#FF7F50","#00008B","#BDB76B","#8FBC8F","#00BFFF","#FFD700","#4B0082","#D3D3D3","#FFA07A","#7B68EE","#6B8E23","#EEE8AA","#FFC0CB","#BC8F8F","#A0522D","#708090","#00FF7F","#FF6347","#EE82EE","#F0F8FF");
$ga_mois = array('JAN','FEV','MAR','APR','MAI','JUIN','JUIL','AOUT','SEPT','OCT','NOV','DEC');
  
$a_resultats = array();
if (!empty ($gi_annee) && !empty ($gi_idf_canton))
{
	$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);	
	$st_requete= "select ca.nom,month(date_demande) as mois, count(*) from stats_gbk sg join commune_acte ca on (sg.idf_commune=ca.idf) join canton c on (ca.idf_canton=c.idf) where c.idf=$gi_idf_canton and year(date_demande)=$gi_annee group by ca.nom, month(date_demande) order by ca.nom, mois";
	//print("Req=$st_requete<br>");
	$ga_donnees = $connexionBD->liste_valeur_par_doubles_clefs($st_requete);	
	// empile les étiquettes
	$a_resultats['labels'] = $ga_mois;
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
		$a_mois = range(1,12);
		foreach ($a_mois as $i_mois)
		{			
			$a_donnees[] = array_key_exists($i_mois,$a_stat_com) ? intval($a_stat_com[$i_mois][0]): 0;
		}
		$a_serie_courante['data']=$a_donnees;
		$a_resultats['donnees'][]=$a_serie_courante;
		$i=$i<count($ga_couleurs)-1?$i+1:0;
	}
}
echo json_encode($a_resultats);
?>