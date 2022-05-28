<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../Commun/commun.php';
require_once __DIR__ . '/../Commun/VerificationDroits.php';
verifie_privilege(DROIT_STATS);
require_once __DIR__ . '/../Origin/PaginationTableau.php';

$gst_tri = empty($_GET['tri']) ? 'TOT' : $_GET['tri'];

switch ($gst_tri) {
    case 'NAI':
        $st_tri_sql = ' order by nb_naissances desc';
        break;
    case 'MAR':
        $st_tri_sql = ' order by nb_mariages desc';
        break;
    case 'DEC':
        $st_tri_sql = ' order by nb_deces desc';
        break;
    case 'CM':
        $st_tri_sql = ' order by nb_cm desc';
        break;
    case 'TOT':
    default:
        $st_tri_sql = ' order by total desc';
}

$st_requete = "select da.idf_adherent,concat(a.prenom,' ',a.nom,' (',a.idf,')'),count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent da join adherent a on (da.idf_adherent=a.idf) where YEAR(da.date_demande) = YEAR( NOW()) group by da.idf_adherent $st_tri_sql limit 50";
$a_ddes_adht = $connexionBD->sql_select_multiple($st_requete);


/*
  Renvoie le lien pour afficher les demandes d'une commune de l'adhérent courant
  @param integer $pi_nb_ddes nombre de demandes
  @param integer $pi_idf_adherent identifiant de l'adhérent
  @param integer $pi_idf_type_acte identifiant type de l'acte 
*/
function stats_adht($pi_nb_ddes, $pi_idf_adherent)
{
    if ($pi_nb_ddes != 0) {
        return "<a href=\"./StatsAdht.php?mode=VUE_STAT&idf_adherent=$pi_idf_adherent\" target=\"_blank\">$pi_nb_ddes</a>";
    } else {
        return $pi_nb_ddes;
    }
}


print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr"> ');
print('<title>Base ' . SIGLE_ASSO . ': Stats consulations adhérent</title>');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

