<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once('../Commun/config.php');
require_once('../Commun/constantes.php');
require_once('../Commun/Identification.php');

// La page est reservee uniquement aux gens ayant les droits d'import/export
require_once('../Commun/VerificationDroits.php');
verifie_privilege(DROIT_VALIDATION_TD);
require_once '../Commun/ConnexionBD.php';
require_once('../Commun/commun.php');

/**
 * Exporte l'index des noms pour Geneanet en sortie
 * Exemple d'export :
 * BATARD;dÚcÞs;1777;1777;1;CLAIX;F16;PCH;FRA;C
 * BATARDE;dÚcÞs;1791;1791;1;BECHERESSE;F16;PCH;FRA;C
 */ 

$st_delimiteur = ';';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);   
$connexionBD->execute_requete("select p.libelle,sp.annee_min,sp.annee_max,sp.nb_personnes,ca.nom from stats_patronyme sp join patronyme p on (sp.idf_patronyme=p.idf) join commune_acte ca on (sp.idf_commune=ca.idf) join type_acte ta on (sp.idf_type_acte=ta.idf) join source s on (sp.idf_source=s.idf) where s.idf=".IDF_SOURCE_TD." and sp.idf_type_acte=".IDF_MARIAGE." and p.libelle REGEXP '^[A-Za-z ()]+$' ");
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=le_tdm.csv');

$fp=fopen("php://output",'w') or die ("Impossible d'écrire le fichier en sortie");
while (list($st_patro,$i_annee_min,$i_annee_max,$i_nb_personnes,$st_commune)=$connexionBD->ligne_suivante_resultat())
{
  $st_ligne = join($st_delimiteur,array($st_patro,'',$i_annee_min,$i_annee_max,$i_nb_personnes,$st_commune,$gst_code_dpt_geneabank,$gst_code_region_geneabank,$gst_code_pays_geneabank,'L'));
  fwrite($fp,"$st_ligne\r\n");      
}
fclose($fp);
 
?>
