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
    $st_rech = "$st_rech%";
    $connexionBD->initialise_params(array(':rech' => $st_rech));
    $st_requete = "select distinct prenom from (select `prenom1` as prenom from rep_not_actes where `prenom1` COLLATE latin1_german1_ci like :rech union all select `prenom2` as prenom from rep_not_actes where `prenom2` COLLATE latin1_german1_ci like :rech) AS R order by prenom";
    $a_patros = $connexionBD->sql_select($st_requete);
    $a_resultats = array();
    foreach ($a_patros as $st_patro) {
        $a_val = array();
        $a_val['label'] = sprintf("%s", utf8_encode($st_patro));
        $a_val['value'] = sprintf("%s", utf8_encode($st_patro));
        $a_resultats[] = $a_val;
    }
}
echo json_encode($a_resultats);
