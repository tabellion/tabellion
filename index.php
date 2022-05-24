<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/Commun/Identification.php';
require_once __DIR__ . '/Commun/commun.php';
require_once __DIR__ . '/Commun/constantes.php';
require_once __DIR__ . '/Commun/ConnexionBD.php';

$connexionBD = ConnexionBD::singleton($gst_serveur_bd, $gst_utilisateur_bd, $gst_mdp_utilisateur_bd, $gst_nom_bd);

// Combien d'adhérents ?				
$gi_nb_adherents = $connexionBD->sql_select1("select count(*) from adherent where statut IN ('" . ADHESION_BULLETIN . "','" . ADHESION_INTERNET . "')");
// Combien de X ?
$gi_nb_mar = $connexionBD->sql_select1("select sum(nb_actes) from `stats_commune` where idf_type_acte=" . IDF_MARIAGE);
// Combien de DIV ?
$gi_nb_cm = $connexionBD->sql_select1("select sum(nb_actes) from `stats_commune` where idf_type_acte=" . IDF_DIVERS);
// Combien de ° ?
$gi_nb_nai = $connexionBD->sql_select1("select sum(nb_actes) from `stats_commune`  where idf_type_acte=" . IDF_NAISSANCE);
// Combien de + ?
$gi_nb_dec = $connexionBD->sql_select1("select sum(nb_actes) from `stats_commune` where idf_type_acte=" . IDF_DECES);
// combien au total		
$gi_nb_actes_total = $connexionBD->sql_select1("select sum(nb_actes) from `stats_commune`");