print('<div class="panel-group">');
print('<div class="panel panel-primary">');
print('<div class="panel-heading">Demandes de l\'ann&eacute;e courante par adh&eacute;rent (50 premiers)</div>');
print('<div class="panel-body">');
if (count($a_ddes_adht) > 0) {
    print("<table class=\"table table-bordered table-striped\">\n");
    print("<tr><th>Adh&eacute;</th><th><a href=\"" . basename(__FILE__) . "?tri=TOT\">Total</a></th><th><a href=\"" . basename(__FILE__) . "?tri=NAI\">Ddes naissances</a></th><th><a href=\"" . basename(__FILE__) . "?tri=MAR\">Ddes mariages</a></th><th><a href=\"" . basename(__FILE__) . "?tri=DEC\">Ddes d&eacute;c&eacute;s</a></th><th><a href=\"" . basename(__FILE__) . "?tri=CM\">Ddes CM</a></th></tr>\n");
    foreach ($a_ddes_adht as $a_ligne) {
        list($i_idf_adherent, $st_adherent, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
        print(sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", cp1252_vers_utf8($st_adherent), stats_adht($i_total, $i_idf_adherent), $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm));
    }
    print("</table>\n");
} else {
    print("<div class=\"alert alert-danger\">Pas de demandes</div>");
}
print('</div></div>');
// ddes par jour
$st_requete = "select date_format(date_demande , \"%d/%m/%Y\") as date,count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent da where UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(date_demande)<=30*86400 GROUP BY date_format(date_demande , \"%d/%m/%Y\" ) $st_tri_sql limit 30";
$a_ddes_jours = $connexionBD->sql_select_multiple($st_requete);

print('<div class="panel panel-primary">');
print('<div class="panel-heading">Nombre de demandes des 30 derniers jours</div>');
print('<div class="panel-body">');
if (count($a_ddes_jours) > 0) {
    print("<table class=\"table table-bordered table-striped\">\n");
    print("<tr><th>Jour</th><th><a href=\"" . basename(__FILE__) . "?tri=TOT\">Total</a></th><th><a href=\"" . basename(__FILE__) . "?tri=NAI\">Ddes naissances</a></th><th><a href=\"" . basename(__FILE__) . "?tri=MAR\">Ddes mariages</a></th><th><a href=\"" . basename(__FILE__) . "?tri=DEC\">Ddes d&eacute;c&eacute;s</a></th><th><a href=\"" . basename(__FILE__) . "?tri=CM\">Ddes CM</a></th></tr>\n");
    $f_moyenne_tot = 0;
    $f_moyenne_nai = 0;
    $f_moyenne_mar = 0;
    $f_moyenne_dec = 0;
    $f_moyenne_cm = 0;
    foreach ($a_ddes_jours as $a_ligne) {
        list($st_jour, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
        $f_moyenne_tot += $i_total;
        $f_moyenne_nai += $i_nb_nai;
        $f_moyenne_mar += $i_nb_mar;
        $f_moyenne_dec += $i_nb_dec;
        $f_moyenne_cm += $i_nb_cm;
        print(sprintf("<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>\n", $st_jour, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm));
    }
    $i_nb = count($a_ddes_jours);
    print(sprintf("<tr><th>Moyenne journali&egrave;re</th><th>%.02f</th><th>%0.2f</th><th>%0.2f</th><th>%0.2f</th><th>%0.2f</th></tr>\n", $f_moyenne_tot / $i_nb, $f_moyenne_nai / $i_nb, $f_moyenne_mar / $i_nb, $f_moyenne_dec / $i_nb, $f_moyenne_cm / $i_nb));
    print("</table>\n");
} else {
    print("<div class=\"alert alert-danger\">Pas de demandes</div>");
}
print('</div></div>');
// ddes par canton
$st_requete = "select c.nom,count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent da join commune_acte ca on (da.idf_commune=ca.idf) join canton c on (ca.idf_canton=c.idf) where YEAR(da.date_demande) = YEAR( NOW()) group by c.idf $st_tri_sql limit 30";
$a_ddes_canton = $connexionBD->sql_select_multiple($st_requete);
print('<div class="panel panel-primary">');
print('<div class="panel-heading">Demandes de l\'ann&eacute;e courante par canton (30 premiers) </div>');
print('<div class="panel-body">');
if (count($a_ddes_canton) > 0) {
    print("<table class=\"table table-bordered table-striped\">\n");
    print("<tr><th>Canton</th><th><a href=\"" . basename(__FILE__) . "?tri=TOT\">Total</a></th><th><a href=\"" . basename(__FILE__) . "?tri=NAI\">Ddes naissances</a></th><th><a href=\"" . basename(__FILE__) . "?tri=MAR\">Ddes mariages</a></th><th><a href=\"" . basename(__FILE__) . "?tri=DEC\">Ddes d&eacute;c&eacute;s</a></th><th><a href=\"" . basename(__FILE__) . "?tri=CM\">Ddes CM</a></th></tr>\n");
    foreach ($a_ddes_canton as $a_ligne) {
        list($st_canton, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
        print(sprintf("<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>\n", cp1252_vers_utf8($st_canton), $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm));
    }
    print("</table>\n");
} else {
    print("<div class=\"alert alert-danger\">Pas de demandes</div>");
}
print('</div></div>');
// ddes par commune
$st_requete = "select ca.nom,count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent da join commune_acte ca on (da.idf_commune=ca.idf)  where YEAR(da.date_demande) = YEAR( NOW()) group by ca.idf $st_tri_sql limit 30";
$a_ddes_communes = $connexionBD->sql_select_multiple($st_requete);
print('<div class="panel panel-primary">');
print('<div class="panel-heading">Demandes de l\'ann&eacute;e courante par commune (30 premi&egrave;res)</div>');
print('<div class="panel-body">');
if (count($a_ddes_communes) > 0) {
    print("<table class=\"table table-bordered table-striped\">\n");
    print("<tr><th>Commune</th><th><a href=\"" . basename(__FILE__) . "?tri=TOT\">Total</a></th><th><a href=\"" . basename(__FILE__) . "?tri=NAI\">Ddes naissances</a></th><th><a href=\"" . basename(__FILE__) . "?tri=MAR\">Ddes mariages</a></th><th><a href=\"" . basename(__FILE__) . "?tri=DEC\">Ddes d&eacute;c&eacute;s</a></th><th><a href=\"" . basename(__FILE__) . "?tri=CM\">Ddes CM</a></th></tr>\n");
    foreach ($a_ddes_communes as $a_ligne) {
        list($st_commune, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
        print(sprintf("<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>\n", cp1252_vers_utf8($st_commune), $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm));
    }
    print("</table>\n");
} else {
    print("<div class=\"alert alert-danger\">Pas de demandes</div>");
}
print('</div></div></div>');

print("</div></body>");
print("</html>");
