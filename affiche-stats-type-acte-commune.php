<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/Commun/commun.php';

// ======= Default
$sources = $connexionBD->liste_valeur_par_clef("SELECT idf, nom FROM source ORDER BY nom");
$stats = [];
// ===============

$id_source = isset($_GET['idf_source']) ? $_GET['idf_source'] : 1;

$sql1 = "SELECT ca.idf, ca.nom 
    FROM commune_acte ca 
    JOIN `stats_commune` sc ON (ca.idf=sc.idf_commune) 
    WHERE sc.idf_source=$id_source 
    ORDER BY ca.nom";
$communes = $connexionBD->liste_valeur_par_clef($sql1);

$a_idf_communes = array_keys($communes);
$id_commune = isset($_GET['idf_commune']) ? $_GET['idf_commune'] : $a_idf_communes[0];

if (count($communes) != 0) {
    if (!in_array($id_commune, $a_idf_communes)) $id_commune = $a_idf_communes[0];
    $sql2 = "SELECT nom, annee_min, annee_max, nb_actes 
        FROM stats_commune 
        JOIN type_acte ON (idf_type_acte=idf) 
        WHERE idf_source=$id_source 
        AND idf_commune=$id_commune 
        ORDER BY nom";
    $stats = $connexionBD->sql_select_multiple($sql2);
}

?>
<!DOCTYPE html>

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
    <script type="text/javascript">
        $(document).ready(function() {

            $.fn.select2.defaults.set("theme", "bootstrap");

            $(".js-select-avec-recherche").select2();

            $("#idf_source").change(function() {
                this.form.submit();
            });

            $("#idf_commune").change(function() {
                this.form.submit();
            });

        });
    </script>
    <title>Base <?= SIGLE_ASSO; ?> : Statistiques par commune</title>
</head>

<body>
    <div class="container">
        <?php require_once __DIR__ . '/Commun/menu.php'; ?>

        <form method="get">
            <div class="form-row col-md-12"><label for="idf_source" class="col-form-label col-md-2 col-md-offset-3">Source</label>
                <div class="col-md-4 ">
                    <select name="idf_source" id="idf_source" class="js-select-avec-recherche form-control">
                        <?= chaine_select_options($id_source, $sources); ?>
                    </select>
                </div>
            </div>
            <div class="form-row col-md-12">
                <label for="idf_commune" class="col-form-label col-md-2 col-md-offset-3">Commune</label>
                <div class="col-md-4">
                    <select name="idf_commune" id="idf_commune" class="js-select-avec-recherche form-control">
                        <?= chaine_select_options($id_commune, $communes); ?>
                    </select>
                </div>
            </div>
        </form>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Type d'acte</th>
                    <th>Année minimale</th>
                    <th>Année maximale</th>
                    <th>Nombre d'actes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($stats) > 0) {
                    foreach ($stats as $a_ligne) { ?>
                        <tr>
                        <td><?= cp1252_vers_utf8($a_ligne[0]); ?></td>
                        <td><?= $a_ligne[1]; ?></td>
                        <td><?= $a_ligne[2]; ?></td>
                        <td><?= $a_ligne[3]; ?></td>
                        </tr>
                    <?php }
                } ?>

            </tbody>
        </table>
        <?php if (count($stats) <= 0) { ?>
            <div class="alert alert-warning">Pas de données</div>
        <?php } ?>

    </div>
</body>

</html>