<?php 

require __DIR__ . '/Core/Session.php';
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/ConnexionBD.php';

$session = new Session();

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);