<?php

require_once __DIR__ . '/../../commun/config.php';
require_once __DIR__ . '/../../commun/constantes.php';
require_once __DIR__ . '/../../Origin/ConnexionBD.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

$i_nb_cnx = $connexionBD->sql_select1("select count(*) from adherent where date(derniere_connexion)=date(DATE_SUB(now(), INTERVAL 1 DAY))");

$connexionBD->execute_requete("insert into stats_cnx(date,nbre) values(DATE_SUB(now(),INTERVAL 1 DAY),'$i_nb_cnx')");

$connexionBD->ferme();

print("Script terminé<br>");
