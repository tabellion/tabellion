<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';

$a_resultats = array();
if (isset($_GET['term'])) {
    $st_rech = substr(trim($_GET['term']), 0, 30);
    $st_rech = utf8_decode($st_rech);
    $st_requete = "select idf,nom,code_insee from commune_acte where nom COLLATE latin1_german1_ci like '%$st_rech%'";
    $a_communes = $connexionBD->sql_select_multiple_par_idf($st_requete);
    $a_resultats = array();
    foreach ($a_communes as $i_idf => $a_ligne) {
        list($st_commune, $st_insee) = $a_ligne;
        $a_val = array();
        $a_val['label'] = sprintf("%s (%s)", utf8_encode($st_commune), $st_insee);
        $a_val['value'] = sprintf("%s (%s)", utf8_encode($st_commune), $st_insee);
        $a_resultats[] = $a_val;
    }
}
echo json_encode($a_resultats);
