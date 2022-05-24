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
if (isset ($_GET['idf_rep']) && isset ($_GET['annee']) && isset ($_GET['mois']))
{
	$i_idf_repertoire = (int) $_GET['idf_rep'];
	$i_annee = (int) $_GET['annee'];
	$i_mois = (int) $_GET['mois'];
	$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);
	$st_requete = "select idf_acte,annee,mois,jour,date_rep,`type`,nom1,prenom1,nom2,prenom2,paroisse,commentaires from rep_not_actes where idf_repertoire=$i_idf_repertoire and annee=$i_annee and mois=$i_mois order by jour desc";
	$a_actes = $connexionBD->sql_select_multiple($st_requete);
	$a_resultats = array();
	foreach ($a_actes as $a_acte)
	{
		list($i_idf_acte,$i_annee,$i_mois,$i_jour,$st_date_rep,$st_type,$st_nom1,$st_prenom1,$st_nom2,$st_prenom2,$st_paroisse,$st_commentaires)=$a_acte;
		$a_val['idf_acte'] = $i_idf_acte;
    $st_mois = array_key_exists($i_mois,$ga_mois) ? $ga_mois[$i_mois] : 'Sans Mois';   
		$a_val['date'] = ($i_annee==9999) ? "Sans date": sprintf("%d %s %4d",$i_jour,$st_mois,$i_annee);
    $a_val['date_rep'] = utf8_encode($st_date_rep);
		$a_val['type'] = utf8_encode($st_type);
		$a_val['nom1'] = utf8_encode($st_nom1);
		$a_val['prenom1'] = utf8_encode($st_prenom1);
		$a_val['nom2'] = utf8_encode($st_nom2);
		$a_val['prenom2'] = utf8_encode($st_prenom2);
		$a_val['paroisse'] = utf8_encode($st_paroisse);
    $a_val['commentaires'] = utf8_encode($st_commentaires);		 
		$a_resultats[] = $a_val;     
	}
}
echo json_encode($a_resultats); 
?>