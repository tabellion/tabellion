<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';

// ======== Default
$gst_mode = 'LISTE';
$sources = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM source ORDER BY nom");
$sources[0] = 'Toutes';
$commune_a_chercher = '';
// ================


$id_source = $_GET['idf_source'] ?? 1;
$gi_num_page_cour = $_GET['num_page_statcom'] ?? 1;
$id_commune = $_GET['idf_commune'] ?? null;
$id_type_acte = $_GET['idf_type_acte'] ?? null;

if ($id_commune && $id_type_acte) {
    $sql1 = "SELECT ca.nom, c.nom, ca.debut_communale, ca.debut_greffe 
        FROM commune_acte ca 
        LEFT JOIN canton c ON (ca.idf_canton=c.idf) 
        WHERE ca.idf=$id_commune";
    list($gst_nom_commune, $gst_canton, $gi_debut_communale, $gi_debut_greffe) = $connexionBD->sql_select_liste($sql1);
    $gst_type_acte = $connexionBD->sql_select1("SELECT nom FROM type_acte WHERE idf=$id_type_acte");
    $gst_mode = 'DETAIL';
}

/*
 * Renvoie une chaine intervalle d'années ou une seule année si l'intervalle est vide
 * @param integer $pi_deb : Année de début
 * @param integer $pi_fin : Année de fin 
 * @return string Chaine représentant l'intervalle   
*/
function chaine_intervalle($pi_deb, $pi_fin)
{
    if ($pi_deb == $pi_fin) {
        return "$pi_deb";
    }

    return "$pi_deb-$pi_fin";
}

function cellule_stat($pi_idf_commune, $pi_idf_type_acte, $pi_annee_min, $pi_annee_max, $pi_nb_actes)
{
    return "<td><a href=\"/affiche-stats-commune-detail.php?idf_commune=$pi_idf_commune&amp;idf_type_acte=$pi_idf_type_acte\">" . chaine_intervalle($pi_annee_min, $pi_annee_max, $pi_nb_actes) . "<br>$pi_nb_actes actes</a></td>";
}

/**
 * Affiche l'entête de navigation liens
 * L'entête se presente sous la forme d'une liste d'ancres HTML [pagecourante - delta ... pagecourante ... pagecourante + delta]     
 */
function affiche_entete_liens_navigation($pi_num_page_cour, $pi_nb_pages)
{
    $i_deb = ($pi_num_page_cour - DELTA_NAVIGATION) > 0 ? ($pi_num_page_cour - DELTA_NAVIGATION) : 1;
    $i_fin = ($pi_num_page_cour + DELTA_NAVIGATION) <= $pi_nb_pages ? $pi_num_page_cour + DELTA_NAVIGATION : $pi_nb_pages;
    print('<div class="text-center">');
    print('<ul class="pagination">');
    if ($i_deb > 1)
        print("<li class=\"page-item\"><a href=\"/affiche-stats-commune.php?num_page_statcom=1\" class=\"page-item\">Début</a></li> ");
    if ($i_deb < $i_fin) {
        for ($i = $i_deb; $i <= $i_fin; $i++) {
            if ($i == $pi_num_page_cour)
                print("<li class=\"page-item active\"><span class=\"page-link\">$i<span class=\"sr-only\">(current)</span></span></li>");
            else
                print("<li class=\"page-item\"><a href=\"/affiche-stats-commune.php?num_page_statcom=$i\">$i</a></li>");
        }
    }
    if ($i_fin < $pi_nb_pages)
        print("<li class=\"page-item\"><a href=\"/affiche-stats-commune.php?num_page_statcom=$pi_nb_pages\">Fin</a></li>");
    print("</ul>");
    print('</div>');
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <link rel="shortcut icon" href="assets/img/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="content-language" content="fr">
    <link href="assets/css/styles.css" type="text/css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="assets/css/jquery-ui.css" type="text/css" rel="stylesheet">
    <link href="assets/css/jquery-ui.structure.min.css" type="text/css" rel="stylesheet">
    <link href="assets/css/jquery-ui.theme.min.css" type="text/css" rel="stylesheet">
    <link href="assets/css/select2.min.css" type="text/css" rel="stylesheet">
    <link href="assets/css/select2-bootstrap.min.css" type="text/css" rel="stylesheet">
    <script src="assets/js/jquery-min.js" type="text/javascript"></script>
    <script src="assets/js/jquery-ui.min.js" type="text/javascript"></script>
    <script src="assets/js/select2.min.js" type="text/javascript"></script>
    <script src="assets/js/jquery.validate.min.js" type="text/javascript"></script>
    <script src="assets/js/additional-methods.min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#commune_a_chercher').autocomplete({
                source: function(request, response) {
                    $.getJSON("./ajax/commune_acte.php", {
                            term: request.term,
                            idf_source: $('#idf_source').val()
                        },
                        response);
                },
                minLength: 3
            });

            $('#idf_source').change(function() {
                $("form").submit();
            });

            $('#liste_communes').click(function() {
                $('#mode').val("LISTE");
                $("form").submit();
            });

            $('a.lien_geoportail').click(function() {
                window.open(this.href, 'OpenStreetMap', '_blank');
                return false;
            });

        });
    </script>

    <title>Base <?= SIGLE_ASSO; ?> : Etat des relevés</title>
