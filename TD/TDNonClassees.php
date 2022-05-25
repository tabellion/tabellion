<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association G�n�alogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique G�n�rale GPL GNU publi�e par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_VALIDATION_TD);
require_once __DIR__ . '/../Commun/commun.php';

$st_delimiteur = ';';

$st_requete = "select c.nom,ca.nom,count(*) from acte a join commune_acte ca on (a.idf_commune=ca.idf) join canton c on (ca.idf_canton=c.idf) where a.idf_source=1  and a.idf_type_acte=1 and a.annee > 1794 and a.details_supplementaires=0 group by c.nom,ca.nom order by c.nom,ca.nom
";

$a_nb_actes = $connexionBD->sql_select_multiple($st_requete);
if (count($a_nb_actes) > 0) {
    $fp = fopen("php://output", 'w') or die("Impossible d'�crire le fichier en sortie");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=tdm_non_classees.csv');
    fputcsv($fp, array('Canton', 'Commune', 'Nombre d\'actes'), $st_delimiteur);
    foreach ($a_nb_actes as $a_ligne) {
        fputcsv($fp, $a_ligne, $st_delimiteur);
    }
    fclose($fp);
}
