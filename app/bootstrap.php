<?php 
error_reporting(E_ALL);

use App\Core\Session;
use App\Core\Configuration;
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

$adresse_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';

$session = new Session();
$config = new Configuration();
$dbconfig = $config->get('database');

$connexionBD = ConnexionBD::singleton($config->get('database'));

$user = $session->getAttribute('user') ?? [];

// TODO: faire une fonctionalité
$errors = []; // Flash message = ['type' => 'level_type', 'message' => 'Le message']