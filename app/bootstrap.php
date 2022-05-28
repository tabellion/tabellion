<?php 
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Core/Session.php';
require __DIR__ . '/Core/Configuration.php';
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../commun/commun.php';
require_once __DIR__ . '/../Origin/ConnexionBD.php';

if (!file_exists(__DIR__ . '/../config.yaml.cfg')) {
    echo "Une mise à jour de l'application est necessaire.";
    exit;
}

$session = new Session();
$config = new Configuration();
$databasecfg = $config->get('database');

$connexionBD = ConnexionBD::singleton($databasecfg);

$user = $session->getAttribute('user') ?? [];

require_once __DIR__ . '/../commun/verification-droits.php';