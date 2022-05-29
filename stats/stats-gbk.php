<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/../app/bootstrap.php';

verifie_privilege(DROIT_STATS);


$gst_mode = empty($_POST['mode']) ? 'FORMULAIRE' : $_POST['mode'];

if ($gst_mode == 'EXPORT_HISTORIQUE') {
    exporte_historique_cantons($connexionBD);
    exit();
}

/**
 * Exporte l'historique des demandes GBK
 * @param object $pconnexionBD Connexion à la BD 
 */
function exporte_historique_cantons($pconnexionBD)
{
    $st_requete =  "select c.nom as canton,year(s_gbk.date_demande) as annee,count(*) as nb from stats_gbk s_gbk join commune_acte ca on (s_gbk.idf_commune=ca.idf) join canton c on (ca.idf_canton=c.idf) group by c.nom,year(s_gbk.date_demande) order by annee asc, canton collate latin1_german1_ci asc";
    $a_ddes_annee_cantons = $pconnexionBD->liste_valeur_par_doubles_clefs($st_requete);
    list($i_sec, $i_min, $i_heure, $i_jour, $i_mois, $i_annee, $i_jsem, $i_jan, $b_hiv) =   localtime();
    $a_annees = range(2010, 1900 + $i_annee, +1);
    header("Content-type: text/csv");
    header("Expires: 0");
    header("Pragma: public");
    header("Content-disposition: attachment; filename=\"histo_canton_gbk.csv\"");
    $fh = @fopen('php://output', 'w');
    $a_entete = array('Canton/Annees');
    foreach ($a_annees as $i_annee) {
        $a_entete[] = $i_annee;
    }
    fputcsv($fh, $a_entete, SEP_CSV);
    foreach ($a_ddes_annee_cantons as $st_canton => $a_ddes_annnee) {
        $a_ligne = array($st_canton);
        foreach ($a_annees as $i_annee) {
            if (array_key_exists($i_annee, $a_ddes_annnee)) {
                $a_ligne[] = $a_ddes_annnee[$i_annee][0];
            } else {
                $a_ligne[] = '';
            }
        }
        fputcsv($fh, $a_ligne, SEP_CSV);
    }
    fclose($fh);
}

/**
 * Affiche les statistiques cumulees
 * @param object $pconnexionBD Connexion à la BD 
 */
function affiche_stats_cumulees($pconnexionBD)
{
    print('<div class="panel-group">');
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Par canton</div>');
    print('<div class="panel-body">');
    $st_requete = 'select c.nom,count(*) as nombre from stats_gbk s_gbk join commune_acte ca on (s_gbk.idf_commune=ca.idf) join canton c on (ca.idf_canton = c.idf) group by c.nom order by nombre desc';
    $a_ddes_canton = $pconnexionBD->liste_valeur_par_clef($st_requete);
    print('<table class="table table-bordered table-striped">');
    print("<tr><th>Canton</th><th>Demandes</th></tr>\n");
    foreach ($a_ddes_canton as $st_canton => $i_ddes) {
        print("<tr><td>" . cp1252_vers_utf8($st_canton) . "</td><td>$i_ddes</td></tr>\n");
    }
    print('</table>');
    print('</div></div>');
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Par mois et ann&eacute;e</div>');
    print('<div class="panel-body">');
    $st_requete = 'select date_format(date_demande , "%m/%y" ) as date_mois, count(*)as nombre from stats_gbk group by date_format(date_demande , "%m/%y" ) order by date_demande desc limit 50';
    $a_ddes_mois_annee = $pconnexionBD->liste_valeur_par_clef($st_requete);
    print('<table class="table table-bordered table-striped">');
    print("<tr><th>Date</th><th>Demandes</th></tr>\n");
    foreach ($a_ddes_mois_annee as $st_mois_annee => $i_ddes) {
        print("<tr><td>$st_mois_annee</td><td>$i_ddes</td></tr>\n");
    }
    print('</table>');
    print('</div></div>');
    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Par type d\'acte</div>');
    print('<div class="panel-body">');
    $st_requete = 'select ta.nom, count(*) as nombre from stats_gbk as s_gbk join type_acte ta on (s_gbk.idf_type_acte = ta.idf)  group by ta.nom  asc order by  nombre desc limit 30';
    $a_ddes_types_acte = $pconnexionBD->liste_valeur_par_clef($st_requete);
    print('<table class="table table-bordered table-striped">');
    print("<tr><th>Types d'acte</th><th>Demandes</th></tr>\n");
    foreach ($a_ddes_types_acte as $st_type => $i_ddes) {
        print("<tr><td>" . cp1252_vers_utf8($st_type) . "</td><td>$i_ddes</td></tr>\n");
    }
    print('</table>');
    print('</div></div>');
    print("<form method=post>");
    print("<input type=hidden name=mode value=\"FORMULAIRE\">");
    print("<button type=submit class=\"btn btn-primary col-md-4 col-md-offset-4\"><span class=\"glyphicon glyphicon-home\"></span> Retour vers le menu</button>");
    print("</form>");
    print('</div>');
}

/**
 * Affiche le menu formulaire
 * @param object $pconnexionBD Connexion à la BD 
 */
