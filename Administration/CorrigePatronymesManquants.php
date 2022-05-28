<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../libs/phonex.cls.php';
require_once __DIR__ . '/chargement/chargement.php';
require_once __DIR__ . '/../Origin/Patronyme.php';
require_once __DIR__ . '/../Origin/TypeActe.php';
require_once __DIR__ . '/../Origin/StatsPatronyme.php';

$st_requete = "SELECT p.idf_acte, a.idf_commune, a.idf_source, a.idf_type_acte 
    FROM personne p 
    JOIN acte a ON (p.idf_acte=a.idf) 
    WHERE p.patronyme not in (SELECT libelle FROM patronyme)";
$a_actes = $connexionBD->sql_select_multiple_par_idf($st_requete);

foreach ($a_actes as $i_idf_acte => $a_champs) {
    list($idf_commune, $i_idf_source, $idf_type_acte) = $a_champs;
    print("Acte $i_idf_acte => [$idf_commune,$i_idf_source,$idf_type_acte]\n");
    $stats_patro = new StatsPatronyme($connexionBD, $idf_commune, $i_idf_source, $idf_type_acte);
    $a_patros = $connexionBD->sql_select("SELECT patronyme FROM personne WHERE idf_acte=$i_idf_acte");
    foreach ($a_patros as $st_patro) {
        print("\tAjout du patronyme $st_patro\n");
        $stats_patro->ajoute_patronyme($st_patro);
    }
    $stats_patro->maj_stats_patronymes_ajoutes($idf_commune, $i_idf_source, $idf_type_acte);
}