print('<!DOCTYPE html>');
print("<head>");
print('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">');
print('<meta http-equiv="content-language" content="fr">');
$st_prefixe_asso = commence_par_une_voyelle(SIGLE_ASSO) ? "de l'" : "du ";
print("<title>Bienvenue sur la base $st_prefixe_asso" . SIGLE_ASSO . "!</title>");
print('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
print("<link href='assets/css/styles.css' type='text/css' rel='stylesheet'>");
print("<link href='assets/css/bootstrap.min.css' rel='stylesheet'>");
print("<script src='assets/js/jquery-min.js' type='text/javascript'></script>");
print("<script src='assets/js/bootstrap.min.js' type='text/javascript'></script>");
print('<link rel="shortcut icon" href="assets/img/favicon.ico">');
?>
<!-- script Google Analytics -- debut -->
<script type='text/javascript'>
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-9306738-3']);
    _gaq.push(['_trackPageview']);
    (function() {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
</script>
<!-- //script Google Analytics -- fin -->
<?php
print("</head>");
print("<body>");
print('<div class="container">');

require_once __DIR__ . '/Commun/menu.php';

print("<div class='row col-md-12'>");
print("<div class='col-md-4'>");
print('<div class="panel-group">');
print('<div class="panel panel-primary">');
print('<div class="panel-heading">Notre espace membres</div>');
print('<div class="panel-body">');
// Heure & Date
$st_date = date("d-m-Y");
$st_heure = date("H:i");
print("<div class=\"row text-center\">Aujourd'hui le $st_date &agrave; $st_heure</div>");
print("<div class=\"row text-center\">$gi_nb_adherents adh&eacute;rents inscrits sur la base de $st_prefixe_asso" . SIGLE_ASSO . "</div>");
print('</div></div>');
print('<div class="panel panel-primary">');
print('<div class="panel-heading">Info sur la base</div>');
print('<div class="panel-body">');
print("<div class=\"row text-center\">" . number_format($gi_nb_actes_total, 0, ',', ' ') . " actes dont :</div>");
print("<div class=\"row text-center\">Naissances: " . number_format($gi_nb_nai, 0, ',', ' ') . " actes</div>");
print("<div class=\"row text-center\">Mariages: " . number_format($gi_nb_mar, 0, ',', ' ') . " actes</div>");
print("<div class=\"row text-center\">D&eacute;c&egrave;s: " . number_format($gi_nb_dec, 0, ',', ' ') . " actes</div>");
print("<div class=\"row text-center\">CM: " . number_format($gi_nb_cm, 0, ',', ' ') . " actes</div>");
print("<div class=\"row text-center\">et autres...</div>");
print('</div></div>');

$gi_nbjours = 30;

print('<div class="panel panel-primary">');
print("<div class=\"panel-heading\">Historique des chargements sur les $gi_nbjours derniers jours</div>");
print('<div class="panel-body">');
$a_chargements = $connexionBD->sql_select_multiple("select date_format(c.date_chgt,'%d/%m/%Y'),ca.nom,c.type_acte_nim,c.nb_actes from `chargement` c join commune_acte ca on (c.idf_commune=ca.idf) where datediff(now(),c.date_chgt)<$gi_nbjours and c.publication=1 order by c.date_chgt desc");
if (count($a_chargements) == 0) {
    print("<div class=\"alert alert-danger\">Pas de chargements dans les $gi_nbjours derniers jours</div>");
} else {
    print("<table class=\"table table-bordered table-striped table-sm\">");
    print("<thead>");
    print("<tr>");
    print("<th scope=\"col\">Commune</th><th scope=\"col\">Type</th><th scope=\"col\">Nbre actes</th>");
    print("</tr>");
    print("</thead>");
    print("<tbody>");
    foreach ($a_chargements as $a_chargement) {
        list($st_date, $st_commune, $st_type_nim, $i_nb_actes) = $a_chargement;
        switch ($st_type_nim) {
            case IDF_NAISSANCE:
                $st_type_acte = '°';
                break;
            case IDF_MARIAGE:
                $st_type_acte = 'X';
                break;
            case IDF_DECES:
                $st_type_acte = '&dagger;';
                break;
            case IDF_DIVERS:
                $st_type_acte = 'Divers(CM...)';
                break;
            case IDF_RECENS:
                $st_type_acte = 'Recensement';
                break;
        }
        print("<tr>");
        print("<td>" . cp1252_vers_utf8($st_commune) . "</td><td>$st_type_acte</td><td>$i_nb_actes</td>");
        print("</tr>");
    }
    print("</tbody>");
    print("</table>");
}
print('</div></div></div>');
print("</div>");

print('<div class="col-md-4">');
// Lit les bulletins dans le répertoire Articles. Ils doivent commencer par ArticleBulletin et avoir l'extension HTML
$a_bulletins = glob("Articles/ArticleBulletin*.html");
$st_bulletin_html = '';
if (count($a_bulletins) > 0) {
    // Choisit un numéro au hasard
    $i_bulletin_choisi = mt_rand(1, count($a_bulletins) - 1);
    // construit le nom de fichier
    $st_article_bulletin = $a_bulletins[$i_bulletin_choisi];
    // L'affichage du bulletin est remplacée par celle d'une annonce si celle-ci existe 
    if (file_exists("Articles/Annonce.html"))
        $st_bulletin_html = file_get_contents("Articles/Annonce.html");
    else
        $st_bulletin_html = file_get_contents($st_article_bulletin);
    if (preg_match('~<body[^>]*>(.*?)</body>~si', $st_bulletin_html, $a_patterns))
        print(cp1252_vers_utf8($a_patterns[1]));
} else
    print('<div class="alert alert-danger">Pas d\'article disponible</div>');

print("</div>");
print('<div class="col-md-4">');
?>

<div class="panel panel-primary">
    <div class="panel-heading">Entraide inter-régionale, mariés ailleurs :</div>
    <div class="panel-body">
        <div class="text-justify">
            le Cercle généalogique des Deux-Sèvres nous a communiqué la liste des
            mariages relevés dans les registres des communes (79) dont l'un au moins
            des époux est originaire de Charente.</div>
        <br>
        <div class="text-justify">
            Nos adhérents, peuvent consulter ces relevés sur les ordinateurs de notre
            local de permanence, ou lors de rencontres ponctuelles ou l'A.G.C est
            présente.</div>
        <br>
        <div class="text-justify">
            A noter que nous procédons de même avec l'envoi des mariages de
            Deux-Sèvriens célébrés en Charente.</div>
    </div>
    <br>
</div>

<div class="panel panel-primary">
    <div class="panel-heading ">Loi sur les d&eacute;lais de communication des archives (Journal Officiel du 16&nbsp;juillet&nbsp;2008) </div>
    <div class="panel-body ">

        <table class="table table-bordered table-striped table-sm">
            <thead>
                <tr>
                    <th scope="col"> Nature des documents
                    </th>
                    <th scope="col"> D&eacute;lai initial
                    </th>
                    <th scope="col"> Nouveau d&eacute;lai
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td> Vie priv&eacute;e </td>
                    <td> 60 ans </td>
                    <td> 50 ans </td>
                </tr>
                <tr>
                    <td> Actes des notaires </td>
                    <td> 100 ans </td>
                    <td> 75 ans </td>
                </tr>
                <tr>
                    <td> Archives des juridictions </td>
                    <td> 100 ans </td>
                    <td> 75 ans </td>
                </tr>
                <tr>
                    <td> Registres de naissance de l'&eacute;tat civil </td>
                    <td> 100 ans </td>
                    <td> 75 ans </td>
                </tr>
                <tr>
                    <td> Registres de mariage de l'&eacute;tat civil </td>
                    <td> 100 ans </td>
                    <td> 75 ans </td>
                </tr>
                <tr>
                    <td> Registres de d&eacute;c&egrave;s de l'&eacute;tat civil </td>
                    <td> - </td>
                    <td> Imm&eacute;diat </td>
                </tr>
                <tr>
                    <td> Tables d&eacute;cennales </td>
                    <td> 100 ans </td>
                    <td> Imm&eacute;diat </td>
                </tr>
                <tr>
                    <td> Questionnaires de recensement de la population </td>
                    <td> 100 ans </td>
                    <td> 75 ans </td>
                </tr>
                <tr>
                    <td> Dossiers de personnels </td>
                    <td> 120 ans </td>
                    <td> 75 ans </td>
                </tr>
                <tr>
                    <td> Secret m&eacute;dical </td>
                    <td> 150 ans </td>
                    <td> 120 ans ou 25 ans &agrave; compter du d&eacute;c&egrave;s </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php
print("</div>");
print("</div></body></html>");
