<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Origin/CompteurActe.php';
require_once __DIR__ . '/../Origin/Acte.php';
require_once __DIR__ . '/../Origin/CompteurPersonne.php';
require_once __DIR__ . '/../Origin/Personne.php';
require_once __DIR__ . '/../Origin/Prenom.php';
require_once __DIR__ . '/../Origin/Profession.php';
require_once __DIR__ . '/../Origin/CommunePersonne.php';
require_once __DIR__ . '/../Origin/TypeActe.php';
require_once __DIR__ . '/../Origin/Union.php';

// Redirect to identification
if (!$session->isAuthenticated()) {
    $session->setAttribute('url_retour', '/administration/gestion-communes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}
if (!in_array('CHGMT_EXPT', $user['privileges'])) {
    header('HTTP/1.0 401 Unauthorized');
    exit;
}


if (isset($_REQUEST['idf_acte'])) {
    $i_idf_acte = (int) $_REQUEST['idf_acte'];
    header("Content-type: text/csv");
    header("Expires: 0");
    header("Pragma: public");
    header("Content-disposition: attachment; filename=\"ExportNimV3-$i_idf_acte.csv\"");
    $pf = @fopen('php://output', 'w');
    $gi_idf_acte = (int) $_REQUEST['idf_acte'];
    $go_acte = new Acte($connexionBD, null, null, null, null, null, null);
    $go_acte->charge($gi_idf_acte);
    $a_col_personnes = $go_acte->colonnes_entete_nimv3();
    $a_col_personnes = array_merge($a_col_personnes, $go_acte->liste_personnes_nimv3());
    $a_col_personnes[] = str_replace("\r\n", '§', $go_acte->getCommentaires());
    $a_col_personnes[] = '';
    $a_col_personnes[] = $go_acte->getUrl();
    fwrite($pf, (join(';', $a_col_personnes)));
    fwrite($pf, "\r\n");
    fclose($pf);
    exit();
} else
    die("idf_acte n'est pas défini");