function affiche_formulaire($pconnexionBD)
{
    $a_cantons = $pconnexionBD->liste_valeur_par_clef("select idf,nom from canton order by nom");

    list($i_sec, $i_min, $i_heure, $i_jour, $i_mois, $i_annee, $i_jsem, $i_jan, $b_hiv) =   localtime();
    $a_annees = range(1900 + $i_annee, 2010, -1);

    print('<div class="panel panel-primary">');
    print('<div class="panel-heading">Statistiques G&eacute;n&eacute;bank</div>');
    print('<div class="panel-body">');

    print('<div class="panel-group">');

    print('<div class="panel panel-default">');
    print('<div class="panel-heading">Tous les cantons par annn&eacute;e</div>');
    print('<div class="panel-body">');
    print("<form method=post>");
    print("<input type=hidden name=mode value=\"EXPORT_HISTORIQUE\">");
    print('<div class="form-group col-md-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-download-alt"></span> Exporter au format CSV l\'historique de tous les cantons</button></div>');
    print("</form></div></div>");

    print("<form method=post>");
    print('<div class="panel panel-default">');
    print('<div class="panel-heading">Statistiques cumul&eacute;es</div>');
    print('<div class="panel-body">');
    print("<input type=hidden name=mode value=\"STATS_CUMULEES\">");
    print('<div class="form-group col-md-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-stats"> Afficher les statistiques cumul&eacute;es</button></div>');
    print("</form></div></div>");

    print("<form id=stats_canton method=post>");
    print('<div class="panel panel-default">');
    print('<div class="panel-heading">par ann&eacute;e et canton</div>');
    print('<div class="panel-body">');
    print('<div class="form-row col-md-12">');
    print('<label for="annee">Ann&eacute;e:</label><select name="annee" id="annee" class="form-control"> ');
    print(chaine_select_options_simple(null, $a_annees));
    print('</select></div>');
    print('<div class="form-row col-md-12">');
    print('<div><label for="idf_canton">Canton:</label><select name="idf_canton" id="idf_canton" class="form-control js-select-avec-recherche">');
    print(chaine_select_options(null, $a_cantons));
    print('</select></div>');
    print("<input type=hidden name=mode value=\"STATS_PAROISSES\">");
    print('<div class="form-row col-md-12">');
    print('<div class="form-group col-md-4 col-md-offset-4"><button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-picture"></span> Afficher les statistiques</button></div>');
    print('</div>');
    print("</form></div></div>");

    print('<div id="tableau_stats_canton"></div>');
    print('<canvas id="MonGraphe" width="600" height="500"></canvas>');
    print("</div></div>");
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="content-language" content="fr" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='../assets/css/styles.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/bootstrap.min.css' rel='stylesheet'>
    <link href='../assets/css/jquery-ui.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/jquery-ui.structure.min.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/jquery-ui.theme.min.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/select2.min.css' type='text/css' rel='stylesheet'>
    <link href='../assets/css/select2-bootstrap.min.css' type='text/css' rel='stylesheet'>
    <script src='../assets/js/jquery-min.js' type='text/javascript'></script>
    <script src='../assets/js/jquery-ui.min.js' type='text/javascript'></script>
    <script src='../assets/js/select2.min.js' type='text/javascript'></script>
    <script src='../assets/js/Chart.min.js' type='text/javascript'></script>
    <script src='../assets/js/bootstrap.min.js' type='text/javascript'></script>
    <script type='text/javascript'>
        $(document).ready(function() {
            $.fn.select2.defaults.set("theme", "bootstrap");

            $(".js-select-avec-recherche").select2();

            $('#stats_canton').submit(function(event) {
                $.ajax({
                        type: 'GET',
                        url: '../ajax/stats_gbk.php',
                        data: 'annee=' + $('#annee').val() + '&idf_canton=' + $('#idf_canton').val(),
                        dataType: 'json'
                    })
                    .done(function(donnees) {
                        var labels = donnees["labels"];
                        var ensemble_donnees = donnees["donnees"];
                        // affichage du tableau
                        var tableau = "<table class=\"table table-bordered table-striped\"><tr><th><Commune></th>";
                        jQuery.each(labels, function(i, val) {
                            tableau += "<th>" + val + "</th>";
                        });
                        tableau += "</tr>";
                        var cumul = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
                        jQuery.each(ensemble_donnees, function(i, obj) {
                            tableau += "<tr><td>" + obj["label"] + "</td>";
                            consultations = obj["data"];
                            jQuery.each(consultations, function(i, val) {
                                if (val != 0) {
                                    tableau += "<td>" + val + "</td>";
                                } else {
                                    tableau += "<td>&nbsp;</td>";
                                }
                                cumul[i] += val;
                            });
                            tableau += "</tr>";
                        });
                        tableau += "<tr><th>Total</th>";
                        jQuery.each(cumul, function(i, val) {
                            if (val != 0) {
                                tableau += "<th>" + val + "</th>";
                            } else {
                                tableau += "<th>&nbsp;</th>";
                            }
                        });
                        tableau += "</tr></table>";
                        //console.log(tableau);
                        $('#tableau_stats_canton').html('');
                        $('#tableau_stats_canton').append(tableau);
                        // affichage du graphe
                        var ctx = document.getElementById('MonGraphe').getContext('2d');
                        var myChart = new Chart(ctx, {
                            title: 'stats_gbk',
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: ensemble_donnees
                            },
                            options: {
                                scales: {
                                    xAxes: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Mois'
                                        }
                                    }],
                                    yAxes: [{
                                        display: true,
                                        scaleLabel: {
                                            display: true,
                                            labelString: 'Nombre'
                                        }
                                    }]
                                }
                            }
                        });
                    });
                event.preventDefault();
            });
        });
    </script>
</head>

<body>
    <div class="container">
        <?php require_once __DIR__ . '/../commun/menu.php';

        switch ($gst_mode) {
            case 'FORMULAIRE':
                affiche_formulaire($connexionBD);
                break;
            case 'STATS_CUMULEES':
                affiche_stats_cumulees($connexionBD);
                break;
        } ?>
    </div>
</body>

</html>