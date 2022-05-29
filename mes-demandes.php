<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Origin/PaginationTableau.php';

// Redirect to identification
if (!$session->getAttribute('ident')) {
    $session->setAttribute('url_retour', '/mes-demandes.php');
    header('HTTP/1.0 401 Unauthorized');
    header('Location: /se-connecter.php');
    exit;
}

// ========= Default
$modes = ['VUE_STAT', 'VUE_DEMANDES_COMMUNE', 'VUE_DEMANDES_MOIS_ANNNEE'];
$mode = $_GET['mode'] ?? 'VUE_STAT';
// =================


/*
  Renvoie le lien pour afficher les demandes d'une commune de l'adhérent courant
  @param integer $pi_nb_ddes nombre de demandes
  @param integer $pi_idf_commune identifiant de la commune
  @param integer $pi_idf_type_acte identifiant type de l'acte 
*/
function ddes_communes($pi_nb_ddes, $pi_idf_commune, $pi_idf_type_acte)
{
    if ($pi_nb_ddes != 0) {
        return "<a href=\"/mes-demandes.php?mode=VUE_DEMANDES_COMMUNE&idf_commune=$pi_idf_commune&idf_type_acte=$pi_idf_type_acte\">$pi_nb_ddes</a>";
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
        return "<a href=\"/mes-demandes.php?mode=VUE_DEMANDES_MOIS_ANNNEE&mois=$pi_mois&annee=$pi_annee&idf_type_acte=$pi_idf_type_acte\">$pi_nb_ddes</a>";
    } else {
        return $pi_nb_ddes;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="content-language" content="fr" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/styles.css" type="text/css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/js/jquery-min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <title>Base <?= SIGLE_ASSO; ?> : Stats consulations adhérent</title>
</head>

<body>
    <div class="container">

        <?php require_once __DIR__ . '/commun/menu.php';

        switch ($mode) {
            case 'VUE_STAT':
                $st_requete = "SELECT count(*) total, 
                    sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances, 
                    sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, 
                    sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, 
                    sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm 
                    FROM demandes_adherent 
                    WHERE idf_adherent=$user[idf]";
                list($i_tot_ddes, $i_tot_nai, $i_tot_mar, $i_tot_dec, $i_tot_cm) = $connexionBD->sql_select_liste($st_requete);
                print('<div class="panel panel-primary">');
                print('<div class="panel-heading">Statistiques de vos demandes</div>');
                print('<div class="panel-body">');
                print('<div class="panel-group">');

                print('<div class="panel panel-info">');
                print('<div class="panel-heading">Total des demandes</div>');
                print('<div class="panel-body">');
                print("<table class=\"table table-bordered table-striped\">\n");
                print("<tr><th>Total des ddes</th><th>Total naissances</th><th>Total mariages</th><th>Total décés</th><th>Total CM</th></tr>\n");
                print("<tr><td>$i_tot_ddes</td><td>$i_tot_nai</td><td>$i_tot_mar</td><td>$i_tot_dec</td><td>$i_tot_cm</td></tr>\n");
                print("</table></div></div>\n");
                $st_requete = "SELECT min(date_format(date_demande,\"%d / %c \")) AS date_dde, 
                    count(*) total, 
                    sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,
                    sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, 
                    sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, 
                    sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm 
                    FROM demandes_adherent 
                    WHERE idf_adherent=$user[idf]
                    AND datediff(now(),date_demande) <= 30 
                    GROUP BY date_demande 
                    ORDER BY date_dde DESC 
                    LIMIT 0,30";
                $a_ddes_dernier_mois = $connexionBD->sql_select_multiple($st_requete);
                print('<div class="panel panel-info">');
                print('<div class="panel-heading">Demandes des 30 derniers jours</div>');
                print('<div class="panel-body">');
                if (count($a_ddes_dernier_mois) > 0) {
                    print("<table class=\"table table-bordered table-striped\">\n");
                    print("<tr><th>Jour</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes décés</th><th>Ddes CM</th></tr>\n");
                    foreach ($a_ddes_dernier_mois as $a_ligne) {
                        list($st_jour, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
                        print("<tr><td>$st_jour</td><td>$i_total</td><td>$i_nb_nai</td><td>$i_nb_mar</td><td>$i_nb_dec</td><td>$i_nb_cm</td></tr>\n");
                    }
                    print("</table>\n");
                } else {
                    print("<div class=\"alert alert-danger\">Pas de demandes</div>");
                }
                print('</div></div>');

                $st_requete = "SELECT min(YEAR(date_demande)) AS annee, min(MONTH(date_demande)) AS mois, 
                    count(*) total, 
                    sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,
                    sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, 
                    sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, 
                    sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm 
                    FROM demandes_adherent 
                    WHERE idf_adherent=$user[idf] 
                    GROUP BY YEAR(date_demande)*100+MONTH(date_demande) 
                    ORDER BY annee DESC, mois DESC 
                    LIMIT 12";
                $a_ddes_derniere_anneee = $connexionBD->sql_select_multiple($st_requete);

                print('<div class="panel panel-info">');
                print('<div class="panel-heading">Demandes des 12 derniers mois</div>');
                print('<div class="panel-body">');
                if (count($a_ddes_derniere_anneee) > 0) {
                    print("<table class=\"table table-bordered table-striped\">\n");
                    print("<tr><th>Année/Mois</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes décés</th><th>Ddes CM</th></tr>\n");
                    foreach ($a_ddes_derniere_anneee as $a_ligne) {
                        list($i_annee, $i_mois, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
                        print(sprintf("<tr><td>%04d/%02d</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", $i_annee, $i_mois, $i_total, ddes_mois_annee($i_nb_nai, $i_mois, $i_annee, IDF_NAISSANCE), ddes_mois_annee($i_nb_mar, $i_mois, $i_annee, IDF_MARIAGE), ddes_mois_annee($i_nb_dec, $i_mois, $i_annee, IDF_DECES), ddes_mois_annee($i_nb_cm, $i_mois, $i_annee, IDF_CM)));
                    }
                    print("</table>\n");
                } else {
                    print("<div class=\"alert alert-danger\">Pas de demandes</div>");
                }
                print('</div></div>');

                $st_requete = "SELECT min(ca.nom) AS paroisse, ca.idf, 
                    count(*) total, 
                    sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,
                    sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, 
                    sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, 
                    sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm 
                    FROM demandes_adherent da 
                    JOIN commune_acte ca ON (da.idf_commune=ca.idf) 
                    WHERE idf_adherent=$user[idf] 
                    GROUP BY ca.idf 
                    ORDER BY total DESC 
                    LIMIT 20";
                $a_ddes_paroisses = $connexionBD->sql_select_multiple($st_requete);
                print('<div class="panel panel-info">');
                print('<div class="panel-heading">Demandes des 20 premi&egrave;res paroisses</div>');
                print('<div class="panel-body">');
                if (count($a_ddes_paroisses) > 0) {
                    print("<table class=\"table table-bordered table-striped\">\n");
                    print("<tr><th>Paroisse</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes décés</th><th>Ddes CM</th></tr>\n");
                    foreach ($a_ddes_paroisses as $a_ligne) {
                        list($st_paroisse, $i_idf_paroisse, $i_total, $i_nb_nai, $i_nb_mar, $i_nb_dec, $i_nb_cm) = $a_ligne;
                        print(sprintf("<tr><td>%s</td><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>\n", cp1252_vers_utf8($st_paroisse), $i_total, ddes_communes($i_nb_nai, $i_idf_paroisse, IDF_NAISSANCE), ddes_communes($i_nb_mar, $i_idf_paroisse, IDF_MARIAGE), ddes_communes($i_nb_dec, $i_idf_paroisse, IDF_DECES), ddes_communes($i_nb_cm, $i_idf_paroisse, IDF_CM)));
                    }
                    print("</table>\n");
                } else {
                    print("<div class=\"alert alert-danger\">Pas de demandes</div>");
                }
                print('</div></div>');

                $st_requete = "SELECT min(c.nom) AS canton, 
                    count(*) total, 
                    sum(case when idf_type_acte=" . IDF_NAISSANCE . "  then 1 else 0 end) nb_naissances,
                    sum(case when idf_type_acte=" . IDF_MARIAGE . "  then 1 else 0 end) nb_mariages, 
                    sum(case when idf_type_acte=" . IDF_DECES . "  then 1 else 0 end) nb_deces, 
                    sum(case when idf_type_acte=" . IDF_CM . "  then 1 else 0 end) nb_cm 
                    FROM demandes_adherent da 
                    JOIN commune_acte ca on (da.idf_commune=ca.idf) 
                    JOIN canton c on (ca.idf_canton=c.idf) 
                    WHERE idf_adherent=$user[idf]  
                    GROUP BY c.nom 
                    ORDER BY total DESC 
                    LIMIT 20";
                $a_ddes_cantons = $connexionBD->sql_select_multiple($st_requete);
                print('<div class="panel panel-info">');
                print('<div class="panel-heading">Demandes des 20 premiers cantons</div>');
                print('<div class="panel-body">');
                if (count($a_ddes_cantons) > 0) {
                    print("<table class=\"table table-bordered table-striped\">\n");
                    print("<tr><th>Canton</th><th>Total</th><th>Ddes naissances</th><th>Ddes mariages</th><th>Ddes décés</th><th>Ddes CM</th></tr>\n");
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
                $i_idf_commune = $_GET['idf_commune'] ?? null;
                $i_idf_type_acte = isset($_GET['idf_type_acte']) ?? null;
                $st_commune = $connexionBD->sql_select1("SELECT nom FROM commune_acte WHERE idf=$i_idf_commune");
                $st_type_acte = $connexionBD->sql_select1("SELECT nom FROM type_acte WHERE idf=$i_idf_type_acte");

                print('<div class="panel panel-primary">');
                print("<div class=\"panel-heading\">Vos demandes par commune</div>");
                print('<div class="panel-body">');
                print('<div class="panel panel-info">');
                print("<div class=\"panel-heading\">" . cp1252_vers_utf8($st_type_acte) . " à " . cp1252_vers_utf8($st_commune) . "</div>");
                print('<div class="panel-body">');
                $gi_num_page = $_POST['num_page_ddes_adht'] ?? 1;
                $st_requete = "SELECT DISTINCT date_demande, idf_acte 
                    FROM demandes_adherent 
                    WHERE idf_adherent=$user[idf] 
                    AND idf_commune=$i_idf_commune 
                    AND idf_type_acte=$i_idf_type_acte 
                    ORDER BY date_demande DESC";
                $a_liste_ddes = $connexionBD->sql_select_multiple($st_requete);
                $i_nb_ddes = count($a_liste_ddes);
                if ($i_nb_ddes > 0) {
                    $pagination = new PaginationTableau(basename(__FILE__), 'num_page_ddes_adht', $i_nb_ddes, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Date de la demande', 'Date de l\'acte', 'Parties'));
                    $a_actes = array();
                    foreach ($a_liste_ddes as $a_dde) {
                        list($st_date_dde, $i_idf_acte) = $a_dde;
                        if (!empty($i_idf_acte))
                            $a_actes[] = $i_idf_acte;
                    }
                    $a_liste_actes = array();
                    if (count($a_actes) > 0) {
                        $st_liste_actes = join(',', $a_actes);
                        $st_requete = "select a.idf,a.date, GROUP_CONCAT(DISTINCT concat(ifnull(prenom.libelle,''),' ',p.patronyme) order by p.idf separator ' X ') from acte a join personne p on (a.idf=p.idf_acte and p.idf_type_presence=" . IDF_PRESENCE_INTV . ") join prenom on (p.idf_prenom=prenom.idf)  where a.idf in ($st_liste_actes) group by a.idf";
                        //print("Req=$st_requete<br>");
                        $a_liste_actes = $connexionBD->sql_select_multiple_par_idf($st_requete);
                    }
                    print("<form name=\"DemandesAdherents\"  method=\"post\">");

                    $a_tableau_affichage = array();
                    foreach ($a_liste_ddes as $a_dde) {
                        list($st_date_dde, $i_idf_acte) = $a_dde;
                        if (array_key_exists($i_idf_acte, $a_liste_actes)) {
                            list($st_date_acte, $st_parties) = $a_liste_actes[$i_idf_acte];
                            $a_tableau_affichage[] = array($st_date_dde, $st_date_acte, $st_parties);
                        } else {
                            $a_tableau_affichage[] = array($st_date_dde, '&nbsp;', "Référence originale de l'acte modifiée");
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
                print("<form name=\"RetourVueStat\" method=\"post\">");
                print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-home"></span> Retour vers les statistiques</button></div>');
                print("<input type=\"hidden\" name=\"mode\" value=\"VUE_STAT\">");
                print("</form></div></div></div>");
                break;
            case 'VUE_DEMANDES_MOIS_ANNNEE':
                $i_mois = $_GET['mois'] ?? null;
                $i_annee = $_GET['annee'] ?? null;
                $i_idf_type_acte = $_GET['idf_type_acte'] ?? null;
                $st_type_acte = $connexionBD->sql_select1("SELECT nom FROM type_acte WHERE idf=$i_idf_type_acte");
                print('<div class="panel panel-primary">');
                print('<div class="panel-heading">Vos demandes par mois et année</div>');
                print('<div class="panel-body">');
                print('<div class="panel panel-info">');
                print(sprintf("<div class=\"panel-heading\">%s en %0.2d/%0.4d</div>", cp1252_vers_utf8($st_type_acte), $i_mois, $i_annee));
                print('<div class="panel-body">');
                $gi_num_page = $_POST['num_page_ddes_adht'] ?? 1;
                $st_requete = "SELECT DISTINCT date_demande, ca.nom, idf_acte 
                    FROM demandes_adherent da 
                    JOIN commune_acte ca ON (da.idf_commune=ca.idf) 
                    WHERE idf_adherent=$user[idf] 
                    AND idf_type_acte=$i_idf_type_acte 
                    AND year(date_demande)=$i_annee 
                    AND month(date_demande)=$i_mois 
                    ORDER BY date_demande DESC";
                $a_liste_ddes = $connexionBD->sql_select_multiple($st_requete);
                $i_nb_ddes = count($a_liste_ddes);
                if ($i_nb_ddes > 0) {
                    $pagination = new PaginationTableau(basename(__FILE__), 'num_page_ddes_adht', $i_nb_ddes, NB_LIGNES_PAR_PAGE, DELTA_NAVIGATION, array('Date de la demande', 'Commune', 'Date de l\'acte', 'Parties'));
                    $a_actes = array();
                    foreach ($a_liste_ddes as $a_dde) {
                        list($st_date_dde, $st_commune, $i_idf_acte) = $a_dde;
                        if (!empty($i_idf_acte))
                            $a_actes[] = $i_idf_acte;
                    }
                    $a_liste_actes = array();
                    if (count($a_actes) > 0) {
                        $st_liste_actes = join(',', $a_actes);
                        $st_requete = "SELECT a.idf, a.date, GROUP_CONCAT(DISTINCT concat(ifnull(prenom.libelle,''),' ',p.patronyme) ORDER BY p.idf separator ' X ') 
                    FROM acte a 
                    JOIN personne p on (a.idf=p.idf_acte AND p.idf_type_presence=" . IDF_PRESENCE_INTV . ") 
                    JOIN prenom on (p.idf_prenom=prenom.idf) 
                    WHERE a.idf in ($st_liste_actes) 
                    GROUP BY a.idf";
                        $a_liste_actes = $connexionBD->sql_select_multiple_par_idf($st_requete);
                    }
                    print("<form name=\"DemandesAdherents\"  method=\"post\">");

                    $a_tableau_affichage = array();
                    foreach ($a_liste_ddes as $a_dde) {
                        list($st_date_dde, $st_commune, $i_idf_acte) = $a_dde;
                        if (array_key_exists($i_idf_acte, $a_liste_actes)) {
                            list($st_date_acte, $st_parties) = $a_liste_actes[$i_idf_acte];
                            $a_tableau_affichage[] = array($st_date_dde, $st_commune, $st_date_acte, $st_parties);
                        } else {
                            $a_tableau_affichage[] = array($st_date_dde, $st_commune, '&nbsp;', "Référence originale de l'acte modifiée");
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
                print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-home"></span> Retour vers les statistiques</button></div>');
                print("<input type=\"hidden\" name=\"mode\" value=\"VUE_STAT\">");
                print("</form></div></div></div>");
                break;
        } ?>
    </div>
</body>

</html>