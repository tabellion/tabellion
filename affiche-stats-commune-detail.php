<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';

// ======== Default

$sources = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM source ORDER BY nom");

// ========= Request
$page = $_GET['page'] ?? 1;
$id_source = $_GET['idf_source'] ?? 1;
$id_commune = $_GET['idf_commune'] ?? null;
$id_type_acte = $_GET['idf_type_acte'] ?? null;
if ($id_commune && $id_type_acte) {
    $sql1 = "SELECT ca.nom, c.nom, ca.debut_communale, ca.debut_greffe 
        FROM commune_acte ca 
        LEFT JOIN canton c ON (ca.idf_canton=c.idf) 
        WHERE ca.idf=$id_commune";
    list($gst_nom_commune, $gst_canton, $gi_debut_communale, $gi_debut_greffe) = $connexionBD->sql_select_liste($sql1);
    $gst_type_acte = $connexionBD->sql_select1("SELECT nom FROM type_acte WHERE idf=$id_type_acte");
}

// ======== Log
$pf = @fopen("logs/requetes_depouillements.log", 'a');
// date_default_timezone_set($gst_time_zone); // NB: Doit prendre le timezone du serveur
list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
$i_mois++;
$i_annee += 1900;
$st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
$gst_adresse_ip = $_SERVER['REMOTE_ADDR'];
$st_ident = $session->getAttribute('ident');
$st_chaine_log = join(';', array($st_date_log, $st_ident, $gst_adresse_ip, cp1252_vers_utf8($gst_nom_commune), cp1252_vers_utf8($gst_type_acte)));
@fwrite($pf, "$st_chaine_log\n");
@fclose($pf);
// ============

/*
 * Renvoie une chaine intervalle d'années ou une seule année si l'intervalle est vide
 * @param integer $pi_deb : Année de début
 * @param integer $pi_fin : Année de fin 
 * @return string Chaine représentant l'intervalle   
*/
function chaine_intervalle($pi_deb, $pi_fin)
{
    if ($pi_deb == $pi_fin) {
        return "$pi_deb";
    }

    return "$pi_deb-$pi_fin";
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="content-language" content="fr">
    <link href="assets/css/styles.css" type="text/css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <script src="assets/js/jquery-min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <title>Base <?= SIGLE_ASSO; ?> : Etat des relevés</title>
</head>

<body>
    <div class="container">

        <?php require_once __DIR__ . '/commun/menu.php'; ?>
        <div class="panel panel-primary">
            <div class="panel-heading">
                Liste des années disponibles de: <?= cp1252_vers_utf8($gst_nom_commune) . " (" . cp1252_vers_utf8($gst_type_acte) . ")"; ?>
                <?php if ($gst_canton != '') print("<br> Canton de " . cp1252_vers_utf8($gst_canton)); ?>
                <br>Source: <?= $sources[$id_source]; ?>
            </div>

            <div class="panel-body">
                <blockquote class="blockquote">
                    <?php
                    if ($gi_debut_communale != 0) print("Début de la collection communale: $gi_debut_communale<br>");
                    if ($gi_debut_greffe != 0) print("Début de la collection du greffe: $gi_debut_greffe");
                    print('</blockquote>'); ?>
                    <table class="table table-bordered table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>Années</th>
                                <th>Nombre d'actes</th>
                            </tr>
                        </thead>
                        <?php if ($id_source) {
                            $st_requete = "SELECT DISTINCT annee, count(idf) FROM acte 
                WHERE idf_commune=$id_commune 
                AND idf_type_acte=$id_type_acte 
                AND idf_source=$id_source 
                GROUP BY annee 
                ORDER BY annee";
                        } else {
                            $st_requete = "SELECT DISTINCT annee, count(idf) FROM acte 
                WHERE idf_commune=$id_commune 
                AND idf_type_acte=$id_type_acte 
                GROUP BY annee 
                ORDER BY annee";
                        }

                        $a_liste_annnees = $connexionBD->liste_valeur_par_clef($st_requete);
                        $i_annee_binf = -1;
                        $i_annee_cour = -1;
                        $i_cpt = 0;
                        if (count($a_liste_annnees) > 0) {
                            $i_nb_actes = 0;

                            foreach ($a_liste_annnees as $i_annee => $i_nb_actes_annee) {
                                if ($i_annee == 9999) continue;
                                if ($i_annee == 0) continue;
                                if ($i_annee_binf == -1) {
                                    $i_annee_binf = $i_annee;
                                    $i_annee_cour = $i_annee;
                                    $i_nb_actes = $i_nb_actes_annee;
                                    continue;
                                }
                                if ($i_annee == $i_annee_cour + 1) {
                                    $i_annee_cour++;
                                    $i_nb_actes += $i_nb_actes_annee;
                                } else {
                                    print("<tr>");
                                    print("<td>" . chaine_intervalle($i_annee_binf, $i_annee_cour) . "</td><td >$i_nb_actes</td></tr>\n");
                                    $i_annee_binf = $i_annee;
                                    $i_annee_cour = $i_annee;
                                    $i_nb_actes   = $i_nb_actes_annee;
                                    $i_cpt++;
                                }
                            }
                            print("<tr>");
                            print("<td >" . chaine_intervalle($i_annee_binf, $i_annee_cour) . "</td><td>$i_nb_actes</td></tr>\n");
                        } ?>
                    </table>
            </div>
        </div>
    </div>
</body>

</html>