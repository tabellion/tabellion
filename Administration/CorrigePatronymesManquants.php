<?php
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../libs/phonex.cls.php';
require_once __DIR__ . '/chargement/chargement.php';
require_once __DIR__ . '/chargement/Patronyme.php';
require_once __DIR__ . '/chargement/TypeActe.php';
require_once __DIR__ . '/chargement/StatsPatronyme.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);
$st_requete = "select p.idf_acte,a.idf_commune,a.idf_source,a.idf_type_acte from personne p join acte a on (p.idf_acte=a.idf) where p.patronyme not in (select libelle from patronyme)";
$a_actes = $connexionBD->sql_select_multiple_par_idf($st_requete);

foreach ($a_actes as $i_idf_acte => $a_champs) {
    list($idf_commune, $i_idf_source, $idf_type_acte) = $a_champs;
    print("Acte $i_idf_acte => [$idf_commune,$i_idf_source,$idf_type_acte]\n");
    $stats_patro = new StatsPatronyme($connexionBD, $idf_commune, $i_idf_source, $idf_type_acte);
    $a_patros = $connexionBD->sql_select("select patronyme from personne where idf_acte=$i_idf_acte");
    foreach ($a_patros as $st_patro) {
        print("\tAjout du patronyme $st_patro\n");
        $stats_patro->ajoute_patronyme($st_patro);
    }
    $stats_patro->maj_stats_patronymes_ajoutes($idf_commune, $i_idf_source, $idf_type_acte);
}