</head>

<body>
    <div class="container">

        <?php require_once __DIR__ . '/commun/menu.php';

        print("<form method=\"get\">");

        switch ($gst_mode) {
            case 'LISTE':
                print('<div class="form-row col-md-12">');
                print('<label for="idf_source" class="col-form-label col-md-2 col-md-offset-2">Source</label>');
                print('<div class="col-md-4">');
                print('<select name=idf_source id=idf_source class="form-control">');
                print(chaine_select_options($id_source, $sources));
                print('</select></div>');

                print('<div class="form-row col-md-12">');
                print("<label for=\"commune_a_chercher\" class=\"col-form-label col-md-2 col-md-offset-2\">Commune</label>");
                print('<div class="col-md-4">');
                print("<input name=\"commune_a_chercher\"  id=\"commune_a_chercher\" value=\"$commune_a_chercher\" size=\"25\" maxlength=\"50\" type=\"Text\" class=\"form-control\" aria-describedby=\"aideCommune\">");
                print('</div>');
                print('<div class="form-group col-md-2"><button type=submit class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Chercher</button></div>');
                print('</div>');
                print('<div class="form-row col-md-12 text-center">');
                print('<small id="aideCommune" class="form-text text-muted">Vous pouvez mettre le caractère "*" pour chercher sur une racine (ex.: saint*)</small></div>');
                print('</div>');

                // Affichage des initiales
                $commune_a_chercher = str_replace('*', '%', $commune_a_chercher);
                if (empty($id_source)) {
                    $st_requete = "SELECT DISTINCT (left( ca.nom, 1 )) AS init FROM `commune_acte` ca ";
                    if (!empty($commune_a_chercher)) {
                        $st_requete .= " where ca.nom like :recherche";
                        $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                    }
                } else {
                    $st_requete = "SELECT DISTINCT (left( ca.nom, 1 )) AS init FROM `commune_acte` ca JOIN `stats_commune` sc on (ca.idf=sc.idf_commune) WHERE sc.idf_source=$id_source ";
                    if (!empty($commune_a_chercher)) {
                        $st_requete .= " and ca.nom like :recherche";
                        $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                    }
                }
                $st_requete .= " ORDER BY init";

                $a_initiales_communes = $connexionBD->sql_select($st_requete);
                if (count($a_initiales_communes) > 0) {
                    print('<div class="form-row">');
                    print('<div class="text-center">');
                    print('<ul class="pagination">');
                    $gc_initiale = $_GET['initiale_statcom'] ?? $a_initiales_communes[0];
                    if (!in_array(utf8_vers_cp1252($gc_initiale), $a_initiales_communes))
                        $gc_initiale = $a_initiales_communes[0];
                    foreach ($a_initiales_communes as $c_initiale) {
                        if ($c_initiale == utf8_vers_cp1252($gc_initiale))
                            print("<li class=\"page-item active\"><span class=\"page-link\">" . cp1252_vers_utf8($c_initiale) . "<span class=\"sr-only\">(current)</span></span></li>");
                        else
                            print("<li class=\"page-item\"><a class=\"page-link\" href=\"/affiche-stats-commune.php?initiale_statcom=" . cp1252_vers_utf8($c_initiale) . "&amp;idf_source=$id_source\">" . cp1252_vers_utf8($c_initiale) . "</a></li>");
                    }
                    print("</ul>");
                    print('</div>');
                    print('</div>');
                    //Calcul de la liste complète des communes commencant par l'initiale
                    if (empty($id_source)) {
                        if (empty($commune_a_chercher))
                            $st_requete = "select ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca  where ca.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' order by ca.nom";
                        else {
                            $st_requete = "select ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca  where ca.nom like :recherche order by ca.nom";
                            $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                        }
                    } else
			if (empty($commune_a_chercher))
                        $st_requete = "select distinct ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca join `stats_commune` sc on (ca.idf=sc.idf_commune) where sc.idf_source=$id_source and ca.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' order by ca.nom";
                    else {
                        $st_requete = "select distinct ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca join `stats_commune` sc on (ca.idf=sc.idf_commune) where sc.idf_source=$id_source and ca.nom like :recherche order by ca.nom";
                        $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                    }
                    $a_liste_communes = $connexionBD->sql_select_multiple_par_idf($st_requete);
                    $i_nb_lignes =  count($a_liste_communes);
                    $i_nb_pages = empty($commune_a_chercher) ? ceil($i_nb_lignes / NB_LIGNES_PAR_PAGE) : 1;

                    if ($gi_num_page_cour > $i_nb_pages) $gi_num_page_cour = $i_nb_pages;
                    if ($gi_num_page_cour < 1) $gi_num_page_cour = 1;
                    // Affichage de la page courante
                    $i_limite_inf = ($gi_num_page_cour - 1) * NB_LIGNES_PAR_PAGE;

                    if (empty($id_source)) {
                        if (empty($commune_a_chercher))
                            $st_requete = "select ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca where ca.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' order by ca.nom limit $i_limite_inf," . NB_LIGNES_PAR_PAGE;
                        else {
                            $st_requete = "select ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca where ca.nom like :recherche order by ca.nom";
                            $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                        }
                    } else {
                        if (empty($commune_a_chercher))
                            $st_requete = "select distinct ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca join `stats_commune` sc on (ca.idf=sc.idf_commune) where sc.idf_source=$id_source and ca.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' order by ca.nom limit $i_limite_inf," . NB_LIGNES_PAR_PAGE;
                        else {
                            $st_requete = "select distinct ca.idf,ca.nom,ca.debut_communale,ca.debut_greffe from `commune_acte` ca join `stats_commune` sc on (ca.idf=sc.idf_commune) where sc.idf_source=$id_source and ca.nom like :recherche order by ca.nom";
                            $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                        }
                    }
                    $a_liste_communes = $connexionBD->sql_select_multiple_par_idf($st_requete);
                    // Affichage de la page courante     
                    affiche_entete_liens_navigation($gi_num_page_cour, $i_nb_pages);
                    if (empty($id_source)) {
                        if (empty($commune_a_chercher))
                            $st_requete = "select stats_commune.idf_commune,stats_commune.idf_type_acte,min(stats_commune.annee_min),max(stats_commune.annee_max),sum(stats_commune.nb_actes) from stats_commune join commune_acte on (stats_commune.idf_commune=commune_acte.idf) where commune_acte.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' group by stats_commune.idf_commune,stats_commune.idf_type_acte";
                        else {
                            $st_requete = "select stats_commune.idf_commune,stats_commune.idf_type_acte,min(stats_commune.annee_min),max(stats_commune.annee_max),sum(stats_commune.nb_actes) from stats_commune join commune_acte on (stats_commune.idf_commune=commune_acte.idf) where commune_acte.nom like :recherche group by stats_commune.idf_commune,stats_commune.idf_type_acte";
                            $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                        }
                    } else {
                        if (empty($commune_a_chercher))
                            $st_requete = "select stats_commune.idf_commune,stats_commune.idf_type_acte,stats_commune.annee_min,stats_commune.annee_max,stats_commune.nb_actes from stats_commune join commune_acte on (stats_commune.idf_commune=commune_acte.idf and commune_acte.nom like '" . utf8_vers_cp1252($gc_initiale) . "%' and stats_commune.idf_source=$id_source) ";
                        else {
                            $st_requete = "select stats_commune.idf_commune,stats_commune.idf_type_acte,stats_commune.annee_min,stats_commune.annee_max,stats_commune.nb_actes from stats_commune join commune_acte on (stats_commune.idf_commune=commune_acte.idf and commune_acte.nom like :recherche and stats_commune.idf_source=$id_source) ";
                            $connexionBD->initialise_params(array(":recherche" => utf8_vers_cp1252($commune_a_chercher)));
                        }
                    }
                    $a_stats_communes = $connexionBD->liste_valeur_par_doubles_clefs($st_requete);
                    if (count($a_liste_communes) != 0) {
                        print("<div class=\"row\">La fourchette affichée est l'intervalle maximal de couverture. Pour obtenir le détail des périodes relevées, cliquer sur le nombre d'actes</div>");
                        print("<div class=\"row\"><table class=\"table table-bordered table-striped table-condensed\">\n");
                        print("<thead><tr>");
                        print("<th>Commune/Paroisse</th><th>Début Coll.<br>Greffe (AD)</th><th>Début Coll.<br>communale</th><th>Naissances</th><th>Mariages</th><th>Décés</th><th>Contrats de mariage</th>");
                        print("</tr></thead>\n");
                        $i = 0;
                        print('<tbody>');
                        foreach ($a_liste_communes as $i_idf_commune => $a_info_commune) {
                            list($st_nom_commune, $i_debut_communale, $i_debut_greffe) = $a_info_commune;
                            print("<tr>");
                            $st_cellule_commune = "<td><a class=\"lien_geoportail\" href=\"/open-street-map.php?idf_commune=$i_idf_commune\">" . cp1252_vers_utf8($st_nom_commune) . "</a></td>";
                            print("$st_cellule_commune");
                            if (empty($i_debut_greffe))
                                print("<td>&nbsp;</td>");
                            else
                                print("<td>$i_debut_greffe</td>");
                            if (empty($i_debut_communale))
                                print("<td>&nbsp;</td>");
                            else
                                print("<td>$i_debut_communale</td>");
                            if (isset($a_stats_communes[$i_idf_commune][IDF_NAISSANCE])) {
                                list($i_annee_min, $i_annee_max, $i_nb_actes) = $a_stats_communes[$i_idf_commune][IDF_NAISSANCE];
                                print cellule_stat($i_idf_commune, IDF_NAISSANCE, $i_annee_min, $i_annee_max, $i_nb_actes);
                            } else
                                print("<td>&nbsp;</td>");
                            if (isset($a_stats_communes[$i_idf_commune][IDF_MARIAGE])) {
                                list($i_annee_min, $i_annee_max, $i_nb_actes) = $a_stats_communes[$i_idf_commune][IDF_MARIAGE];
                                print cellule_stat($i_idf_commune, IDF_MARIAGE, $i_annee_min, $i_annee_max, $i_nb_actes);
                            } else
                                print("<td>&nbsp;</td>");
                            if (isset($a_stats_communes[$i_idf_commune][IDF_DECES])) {
                                list($i_annee_min, $i_annee_max, $i_nb_actes) = $a_stats_communes[$i_idf_commune][IDF_DECES];
                                print cellule_stat($i_idf_commune, IDF_DECES, $i_annee_min, $i_annee_max, $i_nb_actes);
                            } else
                                print("<td>&nbsp;</td>");
                            if (isset($a_stats_communes[$i_idf_commune][IDF_CM])) {
                                list($i_annee_min, $i_annee_max, $i_nb_actes) = $a_stats_communes[$i_idf_commune][IDF_CM];
                                print cellule_stat($i_idf_commune, IDF_CM, $i_annee_min, $i_annee_max, $i_nb_actes);
                            } else
                                print("<td>&nbsp;</td>");
                            print("</tr>\n");
                            $i++;
                        }
                        print('</tbody>');
                        print("</table></div>\n");
                        // Affichage de la page courante     
                        affiche_entete_liens_navigation($gi_num_page_cour, $i_nb_pages);
                    } else
                        print("<div class=\"form-row col-md-12\"><div class=\"text-center alert alert-danger\">Pas de relevés</div></div>");
                } else
                    print("<div class=\"form-row col-md-12\"><div class=\"text-center alert alert-danger\">Pas de communes en base</div></div>");
                break;
            case 'DETAIL':
                print("<input type=hidden name=mode value=\"LISTE\">");
                print('<div class="panel panel-primary">');
                print('<div class="panel-heading">');
                print("Liste des années disponibles de: " . cp1252_vers_utf8($gst_nom_commune) . " (" . cp1252_vers_utf8($gst_type_acte) . ")");
                if ($gst_canton != '')
                    print("<br> Canton de " . cp1252_vers_utf8($gst_canton));
                print("<br>Source: $sources[$id_source]");
                print("</div>");

                print('<div class="panel-body">');
                print('<blockquote class="blockquote">');
                if ($gi_debut_communale != 0) print("Début de la collection communale: $gi_debut_communale<br>");
                if ($gi_debut_greffe != 0) print("Début de la collection du greffe: $gi_debut_greffe");
                print('</blockquote>');
                if (empty($id_source))
                    $st_requete = "select distinct annee,count(idf) from acte where idf_commune=$id_commune and idf_type_acte=$id_type_acte group by annee order by annee";
                else
                    $st_requete = "select distinct annee,count(idf) from acte where idf_commune=$id_commune and idf_type_acte=$id_type_acte and idf_source=$id_source group by annee order by annee";
                //print("Req=$st_requete<br>");
                $a_liste_annnees = $connexionBD->liste_valeur_par_clef($st_requete);
                $i_annee_binf = -1;
                $i_annee_cour = -1;
                $i_cpt = 0;
                if (count($a_liste_annnees) > 0) {
                    $i_nb_actes = 0;
                    print("<table class=\"table table-bordered table-striped table-condensed\">");
                    print("<thead><tr><th>Années</th><th>Nombre d'actes</th></tr></thead>");
                    foreach ($a_liste_annnees as $i_annee => $i_nb_actes_annee) {
                        if ($i_annee == 9999) continue;
                        if ($i_annee == 0) continue;
                        if ($i_annee_binf == -1) {
                            $i_annee_binf = $i_annee;
                            $i_annee_cour = $i_annee;
                            $i_nb_actes = $i_nb_actes_annee;
                            continue;
                        }
                        if ($i_annee == $i_annee_cour + 1) {
                            $i_annee_cour++;
                            $i_nb_actes += $i_nb_actes_annee;
                        } else {

                            print("<tr>");
                            print("<td>" . chaine_intervalle($i_annee_binf, $i_annee_cour) . "</td><td >$i_nb_actes</td></tr>\n");
                            $i_annee_binf = $i_annee;
                            $i_annee_cour = $i_annee;
                            $i_nb_actes   = $i_nb_actes_annee;
                            $i_cpt++;
                        }
                    }
                    print("<tr>");
                    print("<td >" . chaine_intervalle($i_annee_binf, $i_annee_cour) . "</td><td>$i_nb_actes</td></tr>\n");
                    print("</table>");
                } else {
                    print("Pas d'actes");
                }
                print("</div></div>");
                print("<div class=\"row\">");
                print("<a class=\"btn btn-primary col-md-4 col-md-offset-4\" role=\"button\" href=\"" . basename(__FILE__) . "\" id=\"liste_communes\"> <span class=\"glyphicon glyphicon-home\"></span> Liste des communes</a>");
                print("</div>");
                $pf = @fopen("$gst_rep_logs/requetes_depouillements.log", 'a');
                // date_default_timezone_set($gst_time_zone); // NB: Doit prendre le timezone du serveur
                list($i_sec, $i_min, $i_heure, $i_jmois, $i_mois, $i_annee, $i_j_sem, $i_j_an, $b_hiver) = localtime();
                $i_mois++;
                $i_annee += 1900;
                $st_date_log = sprintf("%02d/%02d/%04d %02d:%02d:%02d", $i_jmois, $i_mois, $i_annee, $i_heure, $i_min, $i_sec);
                $gst_adresse_ip = $_SERVER['REMOTE_ADDR'];
                $st_ident = $session->getAttribute('ident');
                $st_chaine_log = join(';', array($st_date_log, $st_ident, $gst_adresse_ip, cp1252_vers_utf8($gst_nom_commune), cp1252_vers_utf8($gst_type_acte)));
                @fwrite($pf, "$st_chaine_log\n");
                @fclose($pf);
                break;
        } ?>
        </form>
    </div>
</body>

</html>