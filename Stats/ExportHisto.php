<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_STATS);
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/ConnexionBD.php';

$ga_mois = array(
    1 => 'Janvier',
    2 => 'Fevrier',
    3 => 'Mars',
    4 => 'Avril',
    5 => 'Mai',
    6 => 'Juin',
    7 => 'Juillet',
    8 => 'Aout',
    9 => 'Septembre',
    10 => 'Octobre',
    11 => 'Novembre',
    12 => 'Decembre'
);

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

function ecrit_tableau_stats($pfh, $pst_lib, $pa_valeurs)
{
    global $ga_mois;
    $a_lib = array_values($ga_mois);
    array_unshift($a_lib, $pst_lib);
    fputcsv($pfh, $a_lib, SEP_CSV);
    foreach ($pa_valeurs as $i_annee => $a_ligne) {
        $a_ligne_stat = array($i_annee);
        foreach ($ga_mois as $i_mois => $st_mois) {
            if (array_key_exists($i_mois, $a_ligne))
                $a_ligne_stat[] = $a_ligne[$i_mois][0];
            else
                $a_ligne_stat[] = '';
        }
        fputcsv($pfh, $a_ligne_stat, SEP_CSV);
    }
    fputcsv($pfh, array('', '', '', '', '', '', '', '', '', '', '', '', ''), SEP_CSV);
}
$st_requete_v4_tot = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM demandes_adherent da  GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
$a_v4_tot = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_v4_tot);

$st_requete_v4_cm = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM demandes_adherent da where da.idf_type_acte=" . IDF_CM . " GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
$a_v4_cm = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_v4_cm);

$st_requete_v4_mar = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM demandes_adherent da where da.idf_type_acte=" . IDF_MARIAGE . " GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
$a_v4_mar = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_v4_mar);

$st_requete_v4_nai = 'SELECT year(date_demande),month(date_demande), count( * ) AS nombre FROM demandes_adherent where idf_type_acte=' . IDF_NAISSANCE . ' GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc';
$a_v4_nai = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_v4_nai);

$st_requete_v4_dec = 'SELECT year(date_demande),month(date_demande), count( * ) AS nombre FROM demandes_adherent where idf_type_acte=' . IDF_DECES . ' GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc';
$a_v4_dec = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_v4_dec);

if (!empty($gst_administrateur_gbk)) {

    $st_requete_gbk_tot = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM stats_gbk  GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
    $a_gbk_tot = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_gbk_tot);

    $st_requete_gbk_cm = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM stats_gbk where idf_type_acte=" . IDF_CM . " GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
    $a_gbk_cm = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_gbk_cm);

    $st_requete_gbk_mar = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM stats_gbk where idf_type_acte=" . IDF_MARIAGE . " GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
    $a_gbk_mar = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_gbk_mar);

    $st_requete_gbk_nai = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM stats_gbk where idf_type_acte=" . IDF_NAISSANCE . " GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
    $a_gbk_nai = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_gbk_nai);

    $st_requete_gbk_dec = "SELECT year(date_demande),month(date_demande),count(*)as nombre FROM stats_gbk where idf_type_acte=" . IDF_DECES . " GROUP BY year(date_demande),month(date_demande) ORDER BY year(date_demande) asc";
    $a_gbk_dec = $connexionBD->liste_valeur_par_doubles_clefs($st_requete_gbk_dec);
}

header("Content-type: text/csv");
header("Expires: 0");
header("Pragma: public");
header("Content-disposition: attachment; filename=\"stats_di.csv\"");
$fh = @fopen('php://output', 'w');
ecrit_tableau_stats($fh, 'V4 Total', $a_v4_tot);
ecrit_tableau_stats($fh, 'V4 CM', $a_v4_cm);
ecrit_tableau_stats($fh, 'V4 Mar', $a_v4_mar);
ecrit_tableau_stats($fh, 'V4 Nai', $a_v4_nai);
ecrit_tableau_stats($fh, 'V4 Dec', $a_v4_dec);
if (!empty($gst_administrateur_gbk)) {
    ecrit_tableau_stats($fh, 'GBK Total', $a_gbk_tot);
    ecrit_tableau_stats($fh, 'GBK CM', $a_gbk_cm);
    ecrit_tableau_stats($fh, 'GBK Mar', $a_gbk_mar);
    ecrit_tableau_stats($fh, 'GBK Nai', $a_gbk_nai);
    ecrit_tableau_stats($fh, 'GBK Dec', $a_gbk_dec);
}
fclose($fh);
exit();

print('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN"><html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<title>Statistiques</title>");
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/menu.js' type='text/javascript'></script>");
print("</head>");
print("<body>");
print('</body></html>');
