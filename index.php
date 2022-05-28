<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';

// Heure & Date
$st_date = date("d-m-Y");
$st_heure = date("H:i");
$gi_nbjours = 30;

// Combien d'adhérents ?				
$gi_nb_adherents = $connexionBD->sql_select1("SELECT count(*) FROM adherent WHERE statut IN ('" . ADHESION_BULLETIN . "','" . ADHESION_INTERNET . "')");
// Combien de X ?
$gi_nb_mar = $connexionBD->sql_select1("SELECT sum(nb_actes) FROM `stats_commune` WHERE idf_type_acte=" . IDF_MARIAGE);
// Combien de DIV ?
$gi_nb_cm = $connexionBD->sql_select1("SELECT sum(nb_actes) FROM `stats_commune` WHERE idf_type_acte=" . IDF_DIVERS);
// Combien de ° ?
$gi_nb_nai = $connexionBD->sql_select1("SELECT sum(nb_actes) FROM `stats_commune` WHERE idf_type_acte=" . IDF_NAISSANCE);
// Combien de + ?
$gi_nb_dec = $connexionBD->sql_select1("SELECT sum(nb_actes) FROM `stats_commune` WHERE idf_type_acte=" . IDF_DECES);
// combien au total		
$gi_nb_actes_total = $connexionBD->sql_select1("SELECT sum(nb_actes) FROM `stats_commune`");

// Transformations pour la vue
$aujourdhui = "$st_date à $st_heure";
$n_adherents = $gi_nb_adherents;
$n_naissances = number_format($gi_nb_nai, 0, ',', ' ');
$n_mariages =  number_format($gi_nb_mar, 0, ',', ' ');
$n_deces = number_format($gi_nb_dec, 0, ',', ' ');
$n_contrats_mariage = number_format($gi_nb_cm, 0, ',', ' ');
$n_actes = number_format($gi_nb_actes_total, 0, ',', ' ');

$a_chargements = $connexionBD->sql_select_multiple("select date_format(c.date_chgt,'%d/%m/%Y'),ca.nom,c.type_acte_nim,c.nb_actes from `chargement` c join commune_acte ca on (c.idf_commune=ca.idf) where datediff(now(),c.date_chgt)<$gi_nbjours and c.publication=1 order by c.date_chgt desc");

?>
<!DOCTYPE html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="content-language" content="fr">
    <title>Bienvenue sur la base <?= SIGLE_ASSO; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/styles.css" type='text/css' rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/js/jquery-min.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <link rel="shortcut icon" href="assets/img/favicon.ico">

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

</head>

<body>
    <div class="container">

        <?php require_once __DIR__ . '/commun/menu.php'; ?>

        <div class='row col-md-12'>
            <div class='col-md-4'>
                <div class="panel-group">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Notre espace membres</div>
                        <div class="panel-body">
                            <div class="row text-center">Aujourd'hui le <?= $aujourdhui ?></div>
                            <div class="row text-center"><?= $n_adherents; ?> adhérents inscrits sur la base <?= SIGLE_ASSO; ?></div>
                        </div>
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading">Info sur la base</div>
                        <div class="panel-body">
                            <div class="row text-center"><?= $n_actes; ?> actes dont :</div>
                            <div class="row text-center">Naissances: <?= $n_naissances; ?> actes</div>
                            <div class="row text-center">Mariages: <?= $n_mariages; ?> actes</div>
                            <div class="row text-center">Décès: <?= $n_deces; ?> actes</div>
                            <div class="row text-center">CM: <?= $n_contrats_mariage; ?> actes</div>
                            <div class="row text-center">et autres...</div>
                        </div>
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading">Historique des chargements sur les <?= $gi_nbjours; ?> derniers jours</div>
                        <div class="panel-body">
                            <?php if (count($a_chargements) == 0) { ?>
                                <div class="alert alert-warning">Pas de chargements disponible</div>
                            <?php } else { ?>
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th scope="col">Commune</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Nbre actes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($a_chargements as $a_chargement) {
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
                                            } ?>
                                            <tr>
                                                <td><?= cp1252_vers_utf8($st_commune); ?></td>
                                                <td><?= $st_type_acte; ?></td>
                                                <td><?= $i_nb_actes; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <?php
                // Lit les bulletins dans le répertoire articles. Ils doivent commencer par article-bulletin et avoir l'extension HTML
                $a_bulletins = glob("../storage/articles/article-bulletin*.html");
                $st_bulletin_html = '';
                if (count($a_bulletins) > 0) {
                    // Choisit un numéro au hasard
                    $i_bulletin_choisi = mt_rand(1, count($a_bulletins) - 1);
                    // construit le nom de fichier
                    $st_article_bulletin = $a_bulletins[$i_bulletin_choisi];
                    // L'affichage du bulletin est remplacée par celle d'une annonce si celle-ci existe 
                    if (file_exists("../storage/articles/annonce.html"))
                        $st_bulletin_html = file_get_contents("../storage/articles/annonce.html");
                    else
                        $st_bulletin_html = file_get_contents($st_article_bulletin);
                    if (preg_match('~<body[^>]*>(.*?)</body>~si', $st_bulletin_html, $a_patterns))
                        print(cp1252_vers_utf8($a_patterns[1]));
                } else { ?>
                    <div class="alert alert-warning">Pas d'article disponible</div>
                <?php } ?>
            </div>
            <div class="col-md-4">

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
                    <div class="panel-heading ">Loi sur les délais de communication des archives (Journal Officiel du 16 juillet 2008) </div>
                    <div class="panel-body ">

                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th scope="col"> Nature des documents
                                    </th>
                                    <th scope="col"> Délai initial
                                    </th>
                                    <th scope="col"> Nouveau délai
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td> Vie privée </td>
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
                                    <td> Registres de naissance de l'état civil </td>
                                    <td> 100 ans </td>
                                    <td> 75 ans </td>
                                </tr>
                                <tr>
                                    <td> Registres de mariage de l'état civil </td>
                                    <td> 100 ans </td>
                                    <td> 75 ans </td>
                                </tr>
                                <tr>
                                    <td> Registres de décès de l'état civil </td>
                                    <td> - </td>
                                    <td> Immédiat </td>
                                </tr>
                                <tr>
                                    <td> Tables décennales </td>
                                    <td> 100 ans </td>
                                    <td> Immédiat </td>
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
                                    <td> Secret médical </td>
                                    <td> 150 ans </td>
                                    <td> 120 ans ou 25 ans à compter du décès </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
</body>

</html>