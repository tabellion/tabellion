<?php
// Copyright (C) : Fabrice Bouffanet 2010-2019 (Association Généalogique de la Charente)
// Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les termes de la
// Licence Publique Générale GPL GNU publiée par la Free Software Foundation
// Texte de la licence : http://www.gnu.org/copyleft/gpl.html
//-------------------------------------------------------------------
require_once __DIR__ . '/app/bootstrap.php';

$gf_pi = 3.14159265359;
$id_commune = (int) $_GET['idf_commune'] ?? null;
if (!$id_commune) {
    throw new Exception("Erreur: L'identifiant de commune est manquant.");
}

$error = null;

$commune = $connexionBD->find("SELECT nom, latitude, longitude FROM commune_acte WHERE idf=$id_commune");
if (is_null($commune)) {
    $error ="Cette commune n'existe pas dans la database.";
}
if (null == $commune['latitude'] || null == $commune['longitude']) {
    $error ="Les coordonnées de cette commune ne sont pas dans la database.";
    $f_lat_deg = 45.72;
    $f_lon_deg = -26.90;
} else {
    $f_lat_deg = $commune['latitude'] * 180 / $gf_pi;
    $f_lon_deg = $commune['longitude'] * 180 / $gf_pi;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-language" content="fr">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='assets/css/styles.css' type='text/css' rel='stylesheet'>
    <link href='assets/css/bootstrap.min.css' rel='stylesheet'>
    <script src='assets/js/jquery-min.js' type='text/javascript'></script>
    <script src='assets/js/bootstrap.min.js' type='text/javascript'></script>
    <script type='text/javascript'>
        $(document).ready(function() {
            $("#ferme").click(function() {
                window.close();
            });
        });
    </script>
    <meta charset="utf-8">
    <!-- Nous chargeons les fichiers CDN de Leaflet. Le CSS AVANT le JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css" integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==" crossorigin="" />
    <style type="text/css">
        #map {
            /* la carte DOIT avoir une hauteur sinon elle n'apparaît pas */
            width: 100%;
            height: 600px;
            background-position: center center;
            background-repeat: no-repeat;
        }
    </style>
    <?php print("<title>API OpenStreeMap - $commune[nom] </title>"); ?>
</head>

<body>
    <div class="container">
        <div class="text-center"><img src="<?php $gst_logo_association; ?>"></div>
        <?php print("<div class=\"text-center\">" . htmlentities($commune['nom'], ENT_COMPAT, 'cp1252') . "</div><br>\n"); ?>
        <?php  if ($error) { ?>
            <div class="bg-danger text-center">
                <?= $error; ?>
            </div>
        <?php } ?>
        <div class="row center-block">
            <div id="map">
                <!-- Ici s'affichera la carte -->
            </div>
        </div>
        <div class="row">
            <button type="button" id=ferme class="btn btn-warning col-xs-4 col-xs-offset-4">Fermer la fenêtre</button>
        </div>
        <!-- Fichiers Javascript -->
        <script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js" integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==" crossorigin=""></script>
        <script type="text/javascript">
            // On initialise la latitude et la longitude de Paris (centre de la carte)
            var lat = <?php echo json_encode($f_lat_deg); ?>;
            var lon = <?php echo json_encode($f_lon_deg); ?>;
            var ville = <?php echo json_encode($commune['nom']); ?>;
            var macarte = null;
            // Fonction d'initialisation de la carte

            function initMap() {
                // Créer l'objet "macarte" et l'insèrer dans l'élément HTML qui a l'ID "map"
                macarte = L.map('map').setView([lat, lon], 11);
                // Leaflet ne récupère pas les cartes (tiles) sur un serveur par défaut. Nous devons lui préciser où nous souhaitons les récupérer. Ici, openstreetmap.fr
                L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
                    // Il est toujours bien de laisser le lien vers la source des données
                    attribution: 'données © <a href="//osm.org/copyright">OpenStreetMap</a>/ODbL - rendu <a href="//openstreetmap.fr">OSM France</a>',
                    minZoom: 1,
                    maxZoom: 20
                }).addTo(macarte);
                // Nous ajoutons un marqueur
                var marker = L.marker([lat, lon]).addTo(macarte);
                // Nous ajoutons la popup. A noter que son contenu (ici la variable ville) peut être du HTML
                marker.bindPopup(ville);
            }

            window.onload = function() {
                // Fonction d'initialisation qui s'exécute lorsque le DOM est chargé
                initMap();
            };
        </script>
</body>

</html>