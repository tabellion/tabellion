<?php
require_once 'Commun/config.php';
require_once('Commun/Identification.php');
require_once('Commun/constantes.php');
require_once 'Commun/commun.php';
require_once('Commun/ConnexionBD.php');
require_once('Commun/PaginationTableau.php');
print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=cp1252" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='js/jquery-min.js' type='text/javascript'></script>");
print("<script src='js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');

/******************************************************************************/
/*                         Corps du programme                                 */
/******************************************************************************/
$connexionBD = ConnexionBD::singleton($gst_serveur_bd,$gst_utilisateur_bd,$gst_mdp_utilisateur_bd,$gst_nom_bd);

print('<meta http-equiv="Content-Type" content="text/html; charset=cp1252" />');
print('<meta http-equiv="content-language" content="fr" /> ');
print("<link href='Commun/Styles.css' type='text/css' rel='stylesheet'/>");

print("<body>");
print('<div class="container">');
require_once("Commun/menu.php");


echo("<img src=\"images/MenuMembre.png\">"); 
echo("<img src=\"images/Reinscription.png\">");
echo("<img src=\"images/NotifierReinscription.png\">");  

print("</div></body>");
?>