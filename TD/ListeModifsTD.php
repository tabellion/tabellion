<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../Commun/config.php';
require_once __DIR__ . '/../Commun/constantes.php';
require_once __DIR__ . '/../Commun/Identification.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_VALIDATION_TD);
require_once __DIR__ . '/../Commun/ConnexionBD.php';
require_once __DIR__ . '/../Commun/commun.php';

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr">');
print("<title>Liste des demandes de modification de TD en attente</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../js/bootstrap.min.js' type='text/javascript'></script>");
print('</head>');
print('<body>');
print('<div class="container">');

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);
require_once __DIR__ . '/../Commun/menu.php';
print('<div class="panel panel-primary">');
print("<div class=\"panel-heading\">Demandes de modification de TD</div>");
print('<div class="panel-body">');

$i_nb_tds = $connexionBD->sql_select1("select sum( nb_actes ) from `stats_commune` where `idf_type_acte` =" . IDF_MARIAGE . " and `idf_source` =" . IDF_SOURCE_TD);
print("<div class=\"row\"><div class=\"col-md-4 col-md-offset-4\"><div class=\"badge\">$i_nb_tds</div> TDMs en base</div></div>");
if (isset($_SESSION['ident'])) {
    $st_requete = "select distinct ma.idf,a.date,ta.nom,ca.nom,GROUP_CONCAT(distinct concat(prties.prenom,' ',prties.patronyme) order by prties.idf separator ' X ') as parties,ma.date_modif,ma.email_demandeur from `modification_acte` ma join acte a on (ma.idf_acte=a.idf) join `modification_personne` prties on (ma.idf=prties.idf_modification_acte and prties.idf_type_presence=" . IDF_PRESENCE_INTV . ") join type_acte ta on (a.idf_type_acte=ta.idf) join commune_acte ca on (a.idf_commune=ca.idf) where a.idf_source=" . IDF_SOURCE_TD . " and ma.statut is null or ma.statut not in ('A','R') group by ma.idf";
    $a_liste_modifs = $connexionBD->sql_select_multiple_par_idf($st_requete);
    if (count($a_liste_modifs) > 0) {
        print('<div class="panel panel-info">');
        print("<div class=\"panel-heading\">Demandes de modifications</div>");
        print('<div class="panel-body">');
        print("<table class=\"table table-bordered table-striped\">\n");
        print("<tr>");
        print("<th>Date de l'acte</th>");
        print("<th>Type</th>");
        print("<th>Commune</th>");
        print("<th>Parties</th>");
        print("<th>Date de la demande</th>");
        print("<th>Email demandeur</th>");
        print("<th>&nbsp;</th>");
        print("</tr>");
        foreach ($a_liste_modifs as $i_idf_modif  => $a_groupe) {
            list($st_date, $st_type, $st_commune, $st_parties, $st_date_demande, $st_email_demandeur) = $a_groupe;
            print("<tr>");
            print("<td>$st_date</td>");
            print("<td>" . cp1252_vers_utf8($st_type) . "</td>");
            print("<td>" . cp1252_vers_utf8($st_commune) . "</td>");
            print("<td>" . cp1252_vers_utf8($st_parties) . "</td>");
            print("<td>$st_date_demande</td>");
            print("<td>$st_email_demandeur</td>");
            print("<td>");
            print("<form action=\"../Administration/ValidationModification.php\" method=\"POST\">");
            print("<input type=\"hidden\" name=\"idf_modification\" value=\"$i_idf_modif\">");
            $st_nom_script = basename(__FILE__);
            print("<input type=\"hidden\" name=\"adresse_retour\" value=\"TD/$st_nom_script\">");
            print('<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-eye-open"></span> Voir la modification</button>');
            print("</form></td>");
            print("</tr>");
        }
        print("</table>");
        print("</div></div>");
    } else {
        print('<div class="alert alert-danger">Pas de demandes de TD en attente</div>');
    }
}
print("</div></div>");
print("</div></body></html>");
