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

$i_session_idf_adherent =  isset($_SESSION['idf_adherent']) ? $_SESSION['idf_adherent'] : null;
$gi_idf_adherent =   isset($_GET['idf_adherent']) ? (int) $_GET['idf_adherent'] : $i_session_idf_adherent;
$_SESSION['idf_adherent'] = $gi_idf_adherent;

$st_session_mode =  isset($_SESSION['mode']) ? $_SESSION['mode'] : 'VUE_STAT';
$gst_mode =  isset($_REQUEST['mode']) ? $_REQUEST['mode'] : $st_session_mode;
if (!empty($gst_mode) && !in_array($gst_mode, array('VUE_STAT', 'VUE_DEMANDES_COMMUNE', 'VUE_DEMANDES_MOIS_ANNNEE')))
    $gst_mode = 'VUE_STAT';


/*
  Renvoie le lien pour afficher les demandes d'une commune de l'adhérent courant
  @param integer $pi_nb_ddes nombre de demandes
  @param integer $pi_idf_commune identifiant de la commune
  @param integer $pi_idf_type_acte identifiant type de l'acte 
*/
function ddes_communes($pi_nb_ddes, $pi_idf_commune, $pi_idf_type_acte)
{
    if ($pi_nb_ddes != 0) {
        return "<a href=\"" . basename(__FILE__) . "?mode=VUE_DEMANDES_COMMUNE&idf_commune=$pi_idf_commune&idf_type_acte=$pi_idf_type_acte\">$pi_nb_ddes</a>";
    } else {
        return $pi_nb_ddes;
    }
}

/*
  Renvoie le lien pour afficher les demandes d'une commune de l'adhérent courant
  @param integer $pi_nb_ddes nombre de demandes
  @param integer $pi_mois identifiant du mois
  @param integer  $pi_annee identifiant de l'année
  @param integer $pi_idf_type_acte identifiant type de l'acte 
*/
function ddes_mois_annee($pi_nb_ddes, $pi_mois, $pi_annee, $pi_idf_type_acte)
{
    if ($pi_nb_ddes != 0) {
        return "<a href=\"" . basename(__FILE__) . "?mode=VUE_DEMANDES_MOIS_ANNNEE&mois=$pi_mois&annee=$pi_annee&idf_type_acte=$pi_idf_type_acte\">$pi_nb_ddes</a>";
    } else {
        return $pi_nb_ddes;
    }
}


