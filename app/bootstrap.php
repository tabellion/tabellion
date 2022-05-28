<?php 

require __DIR__ . '/Core/Session.php';

require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../commun/commun.php';

require_once __DIR__ . '/../Origin/ConnexionBD.php';

$session = new Session();
$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);


$databasecfg = [
    'host' => $gst_serveur_bd,
    'user' => $gst_utilisateur_bd,
    'password' => $gst_mdp_utilisateur_bd,
    'dbname' => $gst_nom_bd
];

$user = $session->getAttribute('user') ?? [];

require_once __DIR__ . '/../commun/verification-droits.php';