print('<!DOCTYPE html>');
print("<head>");
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" >');
print('<meta http-equiv="content-language" content="fr"> ');
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>");
print("<link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'> ");
print("<script src='../assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>");
print("<script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('<title>Base ' . SIGLE_ASSO . ': Stats consultations adhérent</title>');
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/../Commun/menu.php';

if (isset($gi_idf_adherent)) {
    switch ($gst_mode) {
        case 'VUE_STAT':
            unset($_SESSION['mode']);
            unset($_SESSION['idf_commune']);
            unset($_SESSION['idf_type_acte']);
            unset($_SESSION['num_page_ddes_adht']);
            $st_requete = "select count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent where idf_adherent=$gi_idf_adherent";
            list($i_tot_ddes, $i_tot_nai, $i_tot_mar, $i_tot_dec, $i_tot_cm) = $connexionBD->sql_select_liste($st_requete);
            $st_adherent = $connexionBD->sql_select1("select concat(prenom,' ',nom,'(',idf,')') from adherent where idf=$gi_idf_adherent");
            print('<div class="panel panel-primary">');
            print("<div class=\"panel-heading\">Statistiques des demandes de l'adh&eacute;rent " . cp1252_vers_utf8($st_adherent) . "</div>");
            print('<div class="panel-body">');
            print('<div class="panel-group">');

            print('<div class="panel panel-info">');
            print('<div class="panel-heading">Total des demandes</div>');
            print('<div class="panel-body">');
            print("<table class=\"table table-bordered table-striped\">\n");
            print("<tr><th>Total des ddes</th><th>Total naissances</th><th>Total mariages</th><th>Total d&eacute;c&eacute;s</th><th>Total CM</th></tr>\n");
            print("<tr><td>$i_tot_ddes</td><td>$i_tot_nai</td><td>$i_tot_mar</td><td>$i_tot_dec</td><td>$i_tot_cm</td></tr>\n");
            print("</table></div></div>\n");

            $st_requete = "select min(date_format(date_demande,\"%d / %c \")) AS date_dde, count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent where idf_adherent=$gi_idf_adherent and  datediff(now(),date_demande) <= 30 group by date_demande order by date_dde desc limit 0 , 30";
            $a_ddes_dernier_mois = $connexionBD->sql_select_multiple($st_requete);
            print('<div class="panel panel-info">');
            print('<div class="panel-heading">Demandes des 30 derniers jours</div>');
            print('<div class="panel-body">');
            if (count($a_ddes_dernier_mois) > 0) {
                print("<table class=\"table table-bordered table-striped\">\n");
                print("<tr><th>Jour</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes d&eacute;c&eacute;s</th><th>Ddes CM</th></tr>\n");
                foreach ($a_ddes_dernier_mois as $a_ligne) {
                    list($st_jour, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
                    print("<tr><td>$st_jour</td><td>$i_total</td><td>$i_nb_nai</td><td>$i_nb_mar</td><td>$i_nb_dec</td><td>$i_nb_cm</td></tr>\n");
                }
                print("</table>\n");
            } else {
                print("<div class=\"alert alert-danger\">Pas de demandes</div>");
            }
            print('</div></div>');

            $st_requete = "select min(YEAR(date_demande)) as annee, min(MONTH(date_demande)) as mois, count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent where idf_adherent=$gi_idf_adherent group by YEAR(date_demande)*100+MONTH(date_demande) order by annee desc, mois desc limit 12";
            $a_ddes_derniere_anneee = $connexionBD->sql_select_multiple($st_requete);
            print('<div class="panel panel-info">');
            print('<div class="panel-heading">Demandes des 12 derniers mois</div>');
            print('<div class="panel-body">');
            if (count($a_ddes_derniere_anneee) > 0) {
                print("<table class=\"table table-bordered table-striped\">\n");
                print("<tr><th>Ann&eacute;e/Mois</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes d&eacute;c&eacute;s</th><th>Ddes CM</th></tr>\n");
                foreach ($a_ddes_derniere_anneee as $a_ligne) {
                    list($i_annee, $i_mois, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
                    print(sprintf("<tr><td>%04d/%02d</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", $i_annee, $i_mois, $i_total, ddes_mois_annee($i_nb_nai, $i_mois, $i_annee, IDF_NAISSANCE), ddes_mois_annee($i_nb_mar, $i_mois, $i_annee, IDF_MARIAGE), ddes_mois_annee($i_nb_dec, $i_mois, $i_annee, IDF_DECES), ddes_mois_annee($i_nb_cm, $i_mois, $i_annee, IDF_CM)));
                }
                print("</table>\n");
            } else {
                print("<div class=\"alert alert-danger\">Pas de demandes</div>");
            }
            print('</div></div>');

            $st_requete = "select  ca.nom as paroisse, ca.idf,count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent da join commune_acte ca on (da.idf_commune=ca.idf) where da.idf_adherent=$gi_idf_adherent group  by ca.idf order by total desc limit 20";
            $a_ddes_paroisses = $connexionBD->sql_select_multiple($st_requete);
            print('<div class="panel panel-info">');
            print('<div class="panel-heading">Demandes des 20 premi&egrave;res paroisses</div>');
            print('<div class="panel-body">');
            if (count($a_ddes_paroisses) > 0) {
                print("<table class=\"table table-bordered table-striped\">\n");
                print("<tr><th>Paroisse</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes d&eacute;c&eacute;s</th><th>Ddes CM</th></tr>\n");
                foreach ($a_ddes_paroisses as $a_ligne) {
                    list($st_paroisse, $i_idf_paroisse, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
                    print(sprintf("<tr><td>%s</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", cp1252_vers_utf8($st_paroisse), $i_total, ddes_communes($i_nb_nai, $i_idf_paroisse, IDF_NAISSANCE), ddes_communes($i_nb_mar, $i_idf_paroisse, IDF_MARIAGE), ddes_communes($i_nb_dec, $i_idf_paroisse, IDF_DECES), ddes_communes($i_nb_cm, $i_idf_paroisse, IDF_CM)));
                }
                print("</table>\n");
            } else {
                print("<div class=\"alert alert-danger\">Pas de demandes</div>");
            }
            print('</div></div>');

            $st_requete = "select  c.nom as canton, count(*) total, sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm from demandes_adherent da join commune_acte ca on (da.idf_commune=ca.idf) join canton c on (ca.idf_canton=c.idf) where da.idf_adherent=$gi_idf_adherent group  by c.nom order by total desc limit 20";
            $a_ddes_cantons = $connexionBD->sql_select_multiple($st_requete);
            print('<div class="panel panel-info">');
            print('<div class="panel-heading">Demandes des 20 premiers cantons</div>');
            print('<div class="panel-body">');
            if (count($a_ddes_cantons) > 0) {
                print("<table class=\"table table-bordered table-striped\">\n");
                print("<tr><th>Canton</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes d&eacute;c&eacute;s</th><th>Ddes CM</th></tr>\n");
                foreach ($a_ddes_cantons as $a_ligne) {
                    list($st_canton, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
                    print(sprintf("<tr><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td><td>%d</td></tr>\n", cp1252_vers_utf8($st_canton), $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm));
                }
                print("</table>\n");
            } else {
                print("<div class=\"alert alert-danger\">Pas de demandes</div>");
            }
            print('</div></div>');
            print('</div></div></div>');
            break;
        case 'VUE_DEMANDES_COMMUNE':
            $i_session_idf_commune = isset($_SESSION['idf_commune']) ? $_SESSION['idf_commune'] : null;
            $i_idf_commune = isset($_GET['idf_commune']) ? (int) $_GET['idf_commune'] : $i_session_idf_commune;
            $i_session_idf_type_acte = isset($_SESSION['idf_type_acte']) ? $_SESSION['idf_type_acte'] : null;
            $i_idf_type_acte = isset($_GET['idf_type_acte']) ? (int) $_GET['idf_type_acte'] : $i_session_idf_type_acte;
            $i_session_num_page = isset($_SESSION['num_page_ddes_adht']) ? $_SESSION['num_page_ddes_adht'] : 1;
            $st_commune = $connexionBD->sql_select1("select nom from commune_acte where idf=$i_idf_commune");
            $st_type_acte = $connexionBD->sql_select1("select nom from type_acte where idf=$i_idf_type_acte");
            $st_adherent = $connexionBD->sql_select1("select concat(prenom,' ',nom,'(',idf,')') from adherent where idf=$gi_idf_adherent");

            print('<div class="panel panel-primary">');
            print("<div class=\"panel-heading\">Demandes de l'adh&eacute;rent " . cp1252_vers_utf8($st_adherent) . "</div>");
            print('<div class="panel-body">');
            print('<div class="panel panel-info">');
            print("<div class=\"panel-heading\">" . cp1252_vers_utf8($st_type_acte) . " &agrave; " . cp1252_vers_utf8($st_commune) . "</div>");
            print('<div class="panel-body">');
            $gi_num_page = empty($_POST['num_page_ddes_adht']) ?  $i_session_num_page : (int) $_POST['num_page_ddes_adht'];
            $_SESSION['mode'] = $gst_mode;
            $_SESSION['idf_commune'] = $i_idf_commune;
            $_SESSION['idf_type_acte'] = $i_idf_type_acte;
            $_SESSION['num_page_ddes_adht'] = $gi_num_page;
            $st_requete = "select distinct date_demande,adresse_ip,idf_acte from demandes_adherent where idf_adherent=$gi_idf_adherent and idf_commune=$i_idf_commune and idf_type_acte=$i_idf_type_acte order by date_demande desc";
            //print("Req=$st_requete<br>");
            $a_liste_ddes = $connexionBD->sql_select_multiple($st_requete);
            $i_nb_ddes = count($a_liste_ddes);
            if ($i_nb_ddes > 0) {
                $pagination = new PaginationTableau(basename(__FILE__), 'num_page_ddes_adht', $i_nb_ddes, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Date de la demande', 'Adresse IP', 'Date de l\'acte', 'Parties'));
                $a_actes = array();
                foreach ($a_liste_ddes as $a_dde) {
                    list($st_date_dde, $st_adresse_ip, $i_idf_acte) = $a_dde;
                    if (!empty($i_idf_acte))
                        $a_actes[] = $i_idf_acte;
                }
                $a_liste_actes = array();
                if (count($a_actes) > 0) {
                    $st_liste_actes = join(',', $a_actes);
                    $st_requete = "select a.idf,a.date, GROUP_CONCAT(DISTINCT concat(ifnull(prenom.libelle,''),' ',p.patronyme) order by p.idf separator ' X ') from acte a join personne p on (a.idf=p.idf_acte and p.idf_type_presence=" . IDF_PRESENCE_INTV . ") join prenom on (p.idf_prenom=prenom.idf) where a.idf in ($st_liste_actes) group by a.idf";
                    //print("Req=$st_requete<br>");
                    $a_liste_actes = $connexionBD->sql_select_multiple_par_idf($st_requete);
                }
                print("<form name=\"DemandesAdherents\"  method=\"post\">");

                $a_tableau_affichage = array();
                foreach ($a_liste_ddes as $a_dde) {
                    list($st_date_dde, $st_adresse_ip, $i_idf_acte) = $a_dde;
                    if (array_key_exists($i_idf_acte, $a_liste_actes)) {
                        list($st_date_acte, $st_parties) = $a_liste_actes[$i_idf_acte];
                        $a_tableau_affichage[] = array($st_date_dde, $st_adresse_ip, $st_date_acte, $st_parties);
                    } else {
                        $a_tableau_affichage[] = array($st_date_dde, $st_adresse_ip, '&nbsp;', "R&eacute;f&eacute;rence originale de l'acte modifi&eacute;e");
                    }
                }
                $pagination->init_page_cour($gi_num_page);
                $pagination->affiche_entete_liste_select('DemandesAdherents');
                $pagination->affiche_tableau_simple($a_tableau_affichage);
                $pagination->affiche_entete_liste_select('DemandesAdherents');
                print("</form>");
            } else {
                print("<div class=\"alert alert-danger\">Pas de demandes</div>");
            }
            print("<form name=\"RetourVueStat\"  method=\"post\">");
            print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-home"></span>   Retour vers les statistiques</button></div>');
            print("<input type=\"hidden\" name=\"mode\" value=\"VUE_STAT\">");
            print("</form></div></div></div>");
            break;
        case 'VUE_DEMANDES_MOIS_ANNNEE':
            $i_session_mois = isset($_SESSION['mois']) ? $_SESSION['mois'] : null;
            $i_mois = isset($_GET['mois']) ? (int) $_GET['mois'] : $i_session_mois;
            $i_session_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : null;
            $i_annee = isset($_GET['annee']) ? (int) $_GET['annee'] : $i_session_annee;
            $i_session_idf_type_acte = isset($_SESSION['idf_type_acte']) ? $_SESSION['idf_type_acte'] : null;
            $i_idf_type_acte = isset($_GET['idf_type_acte']) ? (int) $_GET['idf_type_acte'] : $i_session_idf_type_acte;
            $i_session_num_page = isset($_SESSION['num_page_ddes_adht']) ? $_SESSION['num_page_ddes_adht'] : 1;
            $st_type_acte = $connexionBD->sql_select1("select nom from type_acte where idf=$i_idf_type_acte");
            $st_adherent = $connexionBD->sql_select1("select concat(prenom,' ',nom,'(',idf,')') from adherent where idf=$gi_idf_adherent");
            print('<div class="panel panel-primary">');
            print("<div class=\"panel-heading\">Demandes de l'adh&eacute;rent " . cp1252_vers_utf8($st_adherent) . "</div>");
            print('<div class="panel-body">');
            print('<div class="panel panel-info">');
            print(sprintf("<div class=\"panel-heading\">%s en %0.2d/%0.4d</div>", cp1252_vers_utf8($st_type_acte), $i_mois, $i_annee));
            print('<div class="panel-body">');
            $gi_num_page = empty($_POST['num_page_ddes_adht']) ?  $i_session_num_page : (int) $_POST['num_page_ddes_adht'];
            $_SESSION['mode'] = $gst_mode;
            $_SESSION['mois'] = $i_mois;
            $_SESSION['annee'] = $i_annee;
            $_SESSION['idf_type_acte'] = $i_idf_type_acte;
            $_SESSION['num_page_ddes_adht'] = $gi_num_page;
            $st_requete = "select distinct date_demande,adresse_ip,ca.nom,idf_acte from demandes_adherent da join commune_acte ca on (da.idf_commune=ca.idf) where idf_adherent=$gi_idf_adherent  and idf_type_acte=$i_idf_type_acte and year(date_demande)=$i_annee and month(date_demande)=$i_mois order by date_demande desc";
            //print("Req=$st_requete<br>");
            $a_liste_ddes = $connexionBD->sql_select_multiple($st_requete);
            $i_nb_ddes = count($a_liste_ddes);
            if ($i_nb_ddes > 0) {
                $pagination = new PaginationTableau(basename(__FILE__), 'num_page_ddes_adht', $i_nb_ddes, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Date de la demande', 'Adresse IP', 'Commune', 'Date de l\'acte', 'Parties'));
                $a_actes = array();
                foreach ($a_liste_ddes as $a_dde) {
                    list($st_date_dde, $st_adresse_ip, $st_commune, $i_idf_acte) = $a_dde;
                    if (!empty($i_idf_acte))
                        $a_actes[] = $i_idf_acte;
                }
                $a_liste_actes = array();
                if (count($a_actes) > 0) {
                    $st_liste_actes = join(',', $a_actes);
                    $st_requete = "select a.idf,a.date, GROUP_CONCAT(DISTINCT concat(ifnull(prenom.libelle,''),' ',p.patronyme) order by p.idf separator ' X ') from acte a join personne p on (a.idf=p.idf_acte and p.idf_type_presence=" . IDF_PRESENCE_INTV . ")  join prenom on (p.idf_prenom=prenom.idf) where a.idf in ($st_liste_actes) group by a.idf";
                    //print("Req=$st_requete<br>");
                    $a_liste_actes = $connexionBD->sql_select_multiple_par_idf($st_requete);
                }
                print("<form name=\"DemandesAdherents\"  method=\"post\">");

                $a_tableau_affichage = array();
                foreach ($a_liste_ddes as $a_dde) {
                    list($st_date_dde, $st_adresse_ip, $st_commune, $i_idf_acte) = $a_dde;
                    if (array_key_exists($i_idf_acte, $a_liste_actes)) {
                        list($st_date_acte, $st_parties) = $a_liste_actes[$i_idf_acte];
                        $a_tableau_affichage[] = array($st_date_dde, $st_adresse_ip, $st_commune, $st_date_acte, $st_parties);
                    } else {
                        $a_tableau_affichage[] = array($st_date_dde, $st_adresse_ip, $st_commune, '&nbsp;', "R&eacute;f&eacute;rence originale de l'acte modifi&eacute;e");
                    }
                }
                $pagination->init_page_cour($gi_num_page);
                $pagination->affiche_entete_liste_select('DemandesAdherents');
                $pagination->affiche_tableau_simple($a_tableau_affichage);
                $pagination->affiche_entete_liste_select('DemandesAdherents');
                print("</form>");
            } else {
                print("<div class=\"alert alert-danger\">Pas de demandes</div>");
            }
            print("<form name=\"RetourVueStat\"  method=\"post\">");
            print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-home"></span>  Retour vers les statistiques</button></div>');
            print("<input type=\"hidden\" name=\"mode\" value=\"VUE_STAT\">");
            print("</form></div></div></div>");
            break;
        default:
            print("<div class=\"alert alert-danger\">Mode $gst_mode inconnu</div>");
    }
} else {
    print("<div class=\"alert alert-danger\">idf_adherent n'est pas d&eacute;fini</div>");
}
print("</div></body>");
print("</html>");
